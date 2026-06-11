<?php
/**
 * Plugin Name:       LaunchOverlay – Coming Soon Banner for Products
 * Plugin URI:        https://gorrie.us/products-page/
 * Description:       Add "Coming Soon", "Pre-Order", and launch banner overlays to WooCommerce product images. Control purchase availability per product.
 * Version:           1.1.1
 * Author:            Mark J. Gorrie
 * Author URI:        https://gorrie.us/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       launchoverlay
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * WC requires at least: 7.0
 * WC tested up to:   9.0
 *
 * @package LaunchOverlay
 */

defined( 'ABSPATH' ) || exit;

define( 'LAUNCHOVERLAY_VERSION',     '1.1.1' );
define( 'LAUNCHOVERLAY_PLUGIN_FILE',  __FILE__ );
define( 'LAUNCHOVERLAY_PLUGIN_DIR',   plugin_dir_path( __FILE__ ) );
define( 'LAUNCHOVERLAY_PLUGIN_URL',   plugin_dir_url( __FILE__ ) );
define( 'LAUNCHOVERLAY_PLUGIN_BASE',  plugin_basename( __FILE__ ) );

/* ── HPOS compatibility ─────────────────────────────────────────────────────── */
add_action( 'before_woocommerce_init', function () {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

/* ── Boot ───────────────────────────────────────────────────────────────────── */
add_action( 'plugins_loaded', 'launchoverlay_boot', 5 );
function launchoverlay_boot() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'launchoverlay_wc_notice' );
		return;
	}

	load_plugin_textdomain( 'launchoverlay', false, dirname( LAUNCHOVERLAY_PLUGIN_BASE ) . '/languages' );

	require_once LAUNCHOVERLAY_PLUGIN_DIR . 'includes/class-lo-settings.php';
	require_once LAUNCHOVERLAY_PLUGIN_DIR . 'includes/class-lo-product-meta.php';
	require_once LAUNCHOVERLAY_PLUGIN_DIR . 'includes/class-lo-overlay.php';
	require_once LAUNCHOVERLAY_PLUGIN_DIR . 'includes/class-lo-cart-control.php';
	require_once LAUNCHOVERLAY_PLUGIN_DIR . 'admin/class-lo-admin.php';

	LO_Settings::instance();
	LO_Product_Meta::instance();
	LO_Overlay::instance();
	LO_Cart_Control::instance();
	LO_Admin::instance();
}

function launchoverlay_wc_notice() {
	printf(
		'<div class="notice notice-error"><p>%s</p></div>',
		wp_kses(
			sprintf(
				/* translators: %s: WooCommerce plugin link */
				__( 'LaunchOverlay requires %s to be installed and active.', 'launchoverlay' ),
				'<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a>'
			),
			array( 'a' => array( 'href' => array(), 'target' => array() ) )
		)
	);
}

/* ── Activation defaults ────────────────────────────────────────────────────── */
register_activation_hook( __FILE__, 'launchoverlay_activate' );
function launchoverlay_activate() {
	if ( ! get_option( 'launchoverlay_settings' ) ) {
		add_option( 'launchoverlay_settings', array(
			'overlay_color_coming_soon' => '#3b5bdb',
			'overlay_color_pre_order'   => '#f76707',
			'overlay_color_sold_out'    => '#343a40',
			'overlay_color_text'        => '#ffffff',
			'overlay_position'          => 'top-left',
			'overlay_style'             => 'banner',
			'show_on_shop'              => 'yes',
			'show_on_product'           => 'yes',
		) );
	}
}

/* ── Plugin action links ────────────────────────────────────────────────────── */
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'launchoverlay_action_links' );
function launchoverlay_action_links( $links ) {
	array_unshift(
		$links,
		'<a href="' . esc_url( admin_url( 'admin.php?page=launchoverlay' ) ) . '">'
			. esc_html__( 'Settings', 'launchoverlay' )
		. '</a>'
	);
	return $links;
}
