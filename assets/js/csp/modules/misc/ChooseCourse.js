/**
 * Choose Course widget (misc module) JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.modules.misc.chooseCourse = {
	updateClose: function() {
		$('#' + $('#course_div_html').data('id')).html($('#course_div_html').val());

		$.colorbox.close();
	},
	titleUpdate: function(sel){
		$('#w_course_period_title,#w_course_title,#w_subject_title').addClass('hide');

		$('#w_' + sel + '_title').removeClass('hide');
	},
	ready: function() {
		csp.modules.misc.chooseCourse.updateClose();

		$('.onchange-widget-course-title-update').on('change', function() {
			csp.modules.misc.chooseCourse.titleUpdate(this.value);
		});
	}
}

$(csp.modules.misc.chooseCourse.ready);
