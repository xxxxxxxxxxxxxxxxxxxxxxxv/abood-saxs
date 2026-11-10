<?php
/**
 * Add / Drop Breakdown over Time
 *
 * @package RosarioSIS
 * @subpackage Students
 */

require_once 'ProgramFunctions/Charts.fnc.php';

$_REQUEST['event_type'] = issetVal( $_REQUEST['event_type'], '' );

if ( ! in_array( $_REQUEST['event_type'], [ '', 'enrolled', 'dropped' ] ) )
{
	$_REQUEST['event_type'] = '';
}

$_REQUEST['all_schools'] = issetVal( $_REQUEST['all_schools'], '' );

$min_date = DBGetOne( "SELECT min(SCHOOL_DATE) AS MIN_DATE
	FROM attendance_calendar
	WHERE SYEAR='" . UserSyear() . "'
	AND SCHOOL_ID='" . UserSchool() . "'" );

if ( ! $min_date )
{
	$min_date = date( 'Y-m' ) . '-01';
}

// Set start date.
$start_date = RequestedDate( 'start', $min_date, 'set' );

// Set end date.
$end_date = RequestedDate( 'end', DBDate(), 'set' );

$chart_types = [ 'bar', 'pie', 'list' ];

// Set Chart Type.
if ( ! isset( $_REQUEST['chart_type'] )
	|| ! in_array( $_REQUEST['chart_type'], $chart_types ) )
{
	$_REQUEST['chart_type'] = 'bar';
}

$start_month = mb_substr( $start_date, 0, 7 );

$end_month = mb_substr( $end_date, 0, 7 );

$month_options = [];

for ( $i = $start_month; $i <= $end_month; $i = date( 'Y-m', strtotime( '+1 month', strtotime( $i . '-01' ) ) ) )
{
	$month_options[ $i ] = strftime_compat( '%b %y', $i . '-01' );
}

$schools_where_sql = " AND ssm.SCHOOL_ID='" . UserSchool() . "'";

if ( $_REQUEST['all_schools'] )
{
	// Search All Schools.
	$schools_where_sql = "";

	if ( User( 'SCHOOLS' ) )
	{
		$schools_where_sql = " AND ssm.SCHOOL_ID IN (" . mb_substr( str_replace( ',', "','", User( 'SCHOOLS' ) ), 2, -2 ) . ") ";
	}
}

if ( $_REQUEST['event_type'] )
{
	$event_column = $_REQUEST['event_type'] === 'enrolled' ? 'ssm.START_DATE' : 'ssm.END_DATE';

	// Note: cannot use GetStuList() here as we need to sum enrolled / dropped for EVERY school year!
	$totals_RET = DBGet( "SELECT CAST(" . $event_column . " AS char(7)) AS EVENT_MONTH,COUNT(*) AS COUNT
		FROM students s
		JOIN student_enrollment ssm ON (ssm.STUDENT_ID=s.STUDENT_ID" . $schools_where_sql . ")
		WHERE " . $event_column . " BETWEEN '" . $start_date . "' AND '" . $end_date . "'
		GROUP BY EVENT_MONTH
		LIMIT 1000", [], [ 'EVENT_MONTH' ] );

	foreach ( (array) $month_options as $month => $fmonth )
	{
		$chart['chart_data'][0][] = $fmonth;

		$chart['chart_data'][1][] = ( empty( $totals_RET[ $month ][1]['COUNT'] ) ? 0 : $totals_RET[ $month ][1]['COUNT'] );
	}
}
else // Enrolled / Dropped
{
	$event_column = 'ssm.START_DATE';

	// Note: cannot use GetStuList() here as we need to sum enrolled / dropped for EVERY school year!
	$totals_start_RET = DBGet( "SELECT CAST(" . $event_column . " AS char(7)) AS EVENT_MONTH,COUNT(*) AS COUNT
		FROM students s
		JOIN student_enrollment ssm ON (ssm.STUDENT_ID=s.STUDENT_ID" . $schools_where_sql . ")
		WHERE " . $event_column . " BETWEEN '" . $start_date . "' AND '" . $end_date . "'
		GROUP BY EVENT_MONTH
		LIMIT 1000", [], [ 'EVENT_MONTH' ] );

	$event_column = 'ssm.END_DATE';

	$totals_end_RET = DBGet( "SELECT CAST(" . $event_column . " AS char(7)) AS EVENT_MONTH,COUNT(*) AS COUNT
		FROM students s
		JOIN student_enrollment ssm ON (ssm.STUDENT_ID=s.STUDENT_ID" . $schools_where_sql . ")
		WHERE " . $event_column . " BETWEEN '" . $start_date . "' AND '" . $end_date . "'
		GROUP BY EVENT_MONTH
		LIMIT 1000", [], [ 'EVENT_MONTH' ] );

	$chart['chart_data'][0][0] = '';

	foreach ( (array) $month_options as $month => $fmonth )
	{
		$chart['chart_data'][0][] = $fmonth;
	}

	$events = [ 1 => _( 'Enrolled' ), 2 => _( 'Dropped' ) ];

	foreach ( $events as $index => $event )
	{
		$chart['chart_data'][ $index ][0] = $event;

		foreach ( (array) $month_options as $month => $fmonth )
		{
			if ( $index === 1 )
			{
				$chart['chart_data'][ $index ][] = ( empty( $totals_start_RET[ $month ][1]['COUNT'] ) ?
					0 : $totals_start_RET[ $month ][1]['COUNT'] );
			}

			if ( $index === 2 )
			{
				$chart['chart_data'][ $index ][] = ( empty( $totals_end_RET[ $month ][1]['COUNT'] ) ?
					0 : $totals_end_RET[ $month ][1]['COUNT'] );
			}
		}
	}

	if ( $_REQUEST['chart_type'] !== 'list' )
	{
		$datacolumns = 0;
		$ticks = [];

		foreach ( (array) $chart['chart_data'] as $chart_data )
		{
			// Ticks
			if ( $datacolumns++ == 0 )
			{
				$jump = true;

				foreach ( $chart_data as $tick )
				{
					if ( $jump )
					{
						$jump = false;
					}
					else
					{
						$ticks[] = $tick;
					}
				}
			}
			else
			{
				$series = true;

				foreach ( (array) $chart_data as $i => $data )
				{
					if ( $series )
					{
						$series = false;

						$series_label = $data;

						// Set series label + ticks
						$chart_data_series[ $series_label ][0] = $ticks;

						$chart_data_series[ $series_label ][1] = [];
					}
					else
					{
						$chart_data_series[ $series_label ][1][] = $data;
					}
				}
			}
		}
	}
}

if ( ! $_REQUEST['modfunc'] )
{
	echo '<form action="' . PreparePHP_SELF( [], [
		'month_start',
		'day_start',
		'year_start',
		'month_end',
		'day_end',
		'year_end',
	] ) . '" method="GET">';

	AllowEditTemporary( 'start' );

	$select_options = [
		'' => _( 'Enrolled' ) . ' / ' . _( 'Dropped' ),
		'enrolled' => _( 'Enrolled' ),
		'dropped' => _( 'Dropped' ),
	];

	$select = SelectInput(
		$_REQUEST['event_type'],
		'event_type',
		'<span class="a11y-hidden">' . _( 'Event' ) . '</span>',
		$select_options,
		false,
		// @since 12.5 CSP remove unsafe-inline Javascript
		'autocomplete="off" class="onchange-ajax-post-form"',
		false
	);

	AllowEditTemporary( 'stop' );

	$all_schools = '';

	if ( SchoolInfo( 'SCHOOLS_NB' ) > 1
		&& ( ! trim( (string) User( 'SCHOOLS' ), ',' )
			|| mb_substr_count( User( 'SCHOOLS' ), ',' ) > 2 ) )
	{
		$all_schools = CheckBoxOnclick(
			'all_schools',
			_( 'All Schools' )
		);
	}

	DrawHeader( $select, $all_schools );

	DrawHeader(
		_( 'Report Timeframe' ) . ': ' .
			PrepareDate( $start_date, '_start', false ) . ' &nbsp; ' . _( 'to' ) . ' &nbsp; ' .
			PrepareDate( $end_date, '_end', false ) . ' ' .
			SubmitButton( _( 'Go' ) )
	);

	if ( $end_date < $start_date )
	{
		// Fix PHP warning: fatal error when end date < start date
		$error[] = _( 'Start date must be anterior to end date.' );

		echo ErrorMessage( $error, 'fatal' );
	}

	echo '<br />';

	if ( ! $_REQUEST['event_type'] ) // Enrolled / Dropped
	{
		// Force Chart Type to bar if pie.
		if ( $_REQUEST['chart_type'] === 'pie' )
		{
			$_REQUEST['chart_type'] = 'bar';
		}

		$tabs = [
			[
				'title' => _( 'Column' ),
				'link' => PreparePHP_SELF( $_REQUEST, [], [ 'chart_type' => 'bar' ] ),
			],
			[
				'title' => _( 'List' ),
				'link' => PreparePHP_SELF( $_REQUEST, [], [ 'chart_type' => 'list' ] ),
			]
		];
	}
	else
	{
		$tabs = [
			[
				'title' => _( 'Column' ),
				'link' => PreparePHP_SELF( $_REQUEST, [], [ 'chart_type' => 'bar' ] ),
			],
			[
				'title' => _( 'Pie' ),
				'link' => PreparePHP_SELF( $_REQUEST, [], [ 'chart_type' => 'pie' ] ),
			],
			[
				'title' => _( 'List' ),
				'link' => PreparePHP_SELF( $_REQUEST, [], [ 'chart_type' => 'list' ] ),
			]
		];
	}

	$_ROSARIO['selected_tab'] = PreparePHP_SELF( $_REQUEST );

	PopTable( 'header', $tabs );

	if ( $_REQUEST['chart_type'] === 'list' )
	{
		$chart_data = [ '0' => '' ];

		if ( ! $_REQUEST['event_type'] ) // Enrolled / Dropped
		{
			$LO_columns = [ 'TITLE' => _( 'Month' ) ];

			foreach ( (array) $chart['chart_data'] as $event => $values )
			{
				if ( $event != 0 )
				{
					$LO_columns[ $event ] = $values[0];

					unset( $values[0] );

					$total_count = 0;

					foreach ( (array) $values as $key => $value )
					{
						$chart_data[ $key ][ $event ] = $value;

						$total_count += $value;
					}

					$chart_data[ ++$key ][ $event ] = $total_count;
				}
				else
				{
					unset( $values[0] );

					foreach ( (array) $values as $key => $value )
					{
						$chart_data[ $key ]['TITLE'] = $value;
					}

					$chart_data[ ++$key ] = [ 'TITLE' => _( 'Total' ) ];
				}
			}
		}
		else
		{
			$total_count = 0;

			foreach ( (array) $chart['chart_data'][1] as $key => $value )
			{
				$chart_data[] = [ 'TITLE' => $chart['chart_data'][0][ $key ], 'VALUE' => $value ];

				$total_count += $value;
			}

			$chart_data[] = [ 'TITLE' => _( 'Total' ), 'VALUE' => $total_count ];

			$LO_columns = [
				'TITLE' => _( 'Month' ),
				'VALUE' => $select_options[ $_REQUEST['event_type'] ],
			];
		}

		unset( $chart_data[0] );

		$LO_options['responsive'] = false;

		ListOutput( $chart_data, $LO_columns, 'Month', 'Months', [], [], $LO_options );
	}
	// Chart.js charts.
	else
	{
		$chart_title = sprintf( _( '%s Breakdown' ), $select_options[ $_REQUEST['event_type'] ] );

		if ( $_REQUEST['chart_type'] === 'pie' )
		{
			foreach ( (array) $chart['chart_data'][0] as $index => $label )
			{
				if ( ! is_numeric( $chart['chart_data'][1][ $index ] ) )
				{
					continue;
				}

				// Limit label to 30 char max.
				$chart['chart_data'][0][ $index ] = mb_substr( $label, 0, 30 );
			}
		}

		echo ChartjsChart(
			$_REQUEST['chart_type'],
			! empty( $chart_data_series ) ? $chart_data_series : $chart['chart_data'],
			$chart_title
		);
	}

	PopTable( 'footer' );

	echo '</form>';
}
