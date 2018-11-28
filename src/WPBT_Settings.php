<?php

class WPBT_Settings {

	protected $wp_beta_tester;

	public function __construct( WP_Beta_Tester $wp_beta_tester ) {
		$this->wp_beta_tester = $wp_beta_tester;
		new WPBT_Core( $wp_beta_tester );
		new WPBT_Extras( $wp_beta_tester );
	}

	public function load_hooks() {
		add_action( 'admin_init', array( $this, 'add_settings' ) );
		add_action( is_multisite() ? 'network_admin_menu' : 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'network_admin_edit_wp_beta_tester', array( $this, 'update_settings' ) );
		//add_action( 'admin_init', [ $this, 'update_settings' ] );

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


	public function update_settings() {
		if ( isset( $_POST['option_page'] ) ) {
			if ( 'wp_beta_tester_options' === $_POST['option_page'] ) {
				update_site_option( 'wp_beta_tester_stream', $this->validate_setting( $_POST['wp_beta_tester_stream'] ) );
			}

			$redirect_url = is_multisite() ? network_admin_url( 'settings.php' ) : admin_url( 'options-general.php' );
			$location     = add_query_arg(
				array(
					'page'    => 'wp_beta_tester',
					'updated' => 'true',
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
			printf( wp_kses_post( __( '<strong>Error:</strong> Your current <a href="%s">WordPress Beta Tester plugin configuration</a> will downgrade your install to a previous version - please reconfigure it.', 'wordpress-beta-tester' ) ), admin_url( $admin_page . '?page=wp_beta_tester' ) );
			echo '</p></div>';
		}
	}

	/**
	 * Define tabs for Settings page.
	 * By defining in a method, strings can be translated.
	 *
	 * @access private
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
		$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'wp_beta_tester_settings';
		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $this->settings_tabs() as $key => $name ) {
			$active = ( $current_tab === $key ) ? 'nav-tab-active' : '';
			echo '<a class="nav-tab ' . $active . '" href="?page=wp_beta_tester&tab=' . $key . '">' . $name . '</a>';
		}
		echo '</h2>';
	}

	private function saved_settings_notice() {
		if ( ( isset( $_GET['updated'] ) && true == $_GET['updated'] ) ||
			( isset( $_GET['settings-updated'] ) && true == $_GET['settings-updated'] )
		) {
			echo '<div class="updated">';
			echo '<p>' . esc_html__( 'Saved.', 'wordpress-beta-tester' ) . '</p>';
			echo '</div>';
		}
	}

	public function add_settings() {
		do_action( 'wp_beta_tester_add_settings' );
	}

	public function create_settings_page() {
		$this->saved_settings_notice();
		$action = is_multisite() ? 'edit.php?action=wp_beta_tester' : 'options.php';
		$tab    = isset( $_GET['tab'] ) ? $_GET['tab'] : 'wp_beta_tester_settings';
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
		 * @since 8.0.0
		 *
		 * @param string $tab    Name of tab.
		 * @param string $action Save action for appropriate WordPress installation.
		 *                       Single site or Multisite.
		 */
		do_action( 'wp_beta_tester_add_admin_page', $tab, $action );
		echo '</div>';
	}
}
