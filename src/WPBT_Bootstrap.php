<?php

class WPBT_Bootstrap {

	public function run() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		$this->load_requires();
		new WP_Beta_Tester();
	}

	public function load_textdomain() {
		load_plugin_textdomain( 'wordpress-beta-tester' );
	}

	protected function load_requires() {
		require_once WP_BETA_TESTER_DIR . '/src/WP_Beta_Tester.php';
		require_once WP_BETA_TESTER_DIR . '/src/WPBT_Settings.php';
		require_once WP_BETA_TESTER_DIR . '/src/WPBT_Core.php';
		require_once WP_BETA_TESTER_DIR . '/src/WPBT_Extras.php';
	}
}
