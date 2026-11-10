<?php
/**
 * Class Rank (Grades) Widget class
 *
 * @since 12.7
 *
 * @package RosarioSIS
 */

namespace RosarioSIS\Widget;

class ClassRank implements \RosarioSIS\Widget
{
	function canBuild( $modules )
	{
		return $modules['Grades'];
	}

	function extra( $extra )
	{
		if ( ! isset( $_REQUEST['class_rank_low'] )
			|| ! is_numeric( $_REQUEST['class_rank_low'] )
			|| ! isset( $_REQUEST['class_rank_high'] )
			|| ! is_numeric( $_REQUEST['class_rank_high'] ) )
		{
			return $extra;
		}

		$_REQUEST['class_rank_low'] = (int) $_REQUEST['class_rank_low'];
		$_REQUEST['class_rank_high'] = (int) $_REQUEST['class_rank_high'];

		if ( $_REQUEST['class_rank_low'] > $_REQUEST['class_rank_high'] )
		{
			$temp = $_REQUEST['class_rank_high'];
			$_REQUEST['class_rank_high'] = $_REQUEST['class_rank_low'];
			$_REQUEST['class_rank_low'] = $temp;
		}

		if ( mb_strpos( $extra['FROM'], 'student_mp_stats sms' ) === false )
		{
			$extra['FROM'] .= ",student_mp_stats sms";

			$extra['WHERE'] .= " AND sms.STUDENT_ID=s.STUDENT_ID
				AND sms.MARKING_PERIOD_ID='" . (int) $_REQUEST['class_rank_term'] . "'";
		}

		$extra['WHERE'] .= " AND sms.CUM_RANK BETWEEN
			'" . $_REQUEST['class_rank_low'] . "'
			AND '" . $_REQUEST['class_rank_high'] . "'";

		if ( ! $extra['NoSearchTerms'] )
		{
			$extra['SearchTerms'] .= '<b>' . GetMP( $_REQUEST['class_rank_term'], 'TITLE' ) . ' &mdash; ' .
				_( 'Class Rank' ) . ' ' . _( 'Between' ) . ': </b>' .
				$_REQUEST['class_rank_low'] . ' &amp; ' . $_REQUEST['class_rank_high'] . '<br />';
		}

		return $extra;
	}

	function html()
	{
		$html = '<tr class="st"><td>' . _( 'Class Rank' ) . '<br />';

		if ( GetMP( $MPfy = GetParentMP( 'FY', GetParentMP( 'SEM', UserMP() ) ), 'DOES_GRADES' ) == 'Y' )
		{
			$html .= '<label title="' . AttrEscape( GetMP( $MPfy, 'TITLE' ) ) . '">
					<input type="radio" name="class_rank_term" value="' . AttrEscape( $MPfy ) . '">&nbsp;' .
					GetMP( $MPfy, 'SHORT_NAME' ) .
				'</label> &nbsp; ';
		}

		if ( GetMP( $MPsem = GetParentMP( 'SEM', UserMP() ), 'DOES_GRADES' ) == 'Y' )
		{
			$html .= '<label title="' . AttrEscape( GetMP( $MPsem, 'TITLE' ) ) . '">
					<input type="radio" name="class_rank_term" value="' . AttrEscape( $MPsem ) . '">&nbsp;' .
					GetMP( $MPsem, 'SHORT_NAME' ) .
				'</label> &nbsp; ';
		}

		if ( GetMP( $MPtrim = UserMP(), 'DOES_GRADES' ) == 'Y' )
		{
			$html .= '<label title="' . AttrEscape( GetMP( $MPtrim, 'TITLE' ) ) . '">
					<input type="radio" name="class_rank_term" value="' . AttrEscape( $MPtrim ) . '" checked>&nbsp;' .
					GetMP( $MPtrim, 'SHORT_NAME' ) .
				'</label> &nbsp; ';
		}

		if ( mb_strlen( $pros = GetChildrenMP( 'PRO', UserMP() ) ) )
		{
			$pros = explode( ',', str_replace( "'", '', $pros ) );

			foreach ( $pros as $pro )
			{
				if ( GetMP( $pro, 'DOES_GRADES' ) !== 'Y' )
				{
					continue;
				}

				$html .= '<label title="' . AttrEscape( GetMP( $pro, 'TITLE' ) ) . '">
						<input type="radio" name="class_rank_term" value="' . AttrEscape( $pro ) . '">&nbsp;' .
						GetMP( $pro, 'SHORT_NAME' ) .
					'</label> &nbsp; ';
			}
		}

		return $html . '</td><td><label>' . _( 'Between' ) .
		' <input type="number" name="class_rank_low" min="0" max="99999" step="1"></label>' .
		' <label>&amp;' .
		' <input type="number" name="class_rank_high" min="0" max="99999" step="1"></label>
		</td></tr>';
	}
}
