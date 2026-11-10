/**
 * Recalculate Daily Attendance program (Attendance module) JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.modules.attendance.fixDailyAttendance = {
	msgDone: function() {
		// Display note after calculating daily attendance
		$('#message_div').html($('#message_div').data('msgDone'));
	}
}

csp.modules.attendance.fixDailyAttendance.msgDone();
