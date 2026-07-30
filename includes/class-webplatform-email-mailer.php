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
        if (empty($recipients)) {
            return $return;
        }

        $headers = $this->normalize_headers($attributes['headers'] ?? array());
        $content_type = $headers['content-type'] ?? '';
        $is_html = false !== stripos($content_type, 'text/html');
        $reply_to = (!empty($headers['reply-to']) && is_email($headers['reply-to'])) ? sanitize_email($headers['reply-to']) : null;

        $last_result = true;
        foreach ($recipients as $recipient) {
            $payload = array(
                'to' => $recipient,
                'subject' => wp_strip_all_tags((string) $attributes['subject']),
                $is_html ? 'html' : 'text' => (string) $attributes['message'],
            );
            if ($reply_to) {
                $payload['reply_to'] = $reply_to;
            }
            $result = $this->client->send($payload);
            if (is_wp_error($result)) {
                $last_result = $result;
            }
        }
        return $last_result;
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
