<?php
/**
 * LaunchOverlay Lite — Uninstall
 * Runs when the plugin is deleted from WordPress admin.
 *
 * @package LaunchOverlay
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'launchoverlay_settings' );

$keys = array(
	'_lo_enabled',
	'_lo_type',
	'_lo_hide_add_to_cart',
	'_lo_hide_price',
	'_lo_custom_message',
);

foreach ( $keys as $k ) {
	delete_post_meta_by_key( $k );
}

wp_clear_scheduled_hook( 'launchoverlay_daily_check' );
