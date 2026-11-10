<?php
/**
 * Report Card Grade (Grades) Widget class
 *
 * @since 12.7
 *
 * @package RosarioSIS
 */

namespace RosarioSIS\Widget;

class LetterGrade implements \RosarioSIS\Widget
{
	function canBuild( $modules )
	{
		return $modules['Grades'];
	}

	function extra( $extra )
	{
		if ( empty( $_REQUEST['letter_grade'] ) )
		{
			return $extra;
		}

		$letter_grades = '';

		foreach ( (array) $_REQUEST['letter_grade'] as $grades )
		{
			foreach ( $grades as $grade )
			{
				if ( ! $grade )
				{
					continue;
				}

				$letter_grades .= ",'" . $grade . "'";
			}
		}

		if ( empty( $letter_grades ) )
		{
			return $extra;
		}

		$LetterGradeSearchTerms = '<b>' . GetMP( $_REQUEST['letter_grade_term'], 'TITLE' ) . ' &mdash; ' .
			( isset( $_REQUEST['letter_grade_exclude'] )
			&& $_REQUEST['letter_grade_exclude'] == 'Y' ?
				_( 'Without' ) :
				_( 'With' ) ) .
			' ' . _( 'Report Card Grade' ) . ': </b>';

		$letter_grade_titles = DBGetOne( "SELECT " . DBSQLCommaSeparatedResult( 'TITLE', ', ' ) . "
			FROM report_card_grades
			WHERE SCHOOL_ID='" . UserSchool() . "'
			AND SYEAR='" . UserSyear() . "'
			AND ID IN(" . mb_substr( $letter_grades, 1 ) . ")" );

		$LetterGradeSearchTerms .= $letter_grade_titles . '<br />';

		if ( ! $extra['NoSearchTerms'] )
		{
			$extra['SearchTerms'] .= $LetterGradeSearchTerms;
		}

		$extra['WHERE'] .= " AND " . ( isset( $_REQUEST['letter_grade_exclude'] )
			&& $_REQUEST['letter_grade_exclude'] == 'Y' ? 'NOT ' : '' ) . "EXISTS
			(SELECT ''
				FROM student_report_card_grades sg3
				WHERE sg3.STUDENT_ID=ssm.STUDENT_ID
				AND sg3.SYEAR=ssm.SYEAR
				AND sg3.REPORT_CARD_GRADE_ID IN (" . mb_substr( $letter_grades, 1 ) . ")
				AND sg3.MARKING_PERIOD_ID='" . (int) $_REQUEST['letter_grade_term'] . "' )";

		return $extra;
	}

	function html()
	{
		global $_ROSARIO;

		$html = '<tr class="st"><td>' . _( 'Grade' ) . '<br />
		<label>
			<input type="checkbox" name="letter_grade_exclude" value="Y">&nbsp;' . _( 'Did not receive' ) .
		'</label>
		<br>';

		if ( GetMP( $MPfy = GetParentMP( 'FY', GetParentMP( 'SEM', UserMP() ) ), 'DOES_GRADES' ) == 'Y' )
		{
			$html .= '<label title="' . AttrEscape( GetMP( $MPfy, 'TITLE' ) ) . '">
					<input type="radio" name="letter_grade_term" value="' . AttrEscape( $MPfy ) . '">&nbsp;' .
					GetMP( $MPfy, 'SHORT_NAME' ) .
				'</label> &nbsp; ';
		}

		if ( GetMP( $MPsem = GetParentMP( 'SEM', UserMP() ), 'DOES_GRADES' ) == 'Y' )
		{
			$html .= '<label title="' . AttrEscape( GetMP( $MPsem, 'TITLE' ) ) . '">
					<input type="radio" name="letter_grade_term" value="' . AttrEscape( $MPsem ) . '">&nbsp;' .
					GetMP( $MPsem, 'SHORT_NAME' ) .
				'</label> &nbsp; ';
		}

		if ( GetMP( $MPtrim = UserMP(), 'DOES_GRADES' ) == 'Y' )
		{
			$html .= '<label title="' . AttrEscape( GetMP( $MPtrim, 'TITLE' ) ) . '">
					<input type="radio" name="letter_grade_term" value="' . AttrEscape( $MPtrim ) . '" checked>&nbsp;' .
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
						<input type="radio" name="letter_grade_term" value="' . AttrEscape( $pro ) . '">&nbsp;' .
						GetMP( $pro, 'SHORT_NAME' ) .
					'</label> &nbsp; ';
			}
		}

		$html .= '</td><td>';

		// FJ fix error Invalid argument supplied for foreach().
		if ( empty( $_REQUEST['search_modfunc'] ) )
		{
			if ( ! empty( $_REQUEST['widgetfunc'] )
				&& $_REQUEST['widgetfunc'] === 'letter_grade_search' )
			{
				$results = $this->_search( issetVal( $_REQUEST['scale_id'] ), issetVal( $_REQUEST['term'] ) );

				// Warehouse( 'header' ) spits out JS to check "If jQuery not available, log out.": remove it from response.
				ob_get_clean();

				header( 'Content-type: application/json' );

				echo json_encode( $results );

				// Cache response.
				ETagCache( 'stop' );

				exit;
			}

			$grade_scales_RET = DBGet( "SELECT rs.ID,rs.TITLE,
				(SELECT COUNT(1)
					FROM report_card_grades rg
					WHERE rg.SCHOOL_ID='" . UserSchool() . "'
					AND rg.SYEAR='" . UserSyear() . "'
					AND rg.GRADE_SCALE_ID=rs.ID) AS GRADES_COUNT
				FROM report_card_grade_scales rs
				WHERE rs.SCHOOL_ID='" . UserSchool() . "'
				AND rs.SYEAR='" . UserSyear() . "'" .
				( User( 'PROFILE' ) === 'teacher' ?
				" AND rs.ID=(SELECT GRADE_SCALE_ID
					FROM course_periods
					WHERE COURSE_PERIOD_ID='" . UserCoursePeriod() . "')" : '' ) .
				" ORDER BY rs.SORT_ORDER IS NULL,rs.SORT_ORDER,rs.ID" );

			if ( empty( $grade_scales_RET ) )
			{
				// @since 12.5 Hide Report Card Grade widget if Course is not graded
				return '';
			}

			foreach ( (array) $grade_scales_RET as $grade_scale )
			{
				$html .= $this->_gradeScaleInputHtml( $grade_scale );
			}
		}

		return $html . '</td></tr>';
	}

	/**
	 * Grade Scale Input HTML
	 *
	 * @uses Select2Input()
	 *
	 * @since 12.5
	 *
	 * @access private
	 *
	 * @param  array $grade_scale Grade Scale Grades.
	 *
	 * @return string Grade Scale Input HTML.
	 */
	private function _gradeScaleInputHtml( $grade_scale )
	{
		static $js_included = false;

		$grades_RET = [];

		if ( $grade_scale['GRADES_COUNT'] <= 1003 ) // 1001 Grades is 100 with 1 decimal or 10 with 2 decimals
		{
			$grades_RET = DBGet( "SELECT rg.ID,rg.TITLE
				FROM report_card_grades rg
				WHERE rg.SCHOOL_ID='" . UserSchool() . "'
				AND rg.SYEAR='" . UserSyear() . "'
				AND rg.GRADE_SCALE_ID='" . (int) $grade_scale['ID'] . "'
				ORDER BY rg.BREAK_OFF IS NULL,rg.BREAK_OFF DESC,rg.SORT_ORDER IS NULL,rg.SORT_ORDER" );
		}

		$grades_options = [];

		foreach ( (array) $grades_RET as $grade )
		{
			$grades_options[ $grade['ID'] ] = $grade['TITLE'];
		}

		// @since 10.6.1 Fix Grades input not displaying for Teachers.
		$ajax_url = '';

		if ( ! $grades_options
			&& $grade_scale['GRADES_COUNT'] > 1003 )
		{
			/**
			 * More than 1003 grades: AJAX search
			 *
			 * @see `_search` method below
			 */
			$ajax_url = PreparePHP_SELF( [], [],
				[ 'widgetfunc' => 'letter_grade_search', 'scale_id' => $grade_scale['ID'] ]
			);
		}

		AllowEditTemporary( 'start' );

		// @since 9.0 Use multiple select input for grades list to gain space.
		$html = '<div>' . Select2Input(
			'',
			'letter_grade[' . $grade_scale['ID'] . '][]',
			$grade_scale['TITLE'],
			$grades_options,
			false,
			'multiple style="width: 217px"' .
			( $ajax_url ? ' data-ajax-url="' . $ajax_url . '"' : '' )
		) . '</div>';

		AllowEditTemporary( 'stop' );

		return $html;
	}

	/**
	 * Select2 AJAX results
	 * Search grades & return JSON to Select2
	 *
	 * @since 12.5
	 *
	 * @link https://select2.org/data-sources/ajax
	 *
	 * ```json
	 * { "results": [ { "id": 1, "text": "Option 1" }, { "id": 2, "text": "Option 2" } ], "pagination": { "more": false } }
	 * ```
	 *
	 * @access private
	 *
	 * @param  int    $scale_id Grading Scale ID.
	 * @param  string $term     Search Term.
	 *
	 * @return array  Results formatted for Select2 JSON.
	 */
	private function _search( $scale_id, $term )
	{
		$json_results = [
			'results' => [],
			'pagination' => [ 'more' => false ], // We limit SQL request to 100, but no pagination / infinite scrolling.
		];

		if ( ! empty( $term )
			&& ! empty( $scale_id ) )
		{
			// ORDER BY full match first.
			$grades_RET = DBGet( "SELECT rg.ID,rg.TITLE
				FROM report_card_grades rg
				WHERE rg.SCHOOL_ID='" . UserSchool() . "'
				AND rg.SYEAR='" . UserSyear() . "'
				AND rg.GRADE_SCALE_ID='" . (int) $scale_id . "'
				AND UPPER(TITLE) LIKE '" . mb_strtoupper( $term ) . "%'
				ORDER BY
					CASE WHEN UPPER(TITLE)='" . mb_strtoupper( $term ) . "' THEN 1 ELSE 2 END,
					rg.BREAK_OFF IS NULL,rg.BREAK_OFF DESC,rg.SORT_ORDER IS NULL,rg.SORT_ORDER
				LIMIT 100" );

			$results = [];

			foreach ( (array) $grades_RET as $grade )
			{
				$results[] = [ 'id' =>  $grade['ID'], 'text' => $grade['TITLE'] ];
			}

			$json_results['results'] = $results;
		}

		return $json_results;
	}
}
