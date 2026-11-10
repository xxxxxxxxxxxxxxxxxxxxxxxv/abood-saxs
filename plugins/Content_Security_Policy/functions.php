<?php
/**
 * Functions
 *
 * @package Content Security Policy plugin
 */

// Add our ContentSecurityPolicyCronDo() function to the Warehouse.php|header action.
add_action( 'Warehouse.php|header', 'ContentSecurityPolicyCronDo' );

/**
 * Run daily CRON on page load.
 * Do my CRON logic.
 *
 * @uses Warehouse.php|header action hook
 */
function ContentSecurityPolicyCronDo()
{
	$cron_day = Config( 'CONTENT_SECURITY_POLICY_CRON_DAY' );

	if ( is_null( $cron_day ) )
	{
		// Config option does not exist yet, insert it into SCHOOL_ID=0.
		DBQuery( "INSERT INTO config (SCHOOL_ID, TITLE, CONFIG_VALUE)
			VALUES (0, 'CONTENT_SECURITY_POLICY_CRON_DAY', CURRENT_DATE);" );

		$cron_day = DBDate();
	}

	if ( DBDate() <= $cron_day
		|| ! UserSchool()
		|| basename( $_SERVER['PHP_SELF'] ) === 'index.php' )
	{
		// CRON already ran today or not logged in.
		return false;
	}

	// Save CRON day.
	Config( 'CONTENT_SECURITY_POLICY_CRON_DAY', DBDate() );

	require_once 'plugins/Content_Security_Policy/includes/common.fnc.php';

	$return = ContentSecurityPolicySendReport( $cron_day );

	return $return;
}

/**
 * Portal Alerts.
 *
 * @uses misc/Portal.php|portal_alerts hook
 *
 * @return bool True if warning, else false.
 */
function ContentSecurityPolicyPortalAlerts()
{
	global $warning,
		$RosarioPlugins;

	if ( User( 'PROFILE' ) !== 'admin'
		|| ! $RosarioPlugins['Content_Security_Policy']
		|| ! AllowUse( 'School_Setup/Configuration.php' )
		|| ! $_SESSION['LAST_LOGIN'] ) // Fix SQL error incorrect TIMESTAMP value: ''
	{
		return false;
	}

	$multiple_schools_admin_has_1_school = SchoolInfo( 'SCHOOLS_NB' ) > 1
		&& DBGetOne( "SELECT SCHOOLS
			FROM staff
			WHERE STAFF_ID='" . User( 'STAFF_ID' ) . "'
			AND SYEAR='" . UserSyear() . "'
			AND SCHOOLS='," . UserSchool() . ",';" );

	if ( $multiple_schools_admin_has_1_school )
	{
		return false;
	}

	$has_new_reports = DBGetOne( "SELECT 1
		FROM csp_reports
		WHERE CREATED_AT>'" . DBEscapeString( $_SESSION['LAST_LOGIN'] ) . "'" );

	if ( ! $has_new_reports )
	{
		return false;
	}

	$reports_url = 'Modules.php?modname=School_Setup/Configuration.php&tab=plugins&modfunc=config&plugin=Content_Security_Policy';

	$start_date = ExplodeDate( mb_substr( $_SESSION['LAST_LOGIN'], 0, 10 ) );

	$reports_url .= '&month_start=' . $start_date['month'] . '&day_start=' . $start_date['day'] .
		'&year_start=' . $start_date['year'];

	$reports_link = ' - <a href="' . URLEscape( $reports_url ) . '">' . _( 'Reports' ) . '</a>';

	// Add warning.
	$warning[] = _( 'Content Security Policy Violations' ) . $reports_link;

	return true;
}


/**
 * Register & Hook our function to
 * the 'misc/Portal.php|portal_alerts' action tag.
 *
 * List of available actions:
 * @see functions/Actions.php
 */
add_action( 'misc/Portal.php|portal_alerts', 'ContentSecurityPolicyPortalAlerts' );
