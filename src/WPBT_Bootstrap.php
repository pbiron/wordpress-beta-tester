<?php
/**
 * WordPress Beta Tester
 *
 * @package WordPress_Beta_Tester
 * @author Andy Fragen, original author Peter Westwood.
 * @license GPLv2+
 * @copyright 2009-2016 Peter Westwood (email : peter.westwood@ftwr.co.uk)
 */

class WPBT_Bootstrap {

	/**
	 * Let's get started.
	 *
	 * @return void
	 */
	public function run() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		$this->load_requires();
		$wpbt = new WP_Beta_Tester();
		$wpbt->run();
		// TODO: I really want to do this, but have to wait for PHP 5.4
		//( new WP_Beta_Tester() )->run();
		register_activation_hook( $this->file, array( $this, 'activate' ) );
		register_deactivation_hook( $this->file, array( $this, 'deactivate' ) );
	}

	/**
	 * Load textdomain.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'wordpress-beta-tester' );
	}

	/**
	 * Run on plugin activation.
	 *
	 * @return void
	 */
	public function activate() {
		delete_site_transient( 'update_core' );
		$wpbt_extras = $this->load_wpbt_extras();
		$wpbt_extras->activate();
	}

	/**
	 * Run on plugin deactivation.
	 *
	 * @return void
	 */
	public function deactivate() {
		delete_site_transient( 'update_core' );
		$wpbt_extras = $this->load_wpbt_extras();
		$wpbt_extras->deactivate();
	}

	/**
	 * Load class WPBT_Extras.
	 *
	 * @return void
	 */
	private function load_wpbt_extras() {
		$options = get_site_option( 'wp_beta_tester', array( 'stream' => 'point' ) );
		$wpbt    = new WP_Beta_Tester( __FILE__ );
		return new WPBT_Extras( $wpbt, $options );
	}

	/**
	 * <sarcasm>Poor man's autoloader.</sarcasm>
	 *
	 * @return void
	 */
	protected function load_requires() {
		require_once WP_BETA_TESTER_DIR . '/src/WP_Beta_Tester.php';
		require_once WP_BETA_TESTER_DIR . '/src/WPBT_Settings.php';
		require_once WP_BETA_TESTER_DIR . '/src/WPBT_Core.php';
		require_once WP_BETA_TESTER_DIR . '/src/WPBT_Extras.php';
		require_once WP_BETA_TESTER_DIR . '/vendor/WPConfigTransformer.php';
	}
}
