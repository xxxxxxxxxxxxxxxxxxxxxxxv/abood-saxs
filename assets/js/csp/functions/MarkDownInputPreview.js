/**
 * MarkDownInputPreview() function JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.functions.markDownInputPreview = {
	preview: function() {
		MarkDownInputPreview(this.dataset.id);
	},
	onEvents: function() {
		// Fix function called as many times as we browsed the page in AJAX / loaded this JS file
		$(document).off('click', '.md-preview .tab');
		$(document).on('click', '.md-preview .tab', csp.functions.markDownInputPreview.preview);
	}
}

$(csp.functions.markDownInputPreview.onEvents);
