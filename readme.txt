=== WebPlatform Email Connector ===
Contributors: webplatform
Tags: email, transactional email, woocommerce, wp mail
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Send WordPress and WooCommerce transactional email through WebPlatform.

== Description ==

WebPlatform Email Connector routes standard WordPress email through the WebPlatform
delivery service. It works with messages created through `wp_mail()`, including common
WooCommerce transactional notifications.

Features:

* Connects with a dedicated WebPlatform merchant API token.
* Supports HTML and plain-text email.
* Preserves the message subject and Reply-To address.
* Provides an optional connection test.
* Can be enabled or disabled without removing saved settings.
* Does not store third-party email-provider credentials in WordPress.
* Synchronizes WordPress users and WooCommerce orders with WebPlatform.
* Opens the WebPlatform Email Campaigns dashboard from WordPress.

== Installation ==

1. Upload and activate the plugin.
2. Open Settings > WebPlatform Email.
3. Enter the WebPlatform URL and merchant API token.
4. Save the settings and test the connection.
5. Enable email routing.

== Frequently Asked Questions ==

= Does this work with WooCommerce email? =

Yes. WooCommerce transactional messages sent through the standard WordPress mail
function are supported.

= What happens when routing is disabled? =

WordPress continues using its normal mail configuration.

== Changelog ==

= 1.1.0 =
* Added audience and order synchronization.
* Added a direct link to the WebPlatform Email Campaigns dashboard.

= 1.0.0 =
* Initial release.
