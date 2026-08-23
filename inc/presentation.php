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
		'theme_json_baseline'   => null,
		'preserve_states'       => array(),
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
	$contract['theme_json_baseline']   = null;
	$contract['preserve_states']       = array();
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
 * Generate preset-only Panel background routing for Product Template cards.
 *
 * @return string
 */
function strap_woocommerce_get_product_template_dynamic_styles() {
	$settings  = wp_get_global_settings();
	$colors    = $settings['color']['palette']['theme'] ?? array();
	$gradients = $settings['color']['gradients']['theme'] ?? array();
	$root      = ':is(.wp-block-woocommerce-product-template, .wc-block-product-template).is-style-system-panel-woo';
	$css       = '';

	foreach ( $colors as $color ) {
		$slug = sanitize_title( $color['slug'] ?? '' );

		if ( '' === $slug ) {
			continue;
		}

		$css .= sprintf(
			"%1\$s.has-%2\$s-background-color { background-color: transparent !important; }\n%1\$s.has-%2\$s-background-color > li.wc-block-product { background-color: var(--wp--preset--color--%2\$s) !important; color: var(--wp--preset--color--%2\$s-text, inherit) !important; }\n",
			$root,
			$slug
		);
	}

	foreach ( $gradients as $gradient ) {
		$slug = sanitize_title( $gradient['slug'] ?? '' );

		if ( '' === $slug ) {
			continue;
		}

		$css .= sprintf(
			"%1\$s.has-%2\$s-gradient-background { background-image: none !important; }\n%1\$s.has-%2\$s-gradient-background > li.wc-block-product { background-image: var(--wp--preset--gradient--%2\$s) !important; }\n",
			$root,
			$slug
		);
	}

	return $css;
}
