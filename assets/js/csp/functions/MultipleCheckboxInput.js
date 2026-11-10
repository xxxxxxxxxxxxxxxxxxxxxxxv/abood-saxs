/**
 * MultipleCheckboxInput() function JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.functions.multipleCheckboxInput = function() {
	/**
	 * Check for required checkbox group:
	 * Make sure at least one checkbox is checked before submitting the form.
	 * Otherwise, show alert box.
	 *
	 * @link https://stackoverflow.com/questions/6218494/using-the-html5-required-attribute-for-a-group-of-checkboxes
	 */
	$('#body form').on('submit', function(e) {
		var ret = true;

		$('.multiple-checkbox-input.required', this).each(function() {

			if (ret && $('input[type=checkbox]:checked', this).length < 1) {
				e.preventDefault();

				e.stopImmediatePropagation();

				this.scrollIntoView({behavior: "smooth", block: "center"});

				alert($('#multiple_checkbox_input_required_alert').val());

				ret = false;
			}
		});

		return ret;
	});
}


$(csp.functions.multipleCheckboxInput);
