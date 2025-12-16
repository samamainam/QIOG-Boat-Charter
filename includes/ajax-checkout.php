<?php
if (!defined('ABSPATH'))
    exit;

add_action('wp_ajax_qiog_save_customer', 'qiog_save_customer');
add_action('wp_ajax_nopriv_qiog_save_customer', 'qiog_save_customer');

function qiog_save_customer()
{
    global $wpdb;
    $table = $wpdb->prefix . 'qiog_charter_bookings';

    $booking = isset($_POST['booking']) ? json_decode(stripslashes($_POST['booking']), true) : [];

    if (!$booking) {
        wp_send_json_error('Booking data missing');
        wp_die();
    }

    $wpdb->insert($table, [
        'stops' => json_encode($booking['stops']),
        'addons' => json_encode($booking['addons']),
        'stops_count' => intval($booking['stops_count']),
        'base_price' => intval($booking['base_price']),
        'addon_total' => intval($booking['addon_total']),
        'grand_total' => intval($booking['grand_total']),
        'full_name' => sanitize_text_field($_POST['full_name']),
        'email' => sanitize_email($_POST['email']),
        'phone' => sanitize_text_field($_POST['phone']),
        'notes' => sanitize_textarea_field($_POST['notes']),
    ]);

    wp_send_json_success(['booking_id' => $wpdb->insert_id]);
}
