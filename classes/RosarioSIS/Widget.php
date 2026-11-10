<?php
/**
 * (Student) Widget interface
 *
 * @since 8.6
 *
 * @package RosarioSIS
 */

namespace RosarioSIS;

// Widget interface.
// Implement this interface when creating a new Widget.
interface Widget
{
	/**
	 * Check whether Widget can be built:
	 * Usually check if the corresponding Module is active
	 * Maybe check if User is admin
	 * Maybe check if AllowUse() for corresponding modname
	 *
	 * @param  array  $modules $RosarioModules global.
	 *
	 * @return bool True if can build Widget, else false.
	 */
	public function canBuild( $modules );

	/**
	 * Build extra SQL, and search terms
	 *
	 * @param  array  $extra Extra array, see definition in Widgets class.
	 *
	 * @return array         $extra array with Widget extra added.
	 */
	public function extra( $extra );

	/**
	 * Build HTML form
	 *
	 * @return string HTML form.
	 */
	public function html();
}
