<?php

if (!defined('ABSPATH')) {
    exit;
}

class WebPlatform_Email_API
{
    const OPTION_KEY = 'webplatform_email_settings';

    public function settings()
    {
        return wp_parse_args(get_option(self::OPTION_KEY, array()), array(
            'base_url' => 'https://webplatform.co.in',
            'access_token' => '',
            'enabled' => 0,
            'timeout' => 20,
            'license_instance_id' => '',
            'license_activation_token' => '',
            'license_status' => 'inactive',
        ));
    }

    public function configured()
    {
        $settings = $this->settings();
        return !empty($settings['base_url']) && !empty($settings['access_token']);
    }

    public function status()
    {
        return $this->request('GET', '/api/merchant/email/status');
    }

    public function send($payload)
    {
        return $this->request('POST', '/api/merchant/email/send', $payload);
    }

    public function activate_license($instance_id)
    {
        return $this->request('POST', '/api/plugin-license/activate', array(
            'product_key' => 'webplatform-email',
            'plugin' => 'webplatform-email',
            'site_url' => home_url('/'),
            'version' => WPEMAIL_VERSION,
            'instance_id' => $instance_id,
        ));
    }

    public function validate_license($instance_id, $activation_token)
    {
        return $this->request('POST', '/api/plugin-license/validate', array(
            'product_key' => 'webplatform-email',
            'plugin' => 'webplatform-email',
            'site_url' => home_url('/'),
            'version' => WPEMAIL_VERSION,
            'instance_id' => $instance_id,
            'activation_token' => $activation_token,
        ), $activation_token);
    }

    public function deactivate_license($instance_id, $activation_token)
    {
        return $this->request('POST', '/api/plugin-license/deactivate', array(
            'product_key' => 'webplatform-email',
            'plugin' => 'webplatform-email',
            'site_url' => home_url('/'),
            'version' => WPEMAIL_VERSION,
            'instance_id' => $instance_id,
            'activation_token' => $activation_token,
        ), $activation_token);
    }

    private function request($method, $path, $body = null, $bearer = '')
    {
        $settings = $this->settings();
        $token = '' !== $bearer ? $bearer : trim($settings['access_token']);
        if (empty($settings['base_url']) || '' === $token) {
            return new WP_Error('webplatform_email_not_configured', __('WebPlatform Email is not configured.', 'webplatform-email'));
        }

        $args = array(
            'method' => $method,
            'timeout' => max(5, min(30, absint($settings['timeout']))),
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'User-Agent' => 'WebPlatform-Email-WordPress/' . WPEMAIL_VERSION,
            ),
        );
        if (null !== $body) {
            $args['body'] = wp_json_encode($body);
        }

        $response = wp_remote_request(untrailingslashit(esc_url_raw($settings['base_url'])) . $path, $args);
        if (is_wp_error($response)) {
            return $response;
        }

        $status = wp_remote_retrieve_response_code($response);
        $decoded = json_decode(wp_remote_retrieve_body($response), true);
        if (!is_array($decoded)) {
            return new WP_Error('webplatform_email_invalid_response', __('WebPlatform returned an invalid response.', 'webplatform-email'));
        }
        if ($status < 200 || $status >= 300 || empty($decoded['success'])) {
            $message = isset($decoded['message']) ? sanitize_text_field($decoded['message']) : sprintf(
                /* translators: %d: HTTP response status code. */
                __('WebPlatform request failed with HTTP %d.', 'webplatform-email'),
                $status
            );
            return new WP_Error('webplatform_email_api_error', $message, array('status' => $status));
        }

        return $decoded;
    }
}
