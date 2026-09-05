( function( hooks, registry, compose, element, blockEditor, components, i18n ) {
	'use strict';

	if ( ! hooks || ! registry || ! registry.blocks ) {
		return;
	}

	var productButton = registry.blocks['woocommerce/product-button'];

	hooks.addFilter(
		'blocks.registerBlockType',
		'systemstrap-woocommerce/block-client-schema',
		function( settings, name ) {
			if ( 'woocommerce/product-button' === name && productButton ) {
				var supports = Object.assign( {}, settings.supports || {} );
				var color = Object.assign( {}, supports.color || {}, productButton.supports.color || {} );

				color.gradients = true;
				supports.color  = color;

				return Object.assign( {}, settings, {
					supports: supports
				} );
			}

			if ( 'woocommerce/product-reviews-pagination' === name ) {
				return Object.assign( {}, settings, {
					attributes: Object.assign( {}, settings.attributes || {}, {
						showLabel: {
							type: 'boolean',
							default: true
						}
					} ),
					providesContext: Object.assign( {}, settings.providesContext || {}, {
						'reviews/showLabel': 'showLabel'
					} )
				} );
			}

			if ( 'woocommerce/product-reviews-pagination-previous' === name || 'woocommerce/product-reviews-pagination-next' === name ) {
				var usesContext = ( settings.usesContext || [] ).slice();

				if ( -1 === usesContext.indexOf( 'reviews/showLabel' ) ) {
					usesContext.push( 'reviews/showLabel' );
				}

				return Object.assign( {}, settings, {
					usesContext: usesContext
				} );
			}

			return settings;
		}
	);

	if ( ! compose || ! element || ! blockEditor || ! components || ! i18n ) {
		return;
	}

	var applicationPanelBlocks = registry.applicationPanelBlocks || {};
	var reviewThreadBlocks = {
		'woocommerce/product-review-template': true,
		'woocommerce/all-reviews': true,
		'woocommerce/reviews-by-product': true,
		'woocommerce/reviews-by-category': true
	};

	hooks.addFilter(
		'editor.BlockListBlock',
		'systemstrap-woocommerce/application-panel-editor-class',
		compose.createHigherOrderComponent(
			function( BlockListBlock ) {
				return function( props ) {
					var applicationPanelType = applicationPanelBlocks[ props.name ];

					if ( ! applicationPanelType ) {
						return element.createElement( BlockListBlock, props );
					}

					return element.createElement(
						BlockListBlock,
						Object.assign( {}, props, {
							className: ( props.className || '' ) + ' strap-panel-surface' + ( 'application' === applicationPanelType ? ' strap-woocommerce-application-panel' : '' )
						} )
					);
				};
			},
			'withApplicationPanelEditorClass'
		)
	);

	/*
	 * Woo's review editors render client-side, so the frontend render filter
	 * cannot add the neutral Comments bridge there. The Woo Edit components do
	 * not forward BlockEdit attributes into useBlockProps(), so the stable
	 * BlockListBlock wrapper is the editor bridge boundary.
	 */
	hooks.addFilter(
		'editor.BlockListBlock',
		'systemstrap-woocommerce/review-thread-editor-class',
		compose.createHigherOrderComponent(
			function( BlockListBlock ) {
				return function( props ) {
					var authoredClassName = ( props.attributes && props.attributes.className ) || '';
					var treatmentMatch = authoredClassName.match( /(?:^|\s)(is-style-system-(?:list|panel))(?:\s|$)/ );

					if ( ! reviewThreadBlocks[ props.name ] || ! treatmentMatch ) {
						return element.createElement( BlockListBlock, props );
					}

					return element.createElement(
						BlockListBlock,
						Object.assign( {}, props, {
							className: ( props.className || '' ) + ' strap-comments-thread ' + treatmentMatch[ 1 ]
						} )
					);
				};
			},
			'withReviewThreadEditorClass'
		)
	);

	hooks.addFilter(
		'editor.BlockEdit',
		'systemstrap-woocommerce/reviews-pagination-show-label-control',
		compose.createHigherOrderComponent(
			function( BlockEdit ) {
				return function( props ) {
					var isPagination = 'woocommerce/product-reviews-pagination' === props.name;
					var arrow = props.attributes.paginationArrow || 'none';
					var showLabel = false !== props.attributes.showLabel;

					element.useEffect( function() {
						if ( isPagination && 'none' === arrow && ! showLabel ) {
							props.setAttributes( { showLabel: true } );
						}
					}, [ isPagination, arrow, showLabel, props.setAttributes ] );

					return element.createElement(
						element.Fragment,
						null,
						element.createElement( BlockEdit, props ),
						isPagination && 'none' !== arrow && element.createElement(
							blockEditor.InspectorControls,
							null,
							element.createElement(
								components.PanelBody,
								{ title: i18n.__( 'Settings', 'systemstrap-woocommerce' ) },
								element.createElement( components.ToggleControl, {
									label: i18n.__( 'Show label text', 'systemstrap-woocommerce' ),
									help: i18n.__( 'Make label text visible, e.g. "Next Page".', 'systemstrap-woocommerce' ),
									checked: showLabel,
									onChange: function( value ) {
										props.setAttributes( { showLabel: value } );
									}
								} )
							)
						)
					);
				};
			},
			'withReviewsPaginationShowLabelControl'
		)
	);
} )( window.wp.hooks, window.strapWooEditorCompatibility, window.wp.compose, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.i18n );
