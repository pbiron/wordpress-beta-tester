<?php

class WP_Beta_Tester {

	public function __construct() {
		$options = get_site_option( 'wp_beta_tester', array( 'stream' => 'point' ) );
		$this->load_hooks();
		$settings = new WPBT_Settings( $this, $options );
		$settings->load_hooks();
	}

	protected function load_hooks() {
		add_action(
			'update_option_wp_beta_tester_stream',
			array(
				$this,
				'action_update_option_wp_beta_tester_stream',
			)
		);
		add_filter( 'pre_http_request', array( $this, 'filter_http_request' ), 10, 3 );
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
		if ( $this->check_if_settings_downgrade() ) {
			?>
			<div id="message" class="error">
				<p>
				<?php
				$admin_page = is_multisite() ? 'settings.php' : 'tools.php';
				/* translators: %s: link to setting page */
				printf( wp_kses_post( __( '<strong>Error:</strong> Your current <a href="%s">WordPress Beta Tester plugin configuration</a> will downgrade your install to a previous version - please reconfigure it.', 'wordpress-beta-tester' ) ), admin_url( $admin_page . '?page=wp_beta_tester' ) );
				?>
				</p>
			</div>
			<?php
		}
	}

	public function filter_http_request( $result, $args, $url ) {
		if ( $result || isset( $args['_beta_tester'] ) ) {
			return $result;
		}
		if ( false === strpos( $url, '//api.wordpress.org/core/version-check/' ) ) {
			return $result;
		}

		// It's a core-update request.
		$args['_beta_tester'] = true;

		global $wp_version;
		$url = str_replace( 'version=' . $wp_version, 'version=' . $this->mangle_wp_version(), $url );

		return wp_remote_get( $url, $args );
	}

	public function action_update_option_wp_beta_tester_stream() {
		// Our option has changed so update the cached information pronto.
		do_action( 'wp_version_check' );
	}

	public function get_preferred_from_update_core() {
		if ( ! function_exists( 'get_preferred_from_update_core' ) ) {
			require_once ABSPATH . 'wp-admin/includes/update.php';
		}

		// Validate that we have api data and if not get the normal data so we always have it.
		$preferred = get_preferred_from_update_core();
		if ( false === $preferred ) {
			wp_version_check();
			$preferred = get_preferred_from_update_core();
		}

		return $preferred;
	}

	protected function mangle_wp_version() {
		$options    = get_site_option( 'wp_beta_tester', array( 'stream' => 'point' ) );
		$preferred  = $this->get_preferred_from_update_core();
		$wp_version = get_bloginfo( 'version' );

		// If we're getting no updates back from get_preferred_from_update_core(),
		// let an HTTP request go through unmangled.
		if ( ! isset( $preferred->current ) ) {
			return $wp_version;
		}

		$versions = array_map( 'intval', explode( '.', $preferred->current ) );

		switch ( $options['stream'] ) {
			case 'point':
				$versions[2] = isset( $versions[2] ) ? $versions[2] + 1 : 1;
				$wp_version  = $versions[0] . '.' . $versions[1] . '.' . $versions[2] . '-wp-beta-tester';
				break;
			case 'unstable':
				++ $versions[1];
				if ( 10 === $versions[1] ) {
					++ $versions[0];
					$versions[1] = 0;
				}
				$wp_version = $versions[0] . '.' . $versions[1] . '-wp-beta-tester';
				break;
		}

		return $wp_version;
	}

	public function check_if_settings_downgrade() {
		global $wp_version;
		$wp_real_version    = explode( '-', $wp_version );
		$wp_mangled_version = explode( '-', $this->mangle_wp_version() );

		return version_compare( $wp_mangled_version[0], $wp_real_version[0], 'lt' );
	}

	/**
	 * Check current page.
	 *
	 * @param array $pages
	 * @return bool
	 */
	public function is_current_page( array $pages ) {
		global $pagenow;
		return in_array( $pagenow, $pages );
	}

}
