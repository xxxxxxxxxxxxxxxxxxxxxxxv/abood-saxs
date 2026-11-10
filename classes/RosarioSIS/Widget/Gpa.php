<?php
/**
 * GPA (Grades) Widget class
 *
 * @since 12.7
 *
 * @package RosarioSIS
 */

namespace RosarioSIS\Widget;

class Gpa implements \RosarioSIS\Widget
{
	function canBuild( $modules )
	{
		return $modules['Grades'];
	}

	function extra( $extra )
	{
		if ( ! isset( $_REQUEST['gpa_low'] )
			|| ! is_numeric( $_REQUEST['gpa_low'] )
			|| ! isset( $_REQUEST['gpa_high'] )
			|| ! is_numeric( $_REQUEST['gpa_high'] ) )
		{
			return $extra;
		}

		if ( $_REQUEST['gpa_low'] > $_REQUEST['gpa_high'] )
		{
			$temp = $_REQUEST['gpa_high'];
			$_REQUEST['gpa_high'] = $_REQUEST['gpa_low'];
			$_REQUEST['gpa_low'] = $temp;
		}

		if ( ! empty( $_REQUEST['list_gpa'] ) )
		{
			$extra['SELECT'] .= ',sms.CUM_WEIGHTED_FACTOR,sms.CUM_UNWEIGHTED_FACTOR';

			$extra['columns_after']['CUM_WEIGHTED_FACTOR'] = _( 'Weighted GPA' );
			$extra['columns_after']['CUM_UNWEIGHTED_FACTOR'] = _( 'Unweighted GPA' );
		}

		if ( mb_strpos( $extra['FROM'], 'student_mp_stats sms' ) === false )
		{
			$extra['FROM'] .= ",student_mp_stats sms";

			$extra['WHERE'] .= " AND sms.STUDENT_ID=s.STUDENT_ID
				AND sms.MARKING_PERIOD_ID='" . (int) $_REQUEST['gpa_term'] . "'";
		}

		$extra['WHERE'] .= " AND sms.CUM_" .
			( ( isset( $_REQUEST['gpa_weighted'] ) && $_REQUEST['gpa_weighted'] === 'Y' ) ? '' : 'UN' ) .
			"WEIGHTED_FACTOR *" . SchoolInfo( 'REPORTING_GP_SCALE' ) . "
			BETWEEN '" . $_REQUEST['gpa_low'] . "' AND '" . $_REQUEST['gpa_high'] . "'";

		if ( ! $extra['NoSearchTerms'] )
		{
			$extra['SearchTerms'] .= '<b>' . GetMP( $_REQUEST['gpa_term'], 'TITLE' ) . ' &mdash; ' .
				( ( isset( $_REQUEST['gpa_weighted'] ) && $_REQUEST['gpa_weighted'] === 'Y' ) ?
					_( 'Weighted GPA' ) :
					_( 'Unweighted GPA' ) ) .
				' ' . _( 'Between' ) . ': </b>' .
				$_REQUEST['gpa_low'] . ' &amp; ' . $_REQUEST['gpa_high'] . '<br />';
		}

		return $extra;
	}

	function html()
	{
		$html = '<tr class="st"><td>' . _( 'GPA' ) . '<br />
		<label>
			<input type="checkbox" name="gpa_weighted" value="Y" checked>&nbsp;' . _( 'Weighted' ) .
		'</label>
		<br>';

		if ( GetMP( $MPfy = GetParentMP( 'FY', GetParentMP( 'SEM', UserMP() ) ), 'DOES_GRADES') == 'Y' )
		{
			$html .= '<label title="' . AttrEscape( GetMP( $MPfy, 'TITLE' ) ) . '">
					<input type="radio" name="gpa_term" value="' . AttrEscape( $MPfy ) . '" checked>&nbsp;' .
					GetMP( $MPfy, 'SHORT_NAME' ) .
				'</label> &nbsp; ';
		}

		if ( GetMP( $MPsem = GetParentMP( 'SEM', UserMP() ), 'DOES_GRADES' ) == 'Y' )
		{
			$html .= '<label title="' . AttrEscape( GetMP( $MPsem, 'TITLE' ) ) . '">
					<input type="radio" name="gpa_term" value="' . AttrEscape( $MPsem ) . '">&nbsp;' .
					GetMP( $MPsem, 'SHORT_NAME' ) .
				'</label> &nbsp; ';
		}

		if ( GetMP( $MPtrim = UserMP(), 'DOES_GRADES' ) == 'Y' )
		{
			$html .= '<label title="' . AttrEscape( GetMP( $MPtrim, 'TITLE' ) ) . '">
					<input type="radio" name="gpa_term" value="' . AttrEscape( $MPtrim ) . '" checked>&nbsp;' .
					GetMP( $MPtrim, 'SHORT_NAME' ) .
				'</label>';
		}

		return $html . '</td><td><label>' . _( 'Between' ) .
		' <input type="number" name="gpa_low" min="0" max="99999" step="0.01"></label>' .
		' <label>&amp;' .
		' <input type="number" name="gpa_high" min="0" max="99999" step="0.01"></label>
		</td></tr>';
	}
}
