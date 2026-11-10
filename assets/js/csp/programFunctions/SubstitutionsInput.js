/**
 * Substitutions ProgramFunctions JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.programFunctions.substitutionsInput = {
	updateCode: function(event) {

		var codeValue = event.target.value,
			code = $('#substitutions_code_' + $(this).data('id'));

		// Update code with corresponding selected input value.
		code.val(codeValue);

		code.attr('size', codeValue.length - 1);
	},
	copyCode: function() {

		var code = $('#substitutions_code_' + $(this).data('id'));

		code.focus().select();

		// Copy code into clipboard.
		document.execCommand("copy");
	},
	onEvents: function() {
		// Set select onchange & button onclick functions.
		$('.onchange-substitutions-input').change(csp.programFunctions.substitutionsInput.updateCode);
		$('.onclick-substitutions-button').click(csp.programFunctions.substitutionsInput.copyCode);
	}
};

$(csp.programFunctions.substitutionsInput.onEvents);
