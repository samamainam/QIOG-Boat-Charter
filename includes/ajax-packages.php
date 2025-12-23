<?php
if (!defined('ABSPATH'))
    exit;


// Fetch packages (with included stops and addons)
add_action('wp_ajax_qiog_get_packages', 'qiog_get_packages');
add_action('wp_ajax_nopriv_qiog_get_packages', 'qiog_get_packages');
function qiog_get_packages()
{
    global $wpdb;
    $packages_table = $wpdb->prefix . 'qiog_charter_packages';
    $package_stops_table = $wpdb->prefix . 'qiog_package_stops';
    $package_addons_table = $wpdb->prefix . 'qiog_package_addons';
    $stops_table = $wpdb->prefix . 'qiog_charter_stops';

    $packages = $wpdb->get_results("SELECT id, name, description, price FROM $packages_table ORDER BY id ASC", ARRAY_A);

    foreach ($packages as &$pkg) {
        $pkg_id = intval($pkg['id']);

        // Get stop IDs
        $stop_ids = $wpdb->get_col($wpdb->prepare("SELECT stop_id FROM $package_stops_table WHERE package_id = %d", $pkg_id));
        $pkg['stops'] = array_map('intval', $stop_ids);

        // Get stop names
        $stop_names = [];
        if (!empty($stop_ids)) {
            $stop_ids_clean = array_map('intval', $stop_ids);
            $ids_placeholder = implode(',', array_fill(0, count($stop_ids_clean), '%d'));
            $stop_names = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT name FROM $stops_table WHERE id IN ($ids_placeholder)",
                    ...$stop_ids_clean
                )
            );
        }
        $pkg['stop_names'] = $stop_names;

        // Get addon IDs
        $addon_ids = $wpdb->get_col($wpdb->prepare("SELECT addon_id FROM $package_addons_table WHERE package_id = %d", $pkg_id));
        $pkg['addons'] = array_map('intval', $addon_ids);
    }

    wp_send_json_success($packages);
}
