<?php
/**
 * Plugin Name: WebPlatform Email Connector
 * Plugin URI: https://webplatform.co.in/plugins
 * Description: Send WordPress and WooCommerce transactional email through WebPlatform.
 * Version: 1.3.1
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Author: WebPlatform
 * Author URI: https://webplatform.co.in/
 * License: GPL-2.0-or-later
 * Text Domain: webplatform-email-connector
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WPEMAIL_VERSION', '1.3.1');
define('WPEMAIL_FILE', __FILE__);
define('WPEMAIL_DIR', plugin_dir_path(__FILE__));
define('WPEMAIL_URL', plugin_dir_url(__FILE__));

require_once WPEMAIL_DIR . 'includes/class-webplatform-email-api.php';
require_once WPEMAIL_DIR . 'includes/class-webplatform-email-admin.php';
require_once WPEMAIL_DIR . 'includes/class-webplatform-email-mailer.php';
require_once WPEMAIL_DIR . 'includes/class-webplatform-email-sync.php';
require_once WPEMAIL_DIR . 'includes/class-webplatform-email-woocommerce.php';

function webplatform_email_boot()
{
    load_plugin_textdomain('webplatform-email-connector', false, dirname(plugin_basename(__FILE__)) . '/languages');

    $client = new WebPlatform_Email_API();
    new WebPlatform_Email_Admin($client);
    new WebPlatform_Email_Mailer($client);
    if (class_exists('WooCommerce')) {
        new WebPlatform_Email_WooCommerce();
    }
}
add_action('plugins_loaded', 'webplatform_email_boot');

/**
 * Display the current WebPlatform brand on this plugin's settings screen.
 */
function webplatform_email_admin_brand()
{
    if (!current_user_can('manage_options') || !isset($_GET['page'])) {
        return;
    }

    $page = sanitize_key(wp_unslash($_GET['page']));
    if ('webplatform-email' !== $page) {
        return;
    }

    ?>
    <div class="webplatform-plugin-brand" style="display:flex;align-items:center;gap:12px;margin:16px 0 8px;padding:12px 16px;background:#fff;border:1px solid #dcdcde;border-radius:8px;box-sizing:border-box;max-width:1100px">
        <img src="<?php echo esc_url(WPEMAIL_URL . 'assets/brand-icon.png'); ?>" width="48" height="48" alt="" aria-hidden="true" style="display:block;width:48px;height:48px;object-fit:contain">
        <img src="<?php echo esc_url(WPEMAIL_URL . 'assets/brand-wordmark.png'); ?>" width="180" height="35" alt="<?php echo esc_attr__('WebPlatform', 'webplatform-email-connector'); ?>" style="display:block;width:180px;max-width:45vw;height:auto">
    </div>
    <?php
}
add_action('admin_notices', 'webplatform_email_admin_brand');
