<?php
/**
 * Medical Immunization or Physical (Students) Widget class
 * Called in Search.fnc.php
 *
 * @since 12.7
 *
 * @package RosarioSIS
 */

namespace RosarioSIS\Widget;

class MedicalDate implements \RosarioSIS\Widget
{
	function canBuild( $modules )
	{
		return AllowUse( 'Students/Student.php&category_id=2' );
	}

	function extra( $extra )
	{
		$medical_begin = RequestedDate(
			'medical_begin',
			( issetVal( $_REQUEST['medical_begin'], '' ) )
		);

		$medical_end = RequestedDate(
			'medical_end',
			( issetVal( $_REQUEST['medical_end'], '' ) )
		);

		if ( ! $medical_begin
			&& ! $medical_end )
		{
			return $extra;
		}

		if ( $medical_begin || $medical_end )
		{
			$medical_type = ! empty( $_REQUEST['medical_type'] )
				&& $_REQUEST['medical_type'] === 'Physical' ?
				'Physical' : 'Immunization';

			$extra['WHERE'] .= " AND s.STUDENT_ID IN(SELECT STUDENT_ID
				FROM student_medical
				WHERE TYPE='" . $medical_type . "' ";

			$medical_type_label = $medical_type === 'Physical' ?
				_( 'Physical' ) : _( 'Immunization' );
		}

		if ( $medical_begin
			&& $medical_end )
		{
			$extra['WHERE'] .= " AND MEDICAL_DATE
				BETWEEN '" . $medical_begin .
				"' AND '" . $medical_end . "' ";

			if ( ! $extra['NoSearchTerms'] )
			{
				$extra['SearchTerms'] .= '<b>' . $medical_type_label . ' ' . _( 'Between' ) . ': </b>' .
					ProperDate( $medical_begin ) . ' &amp; ' .
					ProperDate( $medical_end ) . '<br />';
			}
		}
		elseif ( $medical_begin )
		{
			$extra['WHERE'] .= " AND MEDICAL_DATE>='" . $medical_begin . "' ";

			if ( ! $extra['NoSearchTerms'] )
			{
				$extra['SearchTerms'] .= '<b>' . $medical_type_label . ' ' . _( 'On or After' ) . ' </b>' .
					ProperDate( $medical_begin ) . '<br />';
			}
		}
		elseif ( $medical_end )
		{
			$extra['WHERE'] .= " AND MEDICAL_DATE<='" . $medical_end . "' ";

			if ( ! $extra['NoSearchTerms'] )
			{
				$extra['SearchTerms'] .= '<b>' . $medical_type_label . ' ' . _( 'On or Before' ) . ' </b>' .
					ProperDate( $medical_end ) . '<br />';
			}
		}

		if ( $medical_begin || $medical_end )
		{
			$extra['WHERE'] .= ") ";
		}

		return $extra;
	}

	function html()
	{
		$medical_begin_default = '';

		return '<tr class="st"><td>
		<label>
			<input type="radio" name="medical_type" value="Immunization" checked>&nbsp;' .
			_( 'Immunization' ) .
		'</label><br>
		<label>
			<input type="radio" name="medical_type" value="Physical">&nbsp;' .
			_( 'Physical' ) .
		'</label></td><td>
		<table class="cellspacing-0 valign-middle"><tr><td>
		<span class="sizep2">&ge;</span>&nbsp;
		</td><td>' .
		PrepareDate( $medical_begin_default, '_medical_begin', true, [ 'short' => true ] ) .
		'</td></tr><tr><td>
		<span class="sizep2">&le;</span>&nbsp;
		</td><td>' .
		PrepareDate( '', '_medical_end', true, [ 'short' => true ] ) .
		'</td></tr></table>
		</td></tr>';
	}
}
