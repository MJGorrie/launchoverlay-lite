<?php
/**
 * LO_Settings — plugin-wide settings manager.
 *
 * @package LaunchOverlay
 */

defined( 'ABSPATH' ) || exit;

class LO_Settings {

	private static $inst = null;

	private $defaults = array(
		'overlay_color_coming_soon' => '#3b5bdb',
		'overlay_color_pre_order'   => '#f76707',
		'overlay_color_sold_out'    => '#343a40',
		'overlay_color_text'        => '#ffffff',
		'overlay_position'          => 'top-left',
		'overlay_style'             => 'banner',
		'show_on_shop'              => 'yes',
		'show_on_product'           => 'yes',
	);

	public static function instance() {
		if ( null === self::$inst ) {
			self::$inst = new self();
		}
		return self::$inst;
	}

	private function __construct() {}

	/** @return array All settings merged with defaults. */
	public function all() {
		return wp_parse_args( get_option( 'launchoverlay_settings', array() ), $this->defaults );
	}

	/**
	 * Get one setting value.
	 *
	 * @param string $key      Setting key.
	 * @param mixed  $fallback Value returned when key absent.
	 * @return mixed
	 */
	public function get( $key, $fallback = null ) {
		$all = $this->all();
		if ( array_key_exists( $key, $all ) ) {
			return $all[ $key ];
		}
		if ( null !== $fallback ) {
			return $fallback;
		}
		return isset( $this->defaults[ $key ] ) ? $this->defaults[ $key ] : null;
	}

	/**
	 * Sanitise and persist settings from a POST array.
	 *
	 * @param array $data Raw $_POST data.
	 */
	public function save( array $data ) {
		$clean = array();
		foreach ( array_keys( $this->defaults ) as $k ) {
			if ( 'show_on_shop' === $k || 'show_on_product' === $k ) {
				$clean[ $k ] = isset( $data[ $k ] ) ? 'yes' : 'no';
			} elseif ( isset( $data[ $k ] ) ) {
				$clean[ $k ] = sanitize_text_field( wp_unslash( $data[ $k ] ) );
			}
		}
		update_option( 'launchoverlay_settings', array_merge( $this->all(), $clean ) );
	}

	// ── Static label helpers ──────────────────────────────────────────────────

	/**
	 * Available overlay types.
	 *
	 * @return array  key => translated label
	 */
	public static function overlay_types() {
		return array(
			'coming_soon'    => __( 'Coming Soon',    'launchoverlay' ),
			'pre_order'      => __( 'Pre-Order',      'launchoverlay' ),
			'launching_soon' => __( 'Launching Soon', 'launchoverlay' ),
			'sold_out'       => __( 'Sold Out',       'launchoverlay' ),
			'available_soon' => __( 'Available Soon', 'launchoverlay' ),
		);
	}

	/**
	 * Available banner positions.
	 *
	 * @return array
	 */
	public static function positions() {
		return array(
			'top-left'      => __( 'Top Left',      'launchoverlay' ),
			'top-center'    => __( 'Top Centre',    'launchoverlay' ),
			'top-right'     => __( 'Top Right',     'launchoverlay' ),
			'center'        => __( 'Centre',        'launchoverlay' ),
			'bottom-left'   => __( 'Bottom Left',   'launchoverlay' ),
			'bottom-center' => __( 'Bottom Centre', 'launchoverlay' ),
			'bottom-right'  => __( 'Bottom Right',  'launchoverlay' ),
		);
	}

	/**
	 * Available display styles.
	 *
	 * @return array
	 */
	public static function styles() {
		return array(
			'banner' => __( 'Banner',     'launchoverlay' ),
			'badge'  => __( 'Badge/Pill', 'launchoverlay' ),
		);
	}

	/**
	 * Available colour themes.
	 *
	 * @return array  key => array( bg, text )
	 */
	public static function colour_themes() {
		return array(
			'dark'  => array( 'bg' => '#343a40', 'text' => '#ffffff', 'label' => __( 'Dark',  'launchoverlay' ) ),
			'light' => array( 'bg' => '#f8f9fa', 'text' => '#212529', 'label' => __( 'Light', 'launchoverlay' ) ),
			'blue'  => array( 'bg' => '#3b5bdb', 'text' => '#ffffff', 'label' => __( 'Blue',  'launchoverlay' ) ),
			'green' => array( 'bg' => '#2f9e44', 'text' => '#ffffff', 'label' => __( 'Green', 'launchoverlay' ) ),
			'amber' => array( 'bg' => '#f76707', 'text' => '#ffffff', 'label' => __( 'Amber', 'launchoverlay' ) ),
			'red'   => array( 'bg' => '#c92a2a', 'text' => '#ffffff', 'label' => __( 'Red',   'launchoverlay' ) ),
		);
	}
}
