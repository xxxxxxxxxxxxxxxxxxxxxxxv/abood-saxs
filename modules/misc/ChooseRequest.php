<?php

$_REQUEST['modfunc'] = 'choose_course';

if ( empty( $_REQUEST['course_id'] ) )
{
	include 'modules/Scheduling/Courses.php';
}
else
{
	$course_title = ParseMLField( DBGetOne( "SELECT TITLE
		FROM courses
		WHERE COURSE_ID='" . (int) $_REQUEST['course_id'] . "'" ) );

	$html_to_escape = $course_title .
	'<input type="hidden" name="request_course_id" value="' . AttrEscape( $_REQUEST['course_id'] ) . '" /><br />
	<label><input type="checkbox" name="missing_request_course" value="Y" /> ' .
	_( 'Not Requested' ) . '</label>';

	// @since 12.0 Use colorBox instead of popup window
	// @since 12.5 CSP remove unsafe-inline Javascript
	?>
	<input type="hidden" disabled id="request_div_html" value="<?php echo AttrEscape( $html_to_escape ); ?>" />
	<script src="assets/js/csp/modules/misc/ChooseRequest.js?v=12.5"></script>
	<?php
}
