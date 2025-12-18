<?php
/**
 * Plugin Name: QIOG Boat Charter Builder
 * Description: Build Your Charter – drag & drop stops and addons with live pricing.
 * Version: 1.0.0
 * Author: QIOG
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('QIOG_CHARTER_PATH', plugin_dir_path(__FILE__));
define('QIOG_CHARTER_URL', plugin_dir_url(__FILE__));


// Admin files
require_once QIOG_CHARTER_PATH . 'admin/menu.php';
require_once QIOG_CHARTER_PATH . 'admin/dashboard.php';
require_once QIOG_CHARTER_PATH . 'admin/stops.php';
require_once QIOG_CHARTER_PATH . 'admin/addons.php';


// Load shortcode
require_once QIOG_CHARTER_PATH . 'includes/shortcode-builder.php';
// Load AJAX stops & addons handler
require_once QIOG_CHARTER_PATH . 'includes/ajax-stops-addons.php';
// Load AJAX booking handler
require_once QIOG_CHARTER_PATH . 'includes/ajax-booking.php';
// Load database logic
require_once QIOG_CHARTER_PATH . 'includes/database.php';
// Load checkout page shortcode
require_once QIOG_CHARTER_PATH . 'includes/shortcode-checkout.php';
// Load AJAX checkout handler
require_once QIOG_CHARTER_PATH . 'includes/ajax-checkout.php';
// Load admin dashboard
require_once QIOG_CHARTER_PATH . 'admin/dashboard.php';
// Load customization settings page
require_once QIOG_CHARTER_PATH . 'admin/customization.php';




// Run DB setup on activation
register_activation_hook(__FILE__, 'qiog_create_charter_tables');



// Enqueue frontend assets
add_action('wp_enqueue_scripts', function () {

    // jQuery UI (drag & drop)
    wp_enqueue_script('jquery-ui-sortable');

    // Plugin JS
    wp_enqueue_script(
        'qiog-charter-js',
        QIOG_CHARTER_URL . 'assets/js/charter-builder.js',
        ['jquery', 'jquery-ui-sortable'],
        '1.0',
        true
    );

    // Plugin CSS
    wp_enqueue_style(
        'qiog-charter-css',
        QIOG_CHARTER_URL . 'assets/css/charter-builder.css',
        [],
        '1.0'
    );

    // Localize script with AJAX URL
    wp_localize_script('qiog-charter-js', 'qiogCharter', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'checkout_url' => site_url('/checkout/')
    ]);
});



// Enqueue admin assets
add_action('admin_enqueue_scripts', function ($hook) {
    // Only load on our bookings page
    if ($hook != 'toplevel_page_qiog-charter-bookings')
        return;

    wp_enqueue_style('qiog-admin-css', QIOG_CHARTER_URL . 'admin/styles.css');
    wp_enqueue_script('qiog-admin-js', QIOG_CHARTER_URL . 'admin/scripts.js', ['jquery'], '1.0', true);
});
