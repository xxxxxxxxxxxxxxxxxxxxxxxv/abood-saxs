/**
 * Portal Polls program (School module) JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.modules.schoolSetup.portalPolls = {
	newOption: function() {
		var table = document.getElementById('new-options-table'),
			nbOptions = (table.rows.length - 1),
			row = table.insertRow(nbOptions);

		// Fill the cells.
		function createCell(cell, tr, newId) {
			cell.innerHTML = tr.cells[0].innerHTML;

			reg = new RegExp('new' + (newId - 1), 'g'); //g for global string

			cell.innerHTML = cell.innerHTML.replace(reg, 'new' + newId);
		}

		// Insert table cells to the new row.
		var tr = document.getElementById('new-option-' + nbOptions);

		row.setAttribute('id', 'new-option-' + (nbOptions + 1));

		createCell(row.insertCell(0), tr, nbOptions + 1);
	},
	onEvents: function() {
		$('.onclick-add-new-question').on('click', csp.modules.schoolSetup.portalPolls.newOption);
	}
}

$(csp.modules.schoolSetup.portalPolls.onEvents);
