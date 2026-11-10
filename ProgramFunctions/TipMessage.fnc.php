<?php
/**
 * Tip Message functions
 *
 * @package RosarioSIS
 * @subpackage ProgramFunctions
 */

/**
 * Make Tip Message
 *
 * @example MakeTipMessage( '<img src="' . URLEscape( $picture_path ) . '" width="150">', $title, $title );
 *
 * @uses DHTML tip message JS plugin
 *
 * @see assets/js/tipmessage/
 * @see assets/js/csp/csp.js
 *
 * @param  string $message Tip message.
 * @param  string $title   Tip title.
 * @param  string $label   Tip label.
 *
 * @return string Tip Message
 */
function MakeTipMessage( $message, $title, $label )
{
	if ( isset( $_REQUEST['_ROSARIO_PDF'] ) )
	{
		return '<div class="tipmsg-label">' . $label . '</div>';
	}

	// @since 12.5 CSP remove unsafe-inline Javascript
	return '<div class="tipmsg-label onmouseover-tipmsg" data-title="' .
		AttrEscape( $title ) . '" data-msg="' . AttrEscape( $message ) . '">' .
		$label . '</div>';
}


/**
 * Make Student Photo Tip Message
 * Look for current & previous school year Photos
 *
 * @example require_once 'ProgramFunctions/TipMessage.fnc.php';
 *          return MakeStudentPhotoTipMessage( $THIS_RET['STUDENT_ID'], $full_name );
 *
 * @uses MakeTipMessage()
 *
 * @global $StudentPicturesPath Student Pictures Path
 *
 * @param  string $student_id Student ID.
 * @param  string $title      Tip title & label.
 *
 * @return string Student Photo Tip Message or $title if no Photo found
 */
function MakeStudentPhotoTipMessage( $student_id, $title )
{
	global $StudentPicturesPath;

    // @since 9.0 Fix Improper Access Control security issue: add random string to photo file name.
    $picture_path = (array) glob( $StudentPicturesPath . '*/' . $student_id . '.*jpg' );

    $picture_path = end( $picture_path );

	if ( $picture_path )
	{
		return MakeTipMessage( '<img src="' . URLEscape( $picture_path ) . '" width="150">', $title, $title );
	}

	return $title;
}


/**
 * Make User Photo Tip Message
 * Look for current & previous school year Photos
 *
 * @example require_once 'ProgramFunctions/TipMessage.fnc.php';
 *          return MakeUserPhotoTipMessage( $THIS_RET['STAFF_ID'], $full_name, $THIS_RET['ROLLOVER_ID'] );
 *
 * @since 3.8
 *
 * @uses MakeTipMessage()
 *
 * @global $UserPicturesPath User Pictures Path
 *
 * @param  string $staff_id          Staff ID.
 * @param  string $title             Tip title & label.
 * @param  int    $staff_rollover_id Staff Rollover ID (to get last year's photo). Defaults to 0.
 *
 * @return string User Photo Tip Message or $title if no Photo found
 */
function MakeUserPhotoTipMessage( $staff_id, $title, $staff_rollover_id = 0 )
{
	global $UserPicturesPath;

    // @since 9.0 Fix Improper Access Control security issue: add random string to photo file name.
    $picture_path = (array) glob( $UserPicturesPath . UserSyear() . '/' . $staff_id . '.*jpg' );

    $picture_path = end( $picture_path );

    if ( ! $picture_path
        && $staff_rollover_id )
    {
        // Use Last Year's if Missing.
        // @since 9.0 Fix Improper Access Control security issue: add random string to photo file name.
        $picture_path = (array) glob( $UserPicturesPath . ( UserSyear() - 1 ) . '/' . $staff_rollover_id . '.*jpg' );

        $picture_path = end( $picture_path );
    }

	if ( $picture_path )
	{
		return MakeTipMessage( '<img src="' . URLEscape( $picture_path ) . '" width="150">', $title, $title );
	}

	return $title;
}
