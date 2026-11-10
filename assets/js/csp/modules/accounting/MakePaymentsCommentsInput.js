/**
 * _makePaymentsCommentsInput() function (Accounting module) JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.modules.accounting.makePaymentsCommentsInput = {
	salariesReconcile: function() {
		// Automatically fills the Comments & Amount inputs
		var values = this.value.split('|'),
			amount = values[0],
			comments = values[1];

		$('#valuesnewAMOUNT').val(amount);
		$('#valuesnewCOMMENTS').val(comments);
	},
	onEvents: function() {
		$('.onchange-accounting-salaries').on('change', csp.modules.accounting.makePaymentsCommentsInput.salariesReconcile);
	}
}

$(csp.modules.accounting.makePaymentsCommentsInput.onEvents);
