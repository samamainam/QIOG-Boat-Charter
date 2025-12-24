<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('qiog_customization_options');
delete_option('qiog_builder_page_id');
delete_option('qiog_generated_checkout_page_id');
delete_option('qiog_email_settings');
