/**
 * CaptchaInput() function JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.functions.captchaInput = function() {
	$('.captcha').each(function() {
		captcha(this.id);
	});
}

$(csp.functions.captchaInput);
