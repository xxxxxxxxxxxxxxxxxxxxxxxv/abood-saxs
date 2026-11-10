<?php

require_once 'ProgramFunctions/_makeLetterGrade.fnc.php';
require_once 'ProgramFunctions/Charts.fnc.php';
require_once 'modules/Grades/includes/FinalGrades.inc.php';

if ( ! empty( $_SESSION['is_secondary_teacher'] ) )
{
	// @since 6.9 Add Secondary Teacher: set User to main teacher.
	UserImpersonateTeacher();
}

DrawHeader( ProgramTitle() );

// Set Assignment ID

if ( ! isset( $_REQUEST['assignment_id'] )
	|| empty( $_REQUEST['assignment_id'] ) )
{
	$_REQUEST['assignment_id'] = 'totals';
}

$chart_types = [ 'line', 'pie', 'list' ];

// Set Chart Type.
if ( ! isset( $_REQUEST['chart_type'] )
	|| ! in_array( $_REQUEST['chart_type'], $chart_types ) )
{
	$_REQUEST['chart_type'] = 'line';
}

//FJ fix errors relation «course_weights» doesnt exist & columns c.grad_subject_id & cp.does_grades & cp.does_gpa do not exist
//$course_id = DBGet( "SELECT c.GRAD_SUBJECT_ID,cp.COURSE_ID,cp.TITLE,c.TITLE AS COURSE_TITLE,c.SHORT_NAME AS COURSE_NUM,cw.CREDITS,cw.GPA_MULTIPLIER,cp.DOES_GRADES,cp.GRADE_SCALE_ID,cp.DOES_GPA as AFFECTS_GPA FROM course_periods cp,courses c,COURSE_WEIGHTS cw WHERE cw.COURSE_ID=cp.COURSE_ID AND cw.COURSE_WEIGHT=cp.COURSE_WEIGHT AND c.COURSE_ID=cp.COURSE_ID AND cp.COURSE_PERIOD_ID='".UserCoursePeriod()."'" );
$course_id = DBGet( "SELECT cp.COURSE_ID,cp.GRADE_SCALE_ID
	FROM course_periods cp,courses c
	WHERE c.COURSE_ID=cp.COURSE_ID
	AND cp.COURSE_PERIOD_ID='" . UserCoursePeriod() . "'" );

if ( ! isset( $course_id[1]['GRADE_SCALE_ID'] ) )
{
	ErrorMessage( [ _( 'This course is not graded.' ) ], 'fatal' );
}

$grade_scale_id = $course_id[1]['GRADE_SCALE_ID'];

$course_id = $course_id[1]['COURSE_ID'];

//FJ fix error column scale_id doesnt exist
//$grades_RET = DBGet( "SELECT ID,TITLE FROM report_card_grades WHERE SCALE_ID='".$grade_scale_id."' AND SYEAR='".UserSyear()."' AND SCHOOL_ID='".UserSchool()."' ORDER BY SORT_ORDER IS NULL,SORT_ORDER" );
$grades_RET = DBGet( "SELECT ID,TITLE,GPA_VALUE
	FROM report_card_grades
	WHERE GRADE_SCALE_ID='" . (int) $grade_scale_id . "'
	AND SYEAR='" . UserSyear() . "'
	AND SCHOOL_ID='" . UserSchool() . "'
	ORDER BY BREAK_OFF IS NULL,BREAK_OFF DESC,SORT_ORDER IS NULL,SORT_ORDER" );

$grades = [];

foreach ( (array) $grades_RET as $grade )
{
	$grades[] = [ 'TITLE' => $grade['TITLE'], 'GPA_VALUE' => $grade['GPA_VALUE'] ];
}

// Fix SQL Get all assignment types having assignments for this course period
$types_RET = DBGet( "SELECT ASSIGNMENT_TYPE_ID,TITLE
	FROM gradebook_assignment_types gt
	WHERE (STAFF_ID='" . User( 'STAFF_ID' ) . "'
		OR EXISTS(SELECT 1 FROM gradebook_assignments
			WHERE STAFF_ID='" . User( 'STAFF_ID' ) . "'
			AND (COURSE_ID='" . (int) $course_id . "' OR COURSE_PERIOD_ID='" . UserCoursePeriod() . "')
			AND ASSIGNMENT_TYPE_ID=gt.ASSIGNMENT_TYPE_ID
			AND MARKING_PERIOD_ID='" . UserMP() . "'))
	AND COURSE_ID='" . (int) $course_id . "'
	ORDER BY TITLE" );

$assignments_RET = DBGet( "SELECT ASSIGNMENT_ID,TITLE,POINTS
	FROM gradebook_assignments
	WHERE STAFF_ID='" . User( 'STAFF_ID' ) . "'
	AND (COURSE_ID='" . (int) $course_id . "' OR COURSE_PERIOD_ID='" . UserCoursePeriod() . "')
	AND MARKING_PERIOD_ID='" . UserMP() . "'
	ORDER BY " . DBEscapeIdentifier( Preferences( 'ASSIGNMENT_SORTING', 'Gradebook' ) ) . " DESC" );

// @since 12.5 CSP remove unsafe-inline Javascript
$assignment_select = '<select name="assignment_id" id="assignment_id" class="onchange-ajax-post-form">';

$assignment_select .= '<option value="totals"' . ( $_REQUEST['assignment_id'] === 'totals' ? ' selected' : '' ) . '>' .
_( 'Totals' ) .
	'</option>';

// Assignment Types.

foreach ( (array) $types_RET as $type )
{
	$selected = '';

	if ( $_REQUEST['assignment_id'] === ( 'totals' . $type['ASSIGNMENT_TYPE_ID'] ) )
	{
		// @since 10.5.2 Truncate Assignment title to 36 chars
		$title = mb_strlen( $type['TITLE'] ) <= 36 ?
			$type['TITLE'] :
			mb_substr( $type['TITLE'], 0, 33 ) . '...';

		$selected = ' selected';
	}

	$assignment_select .= '<option value="totals' . $type['ASSIGNMENT_TYPE_ID'] . '"' . $selected . '>' .
		$type['TITLE'] .
		'</option>';
}

// Assignments.

foreach ( (array) $assignments_RET as $assignment )
{
	$selected = '';

	if ( $_REQUEST['assignment_id'] === $assignment['ASSIGNMENT_ID'] )
	{
		// @since 10.5.2 Truncate Assignment title to 36 chars
		$title = mb_strlen( $assignment['TITLE'] ) <= 36 ?
			$assignment['TITLE'] :
			mb_substr( $assignment['TITLE'], 0, 33 ) . '...';

		$selected = ' selected';
	}

	$assignment_select .= '<option value="' . AttrEscape( $assignment['ASSIGNMENT_ID'] ) . '"' . $selected . '>' .
		$assignment['TITLE'] .
		'</option>';
}

$assignment_select .= '</select>
	<label for="assignment_id" class="a11y-hidden">' . _( 'Assignments' ) . '</label>';

$extra['SELECT_ONLY'] = issetVal( $extra['SELECT_ONLY'], '' );
$extra['SELECT_ONLY'] .= "ssm.STUDENT_ID,'' AS LETTER_GRADE";

$extra['functions'] = [ 'LETTER_GRADE' => '_makeGrade' ];

// Totals.
if ( $_REQUEST['assignment_id'] === 'totals' )
{
	$title = _( 'Grade' );

	// @since 12.2 Use FinalGradesQtrOrProCalculate() function
	// @global variable used by _makeGrade()
	$import_RET = FinalGradesQtrOrProCalculate( UserCoursePeriod(), UserMP() );

	foreach ( (array) $assignments_RET as $assignment )
	{
		$total_points[$assignment['ASSIGNMENT_ID']] = $assignment['POINTS'];
	}
}

// Assignment Type.
elseif ( ! is_numeric( $_REQUEST['assignment_id'] ) )
{
	$type_id = mb_substr( $_REQUEST['assignment_id'], 6 );

	// @since 12.2 Use FinalGradesQtrOrProCalculate() function
	// @global variable used by _makeGrade()
	$import_RET = FinalGradesQtrOrProCalculate( UserCoursePeriod(), UserMP(), $type_id );

	foreach ( (array) $assignments_RET as $assignment )
	{
		$total_points[$assignment['ASSIGNMENT_ID']] = $assignment['POINTS'];
	}
}

// Assignment.
elseif ( ! empty( $_REQUEST['assignment_id'] ) )
{
	$total_points = DBGetOne( "SELECT POINTS
		FROM gradebook_assignments
		WHERE ASSIGNMENT_ID='" . (int) $_REQUEST['assignment_id'] . "'" );

	$current_RET = DBGet( "SELECT STUDENT_ID,POINTS,COMMENT,ASSIGNMENT_ID
		FROM gradebook_grades
		WHERE ASSIGNMENT_ID='" . (int) $_REQUEST['assignment_id'] . "'
		AND COURSE_PERIOD_ID='" . UserCoursePeriod() . "'",
		[],
		[ 'STUDENT_ID', 'ASSIGNMENT_ID' ] );
}

$stu_RET = GetStuList( $extra );

$RET = [];

foreach ( (array) $stu_RET as $stu )
{
	$RET[$stu['LETTER_GRADE']] = isset( $RET[$stu['LETTER_GRADE']] ) ? ++$RET[$stu['LETTER_GRADE']] : 1;
}

$chart['chart_data'][1] = [];

foreach ( (array) $grades as $option )
{
	$chart['chart_data'][0][] = $option['GPA_VALUE'];

	$chart['chart_data'][1][] = ( empty( $RET[$option['TITLE']] ) ? 0 : $RET[$option['TITLE']] );

	// Add Grade Title (only to Pie Chart & List).
	$chart['chart_data'][2][] = $option['TITLE'];
}

if ( ! $_REQUEST['modfunc'] )
{
	echo '<form action="' . PreparePHP_SELF( $_REQUEST, [ 'assignment_id' ] ) . '" method="GET">';

	$RET = GetStuList();

	if ( empty( $RET ) )
	{
		echo ErrorMessage( [ _( 'No Students were found.' ) ], 'fatal' );
	}

	DrawHeader( $assignment_select, SubmitButton( _( 'Go' ) ) );

	echo '<br />';

	if ( ! empty( $_REQUEST['assignment_id'] ) )
	{
		$tabs = [
			[
				'title' => _( 'Line' ),
				'link' => PreparePHP_SELF( $_REQUEST, [], [ 'chart_type' => 'line' ] ),
			],
			[
				'title' => _( 'Pie' ),
				'link' => PreparePHP_SELF( $_REQUEST, [], [ 'chart_type' => 'pie' ] ),
			],
			[
				'title' => _( 'List' ),
				'link' => PreparePHP_SELF( $_REQUEST, [], [ 'chart_type' => 'list' ] ),
			],
		];

		$_ROSARIO['selected_tab'] = PreparePHP_SELF( $_REQUEST );

		PopTable( 'header', $tabs );

//var_dump($chart['chart_data']);

		// List.
		if ( $_REQUEST['chart_type'] === 'list' )
		{
			$chart_data = [ '0' => '' ];

			foreach ( (array) $chart['chart_data'][1] as $key => $y )
			{
				$chart_data[] = [
					'TITLE' => $chart['chart_data'][2][$key],
					'GPA' => $chart['chart_data'][0][$key],
					'VALUE' => $y,
				];
			}

			unset( $chart_data[0] );

			$LO_options['responsive'] = false;

			$LO_columns = [
				'TITLE' => _( 'Title' ),
				'GPA' => _( 'GPA Value' ),
				'VALUE' => _( 'Number of Students' ),
			];

			ListOutput( $chart_data, $LO_columns, 'Grade', 'Grades', [], [], $LO_options );
		}

		// Chart.js charts.
		else
		{
			$chart_title = sprintf( _( '%s Breakdown' ), $title );

			if ( $_REQUEST['chart_type'] === 'pie' )
			{
				foreach ( (array) $chart['chart_data'][0] as $i => $x )
				{
					if ( $chart['chart_data'][1][$i] == 0 )
					{
						// Remove empty slices not to overload the legends.
						unset(
							$chart['chart_data'][0][$i],
							$chart['chart_data'][1][$i]
						);

						continue;
					}

					$chart['chart_data'][0][$i] = $chart['chart_data'][2][$i] . ', ' . $x;
				}
			}

			echo ChartjsChart(
				$_REQUEST['chart_type'],
				$chart['chart_data'],
				$chart_title
			);
		}

		PopTable( 'footer' );
	}

	echo '</form>';
}

/**
 * Make Letter Grade
 *
 * Local function
 *
 * @param  string $value  ''
 * @param  string $column 'LETTER_GRADE'
 * @return string Letter Grade
 */
function _makeGrade( $value, $column )
{
	global $THIS_RET,
		$total_points,
		$current_RET,
		$import_RET;

	// Totals or Assignment Type

	if ( ! is_numeric( $_REQUEST['assignment_id'] )
		&& empty( $_REQUEST['student_id'] ) )
	{
		return issetVal( $import_RET[$THIS_RET['STUDENT_ID']][1]['GRADE_LETTER'] );
	}

	// Assignment
	else
	{
		// Not Excused, Not Extra Credit.
		$current_RET[$THIS_RET['STUDENT_ID']][$_REQUEST['assignment_id']][1]['POINTS'] = issetVal( $current_RET[$THIS_RET['STUDENT_ID']][$_REQUEST['assignment_id']][1]['POINTS'] );

		if ( $current_RET[$THIS_RET['STUDENT_ID']][$_REQUEST['assignment_id']][1]['POINTS'] !== '*'
			&& $total_points )
		{
			return _makeLetterGrade(
				$current_RET[$THIS_RET['STUDENT_ID']][$_REQUEST['assignment_id']][1]['POINTS'] / $total_points,
				UserCoursePeriod()
			);
		}

		return _( 'N/A' );
	}
}
