/**
 * Group Schedule program (Scheduling module) JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.modules.scheduling.massSchedule = {
	updateClose: function() {
		$('#course_div').append($('#course_div_html').val());

		$.colorbox.close();
	},
	ready: function() {
		csp.modules.scheduling.massSchedule.updateClose();
	}
}

$(csp.modules.scheduling.massSchedule.ready);
