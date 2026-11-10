<?php
/**
 * Merge Attendance Chart & Absence Summary
 *
 * @package RosarioSIS
 * @subpackage Attendance
 */

DrawHeader( ProgramTitle() );

$_REQUEST['report'] = issetVal( $_REQUEST['report'], '' );

$report_link = PreparePHP_SELF(
	[],
	[ 'report', 'attendance' ]
) . '&report=';

// Temporary AllowEdit for non admin users for SelectInput display.
AllowEditTemporary( 'start' );

$report_select = SelectInput(
	$_REQUEST['report'],
	'report',
	'',
	[
		'' => ( User( 'PROFILE' ) === 'admin' || User( 'PROFILE' ) === 'teacher' ?
			_( 'Attendance Chart' ) :
			_( 'Daily Summary' ) ),
		'absence' => _( 'Absence Summary' ),
	],
	false,
	// @since RosarioSIS 12.5 CSP remove unsafe-inline Javascript
	// Note: `this.value` inside link is automatically replaced
	'class="onchange-ajax-link" data-link="' . $report_link . 'this.value" autocomplete="off"',
	false
);

AllowEditTemporary( 'stop' );

DrawHeader( $report_select );

if ( $_REQUEST['report'] === 'absence' )
{
	require_once 'modules/Attendance/includes/StudentSummary.php';
}
else
{
	require_once 'modules/Attendance/includes/DailySummary.php';
}
