<?php
/**
 * Account Status (Food Service) Staff Widget class
 *
 * @since 12.7
 *
 * @package RosarioSIS
 */

namespace RosarioSIS\StaffWidget;

class FsaStatus implements \RosarioSIS\StaffWidget
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
			$extra['FROM'] .= ',food_service_staff_accounts fssa';

			$extra['WHERE'] .= ' AND fssa.STAFF_ID=s.STAFF_ID';
		}

		if ( $_REQUEST['fsa_status'] == 'Active' )
		{
			$extra['WHERE'] .= ' AND fssa.STATUS IS NULL';
		}
		else
			$extra['WHERE'] .= " AND fssa.STATUS='" . $_REQUEST['fsa_status'] . "'";

		if ( ! $extra['NoSearchTerms'] )
		{
			$extra['SearchTerms'] .= '<b>' . _( 'Food Service Status' ) . ': </b>' .
				$_REQUEST['fsa_status'] . '<br />';
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
