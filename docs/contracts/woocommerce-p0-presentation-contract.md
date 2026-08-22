# SystemStrap WooCommerce P0 Presentation Contract

## Scope

This contract governs P0 presentation integration in the
`systemstrap-woocommerce` companion. It applies only to source-proven public
WooCommerce block roots and stable public component surfaces.

The companion must extend WooCommerce behavior. It must not rewrite private
interactive DOM, replace WooCommerce application logic, or introduce a
parallel template-routing system.

## Selection and opt-out

Presentation selection order is:

1. Authored saved `is-style-system-*-woo` block style.
2. Explicit stored component mapping.
3. Validated companion default.
4. Native WooCommerce.

`native` is a terminal selection. It must remove/withhold companion
presentation classes, descendant propagation, and defaults for that component.

Only registered treatments, block names, root classes, and public descendants
may be selected. Component mappings must store treatment identifiers only; they
must not store raw CSS, selectors, executable code, callbacks, or private-DOM
instructions.

## Proven P0 families

### Product Button

The public Product Button root is
`.wp-block-button.wc-block-components-product-button`. Its interactive child is
`.wp-block-button__link.wp-element-button.wc-block-components-product-button__button`.

Any selected presentation must preserve the native simple-product add-to-cart,
external-product link, variable/grouped-product link, loading, added, disabled,
focus, accessible-label, and View Cart states. It must not replace the child
element or its WooCommerce interactivity directives.

### Product Reviews and Reviews Pagination

The final user-facing Woo reviews treatments are System List Woo, System List
Flush Woo, and System Panel Woo. They map to the equivalent SystemStrap
Comments-family ownership model while using Woo-specific modern and legacy
adapters.

Modern Reviews and legacy `comments_template()` fallback require separate
public-descendant adapters beneath one selected root contract. Review rating,
review form, reply, pagination click behavior, and accessible/native state must
remain WooCommerce-owned.

### Cart and Checkout

Cart and Checkout are Level 5 application boundaries. A future mapping may
select only their public application root or a separately source-proven stable
public component surface.

The companion must not rewrite, filter-propagate into, or take ownership of
hydrated cart state, loading masks, quantity events, address fields, shipping,
payment, validation, express-payment, order-summary state, or place-order
behavior.

### Account

Account Navigation, Forms, Orders Table, and Notices may use whole-component
adapters only. They must preserve native semantics, active state,
`aria-current`, labels, fields, action links/buttons, responsive table data, and
extension-hook content.

Endpoint-specific descendants without a public runtime contract remain Native
or deferred. They must not be styled through inferred selectors.

## Native Product Gallery exception

Product Gallery is Native WooCommerce for P0. No Gallery `-woo` style,
component mapping, bridge CSS, render filter, or presentation propagation may
be registered.

Gallery media wrappers, links, thumbnails, selected-thumbnail state, zoom,
lightbox, responsive sizing, crop behavior, and sale-badge relationships remain
WooCommerce-owned.

## Editor and frontend parity

Every future public block-style treatment must validate saved editor behavior
and frontend behavior. Different editor and frontend public roots require
separate minimal support or deferral; they do not authorize broad render-time
class injection.

## Boundaries

This contract does not authorize global WooCommerce CSS, product-template
overrides, template changes, WooCommerce core changes, theme JSON merges beyond
metadata-supported fields, or presentation behavior for components not covered
by a source- and runtime-proven public contract.
