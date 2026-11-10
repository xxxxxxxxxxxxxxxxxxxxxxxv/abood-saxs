<?php
/**
 * Daily Transactions
 *
 * @since 8.0 Merge Daily Transactions & Daily Totals programs
 *
 * @package RosarioSIS
 * @subpackage modules
 */

DrawHeader( ProgramTitle() );

$_REQUEST['program'] = issetVal( $_REQUEST['program'], '' );

if ( $_REQUEST['program'] === 'totals'
	&& User( 'PROFILE' ) === 'admin' )
{
	require_once 'modules/Accounting/includes/DailyTotals.php';
}
else
{
	require_once 'modules/Accounting/includes/DailyTransactions.php';
}


/**
 * Program Menu
 *
 * Local function
 *
 * @since 8.0
 * @since 10.9 Temporary AllowEdit so SelectInput() is displayed to everyone
 *
 * @param  string $program Program: transactions|totals.
 *
 * @return string           Select Program input.
 */
function _programMenu( $program )
{
	AllowEditTemporary( 'start' );

	$link = PreparePHP_SELF(
		[],
		[ 'program' ]
	) . '&program=';

	$menu = SelectInput(
		$program,
		'program',
		'',
		[
			'transactions' => _( 'Daily Transactions' ),
			'totals' => _( 'Daily Totals' ),
		],
		false,
		// @since RosarioSIS 12.5 CSP remove unsafe-inline Javascript
		// Note: `this.value` inside link is automatically replaced
		'class="onchange-ajax-link" data-link="' . $link . 'this.value" autocomplete="off"',
		false
	);

	AllowEditTemporary( 'stop' );

	return $menu;
}
