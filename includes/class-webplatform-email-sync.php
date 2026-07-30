<?php

if (!defined('ABSPATH')) {
    exit;
}

class WebPlatform_Email_Sync
{
    public static function payload()
    {
        $contacts = array();
        foreach (get_users(array('orderby' => 'ID', 'order' => 'DESC')) as $user) {
            $contacts[] = array(
                'id' => 'user:' . $user->ID,
                'email' => sanitize_email($user->user_email),
                'name' => sanitize_text_field($user->display_name),
                'phone' => sanitize_text_field(get_user_meta($user->ID, 'billing_phone', true)),
                'email_consent' => (bool) get_user_meta($user->ID, 'webplatform_email_consent', true),
                'message_consent' => (bool) get_user_meta($user->ID, 'webplatform_message_consent', true),
                'updated_at' => get_date_from_gmt($user->user_registered, DATE_ATOM),
            );
        }

        $orders = array();
        if (function_exists('wc_get_orders')) {
            foreach (wc_get_orders(array('orderby' => 'modified', 'order' => 'DESC')) as $order) {
                if ('yes' === $order->get_meta('_webplatform_email_consent')) {
                    $contacts[] = array(
                        'id' => 'order-contact:' . $order->get_id(),
                        'email' => sanitize_email($order->get_billing_email()),
                        'name' => sanitize_text_field($order->get_formatted_billing_full_name()),
                        'phone' => sanitize_text_field($order->get_billing_phone()),
                        'email_consent' => true,
                        'message_consent' => false,
                        'updated_at' => $order->get_date_modified() ? $order->get_date_modified()->format(DATE_ATOM) : null,
                    );
                }
                $orders[] = array(
                    'id' => 'order:' . $order->get_id(),
                    'status' => $order->get_status(),
                    'total' => (float) $order->get_total(),
                    'currency' => $order->get_currency(),
                    'customer_email' => sanitize_email($order->get_billing_email()),
                    'customer_phone' => sanitize_text_field($order->get_billing_phone()),
                    'updated_at' => $order->get_date_modified() ? $order->get_date_modified()->format(DATE_ATOM) : null,
                );
            }
        }

        return array(
            'site_url' => home_url('/'),
            'contacts' => $contacts,
            'orders' => $orders,
        );
    }
}
