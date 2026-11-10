<?php
/**
 * Account Barcode (Food Service) Widget class
 *
 * @since 12.7
 *
 * @package RosarioSIS
 */

namespace RosarioSIS\Widget;

class FsaBarcode implements \RosarioSIS\Widget
{
	function canBuild( $modules )
	{
		return $modules['Food_Service'];
	}

	function extra( $extra )
	{
		if ( empty( $_REQUEST['fsa_barcode'] ) )
		{
			return $extra;
		}

		if ( ! mb_strpos( $extra['FROM'], 'fssa' ) )
		{
			$extra['FROM'] .= ",food_service_student_accounts fssa";

			$extra['WHERE'] .= " AND fssa.STUDENT_ID=s.STUDENT_ID";
		}

		$extra['WHERE'] .= " AND fssa.BARCODE='" . $_REQUEST['fsa_barcode'] . "'";

		if ( ! $extra['NoSearchTerms'] )
		{
			$extra['SearchTerms'] .= '<b>' . _( 'Food Service Barcode' ) . ': </b>' .
				$_REQUEST['fsa_barcode'] . '<br />';
		}

		return $extra;
	}

	function html()
	{
		return '<tr class="st"><td><label for="fsa_barcode">' . _( 'Barcode' ) .
		'</label></td><td>
		<input type="text" name="fsa_barcode" id="fsa_barcode" size="15" maxlength="50">
		</td></tr>';
	}
}
