<?php
/**
 * LO_Product_Meta — per-product overlay settings stored as post meta.
 *
 * WordPress.org guidelines:
 *  - No references to paid/Pro upgrades in a .org-hosted plugin.
 *  - All output properly escaped.
 *  - All $_POST data sanitised and unslashed before use.
 *
 * @package LaunchOverlay
 */

defined( 'ABSPATH' ) || exit;

class LO_Product_Meta {

	const PFX = '_lo_';

	private static $inst = null;

	public static function instance() {
		if ( null === self::$inst ) {
			self::$inst = new self();
		}
		return self::$inst;
	}

	private function __construct() {
		add_filter( 'woocommerce_product_data_tabs',    array( $this, 'add_tab' ) );
		add_action( 'woocommerce_product_data_panels',  array( $this, 'render_panel' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save' ) );
	}

	// ── Tab ──────────────────────────────────────────────────────────────────

	public function add_tab( $tabs ) {
		$tabs['launchoverlay'] = array(
			'label'    => __( 'LaunchOverlay', 'launchoverlay' ),
			'target'   => 'lo_product_panel',
			'class'    => array(),
			'priority' => 80,
		);
		return $tabs;
	}

	// ── Panel ─────────────────────────────────────────────────────────────────

	public function render_panel() {
		global $post;
		$pid = $post->ID;

		$enabled    = get_post_meta( $pid, self::PFX . 'enabled',          true );
		$type       = get_post_meta( $pid, self::PFX . 'type',             true );
		$hide_btn   = get_post_meta( $pid, self::PFX . 'hide_add_to_cart', true );
		$hide_price = get_post_meta( $pid, self::PFX . 'hide_price',       true );
		$msg        = get_post_meta( $pid, self::PFX . 'custom_message',   true );

		// Pro plugin signals its presence via this constant.
		$is_pro = defined( 'LAUNCHOVERLAY_PRO_ACTIVE' ) && LAUNCHOVERLAY_PRO_ACTIVE;
		?>
		<div id="lo_product_panel" class="panel woocommerce_options_panel lo-product-panel">

			<div class="lo-panel-header">
				<span class="lo-panel-icon" aria-hidden="true">&#128640;</span>
				<span class="lo-panel-title"><?php esc_html_e( 'LaunchOverlay', 'launchoverlay' ); ?></span>
				<?php if ( $is_pro ) : ?>
					<span class="lo-pro-pill"><?php esc_html_e( 'Pro', 'launchoverlay' ); ?></span>
				<?php endif; ?>
			</div>

			<div class="options_group lo-options-group">

				<!-- Enable Overlay -->
				<p class="form-field lo-field">
					<label class="lo-label"><?php esc_html_e( 'Enable Overlay', 'launchoverlay' ); ?></label>
					<label class="lo-toggle" for="lo_enabled">
						<input type="checkbox" id="lo_enabled" name="lo_enabled" value="yes"
							<?php checked( $enabled, 'yes' ); ?> />
						<span class="lo-toggle-track"><span class="lo-toggle-thumb"></span></span>
						<span class="lo-toggle-desc">
							<?php esc_html_e( 'Show a banner overlay on this product\'s image.', 'launchoverlay' ); ?>
						</span>
					</label>
				</p>

				<!-- Banner Text -->
				<p class="form-field lo-field">
					<label for="lo_type" class="lo-label">
						<?php esc_html_e( 'Banner Text', 'launchoverlay' ); ?>
					</label>
					<select id="lo_type" name="lo_type" class="lo-select">
						<option value=""><?php esc_html_e( '— Select —', 'launchoverlay' ); ?></option>
						<?php foreach ( LO_Settings::overlay_types() as $k => $v ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $type, $k ); ?>>
								<?php echo esc_html( $v ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</p>

				<!-- Disable Add to Cart -->
				<p class="form-field lo-field">
					<label class="lo-label">
						<?php esc_html_e( 'Disable Add to Cart', 'launchoverlay' ); ?>
					</label>
					<label class="lo-toggle" for="lo_hide_add_to_cart">
						<input type="checkbox" id="lo_hide_add_to_cart" name="lo_hide_add_to_cart" value="yes"
							<?php checked( $hide_btn, 'yes' ); ?> />
						<span class="lo-toggle-track"><span class="lo-toggle-thumb"></span></span>
						<span class="lo-toggle-desc">
							<?php esc_html_e( 'Remove the Add to Cart button and block direct purchase.', 'launchoverlay' ); ?>
						</span>
					</label>
				</p>

				<!-- Hide Price -->
				<p class="form-field lo-field">
					<label class="lo-label">
						<?php esc_html_e( 'Hide Price', 'launchoverlay' ); ?>
					</label>
					<label class="lo-toggle" for="lo_hide_price">
						<input type="checkbox" id="lo_hide_price" name="lo_hide_price" value="yes"
							<?php checked( $hide_price, 'yes' ); ?> />
						<span class="lo-toggle-track"><span class="lo-toggle-thumb"></span></span>
						<span class="lo-toggle-desc">
							<?php esc_html_e( 'Remove the product price from shop and product pages.', 'launchoverlay' ); ?>
						</span>
					</label>
				</p>

				<!-- Replacement Message -->
				<p class="form-field lo-field">
					<label for="lo_custom_message" class="lo-label">
						<?php esc_html_e( 'Replacement Message', 'launchoverlay' ); ?>
					</label>
					<input type="text" id="lo_custom_message" name="lo_custom_message"
						value="<?php echo esc_attr( $msg ); ?>"
						class="lo-text-input"
						placeholder="<?php esc_attr_e( 'e.g. Notify me when available', 'launchoverlay' ); ?>" />
					<span class="lo-field-desc">
						<?php esc_html_e( 'Shown instead of the Add to Cart button when it is disabled.', 'launchoverlay' ); ?>
					</span>
				</p>

			</div><!-- .lo-options-group -->

			<div class="lo-panel-footer">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=launchoverlay' ) ); ?>">
					&#9881; <?php esc_html_e( 'Global Settings', 'launchoverlay' ); ?>
				</a>
			</div>

		</div><!-- #lo_product_panel -->
		<?php
	}

	// ── Save ─────────────────────────────────────────────────────────────────

	public function save( $pid ) {
		// Sanitise and unslash every field before storing.
		$enabled    = isset( $_POST['lo_enabled'] )           ? 'yes' : 'no';
		$type       = isset( $_POST['lo_type'] )
						? sanitize_key( wp_unslash( $_POST['lo_type'] ) ) : '';
		$hide_btn   = isset( $_POST['lo_hide_add_to_cart'] )  ? 'yes' : 'no';
		$hide_price = isset( $_POST['lo_hide_price'] )        ? 'yes' : 'no';
		$msg        = isset( $_POST['lo_custom_message'] )
						? sanitize_text_field( wp_unslash( $_POST['lo_custom_message'] ) ) : '';

		update_post_meta( $pid, self::PFX . 'enabled',          $enabled    );
		update_post_meta( $pid, self::PFX . 'type',             $type       );
		update_post_meta( $pid, self::PFX . 'hide_add_to_cart', $hide_btn   );
		update_post_meta( $pid, self::PFX . 'hide_price',       $hide_price );
		update_post_meta( $pid, self::PFX . 'custom_message',   $msg        );
	}

	// ── Static data accessor ──────────────────────────────────────────────────

	/**
	 * Return overlay data for a product, or false when disabled/unconfigured.
	 *
	 * @param  int $pid Product ID.
	 * @return array|false
	 */
	public static function get( $pid ) {
		if ( 'yes' !== get_post_meta( $pid, self::PFX . 'enabled', true ) ) {
			return false;
		}
		$type = get_post_meta( $pid, self::PFX . 'type', true );
		return array(
			'type'             => $type,
			'hide_add_to_cart' => 'yes' === get_post_meta( $pid, self::PFX . 'hide_add_to_cart', true ),
			'hide_price'       => 'yes' === get_post_meta( $pid, self::PFX . 'hide_price',       true ),
			'custom_message'   => get_post_meta( $pid, self::PFX . 'custom_message',             true ),
		);
	}
}
