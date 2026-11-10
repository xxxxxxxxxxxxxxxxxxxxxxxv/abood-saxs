<?php
require_once 'modules/Scheduling/functions.inc.php';

if ( $_REQUEST['modfunc'] === 'save' )
{
	if ( ! empty( $_SESSION['MassRequests.php'] ) )
	{
		if ( isset( $_REQUEST['student'] )
			&& is_array( $_REQUEST['student'] ) )
		{
			$course_exists = DBGetOne( "SELECT 1
				FROM courses
				WHERE COURSE_ID='" . (int) $_SESSION['MassRequests.php']['course_id'] . "'
				AND SYEAR='" . UserSyear() . "'" );

			if ( $course_exists )
			{
				$current_RET = DBGet( "SELECT STUDENT_ID
					FROM schedule_requests
					WHERE COURSE_ID='" . (int) $_SESSION['MassRequests.php']['course_id'] . "'
					AND SYEAR='" . UserSyear() . "'", [], [ 'STUDENT_ID' ] );

				foreach ( (array) $_REQUEST['student'] as $student_id )
				{
					if ( ! empty( $current_RET[$student_id] ) )
					{
						continue;
					}

					DBInsert(
						'schedule_requests',
						[
							'SYEAR' => UserSyear(),
							'SCHOOL_ID' => UserSchool(),
							'STUDENT_ID' => (int) $student_id,
							'SUBJECT_ID' => (int) $_SESSION['MassRequests.php']['subject_id'],
							'COURSE_ID' => (int) $_SESSION['MassRequests.php']['course_id'],
							'MARKING_PERIOD_ID' => '',
							'WITH_TEACHER_ID' => $_REQUEST['with_teacher_id'],
							'NOT_TEACHER_ID' => $_REQUEST['without_teacher_id'],
							'WITH_PERIOD_ID' => $_REQUEST['with_period_id'],
							'NOT_PERIOD_ID' => $_REQUEST['without_period_id'],
						]
					);
				}

				$note[] = button( 'check' ) . '&nbsp;' .
				_( 'This course has been added as a request for the selected students.' );
			}
			else
			{
				$error[] = _( 'No courses found' );
			}
		}
		else
		{
			$error[] = _( 'You must choose at least one student.' );
		}
	}
	else
	{
		$error[] = _( 'You must choose a course.' );
	}

	// Unset modfunc, student & redirect URL.
	RedirectURL( [ 'modfunc', 'student' ] );

	unset( $_SESSION['MassRequests.php'] );
}

if ( $_REQUEST['modfunc'] != 'choose_course' )
{
	DrawHeader( ProgramTitle() );

	echo ErrorMessage( $error );

	echo ErrorMessage( $note, 'note' );

	if ( $_REQUEST['search_modfunc'] === 'list' )
	{
		echo '<form action="' . URLEscape( 'Modules.php?modname=' . $_REQUEST['modname'] . '&modfunc=save' ) . '" method="POST">';

		DrawHeader( '', SubmitButton( _( 'Add Request to Selected Students' ) ) );

		echo '<br />';

		PopTable( 'header', _( 'Request to Add' ) );

		// @since 12.6 HTML add fieldset & padding to PopTable
		echo '<table class="cellpadding-5 width-100p"><td><div id="course_div">';

		if ( ! empty( $_SESSION['MassRequests.php'] ) )
		{
			$course_title = ParseMLField( DBGetOne( "SELECT TITLE
				FROM courses
				WHERE COURSE_ID='" . (int) $_SESSION['MassRequests.php']['course_id'] . "'" ) );

			echo $course_title;
		}

		$popup_url = 'Modules.php?modname=' . $_REQUEST['modname'] . '&modfunc=choose_course';

		// @since 12.0 Use colorBox instead of popup window
		// @since 12.9 Use button() with "+" icon instead of text link
		echo '</div>' . button(
			'add',
			_( 'Choose a Course' ),
			URLEscape( $popup_url ),
			'colorbox'
		) . '</td></tr>';

		echo '<tr><td><fieldset><legend>' . _( 'With' ) . '</legend>';

		echo '<table><tr class="st"><td><label><select name="with_teacher_id"><option value="">' . _( 'N/A' ) . '</option>';
		//FJ fix bug teacher's schools is NULL
		//$teachers_RET = DBGet( "SELECT STAFF_ID,LAST_NAME,FIRST_NAME,MIDDLE_NAME FROM staff WHERE SCHOOLS LIKE '%,".UserSchool().",%' AND SYEAR='".UserSyear()."' AND PROFILE='teacher' ORDER BY LAST_NAME,FIRST_NAME" );
		$teachers_RET = DBGet( "SELECT STAFF_ID," . DisplayNameSQL() . " AS FULL_NAME
			FROM staff
			WHERE (SCHOOLS IS NULL OR position('," . UserSchool() . ",' IN SCHOOLS)>0)
			AND SYEAR='" . UserSyear() . "'
			AND PROFILE='teacher'
			ORDER BY FULL_NAME" );

		foreach ( (array) $teachers_RET as $teacher )
		{
			echo '<option value="' . AttrEscape( $teacher['STAFF_ID'] ) . '">' . $teacher['FULL_NAME'] . '</option>';
		}

		echo '</select>' . FormatInputTitle( _( 'Teacher' ) ) . '</label></td></tr>';

		echo '<tr class="st"><td><label><select name="with_period_id"><option value="">' . _( 'N/A' ) . '</option>';

		$periods_RET = DBGet( "SELECT PERIOD_ID,TITLE
			FROM school_periods
			WHERE SCHOOL_ID='" . UserSchool() . "'
			AND SYEAR='" . UserSyear() . "'
			ORDER BY SORT_ORDER IS NULL,SORT_ORDER" );

		foreach ( (array) $periods_RET as $period )
		{
			echo '<option value="' . AttrEscape( $period['PERIOD_ID'] ) . '">' . $period['TITLE'] . '</option>';
		}

		echo '</select>' . FormatInputTitle( _( 'Period' ) ) . '</label></td></tr></table>';

		echo '</fieldset></td></tr>';

		echo '<tr><td><fieldset><legend>' . _( 'Without' ) . '</legend>';

		echo '<table><tr class="st"><td><label><select name="without_teacher_id"><option value="">' . _( 'N/A' ) . '</option>';

		foreach ( (array) $teachers_RET as $teacher )
		{
			echo '<option value="' . AttrEscape( $teacher['STAFF_ID'] ) . '">' . $teacher['FULL_NAME'] . '</option>';
		}

		echo '</select>' . FormatInputTitle( _( 'Teacher' ) ) . '</label></td></tr>';

		echo '<tr class="st"><td><label><select name="without_period_id"><option value="">' . _( 'N/A' ) . '</option>';

		foreach ( (array) $periods_RET as $period )
		{
			echo '<option value="' . AttrEscape( $period['PERIOD_ID'] ) . '">' . $period['TITLE'] . '</option>';
		}

		echo '</select>' . FormatInputTitle( _( 'Period' ) ) . '</label></td></tr></table>';
		echo '</fieldset></td></tr></table>';

		PopTable( 'footer' );

		echo '<br />';
	}
}

if ( ! $_REQUEST['modfunc'] )
{
	if ( $_REQUEST['search_modfunc'] != 'list' )
	{
		unset( $_SESSION['MassRequests.php'] );
	}

	$extra['link'] = [ 'FULL_NAME' => false ];
	$extra['SELECT'] = ",NULL AS CHECKBOX";
	$extra['functions'] = [ 'CHECKBOX' => 'MakeChooseCheckbox' ];
	$extra['columns_before'] = [ 'CHECKBOX' => MakeChooseCheckbox( 'required', 'STUDENT_ID', 'student' ) ];
	$extra['new'] = true;

	Widgets( 'request' );
	MyWidgets( 'ly_course' );
	//Widgets('activity');

	Search( 'student_id', $extra );

	if ( $_REQUEST['search_modfunc'] === 'list' )
	{
		echo '<br /><div class="center">' . SubmitButton( _( 'Add Request to Selected Students' ) ) . "</div></form>";
	}
}

if ( $_REQUEST['modfunc'] == 'choose_course' )
{
	if ( empty( $_REQUEST['course_id'] ) )
	{
		include 'modules/Scheduling/Courses.php';
	}
	else
	{
		$_SESSION['MassRequests.php']['subject_id'] = issetVal( $_REQUEST['subject_id'] );
		$_SESSION['MassRequests.php']['course_id'] = issetVal( $_REQUEST['course_id'] );

		$course_title = ParseMLField( DBGetOne( "SELECT TITLE
			FROM courses
			WHERE COURSE_ID='" . (int) $_SESSION['MassRequests.php']['course_id'] . "'" ) );

		// @since 12.0 Use colorBox instead of popup window
		// @since 12.5 CSP remove unsafe-inline Javascript
		?>
		<input type="hidden" disabled id="course_div_html" value="<?php echo AttrEscape( $course_title ); ?>" />
		<script src="assets/js/csp/modules/scheduling/MassRequests.js?v=12.5"></script>
		<?php
	}
}
