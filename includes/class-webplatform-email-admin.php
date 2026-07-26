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
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_post_webplatform_email_test', array($this, 'test'));
        add_action('admin_post_webplatform_email_activate', array($this, 'activate'));
        add_action('admin_post_webplatform_email_validate', array($this, 'validate_license'));
        add_action('admin_post_webplatform_email_deactivate', array($this, 'deactivate'));
    }

    public function menu()
    {
        add_options_page(
            __('WebPlatform Email', 'webplatform-email'),
            __('WebPlatform Email', 'webplatform-email'),
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
            'license_instance_id' => $old['license_instance_id'],
            'license_activation_token' => $old['license_activation_token'],
            'license_status' => $old['license_status'],
        );
    }

    public function render()
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        $settings = $this->client->settings();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('WebPlatform Email', 'webplatform-email'); ?></h1>
            <?php settings_errors('webplatform_email'); ?>
            <p><?php esc_html_e('Route WordPress and WooCommerce transactional messages through your WebPlatform account.', 'webplatform-email'); ?></p>

            <h2><?php esc_html_e('Activation', 'webplatform-email'); ?></h2>
            <p><?php esc_html_e('Status:', 'webplatform-email'); ?> <strong><?php echo esc_html(ucfirst($settings['license_status'])); ?></strong></p>
            <?php $this->action_form('webplatform_email_activate', __('Activate', 'webplatform-email'), 'primary'); ?>
            <?php if (!empty($settings['license_activation_token'])) : ?>
                <?php $this->action_form('webplatform_email_validate', __('Check activation', 'webplatform-email')); ?>
                <?php $this->action_form('webplatform_email_deactivate', __('Deactivate', 'webplatform-email')); ?>
            <?php endif; ?>

            <form method="post" action="options.php">
                <?php settings_fields('webplatform_email_group'); ?>
                <table class="form-table" role="presentation">
                    <tr><th scope="row"><label for="wpe-base-url"><?php esc_html_e('WebPlatform URL', 'webplatform-email'); ?></label></th>
                        <td><input id="wpe-base-url" class="regular-text" type="url" name="webplatform_email_settings[base_url]" value="<?php echo esc_attr($settings['base_url']); ?>" required></td></tr>
                    <tr><th scope="row"><label for="wpe-token"><?php esc_html_e('Merchant API token', 'webplatform-email'); ?></label></th>
                        <td><input id="wpe-token" class="regular-text" type="password" name="webplatform_email_settings[access_token]" value="" autocomplete="new-password" placeholder="<?php echo !empty($settings['access_token']) ? esc_attr__('Saved — enter only to replace', 'webplatform-email') : ''; ?>"></td></tr>
                    <tr><th scope="row"><?php esc_html_e('Email routing', 'webplatform-email'); ?></th>
                        <td><label><input type="checkbox" name="webplatform_email_settings[enabled]" value="1" <?php checked(!empty($settings['enabled'])); ?>> <?php esc_html_e('Send WordPress email through WebPlatform', 'webplatform-email'); ?></label></td></tr>
                    <tr><th scope="row"><label for="wpe-timeout"><?php esc_html_e('Request timeout', 'webplatform-email'); ?></label></th>
                        <td><input id="wpe-timeout" type="number" min="5" max="30" name="webplatform_email_settings[timeout]" value="<?php echo esc_attr($settings['timeout']); ?>"> <?php esc_html_e('seconds', 'webplatform-email'); ?></td></tr>
                </table>
                <?php submit_button(); ?>
            </form>
            <hr>
            <h2><?php esc_html_e('Connection test', 'webplatform-email'); ?></h2>
            <?php $this->action_form('webplatform_email_test', __('Test connection', 'webplatform-email')); ?>
        </div>
        <?php
    }

    public function test()
    {
        $this->authorize('webplatform_email_test');
        $result = $this->client->status();
        $this->notice(is_wp_error($result) ? $result->get_error_message() : __('Connected successfully.', 'webplatform-email'), !is_wp_error($result));
    }

    public function activate()
    {
        $this->authorize('webplatform_email_activate');
        $settings = $this->client->settings();
        $instance_id = $settings['license_instance_id'] ?: wp_generate_uuid4();
        $result = $this->client->activate_license($instance_id);
        if (!is_wp_error($result)) {
            $settings['license_instance_id'] = $instance_id;
            $settings['license_activation_token'] = sanitize_text_field($result['data']['activation_token'] ?? $result['token'] ?? '');
            $settings['license_status'] = sanitize_key($result['data']['status'] ?? $result['status'] ?? 'active');
            update_option(WebPlatform_Email_API::OPTION_KEY, $settings, false);
        }
        $this->notice(is_wp_error($result) ? $result->get_error_message() : __('Activation completed.', 'webplatform-email'), !is_wp_error($result));
    }

    public function validate_license()
    {
        $this->authorize('webplatform_email_validate');
        $settings = $this->client->settings();
        $result = $this->client->validate_license($settings['license_instance_id'], $settings['license_activation_token']);
        if (!is_wp_error($result)) {
            $settings['license_status'] = sanitize_key($result['data']['status'] ?? $result['status'] ?? 'active');
            update_option(WebPlatform_Email_API::OPTION_KEY, $settings, false);
        }
        $this->notice(is_wp_error($result) ? $result->get_error_message() : __('Activation is valid.', 'webplatform-email'), !is_wp_error($result));
    }

    public function deactivate()
    {
        $this->authorize('webplatform_email_deactivate');
        $settings = $this->client->settings();
        $result = $this->client->deactivate_license($settings['license_instance_id'], $settings['license_activation_token']);
        if (!is_wp_error($result)) {
            $settings['license_activation_token'] = '';
            $settings['license_status'] = 'inactive';
            update_option(WebPlatform_Email_API::OPTION_KEY, $settings, false);
        }
        $this->notice(is_wp_error($result) ? $result->get_error_message() : __('Activation removed.', 'webplatform-email'), !is_wp_error($result));
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

    private function authorize($action)
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You are not allowed to perform this action.', 'webplatform-email'));
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
