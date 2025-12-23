<?php
if (!defined('ABSPATH'))
    exit;

add_action('admin_menu', 'qiog_charter_admin_menu');

function qiog_charter_admin_menu()
{
    // Top-level menu
    add_menu_page(
        'QIOG Charter',                 // Page title
        'QIOG Charter',                 // Menu title
        'manage_options',               // Capability
        'qiog_charter',                 // Menu slug
        'qiog_render_admin_dashboard',   // Callback for default page
        'dashicons-admin-page',         // Icon
        5
    );

    // Submenu: Bookings (default page)
    add_submenu_page(
        'qiog_charter',
        'Bookings',
        'Bookings',
        'manage_options',
        'qiog_charter',                 // Same slug as top-level for default page
        'qiog_render_admin_dashboard'
    );

    // Submenu: Stops
    add_submenu_page(
        'qiog_charter',
        'Stops',
        'Stops',
        'manage_options',
        'qiog_charter_stops',
        'qiog_charter_stops_page'
    );

    // Submenu: Add-ons
    add_submenu_page(
        'qiog_charter',
        'Add-ons',
        'Add-ons',
        'manage_options',
        'qiog_charter_addons',
        'qiog_charter_addons_page'
    );

    // Submenu: Packages
    add_submenu_page(
        'qiog_charter',
        'Packages',
        'Packages',
        'manage_options',
        'qiog_charter_packages',
        'qiog_charter_packages_page'
    );

    // Submenu: Customization
    add_submenu_page(
        'qiog_charter',
        'Charter Customization',
        'Charter Customization',
        'manage_options',
        'qiog_charter_customization',
        'qiog_charter_customization_page',
    );

    // Submenu: Import / Export
    add_submenu_page(
        'qiog_charter',
        'Import / Export',
        'Import / Export',
        'manage_options',
        'qiog_charter_import_export',
        'qiog_charter_import_export_page'
    );

    add_submenu_page(
        'qiog_charter',
        'Charter Emails',
        'Charter Emails',
        'manage_options',
        'qiog_charter_email_settings',
        'qiog_charter_email_settings_page',
    );

}


