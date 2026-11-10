<?php
/**
 * Ineligible (Eligibility aka Activities) Widget class
 *
 * @since 12.7
 *
 * @package RosarioSIS
 */

namespace RosarioSIS\Widget;

class Eligibility implements \RosarioSIS\Widget
{
	function canBuild( $modules )
	{
		return $modules['Eligibility'];
	}

	function extra( $extra )
	{
		if ( ! isset( $_REQUEST['ineligible'] )
			|| $_REQUEST['ineligible'] !== 'Y' )
		{
			return $extra;
		}

		// Day of the week: 1 (for Monday) through 7 (for Sunday).
		$today = date( 'w' ) ? date( 'w' ) : 7;

		$start_date = date(
			'Y-m-d',
			time() - ( $today - ProgramConfig( 'eligibility', 'START_DAY' ) ) * 60 * 60 * 24
		);

		$end_date = DBDate();

		$extra['WHERE'] .= " AND (SELECT count(*)
			FROM eligibility e
			WHERE ssm.STUDENT_ID=e.STUDENT_ID
			AND e.SYEAR=ssm.SYEAR
			AND e.SCHOOL_DATE BETWEEN '" . $start_date . "'
			AND '" . $end_date . "'
			AND e.ELIGIBILITY_CODE='FAILING') > '0'";

		if ( ! $extra['NoSearchTerms'] )
		{
			$extra['SearchTerms'] .= '<b>' . _( 'Eligibility' ) . ': </b>' .
				_( 'Ineligible' ) . '<br />';
		}

		return $extra;
	}

	function html()
	{
		return '<tr class="st"><td>
		</td><td>
		<label>
			<input type="checkbox" name="ineligible" value="Y">&nbsp;' . _( 'Ineligible' ) .
		'</label>
		</td></tr>';
	}
}
