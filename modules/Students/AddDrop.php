<?php
/**
 * Merge Add / Drop Report & Add / Drop Breakdown over Time
 *
 * @package RosarioSIS
 * @subpackage Students
 */

DrawHeader( ProgramTitle() );

$_REQUEST['report'] = issetVal( $_REQUEST['report'], '' );

$report_link = PreparePHP_SELF(
	[],
	[ 'report', 'chart_type', 'event_type' ]
) . '&report=';

// Temporary AllowEdit for non admin users for SelectInput display.
AllowEditTemporary( 'start' );

$report_select = SelectInput(
	$_REQUEST['report'],
	'report',
	'',
	[
		'' => _( 'Add / Drop Report' ),
		'breakdown' => _( 'Add / Drop Breakdown over Time' ),
	],
	false,
	// @since 12.5 CSP remove unsafe-inline Javascript
	// Note: `this.value` inside link is automatically replaced
	'class="onchange-ajax-link" data-link="' . $report_link . 'this.value" autocomplete="off"',
	false
);

AllowEditTemporary( 'stop' );

DrawHeader( $report_select );

if ( $_REQUEST['report'] === 'breakdown' )
{
	require_once 'modules/Students/includes/AddDropBreakdownTime.php';
}
else
{
	require_once 'modules/Students/includes/AddDrop.php';
}
