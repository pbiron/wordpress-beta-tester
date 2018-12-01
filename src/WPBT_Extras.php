<?php

class WPBT_Extras {

	protected static $options;

	public function __construct( WP_Beta_Tester $wp_beta_tester, $options ) {
		self::$options        = $options;
		$this->wp_beta_tester = $wp_beta_tester;
		$this->load_hooks();
	}

	public function load_hooks() {
		add_filter( 'wp_beta_tester_add_settings_tabs', array( $this, 'add_settings_tab' ) );
		add_action( 'wp_beta_tester_add_settings', array( $this, 'add_settings' ) );
		add_action( 'wp_beta_tester_add_admin_page', array( $this, 'add_admin_page' ), 10, 2 );
		add_action( 'wp_beta_tester_update_settings', array( $this, 'save_settings' ) );
	}

	public function add_settings_tab( $tabs ) {
		return array_merge( $tabs, array( 'wp_beta_tester_extras' => esc_html__( 'Extra Settings', 'wordpress-beta-tester' ) ) );
	}

	public function add_settings() {
		register_setting(
			'wp_beta_tester',
			'wp_beta_tester_extras',
			array( 'WPBT_Settings', 'sanitize' )
		);

		add_settings_section(
			'wp_beta_tester_extras',
			esc_html__( 'Extra Settings', 'wordpress-beta-tester' ),
			array( $this, 'print_extra_settings_top' ),
			'wp_beta_tester_extras'
		);

		// Example with WSOD.
		add_settings_field(
			'extras_settings',
			null,
			array( 'WPBT_Settings', 'checkbox_setting' ),
			'wp_beta_tester_extras',
			'wp_beta_tester_extras',
			array(
				'id'    => 'wsod',
				'title' => 'Help test Servehappy\'s WSOD, Trac #44458',
			)
		);

	}

	public function save_settings( $post_data ) {
		if ( isset( $post_data['option_page'] ) &&
			'wp_beta_tester_extras' === $post_data['option_page']
		) {
			$filtered_options = array_filter(
				self::$options,
				array( $this, 'filter_save_settings' )
				// TODO: uncomment for PHP 5.3
				//function ( $e ) {
				//		return '1' !== $e;
				//}
			);

			$options = isset( $post_data['wp-beta-tester'] )
				? $post_data['wp-beta-tester']
				: array();
			$options = WPBT_Settings::sanitize( $options );
			$options = array_merge( $filtered_options, $options );
			update_site_option( 'wp_beta_tester', (array) $options );
			add_filter( 'wp_beta_tester_save_redirect', array( $this, 'save_redirect_page' ) );
		}
	}

	// TODO: update to anonymous function for PHP 5.3
	private function filter_save_settings( $e ) {
		return '1' !== $e;
	}

	// TODO: update to anonymous function for PHP 5.3
	public function save_redirect_page( $option_page ) {
		return array_merge( $option_page, array( 'wp_beta_tester_extras' ) );
	}

	public function print_extra_settings_top() {
		echo 'This area is for extra special beta testing.';
	}

	public function add_admin_page( $tab, $action ) {
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
