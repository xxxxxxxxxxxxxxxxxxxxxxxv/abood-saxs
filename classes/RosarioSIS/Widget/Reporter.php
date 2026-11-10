<?php
/**
 * Reporter (Discipline) Widget class
 *
 * @since 12.7
 *
 * @package RosarioSIS
 */

namespace RosarioSIS\Widget;

class Reporter implements \RosarioSIS\Widget
{
	function canBuild( $modules )
	{
		return $modules['Discipline'];
	}

	function extra( $extra )
	{
		if ( empty( $_REQUEST['discipline_reporter'] ) )
		{
			return $extra;
		}

		if ( mb_stripos( $extra['FROM'], 'discipline_referrals dr' ) === false )
		{
			// Fix SQL error invalid reference to FROM-clause entry for table "ssm"
			// Add JOIN just after JOIN student_enrollment ssm
			$extra['FROM'] = ' LEFT JOIN discipline_referrals dr
				ON (dr.STUDENT_ID=ssm.STUDENT_ID
				AND dr.SYEAR=ssm.SYEAR
				AND dr.SCHOOL_ID=ssm.SCHOOL_ID) ' . $extra['FROM'];
		}

		$extra['WHERE'] .= " AND dr.STAFF_ID='" . (int) $_REQUEST['discipline_reporter'] . "'";

		if ( ! $extra['NoSearchTerms'] )
		{
			$reporter_name = DBGetOne( "SELECT " . DisplayNameSQL() . " AS FULL_NAME
				FROM staff
				WHERE SYEAR='" . UserSyear() . "'
				AND (SCHOOLS IS NULL OR position('," . UserSchool() . ",' IN SCHOOLS)>0)
				AND (PROFILE='admin' OR PROFILE='teacher')
				AND STAFF_ID='" . (int) $_REQUEST['discipline_reporter'] . "'" );

			$extra['SearchTerms'] .= '<b>' . _( 'Reporter' ) . ': </b>' .
				$reporter_name . '<br />';
		}

		return $extra;
	}

	function html()
	{
		$users_RET = DBGet( "SELECT STAFF_ID," . DisplayNameSQL() . " AS FULL_NAME
			FROM staff
			WHERE SYEAR='" . UserSyear() . "'
			AND (SCHOOLS IS NULL OR position('," . UserSchool() . ",' IN SCHOOLS)>0)
			AND (PROFILE='admin' OR PROFILE='teacher')
			ORDER BY FULL_NAME", [], [ 'STAFF_ID' ] );

		$html = '<tr class="st"><td><label for="discipline_reporter">' .
		_( 'Reporter' ) . '</label></td><td>
		<select name="discipline_reporter" id="discipline_reporter">
			<option value="">' . _( 'Not Specified' ) . '</option>';

		foreach ( (array) $users_RET as $id => $user )
		{
			$html .= '<option value="' . AttrEscape( $id ) . '">' .
					$user[1]['FULL_NAME'] .
				'</option>';
		}

		return $html . '</select></td></tr>';
	}
}
