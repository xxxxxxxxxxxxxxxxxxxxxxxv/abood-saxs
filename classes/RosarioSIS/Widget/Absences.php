<?php
/**
 * Days Absent (Attendance) Widget class
 *
 * @since 12.7
 *
 * @package RosarioSIS
 */

namespace RosarioSIS\Widget;

class Absences implements \RosarioSIS\Widget
{
	function canBuild( $modules )
	{
		return $modules['Attendance'];
	}

	function extra( $extra )
	{
		if ( ! isset( $_REQUEST['absences_low'] )
			|| ! is_numeric( $_REQUEST['absences_low'] )
			|| ! isset( $_REQUEST['absences_high'] )
			|| ! is_numeric( $_REQUEST['absences_high'] ) )
		{
			return $extra;
		}

		if ( $_REQUEST['absences_low'] > $_REQUEST['absences_high'] )
		{
			$temp = $_REQUEST['absences_high'];

			$_REQUEST['absences_high'] = $_REQUEST['absences_low'];

			$_REQUEST['absences_low'] = $temp;
		}

		// Set Absences number SQL condition.
		$absences_sql = $_REQUEST['absences_low'] == $_REQUEST['absences_high'] ?
			" = '" . $_REQUEST['absences_low'] . "'" :
			" BETWEEN '" . $_REQUEST['absences_low'] . "'
				AND '" . $_REQUEST['absences_high'] . "'";

		$extra['WHERE'] .= " AND (SELECT sum(1-STATE_VALUE) AS STATE_VALUE
			FROM attendance_day ad
			WHERE ssm.STUDENT_ID=ad.STUDENT_ID
			AND ad.SYEAR=ssm.SYEAR
			AND ad.MARKING_PERIOD_ID IN (" . GetChildrenMP( $_REQUEST['absences_term'], UserMP() ) . "))" .
			$absences_sql;

		switch ( $_REQUEST['absences_term'] )
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
			$extra['SearchTerms'] .= '<b>' . _( 'Days Absent' ) . ' ' . $term . ': </b>' .
				_( 'Between' ) . ' ' .
				$_REQUEST['absences_low'] . ' &amp; ' . $_REQUEST['absences_high'] . '<br />';
		}

		return $extra;
	}

	function html()
	{
		return '<tr class="st"><td>' .	_( 'Days Absent' ) .
		'<br />
		<label title="' . AttrEscape( _( 'this school year to date' ) ) . '">
			<input type="radio" name="absences_term" value="FY" checked>&nbsp;' .
			_( 'YTD' ) .
		'</label> &nbsp;
		<label title="' . AttrEscape( _( 'this semester to date' ) ) . '">
			<input type="radio" name="absences_term" value="SEM">&nbsp;' .
			GetMP( GetParentMP( 'SEM', UserMP() ), 'SHORT_NAME' ) .
		'</label> &nbsp;
		<label title="' . AttrEscape( _( 'this marking period to date' ) ) . '">
			<input type="radio" name="absences_term" value="QTR">&nbsp;' .
			GetMP( UserMP(), 'SHORT_NAME' ) .
		'</label>
		</td><td><label>' . _( 'Between' ) .
		' <input type="number" name="absences_low" min="0" max="999" step="0.5"></label>' .
		' <label>&amp; ' .
		'<input type="number" name="absences_high" min="0" max="999" step="0.5"><label>
		</td></tr>';
	}
}
