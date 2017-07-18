<?php
/**
 * Requirements checks for WordPress plugin
 * @autor   Kuba Mikita (jakub@underdev.it)
 * @version 1.0
 * @usage   see https://github.com/Kubitomakita/Requirements
 *
 * Supported tests:
 * - php - version
 * - php_extensions - if loaded
 * - wp - version
 * - plugins - active and versions check
 * - theme - active
 */

if ( ! class_exists( 'underDEV_Requirements' ) ) :

class underDEV_Requirements {

	/**
	 * Plugin display name
	 * @var string
	 */
	private $plugin_name;

	/**
	 * Array of checks
	 * @var array
	 */
	private $checks;

	/**
	 * Array of errors
	 * @var array
	 */
	private $errors = array();

	/**
	 * Class constructor
	 * @param string $plugin_name plugin display name
	 * @param array  $checks      checks to perform
	 */
	public function __construct( $plugin_name = '', $checks = array() ) {

		$this->checks      = $checks;
		$this->plugin_name = $plugin_name;

		$this->run_checks();

	}

	/**
	 * Runs checks
	 * @return void
	 */
	public function run_checks() {

		foreach ( $this->checks as $thing_to_check => $comparsion ) {

			$method_name = 'check_' . $thing_to_check;

			if ( method_exists( $this, $method_name ) ) {
				call_user_func( array( $this, $method_name ), $comparsion );
			}

		}

	}

	/**
	 * Check PHP version
	 * @param  string $version version needed
	 * @return void
	 */
	public function check_php( $version ) {

		if ( version_compare( phpversion(), $version, '<' ) ) {
			$this->errors[] = sprintf( 'PHP at least in version %s. Your version is %s', $version, phpversion() );
		}

	}

	/**
	 * Check PHP extensions
	 * @param  string $extensions array of extension names
	 * @return void
	 */
	public function check_php_extensions( $extensions ) {

		$missing_extensions = array();

		foreach ( $extensions as $extension ) {
			if ( ! extension_loaded( $extension ) ) {
				$missing_extensions[] = $extension;
			}
		}

		if ( ! empty( $missing_extensions ) ) {
			$this->errors[] = sprintf(
				_n( 'PHP extension: %s', 'PHP extensions: %s', count( $missing_extensions ) ),
				implode( ', ', $missing_extensions )
			);
		}

	}

	/**
	 * Check WordPress version
	 * @param  string $version version needed
	 * @return void
	 */
	public function check_wp( $version ) {

		if ( version_compare( get_bloginfo( 'version' ), $version, '<' ) ) {
			$this->errors[] = sprintf( 'WordPress at least in version %s. Your version is %s', $version, get_bloginfo( 'version' ) );
		}

	}

	/**
	 * Check if plugins are active and are in needed versions
	 * @param  array $plugins array with plugins,
	 *                        where key is the plugin file and value is the version
	 * @return void
	 */
	public function check_plugins( $plugins ) {

		$active_plugins_raw      = wp_get_active_and_valid_plugins();
		$active_plugins          = array();
		$active_plugins_versions = array();

		foreach ( $active_plugins_raw as $plugin_full_path ) {
			$plugin_file                             = str_replace( WP_PLUGIN_DIR . '/', '', $plugin_full_path );
			$active_plugins[]                        = $plugin_file;
			$active_plugins_versions[ $plugin_file ] = @get_file_data( $plugin_full_path, array( 'Version' ) )[0];
		}

		foreach ( $plugins as $plugin_file => $plugin_data ) {

			if ( ! in_array( $plugin_file, $active_plugins ) ) {
				$this->errors[] = sprintf( '%s plugin active', $plugin_data['name'] );
			} else if ( version_compare( $active_plugins_versions[ $plugin_file ], $plugin_data['version'], '<' ) ) {
				$this->errors[] = sprintf( '%s plugin at least in version %s', $plugin_data['name'], $plugin_data['version'] );
			}

		}

	}

	/**
	 * Check if theme is active
	 * @param  array $needed_theme theme data
	 * @return void
	 */
	public function check_theme( $needed_theme ) {

		$theme = wp_get_theme();

		if ( $theme->get_template() != $needed_theme['slug'] ) {
			$this->errors[] = sprintf( '%s theme active', $needed_theme['name'] );
		}

	}

	/**
	 * Check function collision
	 * @param  array $functions function names
	 * @return void
	 */
	public function check_function_collision( $functions ) {

		$collisions = array();

		foreach ( $functions as $function ) {
			if ( function_exists( $function ) ) {
				$collisions[] = $function;
			}
		}

		if ( ! empty( $collisions ) ) {
			$this->errors[] = sprintf(
				_n( 'register %s function but it\'s already taken', 'register %s functions but these are already taken', count( $collisions ) ),
				implode( ', ', $collisions )
			);
		}

	}

	/**
	 * Check class collision
	 * @param  array $classes class names
	 * @return void
	 */
	public function check_class_collision( $classes ) {

		$collisions = array();

		foreach ( $classes as $class ) {
			if ( class_exists( $class ) ) {
				$collisions[] = $class;
			}
		}

		if ( ! empty( $collisions ) ) {
			$this->errors[] = sprintf(
				_n( 'register %s class but it\'s already defined', 'register %s classes but these are already defined', count( $collisions ) ),
				implode( ', ', $collisions )
			);
		}

	}

	/**
	 * Check if requirements has been satisfied
	 * @return boolean
	 */
	public function satisfied() {
		return empty( $this->errors );
	}

	/**
	 * Displays notice for user about the plugin requirements
	 * @return void
	 */
	public function notice() {

		echo '<div class="error">';

			echo '<p><strong>Plugin ' . $this->plugin_name . ' cannot be loaded</strong> because it needs:</p>';

			echo '<ul style="list-style: disc; padding-left: 20px;">';

				foreach ( $this->errors as $error ) {
					echo '<li>' . $error . '</li>';
				}

			echo '</ul>';

		echo '</div>';

	}

}

endif;
