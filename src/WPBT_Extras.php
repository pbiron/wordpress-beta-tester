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
			array(
				'id' => 'checked',
				'title' => 'Dude, where\'s my car?',
			)
		);
	}

	public function validate_setting($setting){
		return $setting;
	}

	public function print_extra_settings_top() {
		echo 'This area is for extra special beta testing.';
	}

		/**
	 * Get the settings option array and print one of its values.
	 *
	 * @param $args
	 */
	public function checkbox_setting($args) {
		$options = get_site_option( 'wp_beta_tester_extras' );
		//$checked = isset( static::$options[ $args['id'] ] ) ? static::$options[ $args['id'] ] : null;
		$checked = $options;
		?>
		<label for="<?php esc_attr_e( $args['id'] ); ?>">
			<input type="checkbox" name="wp-beta-tester[<?php esc_attr_e( $args['id'] ); ?>]" value="1" <?php checked( '1', $checked ); ?> >
			<?php echo $args['title']; ?>
		</label>
		<?php
	}

	public function add_admin_page($tab, $action){
		?>
		<div>
			<?php if ( 'wp_beta_tester_extras' === $tab ) : ?>
			<form method="post" action="<?php esc_attr_e( $action ); ?>">
				<?php settings_fields( 'wp_beta_tester_extras' ); ?>
				<?php do_settings_sections( 'wp_beta_tester_extras' ); ?>
				<p class="submit"><input type="submit" class="button-primary"
					value="<?php esc_html_e( 'Save Changes', 'wordpress-beta-tester' ); ?>" />
				</p>
			</form>
			<?php endif; ?>
		</div>
		<?php
	}
}
