<?php

if (!defined('ABSPATH')) {
    exit;
}

class WebPlatform_Email_Admin
{
    private $client;

    public function __construct(WebPlatform_Email_API $client)
    {
        $this->client = $client;
        add_action('admin_menu', array($this, 'menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_post_webplatform_email_test', array($this, 'test'));
        add_action('admin_post_webplatform_email_sync', array($this, 'sync'));
    }

    public function enqueue_assets($hook_suffix)
    {
        if ('settings_page_webplatform-email' !== $hook_suffix) {
            return;
        }

        wp_enqueue_style(
            'webplatform-email-admin',
            plugins_url('assets/admin.css', WPEMAIL_FILE),
            array(),
            WPEMAIL_VERSION
        );
    }

    public function menu()
    {
        add_options_page(
            __('WebPlatform Email', 'webplatform-email-connector'),
            __('WebPlatform Email', 'webplatform-email-connector'),
            'manage_options',
            'webplatform-email',
            array($this, 'render')
        );
    }

    public function register_settings()
    {
        register_setting('webplatform_email_group', WebPlatform_Email_API::OPTION_KEY, array(
            'type' => 'array',
            'sanitize_callback' => array($this, 'sanitize'),
            'default' => array(),
        ));
    }

    public function sanitize($input)
    {
        $old = $this->client->settings();
        $token = isset($input['access_token']) ? trim(sanitize_text_field(wp_unslash($input['access_token']))) : '';
        return array(
            'base_url' => isset($input['base_url']) ? untrailingslashit(esc_url_raw($input['base_url'])) : 'https://webplatform.co.in',
            'access_token' => '' !== $token ? $token : $old['access_token'],
            'enabled' => !empty($input['enabled']) ? 1 : 0,
            'timeout' => isset($input['timeout']) ? max(5, min(30, absint($input['timeout']))) : 20,
        );
    }

    public function render()
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        $settings = $this->client->settings();
        $sync_status = $this->client->configured() ? $this->client->connector_status() : null;
        $sync_data = !is_wp_error($sync_status) ? (array) ($sync_status['data'] ?? array()) : array();
        $configured = $this->client->configured();
        $enabled = !empty($settings['enabled']);
        ?>
        <div class="wrap wpe-wrap">
            <div class="wpe-hero">
                <div>
                    <h1><?php esc_html_e('WebPlatform Email', 'webplatform-email-connector'); ?></h1>
                    <p><?php esc_html_e('Reliable transactional email for WordPress and WooCommerce.', 'webplatform-email-connector'); ?></p>
                </div>
                <div class="wpe-statuses" aria-label="<?php esc_attr_e('Connector status', 'webplatform-email-connector'); ?>">
                    <span class="wpe-status <?php echo $configured ? 'is-success' : 'is-muted'; ?>">
                        <span class="dashicons <?php echo $configured ? 'dashicons-yes-alt' : 'dashicons-marker'; ?>" aria-hidden="true"></span>
                        <?php echo $configured ? esc_html__('Account connected', 'webplatform-email-connector') : esc_html__('Setup required', 'webplatform-email-connector'); ?>
                    </span>
                    <span class="wpe-status <?php echo $enabled ? 'is-success' : 'is-muted'; ?>">
                        <span class="dashicons <?php echo $enabled ? 'dashicons-email-alt' : 'dashicons-email-alt2'; ?>" aria-hidden="true"></span>
                        <?php echo $enabled ? esc_html__('Email routing on', 'webplatform-email-connector') : esc_html__('Email routing off', 'webplatform-email-connector'); ?>
                    </span>
                </div>
            </div>
            <?php settings_errors('webplatform_email'); ?>

            <form method="post" action="options.php" class="wpe-card">
                <?php settings_fields('webplatform_email_group'); ?>
                <div class="wpe-card-heading">
                    <span class="wpe-step">1</span>
                    <div><h2><?php esc_html_e('Connect your account', 'webplatform-email-connector'); ?></h2><p><?php esc_html_e('Add the API token from your WebPlatform account to authorize this website.', 'webplatform-email-connector'); ?></p></div>
                </div>
                <div class="wpe-fields">
                    <label for="wpe-token"><?php esc_html_e('Merchant API token', 'webplatform-email-connector'); ?></label>
                    <input id="wpe-token" class="regular-text" type="password" name="webplatform_email_settings[access_token]" value="" autocomplete="new-password" placeholder="<?php echo !empty($settings['access_token']) ? esc_attr__('Token saved — enter a new token only to replace it', 'webplatform-email-connector') : esc_attr__('Paste your API token', 'webplatform-email-connector'); ?>">
                    <p class="description"><?php esc_html_e('Your token is stored securely in WordPress and is never displayed again.', 'webplatform-email-connector'); ?></p>

                    <details class="wpe-advanced">
                        <summary><?php esc_html_e('Advanced settings', 'webplatform-email-connector'); ?></summary>
                        <div class="wpe-advanced-grid">
                            <div><label for="wpe-base-url"><?php esc_html_e('WebPlatform URL', 'webplatform-email-connector'); ?></label><input id="wpe-base-url" class="regular-text" type="url" name="webplatform_email_settings[base_url]" value="<?php echo esc_attr($settings['base_url']); ?>" required></div>
                            <div><label for="wpe-timeout"><?php esc_html_e('Request timeout (seconds)', 'webplatform-email-connector'); ?></label><input id="wpe-timeout" class="small-text" type="number" min="5" max="30" name="webplatform_email_settings[timeout]" value="<?php echo esc_attr($settings['timeout']); ?>"></div>
                        </div>
                    </details>
                </div>

                <div class="wpe-divider"></div>
                <div class="wpe-card-heading">
                    <span class="wpe-step">2</span>
                    <div><h2><?php esc_html_e('Enable email delivery', 'webplatform-email-connector'); ?></h2><p><?php esc_html_e('Route messages sent by WordPress and WooCommerce through WebPlatform.', 'webplatform-email-connector'); ?></p></div>
                </div>
                <label class="wpe-toggle-row">
                    <span><strong><?php esc_html_e('Send email through WebPlatform', 'webplatform-email-connector'); ?></strong><small><?php esc_html_e('You can turn this off at any time without removing your settings.', 'webplatform-email-connector'); ?></small></span>
                    <span class="wpe-switch"><input type="checkbox" name="webplatform_email_settings[enabled]" value="1" <?php checked($enabled); ?>><span aria-hidden="true"></span></span>
                </label>
                <div class="wpe-card-actions">
                    <?php submit_button($configured ? __('Save settings', 'webplatform-email-connector') : __('Save and connect', 'webplatform-email-connector'), 'primary', 'submit', false); ?>
                    <?php if ($configured) : $this->action_link('webplatform_email_test', __('Test connection', 'webplatform-email-connector')); endif; ?>
                </div>
            </form>

            <section class="wpe-card <?php echo $configured ? '' : 'is-disabled'; ?>">
                <div class="wpe-card-heading">
                    <span class="wpe-step">3</span>
                    <div><h2><?php esc_html_e('Sync your audience', 'webplatform-email-connector'); ?></h2><p><?php esc_html_e('Bring WordPress contacts and WooCommerce orders into WebPlatform for campaigns and reporting.', 'webplatform-email-connector'); ?></p></div>
                </div>
                <div class="wpe-sync-summary">
                    <div><strong><?php echo esc_html(number_format_i18n(absint($sync_data['contacts'] ?? 0))); ?></strong><span><?php esc_html_e('Contacts', 'webplatform-email-connector'); ?></span></div>
                    <div><strong><?php echo esc_html(number_format_i18n(absint($sync_data['orders'] ?? 0))); ?></strong><span><?php esc_html_e('Orders', 'webplatform-email-connector'); ?></span></div>
                </div>
                <div class="wpe-card-actions">
                    <?php if ($configured) : ?>
                        <?php $this->action_form('webplatform_email_sync', __('Sync WordPress data', 'webplatform-email-connector'), 'primary'); ?>
                    <?php else : ?>
                        <button class="button button-primary" disabled><?php esc_html_e('Connect account to sync', 'webplatform-email-connector'); ?></button>
                    <?php endif; ?>
                    <?php if (!empty($sync_data['dashboards']['email'])) : ?>
                        <a class="button" href="<?php echo esc_url($sync_data['dashboards']['email']); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Open Email Campaigns', 'webplatform-email-connector'); ?> <span class="dashicons dashicons-external" aria-hidden="true"></span></a>
                    <?php endif; ?>
                </div>
            </section>
        </div>
        <?php
    }

    public function test()
    {
        $this->authorize('webplatform_email_test');
        $result = $this->client->status();
        $this->notice(is_wp_error($result) ? $result->get_error_message() : __('Connected successfully.', 'webplatform-email-connector'), !is_wp_error($result));
    }

    public function sync()
    {
        $this->authorize('webplatform_email_sync');
        $result = $this->client->sync_wordpress(WebPlatform_Email_Sync::payload());
        $this->notice(
            is_wp_error($result) ? $result->get_error_message() : __('WordPress contacts and orders synchronized.', 'webplatform-email-connector'),
            !is_wp_error($result)
        );
    }

    private function action_form($action, $label, $class = 'secondary')
    {
        ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline-block;margin-right:8px">
            <input type="hidden" name="action" value="<?php echo esc_attr($action); ?>">
            <?php wp_nonce_field($action); ?>
            <?php submit_button($label, $class, 'submit', false); ?>
        </form>
        <?php
    }

    private function action_link($action, $label, $class = 'secondary')
    {
        $url = wp_nonce_url(
            add_query_arg('action', $action, admin_url('admin-post.php')),
            $action
        );
        ?>
        <a class="button button-<?php echo esc_attr($class); ?>" href="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></a>
        <?php
    }

    private function authorize($action)
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You are not allowed to perform this action.', 'webplatform-email-connector'));
        }
        check_admin_referer($action);
    }

    private function notice($message, $success)
    {
        add_settings_error('webplatform_email', 'webplatform_email_notice', $message, $success ? 'success' : 'error');
        set_transient('settings_errors', get_settings_errors(), 30);
        wp_safe_redirect(admin_url('options-general.php?page=webplatform-email'));
        exit;
    }
}
