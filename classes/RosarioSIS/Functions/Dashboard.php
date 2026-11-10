<?php
/**
 * Dashboard functions class
 *
 * @since 13.0
 *
 * @package RosarioSIS
 */

namespace RosarioSIS\Functions;

class Dashboard
{
	/** @var array Dashboard HTML */
	protected $dashboard = [];

	/** @var object DashboardModule object instance */
	protected $module;

	/**
	 * Dashboard build
	 *
	 * Calls, for each active and user module
	 * the `Dashboard[Module_Name]` function.
	 *
	 * Place your add-on module `Dashboard[Module_Name]` function in the functions.php file.
	 *
	 * @uses $_REQUEST['_ROSARIO_DASHBOARD']
	 *
	 * @todo For example, set $_REQUEST['_ROSARIO_DASHBOARD']['export'] to 1 to export data.
	 * In URL: &_ROSARIO_DASHBOARD[export]=1
	 *
	 * @global $RosarioModules
	 * @see DashboardModule.php for default core modules `Dashboard[Module_Name]` functions.
	 *
	 * @param object $module DashboardModule object instance. Optional.
	 */
	function __construct( $module = null )
	{
		global $RosarioModules;

		if ( $module )
		{
			$this->module = $module;
		}
		// (Re)create it, if it's gone missing.
		elseif ( ! is_object( $this->module ) )
		{
			$this->module = new DashboardModule();
		}

		if ( ! empty( $_REQUEST['_ROSARIO_DASHBOARD'] ) )
		{
			$this->dashboard = array_merge_recursive( $this->dashboard, $_REQUEST['_ROSARIO_DASHBOARD'] );
		}

		foreach ( $RosarioModules as $module => $activated )
		{
			if ( ! $activated )
			{
				// Module not activated.
				continue;
			}

			$html = $this->module->load( $module );

			$this->add( $module, $html, true );
		}
	}

	/**
	 * Dashboard Output HTML
	 * Modules HTML inside PopTable
	 *
	 * @param integer $rows Number of modules per row, defaults to 4. Optional.
	 */
	function output( $rows = 4 )
	{
		if ( empty( $this->dashboard ) )
		{
			return;
		}

		echo '<br>';

		PopTable( 'header', _( 'Dashboard' ), 'width="100%"' );

		?>
		<table class="dashboard width-100p valign-top fixed-col"><tr class="st">
		<?php

		if ( $rows < 1 )
		{
			$rows = 4;
		}

		$row = 0;

		// Output Dashboard modules, 4 per row.

		foreach ( $this->dashboard as $html ): ?>

			<td><?php echo $html; ?></td>

			<?php

		if ( ++$row % $rows === 0 ): ?>

				</tr><tr class="st">

			<?php endif;
		endforeach;

		?>
		</tr></table>
		<?php

		PopTable( 'footer' );
	}

	/**
	 * Add module HTML to Dashboard
	 *
	 * Add module HTML to $this->dashboard[ $module ]
	 *
	 * @param string  $module Module.
	 * @param string  $html   Dashboard HTML.
	 * @param boolean $append Append HTML.
	 */
	function add( $module, $html, $append = true )
	{
		if ( empty( $html ) )
		{
			return;
		}

		if ( $append
			&& ! empty( $this->dashboard[$module] ) )
		{
			$this->dashboard[$module] .= $html;
		}
		else
		{
			$this->dashboard[$module] = $html;
		}
	}
}
