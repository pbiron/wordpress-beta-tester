<?php
/**
 * WordPress Beta Tester
 *
 * @package WordPress_Beta_Tester
 * @author Andy Fragen, original author Peter Westwood.
 * @license GPLv2+
 * @copyright 2009-2016 Peter Westwood (email : peter.westwood@ftwr.co.uk)
 */

/**
 * WPBT_Core
 */
class WPBT_Core {
	/**
	 * Placeholder for saved options.
	 *
	 * @var array
	 */
	protected static $options;

	/**
	 * Holds the WP_Beta_Tester instance.
	 *
	 * @var WP_Beta_Tester
	 */
	protected $wp_beta_tester;

	/**
	 * Constructor.
	 *
	 * @param  WP_Beta_Tester $wp_beta_tester Instance of class WP_Beta_Tester.
	 * @param  array          $options        Site options.
	 * @return void
	 */
	public function __construct( WP_Beta_Tester $wp_beta_tester, $options ) {
		self::$options        = $options;
		$this->wp_beta_tester = $wp_beta_tester;
	}

	/**
	 * Load hooks.
	 *
	 * @return void
	 */
	public function load_hooks() {
		add_filter( 'wp_beta_tester_add_settings_tabs', array( $this, 'add_settings_tab' ) );
		add_action( 'wp_beta_tester_add_settings', array( $this, 'add_settings' ) );
		add_action( 'wp_beta_tester_add_admin_page', array( $this, 'add_admin_page' ), 10, 2 );
		add_action( 'wp_beta_tester_update_settings', array( $this, 'save_settings' ) );
	}

	/**
	 * Add settings tab for class.
	 *
	 * @param  array $tabs Settings tabs.
	 * @return array
	 */
	public function add_settings_tab( $tabs ) {
		return array_merge( (array) $tabs, array( 'wp_beta_tester_core' => esc_html__( 'WP Beta Tester Settings', 'wordpress-beta-tester' ) ) );
	}

	/**
	 * Setup Settings API.
	 *
	 * @return void
	 */
	public function add_settings() {
		register_setting(
			'wp_beta_tester',
			'wp_beta_tester_core',
			array( 'WPBT_Setting', 'sanitize' )
		);

		add_settings_section(
			'wp_beta_tester_core',
			esc_html__( 'Core Settings', 'wordpress-beta-tester' ),
			array( $this, 'print_core_settings_top' ),
			'wp_beta_tester_core'
		);

		add_settings_field(
			'channel_settings',
			null,
			array( $this, 'channel_radio_group' ),
			'wp_beta_tester_core',
			'wp_beta_tester_core'
		);

		add_settings_field(
			'stream_settings',
			null,
			array( $this, 'stream_radio_group' ),
			'wp_beta_tester_core',
			'wp_beta_tester_core'
		);
	}

	/**
	 * Save settings.
	 *
	 * @param  mixed $post_data $_POST data.
	 * @return void
	 */
	public function save_settings( $post_data ) {
		if ( isset( $post_data['option_page'] )
			&& 'wp_beta_tester_core' === $post_data['option_page']
		) {
			$options                  = isset( $post_data['wp-beta-tester'] ) ? $post_data['wp-beta-tester'] : 'branch-development';
			self::$options['channel'] = WPBT_Settings::sanitize( $options );

			$options_beta_rc                = isset( $post_data['wp-beta-tester-beta-rc'] ) ? $post_data['wp-beta-tester-beta-rc'] : '';
			self::$options['stream-option'] = WPBT_Settings::sanitize( $options_beta_rc );

			// set an option when picking 'branch-development' channel.
			// used to ensure correct mangled version is returned.
			self::$options['revert'] = 'branch-development' === $options;
			update_site_option( 'wp_beta_tester', (array) self::$options );
			add_filter( 'wp_beta_tester_save_redirect', array( $this, 'save_redirect_page' ) );
		}
	}

	/**
	 * Redirect page/tab after saving options.
	 *
	 * @param  array $option_page Settings tabs.
	 * @return array
	 */
	public function save_redirect_page( $option_page ) {
		return array_merge( $option_page, array( 'wp_beta_tester_core' ) );
	}

	/**
	 * Print settings section information.
	 *
	 * @return void
	 */
	public function print_core_settings_top() {
		$this->wp_beta_tester->action_admin_head_plugins_php(); // Check configuration.
		$preferred = $this->wp_beta_tester->get_preferred_from_update_core();
		if ( 'development' !== $preferred->response ) {
			echo '<div class="updated fade">';
			echo '<p>' . wp_kses_post( __( '<strong>Please note:</strong> There are no development builds available for the beta stream you have chosen, so you will receive normal update notifications.', 'wordpress-beta-tester' ) ) . '</p>';
			echo '</div>';
		}

		$preferred->version = $this->get_next_version( $preferred->version );

		echo '<div><p>';
		printf(
			/* translators: 1: link to backing up database, 2: link to make.wp.org/core, 3: link to beta support forum */
			wp_kses_post( __( 'By their nature, these releases are unstable and should not be used anyplace where your data is important. So please <a href="%1$s">back up your database</a> before upgrading to a test release. In order to hear about the latest beta releases, your best bet is to watch the <a href="%2$s">development blog</a> and the <a href="%3$s">beta forum</a>.', 'wordpress-beta-tester' ) ),
			esc_url( _x( 'https://codex.wordpress.org/Backing_Up_Your_Database', 'URL to database backup instructions', 'wordpress-beta-tester' ) ),
			'https://make.wordpress.org/core/',
			esc_url( _x( 'https://wordpress.org/support/forum/alphabeta', 'URL to beta support forum', 'wordpress-beta-tester' ) )
		);
		echo '</p><p>';
		printf(
			/* translators: %s: link to new trac ticket */
			wp_kses_post( __( 'Thank you for helping test WordPress. Please <a href="%s">report any bugs you find</a>.', 'wordpress-beta-tester' ) ),
			'https://core.trac.wordpress.org/newticket'
		);
		echo '</p><p>';
		echo wp_kses_post( __( 'By default, your WordPress install uses the stable update channel. To return to this, please deactivate this plugin and re-install from the <a href="update-core.php">WordPress Updates</a> page.', 'wordpress-beta-tester' ) );
		echo '</p><p>';
		printf(
			/* translators: %s: update version */
			wp_kses_post( __( 'Currently your site is set to update to %s.', 'wordpress-beta-tester' ) ),
			'<strong>' . esc_attr( $preferred->version ) . '</strong>'
		);
		echo '</p></div>';
	}

	/**
	 * Create channel settings radio button options.
	 *
	 * @return void
	 */
	public function channel_radio_group() {
		?>
		<fieldset>
		<tr><th colspan="2">
			<?php esc_html_e( 'Select the update channel you would like this website to use:', 'wordpress-beta-tester' ); ?>
		</th></tr>
		<tr>
			<th><label><input name="wp-beta-tester" id="update-stream-point-nightlies" type="radio" value="branch-development" class="tog" <?php checked( 'branch-development', self::$options['channel'] ); ?> />
			<?php esc_html_e( 'Point release', 'wordpress-beta-tester' ); ?>
			</label></th>
			<td><?php esc_html_e( 'This contains the work that is occurring on a branch in preparation for a x.x.x point release. This should also be fairly stable but will be available before the branch is ready for release.', 'wordpress-beta-tester' ); ?></td>
		</tr>
		<tr>
			<th><label><input name="wp-beta-tester" id="update-stream-bleeding-nightlies" type="radio" value="development" class="tog" <?php checked( 'development', self::$options['channel'] ); ?> />
			<?php esc_html_e( 'Bleeding edge', 'wordpress-beta-tester' ); ?>
			</label></th>
			<td><?php echo( wp_kses_post( __( 'This is the bleeding edge development code from `trunk` which may be unstable at times. <em>Only use this if you really know what you are doing</em>.', 'wordpress-beta-tester' ) ) ); ?></td>
		</tr>
		</fieldset>
		<?php
	}

	/**
	 * Create stream settings radio button options.
	 *
	 * @return void
	 */
	public function stream_radio_group() {
		?>
		<fieldset>
		<tr><th colspan="2">
			<?php esc_html_e( 'Select one of the stream options below:', 'wordpress-beta-tester' ); ?>
		</th></tr>
		<tr>
			<th><label><input name="wp-beta-tester-beta-rc" id="update-stream-beta" type="radio" value="" class="tog" <?php checked( false, self::$options['stream-option'] ); ?> />
			<?php esc_html_e( 'Nightlies', 'wordpress-beta-tester' ); ?>
			</label></th>
			<td><?php echo( wp_kses_post( __( 'Latest daily updates.', 'wordpress-beta-tester' ) ) ); ?></td>
		</tr>

		<tr>
			<th><label><input name="wp-beta-tester-beta-rc" id="update-stream-beta" type="radio" value="beta" class="tog" <?php checked( 'beta', self::$options['stream-option'] ); ?> />
			<?php esc_html_e( 'Beta/RC Only', 'wordpress-beta-tester' ); ?>
			</label></th>
			<td><?php echo( wp_kses_post( __( 'This is for the Beta/RC releases only of the selected channel.', 'wordpress-beta-tester' ) ) ); ?></td>
		</tr>
		<tr>
			<th><label><input name="wp-beta-tester-beta-rc" id="update-stream-rc" type="radio" value="rc" class="tog" <?php checked( 'rc', self::$options['stream-option'] ); ?> />
			<?php esc_html_e( 'Release Candidates Only', 'wordpress-beta-tester' ); ?>
			</label></th>
			<td><?php echo( wp_kses_post( __( 'This is for the Release Candidate releases only of the selected channel.', 'wordpress-beta-tester' ) ) ); ?></td>
		</tr>
		</fieldset>
		<?php
	}

	/**
	 * Create core settings page.
	 *
	 * @param  array  $tab    Settings tab.
	 * @param  string $action Settings form action.
	 * @return void
	 */
	public function add_admin_page( $tab, $action ) {
		?>
		<div>
			<?php if ( 'wp_beta_tester_core' === $tab ) : ?>
			<form method="post" action="<?php esc_attr_e( $action ); ?>">
				<?php settings_fields( 'wp_beta_tester_core' ); ?>
				<?php do_settings_sections( 'wp_beta_tester_core' ); ?>
				<?php submit_button(); ?>
			</form>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Get the next version the site will be updated to.
	 *
	 * @since 2.2.0
	 *
	 * @param  string $preferred_version The preferred version.
	 * @return string
	 */
	public function get_next_version( $preferred_version ) {
		$beta_rc      = ! empty( self::$options['stream-option'] );
		$next_version = $this->calculate_next_versions();

		if ( ! $beta_rc && ! empty( $next_version ) && preg_match( '/alpha|beta|RC/', get_bloginfo( 'version' ) ) ) {
			// Site is not on a beta/RC stream so use the preferred version.
			/* translators: %s: version number */
			return sprintf( __( 'version %s', 'wordpress-beta-tester' ), $preferred_version );
		}

		if ( 1 === count( $next_version ) ) {
			$next_version = array_shift( $next_version );
		} elseif ( empty( $next_version ) ) {
			$next_version = __( 'next development version', 'wordpress-beta-tester' );
		} else {
			// show all versions that may come next.
			add_filter( 'wp_sprintf_l', array( $this, 'wpbt_sprintf_or' ) );
			/* translators: %l: next version numbers */
			$next_version = wp_sprintf( __( 'version %l', 'wordpress-beta-tester' ), $next_version ) . ', ' . __( 'whichever is released first', 'wordpress-beta-tester' );
			remove_filter( 'wp_sprintf_l', array( $this, 'wpbt_sprintf_or' ) );
		}

		return $next_version;
	}

	/**
	 * Calculate next versions.
	 *
	 * @return array $next_version
	 */
	private function calculate_next_versions() {
		$wp_version       = get_bloginfo( 'version' );
		$exploded_version = explode( '-', $wp_version );
		$next_release     = explode( '.', $exploded_version[0] );

		if ( ! isset( $exploded_version[1] )
			|| ( 'development' === self::$options['channel'] && isset( $next_release[2] ) )
			|| ( 'branch-development' === self::$options['channel'] && ! isset( $next_release[2] ) )
		) {
			return array();
		}

		$is_alpha     = 'alpha' === $exploded_version[1];
		$current_beta = preg_match( '/beta(?)/', $exploded_version[1], $beta_version );
		$current_rc   = preg_match( '/RC(?)/', $exploded_version[1], $rc_version );

		$next_version = array(
			'beta'    => ! empty( $beta_version ) || $is_alpha ? $exploded_version[0] . '-beta' . ( ++$current_beta ) : false,
			'rc'      => $exploded_version[0] . '-RC' . ( ++$current_rc ),
			'release' => $exploded_version[0],
		);
		if ( ! $next_version['beta'] || 'rc' === self::$options['stream-option'] ) {
			unset( $next_version['beta'] );
		}

		return $next_version;
	}

	/**
	 * Change the delimiters used by wp_sprintf_l().
	 *
	 * Placeholders (%s) are included to assist translators and then
	 * removed before the array of strings reaches the filter.
	 *
	 * Please note: Ampersands and entities should be avoided here.
	 *
	 * @since 2.2.1
	 *
	 * @param array $delimiters An array of translated delimiters.
	 */
	public function wpbt_sprintf_or( $delimiters ) {
		$delimiters = array(
			/* translators: Used to join items in a list with more than 2 items. */
			'between'          => sprintf( __( '%1$s, %2$s', 'wordpress-beta-tester' ), '', '' ),
			/* translators: Used to join last two items in a list with more than 2 times. */
			'between_last_two' => sprintf( __( '%1$s, or %2$s', 'wordpress-beta-tester' ), '', '' ),
			/* translators: Used to join items in a list with only 2 items. */
			'between_only_two' => sprintf( __( '%1$s or %2$s', 'wordpress-beta-tester' ), '', '' ),
		);

		return $delimiters;
	}
}
