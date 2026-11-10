/**
 * MultipleCheckboxInput() function JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.functions.fileInput = {
	sizeValidate: function() {
		fileInputSizeValidate(this, this.dataset.maxSize);
	},
	onEvents: function() {
		$('.onchange-file-input').on('change', csp.functions.fileInput.sizeValidate);
	}
}

$(csp.functions.fileInput.onEvents);
