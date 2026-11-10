<?php
/**
 * Account Exists (Food Service) Staff Widget class
 *
 * @since 12.7
 *
 * @package RosarioSIS
 */

namespace RosarioSIS\StaffWidget;

class FsaExists implements \RosarioSIS\StaffWidget
{
	function canBuild( $modules )
	{
		return $modules['Food_Service'];
	}

	function extra( $extra )
	{
		if ( empty( $_REQUEST['fsa_exists'] ) )
		{
			return $extra;
		}

		$extra['WHERE'] .= ' AND ' . ( $_REQUEST['fsa_exists'] == 'N' ? 'NOT ' : '' ) . "EXISTS
			(SELECT 'exists'
				FROM food_service_staff_accounts
				WHERE STAFF_ID=s.STAFF_ID)";

		if ( ! $extra['NoSearchTerms'] )
		{
			$extra['SearchTerms'] .= '<b>' . _( 'Food Service Account Exists' ) . ': </b>' .
				( $_REQUEST['fsa_exists'] == 'Y' ? _( 'Yes' ) : _( 'No' ) ) . '<br />';
		}

		return $extra;
	}

	function html( $value = '' )
	{
		return '<tr class="st"><td>' . _( 'Has Account' ) . '</td><td>
		<label><input type="radio" name="fsa_exists" value=""' . ( empty( $value ) ? ' checked' : '' ) . '> ' .
			_( 'All') . '</label> &nbsp;
		<label><input type="radio" name="fsa_exists" value="Y"' . ( $value == 'Y' ? ' checked' : '' ).'> '.
			_( 'Yes' ) . '</label> &nbsp;
		<label><input type="radio" name="fsa_exists" value="N"' . ( $value == 'N' ? ' checked' : '' ) . '> '.
			_( 'No' ) . '</label>
		</td></tr>';
	}
}
