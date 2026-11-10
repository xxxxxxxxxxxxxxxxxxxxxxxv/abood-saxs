/**
 * Marking Periods program (School module) JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.modules.schoolSetup.markingPeriods = {
	// Grade posting date inputs are required when "Graded" is checked.
	postDatesRequired: function() {
		var dates = ['month', 'day', 'year'],
			dateStartInput,
			dateEndInput,
			mpId = getURLParam(document.URL, 'marking_period_id') || 'new';

		for (var i = 0,max = dates.length; i < max; i++) {
			dateStartInput = document.getElementsByName( dates[i] + '_tables[' + mpId + '][POST_START_DATE]' )[0];

			if (dateStartInput) {
				dateStartInput.required = this.checked;
			}

			dateEndInput = document.getElementsByName( dates[i] + '_tables[' + mpId + '][POST_END_DATE]' )[0];

			if (dateEndInput) {
				dateEndInput.required = this.checked;
			}
		}

		// Add .legend-red CSS class to label if input is required/
		$(dateStartInput).parent().nextAll('.legend-gray').toggleClass('legend-red', this.checked);
		$(dateEndInput).parent().nextAll('.legend-gray').toggleClass('legend-red', this.checked);
	},
	onEvents: function() {
		// Fix function called as many times as we browsed the page in AJAX / loaded this JS file
		$(document).off('click', '.onclick-mp-does-grades');
		$(document).on('click', '.onclick-mp-does-grades', csp.modules.schoolSetup.markingPeriods.postDatesRequired);
	}
}

$(csp.modules.schoolSetup.markingPeriods.onEvents);
