<?php
/**
 * Account Discount (Food Service) Widget class
 *
 * @since 12.7
 *
 * @package RosarioSIS
 */

namespace RosarioSIS\Widget;

class FsaDiscount implements \RosarioSIS\Widget
{
	function canBuild( $modules )
	{
		return $modules['Food_Service'];
	}

	function extra( $extra )
	{
		if ( empty( $_REQUEST['fsa_discount'] ) )
		{
			return $extra;
		}

		if ( ! mb_strpos($extra['FROM'], 'fssa' ) )
		{
			$extra['FROM'] .= ",food_service_student_accounts fssa";

			$extra['WHERE'] .= " AND fssa.STUDENT_ID=s.STUDENT_ID";
		}

		$extra['WHERE'] .= $_REQUEST['fsa_discount'] == 'Full' ?
			" AND fssa.DISCOUNT IS NULL" :
			" AND fssa.DISCOUNT='" . $_REQUEST['fsa_discount'] . "'";

		if ( ! $extra['NoSearchTerms'] )
		{
			$extra['SearchTerms'] .= '<b>' . _( 'Food Service Discount' ) . ': </b>' .
				_( $_REQUEST['fsa_discount'] ) . '<br />';
		}

		return $extra;
	}

	function html()
	{
		return '<tr class="st"><td><label for="fsa_discount">' . _( 'Discount' ) . '</label></td><td>
		<select name="fsa_discount" id="fsa_discount">
		<option value="">' . _( 'Not Specified' ) . '</option>
		<option value="Full">' . _( 'Full' ) . '</option>
		<option value="Reduced">' . _( 'Reduced' ) . '</option>
		<option value="Free">' . _( 'Free' ) . '</option>
		</select>
		</td></tr>';
	}
}
