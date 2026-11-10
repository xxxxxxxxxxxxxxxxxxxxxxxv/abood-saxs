/**
 * RegistrationSiblingUseContactsAddress() function (Custom module) JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.modules.custom.registrationSiblingUseContactsAddress = {
	disable: function(checked) {
		$('#registration_contacts_address_wrapper input,#registration_contacts_address_wrapper select,#registration_contacts_address_wrapper textarea').prop('disabled', checked);

		$('#registration_contacts_address_wrapper').toggle();
	},
	ready: function() {
		csp.modules.custom.registrationSiblingUseContactsAddress.disable(true);

		$('.onclick-sibling-use-contacts-address').on('click', function() {
			csp.modules.custom.registrationSiblingUseContactsAddress.disable(this.checked);
		});
	}
}

$(csp.modules.custom.registrationSiblingUseContactsAddress.ready);
