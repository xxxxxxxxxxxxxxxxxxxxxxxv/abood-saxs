<?php
//FJ move Attendance.php from functions/ to modules/Attendance/includes
require_once 'modules/Attendance/includes/UpdateAttendanceDaily.fnc.php';

DrawHeader( ProgramTitle() );

//FJ add translation
$message = '<table><tr><td colspan="7" class="center">' . _( 'From' ) . ' ' .
	DateInput( DBDate(), 'min', '', false, false ) . ' ' . _( 'to' ) . ' ' .
	DateInput( DBDate(), 'max', '', false, false ) . '</td></tr></table>';

if ( Prompt( _( 'Confirm' ), _( 'When do you want to recalculate the daily attendance?' ), $message ) )
{
	// Display notice while calculating daily attendance
	echo '<br />';

	PopTable( 'header', _( 'Recalculate Daily Attendance' ) );

	$msg_done = ErrorMessage( [ _( 'The Daily Attendance for that timeframe has been recalculated.' ) ], 'note' );

	echo '<div id="message_div" data-msg-done="' . AttrEscape( $msg_done ) . '" class="center">
		<span class="loading" style="visbility: visible;"></span> ' . _( 'Calculating ...' ) . ' </div>';

	PopTable( 'footer' );

	// Note: buffer & flush not working with AJAX
	ob_flush();
	flush();
	ignore_user_abort( true );
	set_time_limit( 300 );

	$current_RET = DBGet( "SELECT DISTINCT SCHOOL_DATE
		FROM attendance_calendar
		WHERE SCHOOL_ID='" . UserSchool() . "'
		AND SYEAR='" . UserSyear() . "'", [], [ 'SCHOOL_DATE' ] );

	$students_RET = GetStuList();

	$begin = mktime( 0, 0, 0, (int) $_REQUEST['month_min'], (int) $_REQUEST['day_min'], (int) $_REQUEST['year_min'] ) + 43200;
	$end = mktime( 0, 0, 0, (int) $_REQUEST['month_max'], (int) $_REQUEST['day_max'], (int) $_REQUEST['year_max'] ) + 43200;

	for ( $i = $begin; $i <= $end; $i += 86400 )
	{
		foreach ( (array) $students_RET as $student )
		{
			// @since 7.5 Delete any attendance for this day & student prior to update.
			// Fix case where calendar has changed and day is no longer a school day.
			// Else, the daily attendance will be re-inserted below using UpdateAttendanceDaily().
			DBQuery( "DELETE FROM attendance_day
				WHERE STUDENT_ID='" . (int) $student['STUDENT_ID'] . "'
				AND SYEAR='" . UserSyear() . "'
				AND SCHOOL_DATE='" . date( 'Y-m-d', $i ) . "'" );

			if ( isset( $current_RET[date( 'Y-m-d', $i )] ) )
			{
				UpdateAttendanceDaily( $student['STUDENT_ID'], date( 'Y-m-d', $i ) );
			}
		}
	}

	$_REQUEST['modfunc'] = false;

	// Display note after calculating daily attendance
	// @since 12.5 CSP remove unsafe-inline Javascript
	?>
	<script src="assets/js/csp/modules/attendance/FixDailyAttendance.js?v=12.5"></script>
	<?php
}
