<?php
if (!defined('ABSPATH'))
    exit;

add_action('wp_ajax_qiog_save_booking', 'qiog_save_booking');
add_action('wp_ajax_nopriv_qiog_save_booking', 'qiog_save_booking');

function qiog_save_booking()
{
    global $wpdb;

    $table = $wpdb->prefix . 'qiog_charter_bookings';
    $booking = $_POST['booking'];

    $wpdb->insert($table, [
        'stops' => json_encode($booking['stops']),
        'addons' => json_encode($booking['addons']),
        'stops_count' => intval($booking['stops_count']),
        'base_price' => intval($booking['base_price']),
        'addon_total' => intval($booking['addon_total']),
        'grand_total' => intval($booking['grand_total']),
    ]);

    wp_send_json_success(['booking_id' => $wpdb->insert_id]);
}
