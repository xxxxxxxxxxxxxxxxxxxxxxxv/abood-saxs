/**
 * Group Drops program (Scheduling module) JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.modules.scheduling.massDrops = {
	updateClose: function() {
		$('#course_div').html($('#course_div_html').val());

		$.colorbox.close();
	},
	ready: function() {
		csp.modules.scheduling.massDrops.updateClose();
	}
}

$(csp.modules.scheduling.massDrops.ready);
