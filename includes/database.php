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
        package_name VARCHAR(255) DEFAULT NULL,
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
    image VARCHAR(255) NULL,
    duration INT DEFAULT 60,
    lat DECIMAL(10,8) NULL,
    lng DECIMAL(11,8) NULL,
    description TEXT NULL,
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

    // 4. Packages table
    $packages_table = $wpdb->prefix . 'qiog_charter_packages';
    $sql4 = "CREATE TABLE $packages_table (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset;";

    // 5. Package Stops junction table
    $package_stops_table = $wpdb->prefix . 'qiog_package_stops';
    $sql5 = "CREATE TABLE $package_stops_table (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        package_id BIGINT UNSIGNED NOT NULL,
        stop_id BIGINT UNSIGNED NOT NULL,
        FOREIGN KEY (package_id) REFERENCES $packages_table(id) ON DELETE CASCADE,
        FOREIGN KEY (stop_id) REFERENCES $stops_table(id) ON DELETE CASCADE
    ) $charset;";

    // 6. Package Add-ons junction table
    $package_addons_table = $wpdb->prefix . 'qiog_package_addons';
    $sql6 = "CREATE TABLE $package_addons_table (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        package_id BIGINT UNSIGNED NOT NULL,
        addon_id BIGINT UNSIGNED NOT NULL,
        FOREIGN KEY (package_id) REFERENCES $packages_table(id) ON DELETE CASCADE,
        FOREIGN KEY (addon_id) REFERENCES $addons_table(id) ON DELETE CASCADE
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql1);
    dbDelta($sql2);
    dbDelta($sql3);
    dbDelta($sql4);
    dbDelta($sql5);
    dbDelta($sql6);
}
