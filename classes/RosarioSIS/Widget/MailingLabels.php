<?php
/**
 * Mailing Labels Widget class
 *
 * @since 12.7
 *
 * @package RosarioSIS
 */

namespace RosarioSIS\Widget;

class MailingLabels implements \RosarioSIS\Widget
{
	function canBuild( $modules )
	{
		return true;
	}

	function extra( $extra )
	{
		if ( ! isset( $_REQUEST['mailing_labels'] )
			|| $_REQUEST['mailing_labels'] !== 'Y' )
		{
			return $extra;
		}

		require_once 'ProgramFunctions/MailingLabel.fnc.php';

		$extra['SELECT'] .= ',coalesce(saml.ADDRESS_ID,-ssm.STUDENT_ID) AS ADDRESS_ID,
			saml.ADDRESS_ID AS MAILING_LABEL';

		$extra['FROM'] = " LEFT OUTER JOIN students_join_address saml
			ON (saml.STUDENT_ID=ssm.STUDENT_ID
				AND saml.MAILING='Y'" .
				( isset( $_REQUEST['residence'] ) && $_REQUEST['residence'] == 'Y' ? " AND saml.RESIDENCE='Y'" : '' ) . ")" .
			$extra['FROM'];

		$extra['functions'] += [ 'MAILING_LABEL' => 'MailingLabel' ];

		return $extra;
	}

	function html()
	{
		if ( ! empty( $_REQUEST['search_modfunc'] ) )
		{
			return '<tr class="st"><td colspan="2">' .
			'<label><input type="checkbox" id="mailing_labels" name="mailing_labels" value="Y">&nbsp;' .
				_( 'Mailing Labels' ) . '</label></td>';
		}

		return '<tr class="st"><td>' .
			'<label for="mailing_labels">' . _( 'Mailing Labels' ) . '</label>' .
			'</td><td>' .
			'<input type="checkbox" id="mailing_labels" name="mailing_labels" value="Y">' .
			'</td>';
	}
}
