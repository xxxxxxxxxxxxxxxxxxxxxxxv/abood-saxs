/**
 * Select2Input() function JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.functions.select2Input = {
	opt: {
		language: {
			noResults: function() { return ''; }
		}
	},
	/**
	 * Select2 AJAX results
	 * Use the `data-ajax-url` attribute
	 *
	 * @link https://select2.org/data-sources/ajax
	 *
	 * @since 12.5
	 */
	ajaxOpt: {
		width: function() {
			// Width is `<select style="width: XXXpx">` if defined, or auto
			return $(this).prev('select')[0].style.width || 'auto';
		},
		language: {
			noResults: function() { return ' '; },
			searching: function() { return '...'; }
		}, ajax: {
			url: function() {
				return $(this).data('ajax-url');
			},
			dataType: 'json',
			delay: 800,
			cache: true
		}
	},
	init: function() {
		$('.select2-select').select2(csp.functions.select2Input.opt);

		$('.select2-select[data-ajax-url]').select2(csp.functions.select2Input.ajaxOpt);

		$('.onclick').parent('div').on('click', csp.functions.select2Input.inputDivOnclick);
	},
	select2Div: [],
	inputDivOnclick: function() {
		// Trigger select2() onclick when inside InputDivOnclick()
		if (! $(this).has('.select2-select')) {
			return;
		}

		var selectId = $(this).find('.select2-select').attr('id');

		if (csp.functions.select2Input.select2Div[selectId]) {
			return;
		}

		csp.functions.select2Input.select2Div[selectId] = true;

		var divOpt = csp.functions.select2Input.opt;

		if ($('#' + selectId).data('ajaxUrl')) {
			divOpt = csp.functions.select2Input.ajaxOpt;
		}

		$('#' + selectId).select2(divOpt);
	}
}

$(csp.functions.select2Input.init);
