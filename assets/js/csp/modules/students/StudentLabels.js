/**
 * Print Student Labels program (Students module) JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.modules.students.studentLabels = {
	// Toggle fieldset disabled attribute.
	disableFieldset: function() {
		// Note: do not use `this` here, not working.
		$('input[name="mailing_labels"]').parents('fieldset').prop('disabled', function(i, v) {
			return !v;
		});
	},
	onEvents: function() {
		$('input[name="mailing_labels"]').on('change', csp.modules.students.studentLabels.disableFieldset);
	}
}

$(csp.modules.students.studentLabels.onEvents);
