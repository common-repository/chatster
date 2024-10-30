<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Adds Translation Support
 */
add_action( 'plugins_loaded', function() {
    load_plugin_textdomain( CHATSTER_DOMAIN, FALSE, basename( CHATSTER_PATH ) . '/languages' );
});

/**
 * Adds Settings link to Plugin list
 */
function chatster_add_settings_link( $links ) {
	$links[] = '<a href="' .
		admin_url( 'admin.php?page='.'chatster-menu&chtab=settings' ) . '">' . esc_html__( 'Settings', CHATSTER_DOMAIN ) . '</a>';
	return $links;
}
add_filter('plugin_action_links_'.plugin_basename( CHATSTER_FILE_PATH ), 'chatster_add_settings_link');
