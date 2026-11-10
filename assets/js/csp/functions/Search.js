/**
 * Search() function JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

/**
 * Show Student Grade Level when selected Profile is "Parent"
 * Find a User form
 */
csp.functions.search = {
	profile: function() {
		$('#student_grade_level_row').toggle(this.value === 'parent');
	},
	onEvents: function() {
		$('#search #profile').on('change', csp.functions.search.profile);
	}
}

$(csp.functions.search.onEvents);
