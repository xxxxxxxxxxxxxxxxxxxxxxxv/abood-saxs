/**
 * _makePaymentsCommentsInput() function (Student Billing module) JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.modules.studentBilling.makePaymentsCommentsInput = {
	feesReconcile: function() {
		// Automatically fills the Comments & Amount inputs
		var values = this.value.split('|'),
			amount = values[0],
			comments = values[1];

		$('#valuesnewAMOUNT').val(amount);
		$('#valuesnewCOMMENTS').val(comments);
	},
	onEvents: function() {
		$('.onchange-billing-fees').on('change', csp.modules.studentBilling.makePaymentsCommentsInput.feesReconcile);
	}
}

$(csp.modules.studentBilling.makePaymentsCommentsInput.onEvents);
