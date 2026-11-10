<?php
/**
 * Account ID (Food Service) Widget class
 *
 * @since 12.7
 *
 * @package RosarioSIS
 */

namespace RosarioSIS\Widget;

class FsaAccountId implements \RosarioSIS\Widget
{
	function canBuild( $modules )
	{
		return $modules['Food_Service'];
	}

	function extra( $extra )
	{
		if ( ! isset( $_REQUEST['fsa_account_id'] )
			|| ! is_numeric( $_REQUEST['fsa_account_id'] ) )
		{
			return $extra;
		}

		if ( ! mb_strpos( $extra['FROM'], 'fssa' ) )
		{
			$extra['FROM'] .= ",food_service_student_accounts fssa";

			$extra['WHERE'] .= " AND fssa.STUDENT_ID=s.STUDENT_ID";
		}

		$extra['WHERE'] .= " AND fssa.ACCOUNT_ID='" . (int) $_REQUEST['fsa_account_id'] . "'";

		if ( ! $extra['NoSearchTerms'] )
		{
			$extra['SearchTerms'] .= '<b>' . _( 'Food Service Account ID' ) . ': </b>' .
				(int) $_REQUEST['fsa_account_id'] . '<br />';
		}

		return $extra;
	}

	function html()
	{
		return '<tr class="st"><td><label for="fsa_account_id">' . _( 'Account ID' ) . '</label></td><td>
		<input type="text" name="fsa_account_id" id="fsa_account_id" size="5" maxlength="9">
		</td></tr>';
	}
}
