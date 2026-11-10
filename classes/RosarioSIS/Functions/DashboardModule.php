<?php
/**
 * DashboardModule functions class
 *
 * @since 13.0
 *
 * @package RosarioSIS
 */

namespace RosarioSIS\Functions;

class DashboardModule
{
	/** @var array Menu entries and programs for each module */
	protected $menu;

	/**
	 * Constructor
	 */
	function __construct()
	{
		global $_ROSARIO;

		if ( empty( $_ROSARIO['Menu'] ) )
		{
			require_once 'Menu.php';
		}

		$this->menu = $_ROSARIO['Menu'];
	}

	/**
	 * Dashboard module
	 *
	 * Load 'modules/' . $module . '/includes/Dashboard.inc.php' file
	 * Call 'DashboardDefault$ModuleName' function
	 *
	 * @return string Dashboard module HTML.
	 */
	function load( $module )
	{
		if ( mb_strpos( $module, '..' ) !== false
			|| ! file_exists( 'modules/' . $module . '/includes/Dashboard.inc.php' ) )
		{
			return '';
		}

		require_once 'modules/' . $module . '/includes/Dashboard.inc.php';

		$module_function = 'DashboardDefault' . str_replace( '_', '', $module );

		if ( ! function_exists( $module_function ) )
		{
			return '';
		}

		$data = $module_function();

		return $this->add( $module, $data );
	}

	/**
	 * Dashboard Module Title HTML
	 *
	 * @param  string $module Module.
	 * @param  string $data   Icon image path.
	 * @return string Module Title and data HTML.
	 */
	function add( $module, $data )
	{
		global $RosarioCoreModules;

		if ( empty( $data ) )
		{
			return '';
		}

		$module_std = str_replace( '_Premium', '', $module );

		if ( empty( $this->menu[$module] )
			&& empty( $this->menu[$module_std] ) )
		{
			$module_found = false;

			if ( ! in_array( $module, $RosarioCoreModules ) )
			{
				// Add-on. Check if has menu entries by looping on $menu.
				foreach ( $this->menu as $programs )
				{
					foreach ( $programs as $modname => $title )
					{
						if ( is_string( $modname )
							&& mb_strpos( $modname, $module . '/' ) === 0 )
						{
							$module_found = true;

							break;
						}
					}
				}
			}

			if ( ! $module_found )
			{
				// User profile has no access to module.
				return '';
			}
		}

		$html = $this->title( $module_std );

		$html .= $this->data( $data );

		return $html;
	}

	/**
	 * Dashboard Module Title HTML
	 *
	 * @param  string $module Module.
	 *
	 * @return string Module Title HTML.
	 */
	function title( $module )
	{
		global $RosarioCoreModules;

		if ( ! empty( $this->menu[$module]['title'] ) )
		{
			$module_title = $this->menu[$module]['title'];
		}
		else
		{
			$module_title = _( str_replace( '_', ' ', $module ) );
		}

		ob_start();
		?>
		<h3 class="dashboard-module-title">
			<span class="module-icon <?php echo $module; ?>"
				<?php if ( ! in_array( $module, $RosarioCoreModules ) ) :
				// Module is addon, set custom module icon. ?>
				style="background-image: url(modules/<?php echo $module; ?>/icon.png);"
				<?php endif; ?>></span>
			<?php echo $module_title; ?>
		</h3>
		<?php

		return ob_get_clean();
	}

	/**
	 * Dashboard Module Data HTML
	 * Will skip `null` data values.
	 *
	 * @param  array  $data    Array containing values and their title as key.
	 * @param  int    $columns Number of columns to display. Optional. Defaults to 1 and 2 if data > 10.
	 * @return string Module data HTML
	 */
	function data( $data, $columns = 0 )
	{
		// TODO use object, no include
		require_once 'ProgramFunctions/TipMessage.fnc.php';

		if ( empty( $data ) )
		{
			return '';
		}

		$first_value = reset( $data );

		$first_key = key( $data );

		unset( $data[$first_key] );

		// Detail by Profile & Fail.
		$cell = 0;

		$message = '';

		$data = array_filter( $data, function( $value ) {
			return ! is_null( $value );
		});

		if ( $columns < 1 )
		{
			$columns = 1;

			if ( count( $data ) >= 10 )
			{
				$columns = 2;
			}
		}

		foreach ( $data as $title => $value )
		{
			$message .= '<td><span class="legend-gray">' .
				$title . '</span></td><td>' . $value . '</td>';

			if ( ++$cell % $columns === 0 )
			{
				$message .= '</tr><tr>';
			}
		}

		if ( ! $message )
		{
			return '<div class="dashboard-module-data">' . NoInput( $first_value, $first_key ) . '</div>';
		}

		$message = '<table class="dashboard-module-data-tipmsg widefat col1-align-right"><tr>' .
			$message . '</tr></table>';

		return '<div class="dashboard-module-data">' .
		MakeTipMessage( $message, $first_key, NoInput( $first_value, $first_key ) ) . '</div>';
	}
}
