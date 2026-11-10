/**
 * Rollover program (School module) JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.modules.schoolSetup.rollover = {
	// JS Enable / disable checkbox on Courses checkbox change.
	switchCoursePeriods: function() {
		$('#course_periods').prop('disabled', ! this.checked);

		if (! this.checked) {
			$('#course_periods').prop('checked', false);
		}
	},
	onEvents: function() {
		$('input[name="tables[courses]"]').on('change', csp.modules.schoolSetup.rollover.switchCoursePeriods);
	}
}

$(csp.modules.schoolSetup.rollover.onEvents);
