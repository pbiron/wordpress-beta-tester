<?php

class WPBT_Settings {

	protected $wp_beta_tester;

	protected static $options;

	public function __construct( WP_Beta_Tester $wp_beta_tester, $options ) {
		self::$options        = $options;
		$this->wp_beta_tester = $wp_beta_tester;
		new WPBT_Core( $wp_beta_tester, $options );
		new WPBT_Extras( $wp_beta_tester, $options );
	}

	public function load_hooks() {
		add_action( 'admin_init', array( $this, 'add_settings' ) );
		add_action( is_multisite() ? 'network_admin_menu' : 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'network_admin_edit_wp_beta_tester', array( $this, 'update_settings' ) );
		add_action( 'admin_init', array( $this, 'update_settings' ) );

		add_action( 'admin_head-plugins.php', array( $this, 'action_admin_head_plugins_php' ) );
		add_action( 'admin_head-update-core.php', array( $this, 'action_admin_head_plugins_php' ) );
	}

	public function add_plugin_page() {
		$parent     = is_multisite() ? 'settings.php' : 'tools.php';
		$capability = is_multisite() ? 'manage_network' : 'manage_options';

		add_submenu_page(
			$parent,
			esc_html__( 'Beta Testing WordPress', 'wordpress-beta-tester' ),
			esc_html__( 'Beta Testing', 'wordpress-beta-tester' ),
			$capability,
			'wp_beta_tester',
			array( $this, 'create_settings_page' )
		);
	}

	/**
	 * Calls individuals settings class save methods.
	 *
	 * @return void
	 */
	public function update_settings() {
		/**
		 * Save $options in add-on classes.
		 *
		 * @since 2.0.0
		 */
		do_action( 'wp_beta_tester_update_settings', $_POST );

		$this->redirect_on_save();
	}

	/**
	 * Redirect to correct Settings/Tools tab on Save.
	 *
	 * @param string $option_page
	 */
	protected function redirect_on_save() {
		/**
		 * Filter to add to $option_page array.
		 *
		 * @since 2.0.0
		 * @return array
		 */
		$option_page = apply_filters( 'wp_beta_tester_save_redirect', array( 'wp_beta_tester' ) );
		$update      = false;

		if ( ( isset( $_POST['action'] ) && 'update' === $_POST['action'] ) &&
			( isset( $_POST['option_page'] ) && in_array( $_POST['option_page'], $option_page, true ) )
		) {
			$update = true;
		}

		$redirect_url = is_multisite() ? network_admin_url( 'settings.php' ) : admin_url( 'tools.php' );

		if ( $update ) {
			$query = isset( $_POST['_wp_http_referer'] ) ? parse_url( $_POST['_wp_http_referer'], PHP_URL_QUERY ) : null;
			parse_str( $query, $arr );
			$arr['tab'] = ! empty( $arr['tab'] ) ? $arr['tab'] : 'wp_beta_tester_core';

			$location = add_query_arg(
				array(
					'page'    => 'wp_beta_tester',
					'tab'     => $arr['tab'],
					'updated' => $update,
				),
				$redirect_url
			);
			wp_redirect( $location );
			exit;
		}
	}

	public function action_admin_head_plugins_php() {
		// Workaround the check throttling in wp_version_check()
		$st = get_site_transient( 'update_core' );
		if ( is_object( $st ) ) {
			$st->last_checked = 0;
			set_site_transient( 'update_core', $st );
		}
		wp_version_check();
		// Can output an error here if current config drives version backwards
		if ( $this->wp_beta_tester->check_if_settings_downgrade() ) {
			echo '<div id="message" class="error"><p>';
			$admin_page = is_multisite() ? 'settings.php' : 'tools.php';
			/* translators: %s: link to setting page */
			printf( wp_kses_post( __( '<strong>Error:</strong> Your current <a href="%s">WordPress Beta Tester plugin configuration</a> will downgrade your install to a previous version - please reconfigure it.', 'wordpress-beta-tester' ) ), admin_url( $admin_page . '?page=wp_beta_tester&tab=wp_beta_tester_core' ) );
			echo '</p></div>';
		}
	}

	/**
	 * Define tabs for Settings page.
	 *
	 * @return array
	 */
	private function settings_tabs() {
		/**
		 * Filter settings tabs.
		 *
		 * @since 2.0.0
		 *
		 * @param array $tabs Array of default tabs.
		 */
		return apply_filters( 'wp_beta_tester_add_settings_tabs', array() );
	}

	/**
	 * Renders setting tabs.
	 *
	 * Walks through the object's tabs array and prints them one by one.
	 * Provides the heading for the settings page.
	 *
	 * @access private
	 */
	private function options_tabs() {
		$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'wp_beta_tester_core_settings';
		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $this->settings_tabs() as $key => $name ) {
			$active = ( $current_tab === $key ) ? 'nav-tab-active' : '';
			echo '<a class="nav-tab ' . $active . '" href="?page=wp_beta_tester&tab=' . $key . '">' . $name . '</a>';
		}
		echo '</h2>';
	}

	/**
	 * Create 'Saved' notice for saved settings.
	 *
	 * @return void
	 */
	private function saved_settings_notice() {
		if ( ( isset( $_GET['updated'] ) && true == $_GET['updated'] ) ||
			( isset( $_GET['settings-updated'] ) && true == $_GET['settings-updated'] )
		) {
			echo '<div class="updated">';
			echo '<p>' . esc_html__( 'Saved.', 'wordpress-beta-tester' ) . '</p>';
			echo '</div>';
		}
	}

	// TODO: update to anonymous function for PHP 5.3
	public function add_settings() {
		do_action( 'wp_beta_tester_add_settings' );
	}

	public function create_settings_page() {
		$this->saved_settings_notice();
		$action = is_multisite() ? 'edit.php?action=wp_beta_tester' : 'options.php';
		$tab    = isset( $_GET['tab'] ) ? $_GET['tab'] : 'wp_beta_tester_core';
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Beta Testing WordPress', 'wordpress-beta-tester' ); ?></h1>
			<?php $this->options_tabs(); ?>
			<div class="updated fade">
				<p><?php echo( wp_kses_post( __( '<strong>Please note:</strong> Once you have switched your website to one of these beta versions of software, it will not always be possible to downgrade as the database structure may be updated during the development of a major release.', 'wordpress-beta-tester' ) ) ); ?></p>
			</div>
		<?php

		/**
		 * Action hook to add admin page data to appropriate $tab.
		 *
		 * @since 2.0.0
		 *
		 * @param string $tab    Name of tab.
		 * @param string $action Save action for appropriate WordPress installation.
		 *                       Single site or Multisite.
		 */
		do_action( 'wp_beta_tester_add_admin_page', $tab, $action );
		echo '</div>';
	}

	/**
	 * Sanitize each setting field as needed.
	 *
	 * @param array $input Contains all settings fields as array keys
	 *
	 * @return array
	 */
	public static function sanitize( $input ) {
		$new_input = array();
		if ( ! is_array( $input ) ) {
			$new_input = sanitize_text_field( $input );
		} else {
			foreach ( array_keys( (array) $input ) as $id ) {
				$new_input[ sanitize_text_field( $id ) ] = sanitize_text_field( $input[ $id ] );
			}
		}

		return $new_input;
	}

	/**
	 * Get the settings option array and print one of its values.
	 *
	 * @param array $args 'id' and 'title'
	 */
	public static function checkbox_setting( $args ) {
		$checked = isset( self::$options[ $args['id'] ] ) ? self::$options[ $args['id'] ] : null;
		?>
		<style> .form-table th { display:none; } </style>
		<label for="<?php esc_attr_e( $args['id'] ); ?>">
			<input type="checkbox" name="wp-beta-tester[<?php esc_attr_e( $args['id'] ); ?>]" value="1" <?php checked( '1', $checked ); ?> >
			<?php echo $args['title']; ?>
		</label>
		<?php
	}

}
