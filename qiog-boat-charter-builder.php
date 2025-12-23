<?php
/**
 * Plugin Name: QIOG Boat Charter Builder
 * Description: Build Your Charter – drag & drop stops and addons with live pricing.
 * Version: 1.7.0
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


// Load AJAX stops & addons handler
require_once QIOG_CHARTER_PATH . 'includes/ajax-stops-addons.php';
// Load AJAX packages handler
require_once QIOG_CHARTER_PATH . 'includes/ajax-packages.php';
// Load AJAX checkout/booking handler
require_once QIOG_CHARTER_PATH . 'includes/ajax-checkout.php';
// Load database logic
require_once QIOG_CHARTER_PATH . 'includes/database.php';
// Load shortcode
require_once QIOG_CHARTER_PATH . 'includes/shortcode-builder.php';
// Load shortcode (new version)
require_once QIOG_CHARTER_PATH . 'includes/shortcode-builder-new.php';
// Load checkout page shortcode
require_once QIOG_CHARTER_PATH . 'includes/shortcode-checkout.php';
// Load admin dashboard
require_once QIOG_CHARTER_PATH . 'admin/dashboard.php';
// Load customization settings page
require_once QIOG_CHARTER_PATH . 'admin/customization.php';
// Load packages page
require_once QIOG_CHARTER_PATH . 'admin/packages.php';
// Load import/export page
require_once QIOG_CHARTER_PATH . 'admin/import-export.php';
// Load email settings page
require_once QIOG_CHARTER_PATH . 'admin/email.php';




// Run DB setup on activation
register_activation_hook(__FILE__, 'qiog_create_charter_tables');



// Enqueue frontend assets
add_action('wp_enqueue_scripts', function () {

    // jQuery UI (drag & drop)
    wp_enqueue_script('jquery-ui-sortable');

    // Enqueue jQuery UI Touch Punch for mobile support
    wp_enqueue_script(
        'jquery-ui-touch-punch',
        'https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js',
        array('jquery', 'jquery-ui-sortable'),
        '0.2.3',
        true
    );

    // Plugin JS
    wp_enqueue_script(
        'qiog-charter-js',
        QIOG_CHARTER_URL . 'assets/js/charter-builder.js',
        array('jquery', 'jquery-ui-sortable', 'jquery-ui-touch-punch'),
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

    $options = get_option('qiog_customization_options', []);
    // Localize script with AJAX URL
    wp_localize_script('qiog-charter-js', 'qiogCharter', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'checkout_url' => site_url('/checkout/'),
        'pickup' => [
            'lat' => $options['pickup_lat'] ?? '',
            'lng' => $options['pickup_lng'] ?? '',
            'name' => $options['pickup_label'] ?? 'Pickup Location',
        ],
    ]);

    // Leaflet CSS
    wp_enqueue_style(
        'leaflet-css',
        'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
        [],
        '1.9.4'
    );

    // Leaflet JS
    wp_enqueue_script(
        'leaflet-js',
        'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
        [],
        '1.9.4',
        true
    );
});





add_action('admin_enqueue_scripts', function ($hook) {
    // Only load on our charter pages
    $allowed_hooks = [
        'toplevel_page_qiog_charter',
        'qiog-charter_page_qiog_charter_stops',
        'qiog-charter_page_qiog_charter_addons',
        'qiog-charter_page_qiog_charter_packages',
        'qiog-charter_page_qiog_charter_customization',
    ];

    if (!in_array($hook, $allowed_hooks))
        return;

    // Only load on your email settings page
    if ($hook !== 'qiog-charter_page_qiog_charter_email_settings') {
        return;
    }


    wp_enqueue_editor();
    wp_enqueue_media(); // Required for media uploader
    wp_enqueue_style('qiog-admin-css', QIOG_CHARTER_URL . 'admin/styles.css');
    wp_enqueue_script('qiog-admin-js', QIOG_CHARTER_URL . 'admin/scripts.js', ['jquery'], '1.0', true);

});

