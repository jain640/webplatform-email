<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('webplatform_email_settings');
