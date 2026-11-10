/**
 * Create Student Account (Students module) JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.modules.students.createStudentAccount = {
	reload: function() {
		// @since 6.0 Reload page on School change, so we update UserSchool().
		window.location.href = this.dataset.url + this.value;
	},
	onEvents: function() {
		$('.onchange-school-reload').on('change', csp.modules.students.createStudentAccount.reload);
	}
}

$(csp.modules.students.createStudentAccount.onEvents);
