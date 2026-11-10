<?php
/**
 * Incident Date (Discipline) Widget class
 *
 * @since 12.7
 *
 * @package RosarioSIS
 */

namespace RosarioSIS\Widget;

class IncidentDate implements \RosarioSIS\Widget
{
	function canBuild( $modules )
	{
		return $modules['Discipline'];
	}

	function extra( $extra )
	{
		$discipline_entry_begin = RequestedDate(
			'discipline_entry_begin',
			( issetVal( $_REQUEST['discipline_entry_begin'], '' ) )
		);

		$discipline_entry_end = RequestedDate(
			'discipline_entry_end',
			( issetVal( $_REQUEST['discipline_entry_end'], '' ) )
		);

		if ( ! $discipline_entry_begin
			&& ! $discipline_entry_end )
		{
			return $extra;
		}

		if ( $discipline_entry_end
			&& $discipline_entry_begin > $discipline_entry_end )
		{
			// Begin date > end date, switch.
			$discipline_entry_begin_tmp = $discipline_entry_begin;

			$discipline_entry_begin = $discipline_entry_end;
			$discipline_entry_end = $discipline_entry_begin_tmp;
		}

		if ( $discipline_entry_begin
			|| $discipline_entry_end )
		{
			if ( mb_stripos( $extra['FROM'], 'discipline_referrals dr' ) === false )
			{
				// Fix SQL error invalid reference to FROM-clause entry for table "ssm"
				// Add JOIN just after JOIN student_enrollment ssm
				$extra['FROM'] = ' LEFT JOIN discipline_referrals dr
					ON (dr.STUDENT_ID=ssm.STUDENT_ID
					AND dr.SYEAR=ssm.SYEAR
					AND dr.SCHOOL_ID=ssm.SCHOOL_ID) ' . $extra['FROM'];
			}
		}

		if ( $discipline_entry_begin
			&& $discipline_entry_end )
		{
			$extra['WHERE'] .= " AND dr.ENTRY_DATE
				BETWEEN '" . $discipline_entry_begin .
				"' AND '" . $discipline_entry_end . "'";

			if ( ! $extra['NoSearchTerms'] )
			{
				$extra['SearchTerms'] .= '<b>' . _( 'Incident Date' ) . ' ' . _( 'Between' ) . ': </b>' .
					ProperDate( $discipline_entry_begin ) . ' &amp; ' .
					ProperDate( $discipline_entry_end ) . '<br />';
			}
		}
		elseif ( $discipline_entry_begin )
		{
			$extra['WHERE'] .= " AND dr.ENTRY_DATE>='" . $discipline_entry_begin . "'";

			if ( ! $extra['NoSearchTerms'] )
			{
				$extra['SearchTerms'] .= '<b>' . _( 'Incident Date' ) . ' ' . _( 'On or After' ) . ' </b>' .
					ProperDate( $discipline_entry_begin ) . '<br />';
			}
		}
		elseif ( $discipline_entry_end )
		{
			$extra['WHERE'] .= " AND dr.ENTRY_DATE<='" . $discipline_entry_end . "'";

			if ( ! $extra['NoSearchTerms'] )
			{
				$extra['SearchTerms'] .= '<b>' . _( 'Incident Date' ) . ' ' . _( 'On or Before' ) . ' </b>' .
					ProperDate( $discipline_entry_end ) . '<br />';
			}
		}

		return $extra;
	}

	function html()
	{
		$discipline_entry_begin_default = '';

		if ( $_REQUEST['modname'] === 'Discipline/Referrals.php' )
		{
			// Set default Incident Date for Referrals program only.
			$discipline_entry_begin_default = date( 'Y-m' ) . '-01';
		}

		return '<tr class="st"><td>' . _( 'Incident Date' ) . '</td><td>
		<table class="cellspacing-0 valign-middle"><tr><td>
		<span class="sizep2">&ge;</span>&nbsp;
		</td><td>' .
		PrepareDate( $discipline_entry_begin_default, '_discipline_entry_begin', true, [ 'short' => true ] ) .
		'</td></tr><tr><td>
		<span class="sizep2">&le;</span>&nbsp;
		</td><td>' .
		PrepareDate( '', '_discipline_entry_end', true, [ 'short' => true ] ) .
		'</td></tr></table>
		</td></tr>';
	}
}
