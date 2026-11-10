<?php
require_once 'ProgramFunctions/FileUpload.fnc.php';
require_once 'modules/Accounting/functions.inc.php';

if ( User( 'PROFILE' ) === 'teacher' ) //limit to teacher himself
{
	$_REQUEST['staff_id'] = User( 'STAFF_ID' );
}

if ( empty( $_REQUEST['print_statements'] ) )
{
	DrawHeader( ProgramTitle() );

	Search( 'staff_id', issetVal( $extra ) );
}

if ( $_REQUEST['modfunc'] === 'save'
	&& AllowEdit()
	&& UserStaffID() )
{
	// Add eventual Dates to $_REQUEST['values'].
	AddRequestedDates( 'values', 'post' );

	foreach ( (array) $_REQUEST['values'] as $id => $columns )
	{
		if ( $id !== 'new' )
		{
			$columns['FILE_ATTACHED'] = _savePaymentsFile( $id );

			if ( ! $columns['FILE_ATTACHED'] )
			{
				unset( $columns['FILE_ATTACHED'] );
			}

			DBUpdate(
				'accounting_payments',
				$columns,
				[
					'ID' => (int) $id,
					'STAFF_ID' => UserStaffID(),
					'SYEAR' => UserSyear(),
					'SCHOOL_ID' => UserSchool(),
				]
			);
		}
		elseif ( isset( $columns['AMOUNT'] )
			&& is_numeric( $columns['AMOUNT'] )
			&& $columns['PAYMENT_DATE'] )
		{
			$insert_columns = [
				'STAFF_ID' => UserStaffID(),
				'SYEAR' => UserSyear(),
				'SCHOOL_ID' => UserSchool(),
			];

			$columns['FILE_ATTACHED'] = _savePaymentsFile( $id );

			DBInsert(
				'accounting_payments',
				$insert_columns + $columns
			);
		}
	}

	// Unset modfunc, values, accounting_salaries & redirect URL.
	RedirectURL( [ 'modfunc', 'values', 'accounting_salaries' ] );
}

if ( $_REQUEST['modfunc'] === 'remove'
	// @since 12.1 Add Admin Delete Permission
	&& AllowEdit( 'Accounting/StaffPayments.php&modfunc=remove' ) )
{
	if ( DeletePrompt( _( 'Payment' ) ) )
	{
		$file_attached = DBGetOne( "SELECT FILE_ATTACHED
			FROM accounting_payments
			WHERE ID='" . (int) $_REQUEST['id'] . "'
			AND STAFF_ID='" . UserStaffID() . "'
			AND SYEAR='" . UserSyear() . "'
			AND SCHOOL_ID='" . UserSchool() . "'" );

		// Delete File Attached.
		// Security: use FileDelete() instead of unlink()
		FileDelete( $file_attached );

		DBQuery( "DELETE FROM accounting_payments
			WHERE ID='" . (int) $_REQUEST['id'] . "'
			AND STAFF_ID='" . UserStaffID() . "'
			AND SYEAR='" . UserSyear() . "'
			AND SCHOOL_ID='" . UserSchool() . "'" );

		// Unset modfunc & ID & redirect URL.
		RedirectURL( [ 'modfunc', 'id' ] );
	}
}

if ( UserStaffID() && ! $_REQUEST['modfunc'] )
{
	$payments_total = 0;

	$functions = [
		'REMOVE' => '_makePaymentsRemove',
		'AMOUNT' => '_makePaymentsAmount',
		'PAYMENT_DATE' => 'ProperDate',
		'COMMENTS' => '_makePaymentsCommentsInput',
		'FILE_ATTACHED' => '_makePaymentsFileInput',
	];

	$payments_RET = DBGet( "SELECT '' AS REMOVE,ID,AMOUNT,PAYMENT_DATE,COMMENTS,FILE_ATTACHED
		FROM accounting_payments
		WHERE STAFF_ID='" . UserStaffID() . "'
		AND SYEAR='" . UserSyear() . "'
		AND SCHOOL_ID='" . UserSchool() . "'
		ORDER BY PAYMENT_DATE,ID", $functions );

	$i = 1;
	$RET = [];

	foreach ( (array) $payments_RET as $payment )
	{
		$RET[$i] = $payment;
		$i++;
	}

	$columns = [];

	if ( ! empty( $RET )
		&& empty( $_REQUEST['print_statements'] )
		&& AllowEdit()
		&& ! isset( $_REQUEST['_ROSARIO_PDF'] ) )
	{
		$columns = [ 'REMOVE' => '<span class="a11y-hidden">' . _( 'Delete' ) . '</span>' ];
	}

	$columns += [
		'AMOUNT' => _( 'Amount' ),
		'PAYMENT_DATE' => _( 'Date' ),
		'COMMENTS' => _( 'Comment' ),
	];

	if ( empty( $_REQUEST['print_statements'] ) )
	{
		$columns += [ 'FILE_ATTACHED' => _( 'File Attached' ) ];
	}

	$link = [];

	if ( empty( $_REQUEST['print_statements'] )
		&& AllowEdit() )
	{
		$link['add']['html'] = [
			'REMOVE' => button( 'add' ),
			'AMOUNT' => _makePaymentsTextInput( '', 'AMOUNT' ),
			'PAYMENT_DATE' => _makePaymentsDateInput( DBDate(), 'PAYMENT_DATE' ),
			'COMMENTS' => _makePaymentsCommentsInput( '', 'COMMENTS' ),
			'FILE_ATTACHED' => _makePaymentsFileInput( '', 'FILE_ATTACHED' ),
		];
	}

	$options = [];

	if ( empty( $_REQUEST['print_statements'] )
		&& AllowEdit() )
	{
		echo '<form action="' . URLEscape(
			'Modules.php?modname=' . $_REQUEST['modname'] . '&modfunc=save&staff_id=' . UserStaffID()
		) . '" method="POST">';
		DrawHeader( '', SubmitButton() );
		$options = [ 'valign-middle' => true ];
	}

	ListOutput( $RET, $columns, 'Payment', 'Payments', $link, [], $options );

	if ( empty( $_REQUEST['print_statements'] )
		&& AllowEdit() )
	{
		echo '<div class="center">' . SubmitButton() . '</div>';
	}

	echo '<br />';

	$salaries_total = DBGetOne( "SELECT SUM(f.AMOUNT) AS TOTAL
		FROM accounting_salaries f
		WHERE f.STAFF_ID='" . UserStaffID() . "'
		AND f.SYEAR='" . UserSyear() . "'
		AND f.SCHOOL_ID='" . UserSchool() . "'" );

	$table = '<table class="align-right accounting-staff-payroll-totals"><tr><td>' . _( 'Total from Salaries' ) . ': ' . '</td><td>' . Currency( $salaries_total ) . '</td></tr>';

	$table .= '<tr><td>' . _( 'Less' ) . ': ' . _( 'Total from Staff Payments' ) . ': ' . '</td><td>' . Currency( $payments_total ) . '</td></tr>';

	$table .= '<tr><td>' . _( 'Balance' ) . ': </td>
		<td><b>' . Currency(  ( $salaries_total - $payments_total ), 'CR' ) .
		'</b></td></tr></table>';

	DrawHeader( $table );

	if ( empty( $_REQUEST['print_statements'] )
		&& AllowEdit() )
	{
		echo '</form>';
	}
}
