<?php

DrawHeader( ProgramTitle() );

$_REQUEST['cumulative_balance'] = issetVal( $_REQUEST['cumulative_balance'] );

// @since 12.9 SQL performance: replace subqueries with LEFT JOINs
$extra['SELECT'] = ',COALESCE(f.TOTAL,0)-COALESCE(p.TOTAL,0) AS BALANCE';

$extra['FROM'] = ' LEFT JOIN (SELECT STUDENT_ID,SYEAR,SUM(AMOUNT) AS TOTAL
	FROM billing_fees
	GROUP BY STUDENT_ID,SYEAR) f ON f.STUDENT_ID=ssm.STUDENT_ID
	AND f.SYEAR=ssm.SYEAR
LEFT JOIN (SELECT STUDENT_ID,SYEAR,SUM(AMOUNT) AS TOTAL
	FROM billing_payments
	GROUP BY STUDENT_ID,SYEAR) p ON p.STUDENT_ID=ssm.STUDENT_ID
	AND p.SYEAR=ssm.SYEAR';

if ( $_REQUEST['cumulative_balance'] === 'Y' )
{
	// @since 10.3 Add "Cumulative Balance over school years" checkbox.
	$extra['FROM'] = ' LEFT JOIN (SELECT STUDENT_ID,SUM(AMOUNT) AS TOTAL
		FROM billing_fees
		GROUP BY STUDENT_ID) f ON f.STUDENT_ID=ssm.STUDENT_ID
	LEFT JOIN (SELECT STUDENT_ID,SUM(AMOUNT) AS TOTAL
		FROM billing_payments
		GROUP BY STUDENT_ID) p ON p.STUDENT_ID=ssm.STUDENT_ID';
}

$extra['columns_after'] = [ 'BALANCE' => _( 'Balance' ) ];

$extra['link']['FULL_NAME'] = false;
$extra['new'] = true;
$extra['functions'] = [ 'BALANCE' => '_makeCurrency' ];

Widgets( 'balance' );

if ( User( 'PROFILE' ) === 'parent' || User( 'PROFILE' ) === 'student' )
{
	$_REQUEST['search_modfunc'] = 'list';
}

// Fix SQL error table name "sam" specified more than once
$extra2 = $extra;

if ( $_REQUEST['search_modfunc'] === 'list' )
{
	// @since 10.3 Add "Cumulative Balance over school years" checkbox.
	DrawHeader( CheckBoxOnclick( 'cumulative_balance', _( 'Cumulative Balance over school years' ) ) );

	// Call GetStuList() only so we calculate the $total.
	GetStuList( $extra );

	// Fix Search terms displayed twice
	$_ROSARIO['SearchTerms'] = '';
}

// @since 9.0 Add Total sum of balances.
$extra2['link']['add']['html'] = [
	'FULL_NAME' => '<b>' . _( 'Total' ) . '</b>',
	'BALANCE' => '<b>' . Currency( ( isset( $total ) ? $total * -1 : 0 ) ) . '</b>',
];

Search( 'student_id', $extra2 );

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
