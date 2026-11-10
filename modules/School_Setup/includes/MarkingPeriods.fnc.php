<?php
/**
 * Marking Periods functions
 *
 * @subpackage modules
 * @package RosarioSIS
 */

/**
 * Marking Period DELETE SQL queries
 *
 * @since 5.2
 *
 * @param int    $mp_id   Marking Period ID.
 * @param string $mp_term Term: FY, SEM, QTR or PRO.
 *
 * @return string Marking Period DELETE SQL queries.
 */
function MarkingPeriodDeleteSQL( $mp_id, $mp_term )
{
	$mp_id = intval( $mp_id );

	$delete_sql = '';

	switch ( $mp_term )
	{
		case 'FY':

			$delete_sql .= "DELETE FROM school_marking_periods
				WHERE PARENT_ID IN
					(SELECT MARKING_PERIOD_ID
						FROM school_marking_periods
						WHERE PARENT_ID IN
							(SELECT MARKING_PERIOD_ID
								FROM school_marking_periods
								WHERE PARENT_ID='" . (int) $mp_id . "'))
				AND SYEAR='" . UserSyear() . "'
				AND SCHOOL_ID='" . UserSchool() . "';";

		case 'SEM':

			$delete_sql .= "DELETE FROM school_marking_periods
				WHERE PARENT_ID IN
					(SELECT MARKING_PERIOD_ID
						FROM school_marking_periods
						WHERE PARENT_ID='" . (int) $mp_id . "')
				AND SYEAR='" . UserSyear() . "'
				AND SCHOOL_ID='" . UserSchool() . "';";

		case 'QTR':

			$delete_sql .= "DELETE FROM school_marking_periods
				WHERE PARENT_ID='" . (int) $mp_id . "'
				AND SYEAR='" . UserSyear() . "'
				AND SCHOOL_ID='" . UserSchool() . "';";

		case 'PRO':
		break;
	}

	$delete_sql .= "DELETE FROM school_marking_periods
		WHERE MARKING_PERIOD_ID='" . (int) $mp_id . "'
		AND SYEAR='" . UserSyear() . "'
		AND SCHOOL_ID='" . UserSchool() . "';";

	return $delete_sql;
}
