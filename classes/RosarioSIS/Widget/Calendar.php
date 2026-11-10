<?php
/**
 * Calendar (Enrollment) Widget class
 *
 * @since 12.7
 *
 * @package RosarioSIS
 */

namespace RosarioSIS\Widget;

class Calendar implements \RosarioSIS\Widget
{
	function canBuild( $modules )
	{
		return $modules['Students'];
	}

	function extra( $extra )
	{
		if ( empty( $_REQUEST['calendar'] ) )
		{
			return $extra;
		}

		if ( $_REQUEST['calendar'] === '!' )
		{
			$where_not = ( isset( $_REQUEST['calendar_not'] ) && $_REQUEST['calendar_not'] === 'Y' ?
				'NOT ' : '' );

			$extra['WHERE'] .= " AND ssm.CALENDAR_ID IS " . $where_not . "NULL";

			$text_not = ( isset( $_REQUEST['calendar_not'] ) && $_REQUEST['calendar_not'] === 'Y' ?
				_( 'Any Value' ) : _( 'No Value' ) );
		}
		else
		{
			$where_not = ( isset( $_REQUEST['calendar_not'] ) && $_REQUEST['calendar_not'] === 'Y' ?
				'!' : '' );

			$extra['WHERE'] .= " AND ssm.CALENDAR_ID" . $where_not . "='" . (int) $_REQUEST['calendar'] . "'";

			$calendars_RET = DBGet( "SELECT CALENDAR_ID,TITLE
				FROM attendance_calendars
				WHERE SYEAR='" . UserSyear() . "'
				AND SCHOOL_ID='" . UserSchool() . "'
				ORDER BY DEFAULT_CALENDAR ASC" );

			foreach ( (array) $calendars_RET as $calendar )
			{
				if ( $_REQUEST['calendar'] === $calendar['CALENDAR_ID'] )
				{
					$calendar_title = $calendar['TITLE'];

					break;
				}
			}

			$text_not = ( isset( $_REQUEST['calendar_not'] ) && $_REQUEST['calendar_not'] == 'Y' ?
				_( 'Not' ) . ' ' : '' ) . $calendar_title;
		}

		if ( ! $extra['NoSearchTerms'] )
		{
			$extra['SearchTerms'] .= '<b>' . _( 'Calendar' ) . ': </b>' . $text_not . '<br />';
		}

		return $extra;
	}

	function html()
	{
		$calendars_RET = DBGet( "SELECT CALENDAR_ID,TITLE
			FROM attendance_calendars
			WHERE SYEAR='" . UserSyear() . "'
			AND SCHOOL_ID='" . UserSchool() . "'
			ORDER BY DEFAULT_CALENDAR ASC" );

		$html = '<tr class="st"><td><label for="calendar_input">' . _( 'Calendar' ) . '</label></td><td>
		<label>
			<input type="checkbox" name="calendar_not" value="Y"> ' . _( 'Not' ) .
		'</label>
		<select name="calendar" id="calendar_input">
			<option value="">' . _( 'N/A' ) . '</option>
			<option value="!">' . _( 'No Value' ) . '</option>';

		foreach ( (array) $calendars_RET as $calendar )
		{
			$html .= '<option value="' . AttrEscape( $calendar['CALENDAR_ID'] ) . '">' .
				$calendar['TITLE'] . '</option>';
		}

		return $html . '</select></td></tr>';
	}
}
