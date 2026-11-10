<?php

require_once 'ProgramFunctions/Fields.fnc.php';

DrawHeader( ProgramTitle() );

$_REQUEST['category_id'] = issetVal( $_REQUEST['category_id'] );

if ( $_REQUEST['modfunc'] === 'save'
	&& AllowEdit() )
{
	// Add eventual Dates to $_REQUEST['values'].
	AddRequestedDates( 'values', 'post' );

	if ( ! empty( $_REQUEST['student'] ) )
	{
		$update_enrollment_col = [];

		if ( ! empty( $_REQUEST['values']['GRADE_ID'] ) )
		{
			$update_enrollment_col['GRADE_ID'] = (int) $_REQUEST['values']['GRADE_ID'];
		}

		if ( isset( $_REQUEST['values']['NEXT_SCHOOL'] )
			&& $_REQUEST['values']['NEXT_SCHOOL'] !== '' )
		{
			$update_enrollment_col['NEXT_SCHOOL'] = (int) $_REQUEST['values']['NEXT_SCHOOL'];
		}

		if ( ! empty( $_REQUEST['values']['CALENDAR_ID'] ) )
		{
			$update_enrollment_col['CALENDAR_ID'] = (int) $_REQUEST['values']['CALENDAR_ID'];
		}

		if ( ! empty( $_REQUEST['values']['START_DATE'] ) )
		{
			$update_enrollment_col['START_DATE'] = $_REQUEST['values']['START_DATE'];
		}

		if ( ! empty( $_REQUEST['values']['ENROLLMENT_CODE'] ) )
		{
			$update_enrollment_col['ENROLLMENT_CODE'] = (int) $_REQUEST['values']['ENROLLMENT_CODE'];
		}

		unset(
			$_REQUEST['values']['GRADE_ID'],
			$_REQUEST['values']['NEXT_SCHOOL'],
			$_REQUEST['values']['CALENDAR_ID'],
			$_REQUEST['values']['START_DATE'],
			$_REQUEST['values']['ENROLLMENT_CODE']
		);

		$student_ids = $_REQUEST['student'];

		if ( User( 'SCHOOLS' ) )
		{
			// @since 12.9 Security fix #376 IDOR: Unauthorized Cross-School Data Assignment
			$students_list = implode( ',', array_map( 'intval', $_REQUEST['student'] ) );

			$students_RET = DBGet( "SELECT DISTINCT STUDENT_ID
				FROM student_enrollment
				WHERE SYEAR='" . UserSyear() . "'
				AND SCHOOL_ID IN(" . mb_substr( str_replace( ',', "','", User( 'SCHOOLS' ) ), 2, -2 ) . ")
				AND STUDENT_ID IN(" . $students_list . ")",
				[], [ 'STUDENT_ID' ] );

			$student_ids = empty( $students_RET ) ? [] : array_keys( $students_RET );
		}

		foreach ( (array) $student_ids as $student_id )
		{
			$update_enrollment_col_final = $update_enrollment_col;

			if ( ! empty( $update_enrollment_col['START_DATE'] ) )
			{
				//FJ check if student already enrolled on that date when updating START_DATE
				$already_enrolled = DBGetOne( "SELECT ID
					FROM student_enrollment
					WHERE STUDENT_ID='" . (int) $student_id . "'
					AND SYEAR='" . UserSyear() . "'
					AND '" . $update_enrollment_col['START_DATE'] . "' BETWEEN START_DATE AND END_DATE" );

				if ( $already_enrolled )
				{
					$error[] = _( 'The student is already enrolled on that date, and cannot be enrolled a second time on the date you specified. Please fix, and try enrolling the student again.' );

					unset( $update_enrollment_col_final['START_DATE'] );
				}
			}

			if ( ! $update_enrollment_col_final )
			{
				continue;
			}

			// Enrollment: update only the LAST enrollment record
			/**
			 * Fix MySQL 5.6 error Can't specify target table for update in FROM clause.
			 *
			 * @link https://stackoverflow.com/questions/45494/mysql-error-1093-cant-specify-target-table-for-update-in-from-clause
			 */
			$last_enrollment_id = DBGetOne( "SELECT ID
				FROM student_enrollment
				WHERE SYEAR='" . UserSyear() . "'
				AND SCHOOL_ID='" . UserSchool() . "'
				AND STUDENT_ID='" . (int) $student_id . "'
				ORDER BY START_DATE DESC
				LIMIT 1" );

			DBUpdate(
				'student_enrollment',
				$update_enrollment_col_final,
				[
					'ID' => (int) $last_enrollment_id,
					'SYEAR' => UserSyear(),
				]
			);
		}

		// FJ textarea fields MarkDown sanitize.
		$_REQUEST['values'] = FilterCustomFieldsMarkdown( 'custom_fields', 'values' );

		$fields_RET = DBGet( "SELECT ID,TYPE
			FROM custom_fields
			ORDER BY SORT_ORDER IS NULL,SORT_ORDER", [], [ 'ID' ] );

		$update_student_col = [];

		foreach ( (array) $_REQUEST['values'] as $field => $value )
		{
			$type = $fields_RET[str_replace( 'CUSTOM_', '', $field )][1]['TYPE'];

			if ( $type == 'numeric'
				&& $value != ''
				&& ! is_numeric( $value ) )
			{
				$error[] = _( 'Please enter valid Numeric data.' );
				continue;
			}

			if ( $value != '' || $type === 'radio' )
			{
				if ( $value === '!'
					&& ( $type === 'select' || $type === 'autos' || $type === 'exports' ) )
				{
					// No Value.
					$value = '';
				}

				$update_student_col[ $field ] = $value;
			}
		}

		DBUpdate(
			'students',
			$update_student_col,
			// @since 12.7 SQL allow array in $where_columns: WHERE COLUMN IN(val1,val2)
			[ 'STUDENT_ID' => array_map( 'intval', $student_ids ) ]
		);

		if ( ! $update_enrollment_col
			&& ! $update_student_col )
		{
			$warning[] = _( 'No data was entered.' );
		}
		else
		{
			$note[] = button( 'check' ) . '&nbsp;' . _( 'The specified information was applied to the selected students.' );
		}
	}
	else
	{
		$error[] = _( 'You must choose at least one student.' );
	}

	// Unset modfunc, values & student & redirect URL.
	RedirectURL( [ 'modfunc', 'values', 'student' ] );
}

echo ErrorMessage( $error );

echo ErrorMessage( $note, 'note' );

echo ErrorMessage( $warning, 'warning' );

if ( ! $_REQUEST['modfunc'] )
{
	$extra['link'] = [ 'FULL_NAME' => false ];
	$extra['SELECT'] = ",NULL AS CHECKBOX";

	if ( $_REQUEST['search_modfunc'] === 'list' )
	{
		echo '<form action="' . URLEscape( 'Modules.php?modname=' . $_REQUEST['modname'] . '&modfunc=save' ) . '" method="POST">';
		DrawHeader( '', SubmitButton() );
		echo '<br />';

		if ( ! empty( $_REQUEST['category_id'] ) )
		{
			$fields_RET = DBGet( "SELECT ID,TITLE,TYPE,SELECT_OPTIONS,REQUIRED
				FROM custom_fields
				WHERE CATEGORY_ID='" . (int) $_REQUEST['category_id'] . "'
				ORDER BY SORT_ORDER IS NULL,SORT_ORDER,TITLE", [], [ 'TYPE' ] );
		}
		else
		{
			$fields_RET = DBGet( "SELECT f.ID,f.TITLE,f.TYPE,f.SELECT_OPTIONS,f.REQUIRED
				FROM custom_fields f,student_field_categories c
				WHERE f.CATEGORY_ID=c.ID
				ORDER BY c.SORT_ORDER IS NULL,c.SORT_ORDER,c.TITLE,f.SORT_ORDER IS NULL,f.SORT_ORDER,f.TITLE", [], [ 'TYPE' ] );
		}

		// Only display Categories having fields.
		$categories_RET = DBGet( "SELECT sfc.ID,sfc.TITLE
			FROM student_field_categories sfc
			WHERE EXISTS(SELECT 1 FROM custom_fields cf
				WHERE cf.CATEGORY_ID=sfc.ID)
			ORDER BY sfc.SORT_ORDER IS NULL,sfc.SORT_ORDER,sfc.TITLE" );

		echo '<table class="widefat center"><tr><td><div class="center">';

		$category_onchange_URL = PreparePHP_SELF( $_REQUEST, [ 'category_id' ] ) . '&category_id=';

		echo '<select name="category_id" id="category_id" ' .
			// @since RosarioSIS 12.5 CSP remove unsafe-inline Javascript
			// Note: `this.value` inside link is automatically replaced
			'class="onchange-ajax-link" data-link="' . $category_onchange_URL . 'this.value">';

		echo '<option value="">' . _( 'All Categories' ) . '</option>';

		foreach ( (array) $categories_RET as $category )
		{
			echo '<option value="' . AttrEscape( $category['ID'] ) . '"' .
				( $_REQUEST['category_id'] == $category['ID'] ? ' selected' : '' ) . '>' .
				ParseMLField( $category['TITLE'] ) . '</option>';
		}

		echo '</select>';

		echo FormatInputTitle(
			'<span class="a11y-hidden">' . _( 'Student Info' ) . '</span>',
			'category_id'
		);

		echo '</div></td></tr>';

		if ( isset( $fields_RET['text'] ) )
		{
			foreach ( (array) $fields_RET['text'] as $field )
			{
				echo '<tr><td>' .
					_makeTextInput( 'CUSTOM_' . $field['ID'], false, ParseMLField( $field['TITLE'] ) ) .
					'</td></tr>';
			}
		}

		if ( isset( $fields_RET['numeric'] ) )
		{
			foreach ( (array) $fields_RET['numeric'] as $field )
			{
				echo '<tr><td>' .
					_makeTextInput( 'CUSTOM_' . $field['ID'], true, ParseMLField( $field['TITLE'] ) ) .
					'</td></tr>';
			}
		}

		if ( isset( $fields_RET['date'] ) )
		{
			foreach ( (array) $fields_RET['date'] as $field )
			{
				echo '<tr><td>' .
					_makeDateInput( 'CUSTOM_' . $field['ID'], ParseMLField( $field['TITLE'] ) ) .
					'</td></tr>';
			}
		}

		// Merge select, autos, exports
		// (same or similar SELECT output).
		$fields_RET['select_autos_exports'] = array_merge(
			issetVal( $fields_RET['select'], [] ),
			issetVal( $fields_RET['autos'], [] ),
			issetVal( $fields_RET['exports'], [] )
		);

		// Select.

		foreach ( (array) $fields_RET['select_autos_exports'] as $field )
		{
			$options = $select_options = [];

			$col_name = 'CUSTOM_' . $field['ID'];

			if ( ! $field['REQUIRED'] )
			{
				// Fix #363 Pull-Down field: add "No Value" option.
				$select_options['!'] = _( 'No Value' );
			}

			if ( $field['SELECT_OPTIONS'] )
			{
				$options = explode(
					"\r",
					str_replace( [ "\r\n", "\n" ], "\r", $field['SELECT_OPTIONS'] )
				);
			}

			foreach ( (array) $options as $option )
			{
				$value = $option;

				// Exports specificities.

				if ( $field['TYPE'] === 'exports' )
				{
					$option = explode( '|', $option );

					$option = $value = $option[0];
				}

				if ( $value !== ''
					&& $option !== '' )
				{
					$select_options[$value] = $option;
				}
			}

			if ( $field['TYPE'] === 'autos' )
			{
				// Get autos pull-down edited options.
				$sql_options = "SELECT DISTINCT s." . $col_name . ",upper(s." . $col_name . ") AS SORT_KEY
					FROM students s,student_enrollment sse
					WHERE sse.STUDENT_ID=s.STUDENT_ID
					AND (sse.SYEAR='" . UserSyear() . "' OR sse.SYEAR='" . ( UserSyear() - 1 ) . "')
					AND s." . $col_name . " IS NOT NULL
					AND s." . $col_name . " != ''
					ORDER BY SORT_KEY";

				$options_RET = DBGet( $sql_options );

				// Add the 'new' option, is also the separator.
				$select_options['---'] = '-' . _( 'Edit' ) . '-';

				foreach ( (array) $options_RET as $option )
				{
					if ( ! in_array( $option[$col_name], $select_options ) )
					{
						$select_options[$option[$col_name]] = '<span style="color:blue">' . $option[$col_name] . '</span>';
					}
				}
			}

			echo '<tr><td>' .
				_makeSelectInput( $col_name, $select_options, ParseMLField( $field['TITLE'] ) ) .
				'</td></tr>';
		}

		if ( isset( $fields_RET['textarea'] ) )
		{
			foreach ( (array) $fields_RET['textarea'] as $field )
			{
				echo '<tr><td>' .
					_makeTextAreaInput( 'CUSTOM_' . $field['ID'], ParseMLField( $field['TITLE'] ) ) .
					'</td></tr>';
			}
		}

		if ( ! $_REQUEST['category_id'] || $_REQUEST['category_id'] == '1' )
		{
			$gradelevels_RET = DBGet( "SELECT ID,TITLE
				FROM school_gradelevels
				WHERE SCHOOL_ID='" . UserSchool() . "'
				ORDER BY SORT_ORDER IS NULL,SORT_ORDER" );

			$options = [];

			foreach ( (array) $gradelevels_RET as $gradelevel )
			{
				$options[$gradelevel['ID']] = $gradelevel['TITLE'];
			}

			echo '<tr><td>' .
				_makeSelectInput( 'GRADE_ID', $options, _( 'Grade Level' ) ) .
				'</td></tr>';

			$schools_RET = DBGet( "SELECT ID,TITLE
				FROM schools
				WHERE ID!='" . UserSchool() . "'
				AND SYEAR='" . UserSyear() . "'" );

			$options = [
				UserSchool() => _( 'Next grade at current school' ),
				'0' => _( 'Retain' ),
				'-1' => _( 'Do not enroll after this school year' ),
			];

			foreach ( (array) $schools_RET as $school )
			{
				$options[$school['ID']] = $school['TITLE'];
			}

			echo '<tr><td>' .
				_makeSelectInput( 'NEXT_SCHOOL', $options, _( 'Rolling / Retention Options' ) ) .
				'</td></tr>';

			$calendars_RET = DBGet( "SELECT CALENDAR_ID,DEFAULT_CALENDAR,TITLE
				FROM attendance_calendars
				WHERE SYEAR='" . UserSyear() . "'
				AND SCHOOL_ID='" . UserSchool() . "'
				ORDER BY DEFAULT_CALENDAR ASC" );

			$options = [];

			foreach ( (array) $calendars_RET as $calendar )
			{
				$options[$calendar['CALENDAR_ID']] = $calendar['TITLE'];
			}

			echo '<tr><td>' .
				_makeSelectInput( 'CALENDAR_ID', $options, _( 'Calendar' ) ) .
				'</td></tr>';

			$enrollment_codes_RET = DBGet( "SELECT ID,TITLE AS TITLE
				FROM student_enrollment_codes
				WHERE SYEAR='" . UserSyear() . "'
				AND TYPE='Add'
				ORDER BY SORT_ORDER IS NULL,SORT_ORDER" );

			$options = [];

			foreach ( (array) $enrollment_codes_RET as $enrollment_code )
			{
				$options[$enrollment_code['ID']] = $enrollment_code['TITLE'];
			}

			echo '<tr><td class="nobr">' .
				_makeDateInput( 'START_DATE' ) . ' - ' .
				_makeSelectInput( 'ENROLLMENT_CODE', $options ) .
				FormatInputTitle( _( 'Attendance Start Date this School Year' ) ) .
				'</td></tr>';
		}

		if ( isset( $fields_RET['radio'] ) )
		{
			foreach ( $fields_RET['radio'] as $field )
			{
				echo '<tr><td>' .
					_makeCheckboxInput( 'CUSTOM_' . $field['ID'], ParseMLField( $field['TITLE'] ) ) .
					'</td></tr>';
			}
		}

		echo '</table><br />';
	}

	//Widgets('activity');
	//Widgets('course');
	//Widgets('absences');

	$extra['functions'] = [ 'CHECKBOX' => 'MakeChooseCheckbox' ];
	$extra['columns_before'] = [ 'CHECKBOX' => MakeChooseCheckbox( 'required', 'STUDENT_ID', 'student' ) ];
	$extra['new'] = true;

	Search( 'student_id', $extra );

	if ( $_REQUEST['search_modfunc'] === 'list' )
	{
		echo '<br /><div class="center">' . SubmitButton() . '</div></form>';
	}
}

/**
 * @param $column
 * @param $numeric
 */
function _makeTextInput( $column, $numeric = false, $title = '' )
{
	if ( $numeric === true )
	{
		// Fix Number Field SQL column limit: type numeric(20,2).
		$options = 'type="number" step="any" max="999999999999999999" min="-999999999999999999"';
	}
	else
	{
		$options = 'size=20';
	}

	return TextInput( '', 'values[' . $column . ']', $title, $options );
}

/**
 * @param $column
 * @param $title
 */
function _makeTextAreaInput( $column, $title = '' )
{
	return TextAreaInput( '', 'values[' . $column . ']', $title );
}

/**
 * @param $column
 * @param $title
 */
function _makeDateInput( $column, $title = '' )
{
	return DateInput( '', 'values[' . $column . ']', $title );
}

/**
 * Make Select Input
 *
 * Fix #363 Auto Pull-Down field: add "-Edit-" option
 *
 * @param string $column  Column.
 * @param array  $options Select options.
 * @param string $title   Field Title.
 *
 * @return string Select Input HTML.
 */
function _makeSelectInput( $column, $options, $title = '' )
{
	$extra = 'style="max-width:190px;" autocomplete="off"';

	$name = 'values[' . $column . ']';

	$text_input = '';

	if ( isset( $options['---'] ) )
	{
		if ( AllowEdit()
			&& ! isset( $_REQUEST['_ROSARIO_PDF'] ) )
		{
			// Add hidden & disabled Text input in case user chooses -Edit-.
			$text_input = TextInput(
				'',
				$name . '_text',
				'',
				'size=20 disabled style="display:none;"',
				false
			);
		}

		// @since RosarioSIS 12.5 CSP remove unsafe-inline Javascript
		// When -Edit- option selected, change the auto pull-down to text field.
		$extra .= ' class="onchange-maybe-edit-select"';
	}

	return $text_input . SelectInput( '', $name, $title, $options, 'N/A', $extra );
}

/**
 * Make Checkbox Input
 *
 * Fix #363 Checkbox Field: save unchecked state
 *
 * @param string $column   Column.
 * @param string $title    Field Title.
 *
 * @return string Checkbox Input HTML.
 */
function _makeCheckboxInput( $column, $title = '' )
{
	return CheckboxInput(
		'',
		'values[' . $column . ']',
		$title,
		'',
		false,
		'Yes',
		button( 'help' )
	);
}
