/**
 * Import Form Submit JS
 *
 * Can be reused in add-ons:
 * Form must have the `import-form` CSS class
 * Button must have the `import-button` CSS class
 * Alert message must be in a hidden input with id `import_alert_txt`
 * Stop button HTML must be in a hidden input with id `import_stop_button_html` and have the `stop-button` CSS class
 *
 * @see example in the PHP MoodleImportFormSubmitJS() function
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.modules.importFormSubmit = function(e) {
	e.preventDefault();
	e.stopImmediatePropagation();

	var alertTxt = $('#import_alert_txt').val();

	// Alert.
	if (! window.confirm(alertTxt)) return false;

	var $buttons = $('.import-button'),
		buttonTxt = $buttons.val(),
		seconds = 5,
		stopButtonHTML = $('#import_stop_button_html').val();

	$buttons.css('pointer-events', 'none').attr('disabled', true).val(buttonTxt + ' ... ' + seconds);

	var countdown = setInterval(function() {
		if ( seconds == 0 ) {
			clearInterval(countdown);

			$('.import-form').off('submit').trigger('submit');

			return;
		}

		$buttons.val(buttonTxt + ' ... ' + --seconds);
	}, 1000);

	// Insert stop button.
	$(stopButtonHTML).on('click', function() {
		clearInterval(countdown);

		$('.stop-button').remove();

		$buttons.css('pointer-events', '').attr('disabled', false).val(buttonTxt);

		return false;
	}).insertAfter($buttons);
};

$('.import-form').on('submit', csp.modules.importFormSubmit);
