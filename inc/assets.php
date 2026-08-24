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
	$version = '1.5.1';

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

	wp_register_script(
		'strap-woocommerce-editor-compatibility',
		plugin_dir_url( __DIR__ ) . 'assets/js/editor-compatibility.js',
		array( 'wp-hooks' ),
		$version,
		true
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
 * Mirror SystemStrap's palette contrast route for optional Woo outline styles.
 *
 * Core button outline aliases cannot match the companion's distinct -woo
 * style classes, so only their public Product Button child receives this
 * palette-scoped compatibility rule.
 */
function strap_woocommerce_product_button_outline_styles() {
	$settings = wp_get_global_settings();
	$colors   = $settings['color']['palette']['theme'] ?? array();
	$css      = '';

	foreach ( $colors as $color ) {
		$slug = sanitize_title( $color['slug'] ?? '' );

		if ( ! $slug ) {
			continue;
		}

		$root = ':is(.wp-block-button.wc-block-components-product-button.is-style-button-pill-outline-woo, .wp-block-button.wc-block-components-product-button.is-style-button-square-outline-woo)';
		$child = '> .wp-block-button__link.wp-element-button.wc-block-components-product-button__button';
		$selector = $root . ' ' . $child . ':is(.has-' . $slug . '-color, .has-' . $slug . '-background-color)';

		$css .= $selector . " {\n";
		$css .= "\tbackground-color: transparent !important;\n";
		$css .= "\tcolor: var(--wp--preset--color--{$slug}) !important;\n";
		$css .= "\tborder-color: var(--wp--preset--color--{$slug}) !important;\n";
		$css .= "\t--local-btn-shadow-rgb: var(--wp--preset--color--{$slug}-shadow-rgb);\n";
		$css .= "}\n";
		$css .= $selector . ':not(:disabled):focus' . " {\n";
		$css .= "\tbox-shadow: 0 0 0 .25rem rgba(var(--wp--preset--color--{$slug}-rgb), 0.5);\n";
		$css .= "}\n";
	}

	return $css;
}

/**
 * Append selected Product Button outline compatibility to the shared anchor.
 */
function strap_woocommerce_add_product_button_outline_styles() {
	$css = strap_woocommerce_product_button_outline_styles();

	if ( $css ) {
		wp_add_inline_style( 'strap-woocommerce-blocks', $css );
	}
}
add_action( 'wp_enqueue_scripts', 'strap_woocommerce_add_product_button_outline_styles', 21 );
add_action( 'enqueue_block_editor_assets', 'strap_woocommerce_add_product_button_outline_styles', 2 );

/**
 * Load editor-only capability normalizations before Woo block registration.
 */
function strap_woocommerce_enqueue_editor_compatibility() {
	wp_localize_script(
		'strap-woocommerce-editor-compatibility',
		'strapWooEditorCompatibility',
		array(
			'blocks' => strap_woocommerce_editor_compatibility_registry(),
		)
	);

	wp_enqueue_script( 'strap-woocommerce-editor-compatibility' );
}
add_action( 'enqueue_block_editor_assets', 'strap_woocommerce_enqueue_editor_compatibility', 0 );

/**
 * Register filesystem-backed Woo block variations declared in the registry.
 */
function strap_woocommerce_register_block_styles() {
	$plugin_dir = plugin_dir_path( __DIR__ );
	$plugin_url = plugin_dir_url( __DIR__ );
	$seen       = array();
	$handles    = array();

	foreach ( strap_woocommerce_component_registry() as $component ) {
		if ( empty( $component['block_name'] ) || empty( $component['treatments'] ) || 'admin_mapping' === ( $component['application'] ?? '' ) ) {
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

			foreach ( $treatment['child_blocks'] ?? array() as $child_block_name ) {
				wp_enqueue_block_style(
					$child_block_name,
					array(
						'handle' => $handle,
						'src'    => $plugin_url . 'assets/css/style-variations/' . $treatment['stylesheet'],
						'path'   => $file,
						'deps'   => array( 'strap-woocommerce-blocks', 'strap-woocommerce-variation-anchor' ),
					)
				);

				register_block_style(
					$child_block_name,
					array(
						'name'         => $slug,
						'label'        => $treatment['label'],
						'style_handle' => $handle,
					)
				);
			}

			$seen[ $component['block_name'] ][ $slug ] = true;
		}
	}
}
add_action( 'init', 'strap_woocommerce_register_block_styles', 20 );

/**
 * Register selected component presentation assets that do not have a usable
 * block Styles UI and therefore resolve through the companion mapping option.
 */
function strap_woocommerce_register_mapped_presentation_styles() {
	$plugin_url = plugin_dir_url( __DIR__ );
	$version    = '1.5.1';
	$seen       = array();

	foreach ( strap_woocommerce_component_registry() as $component ) {
		if ( 'admin_mapping' !== ( $component['application'] ?? '' ) ) {
			continue;
		}

		foreach ( $component['treatments'] as $treatment ) {
			if ( empty( $treatment['stylesheet'] ) || isset( $seen[ $treatment['stylesheet'] ] ) ) {
				continue;
			}

			$stylesheet = $treatment['stylesheet'];
			$handle     = 'strap-woocommerce-' . sanitize_title( basename( $stylesheet, '.css' ) );

			wp_register_style(
				$handle,
				$plugin_url . 'assets/css/style-variations/' . $stylesheet,
				array( 'strap-woocommerce-blocks', 'strap-woocommerce-variation-anchor' ),
				$version
			);

			$seen[ $stylesheet ] = true;
		}
	}
}
add_action( 'init', 'strap_woocommerce_register_mapped_presentation_styles', 21 );

/**
 * Enqueue only the admin-selected presentation assets for non-style-capable
 * public Woo components. Native is terminal and enqueues nothing here.
 */
function strap_woocommerce_enqueue_mapped_presentation_styles() {
	foreach ( strap_woocommerce_component_registry() as $component_id => $component ) {
		if ( 'admin_mapping' !== ( $component['application'] ?? '' ) ) {
			continue;
		}

		$treatment_name = strap_woocommerce_get_component_treatment( $component_id );
		$treatment      = $component['treatments'][ $treatment_name ] ?? array();

		if ( 'native' === $treatment_name || empty( $treatment['stylesheet'] ) ) {
			continue;
		}

		wp_enqueue_style( 'strap-woocommerce-' . sanitize_title( basename( $treatment['stylesheet'], '.css' ) ) );
	}
}
add_action( 'wp_enqueue_scripts', 'strap_woocommerce_enqueue_mapped_presentation_styles', 21 );

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
