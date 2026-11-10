<?php
/**
 * Previously Enrolled (Enrollment) Widget class
 *
 * @since 12.7
 *
 * @package RosarioSIS
 */

namespace RosarioSIS\Widget;

class Rolled implements \RosarioSIS\Widget
{
	function canBuild( $modules )
	{
		return $modules['Students'];
	}

	function extra( $extra )
	{
		if ( empty( $_REQUEST['rolled'] ) )
		{
			return $extra;
		}

		$extra['WHERE'] .= " AND " . ( $_REQUEST['rolled'] == 'Y' ? '' : 'NOT ' ) . "exists
			(SELECT ''
				FROM student_enrollment
				WHERE STUDENT_ID=ssm.STUDENT_ID
				AND SYEAR<ssm.SYEAR)";

		if ( ! $extra['NoSearchTerms'] )
		{
			$extra['SearchTerms'] .= '<b>' . _( 'Previously Enrolled' ) . ': </b>' .
				( $_REQUEST['rolled'] == 'Y' ? _( 'Yes' ) : _( 'No' ) ) . '<br />';
		}

		return $extra;
	}

	function html()
	{
		return '<tr class="st"><td>' . _( 'Previously Enrolled' ) . '</td><td>
		<label>
			<input type="radio" value="" name="rolled" checked>&nbsp;' . _( 'N/A' ) .
		'</label> &nbsp;
		<label>
			<input type="radio" value="Y" name="rolled">&nbsp;' . _( 'Yes' ) .
		'</label> &nbsp;
		<label>
			<input type="radio" value="N" name="rolled">&nbsp;' . _( 'No' ) .
		'</label>
		</td></tr>';
	}
}
