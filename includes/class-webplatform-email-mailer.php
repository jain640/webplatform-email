<?php

if (!defined('ABSPATH')) {
    exit;
}

class WebPlatform_Email_Mailer
{
    private $client;

    public function __construct(WebPlatform_Email_API $client)
    {
        $this->client = $client;
        add_filter('pre_wp_mail', array($this, 'route_mail'), 10, 2);
    }

    public function route_mail($return, $attributes)
    {
        $settings = $this->client->settings();
        if (empty($settings['enabled']) || !$this->client->configured()) {
            return $return;
        }

        $recipients = is_array($attributes['to']) ? $attributes['to'] : explode(',', (string) $attributes['to']);
        $recipients = array_values(array_filter(array_map('sanitize_email', $recipients), 'is_email'));
        if (1 !== count($recipients)) {
            return new WP_Error('webplatform_email_recipient_count', __('WebPlatform Email currently sends to one recipient per message.', 'webplatform-email'));
        }

        $headers = $this->normalize_headers($attributes['headers'] ?? array());
        $content_type = $headers['content-type'] ?? '';
        $is_html = false !== stripos($content_type, 'text/html');
        $payload = array(
            'to' => $recipients[0],
            'subject' => wp_strip_all_tags((string) $attributes['subject']),
            $is_html ? 'html' : 'text' => (string) $attributes['message'],
        );
        if (!empty($headers['reply-to']) && is_email($headers['reply-to'])) {
            $payload['reply_to'] = sanitize_email($headers['reply-to']);
        }

        $result = $this->client->send($payload);
        return is_wp_error($result) ? $result : true;
    }

    private function normalize_headers($headers)
    {
        if (is_string($headers)) {
            $headers = preg_split('/\r\n|\r|\n/', $headers);
        }

        $normalized = array();
        foreach ((array) $headers as $header) {
            $parts = explode(':', (string) $header, 2);
            if (2 === count($parts)) {
                $normalized[strtolower(trim($parts[0]))] = trim($parts[1]);
            }
        }
        return $normalized;
    }
}
