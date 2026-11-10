<?php
/**
 * Account Status (Food Service) Widget class
 *
 * @since 12.7
 *
 * @package RosarioSIS
 */

namespace RosarioSIS\Widget;

class FsaStatus implements \RosarioSIS\Widget
{
	function canBuild( $modules )
	{
		return $modules['Food_Service'];
	}

	function extra( $extra )
	{
		if ( empty( $_REQUEST['fsa_status'] ) )
		{
			return $extra;
		}

		if ( ! mb_strpos( $extra['FROM'], 'fssa' ) )
		{
			$extra['FROM'] .= ",food_service_student_accounts fssa";

			$extra['WHERE'] .= " AND fssa.STUDENT_ID=s.STUDENT_ID";
		}

		$extra['WHERE'] .= $_REQUEST['fsa_status'] == 'Active' ?
			" AND fssa.STATUS IS NULL" :
			" AND fssa.STATUS='" . $_REQUEST['fsa_status'] . "'";

		if ( ! $extra['NoSearchTerms'] )
		{
			$extra['SearchTerms'] .= '<b>' . _( 'Account Status' ) . ': </b>' .
				_( $_REQUEST['fsa_status'] ) . '<br />';
		}

		return $extra;
	}

	function html( $value = '' )
	{
		return '<tr class="st"><td><label for="fsa_status">' . _( 'Account Status' ) . '</label></td><td>
		<select name="fsa_status" id="fsa_status">
		<option value="">' . _( 'Not Specified' ) . '</option>
		<option value="Active"' . ( $value == 'active' ? ' selected' : '' ) . '>' . _( 'Active' ) . '</option>
		<option value="Inactive">' . _( 'Inactive' ) . '</option>
		<option value="Disabled">' . _( 'Disabled' ) . '</option>
		<option value="Closed">' . _( 'Closed' ) . '</option>
		</select>
		</td></tr>';
	}
}
