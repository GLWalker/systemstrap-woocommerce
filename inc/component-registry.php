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
			'application' => 'authored_block_class',
			'treatments'  => array(
				'native'              => array( 'label' => 'Native WooCommerce' ),
				'system-comments-woo' => array( 'label' => 'System Comments Woo', 'class' => 'is-style-system-comments-woo', 'stylesheet' => 'woocommerce-product-reviews-system-comments-woo.css' ),
			),
		),
		'reviews_pagination' => array(
			'label'       => 'Reviews Pagination',
			'block_name'  => 'woocommerce/product-reviews-pagination',
			'sibling'     => 'core/comments-pagination',
			'default'     => 'native',
			'application' => 'authored_block_class',
			'treatments'  => array(
				'native'                    => array( 'label' => 'Native WooCommerce' ),
				'system-ui-pagination-woo' => array( 'label' => 'System UI Pagination Woo', 'class' => 'is-style-system-ui-pagination-woo', 'stylesheet' => 'woocommerce-product-reviews-pagination-system-ui-pagination-woo.css' ),
			),
		),
		'product_button' => array(
			'label'       => 'Product Button',
			'block_name'  => 'woocommerce/product-button',
			'sibling'     => 'core/button',
			'default'     => 'native',
			'application' => 'authored_block_class',
			'treatments'  => array(
				'native' => array( 'label' => 'Native WooCommerce' ),
			),
		),
		'account_navigation' => array(
			'label'       => 'Account Navigation',
			'sibling'     => 'core/navigation',
			'default'     => 'native',
			'application' => 'bridge_css',
			'treatments'  => array( 'native' => array( 'label' => 'Native WooCommerce' ), 'system-list-woo' => array( 'label' => 'System List Woo' ), 'system-list-flush-woo' => array( 'label' => 'System List Flush Woo' ) ),
		),
		'orders_table' => array(
			'label'       => 'Orders Table',
			'sibling'     => 'core/table',
			'default'     => 'native',
			'application' => 'bridge_css',
			'treatments'  => array( 'native' => array( 'label' => 'Native WooCommerce' ), 'system-panel-woo' => array( 'label' => 'System Panel Woo' ) ),
		),
		'account_form_controls' => array(
			'label'       => 'Account Form Controls',
			'default'     => 'native',
			'application' => 'bridge_css',
			'treatments'  => array( 'native' => array( 'label' => 'Native WooCommerce' ), 'system-form-controls-woo' => array( 'label' => 'System Form Controls Woo' ) ),
		),
		'notices' => array(
			'label'       => 'Notices',
			'default'     => 'native',
			'application' => 'bridge_css',
			'treatments'  => array( 'native' => array( 'label' => 'Native WooCommerce' ), 'system-panel-woo' => array( 'label' => 'System Panel Woo' ) ),
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
