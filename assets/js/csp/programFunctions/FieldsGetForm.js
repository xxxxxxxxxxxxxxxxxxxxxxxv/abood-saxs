/**
 * GetFieldsForm() ProgramFunctions JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.programFunctions.fieldsGetForm = {
	typeSwitchSelectOptions: function() {
		var type = this.value;

		if (type == 'select' || type == 'autos' || type == 'exports' || type == 'multiple') {
			$('#select_options_wrapper').show();
		} else {
			$('#select_options_wrapper').hide();
		}
	},
	onEvents: function() {
		$('.onchange-field-type').on('change', csp.programFunctions.fieldsGetForm.typeSwitchSelectOptions);
	}
}

$(csp.programFunctions.fieldsGetForm.onEvents);
