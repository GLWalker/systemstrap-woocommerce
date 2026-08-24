( function( hooks, registry ) {
	'use strict';

	if ( ! hooks || ! registry || ! registry.blocks ) {
		return;
	}

	var productButton = registry.blocks['woocommerce/product-button'];

	if ( ! productButton ) {
		return;
	}

	hooks.addFilter(
		'blocks.registerBlockType',
		'systemstrap-woocommerce/product-button-client-schema',
		function( settings, name ) {
			if ( 'woocommerce/product-button' !== name ) {
				return settings;
			}

			var supports = Object.assign( {}, settings.supports || {} );
			var color = Object.assign( {}, supports.color || {}, productButton.supports.color || {} );

			color.gradients = true;
			supports.color  = color;

			return Object.assign( {}, settings, {
				supports: supports
			} );
		}
	);
} )( window.wp.hooks, window.strapWooEditorCompatibility );
