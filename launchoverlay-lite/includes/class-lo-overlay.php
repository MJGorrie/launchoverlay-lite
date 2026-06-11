<?php
/**
 * LO_Overlay — injects banner overlay HTML inside WooCommerce product image markup.
 *
 * Uses two WooCommerce filters so the overlay span is part of the image HTML
 * itself, making it work correctly on any theme without JavaScript.
 *
 *  - woocommerce_product_get_image                    → loop / shop / category pages
 *  - woocommerce_single_product_image_thumbnail_html  → single product gallery
 *
 * @package LaunchOverlay
 */

defined( 'ABSPATH' ) || exit;

class LO_Overlay {

	private static $inst = null;

	/** @var LO_Settings */
	private $cfg;

	public static function instance() {
		if ( null === self::$inst ) {
			self::$inst = new self();
		}
		return self::$inst;
	}

	private function __construct() {
		$this->cfg = LO_Settings::instance();

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_filter( 'woocommerce_product_get_image',                    array( $this, 'filter_loop' ),   10, 2 );
		add_filter( 'woocommerce_single_product_image_thumbnail_html',  array( $this, 'filter_single' ), 10, 2 );
	}

	// ── Assets ───────────────────────────────────────────────────────────────

	/**
	 * Enqueue front-end stylesheet and JS only on WooCommerce pages.
	 */
	public function enqueue() {
		if ( ! is_woocommerce() && ! is_shop() && ! is_product_category() && ! is_product() ) {
			return;
		}

		wp_enqueue_style(
			'launchoverlay',
			LAUNCHOVERLAY_PLUGIN_URL . 'public/css/launchoverlay.css',
			array(),
			LAUNCHOVERLAY_VERSION
		);

		// Inject dynamic CSS custom properties for the user-chosen colours.
		$s = $this->cfg->all();
		wp_add_inline_style(
			'launchoverlay',
			sprintf(
				':root{--lo-cs:%s;--lo-po:%s;--lo-so:%s;--lo-txt:%s;}',
				sanitize_hex_color( $s['overlay_color_coming_soon'] ) ?: '#3b5bdb',
				sanitize_hex_color( $s['overlay_color_pre_order']   ) ?: '#f76707',
				sanitize_hex_color( $s['overlay_color_sold_out']    ) ?: '#343a40',
				sanitize_hex_color( $s['overlay_color_text']        ) ?: '#ffffff'
			)
		);

		wp_enqueue_script(
			'launchoverlay',
			LAUNCHOVERLAY_PLUGIN_URL . 'public/js/launchoverlay.js',
			array(),            // No jQuery dependency — pure vanilla JS
			LAUNCHOVERLAY_VERSION,
			true
		);
	}

	// ── Loop image filter ─────────────────────────────────────────────────────

	/**
	 * Wrap loop thumbnail HTML with an overlay container.
	 *
	 * @param string     $html    Original <img> HTML from WooCommerce.
	 * @param WC_Product $product Product object.
	 * @return string
	 */
	public function filter_loop( $html, $product ) {
		// Single-product page is handled by filter_single.
		if ( is_product() ) {
			return $html;
		}
		if ( 'yes' !== $this->cfg->get( 'show_on_shop' ) ) {
			return $html;
		}

		$data = $this->resolve( $product->get_id() );
		if ( ! $data ) {
			return $html;
		}

		return '<span class="lo-img-wrap">'
			. $html
			. $this->build_overlay( $product->get_id(), $data, false )
		. '</span>';
	}

	// ── Single product image filter ───────────────────────────────────────────

	/**
	 * Inject overlay inside the main gallery image div on single product pages.
	 *
	 * WooCommerce calls this filter for every slide in the gallery; we only
	 * inject on the featured / main image to avoid duplicating the overlay.
	 *
	 * @param string $html          Gallery image HTML block.
	 * @param int    $attachment_id Attachment post ID for this slide.
	 * @return string
	 */
	public function filter_single( $html, $attachment_id ) {
		if ( 'yes' !== $this->cfg->get( 'show_on_product' ) ) {
			return $html;
		}

		global $product;
		if ( ! $product ) {
			return $html;
		}

		$featured = (int) get_post_thumbnail_id( $product->get_id() );
		if ( $featured > 0 && (int) $attachment_id !== $featured ) {
			return $html;
		}

		$data = $this->resolve( $product->get_id() );
		if ( ! $data ) {
			return $html;
		}

		$overlay = $this->build_overlay( $product->get_id(), $data, true );

		// Place the overlay span immediately after the opening gallery div tag.
		$result = preg_replace(
			'/(<div[^>]+woocommerce-product-gallery__image[^>]*>)/i',
			'$1' . $overlay,
			$html,
			1
		);

		return ( null !== $result ) ? $result : $html;
	}

	// ── Resolve ───────────────────────────────────────────────────────────────

	/**
	 * Get overlay data: per-product meta first, then optional Pro bulk rules.
	 *
	 * The Pro plugin registers LO_Pro_Rules when active. Checking via
	 * class_exists() is safe — it adds no coupling when Pro is absent.
	 *
	 * @param  int $pid Product ID.
	 * @return array|false
	 */
	private function resolve( $pid ) {
		$data = LO_Product_Meta::get( $pid );
		if ( $data ) {
			return $data;
		}
		if ( class_exists( 'LO_Pro_Rules' ) ) {
			return LO_Pro_Rules::match( $pid );
		}
		return false;
	}

	// ── Build overlay HTML ────────────────────────────────────────────────────

	/**
	 * Render the overlay <span> HTML string.
	 *
	 * Defers to optional Pro helper classes (LO_Pro_Overlay) when present,
	 * falling back to the global settings otherwise.
	 *
	 * @param  int   $pid    Product ID.
	 * @param  array $data   Overlay data from resolve().
	 * @param  bool  $single Whether this is a single-product page.
	 * @return string  Safe HTML.
	 */
	public function build_overlay( $pid, array $data, $single = false ) {
		$type = sanitize_key( $data['type'] );

		// Style — Pro add-on can override per-product; falls back to global setting.
		$style = $this->cfg->get( 'overlay_style', 'banner' );
		if ( class_exists( 'LO_Pro_Overlay' ) ) {
			$style = LO_Pro_Overlay::get_style( $pid, $style );
		}
		$style = sanitize_key( $style );

		// Position from global settings.
		$pos = sanitize_key( $this->cfg->get( 'overlay_position', 'top-left' ) );

		// Label — Pro add-on can supply custom text; falls back to preset label.
		$types = LO_Settings::overlay_types();
		$label = isset( $types[ $type ] ) ? $types[ $type ] : __( 'Coming Soon', 'launchoverlay' );
		if ( class_exists( 'LO_Pro_Overlay' ) ) {
			$label = LO_Pro_Overlay::get_label( $pid, $type, $label );
		}

		// Inline colour overrides from Pro add-on.
		$inline_style = '';
		if ( class_exists( 'LO_Pro_Overlay' ) ) {
			$inline_style = LO_Pro_Overlay::get_inline_style( $pid );
		}

		$classes = array_filter( array(
			'lo-overlay',
			'lo-type-'  . $type,
			'lo-pos-'   . $pos,
			'lo-style-' . $style,
			$single ? 'lo-single' : '',
		) );

		return sprintf(
			'<span class="%1$s" aria-label="%2$s"%3$s><span class="lo-text" aria-hidden="true">%2$s</span></span>',
			esc_attr( implode( ' ', $classes ) ),
			esc_attr( $label ),
			$inline_style ? ' style="' . esc_attr( $inline_style ) . '"' : ''
		);
	}
}
