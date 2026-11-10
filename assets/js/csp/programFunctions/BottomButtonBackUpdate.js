/**
 * BottomButtonBackUpdate() ProgramFunctions JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

/**
 * "Back to List" Bottom.php button update
 * Use `data-text`, `data-href` & `data-after` attributes
 *
 * @return {boolean} True if updated.
 */
csp.programFunctions.bottomButtonBackUpdate = function() {
	var b = document.getElementById('bottom_button_back_update');

	if (! b) {
		return false;
	}

	$('#BottomButtonBack span').text(b.dataset.text);

	$('#BottomButtonBack').removeClass('hide')
		.attr('href', b.dataset.href)
		.attr('title', b.dataset.text);

	if (b.dataset.after) {
		document.getElementById('BottomButtonBack').insertAdjacentHTML('afterend', '<span id="BottomButtonBackAfter"></span>');

		// Here we use setInnerHTML() (not jQuery) so Javascript gets loaded
		setInnerHTML(document.getElementById('BottomButtonBackAfter'), b.dataset.after);
	}

	return true;
}

$(csp.programFunctions.bottomButtonBackUpdate);
