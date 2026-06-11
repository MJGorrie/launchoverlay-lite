=== LaunchOverlay – Coming Soon Banner for Products ===
Contributors:      markjgorrie
Tags:              woocommerce, coming soon, pre-order, product overlay, banner
Requires at least: 6.0
Tested up to:      6.9
Requires PHP:      7.4
Stable tag:        1.1.1
WC requires at least: 7.0
WC tested up to:      9.0
License:           GPLv2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Add "Coming Soon", "Pre-Order", and launch banner overlays to WooCommerce product images. Control purchase availability per product.

== Description ==

**LaunchOverlay** lets you add eye-catching banners to your WooCommerce product images so customers know a product is *Coming Soon*, *Pre-Order*, or *Launching Soon* — before it goes live.

= Features =

* 5 preset banner texts: Coming Soon, Pre-Order, Launching Soon, Sold Out, Available Soon
* 6 colour themes: Dark, Light, Blue, Green, Amber, Red
* Adjustable colours with a visual colour picker
* Banner style or Badge/Pill style
* 7 placement options: Top Left, Top Centre, Top Right, Centre, Bottom Left, Bottom Centre, Bottom Right
* Per-product enable/disable
* Per-product banner text selection
* Hide price display per product
* Disable Add to Cart with configurable replacement message
* Works on shop pages, category pages, and single product pages
* Live preview in the settings page
* WooCommerce HPOS (custom order tables) compatible
* Accessible markup (aria-label on overlay)
* Lightweight — no jQuery dependency, pure CSS rendering

= How It Works =

1. Install and activate the plugin.
2. Go to **WooCommerce → LaunchOverlay** to set global colour and style defaults.
3. Edit any product and click the **LaunchOverlay** tab in the Product Data panel.
4. Enable the overlay, choose a banner text, and optionally disable Add to Cart.
5. Save the product — the banner appears on your store immediately.

= Compatibility =

Works with both classic and block themes. The overlay is injected via the `woocommerce_product_get_image` filter so no theme template modifications are required.

== Installation ==

1. Upload the `launchoverlay` folder to `/wp-content/plugins/`.
2. Activate the plugin via the **Plugins** screen.
3. Go to **WooCommerce → LaunchOverlay** to configure global defaults.
4. Edit any product and use the **LaunchOverlay** tab to enable per-product banners.

== Frequently Asked Questions ==

= Does this work with block themes? =
Yes. The overlay is injected via the `woocommerce_product_get_image` filter which works with both classic and block themes.

= Will it slow down my store? =
No. The plugin enqueues a single CSS file (~3 KB) and a small vanilla JS file only on WooCommerce pages. There are no external requests and no jQuery dependency.

= Can I set a different banner text per product? =
Yes. Every product has its own LaunchOverlay panel where you can choose from the five preset banner labels, disable Add to Cart, hide the price, and set a replacement message.

= Does it work with WooCommerce HPOS? =
Yes. LaunchOverlay declares full compatibility with WooCommerce High Performance Order Storage (custom order tables).

= Can I disable the Add to Cart button without removing the product from my shop? =
Yes. Enable the overlay, tick "Disable Add to Cart", optionally add a replacement message, and save. The product remains visible but cannot be purchased.

== Screenshots ==

1. Product image with "Coming Soon" banner overlay on the shop page.
2. Per-product LaunchOverlay settings panel inside the product editor.
3. Global settings page under WooCommerce → LaunchOverlay with live preview.

== Changelog ==

= 1.1.1 =
* Removed jQuery dependency — overlay JS is now pure vanilla JavaScript.
* Fixed output escaping throughout to meet WordPress coding standards.
* Fixed wp_unslash() usage before sanitisation on all $_POST reads.
* Fixed plugin action links to use properly escaped output.
* Activation hook converted to named function for compatibility.
* Added 5 preset banner text options (up from 3).
* Added 6 colour theme quick-references.
* Updated position labels to British English spelling.
* Minor code style improvements throughout.

= 1.1.0 =
* Improved overlay rendering via woocommerce_product_get_image filter.
* Refactored admin panel with toggle switches and live preview.
* HPOS compatibility declared.
* Accessibility improvements.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.1.1 =
Recommended update — removes jQuery dependency, fixes escaping issues, and adds more banner text options.
