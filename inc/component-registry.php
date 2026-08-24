<?php
/**
 * WooCommerce component treatment registry for SystemStrap.
 *
 * @package systemstrap-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the stable Woo component mappings supported by this companion.
 *
 * @return array<string, array<string, mixed>>
 */
function strap_woocommerce_component_registry() {
	return array(
		'product_cards' => array(
			'label'       => 'Product Cards',
			'block_name'  => 'woocommerce/product-template',
			'sibling'     => 'core/post-template',
			'default'     => 'native',
			'application' => 'authored_block_class',
			'treatments'  => array(
				'native'           => array( 'label' => 'Native WooCommerce' ),
				'system-panel-woo' => array(
					'label'              => 'System Panel',
					'class'              => 'is-style-system-panel-woo',
					'stylesheet'         => 'woocommerce-product-template-system-panel-woo.css',
					'presentation_depth' => 2,
					'child_targets'      => array( '> li.wc-block-product' ),
					'propagated_styles'  => array( 'background', 'background_image', 'color' ),
				),
				'system-list-woo'  => array(
					'label'              => 'System List',
					'class'              => 'is-style-system-list-woo',
					'stylesheet'         => 'woocommerce-product-template-system-list-woo.css',
					'presentation_depth' => 2,
					'child_targets'      => array( '> li.wc-block-product' ),
				),
				'system-list-flush-woo' => array(
					'label'              => 'System List Flush',
					'class'              => 'is-style-system-list-flush-woo',
					'stylesheet'         => 'woocommerce-product-template-system-list-woo.css',
					'presentation_depth' => 2,
					'child_targets'      => array( '> li.wc-block-product' ),
				),
			),
		),
		'product_images' => array(
			'label'       => 'Product Images',
			'block_name'  => 'woocommerce/product-image',
			'sibling'     => 'core/image',
			'default'     => 'native',
			'application' => 'authored_block_class',
			'treatments'  => array(
				'native' => array( 'label' => 'Native WooCommerce' ),
			),
		),
		'reviews' => array(
			'label'       => 'Reviews',
			'block_name'  => 'woocommerce/product-reviews',
			'sibling'     => 'core/comments',
			'default'     => 'native',
			'application' => 'admin_mapping',
			'treatments'  => array(
				'native'                => array( 'label' => 'Native WooCommerce' ),
				'system-list-woo'       => array(
					'label'              => 'System List Woo',
					'class'              => 'is-style-system-list-woo',
					'stylesheet'         => 'woocommerce-product-reviews-system-list-woo.css',
					'presentation_depth' => 2,
					'child_targets'      => array( '> .wp-block-woocommerce-product-review-template > li', '#reviews #comments > .commentlist > li' ),
				),
				'system-list-flush-woo' => array(
					'label'              => 'System List Flush Woo',
					'class'              => 'is-style-system-list-flush-woo',
					'stylesheet'         => 'woocommerce-product-reviews-system-list-woo.css',
					'presentation_depth' => 2,
					'child_targets'      => array( '> .wp-block-woocommerce-product-review-template > li', '#reviews #comments > .commentlist > li' ),
				),
				'system-panel-woo'    => array(
					'label'              => 'System Panel Woo',
					'class'              => 'is-style-system-panel-woo',
					'stylesheet'         => 'woocommerce-product-reviews-system-panel-woo.css',
					'presentation_depth' => 2,
					'child_targets'      => array( '> .wp-block-woocommerce-product-review-template > li', '#reviews #comments > .commentlist > li' ),
					'propagated_styles'  => array( 'background', 'background_image', 'color' ),
				),
			),
		),
		'reviews_pagination' => array(
			'label'       => 'Reviews Pagination',
			'block_name'  => 'woocommerce/product-reviews-pagination',
			'sibling'     => 'core/comments-pagination',
			'default'     => 'native',
			'application' => 'admin_mapping',
			'treatments'  => array(
				'native' => array( 'label' => 'Native WooCommerce' ),
				'system-ui-pagination-woo' => array(
					'label'        => 'System UI Pagination Woo',
					'class'        => 'is-style-system-ui-pagination-woo',
					'stylesheet'   => 'woocommerce-product-reviews-pagination-system-ui-pagination-woo.css',
				),
				'system-ui-pagination-outline-woo' => array(
					'label'        => 'System UI Pagination Outline Woo',
					'class'        => 'is-style-system-ui-pagination-outline-woo',
					'stylesheet'   => 'woocommerce-product-reviews-pagination-system-ui-pagination-woo.css',
				),
				'system-ui-pagination-pill-woo' => array(
					'label'        => 'System UI Pagination Pill Woo',
					'class'        => 'is-style-system-ui-pagination-pill-woo',
					'stylesheet'   => 'woocommerce-product-reviews-pagination-system-ui-pagination-woo.css',
				),
				'system-ui-pagination-pill-outline-woo' => array(
					'label'        => 'System UI Pagination Pill Outline Woo',
					'class'        => 'is-style-system-ui-pagination-pill-outline-woo',
					'stylesheet'   => 'woocommerce-product-reviews-pagination-system-ui-pagination-woo.css',
				),
				'system-ui-pagination-square-woo' => array(
					'label'        => 'System UI Pagination Square Woo',
					'class'        => 'is-style-system-ui-pagination-square-woo',
					'stylesheet'   => 'woocommerce-product-reviews-pagination-system-ui-pagination-woo.css',
				),
				'system-ui-pagination-square-outline-woo' => array(
					'label'        => 'System UI Pagination Square Outline Woo',
					'class'        => 'is-style-system-ui-pagination-square-outline-woo',
					'stylesheet'   => 'woocommerce-product-reviews-pagination-system-ui-pagination-woo.css',
				),
				'system-ui-pagination-badge-woo' => array(
					'label'        => 'System UI Pagination Badge Woo',
					'class'        => 'is-style-system-ui-pagination-badge-woo',
					'stylesheet'   => 'woocommerce-product-reviews-pagination-system-ui-pagination-woo.css',
				),
			),
		),
		'product_button' => array(
			'label'       => 'Product Button',
			'block_name'  => 'woocommerce/product-button',
			'sibling'     => 'core/button',
			'default'     => 'native',
			'application' => 'authored_block_class',
			'treatments'  => array(
				'native' => array(
					'label'              => 'Native WooCommerce',
					'presentation_depth' => 1,
					'child_targets'      => array( '> .wp-block-button__link.wp-element-button.wc-block-components-product-button__button' ),
					'preserve_states'    => array(
						'simple_add_to_cart',
						'external_navigation',
						'variable_navigation',
						'grouped_navigation',
						'loading',
						'added',
						'disabled',
						'focus',
						'view_cart',
					),
				),
				'button-link-woo' => array(
					'label'              => 'Link',
					'class'              => 'is-style-button-link-woo',
					'stylesheet'         => 'woocommerce-product-button-button-link-woo.css',
					'presentation_depth' => 2,
					'child_targets'      => array( '> .wp-block-button__link.wp-element-button.wc-block-components-product-button__button' ),
				),
				'button-pill-woo' => array(
					'label'              => 'Pill',
					'class'              => 'is-style-button-pill-woo',
					'stylesheet'         => 'woocommerce-product-button-button-pill-woo.css',
					'presentation_depth' => 2,
					'child_targets'      => array( '> .wp-block-button__link.wp-element-button.wc-block-components-product-button__button' ),
				),
				'button-pill-outline-woo' => array(
					'label'              => 'Pill Outline',
					'class'              => 'is-style-button-pill-outline-woo',
					'stylesheet'         => 'woocommerce-product-button-button-pill-outline-woo.css',
					'presentation_depth' => 2,
					'child_targets'      => array( '> .wp-block-button__link.wp-element-button.wc-block-components-product-button__button' ),
				),
				'button-square-woo' => array(
					'label'              => 'Square',
					'class'              => 'is-style-button-square-woo',
					'stylesheet'         => 'woocommerce-product-button-button-square-woo.css',
					'presentation_depth' => 2,
					'child_targets'      => array( '> .wp-block-button__link.wp-element-button.wc-block-components-product-button__button' ),
				),
				'button-square-outline-woo' => array(
					'label'              => 'Square Outline',
					'class'              => 'is-style-button-square-outline-woo',
					'stylesheet'         => 'woocommerce-product-button-button-square-outline-woo.css',
					'presentation_depth' => 2,
					'child_targets'      => array( '> .wp-block-button__link.wp-element-button.wc-block-components-product-button__button' ),
				),
			),
		),
		'account_navigation' => array(
			'label'       => 'Account Navigation',
			'sibling'     => 'core/navigation',
			'default'     => 'native',
			'application' => 'admin_mapping',
			'treatments'  => array(
				'native'                => array( 'label' => 'Native WooCommerce' ),
				'system-list-woo'       => array( 'label' => 'System List Woo', 'class' => 'is-style-system-list-woo', 'stylesheet' => 'woocommerce-my-account-mapped-woo.css', 'presentation_depth' => 2, 'child_targets' => array( '> nav > ul > li' ) ),
				'system-list-flush-woo' => array( 'label' => 'System List Flush Woo', 'class' => 'is-style-system-list-flush-woo', 'stylesheet' => 'woocommerce-my-account-mapped-woo.css', 'presentation_depth' => 2, 'child_targets' => array( '> nav > ul > li' ) ),
			),
		),
		'orders_table' => array(
			'label'       => 'Orders Table',
			'sibling'     => 'core/table',
			'default'     => 'native',
			'application' => 'admin_mapping',
			'treatments'  => array(
				'native'                 => array( 'label' => 'Native WooCommerce' ),
				'system-table-panel-woo' => array( 'label' => 'System Table Panel Woo', 'class' => 'is-style-system-table-panel-woo', 'stylesheet' => 'woocommerce-my-account-mapped-woo.css', 'presentation_depth' => 2, 'child_targets' => array( '> table.woocommerce-orders-table' ) ),
			),
		),
		'downloads_table' => array(
			'label'       => 'Downloads',
			'sibling'     => 'core/table',
			'default'     => 'native',
			'application' => 'admin_mapping',
			'treatments'  => array(
				'native'                 => array( 'label' => 'Native WooCommerce' ),
				'system-table-panel-woo' => array( 'label' => 'System Table Panel Woo', 'class' => 'is-style-system-table-panel-woo', 'stylesheet' => 'woocommerce-my-account-mapped-woo.css', 'presentation_depth' => 2, 'child_targets' => array( '> :is(table.woocommerce-MyAccount-downloads, .woocommerce-Downloads)' ) ),
			),
		),
		'account_form_controls' => array(
			'label'       => 'Account Form Controls',
			'default'     => 'native',
			'application' => 'admin_mapping',
			'treatments'  => array(
				'native'                   => array( 'label' => 'Native WooCommerce' ),
				'system-form-controls-woo' => array( 'label' => 'System Forms Woo', 'class' => 'is-style-system-form-controls-woo', 'stylesheet' => 'woocommerce-my-account-mapped-woo.css', 'presentation_depth' => 2, 'child_targets' => array( '> form' ) ),
			),
		),
		'notices' => array(
			'label'       => 'Notices',
			'default'     => 'native',
			'application' => 'admin_mapping',
			'treatments'  => array(
				'native'            => array( 'label' => 'Native WooCommerce' ),
				'system-notice-woo' => array( 'label' => 'System Notice Woo', 'class' => 'is-style-system-notice-woo', 'stylesheet' => 'woocommerce-my-account-mapped-woo.css', 'presentation_depth' => 1, 'child_targets' => array( '> :is(.woocommerce-error, .woocommerce-info, .woocommerce-message)' ) ),
			),
		),
		'cart_checkout' => array(
			'label'       => 'Cart and Checkout Controls',
			'default'     => 'native',
			'application' => 'bridge_css',
			'treatments'  => array( 'native' => array( 'label' => 'WooCommerce Native Structure' ) ),
		),
	);
}

/**
 * Return client-side editor capability normalizations for compatible Woo blocks.
 *
 * @return array<string, array<string, mixed>>
 */
function strap_woocommerce_editor_compatibility_registry() {
	return array(
		'woocommerce/product-button' => array(
			'supports'   => array(
				'color' => array(
					'gradients' => true,
				),
			),
			'controls'   => array( 'gradient' ),
		),
	);
}

/**
 * Resolve a component treatment, allowing a future settings screen to store
 * only registered treatment names.
 *
 * @param string $component_id Component registry identifier.
 * @return string
 */
function strap_woocommerce_get_component_treatment( $component_id ) {
	$registry = strap_woocommerce_component_registry();

	if ( ! isset( $registry[ $component_id ] ) ) {
		return '';
	}

	$stored = get_option( 'strap_woocommerce_component_mappings', array() );
	$value  = is_array( $stored ) && isset( $stored[ $component_id ] ) ? sanitize_key( $stored[ $component_id ] ) : $registry[ $component_id ]['default'];

	return isset( $registry[ $component_id ]['treatments'][ $value ] ) ? $value : $registry[ $component_id ]['default'];
}
