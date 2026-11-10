<?php
DrawHeader( ProgramTitle() );

// @since 12.9 SQL performance: replace subqueries with LEFT JOINs
// SQL remove SCHOOL_ID join: fix when "Search All Schools" is checked
$extra['SELECT'] = ',COALESCE(f.TOTAL,0)-COALESCE(p.TOTAL,0) AS BALANCE';

$extra['FROM'] = ' LEFT JOIN (SELECT STAFF_ID,SYEAR,SUM(AMOUNT) AS TOTAL
	FROM accounting_salaries
	GROUP BY STAFF_ID,SYEAR) f ON f.STAFF_ID=s.STAFF_ID
	AND f.SYEAR=s.SYEAR
LEFT JOIN (SELECT STAFF_ID,SYEAR,SUM(AMOUNT) AS TOTAL
	FROM accounting_payments
	GROUP BY STAFF_ID,SYEAR) p ON p.STAFF_ID=s.STAFF_ID
	AND p.SYEAR=s.SYEAR';

$extra['columns_after'] = [ 'BALANCE' => _( 'Balance' ) ];

$extra['link']['FULL_NAME'] = false;
$extra['new'] = true;
$extra['functions'] = [ 'BALANCE' => '_makeCurrency' ];

StaffWidgets( 'staff_balance' );

if ( User( 'PROFILE' ) === 'parent' || User( 'PROFILE' ) === 'teacher' )
{
	$_REQUEST['search_modfunc'] = 'list';
}

// Fix SQL error table name "sam" specified more than once
$extra2 = $extra;

if ( $_REQUEST['search_modfunc'] === 'list' )
{
	// Call GetStaffList() only so we calculate the $total.
	GetStaffList( $extra );
}

// @since 10.0 Add Total sum of balances.
$extra2['link']['add']['html'] = [
	'FULL_NAME' => '<b>' . _( 'Total' ) . '</b>',
	'BALANCE' => '<b>' . Currency( ( isset( $total ) ? $total * -1 : 0 ) ) . '</b>',
];

Search( 'staff_id', $extra2 );

/**
 * @param $value
 * @param $column
 */
function _makeCurrency( $value, $column )
{
	global $total;

	if ( ! isset( $total ) )
	{
		$total = 0;
	}

	$total += (float) $value;

	return Currency( $value * -1 );
}
