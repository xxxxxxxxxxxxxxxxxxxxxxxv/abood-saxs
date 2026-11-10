<?php
/**
 * Hacking functions class
 *
 * @since 13.0
 *
 * @package RosarioSIS
 */

namespace RosarioSIS\Functions;

class Hacking
{
	/** @var int Max. Hacking Attempts to log user out */
	public $max_attempts;

	/** @var int Hacking Attempts within one minute */
	public $attempts_within_one_minute;

	/**
	 * Constructor
	 *
	 * @global $_SESSION['HackingLog']
	 *
	 * @param int $max_attempts Max. Hacking Attempts to log user out.
	 */
	function __construct( $max_attempts = 10 )
	{
		$this->max_attempts = $max_attempts;

		// Log Hacking time in session.
		$_SESSION['HackingLog'][] = time();

		$this->attempts_within_one_minute = $this->attemptsWithinOneMinute();
	}

	/**
	 * Calculate Hacking Attempts within the last minute
	 *
	 * @global $_SESSION['HackingLog']
	 *
	 * @return int Hacking Attempts within one minute.
	 */
	function attemptsWithinOneMinute()
	{
		$attempts = 0;

		$one_minute_ago = time() - 60;

		foreach ( $_SESSION['HackingLog'] as $i => $time )
		{
			if ( $time >= $one_minute_ago )
			{
				$attempts++;

				continue;
			}

			unset( $_SESSION['HackingLog'][ $i ] );
		}

		return $attempts;
	}

	/**
	 * Log Hacking Attempt
	 * Log in Apache error.log
	 *
	 * @uses ErrorSendEmail()
	 *
	 * @global $error
	 *
	 * @param  bool $send_email Send email (optional). Defaults to true.
	 * @param  bool $redirect   Redirect user (optional). Defaults to true.
	 *
	 * @return bool True if email sent.
	 */
	function log( $send_email = true, $redirect = true )
	{
		global $error;

		/**
		 * Log "RosarioSIS HACKING ATTEMPT" into Apache error.log
		 * So you can ban IP using a custom fail2ban jail
		 */
		error_log( 'RosarioSIS HACKING ATTEMPT' );

		if ( empty( $error ) )
		{
			$error[] = _( 'You\'re not allowed to use this program!' );
		}

		if ( $send_email )
		{
			$this->sendEmail( 'HACKING ATTEMPT' );
		}

		if ( $redirect )
		{
			$this->redirect();
		}
		else
		{
			$this->forbidden();
		}
	}

	/**
	 * Send email
	 * If user has NOT just logged in (&redirect_to= in referer)
	 * Or if attempts within one minute > max attempts
	 *
	 * @global $error
	 *
	 * @param  string $title Email title.
	 *
	 * @return bool True if email sent.
	 */
	function sendEmail( $title )
	{
		global $error;

		$email_sent = false;

		if ( $this->attempts_within_one_minute >= $this->max_attempts
			|| empty( $_SERVER['HTTP_REFERER'] )
			// Do not send email if user has just logged in (&redirect_to= in referer)
			|| mb_strpos( $_SERVER['HTTP_REFERER'], '&redirect_to=' ) === false )
		{
			$email_sent = ErrorSendEmail( $error, $title );
		}

		return $email_sent;
	}

	/**
	 * Redirect user
	 * Default redirection:
	 * To Portal
	 * Or logout if attempts within one minute > max attempts
	 *
	 * @param string $redirect_url Redirect URL (optional).
	 */
	function redirect( $redirect_url = '' )
	{
		if ( ! $redirect_url )
		{
			$redirect_url = 'Modules.php?modname=misc/Portal.php';

			if ( $this->attempts_within_one_minute >= $this->max_attempts )
			{
				// Destroy session now: some clients do not follow redirection.
				session_unset();

				session_destroy();

				// Logout after 10 Hacking attempts within 1 minute.
				$redirect_url = 'index.php?modfunc=logout&token=' . $_SESSION['token'];
			}
		}

		/**
		 * Redirect automatically to Portal or Logout.
		 * Redirection is done in HTML.
		 *
		 * @link https://stackoverflow.com/questions/42216700/how-can-i-redirect-after-oauth2-with-samesite-strict-and-still-get-my-cookies#answer-64216367
		 */
		ob_clean();
		?>
		<!-- Redirected from \RosarioSIS\Functions\Hacking::redirect() -->
		<html>
		<head>
		<meta http-equiv="REFRESH" content="0;URL=<?php echo URLEscape( $redirect_url ); ?>" />
		</head>
		<body></body>
		</html>
		<?php

		exit;
	}

	/**
	 * Forbidden error
	 *
	 * @global $error
	 *
	 * Return 403 only if not logged in & not AJAX request.
	 */
	function forbidden()
	{
		global $error;

		if ( empty( $_SESSION['STAFF_ID'] ) && empty( $_SESSION['STUDENT_ID'] )
			&& ! isAJAX() )
		{
			// Not logged in & not AJAX request.
			http_response_code( 403 );
		}

		echo ErrorMessage( $error );

		exit;
	}
}
