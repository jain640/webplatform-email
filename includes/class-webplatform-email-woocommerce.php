<?php

if (!defined('ABSPATH')) {
    exit;
}

class WebPlatform_Email_WooCommerce
{
    public function __construct()
    {
        add_action('woocommerce_after_order_notes', array($this, 'opt_in_field'));
        add_action('woocommerce_checkout_update_order_meta', array($this, 'save_opt_in'));
    }

    public function opt_in_field($checkout)
    {
        woocommerce_form_field('webplatform_email_consent', array(
            'type' => 'checkbox',
            'class' => array('form-row-wide'),
            'label' => __('Email me news and offers', 'webplatform-email-connector'),
            'required' => false,
        ), $checkout->get_value('webplatform_email_consent'));
    }

    public function save_opt_in($order_id)
    {
        $nonce = isset($_POST['woocommerce-process-checkout-nonce'])
            ? sanitize_text_field(wp_unslash($_POST['woocommerce-process-checkout-nonce']))
            : '';
        if (!$nonce || !wp_verify_nonce($nonce, 'woocommerce-process_checkout')) {
            return;
        }

        $consent = !empty($_POST['webplatform_email_consent']) ? 'yes' : 'no';
        update_post_meta($order_id, '_webplatform_email_consent', $consent);
        if ('yes' === $consent && get_current_user_id()) {
            update_user_meta(get_current_user_id(), 'webplatform_email_consent', 1);
        }
    }
}
