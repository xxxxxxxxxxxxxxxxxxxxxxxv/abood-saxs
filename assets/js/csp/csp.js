/**
 * CSP Root (global, always loaded) JS
 * CSP stands for Content Security Policy
 * It mainly blocks inline Javascript to help prevent XSS
 *
 * @link https://gitlab.com/francoisjacquet/rosariosis/-/blob/mobile/plugins/Content_Security_Policy/README.md
 *
 * @see ContentSecurityPolicy() function in Warehouse.php
 *
 * @since 12.5 CSP remove unsafe-inline Javascript
 *
 * @package RosarioSIS
 */

/**
 * csp global object to store CSP related functions
 *
 * @type {Object}
 */
var csp = {
	// Root (global, always loaded) JS: AJAX, check all, maybe edit select. See below for details.
	root: {},
	functions: {},
	modules: {
		accounting: {},
		attendance: {},
		custom: {},
		grades: {},
		misc: {},
		scheduling: {},
		schoolSetup: {},
		studentBilling: {},
		students: {},
		users: {},
	},
	plugins: {},
	programFunctions: {},
	widget: {}
};

/**
 * On events
 *
 * About event delegation & performance
 * @link https://stackoverflow.com/questions/12824549/should-all-jquery-events-be-bound-to-document
 * @link https://stackoverflow.com/questions/17443281/should-i-attach-my-onclick-event-to-the-document-or-element
 */
csp.root.onEvents = function() {
	/**
	 * Input related AJAX events
	 * AJAX link to the URL inside the input's `data-link` attribute:
	 * Use the `onchange-ajax-link` & `onclick-ajax-link` CSS classes
	 *
	 * AJAX POST the input's form:
	 * Use the `onchange-ajax-post-form` & `onclick-ajax-post-form` CSS classes
	 *
	 * @see Side.php, modules, plugins
	 *
	 * @uses csp.root.onAjax() function
	 */
	$(document.body).on('change', '.onchange-ajax-post-form,.onchange-ajax-link', csp.root.onAjax);

	$('#body,#colorbox').on('click', '.onclick-ajax-post-form,.onclick-ajax-link', csp.root.onAjax);

	/**
	 * Check all checkboxes:
	 * Use the `onclick-checkall` CSS class & use the `data-name-like` attribute
	 * Or
	 * Display or hide elements:
	 * Use the `onclick-toggle` CSS class & use the `data-id` attribute
	 *
	 * @see Inputs.php, modules
	 *
	 * @uses csp.root.checkAllOrToggle() function
	 */
	$('#body,#colorbox').on('click', '.onclick-checkall,.onclick-toggle', csp.root.checkAllOrToggle);

	/**
	 * Maybe edit select (or Select2) input:
	 * Use the `onchange-maybe-edit-select` CSS class
	 * When -Edit- option selected, change the auto pull-down to text field.
	 *
	 * @see modules, ProgramFunctions
	 *
	 * @uses csp.root.maybeEditSelect() function
	 */
	$('#body,#colorbox').on('change', '.onchange-maybe-edit-select', csp.root.maybeEditSelect);
}

$(csp.root.onEvents);

/**
 * AJAX link to the URL inside the input's `data-link` attribute (and optionnally the `data-target` attribute)
 * AJAX POST the input's form
 *
 * @uses ajaxPostForm() & ajaxLink() functions
 */
csp.root.onAjax = function() {
	if (this.className.indexOf('ajax-post-form') > -1) {
		// AJAX POST the input's form
		if (this.form !== undefined) {
			ajaxPostForm(this.form);
		}

		return;
	}

	if (this.className.indexOf('ajax-link') > -1) {
		if (! this.dataset.link) {
			return;
		}

		// AJAX link to the URL inside the input's `data-link` attribute
		var link = this.dataset.link;

		if (link.indexOf('this.value') !== -1) {
			// Note: `this.value` inside link is automatically replaced
			link = link.replaceAll('this.value', encodeURIComponent(this.value) || '');
		}

		/**
		 * Submit selected date
		 * Use `onchange-date-submit` CSS class & `data-name` attribute
		 *
		 * @see PrepareDate() function
		 */
		if (this.classList.contains('onchange-date-submit')) {
			var name = this.dataset.name,
				urlArgs = [ 'month' + name, 'day' + name, 'year' + name ];

			// Add year / month / day parameters to link.
			for (var i = 0; i < 3; i++) {
				var urlVal = $('select[name="' + urlArgs[i] + '"]').val();

				if (urlVal) {
					link += '&' + urlArgs[i] + '=' + urlVal;
				}
			}
		}

		if (this.dataset.target) {
			// Custom link target inside the input's `data-target` attribute (ID or `_top`, or `_blank`)
			link = {
				href: link,
				target: this.dataset.target
			};
		}

		ajaxLink(link);

		return;
	}
}

/**
 * Check all checkboxes:
 * Use the `onclick-checkall` CSS class & use the `data-name-like` attribute
 * Or
 * Display or hide elements:
 * Use the `onclick-toggle` CSS class & use the `data-id` attribute
 *
 * @see Inputs.php, modules
 *
 * @uses checkAll() function
 */
csp.root.checkAllOrToggle = function() {
	if (this.form && this.dataset.nameLike) {
		checkAll(this.form, this.checked, this.dataset.nameLike);
	}

	if (this.dataset.id) {
		$('#' + this.dataset.id).toggle();
	}
}

// When -Edit- option selected, change the auto pull-down to text field.
csp.root.maybeEditSelect = function() {

	// -Edit- option's value is ---.
	if ( this.value !== '---' ) {
		return;
	}

	var $el = $(this);

	// Remove parent <div> if any
	if ( $el.parent('div').length ) {
		$el.unwrap();
	}

	// Remove the Select2 select.
	$el.next('.select2-container').remove();

	// Remove the select input.
	$el.remove();

	// Show & enable the text input of the same name.
	$('[name="' + this.name + '_text"]').prop('name', this.name).prop('disabled', false).show().focus();
}
