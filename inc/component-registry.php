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
			'sibling'     => 'core/page-list',
			'default'     => 'native',
			'application' => 'admin_mapping',
			'treatments'  => array(
				'native'                => array( 'label' => 'Native WooCommerce' ),
				'system-list-woo'       => array( 'label' => 'System List Woo', 'class' => 'is-style-system-list-woo', 'stylesheet' => 'woocommerce-my-account-mapped-woo.css', 'presentation_depth' => 2, 'child_targets' => array( '> nav > ul > li' ) ),
				'system-list-flush-woo' => array( 'label' => 'System List Flush Woo', 'class' => 'is-style-system-list-flush-woo', 'stylesheet' => 'woocommerce-my-account-mapped-woo.css', 'presentation_depth' => 2, 'child_targets' => array( '> nav > ul > li' ) ),
			),
		),
		'woo_tables' => array(
			'label'       => 'Woo Tables',
			'sibling'     => 'core/table',
			'default'     => 'native',
			'application' => 'admin_mapping',
			'treatments'  => array(
				'native'           => array( 'label' => 'Native WooCommerce' ),
				'system-panel-woo' => array(
					'label'              => 'System Panel',
					'class'              => 'strap-table-surface',
					'stylesheet'         => 'woocommerce-tables-system-panel.css',
					'presentation_depth' => 1,
					'child_targets'      => array( '> table' ),
				),
			),
		),
		'woo_addresses' => array(
			'label'       => 'Woo Addresses',
			'default'     => 'native',
			'application' => 'admin_mapping',
			'treatments'  => array(
				'native'           => array( 'label' => 'Native WooCommerce' ),
				'system-panel-woo' => array(
					'label'              => 'System Panel',
					'class'              => 'strap-panel-surface',
					'stylesheet'         => 'woocommerce-addresses-system-panel.css',
					'theme_style_handle' => 'strap-panel-surface',
					'presentation_depth' => 1,
				),
			),
		),
		'cart_items' => array(
			'label'       => 'Cart Items',
			'block_name'  => 'woocommerce/cart-items-block',
			'default'     => 'native',
			'application' => 'admin_mapping',
			'treatments'  => array(
				'native'           => array( 'label' => 'Native WooCommerce' ),
				'system-panel-woo' => array(
					'label'              => 'System Panel',
					'class'              => 'strap-panel-surface',
					'stylesheet'         => 'woocommerce-application-panel-composition.css',
					'theme_style_handle' => 'strap-panel-surface',
				),
			),
		),
		'cart_totals' => array(
			'label'       => 'Cart Totals',
			'block_name'  => 'woocommerce/cart-totals-block',
			'default'     => 'native',
			'application' => 'admin_mapping',
			'treatments'  => array(
				'native'           => array( 'label' => 'Native WooCommerce' ),
				'system-panel-woo' => array(
					'label'              => 'System Panel',
					'class'              => 'strap-panel-surface',
					'stylesheet'         => 'woocommerce-application-panel-composition.css',
					'theme_style_handle' => 'strap-panel-surface',
				),
			),
		),
		'checkout_fields' => array(
			'label'       => 'Checkout Fields',
			'block_name'  => 'woocommerce/checkout-fields-block',
			'default'     => 'native',
			'application' => 'admin_mapping',
			'treatments'  => array(
				'native'           => array( 'label' => 'Native WooCommerce' ),
				'system-panel-woo' => array(
					'label'              => 'System Panel',
					'class'              => 'strap-panel-surface',
					'stylesheet'         => 'woocommerce-application-panel-composition.css',
					'theme_style_handle' => 'strap-panel-surface',
				),
			),
		),
		'checkout_totals' => array(
			'label'       => 'Checkout Totals',
			'block_name'  => 'woocommerce/checkout-order-summary-block',
			'default'     => 'native',
			'application' => 'admin_mapping',
			'treatments'  => array(
				'native'           => array( 'label' => 'Native WooCommerce' ),
				'system-panel-woo' => array(
					'label'              => 'System Panel',
					'class'              => 'strap-panel-surface',
					'stylesheet'         => 'woocommerce-application-panel-composition.css',
					'theme_style_handle' => 'strap-panel-surface',
				),
			),
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
