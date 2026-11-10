<?php

if ( $_REQUEST['modfunc'] === 'save' )
{
	if ( ! empty( $_REQUEST['student'] )
		&& AllowEdit() )
	{
		//FJ fix SQL bug invalid amount

		if ( is_numeric( $_REQUEST['amount'] ) )
		{
			$due_date = RequestedDate( 'due', '' );

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
				$inserted = DBInsert(
					'billing_fees',
					[
						'SYEAR' => UserSyear(),
						'SCHOOL_ID' => UserSchool(),
						'STUDENT_ID' => (int) $student_id,
						'TITLE' => $_REQUEST['title'],
						'AMOUNT' => $_REQUEST['amount'],
						'ASSIGNED_DATE' => DBDate(),
						'DUE_DATE' => $due_date,
						'COMMENTS' => $_REQUEST['comments'],
						// @since 11.2 Add CREATED_BY column to billing_fees & billing_payments tables
						'CREATED_BY' => DBEscapeString( User( 'NAME' ) ),
					]
				);
			}

			if ( ! empty( $inserted ) )
			{
				$note[] = button( 'check' ) . '&nbsp;' . _( 'That fee has been added to the selected students.' );
			}
		}
		else
		{
			$error[] = _( 'Please enter a valid Amount.' );
		}
	}
	else
	{
		$error[] = _( 'You must choose at least one student.' );
	}

	// Unset modfunc, student & redirect URL.
	RedirectURL( [ 'modfunc', 'student' ] );
}

if ( ! $_REQUEST['modfunc'] )
{
	DrawHeader( ProgramTitle() );

	echo ErrorMessage( $error );

	echo ErrorMessage( $note, 'note' );

	if ( $_REQUEST['search_modfunc'] === 'list' )
	{
		echo '<form action="' . URLEscape( 'Modules.php?modname=' . $_REQUEST['modname'] . '&modfunc=save' ) . '" method="POST">';
		DrawHeader( '', SubmitButton( _( 'Add Fee to Selected Students' ) ) );

		echo '<br />';

		PopTable( 'header', _( 'Fee' ) );

		echo '<table><tr><td>' . TextInput(
			'',
			'title',
			_( 'Title' ),
			'required size="20"'
		) . '</td></tr>';

		echo '<tr><td>' . TextInput(
			'',
			'amount',
			_( 'Amount' ),
			' type="number" step="0.01" max="999999999999" min="-999999999999" required'
		) . '</td></tr>';

		echo '<tr><td>' . DateInput( '', 'due', _( 'Due Date' ), false ) . '</td></tr>';

		echo '<tr><td>' . TextInput(
			'',
			'comments',
			_( 'Comment' ),
			'maxlength="1000" size="25"'
		) . '</td></tr></table>';

		PopTable( 'footer' );

		echo '<br />';
	}

	$extra['link'] = [ 'FULL_NAME' => false ];
	$extra['SELECT'] = ",NULL AS CHECKBOX";
	$extra['functions'] = [ 'CHECKBOX' => 'MakeChooseCheckbox' ];
	$extra['columns_before'] = [ 'CHECKBOX' => MakeChooseCheckbox( 'required', 'STUDENT_ID', 'student' ) ];
	$extra['new'] = true;

	Search( 'student_id', $extra );

	if ( $_REQUEST['search_modfunc'] === 'list' )
	{
		echo '<br /><div class="center">' . SubmitButton( _( 'Add Fee to Selected Students' ) ) . '</div>';
		echo '</form>';
	}
}
