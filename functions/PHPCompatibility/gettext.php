<?php
/**
 * Implementation for PHP gettext extension functions not included by default.
 *
 * @since 3.8
 * @since 12.7 Autoload classes (PSR-4)
 *
 * @copyright PhpMyAdmin
 *
 * @link https://github.com/phpmyadmin/motranslator
 *
 * @package RosarioSIS
 * @subpackage functions
 */

if ( ! function_exists( 'gettext' ) )
{
	// Load compatibility layer.
	PhpMyAdmin\MoTranslator\Loader::loadFunctions();
}
