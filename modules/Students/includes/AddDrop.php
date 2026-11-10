<?php
/**
 * Add / Drop Report
 *
 * @package RosarioSIS
 * @subpackage Students
 */

require_once 'ProgramFunctions/TipMessage.fnc.php';

// Set start date.
$start_date = RequestedDate( 'start', date( 'Y-m' ) . '-01' );

// Set end date.
$end_date = RequestedDate( 'end', DBDate() );

echo '<form action="' . PreparePHP_SELF( [], [
	'month_start',
	'day_start',
	'year_start',
	'month_end',
	'day_end',
	'year_end',
] ) . '" method="GET">';

DrawHeader(
	_( 'Timeframe' ) . ': ' . PrepareDate( $start_date, '_start', false ) . ' ' .
	_( 'to' ) . ' ' . PrepareDate( $end_date, '_end', false ) . ' ' .
	SubmitButton( _( 'Go' ) ) );

echo '</form>';

$schools_where_sql = '';

// Search All Schools.
if ( User( 'SCHOOLS' ) )
{
	$schools_where_sql = " AND se.SCHOOL_ID IN (" . mb_substr( str_replace( ',', "','", User( 'SCHOOLS' ) ), 2, -2 ) . ") ";
}

$enrollment_sql = "SELECT se.START_DATE AS START_DATE,NULL AS END_DATE,se.START_DATE AS DATE,
	se.STUDENT_ID," . DisplayNameSQL( 's' ) . " AS FULL_NAME,sch.TITLE,se.SCHOOL_ID
	FROM student_enrollment se,students s,schools sch
	WHERE s.STUDENT_ID=se.STUDENT_ID
	AND se.START_DATE BETWEEN '" . $start_date . "' AND '" . $end_date . "'
	AND sch.ID=se.SCHOOL_ID" . $schools_where_sql . "
	AND se.SYEAR='" . UserSyear() . "'
	AND se.SYEAR=sch.SYEAR
	UNION
	SELECT NULL AS START_DATE,se.END_DATE AS END_DATE,se.END_DATE AS DATE,
	se.STUDENT_ID," . DisplayNameSQL( 's' ) . " AS FULL_NAME,sch.TITLE,se.SCHOOL_ID
	FROM student_enrollment se,students s,schools sch
	WHERE s.STUDENT_ID=se.STUDENT_ID
	AND se.END_DATE BETWEEN '" . $start_date . "' AND '" . $end_date . "'
	AND sch.ID=se.SCHOOL_ID" . $schools_where_sql . "
	AND se.SYEAR='" . UserSyear() . "'
	AND se.SYEAR=sch.SYEAR
	ORDER BY DATE DESC";

$sql_count = "SELECT SUM(CASE WHEN se.START_DATE BETWEEN '" . $start_date . "' AND '" . $end_date . "' THEN 1 ELSE 0 END) +
	SUM(CASE WHEN se.END_DATE BETWEEN '" . $start_date . "' AND '" . $end_date . "' THEN 1 ELSE 0 END)
	FROM student_enrollment se,students s,schools sch
	WHERE s.STUDENT_ID=se.STUDENT_ID
	AND sch.ID=se.SCHOOL_ID" . $schools_where_sql . "
	AND se.SYEAR='" . UserSyear() . "'
	AND se.SYEAR=sch.SYEAR";

$enrollment_sql .= SQLLimitForList( $sql_count );

$enrollment_RET = DBGet( $enrollment_sql, [
	'FULL_NAME' => '_makeStudentInfoLink',
	'START_DATE' => 'ProperDate',
	'END_DATE' => 'ProperDate',
] );

$columns = [
	'FULL_NAME' => _( 'Student' ),
	'STUDENT_ID' => sprintf( _( '%s ID' ), SchoolBrandName() ),
	'TITLE' => _( 'School' ),
	'START_DATE' => _( 'Enrolled' ),
	'END_DATE' => _( 'Dropped' ),
];

ListOutput( $enrollment_RET, $columns, 'Enrollment Record', 'Enrollment Records' );

/**
 * Make Student Info link
 *
 * @since 7.2
 * @since 9.0 Add Student Photo Tip Message
 *
 * Local function
 * DBGet() callback
 *
 * @param  string $value  Student Full Name.
 * @param  string $column Column.
 *
 * @return string         Link to Student Info program.
 */
function _makeStudentInfoLink( $value, $column = 'FULL_NAME' )
{
	global $THIS_RET;

	$modname = 'Students/Student.php';

	if ( ! AllowUse( $modname )
		|| ! $THIS_RET['STUDENT_ID'] )
	{
		return $value;
	}

	$link = 'Modules.php?modname=' . $modname . '&student_id=' . $THIS_RET['STUDENT_ID'];

	if ( $THIS_RET['SCHOOL_ID'] !== UserSchool() )
	{
		$link .= '&school_id=' . $THIS_RET['SCHOOL_ID'];
	}

	return '<a href="' . URLEscape( $link ) . '">' . MakeStudentPhotoTipMessage( $THIS_RET['STUDENT_ID'], $value ) . '</a>';
}
