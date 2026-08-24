<?php
/**
 * WooCommerce component mapping settings.
 *
 * @package systemstrap-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validate stored mappings against the shared component registry.
 *
 * @param mixed $mappings Submitted mapping values.
 * @return array<string, string>
 */
function strap_woocommerce_sanitize_component_mappings( $mappings ) {
	$valid    = array();
	$registry = strap_woocommerce_component_registry();
	$mappings = is_array( $mappings ) ? $mappings : array();

	foreach ( $registry as $component_id => $component ) {
		if ( 'admin_mapping' !== ( $component['application'] ?? '' ) || ! isset( $mappings[ $component_id ] ) ) {
			continue;
		}

		$treatment = sanitize_key( $mappings[ $component_id ] );

		if ( isset( $component['treatments'][ $treatment ] ) ) {
			$valid[ $component_id ] = $treatment;
		}
	}

	return $valid;
}

/**
 * Register the component mapping option.
 */
function strap_woocommerce_register_component_mapping_settings() {
	register_setting(
		'systemstrap_woocommerce_mappings',
		'strap_woocommerce_component_mappings',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'strap_woocommerce_sanitize_component_mappings',
			'default'           => array(),
		)
	);
}
add_action( 'admin_init', 'strap_woocommerce_register_component_mapping_settings' );

/**
 * Register the companion settings page.
 */
function strap_woocommerce_register_component_mapping_page() {
	add_options_page(
		__( 'SystemStrap WooCommerce', 'systemstrap-woocommerce' ),
		__( 'SystemStrap WooCommerce', 'systemstrap-woocommerce' ),
		'manage_options',
		'systemstrap-woocommerce',
		'strap_woocommerce_render_component_mapping_page'
	);
}
add_action( 'admin_menu', 'strap_woocommerce_register_component_mapping_page' );

/**
 * Render the component treatment mapping page.
 */
function strap_woocommerce_render_component_mapping_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$registry = strap_woocommerce_component_registry();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'SystemStrap WooCommerce', 'systemstrap-woocommerce' ); ?></h1>
		<p><?php esc_html_e( 'Choose optional SystemStrap presentation for supported WooCommerce components. Native WooCommerce leaves the component unchanged.', 'systemstrap-woocommerce' ); ?></p>
		<form action="options.php" method="post">
			<?php settings_fields( 'systemstrap_woocommerce_mappings' ); ?>
			<table class="form-table" role="presentation">
				<tbody>
					<?php foreach ( $registry as $component_id => $component ) : ?>
						<?php if ( 'admin_mapping' !== ( $component['application'] ?? '' ) ) : ?>
							<?php continue; ?>
						<?php endif; ?>
						<tr>
							<th scope="row"><label for="strap-woocommerce-<?php echo esc_attr( $component_id ); ?>"><?php echo esc_html( $component['label'] ); ?></label></th>
							<td>
								<select id="strap-woocommerce-<?php echo esc_attr( $component_id ); ?>" name="strap_woocommerce_component_mappings[<?php echo esc_attr( $component_id ); ?>]">
									<?php foreach ( $component['treatments'] as $treatment_id => $treatment ) : ?>
										<option value="<?php echo esc_attr( $treatment_id ); ?>" <?php echo selected( strap_woocommerce_get_component_treatment( $component_id ), $treatment_id, false ); ?>><?php echo esc_html( $treatment['label'] ); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
