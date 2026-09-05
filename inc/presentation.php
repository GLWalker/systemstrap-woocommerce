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
 * Map an admin-selected locked Woo application region to the neutral System
 * Panel surface and structural composition contracts without changing Woo's
 * nested markup.
 *
 * @param string $block_content Rendered block markup.
 * @param string $component_id  Component registry identifier.
 * @return string
 */
function strap_woocommerce_render_application_panel_surface( $block_content, $component_id ) {
	if ( 'system-panel-woo' !== strap_woocommerce_get_component_treatment( $component_id ) || ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return $block_content;
	}

	$processor = new WP_HTML_Tag_Processor( $block_content );

	if ( ! $processor->next_tag( array( 'tag_name' => 'div' ) ) ) {
		return $block_content;
	}

	$processor->add_class( 'strap-panel-surface' );

	/*
	 * Woo's Order Summary retains its own compact content shell. It consumes
	 * only the neutral surface; the application-region inset belongs to the
	 * Cart and Checkout Fields roots.
	 */
	if ( 'checkout_totals' !== $component_id ) {
		$processor->add_class( 'strap-woocommerce-application-panel' );
	}

	return $processor->get_updated_html();
}

foreach ( array(
	'woocommerce/cart-items-block'  => 'cart_items',
	'woocommerce/cart-totals-block' => 'cart_totals',
	'woocommerce/checkout-fields-block' => 'checkout_fields',
	'woocommerce/checkout-order-summary-block' => 'checkout_totals',
) as $strap_woocommerce_application_block => $strap_woocommerce_application_component ) {
	add_filter(
		'render_block_' . $strap_woocommerce_application_block,
		static function( $block_content ) use ( $strap_woocommerce_application_component ) {
			return strap_woocommerce_render_application_panel_surface( $block_content, $strap_woocommerce_application_component );
		},
		10
	);
}
unset( $strap_woocommerce_application_block, $strap_woocommerce_application_component );

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
 * Bridge only an explicitly selected Review Template style to the neutral
 * SystemStrap Comments master hook. Native Woo review output is unchanged.
 *
 * @param string $block_content Rendered block markup.
 * @param array  $parsed_block  Parsed block data.
 * @return string
 */
function strap_woocommerce_render_product_review_template_comments_style_adapter( $block_content, $parsed_block ) {
	if ( ! class_exists( 'WP_HTML_Tag_Processor' ) || ! str_contains( $block_content, 'is-style-system-' ) ) {
		return $block_content;
	}

	$processor = new WP_HTML_Tag_Processor( $block_content );

	if ( ! $processor->next_tag( array( 'tag_name' => 'ol' ) ) ) {
		return $block_content;
	}

	$classes = (string) $processor->get_attribute( 'class' );

	if ( ! preg_match( '/(?:^|\\s)is-style-system-(?:list|panel)(?:\\s|$)/', $classes ) ) {
		return $block_content;
	}

	$processor->add_class( 'strap-comments-thread' );

	return $processor->get_updated_html();
}
add_filter( 'render_block_woocommerce/product-review-template', 'strap_woocommerce_render_product_review_template_comments_style_adapter', 10, 2 );

/**
 * Bridge a selected standalone archive-review style to the neutral Comments
 * master hook. Woo's client renderer retains this public root and renders its
 * review list as a direct child, so no client-DOM mutation is required.
 *
 * @param string $block_content Rendered block markup.
 * @param array  $parsed_block  Parsed block data.
 * @return string
 */
function strap_woocommerce_render_archive_review_comments_style_adapter( $block_content, $parsed_block ) {
	if ( ! class_exists( 'WP_HTML_Tag_Processor' ) || ! str_contains( $block_content, 'is-style-system-' ) ) {
		return $block_content;
	}

	$processor = new WP_HTML_Tag_Processor( $block_content );

	if ( ! $processor->next_tag( array( 'tag_name' => 'div' ) ) ) {
		return $block_content;
	}

	$classes = (string) $processor->get_attribute( 'class' );

	if ( ! preg_match( '/(?:^|\\s)is-style-system-(?:list|panel)(?:\\s|$)/', $classes ) ) {
		return $block_content;
	}

	$processor->add_class( 'strap-comments-thread' );

	return $processor->get_updated_html();
}

foreach ( array( 'woocommerce/all-reviews', 'woocommerce/reviews-by-product', 'woocommerce/reviews-by-category' ) as $strap_woocommerce_archive_review_block ) {
	add_filter( 'render_block_' . $strap_woocommerce_archive_review_block, 'strap_woocommerce_render_archive_review_comments_style_adapter', 10, 2 );
}
unset( $strap_woocommerce_archive_review_block );

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
 * Return whether the optional My Account Address System Panel mapping is selected.
 *
 * @return bool
 */
function strap_woocommerce_has_address_surface_mapping() {
	return ! is_admin()
		&& is_account_page()
		&& '' !== strap_woocommerce_get_account_mapping_class( 'woo_addresses' );
}

/**
 * Start a narrow output bridge for Woo's stable My Account address overview.
 *
 * The template's direct `.woocommerce-Address` cards retain their complete
 * Woo markup; the bridge only adds the neutral shared Panel class.
 *
 * @param string $template_name Woo template name.
 */
function strap_woocommerce_before_my_account_address_surface( $template_name ) {
	if ( 'myaccount/my-address.php' !== $template_name || ! strap_woocommerce_has_address_surface_mapping() || ! is_wc_endpoint_url( 'edit-address' ) ) {
		return;
	}

	$GLOBALS['strap_woocommerce_address_surface_buffer_level'] = ob_get_level();
	ob_start();
}
add_action( 'woocommerce_before_template_part', 'strap_woocommerce_before_my_account_address_surface', 1 );

/**
 * Apply the selected neutral Panel class directly to each public Woo address card.
 *
 * @param string $template_name Woo template name.
 */
function strap_woocommerce_after_my_account_address_surface( $template_name ) {
	if ( 'myaccount/my-address.php' !== $template_name || ! isset( $GLOBALS['strap_woocommerce_address_surface_buffer_level'] ) ) {
		return;
	}

	$buffer_level = $GLOBALS['strap_woocommerce_address_surface_buffer_level'];
	unset( $GLOBALS['strap_woocommerce_address_surface_buffer_level'] );

	if ( ob_get_level() <= $buffer_level ) {
		return;
	}

	$content = ob_get_clean();

	if ( ! class_exists( 'WP_HTML_Tag_Processor' ) || ! str_contains( $content, 'woocommerce-Address' ) ) {
		echo $content;
		return;
	}

	$processor = new WP_HTML_Tag_Processor( $content );

	while ( $processor->next_tag( array( 'tag_name' => 'div' ) ) ) {
		$classes = (string) $processor->get_attribute( 'class' );

		if ( ! preg_match( '/(?:^|\\s)woocommerce-Address(?:\\s|$)/', $classes ) ) {
			continue;
		}

		$processor->add_class( 'strap-panel-surface' );
	}

	echo $processor->get_updated_html();
}
add_action( 'woocommerce_after_template_part', 'strap_woocommerce_after_my_account_address_surface', 999 );

/**
 * Return whether the selected Address surface applies to My Account View Order.
 *
 * @return bool
 */
function strap_woocommerce_is_view_order_address_surface() {
	return strap_woocommerce_has_address_surface_mapping()
		&& is_wc_endpoint_url( 'view-order' );
}

/**
 * Start a narrow output bridge for Woo's View Order customer address template.
 *
 * @param string $template_name Woo template name.
 */
function strap_woocommerce_before_view_order_address_surface( $template_name ) {
	if ( 'order/order-details-customer.php' !== $template_name || ! strap_woocommerce_is_view_order_address_surface() ) {
		return;
	}

	$GLOBALS['strap_woocommerce_view_order_address_surface_buffer_level'] = ob_get_level();
	ob_start();
}
add_action( 'woocommerce_before_template_part', 'strap_woocommerce_before_view_order_address_surface', 1 );

/**
 * Apply the selected neutral Panel class to Woo's existing View Order address elements.
 *
 * @param string $template_name Woo template name.
 */
function strap_woocommerce_after_view_order_address_surface( $template_name ) {
	if ( 'order/order-details-customer.php' !== $template_name || ! isset( $GLOBALS['strap_woocommerce_view_order_address_surface_buffer_level'] ) ) {
		return;
	}

	$buffer_level = $GLOBALS['strap_woocommerce_view_order_address_surface_buffer_level'];
	unset( $GLOBALS['strap_woocommerce_view_order_address_surface_buffer_level'] );

	if ( ob_get_level() <= $buffer_level ) {
		return;
	}

	$content = ob_get_clean();

	if ( ! class_exists( 'WP_HTML_Tag_Processor' ) || ! str_contains( $content, 'woocommerce-customer-details' ) ) {
		echo $content;
		return;
	}

	$processor = new WP_HTML_Tag_Processor( $content );

	while ( $processor->next_tag( array( 'tag_name' => 'address' ) ) ) {
		$processor->add_class( 'strap-panel-surface' );
	}

	echo $processor->get_updated_html();
}
add_action( 'woocommerce_after_template_part', 'strap_woocommerce_after_view_order_address_surface', 999 );

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

/**
 * Return whether the optional Woo Tables System Panel mapping is selected.
 *
 * @return bool
 */
function strap_woocommerce_has_table_surface_mapping() {
	return ! is_admin() && '' !== strap_woocommerce_get_account_mapping_class( 'woo_tables' );
}

/**
 * Open the shared SystemStrap Table Surface around a stable Woo table output.
 */
function strap_woocommerce_open_table_surface() {
	if ( ! strap_woocommerce_has_table_surface_mapping() ) {
		return;
	}

	echo '<div class="strap-woocommerce-table-surface ' . esc_attr( strap_woocommerce_get_account_mapping_class( 'woo_tables' ) ) . '">';
}

/**
 * Close a selected shared SystemStrap Table Surface wrapper.
 */
function strap_woocommerce_close_table_surface() {
	if ( ! strap_woocommerce_has_table_surface_mapping() ) {
		return;
	}

	echo '</div>';
}

/**
 * Wrap Account Orders only when Woo is about to render its semantic table.
 *
 * @param bool $has_orders Whether the current account query has orders.
 */
function strap_woocommerce_before_account_orders_table_surface( $has_orders ) {
	if ( ! $has_orders ) {
		return;
	}

	strap_woocommerce_open_table_surface();
}
add_action( 'woocommerce_before_account_orders', 'strap_woocommerce_before_account_orders_table_surface', 1 );
add_action( 'woocommerce_before_account_orders_pagination', 'strap_woocommerce_close_table_surface', 1 );

/**
 * Wrap Account Payment Methods only when Woo renders its semantic table.
 *
 * @param bool $has_methods Whether the customer has saved payment methods.
 */
function strap_woocommerce_before_account_payment_methods_table_surface( $has_methods ) {
	if ( ! $has_methods ) {
		return;
	}

	strap_woocommerce_open_table_surface();
}

/**
 * Close the Account Payment Methods table surface before Woo's add-method link.
 *
 * @param bool $has_methods Whether the customer has saved payment methods.
 */
function strap_woocommerce_after_account_payment_methods_table_surface( $has_methods ) {
	if ( ! $has_methods ) {
		return;
	}

	strap_woocommerce_close_table_surface();
}
add_action( 'woocommerce_before_account_payment_methods', 'strap_woocommerce_before_account_payment_methods_table_surface', 1 );
add_action( 'woocommerce_after_account_payment_methods', 'strap_woocommerce_after_account_payment_methods_table_surface', 1 );

/**
 * Limit order-detail table bridges to the authenticated View Order endpoint.
 *
 * @return bool
 */
function strap_woocommerce_is_view_order_table_surface() {
	return strap_woocommerce_has_table_surface_mapping() && is_account_page() && is_wc_endpoint_url( 'view-order' );
}

/**
 * Wrap View Order details without changing Woo's table or responsive markup.
 */
function strap_woocommerce_before_view_order_table_surface() {
	if ( ! strap_woocommerce_is_view_order_table_surface() ) {
		return;
	}

	strap_woocommerce_open_table_surface();
}

/**
 * Close the View Order details table surface before Woo's following actions.
 */
function strap_woocommerce_after_view_order_table_surface() {
	if ( ! strap_woocommerce_is_view_order_table_surface() ) {
		return;
	}

	strap_woocommerce_close_table_surface();
}
add_action( 'woocommerce_order_details_before_order_table', 'strap_woocommerce_before_view_order_table_surface', 1 );
add_action( 'woocommerce_order_details_after_order_table', 'strap_woocommerce_after_view_order_table_surface', 1 );

/**
 * Identify Account Downloads and View Order downloads template rendering.
 *
 * @param string $template_name Woo template name.
 * @return bool
 */
function strap_woocommerce_is_account_downloads_table_template( $template_name ) {
	return 'order/order-downloads.php' === $template_name
		&& strap_woocommerce_has_table_surface_mapping()
		&& is_account_page()
		&& ( is_wc_endpoint_url( 'downloads' ) || is_wc_endpoint_url( 'view-order' ) );
}

/**
 * Open a shared surface around Woo's stable downloads template output.
 *
 * @param string $template_name Woo template name.
 */
function strap_woocommerce_before_account_downloads_table_surface( $template_name ) {
	if ( ! strap_woocommerce_is_account_downloads_table_template( $template_name ) ) {
		return;
	}

	strap_woocommerce_open_table_surface();
}

/**
 * Close a shared surface around Woo's stable downloads template output.
 *
 * @param string $template_name Woo template name.
 */
function strap_woocommerce_after_account_downloads_table_surface( $template_name ) {
	if ( ! strap_woocommerce_is_account_downloads_table_template( $template_name ) ) {
		return;
	}

	strap_woocommerce_close_table_surface();
}
add_action( 'woocommerce_before_template_part', 'strap_woocommerce_before_account_downloads_table_surface', 1 );
add_action( 'woocommerce_after_template_part', 'strap_woocommerce_after_account_downloads_table_surface', 999 );
