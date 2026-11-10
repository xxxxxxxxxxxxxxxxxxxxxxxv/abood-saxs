/**
 * Export program (misc module) JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.modules.misc.export = {
	submit: function() {
		var d = document,
			s = d.search;

		s.relation.value = d.getElementById('relation').value;
		s.residence.value = d.getElementById('residence').checked;
		s.mailing.value = d.getElementById('mailing').checked;
		s.bus_pickup.value = d.getElementById('bus_pickup').checked;
		s.bus_dropoff.value = d.getElementById('bus_dropoff').checked;
	},
	addField: function() {
		$('#names_div').append('<li>' + $(this).parent('label').text() + '</li>');

		$('#fields_div').append(
			'<input type="hidden" name="fields[' + this.dataset.name + ']" value="Y" />'
		);

		this.disabled = true;
	},
	onEvents: function() {
		$('form[name=search]').on('submit', csp.modules.misc.export.submit);

		$('.onclick-export-field').on('click', csp.modules.misc.export.addField);
	}
}

$(csp.modules.misc.export.onEvents);
