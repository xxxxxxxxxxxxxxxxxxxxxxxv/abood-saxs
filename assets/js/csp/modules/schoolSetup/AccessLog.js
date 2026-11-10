/**
 * Access Log program (School module) JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.modules.schoolSetup.accessLog = {
	// When clicking on Username, go to Student or User Info.
	goToStudentUserInfo: function() {
		$('.al-username').attr('href', function(){
			var url = 'Modules.php?modname=Users/User.php&search_modfunc=list&';

			if ( $(this).hasClass('student') ) {
				url = url.replace( 'Users/User.php', 'Students/Student.php' ) + 'cust[USERNAME]=';
			} else {
				url += 'username=';
			}

			return url + encodeURIComponent( '"' + this.firstChild.data + '"' );
		});
	},
	ready: function() {
		csp.modules.schoolSetup.accessLog.goToStudentUserInfo();
	}
}

$(csp.modules.schoolSetup.accessLog.ready);
