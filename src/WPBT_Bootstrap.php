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
		$wpbt = new WP_Beta_Tester();
		$wpbt->run();
		// TODO: I really want to do this, but have to wait for PHP 5.4
		//( new WP_Beta_Tester() )->run();
	}

	/**
	 * Load textdomain.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'wordpress-beta-tester' );
	}
}
