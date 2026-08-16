# WordPress 7.1 compatibility validation

WordPress.org requested plugin authors to validate compatibility with WordPress 7.1 before changing the `Tested up to` value in `readme.txt`.

## Test matrix

Run the checks on the latest WordPress 7.1 release candidate (and repeat on the final 7.1 release):

- PHP 7.4, 8.0, 8.2, and 8.3 where available.
- WordPress 7.1 with the default theme.
- WordPress 7.1 with WooCommerce enabled.

## Required checks

1. Install and activate WebPlatform Email Connector on a clean WordPress site.
2. Confirm Settings > WebPlatform Email loads without PHP warnings, notices, or JavaScript errors.
3. Save the WebPlatform URL and merchant API token and reload the settings page.
4. Run the connection test.
5. Enable routing and send a plain-text `wp_mail()` message.
6. Send an HTML `wp_mail()` message and verify subject, recipient, body, and Reply-To behavior.
7. Test multiple recipients.
8. Disable routing and confirm WordPress returns to its normal mail path.
9. With WooCommerce enabled, trigger at least one transactional email.
10. Run manual WordPress-user synchronization.
11. With WooCommerce enabled, run order synchronization.
12. Verify the Email Campaigns dashboard link opens the expected WebPlatform destination.
13. Run WordPress Plugin Check and resolve new errors before release.
14. Review the PHP error log and browser console after the smoke tests.

## Release gate

Only after the checks above pass:

1. Change `Tested up to: 7.0` to `Tested up to: 7.1` in `readme.txt`.
2. Do not change `Stable tag` or the plugin version for a metadata-only compatibility declaration unless code also changes.
3. Commit the readme update to the WordPress.org SVN `trunk` and the current stable tag as appropriate for the release workflow.
4. Verify the WordPress.org plugin page shows WordPress 7.1 compatibility.
