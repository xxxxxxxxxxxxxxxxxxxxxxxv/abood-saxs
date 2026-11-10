<?php

Widgets( 'mailing_labels' );
Widgets( 'balance' );

if ( empty( $_REQUEST['search_modfunc'] ) )
{
	DrawHeader( ProgramTitle() );

	$extra['new'] = true;
	$extra['action'] = empty( $extra['action'] ) ? '&_ROSARIO_PDF=true' : $extra['action'] . '&_ROSARIO_PDF=true';

	Search( 'student_id', $extra );
}
else
{
	// For the Student Fees / Student Payments programs.
	$_REQUEST['print_statements'] = true;

	if ( isset( $_REQUEST['mailing_labels'] )
		&& $_REQUEST['mailing_labels'] === 'Y' )
	{
		$extra['group'][] = 'ADDRESS_ID';
	}

	$students_RET = GetStuList( $extra );

	if ( ! empty( $students_RET ) )
	{
		$SESSION_student_id_save = UserStudentID();

		$handle = PDFStart();

		$user_schools = trim( (string) User( 'SCHOOLS' ), ',' ) ?
			explode( ',', trim( User( 'SCHOOLS' ), ',' ) ) : '';

		foreach ( (array) $students_RET as $student )
		{
			if ( isset( $_REQUEST['mailing_labels'] )
				&& $_REQUEST['mailing_labels'] === 'Y' )
			{
				foreach ( (array) $student as $address )
				{
					unset( $_ROSARIO['DrawHeader'] );

					DrawHeader( _( 'Statement' ) );
					DrawHeader( $address['FULL_NAME'], $address['STUDENT_ID'] );
					DrawHeader( $address['GRADE_ID'] );
					DrawHeader( SchoolInfo( 'TITLE' ), ProperDate( DBDate() ) );

					// @since 11.6 Add Mailing Label position
					echo MailingLabelPositioned( $address['MAILING_LABEL'] );

					if ( ! empty( $_REQUEST['_search_all_schools'] )
						&& $address['SCHOOL_ID'] != UserSchool() )
					{
						// Check user is in student's school.
						if ( ! $user_schools
							|| in_array( $address['SCHOOL_ID'], $user_schools ) )
						{
							// Fix HACKING ATTEMPT in SetUserStudentID() when "Search All Schools" checked
							$_SESSION['UserSchool'] = $address['SCHOOL_ID'];
						}
					}

					SetUserStudentID( $address['STUDENT_ID'] );

					require 'modules/Student_Billing/StudentFees.php';
					require 'modules/Student_Billing/StudentPayments.php';

					echo '<div style="page-break-after: always;"></div>';
				}
			}
			else
			{
				if ( ! empty( $_REQUEST['_search_all_schools'] )
					&& $student['SCHOOL_ID'] != UserSchool() )
				{
					// Check user is in student's school.
					if ( ! $user_schools
						|| in_array( $student['SCHOOL_ID'], $user_schools ) )
					{
						// Fix HACKING ATTEMPT in SetUserStudentID() when "Search All Schools" checked
						$_SESSION['UserSchool'] = $student['SCHOOL_ID'];
					}
				}

				SetUserStudentID( $student['STUDENT_ID'] );

				unset( $_ROSARIO['DrawHeader'] );

				DrawHeader( _( 'Statement' ) );
				DrawHeader( $student['FULL_NAME'], $student['STUDENT_ID'] );
				DrawHeader( $student['GRADE_ID'] );
				DrawHeader( SchoolInfo( 'TITLE' ), ProperDate( DBDate() ) );

				require 'modules/Student_Billing/StudentFees.php';
				require 'modules/Student_Billing/StudentPayments.php';

				echo '<div style="page-break-after: always;"></div>';
			}
		}

		$_SESSION['student_id'] = $SESSION_student_id_save;

		PDFStop( $handle );
	}
	else
		BackPrompt( _( 'No Students were found.' ) );
}
