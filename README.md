=== QIOG Boat Charter Builder ===
Contributors: QIOG Cayman
Tags: booking, charter, boat, builder, drag-and-drop, addons
Requires at least: 5.0
Tested up to: 6.5
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Build Your Charter – drag & drop stops and addons with live pricing.

== Description ==
QIOG Boat Charter Builder allows users to create boat charters by dragging and dropping stops and addons with live pricing and AJAX-powered booking and checkout. Includes a shortcode-based frontend builder and a checkout shortcode.

== Installation ==
1. Upload the `qiog-boat-charter-Builder` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the shortcode `[qiog_charter_builder]` to display the charter builder on any page.
4. Create a page with the slug `checkout` (or update the `checkout_url` in the localized script) to use the built-in checkout shortocode: `[qiog_charter_checkout]`.

== Frequently Asked Questions ==
= Which shortcodes are available? =
- ` [qiog_charter_builder]` — Displays the builder UI.
- ` [qiog_charter_checkout]` — Displays the checkout page.

= How do I change the AJAX endpoints? =
The plugin localizes the `qiogCharter` script with `ajax_url` pointing to `admin-ajax.php`. Handlers are in `includes/ajax-stops-addons.php`, `includes/ajax-booking.php`, and `includes/ajax-checkout.php`.

== Screenshots ==
1. Builder interface — drag & drop stops and addons.
2. Admin bookings page.
3. Checkout page with live price breakdown.

== Changelog ==
= 1.0.0 =
* Initial release.

== Upgrade Notice ==
= 1.0.0 =
Initial release.

== Arbitrary section ==
If you need support, open an issue in your distribution channel or contact the plugin author.
