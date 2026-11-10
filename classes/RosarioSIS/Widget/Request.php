<?php
/**
 * Request (Scheduling) Widget class
 *
 * @since 12.7
 *
 * @package RosarioSIS
 */

namespace RosarioSIS\Widget;

class Request implements \RosarioSIS\Widget
{
	function canBuild( $modules )
	{
		return $modules['Scheduling']
			&& User( 'PROFILE' ) === 'admin';
	}

	function extra( $extra )
	{
		// PART OF THIS IS DUPLICATED IN PrintRequests.php.
		if ( empty( $_REQUEST['request_course_id'] ) )
		{
			return $extra;
		}

		$course_title = ParseMLField( DBGetOne( "SELECT c.TITLE
			FROM courses c
			WHERE c.COURSE_ID='" . (int) $_REQUEST['request_course_id'] . "'" ) );

		// Request.
		if ( ! isset( $_REQUEST['missing_request_course'] )
			|| ! $_REQUEST['missing_request_course'] )
		{
			$extra['FROM'] .= ",schedule_requests sr";

			$extra['WHERE'] .= " AND sr.STUDENT_ID=s.STUDENT_ID
				AND sr.SYEAR=ssm.SYEAR
				AND sr.SCHOOL_ID=ssm.SCHOOL_ID
				AND sr.COURSE_ID='" . (int) $_REQUEST['request_course_id'] . "' ";

			if ( ! $extra['NoSearchTerms'] )
			{
				$extra['SearchTerms'] .= '<b>' . _( 'Request' ) . ': </b>' .
					$course_title . '<br />';
			}
		}
		// Missing Request.
		else
		{
			$extra['WHERE'] .= " AND NOT EXISTS
				(SELECT '' FROM
					schedule_requests sr
					WHERE sr.STUDENT_ID=ssm.STUDENT_ID
					AND sr.SYEAR=ssm.SYEAR
					AND sr.COURSE_ID='" . (int) $_REQUEST['request_course_id'] . "' ) ";

			if ( ! $extra['NoSearchTerms'] )
			{
				$extra['SearchTerms'] .= '<b>' . _( 'Missing Request' ) . ': </b>' .
					$course_title . '<br />';
			}
		}

		return $extra;
	}

	function html()
	{
		// Request Widget: Popup window.
		// @since 12.0 Use colorBox instead of popup window
		return '<tr class="st"><td>'. _( 'Request' ) . '</td><td>
		<div id="request_div"></div>
		<a href="Modules.php?modname=misc/ChooseRequest.php" class="colorbox">' .
			_( 'Choose' ) .
		'</a>
		</td></tr>';
	}
}
