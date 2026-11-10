/**
 * RegistrationAdminContactEnable() function (Custom module) JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.modules.custom.registrationAdminContactEnable = {
	do: function() {
		$('#field' + this.id + ' input,#field' + this.id + ' select').prop('disabled', ! this.checked);

		$('#field' + this.id + ' legend input[type="checkbox"]').prop('disabled', false);
	},
	ready: function() {
		$('.onclick-admin-contact-enable').each(csp.modules.custom.registrationAdminContactEnable.do);

		$('.onclick-admin-contact-enable').on('click', csp.modules.custom.registrationAdminContactEnable.do);
	}
}

$(csp.modules.custom.registrationAdminContactEnable.ready);
