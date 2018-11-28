<?php
/*
	Plugin Name: WordPress Beta Tester
	Plugin URI: https://wordpress.org/plugins/wordpress-beta-tester/
	Description: Allows you to easily upgrade to Beta releases.
	Author: Peter Westwood
	Version: 1.2.6.1
	Network: true
	Author URI: https://blog.ftwr.co.uk/
	Text Domain: wordpress-beta-tester
	License: GPL v2 or later
	GitHub Plugin URI: https://github.com/afragen/wordpress-beta-tester
*/

/*
	Copyright 2009-2016 Peter Westwood (email : peter.westwood@ftwr.co.uk)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define( 'WP_BETA_TESTER_DIR', dirname( __FILE__ ) );

/* Initialise ourselves */
add_action( 'plugins_loaded', 'load_beta_tester_plugin' );

function load_beta_tester_plugin() {
	require_once WP_BETA_TESTER_DIR . '/src/WBT_Bootstrap.php';
	$wp_beta_tester_bootstrap = new WBT_Bootstrap();
	$wp_beta_tester_bootstrap->run();
}

// Clear down
function wordpress_beta_tester_deactivate_or_activate() {
	delete_site_transient( 'update_core' );
}
register_activation_hook( __FILE__, 'wordpress_beta_tester_deactivate_or_activate' );
register_deactivation_hook( __FILE__, 'wordpress_beta_tester_deactivate_or_activate' );
