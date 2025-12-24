<?php
if (!defined('ABSPATH'))
    exit;

add_action('admin_enqueue_scripts', function ($hook) {

    $allowed_hooks = [
        'toplevel_page_qiog_charter',
        'qiog-charter_page_qiog_charter_stops',
        'qiog-charter_page_qiog_charter_addons',
        'qiog-charter_page_qiog_charter_packages',
        'qiog-charter_page_qiog_charter_customization',
        'qiog-charter_page_qiog_charter_email_settings',
    ];

    if (!in_array($hook, $allowed_hooks)) {
        return;
    }

    wp_enqueue_style(
        'qiog-admin-css',
        QIOG_CHARTER_URL . 'admin/styles.css',
        [],
        QIOG_CHARTER_VERSION
    );

    wp_enqueue_script(
        'qiog-admin-js',
        QIOG_CHARTER_URL . 'admin/scripts.js',
        ['jquery'],
        QIOG_CHARTER_VERSION,
        true
    );

    wp_enqueue_editor();
    wp_enqueue_media();
});
