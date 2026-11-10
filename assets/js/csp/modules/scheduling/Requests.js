/**
 * Student Requests program (Scheduling module) JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

csp.modules.scheduling.requests = {
	connection: null,
	sendXMLRequest: function(subjectId, course) {
		csp.modules.scheduling.requests.connection = new XMLHttpRequest();

		csp.modules.scheduling.requests.connection.onreadystatechange = csp.modules.scheduling.requests.processRequest;

		csp.modules.scheduling.requests.connection.open(
			'GET',
			'Modules.php?modname=Scheduling/Requests.php&_ROSARIO_PDF=true&modfunc=XMLHttpRequest&subject_id=' + subjectId +
				'&course_title=' + encodeURIComponent(course),
			true
		);

		csp.modules.scheduling.requests.connection.send(null);
	},
	addCourse: function(course) {
		ajaxLink('Modules.php?modname=Scheduling/Requests.php&modfunc=add&course=' + course);
	},
	processRequest: function() {
		// LOADED && ACCEPTED
		if (csp.modules.scheduling.requests.connection.readyState == 4
			&& csp.modules.scheduling.requests.connection.status == 200) {
			var XMLResponse = csp.modules.scheduling.requests.connection.responseXML;

			document.getElementById('courses_div').style.display = 'block';

			var course_list = XMLResponse.getElementsByTagName('courses');

			course_list = course_list[0];

			var courses = course_list.getElementsByTagName('course');

			for (i = 0; i < courses.length; i++) {
				var id = courses[i].getElementsByTagName('id')[0].firstChild.data;

				var title = courses[i].getElementsByTagName('title')[0].firstChild.data;

				document.getElementById('courses_div').innerHTML += '<a class="onclick-add-course" data-id="' + id + '" href="#!">' + title + '</a><br />';
			}

			if (! courses.length) {
				document.getElementById('courses_div').innerHTML += document.getElementById('no_courses_found').value;
			}
		}
	},
	onEvents: function() {
		$('.onchange-subject-id').on('change', function() {
			document.getElementById('courses_div').innerHTML = '';

			csp.modules.scheduling.requests.sendXMLRequest(
				this.value,
				this.form.course_title.value
			);
		});

		$('.onkey-course-title').on('keypress', function() {
			if (event.keyCode == 13) {
				return false;
			}
		});

		$('.onkey-course-title').on('keyup', function() {
			document.getElementById('courses_div').innerHTML = '';

			csp.modules.scheduling.requests.sendXMLRequest(
				this.form.subject_id.options[this.form.subject_id.selectedIndex].value,
				this.form.course_title.value
			);
		});

		$('#body').on('click', '.onclick-add-course', function() {
			csp.modules.scheduling.requests.addCourse(this.dataset.id);
		});
	}
}

$(csp.modules.scheduling.requests.onEvents);
