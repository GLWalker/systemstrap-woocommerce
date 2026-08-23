<?php
/**
 * WooCommerce asset and variation support for SystemStrap.
 *
 * @package systemstrap-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the generic companion anchors.
 */
function strap_woocommerce_register_styles() {
	$version = '1.5.0';

	wp_register_style(
		'strap-woocommerce-theme-sync',
		plugin_dir_url( __DIR__ ) . 'assets/css/woocommerce-theme-sync.css',
		array(),
		$version
	);

	wp_register_style(
		'strap-woocommerce-blocks',
		plugin_dir_url( __DIR__ ) . 'assets/css/woocommerce-blocks.css',
		array( 'strap-woocommerce-theme-sync' ),
		$version
	);

	wp_register_style(
		'strap-woocommerce-variation-anchor',
		false,
		array( 'wp-block-library' ),
		$version
	);
}
add_action( 'init', 'strap_woocommerce_register_styles', 5 );

/**
 * Resolve the active Global Styles lane for Woo variation assets.
 *
 * @return string
 */
function strap_woocommerce_get_variation_anchor_dependency() {
	$wp_styles = wp_styles();

	foreach ( array( 'global-styles', 'global-styles-css-custom-properties' ) as $handle ) {
		if ( isset( $wp_styles->registered[ $handle ] ) ) {
			return $handle;
		}
	}

	return 'wp-block-library';
}

/**
 * Point the variation anchor at the active Global Styles lane.
 */
function strap_woocommerce_point_variation_anchor() {
	$wp_styles = wp_styles();

	if ( isset( $wp_styles->registered['strap-woocommerce-variation-anchor'] ) ) {
		$wp_styles->registered['strap-woocommerce-variation-anchor']->deps = array(
			strap_woocommerce_get_variation_anchor_dependency(),
		);
	}
}

/**
 * Load companion anchors for frontend WooCommerce contexts.
 */
function strap_woocommerce_enqueue_styles() {
	if ( is_admin() ) {
		return;
	}

	strap_woocommerce_point_variation_anchor();
	wp_enqueue_style( 'strap-woocommerce-theme-sync' );
	wp_enqueue_style( 'strap-woocommerce-blocks' );
}
add_action( 'wp_enqueue_scripts', 'strap_woocommerce_enqueue_styles', 20 );

/**
 * Load generic anchors in the block editor so registered treatments preview.
 */
function strap_woocommerce_enqueue_editor_styles() {
	strap_woocommerce_point_variation_anchor();
	wp_enqueue_style( 'strap-woocommerce-theme-sync' );
	wp_enqueue_style( 'strap-woocommerce-blocks' );
}
add_action( 'enqueue_block_editor_assets', 'strap_woocommerce_enqueue_editor_styles', 1 );

/**
 * Register filesystem-backed Woo block variations declared in the registry.
 */
function strap_woocommerce_register_block_styles() {
	$plugin_dir = plugin_dir_path( __DIR__ );
	$plugin_url = plugin_dir_url( __DIR__ );
	$seen       = array();
	$handles    = array();

	foreach ( strap_woocommerce_component_registry() as $component ) {
		if ( empty( $component['block_name'] ) || empty( $component['treatments'] ) ) {
			continue;
		}

		foreach ( $component['treatments'] as $slug => $treatment ) {
			if ( empty( $treatment['stylesheet'] ) || isset( $seen[ $component['block_name'] ][ $slug ] ) ) {
				continue;
			}

			$file = $plugin_dir . 'assets/css/style-variations/' . $treatment['stylesheet'];
			if ( ! file_exists( $file ) ) {
				continue;
			}

			$handle = 'strap-woocommerce-' . sanitize_title( basename( $file, '.css' ) );

			if ( ! isset( $handles[ $component['block_name'] ][ $handle ] ) ) {
				wp_enqueue_block_style(
					$component['block_name'],
					array(
						'handle' => $handle,
						'src'    => $plugin_url . 'assets/css/style-variations/' . $treatment['stylesheet'],
						'path'   => $file,
						'deps'   => array( 'strap-woocommerce-blocks', 'strap-woocommerce-variation-anchor' ),
					)
				);

				$handles[ $component['block_name'] ][ $handle ] = true;
			}

			register_block_style(
				$component['block_name'],
				array(
					'name'         => $slug,
					'label'        => $treatment['label'],
					'style_handle' => $handle,
				)
			);

			$seen[ $component['block_name'] ][ $slug ] = true;
		}
	}
}
add_action( 'init', 'strap_woocommerce_register_block_styles', 20 );

/**
 * Add Product Template preset-background propagation after the companion
 * stylesheet has been registered and enqueued in the current context.
 */
function strap_woocommerce_enqueue_product_template_dynamic_styles() {
	$css = strap_woocommerce_get_product_template_dynamic_styles();

	if ( '' !== $css ) {
		wp_add_inline_style( 'strap-woocommerce-blocks', $css );
	}
}
add_action( 'wp_enqueue_scripts', 'strap_woocommerce_enqueue_product_template_dynamic_styles', 25 );
add_action( 'enqueue_block_editor_assets', 'strap_woocommerce_enqueue_product_template_dynamic_styles', 25 );

/**
 * Place Woo generic and variation assets in SystemStrap's established buckets.
 *
 * @param array $order Existing bucket order.
 * @return array
 */
function strap_woocommerce_style_bucket_order( $order ) {
	$new_order = array();

	foreach ( $order as $bucket ) {
		if ( 'core_blocks' === $bucket ) {
			$new_order[] = 'woocommerce_plugin';
			$new_order[] = 'woocommerce_sync';
			$new_order[] = 'woocommerce_blocks';
		}

		if ( 'theme_rest' === $bucket ) {
			$new_order[] = 'woocommerce_variations';
		}

		$new_order[] = $bucket;
	}

	return $new_order;
}
add_filter( 'strap_style_bucket_order', 'strap_woocommerce_style_bucket_order' );

/**
 * Categorize companion and WooCommerce styles for the SystemStrap runtime.
 *
 * @param array     $buckets Existing buckets.
 * @param WP_Styles $wp_styles Styles registry.
 * @return array
 */
function strap_woocommerce_categorize_styles( $buckets, $wp_styles ) {
	$plugin_uri = plugin_dir_url( __DIR__ );

	foreach ( array( 'woocommerce_sync', 'woocommerce_blocks', 'woocommerce_variations', 'woocommerce_plugin' ) as $bucket ) {
		if ( ! isset( $buckets[ $bucket ] ) ) {
			$buckets[ $bucket ] = array();
		}
	}

	foreach ( array( 'remainder', 'theme_rest' ) as $pool ) {
		if ( empty( $buckets[ $pool ] ) ) {
			continue;
		}

		$remaining = array();
		foreach ( $buckets[ $pool ] as $handle ) {
			$src = isset( $wp_styles->registered[ $handle ]->src ) && is_string( $wp_styles->registered[ $handle ]->src ) ? $wp_styles->registered[ $handle ]->src : '';

			if ( 'strap-woocommerce-theme-sync' === $handle ) {
				$buckets['woocommerce_sync'][] = $handle;
				continue;
			}

			if ( 'strap-woocommerce-blocks' === $handle ) {
				$buckets['woocommerce_blocks'][] = $handle;
				continue;
			}

			if ( str_contains( $src, $plugin_uri . 'assets/css/style-variations/' ) ) {
				$buckets['woocommerce_variations'][] = $handle;
				continue;
			}

			if ( str_contains( $src, '/wp-content/plugins/woocommerce/' ) || str_starts_with( $handle, 'wc-' ) || str_starts_with( $handle, 'woocommerce-' ) ) {
				$buckets['woocommerce_plugin'][] = $handle;
				continue;
			}

			$remaining[] = $handle;
		}

		$buckets[ $pool ] = $remaining;
	}

	return $buckets;
}
add_filter( 'strap_style_buckets', 'strap_woocommerce_categorize_styles', 10, 2 );
