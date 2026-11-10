/**
 * Mass Create Assignments program (Grades module) JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.modules.grades.massCreateAssignments = {
	// JS handle case: Weight is set => Set min Points to 1 & hide tooltip.
	onChangeNewWeight: function() {
		if (this.value != '') {
			$('#tablesnewPOINTS').attr('min', 1);
			$('#points_tooltip').hide();
		} else {
			$('#tablesnewPOINTS').attr('min', 0);
			$('#points_tooltip').show().css('display', 'inline-block');
		}
	},
	onEvents: function() {
		$('.onchange-new-weight:not([required])').on(
			'change',
			csp.modules.grades.massCreateAssignments.onChangeNewWeight
		);
	}
}

$(csp.modules.grades.massCreateAssignments.onEvents);
