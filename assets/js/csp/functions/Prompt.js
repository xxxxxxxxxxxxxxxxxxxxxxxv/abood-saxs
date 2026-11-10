/**
 * DeletePrompt() & Prompt() functions JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

/**
 * Cancel (Delete) Prompt
 * If inside colorBox, close it. Otherwise, go back in browser history.
 */
csp.functions.prompt = {
	cancel: function() {
		if ($(this).closest('#colorbox').length) {
			$.colorbox.close();
		} else {
			self.history.go(-1);
		}
	},
	onEvents: function() {
		$('.button-prompt-cancel').on('click', csp.functions.prompt.cancel);
	}
}

$(csp.functions.prompt.onEvents);
