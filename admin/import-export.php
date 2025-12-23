<?php
if (!defined('ABSPATH'))
    exit;

// Handle export functionality
add_action('admin_init', function () {
    if (!isset($_POST['qiog_export']))
        return;
    if (!check_admin_referer('qiog_export_data', 'qiog_export_nonce'))
        return;

    global $wpdb;

    $data = [
        'stops' => $wpdb->get_results("SELECT * FROM {$wpdb->prefix}qiog_charter_stops", ARRAY_A),
        'addons' => $wpdb->get_results("SELECT * FROM {$wpdb->prefix}qiog_charter_addons", ARRAY_A),
        'packages' => $wpdb->get_results("SELECT * FROM {$wpdb->prefix}qiog_charter_packages", ARRAY_A),
        'package_stops' => $wpdb->get_results("SELECT * FROM {$wpdb->prefix}qiog_package_stops", ARRAY_A),
        'package_addons' => $wpdb->get_results("SELECT * FROM {$wpdb->prefix}qiog_package_addons", ARRAY_A),
    ];

    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename=qiog-charter-export-' . date('Y-m-d') . '.json');
    echo wp_json_encode($data, JSON_PRETTY_PRINT);
    exit;
});

// Handle import functionality
add_action('admin_init', function () {
    if (empty($_FILES['qiog_import_file']))
        return;
    if (!check_admin_referer('qiog_import_data', 'qiog_import_nonce'))
        return;

    global $wpdb;

    $json = file_get_contents($_FILES['qiog_import_file']['tmp_name']);
    $data = json_decode($json, true);

    if (!$data || !is_array($data)) {
        wp_die('Invalid JSON file.');
    }

    // Table mapping
    $map = [
        'stops' => $wpdb->prefix . 'qiog_charter_stops',
        'addons' => $wpdb->prefix . 'qiog_charter_addons',
        'packages' => $wpdb->prefix . 'qiog_charter_packages',
        'package_stops' => $wpdb->prefix . 'qiog_package_stops',
        'package_addons' => $wpdb->prefix . 'qiog_package_addons',
    ];

    // Disable foreign key checks (important)
    $wpdb->query('SET FOREIGN_KEY_CHECKS=0');

    // Clear tables
    foreach ($map as $table) {
        $wpdb->query("TRUNCATE TABLE {$table}");
    }

    // Insert data
    foreach ($map as $key => $table) {
        if (empty($data[$key]))
            continue;

        foreach ($data[$key] as $row) {
            $wpdb->insert($table, $row);
        }
    }

    // Re-enable checks
    $wpdb->query('SET FOREIGN_KEY_CHECKS=1');

    wp_redirect(
        add_query_arg('import_success', 1, admin_url('admin.php?page=qiog_charter_import_export'))
    );
    exit;
});



function qiog_charter_import_export_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    ?>
    <div class="wrap">
        <h1>Import / Export Charter Data</h1>

        <h2>📤 Export</h2>
        <p>Download all charter data as a JSON file.</p>

        <form method="post">
            <?php wp_nonce_field('qiog_export_data', 'qiog_export_nonce'); ?>
            <input type="hidden" name="qiog_export" value="1">
            <button type="submit" class="button button-primary">
                Export Stops, Add-ons & Packages
            </button>
        </form>

        <hr>

        <h2>📥 Import</h2>
        <p>Upload a previously exported JSON file.</p>

        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('qiog_import_data', 'qiog_import_nonce'); ?>
            <input type="file" name="qiog_import_file" accept=".json" required>
            <br><br>
            <button type="submit" class="button button-primary">
                Import Data
            </button>
        </form>
    </div>
    <?php
    if (isset($_GET['import_success'])) {
        echo '<div class="notice notice-success"><p>Data imported successfully.</p></div>';
    }


}
