<?php
/**
 * Send Email function.
 *
 * @package RosarioSIS
 * @subpackage ProgramFunctions
 */

/**
 * Send Email
 * And eventual Attachment(s)
 * From: RosarioSIS <rosariosis@yourdomain.com>
 *
 * @since 3.6.1 ProgramFunctions/SendEmail.fnc.php|before_send action hook.
 * @since 8.7 ProgramFunctions/SendEmail.fnc.php|send_error action hook.
 * @since 12.0 Update PHPMailer from v5.2.8 to v6.9.1
 * @since 12.7 Autoload classes (PSR-4)
 * @deprecated since 13.0 Use (new RosarioSIS\Functions\Email)->send() instead
 *
 * @example SendEmail( $to, $subject, $msg, 'Foo <bar@from.address>', $cc, array( array( $pdf_file, $pdf_name ) ) );
 *
 * @link https://www.mail-tester.com/
 *
 * @uses PHPMailer class
 * @global $phpmailer
 *
 * @param string|array $to          Recipients, array or comma separated list of emails.
 * @param string       $subject     Subject.
 * @param string       $message     Message.
 * @param string       $reply_to    Reply To email.
 * @param string|array $cc          Carbon Copy, array or comma separated list of emails.
 * @param array        $attachments Array of file paths, or Array of Attachments (file path, file name).
 *
 * @return boolean true if email sent, or false
 */
function SendEmail( $to, $subject, $message, $reply_to = null, $cc = null, $attachments = [] )
{
	return (new RosarioSIS\Functions\Email)->send( $to, $subject, $message, $reply_to, $cc, $attachments );
}
