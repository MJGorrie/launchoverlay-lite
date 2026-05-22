<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Launch_Overlay_Assets {

    public static function init() {
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'frontend_assets' ) );
    }

    public static function admin_assets() {}

    public static function frontend_assets() {}
}

Launch_Overlay_Assets::init();
