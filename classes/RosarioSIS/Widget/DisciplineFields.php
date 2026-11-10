<?php
/**
 * Discipline Fields (Discipline) Widget class
 *
 * @since 12.7
 *
 * @package RosarioSIS
 */

namespace RosarioSIS\Widget;

class DisciplineFields implements \RosarioSIS\Widget
{
	function canBuild( $modules )
	{
		return $modules['Discipline'];
	}

	function extra( $extra )
	{
		if ( ! empty( $_REQUEST['discipline'] ) )
		{
			foreach ( (array) $_REQUEST['discipline'] as $key => $value )
			{
				if ( ! $_REQUEST['discipline'][ $key ] )
				{
					unset( $_REQUEST['discipline'][ $key ] );
				}
			}
		}

		if ( ! empty( $_REQUEST['discipline_begin'] ) )
		{
			foreach ( (array) $_REQUEST['discipline_begin'] as $key => $value )
			{
				if ( ! is_numeric( $_REQUEST['discipline_begin'][ $key ] ) )
				{
					unset( $_REQUEST['discipline_begin'][ $key ] );
				}
			}
		}

		if ( ! empty( $_REQUEST['discipline_end'] ) )
		{
			foreach ( (array) $_REQUEST['discipline_end'] as $key => $value )
			{
				if ( ! is_numeric( $_REQUEST['discipline_end'][ $key ] ) )
				{
					unset( $_REQUEST['discipline_end'][ $key ] );
				}
			}
		}

		if ( empty( $_REQUEST['discipline'] )
			&& empty( $_REQUEST['discipline_begin'] )
			&& empty( $_REQUEST['discipline_end'] ) )
		{
			return $extra;
		}

		if ( mb_stripos( $extra['FROM'], 'discipline_referrals dr' ) === false )
		{
			// Fix SQL error invalid reference to FROM-clause entry for table "ssm"
			// Add JOIN just after JOIN student_enrollment ssm
			$extra['FROM'] = ' LEFT JOIN discipline_referrals dr
				ON (dr.STUDENT_ID=ssm.STUDENT_ID
				AND dr.SYEAR=ssm.SYEAR
				AND dr.SCHOOL_ID=ssm.SCHOOL_ID) ' . $extra['FROM'];
		}

		$extra = $this->_disciplineFieldsSearch( $extra );

		return $extra;
	}

	private function _disciplineFieldsSearch( $extra )
	{
		$categories_RET = DBGet( "SELECT f.ID,u.TITLE,f.DATA_TYPE,u.SELECT_OPTIONS
			FROM discipline_fields f,discipline_field_usage u
			WHERE u.DISCIPLINE_FIELD_ID=f.ID
			AND u.SYEAR='" . UserSyear() . "'
			AND u.SCHOOL_ID='" . UserSchool() . "'
			AND f.DATA_TYPE!='textarea'
			AND f.DATA_TYPE!='date'" );

		foreach ( (array) $categories_RET as $category )
		{
			switch ( $category['DATA_TYPE'] )
			{
				case 'text':

					if ( ! empty( $_REQUEST['discipline'][ $category['ID'] ] ) )
					{
						$extra['WHERE'] .= " AND dr.CATEGORY_" . $category['ID'] .
							" LIKE '" . $_REQUEST['discipline'][ $category['ID'] ] . "%' ";

						if ( ! $extra['NoSearchTerms'] )
						{
							$extra['SearchTerms'] .= '<b>' . $category['TITLE'] . ': </b> ' .
								$_REQUEST['discipline'][ $category['ID'] ] . '<br />';
						}
					}

				break;

				case 'checkbox':

					if ( ! empty( $_REQUEST['discipline'][ $category['ID'] ] ) )
					{
						$extra['WHERE'] .= " AND dr.CATEGORY_" . $category['ID'] . "='Y' ";

						if ( ! $extra['NoSearchTerms'] )
						{
							$extra['SearchTerms'] .= '<b>' . $category['TITLE'] . '</b><br />';
						}
					}

				break;

				case 'numeric':

					// Fix search discipline numeric field for "0" value
					if ( isset( $_REQUEST['discipline_begin'][ $category['ID'] ] )
						&& is_numeric( $_REQUEST['discipline_begin'][ $category['ID'] ] )
						&& isset( $_REQUEST['discipline_end'][ $category['ID'] ] )
						&& is_numeric( $_REQUEST['discipline_end'][ $category['ID'] ] ) )
					{
						$discipline_begin = $_REQUEST['discipline_begin'][ $category['ID'] ];
						$discipline_end = $_REQUEST['discipline_end'][ $category['ID'] ];

						if ( $discipline_begin > $discipline_end )
						{
							// Numeric Discipline field: invert values so BETWEEN works.
							$discipline_begin = $_REQUEST['discipline_end'][ $category['ID'] ];
							$discipline_end = $_REQUEST['discipline_begin'][ $category['ID'] ];
						}

						$extra['WHERE'] .= " AND dr.CATEGORY_" . $category['ID'] .
							" BETWEEN '" . (int) $discipline_begin .
							"' AND '" . (int) $discipline_end . "' ";

						if ( ! $extra['NoSearchTerms'] )
						{
							$extra['SearchTerms'] .= '<b>' . $category['TITLE'] . ' ' . _( 'Between' ) . ': </b>' .
								$discipline_begin . ' &amp; ' .
								$discipline_end . '<br />';
						}
					}

				break;

				case 'multiple_checkbox':
				case 'multiple_radio':
				case 'select':

					if ( ! empty( $_REQUEST['discipline'][ $category['ID'] ] ) )
					{
						if ( $category['DATA_TYPE'] == 'multiple_radio'
							|| $category['DATA_TYPE'] == 'select' )
						{
							$extra['WHERE'] .= " AND dr.CATEGORY_" . $category['ID'] .
								" = '" . $_REQUEST['discipline'][ $category['ID'] ] . "' ";
						}
						elseif ( $category['DATA_TYPE'] == 'multiple_checkbox' )
						{
							$extra['WHERE'] .= " AND dr.CATEGORY_" . $category['ID'] .
								" LIKE '%||" . $_REQUEST['discipline'][ $category['ID'] ] . "||%' ";
						}

						if ( ! $extra['NoSearchTerms'] )
						{
							$extra['SearchTerms'] .= '<b>' . $category['TITLE'] . ': </b>' .
								$_REQUEST['discipline'][ $category['ID'] ] . '<br />';
						}
					}

				break;
			}
		}

		return $extra;
	}

	function html()
	{
		$categories_RET = DBGet( "SELECT f.ID,u.TITLE,f.DATA_TYPE,u.SELECT_OPTIONS
			FROM discipline_fields f,discipline_field_usage u
			WHERE u.DISCIPLINE_FIELD_ID=f.ID
			AND u.SYEAR='" . UserSyear() . "'
			AND u.SCHOOL_ID='" . UserSchool() . "'
			AND f.DATA_TYPE!='textarea'
			AND f.DATA_TYPE!='date'" );

		$html = '';

		foreach ( (array) $categories_RET as $category )
		{
			$input_name = 'discipline[' . $category['ID'] . ']';

			if ( $category['DATA_TYPE'] !== 'numeric' )
			{
				$input_id = GetInputID( $input_name );

				$html .= '<tr class="st"><td><label for="' . $input_id . '">' .
					$category['TITLE'] . '</label></td><td>';
			}
			else
			{
				$html .= '<tr class="st"><td>' . $category['TITLE'] . '</td><td>';
			}

			switch ( $category['DATA_TYPE'] )
			{
				case 'text':

					$html .= '<input type="text" name="' . AttrEscape( $input_name ) .
						'" id="' . $input_id . '" size="24" maxlength="255">';

				break;

				case 'checkbox':

					$html .= '<input type="checkbox" name="' . AttrEscape( $input_name ) .
						'" id="' . $input_id . '" value="Y">';

				break;

				case 'numeric':

					$html .= '<label>' . _( 'Between' ) .
						' <input type="number" name="discipline_begin[' . $category['ID'] .
							']" min="-999999999999999999" max="999999999999999999"></label>' .
						' <label>&amp;' .
						' <input type="number" name="discipline_end[' . $category['ID'] .
							']" min="-999999999999999999" max="999999999999999999"></label>';

				break;

				case 'multiple_checkbox':
				case 'multiple_radio':
				case 'select':

					$category['SELECT_OPTIONS'] = explode( "\r", str_replace( [ "\r\n", "\n" ], "\r", $category['SELECT_OPTIONS'] ) );

					$html .= '<select name="' . AttrEscape( $input_name ) . '" id="' . $input_id . '">
						<option value="">' . _( 'N/A' ) . '</option>';

					foreach ( (array) $category['SELECT_OPTIONS'] as $option )
					{
						$html .= '<option value="' . AttrEscape( $option ) . '">' . $option . '</option>';
					}

					$html .= '</select>';

				break;
			}

			$html .= '</td></tr>';
		}

		return $html;
	}
}
