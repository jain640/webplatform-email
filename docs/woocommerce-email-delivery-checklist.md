# WooCommerce Email Delivery: A Practical Troubleshooting Checklist

When a customer says an order confirmation never arrived, the message may have failed at several different points. WooCommerce can generate the email correctly while WordPress's mail transport, the sending provider, or the recipient's mail server causes the problem later.

Work through the checks below before changing a live store's email setup.

## 1. Confirm that WooCommerce should send the email

Open **WooCommerce → Settings → Emails** and check the relevant notification.

- Confirm that the notification is enabled.
- Check which order status triggers it.
- Review the recipient settings for admin notifications.
- Use a test order to confirm that the expected status transition actually occurs.

A completed checkout does not mean every notification type should fire. Match the email to the order state that triggers it.

## 2. Check the order and application logs

Open the affected order and review its status notes. Then check WooCommerce logs under **WooCommerce → Status → Logs** for errors around the same time.

Record the timestamp, notification type and order status. Avoid copying customer names, addresses, order contents or other personal information into public bug reports.

## 3. Separate generation from delivery

A successful call to WordPress's `wp_mail()` does not prove that the message reached the inbox. It generally indicates that the configured mail transport accepted the message for processing.

Check each layer separately:

1. Did WooCommerce trigger the notification?
2. Did WordPress attempt to hand off the message?
3. Did the sending service accept or reject it?
4. What does the provider report for delivery, bounce or suppression?
5. Was the message filtered or quarantined by the recipient's mail system?

Use provider logs where available rather than repeatedly sending the same test email.

## 4. Verify the sending domain

If messages are accepted but frequently land in spam, review the sending domain's SPF and DKIM configuration and consider whether a DMARC policy is appropriate for the domain's setup.

Make sure only the intended SPF record exists for the domain, and follow the sending provider's exact DNS instructions. DNS changes can take time to propagate, so verify the result before changing other settings.

## 5. Test changes safely

Use a staging store or a controlled test order when possible. Test the important email types individually, including new-order notifications and customer processing or completed-order emails.

Change one thing at a time and keep a short record of what changed and what the test showed. If you switch mail routing, confirm that you can return to the previous configuration before enabling it on a busy store.

For stores using the **WebPlatform Email Connector**, follow the [WebPlatform plugin setup guide](https://webplatform.co.in/help/plugins) to review the connection and routing configuration. Run a controlled test before enabling routing for live customer messages.

## 6. Avoid accidental duplicate sends

If a queue or scheduled task retries after a timeout, first check whether the provider accepted the original request. Blindly resending can create duplicate customer notifications.

For recurring failures, record the error, timestamp and notification type, then retry only after the underlying cause is understood. Never put API tokens or full customer email contents in public logs.

## Quick checklist

- [ ] The relevant WooCommerce notification is enabled.
- [ ] The order reached the status that triggers it.
- [ ] WooCommerce and WordPress logs have been checked.
- [ ] The mail transport and provider logs have been checked separately.
- [ ] Sending-domain DNS settings have been verified.
- [ ] A controlled test passed after the change.
- [ ] The previous configuration can be restored if needed.
- [ ] Logs and support notes contain no exposed customer data or secrets.

The useful habit is to trace one message end to end before changing several settings at once. It makes the root cause easier to identify and reduces the risk of introducing a second problem while fixing the first.
