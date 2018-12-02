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
 * Plugin Name: WordPress Beta Tester
 * Plugin URI: https://wordpress.org/plugins/wordpress-beta-tester/
 * Description: Allows you to easily upgrade to Beta releases.
 * Author: Peter Westwood, Andy Fragen
 * Version: 1.2.6.4
 * Network: true
 * Author URI: https://blog.ftwr.co.uk/
 * Text Domain: wordpress-beta-tester
 * License: GPL v2 or later
 * License URI: https://www.opensource.org/licenses/GPL-2.0
 * GitHub Plugin URI: https://github.com/afragen/wordpress-beta-tester
 */

define( 'WP_BETA_TESTER_DIR', dirname( __FILE__ ) );

/* Initialise ourselves */
add_action( 'plugins_loaded', 'load_beta_tester_plugin' );

function load_beta_tester_plugin() {
	require_once WP_BETA_TESTER_DIR . '/src/WPBT_Bootstrap.php';
	$wp_beta_tester_bootstrap = new WPBT_Bootstrap();
	$wp_beta_tester_bootstrap->run();
}

// Clear down
function wordpress_beta_tester_deactivate_or_activate() {
	delete_site_transient( 'update_core' );
}
register_activation_hook( __FILE__, 'wordpress_beta_tester_deactivate_or_activate' );
register_deactivation_hook( __FILE__, 'wordpress_beta_tester_deactivate_or_activate' );
