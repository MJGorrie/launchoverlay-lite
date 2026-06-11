<?php
/**
 * LO_Cart_Control — hides price and/or disables Add to Cart per product.
 *
 * @package LaunchOverlay
 */

defined( 'ABSPATH' ) || exit;

class LO_Cart_Control {

	private static $inst = null;

	public static function instance() {
		if ( null === self::$inst ) {
			self::$inst = new self();
		}
		return self::$inst;
	}

	private function __construct() {
		add_filter( 'woocommerce_get_price_html',         array( $this, 'maybe_hide_price' ),        10, 2 );
		add_filter( 'woocommerce_loop_add_to_cart_link',  array( $this, 'maybe_loop_button' ),       10, 2 );
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'block_add_to_cart' ),       10, 2 );

		// Mark product as not purchasable — suppresses add-to-cart form on ALL themes.
		add_filter( 'woocommerce_is_purchasable',         array( $this, 'maybe_not_purchasable' ),   10, 2 );

		// Inject custom message in place of the add-to-cart form on single product page.
		add_action( 'woocommerce_single_product_summary', array( $this, 'maybe_show_message' ),      31 );
	}

	/**
	 * Resolve overlay data for a product.
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

	/**
	 * Check if hide_add_to_cart is enabled for a product ID.
	 */
	private function should_hide_cart( $pid ) {
		$data = $this->resolve( $pid );
		return $data && true === $data['hide_add_to_cart'];
	}

	/**
	 * Hide price HTML when configured.
	 */
	public function maybe_hide_price( $html, $product ) {
		$data = $this->resolve( $product->get_id() );
		if ( $data && true === $data['hide_price'] ) {
			return '';
		}
		return $html;
	}

	/**
	 * Modify loop Add to Cart link.
	 */
	public function maybe_loop_button( $html, $product ) {
		$data = $this->resolve( $product->get_id() );
		if ( ! $data || true !== $data['hide_add_to_cart'] ) {
			return $html;
		}
		if ( ! empty( $data['custom_message'] ) ) {
			return '<span class="lo-replacement-msg">' . esc_html( $data['custom_message'] ) . '</span>';
		}
		return '';
	}

	/**
	 * Mark product as not purchasable — suppresses the entire add-to-cart
	 * form at the WooCommerce level, works on every theme.
	 */
	public function maybe_not_purchasable( $purchasable, $product ) {
		if ( $this->should_hide_cart( $product->get_id() ) ) {
			return false;
		}
		return $purchasable;
	}

	/**
	 * Show the custom replacement message on the single product page,
	 * in the same spot where the add-to-cart form was (priority 31, just after form at 30).
	 */
	public function maybe_show_message() {
		global $product;
		if ( ! $product ) {
			return;
		}
		$pid = is_object( $product ) && method_exists( $product, 'get_id' )
			? $product->get_id()
			: ( is_object( $product ) ? $product->ID : 0 );
		if ( ! $pid ) {
			return;
		}
		$data = $this->resolve( $pid );
		if ( ! $data || true !== $data['hide_add_to_cart'] ) {
			return;
		}
		if ( ! empty( $data['custom_message'] ) ) {
			echo '<div class="lo-replacement-msg lo-replacement-msg--single">'
				. esc_html( $data['custom_message'] )
				. '</div>';
		}
	}

	/**
	 * Block direct add-to-cart POST requests as a hard server-side guard.
	 */
	public function block_add_to_cart( $passed, $product_id ) {
		if ( $this->should_hide_cart( $product_id ) ) {
			wc_add_notice(
				__( 'This product is not yet available for purchase.', 'launchoverlay' ),
				'error'
			);
			return false;
		}
		return $passed;
	}
}
