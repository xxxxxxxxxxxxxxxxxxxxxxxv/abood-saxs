<?php
/**
 * Attendance Start / Enrolled (Enrollment) Widget class
 *
 * @since 12.7
 *
 * @package RosarioSIS
 */

namespace RosarioSIS\Widget;

class Enrolled implements \RosarioSIS\Widget
{
	function canBuild( $modules )
	{
		return $modules['Students'];
	}

	function extra( $extra )
	{
		$enrolled_begin = RequestedDate(
			'enrolled_begin',
			( issetVal( $_REQUEST['enrolled_begin'], '' ) )
		);

		$enrolled_end = RequestedDate(
			'enrolled_end',
			( issetVal( $_REQUEST['enrolled_end'], '' ) )
		);

		if ( ! $enrolled_begin
			&& ! $enrolled_end )
		{
			return $extra;
		}

		if ( $enrolled_end
			&& $enrolled_begin > $enrolled_end )
		{
			// Begin date > end date, switch.
			$enrolled_begin_tmp = $enrolled_begin;

			$enrolled_begin = $enrolled_end;
			$enrolled_end = $enrolled_begin_tmp;
		}

		if ( $enrolled_begin
			&& $enrolled_end )
		{
			$extra['WHERE'] .= " AND ssm.START_DATE
				BETWEEN '" . $enrolled_begin .
				"' AND '" . $enrolled_end . "'";

			if ( ! $extra['NoSearchTerms'] )
			{
				$extra['SearchTerms'] .= '<b>' . _( 'Enrolled' ) . ' ' . _( 'Between' ) . ': </b>' .
					ProperDate( $enrolled_begin ) . ' &amp; ' .
					ProperDate( $enrolled_end ) . '<br />';
			}
		}
		elseif ( $enrolled_begin )
		{
			$extra['WHERE'] .= " AND ssm.START_DATE>='" . $enrolled_begin . "'";

			if ( ! $extra['NoSearchTerms'] )
			{
				$extra['SearchTerms'] .= '<b>' . _( 'Enrolled' ) . ' ' . _( 'On or After' ) . ': </b>' .
					ProperDate( $enrolled_begin ) . '<br />';
			}
		}
		elseif ( $enrolled_end )
		{
			$extra['WHERE'] .= " AND ssm.START_DATE<='" . $enrolled_end . "'";

			if ( ! $extra['NoSearchTerms'] )
			{
				$extra['SearchTerms'] .= '<b>' . _( 'Enrolled' ) . ' ' . _( 'On or Before' ) . ': </b>' .
					ProperDate( $enrolled_end ) . '<br />';
			}
		}

		return $extra;
	}

	function html()
	{
		return '<tr class="st"><td>' . _( 'Attendance Start' ) . '</td><td>
		<table class="cellspacing-0 valign-middle"><tr><td class="sizep2">
		&ge;
		</td><td>' .
		PrepareDate( '', '_enrolled_begin', true, [ 'short' => true ] ) .
		'</td></tr><tr><td class="sizep2">
		&le;
		</td><td>' .
		PrepareDate( '', '_enrolled_end', true, [ 'short' => true ] ) .
		'</td></tr></table>
		</td></tr>';
	}
}
