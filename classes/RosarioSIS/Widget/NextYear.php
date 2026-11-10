<?php
/**
 * Next Year (Enrollment) Widget class
 *
 * @since 12.7
 *
 * @package RosarioSIS
 */

namespace RosarioSIS\Widget;

class NextYear implements \RosarioSIS\Widget
{
	function canBuild( $modules )
	{
		return $modules['Students'];
	}

	function extra( $extra )
	{
		$schools_RET = DBGet( "SELECT ID,TITLE
			FROM schools
			WHERE ID!='" . UserSchool() . "'
			AND SYEAR='" . UserSyear() . "'" );

		$next_year_options = [
			'' => _( 'N/A' ),
			'!' => _( 'No Value' ),
			UserSchool() => _( 'Next grade at current school' ),
			'0' => _( 'Retain' ),
			'-1' => _( 'Do not enroll after this school year' ),
		];

		foreach ( (array) $schools_RET as $school )
		{
			$next_year_options[ $school['ID'] ] = $school['TITLE'];
		}

		if ( isset( $_REQUEST['next_year'] )
			&& $_REQUEST['next_year'] !== '' ) // Handle "Retain" case: value is '0'.
		{
			$extra['WHERE'] .= $_REQUEST['next_year'] == '!' ?
				" AND ssm.NEXT_SCHOOL IS NULL" :
				" AND ssm.NEXT_SCHOOL='" . $_REQUEST['next_year'] . "'";

			if ( ! $extra['NoSearchTerms'] )
			{
				$extra['SearchTerms'] .= '<b>' . _( 'Next Year' ) . ': </b>' .
					$next_year_options[$_REQUEST['next_year']] . '<br />';
			}
		}

		return $extra;
	}

	function html()
	{
		$next_year_options = [
			'' => _( 'N/A' ),
			'!' => _( 'No Value' ),
			UserSchool() => _( 'Next grade at current school' ),
			'0' => _( 'Retain' ),
			'-1' => _( 'Do not enroll after this school year' ),
		];

		$schools_RET = DBGet( "SELECT ID,TITLE
			FROM schools
			WHERE ID!='" . UserSchool() . "'
			AND SYEAR='" . UserSyear() . "'" );

		foreach ( (array) $schools_RET as $school )
		{
			$next_year_options[ $school['ID'] ] = $school['TITLE'];
		}

		$html = '<tr class="st"><td><label for="next_year">' . _( 'Next Year' ) . '</label></td><td>
		<select name="next_year" id="next_year">';

		foreach ( $next_year_options as $id => $option )
		{
			$html .= '<option value="' . AttrEscape( $id ) . '">' . $option . '</option>';
		}

		return $html . '</select></td></tr>';
	}
}
