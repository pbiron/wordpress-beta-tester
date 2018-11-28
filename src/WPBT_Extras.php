<?php

class WPBT_Extras {

	protected $wp_beta_tester;

	public function __construct( $wp_beta_tester ) {
		$this->wp_beta_tester = $wp_beta_tester;
		add_filter( 'wp_beta_tester_add_settings_tabs', array( $this, 'add_settings_tab' ) );
		add_action( 'wp_beta_tester_add_settings', array( $this, 'add_settings' ) );
		add_action( 'wp_beta_tester_add_admin_page', array( $this, 'add_admin_page' ), 10, 2 );
	}

	public function add_settings_tab( $tabs ) {
		return array_merge( $tabs, array( 'wp_beta_tester_extras' => esc_html__( 'Extra Settings', 'wordpress-beta-tester' ) ) );
	}

	public function add_settings() {
		register_setting(
			'wp_beta_tester_extras',
			'wp_beta_tester_extras',
			array( $this, 'validate_setting' )
		);

		add_settings_section(
			'wp_beta_tester_extras',
			esc_html__( 'Extra Settings', 'wordpress-beta-tester' ),
			array( $this, 'print_extra_settings_top' ),
			'wp_beta_tester_extras'
		);

		add_settings_field(
			'core_test_settings',
			null,
			array( $this, 'checkbox_setting' ),
			'wp_beta_tester_extras',
			'wp_beta_tester_extras',
			array( esc_html__( 'Dude, fix this', 'wordpress-beta-tester' ) )
		);
	}

	public function print_extra_settings_top() {
		echo 'Now is the time for all good men...';
	}

	public function checkbox_setting() {
		echo 'Checkbox here';
	}

	public function add_admin_page($tab, $action){
		echo 'Extras Admin page goes here.';
	}
}
