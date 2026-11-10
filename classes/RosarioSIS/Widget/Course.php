<?php
/**
 * Course (Scheduling) Widget class
 *
 * @since 12.7
 *
 * @package RosarioSIS
 */

namespace RosarioSIS\Widget;

class Course implements \RosarioSIS\Widget
{
	function canBuild( $modules )
	{
		return $modules['Scheduling']
			&& User( 'PROFILE' ) === 'admin';
	}

	function extra( $extra )
	{
		if ( empty( $_REQUEST['w_course_period_id'] ) )
		{
			return $extra;
		}

		// @since 6.5 Course Widget: add Subject and Not options.
		$extra['WHERE'] .= ! empty( $_REQUEST['w_course_period_id_not'] ) ?
			" AND NOT " : " AND ";

		if ( $_REQUEST['w_course_period_id_which'] === 'subject' )
		{
			$extra['WHERE'] .= " EXISTS(SELECT 1
				FROM schedule w_ss
				WHERE w_ss.STUDENT_ID=s.STUDENT_ID
				AND w_ss.SYEAR=ssm.SYEAR
				AND w_ss.SCHOOL_ID=ssm.SCHOOL_ID
				AND w_ss.COURSE_ID IN(SELECT COURSE_ID
					FROM courses
					WHERE SUBJECT_ID='" . (int) $_REQUEST['w_subject_id'] . "'
					AND SYEAR=ssm.SYEAR
					AND SCHOOL_ID=ssm.SCHOOL_ID)";

			$subject_title = ParseMLField( DBGetOne( "SELECT TITLE
				FROM course_subjects
				WHERE SUBJECT_ID='" . (int) $_REQUEST['w_subject_id'] . "'" ) );

			if ( ! $extra['NoSearchTerms'] )
			{
				$extra['SearchTerms'] .= '<b>' . _( 'Subject' ) . ': </b>'.
					( ! empty( $_REQUEST['w_course_period_id_not'] ) ? _( 'Not' ) . ' ' : '' ) .
					$subject_title . '<br />';
			}
		}
		// Course.
		elseif ( $_REQUEST['w_course_period_id_which'] === 'course' )
		{
			$extra['WHERE'] .= " EXISTS(SELECT 1
				FROM schedule w_ss
				WHERE w_ss.STUDENT_ID=s.STUDENT_ID
				AND w_ss.SYEAR=ssm.SYEAR
				AND w_ss.SCHOOL_ID=ssm.SCHOOL_ID
				AND w_ss.COURSE_ID='" . (int) $_REQUEST['w_course_id'] . "'";

			$course_title = ParseMLField( DBGetOne( "SELECT TITLE
				FROM courses
				WHERE COURSE_ID='" . (int) $_REQUEST['w_course_id'] . "'" ) );

			if ( ! $extra['NoSearchTerms'] )
			{
				$extra['SearchTerms'] .= '<b>' . _( 'Course' ) . ': </b>'.
					( ! empty( $_REQUEST['w_course_period_id_not'] ) ? _( 'Not' ) . ' ' : '' ) .
					$course_title . '<br />';
			}
		}
		// Course Period.
		else
		{
			$extra['WHERE'] .= " EXISTS(SELECT 1
				FROM schedule w_ss
				WHERE w_ss.STUDENT_ID=s.STUDENT_ID
				AND w_ss.SYEAR=ssm.SYEAR
				AND w_ss.SCHOOL_ID=ssm.SCHOOL_ID
				AND w_ss.COURSE_PERIOD_ID='" . (int) $_REQUEST['w_course_period_id'] . "'";

			$course = DBGet( "SELECT c.TITLE AS COURSE_TITLE,cp.TITLE,cp.COURSE_ID
				FROM course_periods cp,courses c
				WHERE c.COURSE_ID=cp.COURSE_ID
				AND cp.COURSE_PERIOD_ID='" . (int) $_REQUEST['w_course_period_id'] . "'", [ 'COURSE_TITLE' => 'ParseMLField' ] );

			if ( ! $extra['NoSearchTerms'] )
			{
				$extra['SearchTerms'] .= '<b>' . _( 'Course Period' ) . ': </b>' .
					( ! empty( $_REQUEST['w_course_period_id_not'] ) ? _( 'Not' ) . ' ' : '' ) .
					$course[1]['COURSE_TITLE'] . ': ' . $course[1]['TITLE'] . '<br />';
			}
		}

		$is_include_inactive = isset( $_REQUEST['include_inactive'] ) && $_REQUEST['include_inactive'] === 'Y';

		if ( ! $is_include_inactive )
		{
			// Fix SQL error when no Quarters found
			$all_mp = GetAllMP( 'QTR', UserMP() );

			// Fix check students Course Status.
			$extra['WHERE'] .= " AND '" . DBDate() . "'>=w_ss.START_DATE
				AND ('" . DBDate() . "'<=w_ss.END_DATE OR w_ss.END_DATE IS NULL)
				AND w_ss.MARKING_PERIOD_ID IN (" . ( $all_mp ? $all_mp : '0' ) . ")";
		}

		$extra['WHERE'] .= ")";

		return $extra;
	}

	function html()
	{
		if ( ! Config( 'COURSE_WIDGET_METHOD' ) )
		{
			// Course Widget: Popup window.
			// @since 12.0 Use colorBox instead of popup window
			$html = '<tr class="st"><td>' . _( 'Course' ) . '</td><td>
			<div id="course_div"></div>
			<a href="Modules.php?modname=misc/ChooseCourse.php" class="colorbox">' .
				_( 'Choose' ) .
			'</a>
			</td></tr>';

			return $html;
		}

		// @since 7.4 Add Course Widget: select / Pull-Down.
		$course_periods_RET = DBGet( "SELECT cp.COURSE_PERIOD_ID,cp.TITLE,
		c.COURSE_ID,cs.SUBJECT_ID,cs.TITLE AS SUBJECT_TITLE
		FROM course_periods cp,courses c,course_subjects cs
		WHERE cp.SYEAR='" . UserSyear() . "'
		AND cp.SCHOOL_ID='" . UserSchool() . "'
		AND cp.COURSE_ID=c.COURSE_ID
		AND cs.SUBJECT_ID=c.SUBJECT_ID
		ORDER BY cs.SORT_ORDER IS NULL,cs.SORT_ORDER,cs.TITLE,cp.SHORT_NAME", [ 'SUBJECT_TITLE' => 'ParseMLField' ] );

		$course_period_options = [];

		$subject_group = '';

		foreach ( $course_periods_RET as $course_period )
		{
			if ( $subject_group !== $course_period['SUBJECT_TITLE'] )
			{
				$subject_group = $course_period['SUBJECT_TITLE'];

				$course_period_options[ $subject_group ] = [];
			}

			// Fix 403 Forbidden error due to pipe "|" in URL when using Apache 5G rules.
			$course_period_value = $course_period['SUBJECT_ID'] . ',' .
				$course_period['COURSE_ID'] . ',' . $course_period['COURSE_PERIOD_ID'];

			$course_period_options[ $subject_group ][ $course_period_value ] = $course_period['TITLE'];
		}

		$course_period_chosen_select = SelectInput(
			'',
			'course_period_select',
			'',
			$course_period_options,
			'N/A',
			'group class="onchange-widget-course-id-update" autocomplete="off"'
		);

		$html = '<tr class="st"><td><label for="course_period_select">' . _( 'Course' ) . '</label></td><td>' .
		$course_period_chosen_select .
		'<div id="course_div" class="hide">
		<label><input type="checkbox" name="w_course_period_id_not" value="Y"> ' .
			_( 'Not' ) . '</label>
		<label><select name="w_course_period_id_which" autocomplete="off">
		<option value="course_period"> ' . _( 'Course Period' ) . '</option>
		<option value="course"> ' . _( 'Course' ) . '</option>
		<option value="subject"> ' . _( 'Subject' ) . '</option>
		</select></label>
		<input type="hidden" name="w_course_period_id" value="">
		<input type="hidden" name="w_course_id" value="">
		<input type="hidden" name="w_subject_id" value="">
		</div></td></tr>';

		// @since 12.5 CSP remove unsafe-inline Javascript
		return $html . '<script src="assets/js/csp/widget/Course.js?v=12.5"></script>';
	}
}
