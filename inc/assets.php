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
		array( 'wp-hooks', 'wp-compose', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n' ),
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
			'blocks'                 => strap_woocommerce_editor_compatibility_registry(),
			'applicationPanelBlocks' => strap_woocommerce_get_selected_application_panel_blocks(),
		)
	);

	wp_enqueue_script( 'strap-woocommerce-editor-compatibility' );
}
add_action( 'enqueue_block_editor_assets', 'strap_woocommerce_enqueue_editor_compatibility', 0 );

/**
 * Return locked Woo application blocks that currently consume System Panel.
 *
 * @return array<string, string>
 */
function strap_woocommerce_get_selected_application_panel_blocks() {
	$blocks = array();

	foreach ( strap_woocommerce_component_registry() as $component_id => $component ) {
		if ( 'admin_mapping' !== ( $component['application'] ?? '' ) || empty( $component['block_name'] ) || 'system-panel-woo' !== strap_woocommerce_get_component_treatment( $component_id ) ) {
			continue;
		}

		if ( empty( $component['treatments']['system-panel-woo']['theme_style_handle'] ) ) {
			continue;
		}

		$blocks[ $component['block_name'] ] = 'checkout_totals' === $component_id ? 'surface' : 'application';
	}

	return $blocks;
}

/**
 * Load the existing System Panel master for selected locked application
 * surfaces in the editor. The client adapter only supplies its public class.
 */
function strap_woocommerce_enqueue_editor_application_panel_styles() {
	if ( empty( strap_woocommerce_get_selected_application_panel_blocks() ) ) {
		return;
	}

	if ( wp_style_is( 'strap-panel-surface', 'registered' ) ) {
		wp_enqueue_style( 'strap-panel-surface' );
	}

	if ( wp_style_is( 'strap-woocommerce-woocommerce-application-panel-composition', 'registered' ) ) {
		wp_enqueue_style( 'strap-woocommerce-woocommerce-application-panel-composition' );
	}
}
add_action( 'enqueue_block_assets', 'strap_woocommerce_enqueue_editor_application_panel_styles', 11 );

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
 * Register Review Template styles against the existing SystemStrap Comments
 * masters. The companion supplies only the Woo block registration; the theme
 * remains the visual authority.
 */
function strap_woocommerce_register_product_review_template_block_styles() {
	$theme_dir = get_template_directory() . '/';
	$theme_uri = get_template_directory_uri() . '/';
	$styles    = array(
		'system-list'  => array(
			'label' => __( 'System List', 'systemstrap-woocommerce' ),
			'file'  => 'core-comments-system-list.css',
		),
		'system-panel' => array(
			'label' => __( 'System Panel', 'systemstrap-woocommerce' ),
			'file'  => 'core-comments-system-panel.css',
		),
	);

	foreach ( $styles as $name => $style ) {
		$file = $theme_dir . 'assets/css/style-variations/' . $style['file'];

		if ( ! file_exists( $file ) ) {
			continue;
		}

		$handle = 'strap-woocommerce-product-review-template-' . $name;

		wp_enqueue_block_style(
			'woocommerce/product-review-template',
			array(
				'handle' => $handle,
				'src'    => $theme_uri . 'assets/css/style-variations/' . $style['file'],
				'path'   => $file,
				'deps'   => array( 'strap-woocommerce-variation-anchor' ),
			)
		);

		register_block_style(
			'woocommerce/product-review-template',
			array(
				'name'         => $name,
				'label'        => $style['label'],
				'style_handle' => $handle,
			)
		);
	}
}
add_action( 'init', 'strap_woocommerce_register_product_review_template_block_styles', 21 );

/**
 * Register the shared Reviews List and Panel choices for Woo's standalone
 * archive-review blocks. They consume the same Comments master assets as the
 * Product Review Template; no archive-specific visual stylesheet is created.
 */
function strap_woocommerce_register_archive_review_block_styles() {
	$theme_dir = get_template_directory() . '/';
	$theme_uri = get_template_directory_uri() . '/';
	$blocks    = array(
		'woocommerce/all-reviews',
		'woocommerce/reviews-by-product',
		'woocommerce/reviews-by-category',
	);
	$styles    = array(
		'system-list'  => array(
			'label' => __( 'System List', 'systemstrap-woocommerce' ),
			'file'  => 'core-comments-system-list.css',
		),
		'system-panel' => array(
			'label' => __( 'System Panel', 'systemstrap-woocommerce' ),
			'file'  => 'core-comments-system-panel.css',
		),
	);

	foreach ( $styles as $name => $style ) {
		$file = $theme_dir . 'assets/css/style-variations/' . $style['file'];

		if ( ! file_exists( $file ) ) {
			continue;
		}

		$handle = 'strap-woocommerce-archive-reviews-' . $name;

		foreach ( $blocks as $block_name ) {
			wp_enqueue_block_style(
				$block_name,
				array(
					'handle' => $handle,
					'src'    => $theme_uri . 'assets/css/style-variations/' . $style['file'],
					'path'   => $file,
					'deps'   => array( 'strap-woocommerce-variation-anchor' ),
				)
			);

			register_block_style(
				$block_name,
				array(
					'name'         => $name,
					'label'        => $style['label'],
					'style_handle' => $handle,
				)
			);
		}
	}
}
add_action( 'init', 'strap_woocommerce_register_archive_review_block_styles', 22 );

/**
 * Register SystemStrap Pagination styles for Woo's modern Reviews Pagination
 * block and its public child controls. Theme markers load the shared master;
 * the companion does not introduce a second pagination stylesheet.
 */
function strap_woocommerce_register_product_reviews_pagination_block_styles() {
	$theme_dir = get_template_directory() . '/';
	$theme_uri = get_template_directory_uri() . '/';

	if ( ! wp_style_is( 'strap-system-ui-pagination', 'registered' ) ) {
		return;
	}

	$styles = array(
		'system-ui-pagination'                => __( 'System UI Pagination', 'systemstrap-woocommerce' ),
		'system-ui-pagination-outline'        => __( 'System UI Pagination Outline', 'systemstrap-woocommerce' ),
		'system-ui-pagination-pill'           => __( 'System UI Pagination Pill', 'systemstrap-woocommerce' ),
		'system-ui-pagination-pill-outline'   => __( 'System UI Pagination Pill Outline', 'systemstrap-woocommerce' ),
		'system-ui-pagination-square'         => __( 'System UI Pagination Square', 'systemstrap-woocommerce' ),
		'system-ui-pagination-square-outline' => __( 'System UI Pagination Square Outline', 'systemstrap-woocommerce' ),
		'system-ui-pagination-badge'          => __( 'System UI Pagination Badge', 'systemstrap-woocommerce' ),
	);
	$blocks = array(
		'woocommerce/product-reviews-pagination',
		'woocommerce/product-reviews-pagination-previous',
		'woocommerce/product-reviews-pagination-numbers',
		'woocommerce/product-reviews-pagination-next',
	);

	foreach ( $styles as $name => $label ) {
		$marker = $theme_dir . 'assets/css/style-variations/core-comments-pagination-' . $name . '.css';

		if ( ! file_exists( $marker ) ) {
			continue;
		}

		$handle = 'strap-woocommerce-product-reviews-pagination-' . $name;

		foreach ( $blocks as $block_name ) {
			wp_enqueue_block_style(
				$block_name,
				array(
					'handle' => $handle,
					'src'    => $theme_uri . 'assets/css/style-variations/' . basename( $marker ),
					'path'   => $marker,
					'deps'   => array( 'strap-system-ui-pagination' ),
				)
			);

			register_block_style(
				$block_name,
				array(
					'name'         => $name,
					'label'        => $label,
					'style_handle' => $handle,
				)
			);
		}
	}
}
add_action( 'init', 'strap_woocommerce_register_product_reviews_pagination_block_styles', 23 );

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
			$dependencies = array( 'strap-woocommerce-blocks', 'strap-woocommerce-variation-anchor' );

			if ( 'woocommerce-tables-system-panel.css' === $stylesheet && wp_style_is( 'strap-table-surface', 'registered' ) ) {
				$dependencies[] = 'strap-table-surface';
			}

			if ( 'woocommerce-application-panel-composition.css' === $stylesheet && wp_style_is( 'strap-panel-surface', 'registered' ) ) {
				$dependencies[] = 'strap-panel-surface';
			}

			if ( 'woocommerce-addresses-system-panel.css' === $stylesheet && wp_style_is( 'strap-panel-surface', 'registered' ) ) {
				$dependencies[] = 'strap-panel-surface';
			}

			wp_register_style(
				$handle,
				$plugin_url . 'assets/css/style-variations/' . $stylesheet,
				$dependencies,
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

		if ( 'native' === $treatment_name ) {
			continue;
		}

		if ( ! empty( $treatment['stylesheet'] ) ) {
			wp_enqueue_style( 'strap-woocommerce-' . sanitize_title( basename( $treatment['stylesheet'], '.css' ) ) );
		}

		if ( ! empty( $treatment['theme_style_handle'] ) && wp_style_is( $treatment['theme_style_handle'], 'registered' ) ) {
			wp_enqueue_style( $treatment['theme_style_handle'] );
		}
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
