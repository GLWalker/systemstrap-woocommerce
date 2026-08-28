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

Product Collection geometry and Product Template presentation are independent
axes except for the width-sensitive Grid normalization defined for System List
Woo and System List Flush Woo below. WooCommerce retains query selection,
saved `displayLayout` attributes, Stack/Carousel behavior, track behavior,
scrolling, and interactivity. The selected Product Template style owns the
mapped item presentation:

| Product Collection | Default | System Panel Woo | System List Woo | System List Flush Woo |
| --- | --- | --- | --- | --- |
| Stack | Native stacked Woo products | Existing Panel presentation on each product | Full-width List media rows | The same media rows with Flush chrome |
| Grid | Native Woo grid | Existing Panel presentation on each product | List-derived media cards within the Woo grid | The same media cards with Flush chrome |
| Carousel | Native Woo carousel | Existing Panel presentation on each slide | List-derived media cards within Woo slides | The same media cards with Flush chrome |

System Panel Woo MUST preserve WooCommerce's selected Product Collection
grid/flex layout and responsive column calculation. System List Woo and System
List Flush Woo are width-sensitive media-object presentations: their Grid root
MUST normalize saved four-, five-, and six-column selections to a three-column
maximum above 900px and MUST render one column at and below 900px, including
at and below 600px. Woo's saved column selection remains untouched. Default
and Panel treatments are not subject to this normalization. Product Image,
Title, Price, Button, and other authored Product Template children retain their
own block ownership.

When a selected List treatment has a direct Product Image child, its direct
product card maps the existing Latest Posts System List media-object contract to
the public Woo blocks. `--wp--custom--medium-width`, emitted by SystemStrap
from WordPress Media Settings `medium_size_w`, is the authoritative maximum
image lane width. The image retains its existing Woo aspect-ratio behavior; the
remaining direct children occupy a compact, start-aligned content lane with the
existing SystemStrap spacing tokens. At the established 600px narrow-screen
breakpoint, Product Image remains primary commerce content at the dynamic
Thumbnail width while the media row remains intact.

Stack List uses the standalone List shell and row seams. Grid and Carousel List
apply that List-derived shell to each direct product card, never to the Grid
root or Carousel track; their independent cards do not acquire Stack seams.
Flush removes the outer border, radius, clipping, and shadow while retaining the
List row surface and applicable seams. A direct System Panel parent owns the
composed shell: nested List roots/cards must route to the nested System UI
surface and suppress additional List shadow/chrome.

SystemStrap's conditional theme compatibility layer routes authored Product
Template background/gradient paint to direct public product cards and
normalizes the native Stack seam. System Panel Woo builds on that corrected
base and owns direct-item Panel presentation in Stack, Grid, and Carousel mode.
In Carousel mode it MUST NOT alter the Product Template root or WooCommerce
track geometry. Native Grid retains WooCommerce's grid/flex gap and columns.
System List and System List Flush remain available in Carousel mode only through
the direct product-card boundary. They MUST NOT style the WooCommerce carousel
track or modify its container-query, scrolling, sizing, navigation, or
interactivity contract.

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

System List Woo maps the review list to the Comments List shell and its direct
review items to List rows. System List Flush Woo maps that same public review
list to the generic flush List shell and direct review items to flush rows.
System Panel Woo maps each top-level review item to a Comments Panel. Nested
reply lists retain Woo markup and receive only the corresponding comments-thread
rail, indentation, and spacing treatment. Author, metadata, content, rating,
and pagination remain baseline or Woo-owned except for inherited presentation
color and spacing.

The mapping registry retains the seven validated SystemStrap pagination
treatments with the `-woo` suffix: System UI Pagination, Outline, Pill, Pill
Outline, Square, Square Outline, and Badge. The modern Reviews Pagination
adapter adds the selected class to Woo's public pagination wrapper. The Reviews
adapter adds that same class to the legacy Reviews wrapper, where it scopes only
the public `.woocommerce-pagination` descendant. Pagination links, current-page
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

Account Navigation is the sole current Account Class C application component.
It has no useful block Styles UI and therefore uses only the validated companion
component mapping option. It may select Native WooCommerce, System List Woo, or
System List Flush Woo. It MUST NOT claim System Navigation treatment because
its public `nav > ul > li > a` markup lacks the Core Navigation structure
required by that component.

The System List Woo master is the rendered standalone System List shell used by
the Archives, Categories, and Pages widgets. Its outer shadow is shared rather
than block-specific: `core-group-system-panel.css` applies
`box-shadow: var(--wp--custom--shadow)` to those `is-style-system-list` roots.
The same file explicitly removes that shadow when a System List is a direct
child of a System Panel, because the Panel owns the composed card shadow.
Therefore the Account System List Woo mapped shell MUST use
`var(--wp--custom--shadow)`; System List Flush Woo remains a flush treatment
and MUST NOT receive standalone Panel chrome.

Account forms and notices remain owned by the SystemStrap theme compatibility
baseline and WooCommerce. They MUST NOT receive a companion mapping or
companion-only presentation CSS. Orders Table and Downloads structural
treatments are deferred: no independent Account-table mapping may be exposed
until one unified Woo Tables presentation decision has a validated master and
public-DOM contract.

Endpoint-specific descendants without a public runtime contract remain Native
or deferred. They must not be styled through inferred selectors.

SystemStrap's conditional theme compatibility layer owns the Account baseline:
theme typography, ordinary spacing, native form controls, semantic table rhythm,
native navigation readability, notices, focus, disabled, invalid, and responsive
behavior. The companion owns only the optional Account Navigation presentation.

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
