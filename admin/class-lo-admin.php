<?php
/**
 * LO_Admin — settings page for LaunchOverlay.
 *
 * WordPress.org guidelines applied:
 *  - No references to paid add-ons or external purchase links.
 *  - No locked/gated features.
 *  - All output escaped; nonce verified; capability checked before saves.
 *  - No tabs or UI sections promoting a Pro/paid version.
 *
 * @package LaunchOverlay
 */

defined( 'ABSPATH' ) || exit;

class LO_Admin {

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
		add_action( 'admin_menu',            array( $this, 'register_menu' ) );
		add_action( 'admin_init',            array( $this, 'handle_save' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	// ── Menu ─────────────────────────────────────────────────────────────────

	public function register_menu() {
		add_submenu_page(
			'woocommerce',
			__( 'LaunchOverlay', 'launchoverlay' ),
			__( 'LaunchOverlay', 'launchoverlay' ),
			'manage_woocommerce',
			'launchoverlay',
			array( $this, 'page' )
		);
	}

	// ── Assets ───────────────────────────────────────────────────────────────

	public function enqueue( $hook ) {
		// Load on plugin settings page AND product edit screens.
		$allowed_hooks = array(
			'woocommerce_page_launchoverlay',
			'post.php',
			'post-new.php',
		);
		if ( ! in_array( $hook, $allowed_hooks, true ) ) {
			return;
		}
		// On product edit screens only load when editing a product.
		if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true )
			&& 'product' !== get_post_type()
		) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style(
			'lo-admin',
			LAUNCHOVERLAY_PLUGIN_URL . 'admin/css/lo-admin.css',
			array( 'wp-color-picker' ),
			LAUNCHOVERLAY_VERSION
		);
		wp_enqueue_script(
			'lo-admin',
			LAUNCHOVERLAY_PLUGIN_URL . 'admin/js/lo-admin.js',
			array( 'jquery', 'wp-color-picker' ),
			LAUNCHOVERLAY_VERSION,
			true
		);
	}

	// ── Save ─────────────────────────────────────────────────────────────────

	public function handle_save() {
		if ( empty( $_POST['lo_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['lo_nonce'] ) ), 'lo_save' ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		if ( ! isset( $_POST['lo_save_general'] ) ) {
			return;
		}

		$this->cfg->save( wp_unslash( $_POST ) );
		add_settings_error(
			'launchoverlay',
			'lo_saved',
			__( 'Settings saved.', 'launchoverlay' ),
			'success'
		);
	}

	// ── Page ─────────────────────────────────────────────────────────────────

	public function page() {
		settings_errors( 'launchoverlay' );
		?>
		<div class="wrap lo-wrap">

			<div class="lo-header">
				<div class="lo-header-brand">
					<div class="lo-header-icon">
						<svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
							<rect width="32" height="32" rx="8" fill="#3b5bdb"/>
							<path d="M8 22 L16 8 L24 22 Z" fill="white" opacity="0.9"/>
							<rect x="13" y="17" width="6" height="2" rx="1" fill="white" opacity="0.6"/>
						</svg>
					</div>
					<div>
						<h1 class="lo-header-title"><?php esc_html_e( 'LaunchOverlay', 'launchoverlay' ); ?></h1>
						<p class="lo-header-sub">
							<?php esc_html_e( 'Add coming soon and launch banners to your WooCommerce products.', 'launchoverlay' ); ?>
						</p>
					</div>
				</div>
			</div>

			<form method="post" action="">
				<?php wp_nonce_field( 'lo_save', 'lo_nonce' ); ?>

				<div class="lo-grid">

					<?php $this->card_colours(); ?>
					<?php $this->card_style(); ?>
					<?php $this->card_display(); ?>
					<?php $this->card_quickstart(); ?>

				</div>

				<div class="lo-save-bar">
					<input type="submit" name="lo_save_general"
						class="lo-btn-primary"
						value="<?php esc_attr_e( 'Save Settings', 'launchoverlay' ); ?>" />
				</div>

			</form>

		</div><!-- .lo-wrap -->
		<?php
	}

	// ── Cards ─────────────────────────────────────────────────────────────────

	private function card_colours() {
		$s = $this->cfg->all();
		?>
		<div class="lo-card">
			<div class="lo-card-head">
				<span class="lo-card-icon" aria-hidden="true">&#127912;</span>
				<h2><?php esc_html_e( 'Colour Themes', 'launchoverlay' ); ?></h2>
			</div>
			<div class="lo-card-body">
				<?php
				$colour_fields = array(
					'overlay_color_coming_soon' => __( 'Coming Soon',    'launchoverlay' ),
					'overlay_color_pre_order'   => __( 'Pre-Order',      'launchoverlay' ),
					'overlay_color_sold_out'    => __( 'Sold Out',       'launchoverlay' ),
					'overlay_color_text'        => __( 'Text Colour',    'launchoverlay' ),
				);
				foreach ( $colour_fields as $key => $label ) :
					$val = isset( $s[ $key ] ) ? $s[ $key ] : '#3b5bdb';
				?>
					<div class="lo-field-row">
						<label class="lo-field-label" for="<?php echo esc_attr( $key ); ?>">
							<?php echo esc_html( $label ); ?>
						</label>
						<input type="text"
							id="<?php echo esc_attr( $key ); ?>"
							name="<?php echo esc_attr( $key ); ?>"
							value="<?php echo esc_attr( $val ); ?>"
							class="lo-color-field" />
					</div>
				<?php endforeach; ?>

				<p class="lo-field-desc" style="margin-top:10px;">
					<?php esc_html_e( 'Tip: these colours apply globally. Each product can override them individually.', 'launchoverlay' ); ?>
				</p>
			</div>
		</div>
		<?php
	}

	private function card_style() {
		$s = $this->cfg->all();
		?>
		<div class="lo-card">
			<div class="lo-card-head">
				<span class="lo-card-icon" aria-hidden="true">&#127775;</span>
				<h2><?php esc_html_e( 'Style & Position', 'launchoverlay' ); ?></h2>
			</div>
			<div class="lo-card-body">

				<div class="lo-field-row">
					<label class="lo-field-label">
						<?php esc_html_e( 'Style', 'launchoverlay' ); ?>
					</label>
					<div class="lo-style-picker">
						<?php foreach ( LO_Settings::styles() as $v => $l ) :
							$selected = $s['overlay_style'] === $v ? 'is-selected' : '';
						?>
							<label class="lo-style-option <?php echo esc_attr( $selected ); ?>">
								<input type="radio" name="overlay_style"
									value="<?php echo esc_attr( $v ); ?>"
									<?php checked( $s['overlay_style'], $v ); ?> />
								<span class="lo-style-preview lo-style-preview--<?php echo esc_attr( $v ); ?>">
									<span class="lo-style-demo-img"></span>
									<span class="lo-style-demo-badge">
										<?php esc_html_e( 'Soon', 'launchoverlay' ); ?>
									</span>
								</span>
								<span class="lo-style-name"><?php echo esc_html( $l ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>

				<div class="lo-field-row lo-field-row--mt">
					<label class="lo-field-label" for="overlay_position">
						<?php esc_html_e( 'Position', 'launchoverlay' ); ?>
					</label>
					<select id="overlay_position" name="overlay_position" class="lo-select">
						<?php foreach ( LO_Settings::positions() as $v => $l ) : ?>
							<option value="<?php echo esc_attr( $v ); ?>"
								<?php selected( $s['overlay_position'], $v ); ?>>
								<?php echo esc_html( $l ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<!-- Live Preview -->
				<div class="lo-preview-box">
					<p class="lo-preview-label"><?php esc_html_e( 'Live Preview', 'launchoverlay' ); ?></p>
					<div class="lo-preview-scene">
						<div class="lo-preview-img-wrap">
							<div class="lo-preview-placeholder">
								<svg width="40" height="40" viewBox="0 0 40 40" fill="none"
									xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
									<rect width="40" height="40" rx="4" fill="#e9ecef"/>
									<path d="M8 28 L14 18 L20 23 L26 14 L32 28 Z" fill="#ced4da"/>
									<circle cx="13" cy="13" r="4" fill="#ced4da"/>
								</svg>
							</div>
							<span class="lo-overlay lo-type-coming_soon lo-pos-top-left lo-style-banner"
								id="lo-preview-badge" aria-hidden="true">
								<span class="lo-text">
									<?php esc_html_e( 'Coming Soon', 'launchoverlay' ); ?>
								</span>
							</span>
						</div>
					</div>
				</div>

			</div>
		</div>
		<?php
	}

	private function card_display() {
		$s = $this->cfg->all();
		?>
		<div class="lo-card">
			<div class="lo-card-head">
				<span class="lo-card-icon" aria-hidden="true">&#128065;</span>
				<h2><?php esc_html_e( 'Display', 'launchoverlay' ); ?></h2>
			</div>
			<div class="lo-card-body">

				<div class="lo-toggle-field">
					<div class="lo-toggle-field-info">
						<strong><?php esc_html_e( 'Shop & Category Pages', 'launchoverlay' ); ?></strong>
						<span><?php esc_html_e( 'Show banners on product loop pages', 'launchoverlay' ); ?></span>
					</div>
					<label class="lo-switch">
						<input type="checkbox" name="show_on_shop" value="yes"
							<?php checked( $s['show_on_shop'], 'yes' ); ?> />
						<span class="lo-switch-track"><span class="lo-switch-thumb"></span></span>
					</label>
				</div>

				<div class="lo-toggle-field">
					<div class="lo-toggle-field-info">
						<strong><?php esc_html_e( 'Single Product Page', 'launchoverlay' ); ?></strong>
						<span><?php esc_html_e( 'Show banner on the product detail page', 'launchoverlay' ); ?></span>
					</div>
					<label class="lo-switch">
						<input type="checkbox" name="show_on_product" value="yes"
							<?php checked( $s['show_on_product'], 'yes' ); ?> />
						<span class="lo-switch-track"><span class="lo-switch-thumb"></span></span>
					</label>
				</div>

			</div>
		</div>
		<?php
	}

	private function card_quickstart() {
		?>
		<div class="lo-card lo-card--guide">
			<div class="lo-card-head">
				<span class="lo-card-icon" aria-hidden="true">&#9889;</span>
				<h2><?php esc_html_e( 'Quick Start', 'launchoverlay' ); ?></h2>
			</div>
			<div class="lo-card-body">
				<ol class="lo-steps">
					<li>
						<span class="lo-step-num">1</span>
						<?php esc_html_e( 'Edit any WooCommerce product.', 'launchoverlay' ); ?>
					</li>
					<li>
						<span class="lo-step-num">2</span>
						<?php esc_html_e( 'Click the LaunchOverlay tab in the Product Data panel.', 'launchoverlay' ); ?>
					</li>
					<li>
						<span class="lo-step-num">3</span>
						<?php esc_html_e( 'Enable the overlay and choose a banner text.', 'launchoverlay' ); ?>
					</li>
					<li>
						<span class="lo-step-num">4</span>
						<?php esc_html_e( 'Optionally disable Add to Cart or hide the price.', 'launchoverlay' ); ?>
					</li>
					<li>
						<span class="lo-step-num">5</span>
						<?php esc_html_e( 'Save the product — the banner appears instantly.', 'launchoverlay' ); ?>
					</li>
				</ol>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=product' ) ); ?>"
				   class="lo-btn-secondary">
					<?php esc_html_e( 'View Products', 'launchoverlay' ); ?> &rarr;
				</a>
			</div>
		</div>
		<?php
	}
}
