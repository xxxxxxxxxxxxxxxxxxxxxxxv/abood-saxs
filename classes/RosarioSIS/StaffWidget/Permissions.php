<?php
/**
 * Permissions (Users) Staff Widget class
 *
 * @since 12.7
 *
 * @package RosarioSIS
 */

namespace RosarioSIS\StaffWidget;

class Permissions implements \RosarioSIS\StaffWidget
{
	function canBuild( $modules )
	{
		return $modules['Users'];
	}

	function extra( $extra )
	{
		if ( empty( $_REQUEST['permissions'] ) )
		{
			return $extra;
		}

		$extra['WHERE'] .= " AND s.PROFILE_ID IS " . ( $_REQUEST['permissions'] == 'Y' ? 'NOT' : '' ) . " NULL
			AND s.PROFILE!='none'";

		if ( ! $extra['NoSearchTerms'] )
		{
			$extra['SearchTerms'] .= '<b>' . _( 'Permissions' ) . ': </b>' .
				( $_REQUEST['permissions'] == 'Y' ? _( 'Profile' ) : _( 'Custom' ) ) . '<br />';
		}

		return $extra;
	}

	function html( $value = '' )
	{
		return '<tr class="st"><td>' .	_( 'Permissions' ) . '</td><td>
		<label><input type="radio" name="permissions" value=""' . ( empty( $value ) ? ' checked' : '' ) . '> ' .
			_( 'All' ) . '</label> &nbsp;
		<label><input type="radio" name="permissions" value="Y"' . ( $value == 'Y' ? ' checked' : '' ) . '> ' .
			_( 'Profile' ) . '</label> &nbsp;
		<label><input type="radio" name="permissions" value="N"' . ( $value == 'N' ? ' checked' : '' ) . '> ' .
			_( 'Custom' ) . '</label>
		</td></tr>';
	}
}
