/**
 * Addresses & Contacts tab (Student Info program, Students module) JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.modules.students.address = {
	showMailing: function() {
		var checked = this.checked;

		$('#mailing_address_div').css('visibility', function() {
			return checked ? 'visible' : 'hidden';
		});
	},
	openMap: function() {
		popups.open(this.dataset.link, 'scrollbars=yes,resizable=yes,width=1000,height=700');
	},
	onEvents: function() {
		$('.onclick-mailing-address').on('click', csp.modules.students.address.showMailing);

		$('.onclick-map-it').on('click', csp.modules.students.address.openMap);
	}
}

$(csp.modules.students.address.onEvents);
