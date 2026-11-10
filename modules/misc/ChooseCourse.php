<?php

$_REQUEST['modfunc'] = 'choose_course';

if ( empty( $_REQUEST['course_period_id'] ) )
{
	include 'modules/Scheduling/Courses.php';
}
else
{
	$course = DBGet( "SELECT c.TITLE AS COURSE_TITLE,cp.TITLE,cs.TITLE AS SUBJECT_TITLE
		FROM course_periods cp,courses c,course_subjects cs
		WHERE c.COURSE_ID=cp.COURSE_ID
		AND cp.COURSE_PERIOD_ID='" . (int) $_REQUEST['course_period_id'] . "'
		AND c.SUBJECT_ID=cs.SUBJECT_ID
		AND cs.SUBJECT_ID='" . (int) $_REQUEST['subject_id'] . "'",
		[
			'COURSE_TITLE' => 'ParseMLField',
			'SUBJECT_TITLE' => 'ParseMLField',
		] );

	$last_year = $_REQUEST['last_year'] == 'true' ? 'ly_' : '';

	// @since 6.5 Course Widget: add Subject and Not options.
	$html_to_escape = '<span id="w_course_period_title">' .$course[1]['TITLE'] . '</span>
		<span id="w_course_title" class="hide">' . $course[1]['COURSE_TITLE'] . '</span>
		<span id="w_subject_title" class="hide">' . $course[1]['SUBJECT_TITLE'] . '</span>';

	$html_to_escape .= '<input type="hidden" name="w_' . $last_year . 'course_period_id" value="' . AttrEscape( $_REQUEST['course_period_id'] ) . '" />
	<input type="hidden" name="w_' . $last_year . 'course_id" value="' . AttrEscape( $_REQUEST['course_id'] ) . '" />
	<input type="hidden" name="w_' . $last_year . 'subject_id" value="' . AttrEscape( $_REQUEST['subject_id'] ) . '" />';

	$html_to_escape .= '<br />
	<label><input type="checkbox" name="w_' . $last_year . 'course_period_id_not" value="Y" /> ' .
		_( 'Not' ) . '</label>
	<label><select name="w_' . $last_year . 'course_period_id_which"
		class="onchange-widget-course-title-update" autocomplete="off">
	<option value="course_period"> ' . _( 'Course Period' ) . '</option>
	<option value="course"> ' . _( 'Course' ) . '</option>
	<option value="subject"> ' . _( 'Subject' ) . '</option>
	</select></label>';

	// @since 12.0 Use colorBox instead of popup window
	// @since 12.5 CSP remove unsafe-inline Javascript
	?>
	<input type="hidden" disabled id="course_div_html" value="<?php echo AttrEscape( $html_to_escape ); ?>"
		data-id="<?php echo AttrEscape( $last_year . 'course_div' ); ?>" />
	<script src="assets/js/csp/modules/misc/ChooseCourse.js?v=12.5"></script>
	<?php
}
