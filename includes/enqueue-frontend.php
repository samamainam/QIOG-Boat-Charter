<?php
if (!defined('ABSPATH'))
    exit;

add_action('wp_enqueue_scripts', function () {

    wp_enqueue_script('jquery-ui-sortable');

    wp_enqueue_script(
        'jquery-ui-touch-punch',
        'https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js',
        ['jquery', 'jquery-ui-sortable'],
        '0.2.3',
        true
    );

    wp_enqueue_script(
        'qiog-charter-js',
        QIOG_CHARTER_URL . 'assets/js/charter-builder.js',
        ['jquery', 'jquery-ui-sortable', 'jquery-ui-touch-punch'],
        QIOG_CHARTER_VERSION,
        true
    );

    wp_enqueue_style(
        'qiog-charter-css',
        QIOG_CHARTER_URL . 'assets/css/charter-builder.css',
        [],
        QIOG_CHARTER_VERSION
    );

    wp_enqueue_style(
        'leaflet-css',
        'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
        [],
        '1.9.4'
    );

    wp_enqueue_script(
        'leaflet-js',
        'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
        [],
        '1.9.4',
        true
    );

    $options = get_option('qiog_customization_options', []);

    wp_localize_script('qiog-charter-js', 'qiogCharter', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'checkout_url' => !empty($options['checkout_page_id'])
            ? get_permalink($options['checkout_page_id'])
            : '',
        'pickup' => [
            'lat' => $options['pickup_lat'] ?? '',
            'lng' => $options['pickup_lng'] ?? '',
            'name' => $options['pickup_label'] ?? 'Pickup Location',
        ],
    ]);
});
