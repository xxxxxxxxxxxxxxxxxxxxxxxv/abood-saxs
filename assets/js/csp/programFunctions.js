/**
 * ProgramFunctions/ (global, always loaded) JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

/**
 * On events
 */
csp.programFunctions.onEvents = function() {
	$('#body .onmouseover-tipmsg').on('mouseover', csp.programFunctions.tipMessage);

	/**
	 * ajaxPrepare #body (or #colorbox) after AJAX call
	 * Use when delegated events are not possible
	 * or not recommended (mouseover fires too frequently)
	 *
	 * @see ajaxPrepare()
	 */
	$('#body,#colorbox').on('ajaxPrepare', function() {
		$('#' + this.id + ' .onmouseover-tipmsg').on('mouseover', csp.programFunctions.tipMessage);
	});
}

$(csp.programFunctions.onEvents);

/**
 * Show Tip Message
 * Use the `onmouseover-tipmsg` CSS class
 * Use the `data-title` & `data-msg` attributes
 *
 * @see MakeTipMessage()
 * @see assets/js/tipmessage/
 *
 * @uses stm() function
 */
csp.programFunctions.tipMessage = function() {
	stm([this.dataset.title, this.dataset.msg]);
}
