/**
 * Courses program (Scheduling module) JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.modules.scheduling.courses = {
	nbSchoolPeriods: 0,
	newSchoolPeriod: function() {
		var $nsp = $('.onclick-new-school-period');

		if (! $nsp.data('new')) {
			$('#schoolPeriod' + $nsp.data('i')).hide();
		}

		$nsp.on('click', csp.modules.scheduling.courses.onClickNewSchoolPeriod);
	},
	onClickNewSchoolPeriod: function() {
		// Note: do NOT use this.dataset here.
		if ($(this).data('new')) {
			if (! csp.modules.scheduling.courses.nbSchoolPeriods) {
				csp.modules.scheduling.courses.nbSchoolPeriods = $(this).data('i');
			}

			return csp.modules.scheduling.courses.addNewSchoolPeriod();
		}

		$('#schoolPeriod' + $(this).data('i')).css('display', 'table-row');

		$(this).hide();
	},
	addNewSchoolPeriod: function() {
		var table = document.getElementById('coursesTable'),
			nbSchoolPeriods = csp.modules.scheduling.courses.nbSchoolPeriods,
			row = table.insertRow(4 + nbSchoolPeriods);

		row.setAttribute('id', 'schoolPeriod' + (nbSchoolPeriods + 1));

		row.setAttribute('class', 'st');

		// insert table cells to the new row
		var tr = document.getElementById('schoolPeriod' + nbSchoolPeriods);

		for (i = 0; i < 2; i++) {
			csp.modules.scheduling.courses.createCell(row.insertCell(i), tr, i, nbSchoolPeriods + 1);
		}

		csp.modules.scheduling.courses.nbSchoolPeriods++;
	},
	// fill the cells
	createCell: function(cell, tr, i, newId) {
		cell.innerHTML = tr.cells[i].innerHTML;

		if (i == 1) {
			cell.setAttribute('colspan', '2');
		}

		var reg = new RegExp('new' + (newId - 1), 'g'); //g for global string

		cell.innerHTML = cell.innerHTML.replace(reg, 'new' + newId);

		// remove required attribute
		cell.innerHTML = cell.innerHTML.replace('required', '');
	},
	colorBox: function() {
		// 1200px width on desktop & 95% on mobile.
		$.colorbox.resize({width: ( screen.width > 1200 ? 1200 : '95%' )});

		// Redirect link & form AJAX result to colorBox instead of body (default)
		$('#colorbox a,#colorbox form').attr('target', 'cboxLoadedContent');

		/**
		 * JS post form on date change.
		 *
		 * @since 12.0 Use colorBox instead of popup window
		 *
		 * JS Fix use select.onchange instead of $(select).on('change') so it gets called by jscalendar
		 */
		$('#colorbox #yearSelect1,#colorbox #monthSelect1,#colorbox #daySelect1').each(function() {
			this.onchange = function() {
				ajaxPostForm(this.form);
			}
		});

		// Update & close.
		if ($('#course_div_html').length) {
			$('#' + $('#course_div_html').data('id')).html($('#course_div_html').val());

			$.colorbox.close();
		}
	},
	ready: function() {
		if ($('#search_term').length) {
			$('#search_term')[0].focus();
		}

		if ($('.onclick-new-school-period').length) {
			csp.modules.scheduling.courses.newSchoolPeriod();
		}

		// @since 12.0 Use colorBox instead of popup window
		if ($('#cboxLoadedContent').length) {
			csp.modules.scheduling.courses.colorBox();
		}
	}
}

$(csp.modules.scheduling.courses.ready);
