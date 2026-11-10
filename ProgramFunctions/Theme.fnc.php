<?php
/**
 * Theme functions
 *
 * @package RosarioSIS
 * @subpackage ProgramFunctions
 */


/**
 * Theme live update.
 * Configured theme has changed? Update it live!
 * Reload the page so the new theme stylesheet is loaded.
 *
 * @since  3.0
 *
 * @param  string  $new_theme New theme name / directory.
 * @param  string  $old_theme Old theme name / directory.
 * @param  boolean $default   Is default theme (Configuration.php) or Preferred theme (Preferences.php)?
 *
 * @return boolean            False if has not changed, else DIES before reloading page.
 */
function ThemeLiveUpdate( $new_theme, $old_theme, $default = true )
{
	if ( ! $new_theme
		|| ! $old_theme
		|| ( $new_theme === $old_theme && ! $default ) )
	{
		// Theme has not changed in My Preferences.
		return false;
	}

	if ( ! $default
		&& Config( 'THEME_FORCE' ) )
	{
		// Theme forced, we should not be able to change it anyway from My Preferences!
		return false;
	}


	if ( $default )
	{
		// Check if loaded stylesheet is the same as the new one.
		// Get the value from DB as Preferences() value is overridden by THEME_FORCE!
		$real_user_preferred_theme = DBGetOne( "SELECT VALUE
			FROM program_user_config
			WHERE USER_ID='" . User( 'STAFF_ID' ) . "'
			AND PROGRAM='Preferences'
			AND TITLE='THEME'" );

		if ( ( ! $real_user_preferred_theme && $new_theme === $old_theme )
			|| ( $real_user_preferred_theme === $new_theme && ! Config( 'THEME_FORCE' ) ) )
		{
			// User already had that Theme loaded, do nothing.
			return false;
		}
	}

	if ( $default
		&& ! Config( 'THEME_FORCE' )
		&& Preferences( 'THEME' ) !== $new_theme )
	{
		// If not Forcing theme, update admin Preferred theme too.
		DBUpdate(
			'program_user_config',
			[ 'VALUE' => $new_theme ],
			[
				'USER_ID' => User( 'STAFF_ID' ),
				'PROGRAM' => 'Preferences',
				'TITLE' => 'THEME',
			]
		);
	}

	/**
	 * Reload the page so the new theme stylesheet gets loaded
	 * Redirection is done in HTML.
	 *
	 * @since 12.5 CSP remove unsafe-inline Javascript
	 *
	 * @link https://stackoverflow.com/questions/42216700/how-can-i-redirect-after-oauth2-with-samesite-strict-and-still-get-my-cookies#answer-64216367
	 */
	ob_clean();
	?>
	<html>
	<head>
	<meta http-equiv="REFRESH" content="0;" />
	</head>
	<body></body>
	</html>
	<?php

	die();
}
