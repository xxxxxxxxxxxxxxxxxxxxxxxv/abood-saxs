/**
 * Course (Scheduling) Widget JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.widget.course = {
	idUpdate: function() {
		var val = this.value;

		if (! val) {
			$('[name=w_course_period_id]').val('');
			$('[name=w_course_id]').val('');
			$('[name=w_subject_id]').val('');
			$('#course_div').hide();

			return;
		}

		$('#course_div').show();

		var values = val.split(',');

		$('[name=w_course_period_id]').val(values[2]);
		$('[name=w_course_id]').val(values[1]);
		$('[name=w_subject_id]').val(values[0]);
	},
	onEvents: function() {
		$('.onchange-widget-course-id-update').on('change', csp.widget.course.idUpdate);
	}
}

$(csp.widget.course.onEvents);
