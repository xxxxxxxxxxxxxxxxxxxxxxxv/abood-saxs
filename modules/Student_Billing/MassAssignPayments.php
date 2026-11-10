<?php
require_once 'modules/Student_Billing/functions.inc.php';

if ( $_REQUEST['modfunc'] === 'save' )
{
	if ( ! empty( $_REQUEST['student'] ) && AllowEdit() )
	{
		$date = RequestedDate( 'date', '' );

		// FJ fix SQL bug invalid amount.

		if ( is_numeric( $_REQUEST['amount'] ) )
		{
			if ( $date )
			{
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
					$id = DBInsert(
						'billing_payments',
						[
							'SYEAR' => UserSyear(),
							'SCHOOL_ID' => UserSchool(),
							'STUDENT_ID' => (int) $student_id,
							'PAYMENT_DATE' => $date,
							'AMOUNT' => $_REQUEST['amount'],
							'COMMENTS' => $_REQUEST['comments'],
							'LUNCH_PAYMENT' => $_REQUEST['lunch_payment'],
							// @since 11.2 Add CREATED_BY column to billing_fees & billing_payments tables
							'CREATED_BY' => DBEscapeString( User( 'NAME' ) ),
						],
						'id'
					);

					if ( $_REQUEST['lunch_payment']
						&& ProgramConfig( 'student_billing', 'STUDENT_BILLING_CREDIT_FOOD_SERVICE_ACCOUNT' ) )
					{
						// @since 13.0 Add "Credit Food Service Account on Lunch Payment" config option
						_creditPaymentsFoodServiceAccount( $id );
					}
				}

				if ( ! empty( $id ) )
				{
					$note[] = button( 'check' ) . '&nbsp;' . _( 'That payment has been added to the selected students.' );
				}
			}
			else
			{
				$error[] = _( 'The date you entered is not valid' );
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

		DrawHeader( '', SubmitButton( _( 'Add Payment to Selected Students' ) ) );

		echo '<br />';

		PopTable( 'header', _( 'Payment' ) );

		echo '<table><tr><td>' . TextInput(
			'',
			'amount',
			_( 'Amount' ),
			'  type="number" step="0.01" max="999999999999" min="-999999999999" required'
		) . '</td></tr>';

		echo '<tr><td>' . DateInput(
			DBDate(),
			'date',
			_( 'Date' ),
			false,
			false
		) . '</td></tr>';

		echo '<tr><td>' . TextInput(
			'',
			'comments',
			_( 'Comment' ),
			'maxlength="1000" size="25"'
		) . '</td></tr>';

		// @since 13.0 Add "Lunch Payment" checkbox
		echo '<tr><td>' . CheckboxInput(
			'',
			'lunch_payment',
			_( 'Lunch Payment' ),
			'',
			true
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
		echo '<br /><div class="center">' . SubmitButton( _( 'Add Payment to Selected Students' ) ) . '</div>';
		echo '</form>';
	}
}
