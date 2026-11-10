<?php
/**
 * Merge Requests Report & Unfilled Requests
 *
 * @package RosarioSIS
 * @subpackage Scheduling
 */

DrawHeader( ProgramTitle() );

$_REQUEST['report'] = issetVal( $_REQUEST['report'], '' );

$report_link = PreparePHP_SELF(
	[],
	[ 'report', 'search_modfunc', 'include_seats', 'expanded_view', 'address_group' ]
) . '&report=';

$report_select = SelectInput(
	$_REQUEST['report'],
	'report',
	'',
	[
		'' => _( 'Requests Report' ),
		'unfilled' => _( 'Unfilled Requests' ),
	],
	false,
	// @since RosarioSIS 12.5 CSP remove unsafe-inline Javascript
	// Note: `this.value` inside link is automatically replaced
	'class="onchange-ajax-link" data-link="' . $report_link . 'this.value" autocomplete="off"',
	false
);

DrawHeader( $report_select );

if ( $_REQUEST['report'] === 'unfilled' )
{
	require_once 'modules/Scheduling/includes/UnfilledRequests.php';
}
else
{
	require_once 'modules/Scheduling/includes/RequestsReport.php';
}
