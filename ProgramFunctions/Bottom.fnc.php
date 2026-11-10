<?php
/**
 * Bottom.php related functions
 *
 * @package RosarioSIS
 * @subpackage ProgramFunctions
 */

/**
 * "Back to List" Bottom.php button update
 * Remove need to make an AJAX call to Bottom.php
 *
 * @see Bottom.php
 *
 * @since 12.0 JS Show BottomButtonBack & update its URL & text
 *
 * @param string Back to list PHP self URL.
 */
function BottomButtonBackUpdate( $back_php_self )
{
	$old_list_php_self = $_SESSION['List_PHP_SELF'];

	$_SESSION['List_PHP_SELF'] = PreparePHP_SELF( $_REQUEST, [ 'bottom_back' ] );

	$_SESSION['Back_PHP_SELF'] = $back_php_self;

	$back_url = $_SESSION['List_PHP_SELF'] . '&bottom_back=true';

	switch ( $_SESSION['Back_PHP_SELF'] )
	{
		case 'student':

			$back_text = _( 'Student List' );
		break;

		case 'staff':

			$back_text = _( 'User List' );
		break;

		case 'course':

			$back_text = _( 'Course List' );
		break;

		default:

			$back_text = sprintf( _( '%s List' ), $_SESSION['Back_PHP_SELF'] );
	}

	ob_start();

	// Do bottom_buttons action hook.
	do_action( 'ProgramFunctions/Bottom.fnc.php|bottom_buttons' );

	$bottom_buttons = ob_get_clean();

	if ( ! $bottom_buttons
		&& $old_list_php_self === $_SESSION['List_PHP_SELF'] )
	{
		// Nothing to update, exit.
		return;
	}

	// @since 12.5 CSP remove unsafe-inline Javascript
	?>
	<input type="hidden" disabled id="bottom_button_back_update"
		data-text="<?php echo AttrEscape( $back_text ); ?>"
		data-href="<?php echo URLEscape( $back_url ); ?>"
		data-after="<?php echo AttrEscape( $bottom_buttons ); ?>" />
	<script src="assets/js/csp/programFunctions/BottomButtonBackUpdate.js?v=12.5"></script>
	<?php
}
