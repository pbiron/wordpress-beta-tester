<?php

class WBT_Settings {

	protected $wp_beta_tester;

	public function __construct( WP_Beta_Tester $wp_beta_tester ) {
		$this->wp_beta_tester = $wp_beta_tester;
		new WBT_WSOD();
		//$this->load_hooks();
	}

	public function load_hooks() {
		add_action( 'admin_init', array( $this, 'page_init' ) );
		add_action( is_multisite() ? 'network_admin_menu' : 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'network_admin_edit_wp_beta_tester', array( $this, 'update_settings' ) );
		//add_action( 'admin_init', [ $this, 'update_settings' ] );

		add_action( 'admin_head-plugins.php', array( $this, 'action_admin_head_plugins_php' ) );
		add_action( 'admin_head-update-core.php', array( $this, 'action_admin_head_plugins_php' ) );
	}

	public function page_init() {
		register_setting(
			'wp_beta_tester_options',
			'wp_beta_tester_stream',
			array( $this, 'validate_setting' )
		);
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

	protected function validate_setting( $setting ) {
		return ( in_array( $setting, array( 'point', 'unstable' ), true ) ? $setting : 'point' );
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
		$tabs = array( 'wp_beta_tester_settings' => esc_html__( 'Beta Tester Settings', 'wordpress-beta-tester' ) );

		/**
		 * Filter settings tabs.
		 *
		 * @since 8.0.0
		 *
		 * @param array $tabs Array of default tabs.
		 */
		return apply_filters( 'wp_beta_tester_add_settings_tabs', $tabs );
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

	private function saved_settings() {
		if ( ( isset( $_GET['updated'] ) && true == $_GET['updated'] ) ||
			( isset( $_GET['settings-updated'] ) && true == $_GET['settings-updated'] )
		) {
			echo '<div class="updated">';
			echo '<p>' . esc_html__( 'Saved.', 'wordpress-beta-tester' ) . '</p>';
			echo '</div>';
		}
	}

	private function print_settings_top() {
		$preferred = $this->wp_beta_tester->get_preferred_from_update_core();
		if ( 'development' !== $preferred->response ) {
			echo '<div class="updated fade">';
			echo '<p>' . wp_kses_post( __( '<strong>Please note:</strong> There are no development builds of the beta stream you have chosen available, so you will receive normal update notifications.', 'wordpress-beta-tester' ) ) . '</p>';
			echo '</div>';
		}
		$this->action_admin_head_plugins_php(); // Check configuration

		echo '<div><p>';
		printf(
			/* translators: 1: link to backing up database, 2: link to make.wp.org/core, 3: link to beta support forum */
			wp_kses_post( __( 'By their nature, these releases are unstable and should not be used anyplace where your data is important. So please <a href="%1$s">back up your database</a> before upgrading to a test release. In order to hear about the latest beta releases, your best bet is to watch the <a href="%2$s">development blog</a> and the <a href="%3$s">beta forum</a>.', 'wordpress-beta-tester' ) ),
			_x( 'https://codex.wordpress.org/Backing_Up_Your_Database', 'URL to database backup instructions', 'wordpress-beta-tester' ),
			'https://make.wordpress.org/core/',
			_x( 'https://wordpress.org/support/forum/alphabeta', 'URL to beta support forum', 'wordpress-beta-tester' )
		);
		echo '</p><p>';
		printf(
			/* translators: %s: link to new trac ticket */
			wp_kses_post( __( 'Thank you for helping test WordPress. Please <a href="%s">report any bugs you find</a>.', 'wordpress-beta-tester' ) ),
			'https://core.trac.wordpress.org/newticket'
		);
		echo '</p><p>';
		echo( wp_kses_post( __( 'By default, your WordPress install uses the stable update stream. To return to this, please deactivate this plugin and re-install from the <a href="update-core.php">WordPress Updates</a> page.', 'wordpress-beta-tester' ) ) );
		echo '</p><p>';
		echo( wp_kses_post( __( 'Why don&#8217;t you <a href="update-core.php">head on over and upgrade now</a>.', 'wordpress-beta-tester' ) ) );
		echo '</p></div>';
	}

	public function create_settings_page() {
		$this->saved_settings();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Beta Testing WordPress', 'wordpress-beta-tester' ); ?></h1>
				<?php $this->options_tabs(); ?>
			<div class="updated fade">
				<p><?php echo( wp_kses_post( __( '<strong>Please note:</strong> Once you have switched your website to one of these beta versions of software, it will not always be possible to downgrade as the database structure may be updated during the development of a major release.', 'wordpress-beta-tester' ) ) ); ?></p>
			</div>
				<?php $action = is_multisite() ? 'edit.php?action=wp_beta_tester' : 'options.php'; ?>
				<?php $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'wp_beta_tester_settings'; ?>
				<?php if ( 'wp_beta_tester_settings' === $tab ) : ?>
					<?php $this->print_settings_top(); ?>
				<form method="post" action="<?php esc_attr_e( $action ); ?>">
					<?php settings_fields( 'wp_beta_tester_options' ); ?>
					<fieldset>
						<legend><?php esc_html_e( 'Please select the update stream you would like this website to use:', 'wordpress-beta-tester' ); ?></legend>
						<?php $stream = get_site_option( 'wp_beta_tester_stream', 'point' ); ?>
						<table class="form-table">
							<tr>
								<th><label><input name="wp_beta_tester_stream"
									id="update-stream-point-nightlies" type="radio" value="point"
									class="tog" <?php checked( 'point', $stream ); ?> />
									<?php esc_html_e( 'Point release nightlies', 'wordpress-beta-tester' ); ?>
									</label></th>
								<td><?php esc_html_e( 'This contains the work that is occurring on a branch in preparation for a x.x.x point release.  This should also be fairly stable but will be available before the branch is ready for release.', 'wordpress-beta-tester' ); ?></td>
							</tr>
							<tr>
								<th><label><input name="wp_beta_tester_stream"
									id="update-stream-bleeding-nightlies" type="radio" value="unstable"
									class="tog" <?php checked( 'unstable', $stream ); ?> />
									<?php esc_html_e( 'Bleeding edge nightlies', 'wordpress-beta-tester' ); ?>
									</label></th>
								<td><?php echo( wp_kses_post( __( 'This is the bleeding edge development code from `trunk` which may be unstable at times. <em>Only use this if you really know what you are doing</em>.', 'wordpress-beta-tester' ) ) ); ?></td>
							</tr>
						</table>
					</fieldset>
					<p class="submit"><input type="submit" class="button-primary"
						value="<?php esc_html_e( 'Save Changes', 'wordpress-beta-tester' ); ?>" />
					</p>
				</form>
				<?php endif; ?>

			</div>
		</div>
		<?php
	}
}
