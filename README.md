# WebPlatform Email Connector

The WebPlatform Email Connector routes WordPress email through WebPlatform. It also supports common WooCommerce transactional notifications that use WordPress's standard `wp_mail()` function.

## Useful links

- [Plugin setup and troubleshooting guide](https://webplatform.co.in/help/plugins)
- [WebPlatform Email Connector on WordPress.org](https://wordpress.org/plugins/webplatform-email-connector/)
- [Explore WebPlatform](https://webplatform.co.in/)
- [Plans and pricing](https://webplatform.co.in/pricing)

## Install and connect

1. Download the plugin ZIP from the official WordPress.org listing or your WebPlatform plugin account.
2. In WordPress, go to **Plugins → Add New → Upload Plugin**, upload the ZIP, and activate it.
3. Open **Settings → WebPlatform Email**.
4. Enter your WebPlatform URL and merchant API token, save the settings, and test the connection.
5. Send a test message and check that it arrives before enabling routing for a live store.

For the full setup flow, see the [WebPlatform plugin guide](https://webplatform.co.in/help/plugins).

## If a WooCommerce email does not arrive

A message shown as “sent” in WordPress does not always mean it reached the recipient's inbox. Work through these checks before changing the mail setup:

1. In **WooCommerce → Settings → Emails**, confirm the affected notification is enabled and its recipient is correct.
2. Send a test message from the connector and check the connection status.
3. Check the recipient's spam/junk folder and the delivery logs available from your email service.
4. If the provider accepted the message but the recipient did not receive it, check sender-domain authentication (SPF/DKIM/DMARC), bounces, and suppression records with the email provider.
5. Test one order notification on a staging site or with a controlled test order before relying on the change in production.

These checks help separate a WordPress/WooCommerce trigger problem from a delivery or inbox-filtering problem. If you need to switch the connector off while investigating, disabling WebPlatform routing returns WordPress to its normal email configuration.

## What the connector sends

When routing is enabled, outgoing email is sent to the WebPlatform delivery API. User and WooCommerce order information is synchronised only when you initiate a sync from the plugin. Use the connector in line with your site's privacy notice and only synchronise contacts for campaigns when you have the appropriate consent.

See the [plugin listing](https://wordpress.org/plugins/webplatform-email-connector/) for the current feature details, requirements, and changelog.
