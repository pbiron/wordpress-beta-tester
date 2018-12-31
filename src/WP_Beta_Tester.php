<?php
/**
 * WordPress Beta Tester
 *
 * @package WordPress_Beta_Tester
 * @author Andy Fragen, original author Peter Westwood.
 * @license GPLv2+
 * @copyright 2009-2016 Peter Westwood (email : peter.westwood@ftwr.co.uk)
 */

class WP_Beta_Tester {

	/**
	 * Holds main plugin file.
	 *
	 * @var $file
	 */
	public $file;

	/**
	 * Constructor.
	 *
	 * @param string $file Main plugin file.
	 * @return void
	 */
	public function __construct( $file ) {
		$this->file = $file;
	}

	/**
	 * Rev up the engines.
	 *
	 * @return void
	 */
	public function run() {
		$options = get_site_option( 'wp_beta_tester', array( 'stream' => 'point' ) );
		$this->load_hooks();
		$settings = new WPBT_Settings( $this, $options );
		$settings->run();
		// TODO: I really want to do this, but have to wait for PHP 5.4
		//( new WPBT_Settings( $this, $options ) )->run();
	}

	/**
	 * Load hooks.
	 *
	 * @return void
	 */
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

	/**
	 * Check and display notice if 'update' really downgrade.
	 *
	 * @return void
	 */
	public function action_admin_head_plugins_php() {
		// Workaround the check throttling in wp_version_check()
		$st = get_site_transient( 'update_core' );
		if ( is_object( $st ) ) {
			$st->last_checked = 0;
			set_site_transient( 'update_core', $st );
		}
		// Can output an error here if current config drives version backwards
		if ( $this->check_if_settings_downgrade() ) {
			echo '<div id="message" class="error"><p>';
			$admin_page = is_multisite() ? 'settings.php' : 'tools.php';
			/* translators: %s: link to setting page */
			printf( wp_kses_post( __( '<strong>Error:</strong> Your current <a href="%s">WordPress Beta Tester plugin configuration</a> will downgrade your install to a previous version - please reconfigure it.', 'wordpress-beta-tester' ) ), admin_url( $admin_page . '?page=wp_beta_tester&tab=wp_beta_tester_core' ) );
			echo '</p></div>';
		}
	}

	/**
	 * Filter 'pre_http_request' to add beta-tester API check.
	 *
	 * @param mixed $result
	 * @param mixed $args
	 * @param mixed $url
	 * @return void
	 */
	public function filter_http_request( $result, $args, $url ) {
		if ( $result || isset( $args['_beta_tester'] ) ) {
			return $result;
		}
		if ( false === strpos( $url, '//api.wordpress.org/core/version-check/' ) ) {
			return $result;
		}

		// It's a core-update request.
		$args['_beta_tester'] = true;

		$wp_version = get_bloginfo( 'version' );
		$url        = str_replace( 'version=' . $wp_version, 'version=' . $this->mangle_wp_version(), $url );

		return wp_remote_get( $url, $args );
	}

	/**
	 * Our option has changed so update the cached information pronto.
	 *
	 * @return void
	 */
	public function action_update_option_wp_beta_tester_stream() {
		do_action( 'wp_version_check' );
	}

	/**
	 * Get preferred update version from core.
	 *
	 * @return void
	 */
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

	/**
	 * Get modified WP version to pass to API check.
	 *
	 * @return void
	 */
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

	/**
	 * Returns whether beta is really downgrade.
	 *
	 * @return void
	 */
	public function check_if_settings_downgrade() {
		$wp_version         = get_bloginfo( 'version' );
		$wp_real_version    = explode( '-', $wp_version );
		$wp_mangled_version = explode( '-', $this->mangle_wp_version() );

		return version_compare( $wp_mangled_version[0], $wp_real_version[0], 'lt' );
	}
}
