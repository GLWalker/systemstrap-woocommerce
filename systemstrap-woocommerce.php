<?php
/**
 * Plugin Name: SystemStrap WooCommerce
 * Description: Lightweight WooCommerce component styling for the SystemStrap theme.
 * Version: 1.5.0
 * Author: SystemStrap
 * Text Domain: systemstrap-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load companion behavior only when WooCommerce is active.
 */
function strap_woocommerce_init() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	require_once plugin_dir_path( __FILE__ ) . 'inc/component-registry.php';
	require_once plugin_dir_path( __FILE__ ) . 'inc/assets.php';
}
add_action( 'plugins_loaded', 'strap_woocommerce_init', 20 );
