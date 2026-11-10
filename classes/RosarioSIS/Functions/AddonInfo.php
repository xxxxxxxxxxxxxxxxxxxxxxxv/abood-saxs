<?php
/**
 * Add-on info functions class
 *
 * @since 13.0
 *
 * @package RosarioSIS
 */

namespace RosarioSIS\Functions;

class AddonInfo
{
	/** @var string Add-on type */
	protected $type;

	/** @var string Add-on directory */
	protected $addon_dir;

	/** @var string composer.json file cache */
	protected $cache;

	/** @var string Remote composer.json file cache */
	protected $cache_remote;

	/** @var array Minimum required version */
	public $min_required_version = [];

	/**
	 * Constructor
	 *
	 * @param string $type      Add-on type: module|plugin.
	 * @param string $addon_dir Add-on directory. For example: 'My_Module'.
	 */
	function __construct( $type, $addon_dir )
	{
		$this->type = in_array( $type, [ 'module', 'plugin' ] ) ? $type : '';

		$this->addon_dir = mb_strpos( $addon_dir, '..' ) === false ? $addon_dir : '';
	}

	/**
	 * Get Addon Info from the composer.json file
	 *
	 * @example $version = (new RosarioSIS\Functions\AddonInfo( 'module', 'Staff_Absences' ))->get( [ 'version' ] );
	 *
	 * @param array  $path JSON object path / keys (optional).
	 *
	 * @return mixed Null if no JSON or path not found, else array or JSON value.
	 */
	function get( $path = [] )
	{
		if ( isset( $this->cache ) )
		{
			$data = $this->cache;
		}
		else
		{
			$json_file = $this->type . 's/' . $this->addon_dir . '/composer.json';

			if ( ! file_exists( $json_file ) )
			{
				return null;
			}

			$json = file_get_contents( $json_file );

			// Decode the JSON string into an associative array
			$data = json_decode( (string) $json, true );

			if ( ! $data )
			{
				return null;
			}

			$this->cache = $data;
		}

		foreach ( (array) $path as $path_elem )
		{
			if ( ! isset( $data[ $path_elem ] ) )
			{
				return null;
			}

			$data = $data[ $path_elem ];
		}

		return $data;
	}

	/**
	 * Get Addon Info from the remote composer.json file
	 *
	 * @example $version_remote = AddonInfoRemoteGet( $this->type, $this->addon_dir, [ 'version' ] );
	 *
	 * @param array  $path JSON object path / keys (optional).
	 *
	 * @return mixed Null if no JSON or path not found, else array or JSON value.
	 */
	function remoteGet( $path = [] )
	{
		if ( isset( $this->cache_remote ) )
		{
			$data = $this->cache_remote;
		}
		else
		{
			$url = $this->get( [ 'extra', 'self_url' ] );

			if ( ! filter_var( (string) $url, FILTER_VALIDATE_URL ) )
			{
				return null;
			}

			try
			{
				$curl = new \curl( [ 'cache' => true ] );

				$json = $curl->get( $url );
			}
			catch ( Exception $e )
			{
				// No curl installed.
				$error[] = $e->getMessage();

				return null;
			}

			// Decode the JSON string into an associative array
			$data = json_decode( (string) $json, true );

			if ( ! $data )
			{
				return null;
			}

			$this->cache_remote = $data;
		}

		foreach ( (array) $path as $path_elem )
		{
			if ( ! isset( $data[ $path_elem ] ) )
			{
				return null;
			}

			$data = $data[ $path_elem ];
		}

		return $data;
	}

	/**
	 * Check required version against current version
	 * Note: won't work with values such as "<10.2" or "<=10.2"
	 * We detect the min required version and compare it with the ">=" operator only.
	 *
	 * Sets $this->min_required_version for use in other methods or publicly
	 *
	 * @param  array  $path            JSON object path / keys.
	 * @param  string $current_version Current version.
	 *
	 * @return bool   True if no required version, false if current version does not meet required version
	 */
	function checkRequiredVersion( $path, $current_version )
	{
		$require = $this->get( $path );

		$min_required_version = $this::getMinSemanticVersion( $require );

		$this->min_required_version[ end( $path ) ] = $min_required_version;

		return ! $min_required_version
			|| version_compare( (string) $current_version, (string) $min_required_version, '>=' );
	}

	/**
	 * Get minimum semantic version
	 *
	 * @link https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/master/src/ComposerJsonReader.php
	 *
	 * @example $min_version = RosarioSIS\Functions\AddonInfo::getMinSemanticVersion( $version );
	 *
	 * @param  string $version Version(s) as per accepted in composer.json files.
	 *
	 * @return string Null if no version found, min version otherwise.
	 */
	static function getMinSemanticVersion( $version )
	{
		if ( '' === $version || null === $version )
		{
			return null;
		}

		/** @var non-empty-list<string> $arr */
		$arr = preg_split( '/\s*\|\|?\s*/', trim( $version ) );

		$arr = array_map( static function( $v )
		{
			$v = ltrim( $v, 'v^~>= ' );

			$v = substr( $v, 0, strcspn( $v, ' ,-' ) );

			if ( substr( $v, -2 ) === '.*' )
			{
				$v = substr( $v, 0, -strlen( '.*' ) );
			}

			return $v;
		}, $arr );

		$text_version = null;

		foreach ( $arr as $key => $v )
		{
			if ( true === preg_match( '/^\D/', $v ) )
			{
				$text_version = $v;
			}
		}

		if ( null !== $text_version )
		{
			return null;
		}

		$min = $arr[0];

		foreach ( (array) $arr as $v )
		{
			if ( version_compare( $v, $min, '<' ) )
			{
				$min = $v;
			}
		}

		$parts = explode( '.', $min );

		return sprintf( '%s.%s', (int) $parts[0], (int) ( isset( $parts[1] ) ? $parts[1] : 0 ) );
	}

	/**
	 * Check add-on requirements
	 * - RosarioSIS version
	 * - Standard add-on version (when checking Premium add-on)
	 * - PHP version
	 * - PHP extensions
	 *
	 * @global $error Error message
	 *
	 * @uses $this->checkRequiredVersion()
	 *
	 * @example (new RosarioSIS\Functions\AddonInfo( 'module', $_REQUEST['module'] ))->checkRequirements();
	 *
	 * @param string $this->type      Add-on type: module|plugin.
	 * @param string $this->addon_dir Add-on directory. For example: 'My_Module'.
	 *
	 * @return bool  True if add-on requirements are met, false otherwise.
	 */
	function checkRequirements()
	{
		global $error;

		$return = true;

		if ( ! $this->get() )
		{
			// No composer.json found.
			return true;
		}

		if ( ! $this->checkRequiredVersion(
				[ 'extra', 'require', 'francoisjacquet/rosariosis' ],
				ROSARIO_VERSION
			) )
		{
			$error[] = sprintf(
				_( 'The %s add-on requires %s version %s. You are currently running %s version %s.' ),
				dgettext( $this->addon_dir, str_replace( '_', ' ', $this->addon_dir ) ),
				'RosarioSIS >=',
				$this->min_required_version['francoisjacquet/rosariosis'],
				'RosarioSIS',
				ROSARIO_VERSION
			);

			$return = false;
		}

		if ( mb_strpos( $this->addon_dir, '_Premium' ) !== false )
		{
			$standard_addon_dir = str_replace( '_Premium', '', $this->addon_dir );

			$standard_version = (new AddonInfo( $this->type, $standard_addon_dir ))->get( [ 'version' ] );

			if ( ! $this->checkRequiredVersion(
					[ 'extra', 'require', 'francoisjacquet/' . mb_strtolower( $standard_addon_dir ) ],
					$standard_version
				) )
			{
				$standard_locale = dgettext( $standard_addon_dir, str_replace( '_', ' ', $standard_addon_dir ) );

				$error[] = sprintf(
					_( 'The %s add-on requires %s version %s. You are currently running %s version %s.' ),
					dgettext( $this->addon_dir, str_replace( '_', ' ', $this->addon_dir ) ),
					$standard_locale . ' >=',
					$this->min_required_version['francoisjacquet/' . mb_strtolower( $standard_addon_dir )],
					$standard_locale,
					issetVal( $standard_version, '?' )
				);

				$return = false;
			}
		}

		if ( ! $this->checkRequiredVersion( [ 'require', 'php' ], PHP_VERSION ) )
		{
			$error[] = sprintf(
				_( 'The %s add-on requires %s version %s. You are currently running %s version %s.' ),
				dgettext( $this->addon_dir, str_replace( '_', ' ', $this->addon_dir ) ),
				'PHP >=',
				$this->min_required_version['php'],
				'PHP',
				PHP_VERSION
			);

			$return = false;
		}

		$require = $this->get( [ 'require' ] );

		foreach ( (array) $require as $require_ext => $require_version )
		{
			if ( mb_strpos( $require_ext, 'ext-' ) === false )
			{
				continue;
			}

			$php_ext = str_replace( 'ext-', '', $require_ext );

			if ( ! extension_loaded( $php_ext ) )
			{
				$error[] = sprintf(
					_( 'The %s add-on requires the %s PHP extension. Please activate it.' ),
					dgettext( $this->addon_dir, str_replace( '_', ' ', $this->addon_dir ) ),
					'<code>' . $php_ext . '</code>'
				);

				$return = false;
			}
		}

		return $return;
	}
}
