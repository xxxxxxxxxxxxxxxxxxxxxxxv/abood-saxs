/**
 * Choose Request widget (misc module) JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.modules.misc.chooseRequest = {
	updateClose: function() {
		$('#request_div').html($('#request_div_html').val());

		$.colorbox.close();
	},
	ready: function() {
		csp.modules.misc.chooseRequest.updateClose();
	}
}

$(csp.modules.misc.chooseRequest.ready);
