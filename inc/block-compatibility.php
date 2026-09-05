<?php
/**
 * WooCommerce block metadata compatibility for SystemStrap.
 *
 * @package systemstrap-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add only missing Woo block metadata required by the Gradient capability,
 * without changing native Woo presentation or action flow.
 *
 * @param array $metadata Block metadata.
 * @return array
 */
function strap_woocommerce_extend_product_button_metadata( $metadata ) {
	if ( ! is_array( $metadata ) || empty( $metadata['name'] ) ) {
		return $metadata;
	}

	$registry = strap_woocommerce_editor_compatibility_registry();
	$entry    = $registry[ $metadata['name'] ] ?? null;

	if ( ! is_array( $entry ) ) {
		return $metadata;
	}

	if ( ! isset( $metadata['attributes'] ) || ! is_array( $metadata['attributes'] ) ) {
		$metadata['attributes'] = array();
	}

	foreach ( $entry['attributes'] ?? array() as $attribute => $type ) {
		if ( ! isset( $metadata['attributes'][ $attribute ] ) ) {
			$metadata['attributes'][ $attribute ] = array( 'type' => $type );
		}
	}

	foreach ( $entry['supports'] as $support => $values ) {
		if ( ! isset( $metadata['supports'][ $support ] ) || ! is_array( $metadata['supports'][ $support ] ) ) {
			$metadata['supports'][ $support ] = array();
		}

		foreach ( $values as $key => $value ) {
			$metadata['supports'][ $support ][ $key ] = $value;
		}
	}

	return $metadata;
}
add_filter( 'block_type_metadata', 'strap_woocommerce_extend_product_button_metadata', 20 );

/**
 * Add Core Query Pagination's label-visibility context to Woo's equivalent
 * Reviews Pagination parent and navigation children.
 *
 * @param array $metadata Block metadata.
 * @return array
 */
function strap_woocommerce_extend_reviews_pagination_metadata( $metadata ) {
	if ( ! is_array( $metadata ) || empty( $metadata['name'] ) ) {
		return $metadata;
	}

	if ( 'woocommerce/product-reviews-pagination' === $metadata['name'] ) {
		if ( ! isset( $metadata['attributes'] ) || ! is_array( $metadata['attributes'] ) ) {
			$metadata['attributes'] = array();
		}

		$metadata['attributes']['showLabel'] = array(
			'type'    => 'boolean',
			'default' => true,
		);

		if ( ! isset( $metadata['providesContext'] ) || ! is_array( $metadata['providesContext'] ) ) {
			$metadata['providesContext'] = array();
		}

		$metadata['providesContext']['reviews/showLabel'] = 'showLabel';
	}

	if ( in_array( $metadata['name'], array( 'woocommerce/product-reviews-pagination-previous', 'woocommerce/product-reviews-pagination-next' ), true ) ) {
		if ( ! isset( $metadata['usesContext'] ) || ! is_array( $metadata['usesContext'] ) ) {
			$metadata['usesContext'] = array();
		}

		if ( ! in_array( 'reviews/showLabel', $metadata['usesContext'], true ) ) {
			$metadata['usesContext'][] = 'reviews/showLabel';
		}
	}

	return $metadata;
}
add_filter( 'block_type_metadata', 'strap_woocommerce_extend_reviews_pagination_metadata', 20 );

/**
 * Hide only the visible text of an arrow-bearing Woo review-pagination link.
 *
 * Woo remains authoritative for the link, URL, arrow, interactivity, and
 * block style classes. As in Core Query Pagination, the removed text becomes
 * the accessible name of the link.
 *
 * @param string   $block_content Rendered block markup.
 * @param array    $parsed_block  Parsed block data.
 * @param WP_Block $block         Block instance.
 * @return string
 */
function strap_woocommerce_render_reviews_pagination_hidden_label( $block_content, $parsed_block, $block ) {
	$show_label = $block->context['reviews/showLabel'] ?? true;
	$arrow      = $block->context['reviews/paginationArrow'] ?? 'none';

	// Match Core: labels cannot be hidden when there is no decorative arrow.
	if ( false !== $show_label || ! in_array( $arrow, array( 'arrow', 'chevron' ), true ) || ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return $block_content;
	}

	$is_previous = 'woocommerce/product-reviews-pagination-previous' === ( $parsed_block['blockName'] ?? '' );
	$default     = $is_previous ? __( 'Older Reviews', 'woocommerce' ) : __( 'Newer Reviews', 'woocommerce' );
	$attributes  = isset( $parsed_block['attrs'] ) && is_array( $parsed_block['attrs'] ) ? $parsed_block['attrs'] : array();
	$label       = isset( $attributes['label'] ) && '' !== trim( (string) $attributes['label'] ) ? wp_strip_all_tags( (string) $attributes['label'] ) : $default;
	$processor   = new WP_HTML_Tag_Processor( $block_content );

	if ( ! $processor->next_tag( 'a' ) ) {
		return $block_content;
	}

	$processor->set_attribute( 'aria-label', $label );
	$inside_arrow = false;

	while ( $processor->next_token() ) {
		if ( '#tag' === $processor->get_token_type() && 'span' === strtolower( (string) $processor->get_token_name() ) ) {
			if ( $processor->is_tag_closer() ) {
				$inside_arrow = false;
			} elseif ( preg_match( '/(?:^|\\s)wp-block-woocommerce-product-reviews-pagination-(?:previous|next)-arrow(?:\\s|$)/', (string) $processor->get_attribute( 'class' ) ) ) {
				$inside_arrow = true;
			}

			continue;
		}

		if ( '#text' === $processor->get_token_type() && ! $inside_arrow && '' !== trim( $processor->get_modifiable_text() ) ) {
			$processor->set_modifiable_text( '' );
		}
	}

	return $processor->get_updated_html();
}
add_filter( 'render_block_woocommerce/product-reviews-pagination-previous', 'strap_woocommerce_render_reviews_pagination_hidden_label', 10, 3 );
add_filter( 'render_block_woocommerce/product-reviews-pagination-next', 'strap_woocommerce_render_reviews_pagination_hidden_label', 10, 3 );
