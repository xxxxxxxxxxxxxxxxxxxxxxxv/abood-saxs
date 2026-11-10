<?php
/**
 * Prompt functions
 *
 * @package RosarioSIS
 * @subpackage functions
 */

/**
 * Prompt before Delete
 * and display OK & Cancel buttons
 *
 * Go back in browser history on Cancel (unless $remove_modfunc_on_cancel = false)
 *
 * @since 11.4 Use 'delete_ok' URL param instead of submit button name
 * @since 12.1 JS If inside colorBox, close it on Cancel
 * @since 12.5 CSP remove unsafe-inline Javascript: add .button-prompt-cancel CSS class
 *
 * @example if ( DeletePrompt( _( 'Title' ) ) ) DBQuery( "DELETE FROM BOK WHERE ID='" . (int) $_REQUEST['benchmark_id'] . "'" );
 *
 * @param  string  $title                    Prompt title.
 * @param  string  $action                   Prompt action (optional). Defaults to 'Delete'.
 * @param  boolean $remove_modfunc_on_cancel Remove &modufnc=XXX part of the cancel button URL (optional).
 *
 * @return boolean true if user clicks OK or Cancel + modfunc, else false
 */
function DeletePrompt( $title, $action = 'Delete', $remove_modfunc_on_cancel = true )
{
	// Display prompt.
	if ( empty( $_REQUEST['delete_ok'] )
		&& empty( $_REQUEST['delete_cancel'] ) )
	{
		// Set default action text.
		if ( $action === 'Delete' )
		{
			$action = _( 'Delete' );
		}

		if ( mb_substr( $_SESSION['locale'], 0, 2 ) !== 'de' )
		{
			// We are inside a sentence, convert nouns to lowercase (except for German).
			$title = mb_strtolower( $title );
		}

		// Force action to lowercase.
		$action = mb_strtolower( $action );

		echo '<br>';

		$PHP_tmp_SELF = PreparePHP_SELF( $_REQUEST, [ 'delete_cancel' ], [ 'delete_ok' => '1' ] );

		if ( ! $remove_modfunc_on_cancel )
		{
			$PHP_tmp_SELF_cancel = PreparePHP_SELF( $_REQUEST, [], [ 'delete_cancel' => '1' ] );
		}

		PopTable( 'header', _( 'Confirm' ) . ( mb_strpos( $action, ' ' ) === false ? ' ' . $action : '' ) );

		$onclick_ajax_link = $js = '';

		if ( ! $remove_modfunc_on_cancel )
		{
			$onclick_ajax_link = ' class="onclick-ajax-link" data-link="' . $PHP_tmp_SELF_cancel . '"';
		}
		else
		{
			// @since 12.5 CSP remove unsafe-inline Javascript
			$js = '<script src="assets/js/csp/functions/Prompt.js?v=12.5"></script>';
		}

		echo '<br><div class="center">' . button( 'warning', '', '', 'bigger' ) .
			'<h4>' . sprintf( _( 'Are you sure you want to %s that %s?' ), $action, $title ) . '</h4>
			<form action="' . $PHP_tmp_SELF . '" method="POST">' .
				SubmitButton( _( 'OK' ), 'delete_ok', '' ) .
				'<input type="button" name="delete_cancel" class="button-primary button-prompt-cancel" value="' .
				AttrEscape( _( 'Cancel' ) ) . '"' . $onclick_ajax_link . '>
			</form>
		</div><br>' . $js;

		PopTable( 'footer' );

		return false;
	}

	// If user clicked OK or Cancel + modfunc.
	RedirectURL( [ 'delete_ok', 'delete_cancel' ] );

	return true;
}


/**
 * Prompt question to user
 * and display OK & Cancel buttons
 *
 * Go back in browser history on Cancel
 *
 * @since 11.4 Use 'delete_ok' URL params instead of submit button name
 * @since 12.1 JS If inside colorBox, close it on Cancel
 * @since 12.5 CSP remove unsafe-inline Javascript: add .button-prompt-cancel CSS class
 *
 * @example if ( Prompt( _( 'Confirm' ), _( 'Do you want to dance?' ), $message ) )
 *
 * @param  string  $title    Prompt title (optional). Defaults to 'Confirm'.
 * @param  string  $question Prompt question (optional). Defaults to ''.
 * @param  string  $message  Prompt message (optional). Defaults to ''.
 *
 * @return boolean true if user clicks OK, else false
 */
function Prompt( $title = 'Confirm', $question = '', $message = '' )
{
	// Display prompt.
	if ( empty( $_REQUEST['delete_ok'] ) )
	{
		// Set default title.
		if ( $title === 'Confirm' )
		{
			$title = _( 'Confirm' );
		}

		echo '<br>';

		$PHP_tmp_SELF = PreparePHP_SELF( $_REQUEST, [], [ 'delete_ok' => '1' ] );

		PopTable( 'header', $title );

		// @since 12.5 CSP remove unsafe-inline Javascript
		$js = '<script src="assets/js/csp/functions/Prompt.js?v=12.5"></script>';

		echo '<h4 class="center">' . $question . '</h4>
			<form action="' . $PHP_tmp_SELF . '" method="POST">' .
				$message .
				'<div class="center"><br>' .
				SubmitButton( _( 'OK' ), 'delete_ok', '' ) .
				// If inside colorBox, close it. Otherwise, go back in browser history.
				'<input type="button" name="delete_cancel" class="button-primary button-prompt-cancel" value="' .
					AttrEscape( _( 'Cancel' ) ) . '">
				</div>
			</form><br>' . $js;

		PopTable( 'footer' );

		return false;
	}

	// If user clicked OK.
	RedirectURL( 'delete_ok' );

	return true;
}


/**
 * Prompt message in JS Alert box & close window
 *
 * Use the BackPrompt function only if there is an error
 * in a script opened in a new window (ie. PDF printing)
 * BackPrompt will alert the message and close the window
 *
 * @param  string $message Alert box message.
 *
 * @return string JS Alert box & close window, then exits
 */
function BackPrompt( $message )
{
	// @since 12.5 CSP remove unsafe-inline Javascript
	?>
	<input type="hidden" disabled id="back_prompt" value="<?php echo AttrEscape( $message ); ?>" />
	<script src="assets/js/csp/functions/BackPrompt.js?v=12.5"></script>

	<?php exit();
}
