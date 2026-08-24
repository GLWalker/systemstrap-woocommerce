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
