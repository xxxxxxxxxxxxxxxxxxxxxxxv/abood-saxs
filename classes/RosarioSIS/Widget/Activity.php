<?php
/**
 * Activity (Eligibility aka Activities) Widget class
 *
 * @since 12.7
 *
 * @package RosarioSIS
 */

namespace RosarioSIS\Widget;

class Activity implements \RosarioSIS\Widget
{
	function canBuild( $modules )
	{
		return $modules['Eligibility'];
	}

	function extra( $extra )
	{
		if ( empty( $_REQUEST['activity_id'] ) )
		{
			return $extra;
		}

		$extra['FROM'] .= ",student_eligibility_activities sea";

		$extra['WHERE'] .= " AND sea.STUDENT_ID=s.STUDENT_ID
			AND sea.SYEAR=ssm.SYEAR
			AND sea.ACTIVITY_ID='" . (int) $_REQUEST['activity_id'] . "'";

		$activity_title = DBGetOne( "SELECT TITLE
			FROM eligibility_activities
			WHERE ID='" . (int) $_REQUEST['activity_id'] . "'" );

		if ( ! $extra['NoSearchTerms'] )
		{
			$extra['SearchTerms'] .= '<b>' . _( 'Activity' ) . ': </b>' .
				$activity_title . '<br />';
		}

		return $extra;
	}

	function html()
	{
		$activities_RET = [];

		if ( empty( $_REQUEST['search_modfunc'] ) )
		{
			$activities_RET = DBGet( "SELECT ID,TITLE
				FROM eligibility_activities
				WHERE SCHOOL_ID='" . UserSchool() . "'
				AND SYEAR='" . UserSyear() . "'" );
		}

		$select = '<select name="activity_id" id="activity_id">
			<option value="">' . _( 'Not Specified' ) . '</option>';

		foreach ( (array) $activities_RET as $activity )
		{
			$select .= '<option value="' . AttrEscape( $activity['ID'] ) . '">' . $activity['TITLE'] . '</option>';
		}

		$select .= '</select>';

		return '<tr class="st"><td><label for="activity_id">' .
		_( 'Activity' ) .
		'</label></td><td>' .
		$select .
		'</td></tr>';
	}
}
