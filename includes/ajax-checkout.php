<?php
if (!defined('ABSPATH'))
    exit;

add_action('wp_ajax_qiog_save_customer', 'qiog_save_customer');
add_action('wp_ajax_nopriv_qiog_save_customer', 'qiog_save_customer');

function qiog_parse_email_template($template, $data)
{
    foreach ($data as $key => $value) {
        $template = str_replace('{{' . $key . '}}', $value, $template);
    }
    return nl2br($template);
}


function qiog_save_customer()
{
    global $wpdb;
    $table = $wpdb->prefix . 'qiog_charter_bookings';

    $booking = isset($_POST['booking']) ? json_decode(stripslashes($_POST['booking']), true) : [];

    if (!$booking) {
        wp_send_json_error('Booking data missing');
        wp_die();
    }

    // Insert booking into database
    $wpdb->insert($table, [
        'package_name' => isset($booking['package_name'])
            ? sanitize_text_field($booking['package_name'])
            : null,
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

    // wp_send_json_success(['booking_id' => $wpdb->insert_id]);

    $booking_id = $wpdb->insert_id;
    $email_settings = get_option('qiog_email_settings', []);

    // Prepare email data
    $data = [
        'customer_name' => $_POST['full_name'],
        'customer_email' => $_POST['email'],
        'customer_phone' => $_POST['phone'],
        'package_name' => $booking['package_name'] ?? 'N/A',
        'stops' => implode(', ', $booking['stops']),
        'addons' => !empty($booking['addons']) ? implode(', ', array_column($booking['addons'], 'name')) : 'None',
        'base_price' => $booking['base_price'],
        'addon_total' => $booking['addon_total'],
        'grand_total' => $booking['grand_total'],
        'notes' => $_POST['notes'] ?? '',
        'booking_id' => $booking_id,
        'site_name' => get_bloginfo('name'),
    ];

    // Send admin email
    if (!empty($email_settings['enable_admin_email'])) {
        $subject = $email_settings['admin_subject'] ?? 'New Charter Booking';
        $body = qiog_parse_email_template(
            $email_settings['admin_template'] ?? 'New booking received.',
            $data
        );

        wp_mail(
            get_option('admin_email'),
            $subject,
            $body,
            ['Content-Type: text/html; charset=UTF-8']
        );
    }


    // Send customer email
    if (!empty($email_settings['enable_customer_email'])) {
        $subject = $email_settings['customer_subject'] ?? 'Your Booking Confirmation';
        $body = qiog_parse_email_template(
            $email_settings['customer_template'] ?? 'Thank you for your booking!',
            $data
        );

        wp_mail(
            $_POST['email'],
            $subject,
            $body,
            ['Content-Type: text/html; charset=UTF-8']
        );
    }

    // Return success response
    wp_send_json_success([
        'booking_id' => $booking_id
    ]);

}