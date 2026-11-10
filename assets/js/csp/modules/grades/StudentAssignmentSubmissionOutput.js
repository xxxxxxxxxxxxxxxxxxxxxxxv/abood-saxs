/**
 * StudentAssignmentSubmissionOutput() function (Grades module) JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.modules.grades.studentAssignmentSubmissionOutput = {
	deleteFiles: function() {
		$('#submission_file_link').hide();

		$('#submission_file_input').show();

		$('#submission_file').prop('disabled', false);

		// Send `$_REQUEST['submission_file']` as `$_FILES['submission_file']` is not sent if input is empty
		// so we can delete old files if no new files are sent.
		$('#submission_file').after('<input type="hidden" name="submission_file" value="Y" />');
	},
	onEvents: function() {
		$('.onclick-delete-files').on('click', csp.modules.grades.studentAssignmentSubmissionOutput.deleteFiles);
	}
}

$(csp.modules.grades.studentAssignmentSubmissionOutput.onEvents);
