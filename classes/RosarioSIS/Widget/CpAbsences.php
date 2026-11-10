<?php
/**
 * Course Period Absences (Attendance) Widget class
 * for admins only (relies on the Course widget).
 *
 * @since 12.7
 *
 * @package RosarioSIS
 */

namespace RosarioSIS\Widget;

class CpAbsences implements \RosarioSIS\Widget
{
	function canBuild( $modules )
	{
		return $modules['Attendance']
			&& User( 'PROFILE' ) === 'admin';
	}

	function extra( $extra )
	{
		if ( ! isset( $_REQUEST['cp_absences_low'] )
			|| ! is_numeric( $_REQUEST['cp_absences_low'] )
			|| ! isset( $_REQUEST['cp_absences_high'] )
			|| ! is_numeric( $_REQUEST['cp_absences_high'] )
			|| ! isset( $_REQUEST['w_course_period_id'] )
			|| ! is_numeric( $_REQUEST['w_course_period_id'] ) )
		{
			return $extra;
		}

		if ( $_REQUEST['cp_absences_low'] > $_REQUEST['cp_absences_high'] )
		{
			$temp = $_REQUEST['cp_absences_high'];

			$_REQUEST['cp_absences_high'] = $_REQUEST['cp_absences_low'];

			$_REQUEST['cp_absences_low'] = $temp;
		}


		// Set Term SQL condition, if not Full Year.
		$term_sql = '';

		if ( $_REQUEST['cp_absences_term'] !== 'FY' )
		{
			$term_sql = " AND ap.MARKING_PERIOD_ID
				IN(" . GetChildrenMP( $_REQUEST['cp_absences_term'], UserMP() ) . ")";
		}

		// Set Absences number SQL condition.
		$absences_sql = $_REQUEST['cp_absences_low'] == $_REQUEST['cp_absences_high'] ?
			" = '" . $_REQUEST['cp_absences_low'] . "'" :
			" BETWEEN '" . $_REQUEST['cp_absences_low'] . "'
				AND '" . $_REQUEST['cp_absences_high'] . "'";

		$extra['WHERE'] .= " AND (SELECT count(*)
			FROM attendance_period ap,attendance_codes ac
			WHERE ac.ID=ap.ATTENDANCE_CODE
			AND ac.STATE_CODE='A'
			AND ap.COURSE_PERIOD_ID='" . (int) $_REQUEST['w_course_period_id'] . "'" .
			$term_sql .
			" AND ap.STUDENT_ID=ssm.STUDENT_ID)" .
			$absences_sql;

		switch ( $_REQUEST['cp_absences_term'] )
		{
			case 'FY':

				$term = _( 'this school year to date' );

			break;

			case 'SEM':

				$term = _( 'this semester to date' );

			break;

			case 'QTR':

				$term = _( 'this marking period to date' );

			break;
		}

		if ( ! $extra['NoSearchTerms'] )
		{
			$extra['SearchTerms'] .= '<b>' . _( 'Course Period Absences' ) . ' ' .
				$term . ': </b>' . _( 'Between' ) . ' ' .
				$_REQUEST['cp_absences_low'] . ' &amp; ' .
				$_REQUEST['cp_absences_high'] . '<br />';
		}

		return $extra;
	}

	function html()
	{
		return '<tr class="st"><td>' .	_( 'Course Period Absences' ) .
		'<div class="tooltip"><i>' .
			_( 'Use the Choose link of the Course widget (under Scheduling) to select a Course Period.' ) .
		'</i></div>' .
		'<br />
		<label title="' . AttrEscape( _( 'this school year to date' ) ) . '">
			<input type="radio" name="cp_absences_term" value="FY" checked>&nbsp;' .
			_( 'YTD' ) .
		'</label> &nbsp;
		<label title="' . AttrEscape( _( 'this semester to date' ) ) . '">
			<input type="radio" name="cp_absences_term" value="SEM">&nbsp;' .
			GetMP( GetParentMP( 'SEM', UserMP() ), 'SHORT_NAME' ) .
		'</label> &nbsp;
		<label title="' . AttrEscape( _( 'this marking period to date' ) ) . '">
			<input type="radio" name="cp_absences_term" value="QTR">&nbsp;' .
			GetMP( UserMP(), 'SHORT_NAME' ) .
		'</label>
		</td><td><label>' . _( 'Between' ) .
		' <input type="number" name="cp_absences_low" min="0" max="999" step="1"></label>' .
		' <label>&amp;' .
		' <input type="number" name="cp_absences_high" min="0" max="999" step="1"></label>
		</td></tr>';
	}
}
