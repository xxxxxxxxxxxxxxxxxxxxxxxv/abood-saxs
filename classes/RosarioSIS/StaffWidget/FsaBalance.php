<?php
/**
 * Account Balance (Food Service) Staff Widget class
 *
 * @since 12.7
 *
 * @package RosarioSIS
 */

namespace RosarioSIS\StaffWidget;

class FsaBalance implements \RosarioSIS\StaffWidget
{
	function canBuild( $modules )
	{
		return $modules['Food_Service'];
	}

	function extra( $extra )
	{
		if ( ! isset( $_REQUEST['fsa_balance'] )
			|| ! is_numeric( $_REQUEST['fsa_balance'] ) )
		{
			return $extra;
		}

		if ( ! mb_strpos( $extra['FROM'], 'fssa' ) )
		{
			$extra['FROM'] .= ',food_service_staff_accounts fssa';

			$extra['WHERE'] .= ' AND fssa.STAFF_ID=s.STAFF_ID';
		}

		$extra['WHERE'] .= " AND fssa.BALANCE" . ( ! empty( $_REQUEST['fsa_bal_ge'] ) ? '>=' : '<' ) .
			"'" . round( $_REQUEST['fsa_balance'], 2 ) . "'";

		if ( ! $extra['NoSearchTerms'] )
		{
			$extra['SearchTerms'] .= '<b>' . _( 'Food Service Balance' ) . ' </b>
				<span class="sizep2">' . ( ! empty( $_REQUEST['fsa_bal_ge'] ) ? '&ge;' : '&lt;' ) . '</span> ' .
				Currency( $_REQUEST['fsa_balance'] ) . '<br />';
		}

		return $extra;
	}

	function html( $value = '' )
	{
		return '<tr class="st"><td><label for="fsa_balance">' . _( 'Balance' ) . '</label></td><td>
		<label class="sizep2">
			<input type="radio" name="fsa_bal_ge" value="" checked> &lt;</label>&nbsp;
		<label  class="sizep2">
			<input type="radio" name="fsa_bal_ge" value="Y"> &ge;</label>
		<input name="fsa_balance" id="fsa_balance" type="number" step="0.01"' .
			( $value ? ' value="' . AttrEscape( $value ) . '"' : '') . ' min="-999999999999999" max="999999999999999">
		</td></tr>';
	}
}
