<?php
/**
 * WordPress Beta Tester
 *
 * @package WordPress_Beta_Tester
 * @author Andy Fragen and Paul Biron, original author Peter Westwood.
 * @license GPLv2+
 * @copyright 2009-2016 Peter Westwood (email : peter.westwood@ftwr.co.uk)
 */

/**
 * WPBT Help
 */
class WPBT_Help {
	/**
	 * Load hooks for screen help tabs.
	 *
	 * @return void
	 */
	public function load_hooks() {
		add_action( 'current_screen', array( $this, 'add_help_tabs' ) );
	}

	/**
	 * Add individual help tabs.
	 *
	 * @return void
	 */
	public function add_help_tabs() {
		get_current_screen()->add_help_tab(
			array(
				'id'      => 'overview',
				'title'   => __( 'Overview' ),
				'content' => '<p>' . __( 'This screen provides help information for the Beta Tester plugin.', 'wordpress-beta-tester' ) . '</p>',
			)
		);

		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
			'<p>' . __( '<a href="https://make.wordpress.org/core/handbook/testing/beta-testing/">Beta Testing</a>', 'wordpress-beta-tester' ) . '</p>'
		);
	}
}
