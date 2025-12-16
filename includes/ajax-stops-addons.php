<?php
if (!defined('ABSPATH'))
    exit;

// Fetch stops
add_action('wp_ajax_qiog_get_stops', 'qiog_get_stops');
add_action('wp_ajax_nopriv_qiog_get_stops', 'qiog_get_stops');
function qiog_get_stops()
{
    global $wpdb;
    $table = $wpdb->prefix . 'qiog_charter_stops';
    $stops = $wpdb->get_results("SELECT id, name, duration FROM $table ORDER BY id ASC", ARRAY_A);
    wp_send_json_success($stops);
}

// Fetch addons
add_action('wp_ajax_qiog_get_addons', 'qiog_get_addons');
add_action('wp_ajax_nopriv_qiog_get_addons', 'qiog_get_addons');
function qiog_get_addons()
{
    global $wpdb;
    $table = $wpdb->prefix . 'qiog_charter_addons';
    $addons = $wpdb->get_results("SELECT id, name, price FROM $table ORDER BY id ASC", ARRAY_A);
    wp_send_json_success($addons);
}
