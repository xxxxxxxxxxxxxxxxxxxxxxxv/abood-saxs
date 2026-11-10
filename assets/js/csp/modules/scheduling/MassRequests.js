/**
 * Group Requests program (Scheduling module) JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.modules.scheduling.massRequests = {
	updateClose: function() {
		$('#course_div').html($('#course_div_html').val());

		$.colorbox.close();
	},
	ready: function() {
		csp.modules.scheduling.massRequests.updateClose();
	}
}

$(csp.modules.scheduling.massRequests.ready);
