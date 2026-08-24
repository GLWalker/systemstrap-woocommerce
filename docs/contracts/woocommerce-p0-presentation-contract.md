# SystemStrap WooCommerce P0 Presentation Contract

## Scope

This contract governs P0 presentation integration in the
`systemstrap-woocommerce` companion. It applies only to source-proven public
WooCommerce block roots and stable public component surfaces.

The companion must extend WooCommerce behavior. It must not rewrite private
interactive DOM, replace WooCommerce application logic, or introduce a
parallel template-routing system.

## Master-component parity gate

Before the companion implements a named SystemStrap treatment, it MUST inspect
the authoritative SystemStrap master CSS and map its outer shell, rows,
typography, states, transitions, responsive behavior, and System UI effects to
source-proven Woo public roles. The result MUST preserve the master contract
unless Woo's public structure makes a property impossible; each exception MUST
be documented. A border/radius approximation is not a SystemStrap component
port.

## Selection and opt-out

Presentation selection order is:

1. Authored saved `is-style-system-*-woo` block style, only for a
   style-capable block.
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

WooCommerce renders the Product Button with Core Button classes and enqueues the
Core Button stylesheet. SystemStrap's shared `.wp-element-button` baseline
therefore remains the native Fill and Outline owner. The companion registers
only these optional, selected Core-style siblings: `button-link-woo`,
`button-pill-woo`, `button-pill-outline-woo`, `button-square-woo`, and
`button-square-outline-woo`. Each saved class belongs to the public root and
targets only its existing interactive child. `system-icon` is excluded because
it is a shared carousel/dialog utility rather than a textual Button style.

The Link, Pill, Pill Outline, Square, and Square Outline siblings mirror their
matching Core Button presentation deltas. The outline siblings additionally
mirror SystemStrap's palette contrast route only for their public Product
Button child. No selected style replaces the child, changes its element type,
or takes ownership of WooCommerce directives or states. No companion
`theme.json` merge is emitted for this block.

WooCommerce natively owns Text and Background support and controls. The
companion adds only the missing `supports.color.gradients` capability. WordPress
registers the standard `gradient` attribute and uses Woo's existing `style`
attribute support while WooCommerce's server renderer consumes the presentation
on its native interactive child. The compatibility normalization does not add
an InspectorControls wrapper, render rewrite, frontend script, or state
override. It remains independent from an explicitly selected optional Button
style.

Within `woocommerce/product-template`, SystemStrap's conditional theme
compatibility lane assigns the Product Button child the existing `small` font
preset only when it has no authored preset or custom font size. This
context-specific baseline is CSS, rather than Global Styles, because `theme.json`
block styles cannot express the Product Template-only descendant boundary.
It remains active without the companion; standalone and single-product actions
remain outside the selector.

## Companion compatibility normalization

Woo compatibility normalization is not a System UI variation. It has three
separate layers:

1. Render/presentation normalization bridges Gutenberg-authored presentation
   only to a source-proven stable rendered element.
2. Global Styles normalization extends compatible, metadata-supported baseline
   data through supported WordPress APIs.
3. Editor capability normalization restores a source-proven Core-compatible
   editor control omitted by a Woo block editor while preserving its native Edit
   component and runtime behavior.

System UI variations remain optional authored presentation treatments only for
style-capable blocks. Baseline presentation compatibility required while this
companion is inactive belongs to SystemStrap's conditional theme compatibility
layer, not this companion.

### Product Template

Product Template registers only `system-panel-woo`, `system-list-woo`, and
`system-list-flush-woo` on `woocommerce/product-template`. The public root is
the rendered Product Template `ul`; the only approved presentation child target
is its direct `li.wc-block-product` card. Native Stack, Grid, and Carousel
remain WooCommerce layout modes. Native Carousel is a terminal Woo-owned
interaction boundary: its layout, scrolling, responsive item sizing, controls,
and Interactivity API directives must not be replaced or rewritten.

System Panel Woo MUST preserve WooCommerce's selected Product Collection
grid/flex layout and responsive column calculation. System List Woo and System
List Flush Woo MUST intentionally use a vertical row stack. Product Image,
Title, Price, Button, and other authored Product Template children retain their
own block ownership.

SystemStrap's conditional theme compatibility layer routes authored Product
Template background/gradient paint to direct public product cards and
normalizes the native Stack seam. System Panel Woo builds on that corrected
base and owns direct-item Panel presentation in Stack, Grid, and Carousel mode.
In Carousel mode it MUST NOT alter the Product Template root or WooCommerce
track geometry. Native Grid retains WooCommerce's grid/flex gap and columns.
System List and System List Flush remain unavailable in Carousel mode because
their parent-level vertical stack contract would corrupt WooCommerce carousel
behavior.

### Product Reviews and Reviews Pagination

Product Reviews and Reviews Pagination are **Class C — no useful Styles UI**.
They do not register block-style controls or depend on saved style classes.
Their final user-facing treatments are selected only through the companion
component mapping: `system-list-woo`, `system-list-flush-woo`, and
`system-panel-woo`. Native WooCommerce is terminal: it receives no companion
Review presentation class.

The modern adapter targets the public Product Review Template `ol > li` review
tree. The legacy adapter targets the public `comments_template()` comment list.
Both preserve Woo review data, rating semantics, comment form submission,
interactivity, and Product/Review schema. Rating remains Woo-owned; the
selected treatment may inherit its color and spacing but must not rewrite it.

The mapping registry retains the seven SystemStrap pagination treatment names
with the `-woo` suffix for future whole-component selection. The public modern
Reviews Pagination and legacy `.woocommerce-pagination` surface receive a
selected mapping through separate adapters. Pagination links, current-page
semantics, disabled state, and Woo navigation behavior remain Woo-owned.

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

Account Navigation, Orders Table, Downloads, Forms, and Notices are Class C
application components. They have no useful block Styles UI and therefore use
only the validated companion component mapping option. Account Navigation may
select Native WooCommerce, System List Woo, or System List Flush Woo. It MUST
NOT claim System Navigation treatment because its public `nav > ul > li > a`
markup lacks the Core Navigation structure required by that component.

The System List Woo master is the rendered standalone System List shell used by
the Archives, Categories, and Pages widgets. Its outer shadow is shared rather
than block-specific: `core-group-system-panel.css` applies
`box-shadow: var(--wp--custom--shadow)` to those `is-style-system-list` roots.
The same file explicitly removes that shadow when a System List is a direct
child of a System Panel, because the Panel owns the composed card shadow.
Therefore the Account System List Woo mapped shell MUST use
`var(--wp--custom--shadow)`; System List Flush Woo remains a flush treatment
and MUST NOT receive standalone Panel chrome.

Orders Table and Downloads may select Native WooCommerce or System Table Panel
Woo. Forms may select Native WooCommerce or System Forms Woo. Notices may
select Native WooCommerce or System Notice Woo. These treatments use public
Woo hooks or the Account page body class and must preserve native semantics,
active state,
`aria-current`, labels, fields, action links/buttons, responsive table data, and
extension-hook content.

Endpoint-specific descendants without a public runtime contract remain Native
or deferred. They must not be styled through inferred selectors.

SystemStrap's conditional theme compatibility layer owns the unselected Account
baseline: theme typography, ordinary spacing, native form controls, semantic
table rhythm, native navigation readability, notices, focus, disabled, invalid,
and responsive behavior. The companion owns only the optional mapped System UI
presentation.

## Native Product Gallery exception

Product Gallery is Native WooCommerce for P0. No Gallery `-woo` style,
component mapping, bridge CSS, render filter, or presentation propagation may
be registered.

Gallery media wrappers, links, thumbnails, selected-thumbnail state, zoom,
lightbox, responsive sizing, crop behavior, and sale-badge relationships remain
WooCommerce-owned.

## Style-capability gate

Every candidate Woo block must be classified before a variation is registered:

- **Class A:** usable Styles UI, saved class round trip, stable public root,
  matching editor/frontend asset loading, and normal editor removal.
- **Class B:** a verified partial style contract plus a narrowly scoped
  compatibility adapter.
- **Class C:** no useful Styles UI; use theme baseline compatibility and a
  companion component mapping/public-boundary adapter.
- **Class D:** private application component; use only safe baseline or stable
  public bridges and never DOM rewriting.

Product Template is Class A. Product Button is Class B. Product Reviews and
Reviews Pagination are Class C. This gate applies to future Woo, BuddyPress,
bbPress, and other SystemStrap companion integrations.

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
