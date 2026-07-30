<?php
/**
 * Plugin Name: WebPlatform Email Connector
 * Plugin URI: https://webplatform.co.in/plugins
 * Description: Send WordPress and WooCommerce transactional email through WebPlatform.
 * Version: 1.2.0
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

define('WPEMAIL_VERSION', '1.2.0');
define('WPEMAIL_FILE', __FILE__);
define('WPEMAIL_DIR', plugin_dir_path(__FILE__));

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
