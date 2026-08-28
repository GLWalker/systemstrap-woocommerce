<?php
/**
 * Structured WooCommerce block presentation resolution.
 *
 * @package systemstrap-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve the registered component identifier for a Woo block.
 *
 * @param string $block_name Block name.
 * @return string
 */
function strap_woocommerce_get_component_id_for_block( $block_name ) {
	foreach ( strap_woocommerce_component_registry() as $component_id => $component ) {
		if ( isset( $component['block_name'] ) && $block_name === $component['block_name'] ) {
			return $component_id;
		}
	}

	return '';
}

/**
 * Resolve a structured presentation contract for a registered Woo block.
 *
 * @param array  $parsed_block Parsed block data.
 * @param string $context      Rendering context.
 * @return array<string, mixed>
 */
function strap_woocommerce_resolve_block_presentation( $parsed_block, $context ) {
	$block_name   = isset( $parsed_block['blockName'] ) ? $parsed_block['blockName'] : '';
	$component_id = strap_woocommerce_get_component_id_for_block( $block_name );

	if ( '' === $component_id ) {
		return array();
	}

	$registry   = strap_woocommerce_component_registry();
	$component  = $registry[ $component_id ];
	$attributes = isset( $parsed_block['attrs'] ) && is_array( $parsed_block['attrs'] ) ? $parsed_block['attrs'] : array();
	$class_name = isset( $attributes['className'] ) && is_string( $attributes['className'] ) ? $attributes['className'] : '';
	$treatment  = 'native';
	$source     = 'native';

	foreach ( $component['treatments'] as $slug => $definition ) {
		if ( empty( $definition['class'] ) || ! preg_match( '/(?:^|\\s)' . preg_quote( $definition['class'], '/' ) . '(?:\\s|$)/', $class_name ) ) {
			continue;
		}

		$treatment = $slug;
		$source    = 'authored';
		break;
	}

	if ( 'native' === $treatment ) {
		$mapped_treatment = strap_woocommerce_get_component_treatment( $component_id );

		if ( 'native' !== $mapped_treatment ) {
			$treatment = $mapped_treatment;
			$source    = 'admin';
		}
	}

	$definition = $component['treatments'][ $treatment ];
	$contract   = array(
		'component_id'          => $component_id,
		'block_name'            => $block_name,
		'treatment'             => $treatment,
		'selection_source'      => $source,
		'presentation_depth'    => isset( $definition['presentation_depth'] ) ? (int) $definition['presentation_depth'] : 0,
		'root_classes'          => empty( $definition['class'] ) ? array() : array( $definition['class'] ),
		'child_targets'         => isset( $definition['child_targets'] ) ? $definition['child_targets'] : array(),
		'propagated_styles'     => isset( $definition['propagated_styles'] ) ? $definition['propagated_styles'] : array(),
		'propagated_attributes' => array(),
		'theme_json_baseline'   => isset( $definition['theme_json_baseline'] ) ? $definition['theme_json_baseline'] : null,
		'preserve_states'       => isset( $definition['preserve_states'] ) && is_array( $definition['preserve_states'] ) ? $definition['preserve_states'] : array(),
		'context'               => $context,
		'native_opt_out'        => 'native' === $treatment,
	);

	$contract = apply_filters( 'strap_woocommerce_block_presentation', $contract, $parsed_block, $context );

	return strap_woocommerce_validate_block_presentation( $contract );
}

/**
 * Validate a filter result against the registered presentation surface.
 *
 * @param mixed $contract Proposed presentation contract.
 * @return array<string, mixed>
 */
function strap_woocommerce_validate_block_presentation( $contract ) {
	if ( ! is_array( $contract ) || empty( $contract['component_id'] ) || empty( $contract['block_name'] ) || empty( $contract['treatment'] ) ) {
		return array();
	}

	$registry = strap_woocommerce_component_registry();

	if ( ! isset( $registry[ $contract['component_id'] ] ) || $registry[ $contract['component_id'] ]['block_name'] !== $contract['block_name'] ) {
		return array();
	}

	$component = $registry[ $contract['component_id'] ];
	$treatment = sanitize_key( $contract['treatment'] );

	if ( ! isset( $component['treatments'][ $treatment ] ) ) {
		return array();
	}

	$definition                   = $component['treatments'][ $treatment ];
	$contract['treatment']        = $treatment;
	$contract['selection_source'] = in_array( $contract['selection_source'], array( 'authored', 'admin', 'default', 'native' ), true ) ? $contract['selection_source'] : 'native';
	$contract['context']          = in_array( $contract['context'], array( 'frontend', 'editor' ), true ) ? $contract['context'] : 'frontend';
	$contract['presentation_depth'] = isset( $definition['presentation_depth'] ) ? (int) $definition['presentation_depth'] : 0;
	$contract['root_classes']        = empty( $definition['class'] ) ? array() : array( $definition['class'] );
	$contract['child_targets']       = isset( $definition['child_targets'] ) ? $definition['child_targets'] : array();
	$contract['propagated_styles']   = isset( $definition['propagated_styles'] ) ? $definition['propagated_styles'] : array();
	$contract['propagated_attributes'] = array();
	$contract['theme_json_baseline']   = isset( $definition['theme_json_baseline'] ) ? $definition['theme_json_baseline'] : null;
	$contract['preserve_states']       = isset( $definition['preserve_states'] ) && is_array( $definition['preserve_states'] ) ? $definition['preserve_states'] : array();
	$contract['native_opt_out']        = 'native' === $treatment;

	if ( $contract['native_opt_out'] ) {
		$contract['selection_source'] = 'native';
	}

	return $contract;
}

/**
 * Apply an explicit admin-selected Product Template treatment to its public
 * root. Authored styles already arrive in WooCommerce's wrapper classes.
 *
 * @param string $block_content Rendered block markup.
 * @param array  $parsed_block  Parsed block data.
 * @return string
 */
function strap_woocommerce_render_product_template_presentation( $block_content, $parsed_block ) {
	$context  = is_admin() ? 'editor' : 'frontend';
	$contract = strap_woocommerce_resolve_block_presentation( $parsed_block, $context );

	if ( empty( $contract ) || $contract['native_opt_out'] ) {
		return $block_content;
	}

	if ( 'admin' === $contract['selection_source'] && ! empty( $contract['root_classes'] ) && class_exists( 'WP_HTML_Tag_Processor' ) ) {
		$processor = new WP_HTML_Tag_Processor( $block_content );

		if ( $processor->next_tag( array( 'tag_name' => 'ul' ) ) ) {
			$classes = (string) $processor->get_attribute( 'class' );

			if ( str_contains( $classes, 'is-product-collection-layout-carousel' ) ) {
				return $block_content;
			}

			foreach ( $contract['root_classes'] as $class_name ) {
				$processor->add_class( $class_name );
			}
		}

		$block_content = $processor->get_updated_html();
	}

	return apply_filters( 'strap_woocommerce_block_presentation_output', $block_content, $contract, $parsed_block, $context );
}
add_filter( 'render_block_woocommerce/product-template', 'strap_woocommerce_render_product_template_presentation', 10, 2 );

/**
 * Map native Product Collection Carousel controls to the shared SystemStrap
 * icon-button utility without changing Woo's button markup or behavior.
 *
 * @param string $block_content Rendered block markup.
 * @param array  $parsed_block  Parsed block data.
 * @return string
 */
function strap_woocommerce_render_product_collection_carousel_icon_buttons( $block_content, $parsed_block ) {
	if ( ! class_exists( 'WP_HTML_Tag_Processor' ) || ! str_contains( $block_content, 'is-product-collection-layout-carousel' ) || ! str_contains( $block_content, 'wc-block-next-previous-buttons__button' ) ) {
		return $block_content;
	}

	$processor = new WP_HTML_Tag_Processor( $block_content );

	while ( $processor->next_tag( array( 'tag_name' => 'button' ) ) ) {
		$classes = (string) $processor->get_attribute( 'class' );

		if ( ! preg_match( '/(?:^|\\s)wc-block-next-previous-buttons__button(?:\\s|$)/', $classes ) ) {
			continue;
		}

		$processor->add_class( 'strap-icon-button' );
		$processor->add_class( 'strap-icon-button--woo' );
	}

	return $processor->get_updated_html();
}
add_filter( 'render_block_woocommerce/product-collection', 'strap_woocommerce_render_product_collection_carousel_icon_buttons', 20, 2 );

/**
 * Apply an explicit Reviews component mapping to its public Woo wrapper.
 * Modern and legacy Reviews retain their own public markup beneath this root.
 *
 * @param string $block_content Rendered block markup.
 * @param array  $parsed_block  Parsed block data.
 * @return string
 */
function strap_woocommerce_render_product_reviews_presentation( $block_content, $parsed_block ) {
	$context  = is_admin() ? 'editor' : 'frontend';
	$contract = strap_woocommerce_resolve_block_presentation( $parsed_block, $context );
	$registry = strap_woocommerce_component_registry();
	$pagination_treatment = strap_woocommerce_get_component_treatment( 'reviews_pagination' );
	$pagination_class = isset( $registry['reviews_pagination']['treatments'][ $pagination_treatment ]['class'] ) && 'native' !== $pagination_treatment
		? $registry['reviews_pagination']['treatments'][ $pagination_treatment ]['class']
		: '';

	if ( ! class_exists( 'WP_HTML_Tag_Processor' ) || ( ( empty( $contract ) || $contract['native_opt_out'] || 'admin' !== $contract['selection_source'] || empty( $contract['root_classes'] ) ) && '' === $pagination_class ) ) {
		return $block_content;
	}

	$processor = new WP_HTML_Tag_Processor( $block_content );

	if ( ! $processor->next_tag() ) {
		return $block_content;
	}

	if ( ! empty( $contract ) && ! $contract['native_opt_out'] && 'admin' === $contract['selection_source'] && ! empty( $contract['root_classes'] ) ) {
		foreach ( $contract['root_classes'] as $class_name ) {
			$processor->add_class( $class_name );
		}
	}

	if ( '' !== $pagination_class ) {
		$processor->add_class( $pagination_class );
	}

	return apply_filters( 'strap_woocommerce_block_presentation_output', $processor->get_updated_html(), $contract, $parsed_block, $context );
}
add_filter( 'render_block_woocommerce/product-reviews', 'strap_woocommerce_render_product_reviews_presentation', 10, 2 );

/**
 * Apply an explicit Reviews Pagination mapping to the modern public Woo wrapper.
 *
 * @param string $block_content Rendered block markup.
 * @param array  $parsed_block  Parsed block data.
 * @return string
 */
function strap_woocommerce_render_product_reviews_pagination_presentation( $block_content, $parsed_block ) {
	$context  = is_admin() ? 'editor' : 'frontend';
	$contract = strap_woocommerce_resolve_block_presentation( $parsed_block, $context );

	if ( empty( $contract ) || $contract['native_opt_out'] || 'admin' !== $contract['selection_source'] || empty( $contract['root_classes'] ) || ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return $block_content;
	}

	$processor = new WP_HTML_Tag_Processor( $block_content );

	if ( ! $processor->next_tag( array( 'tag_name' => 'div' ) ) ) {
		return $block_content;
	}

	foreach ( $contract['root_classes'] as $class_name ) {
		$processor->add_class( $class_name );
	}

	return apply_filters( 'strap_woocommerce_block_presentation_output', $processor->get_updated_html(), $contract, $parsed_block, $context );
}
add_filter( 'render_block_woocommerce/product-reviews-pagination', 'strap_woocommerce_render_product_reviews_pagination_presentation', 10, 2 );

/**
 * Return the selected Account mapping class when it is non-native.
 *
 * @param string $component_id Account component registry identifier.
 * @return string
 */
function strap_woocommerce_get_account_mapping_class( $component_id ) {
	$registry  = strap_woocommerce_component_registry();
	$treatment = strap_woocommerce_get_component_treatment( $component_id );

	if ( ! isset( $registry[ $component_id ]['treatments'][ $treatment ] ) || 'native' === $treatment ) {
		return '';
	}

	return isset( $registry[ $component_id ]['treatments'][ $treatment ]['class'] ) ? $registry[ $component_id ]['treatments'][ $treatment ]['class'] : '';
}

/**
 * Wrap the stable Account navigation hook output only for a selected mapping.
 */
function strap_woocommerce_before_account_navigation_presentation() {
	if ( is_admin() ) {
		return;
	}

	$class_name = strap_woocommerce_get_account_mapping_class( 'account_navigation' );

	if ( '' === $class_name ) {
		return;
	}

	echo '<div class="strap-woocommerce-account-navigation ' . esc_attr( $class_name ) . '">';
}
add_action( 'woocommerce_before_account_navigation', 'strap_woocommerce_before_account_navigation_presentation', 1 );

/**
 * Close the selected Account navigation presentation wrapper.
 */
function strap_woocommerce_after_account_navigation_presentation() {
	if ( is_admin() || '' === strap_woocommerce_get_account_mapping_class( 'account_navigation' ) ) {
		return;
	}

	echo '</div>';
}
add_action( 'woocommerce_after_account_navigation', 'strap_woocommerce_after_account_navigation_presentation', 999 );
