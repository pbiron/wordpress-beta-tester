<?php

class WBT_WSOD {

	public function __construct() {
		add_filter( 'wp_beta_tester_add_settings_tabs', array( $this, 'add_settings_tab' ) );
	}

	public function add_settings_tab( $tabs ) {
		$tab = array( 'wp_beta_tester_wsod' => esc_html__( 'WSOD Settings', 'wordpress-beta-tester' ) );

		return array_merge( $tabs, $tab );
	}
}
