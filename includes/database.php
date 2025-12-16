<?php
if (!defined('ABSPATH')) {
    exit;
}

// Activation hook
register_activation_hook(__FILE__, 'qiog_create_charter_tables');

function qiog_create_charter_tables()
{
    global $wpdb;
    $charset = $wpdb->get_charset_collate();

    // 1. Bookings table
    $bookings_table = $wpdb->prefix . 'qiog_charter_bookings';
    $sql1 = "CREATE TABLE $bookings_table (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        stops LONGTEXT NOT NULL,
        addons LONGTEXT NOT NULL,
        stops_count INT NOT NULL,
        base_price INT NOT NULL,
        addon_total INT NOT NULL,
        grand_total INT NOT NULL,
        full_name VARCHAR(255),
        email VARCHAR(255),
        phone VARCHAR(50),
        notes TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset;";

    // 2. Stops table
    $stops_table = $wpdb->prefix . 'qiog_charter_stops';
    $sql2 = "CREATE TABLE $stops_table (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        duration INT DEFAULT 60,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset;";

    // 3. Add-ons table
    $addons_table = $wpdb->prefix . 'qiog_charter_addons';
    $sql3 = "CREATE TABLE $addons_table (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NOT NULL DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql1);
    dbDelta($sql2);
    dbDelta($sql3);
}
