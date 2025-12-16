<?php
if (!defined('ABSPATH'))
    exit;

function qiog_charter_addons_page()
{
    global $wpdb;
    $table = $wpdb->prefix . 'qiog_charter_addons';

    // Handle form submission
    if (isset($_POST['qiog_add_addon'])) {
        $name = sanitize_text_field($_POST['addon_name']);
        $price = floatval($_POST['addon_price']);
        $wpdb->insert($table, ['name' => $name, 'price' => $price]);
        echo '<div class="notice notice-success"><p>Add-on added successfully.</p></div>';
    }

    $addons = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");
    ?>
    <div class="wrap">
        <h1>Add-ons</h1>
        <form method="POST">
            <p>
                <label>Add-on Name:</label><br>
                <input type="text" name="addon_name" required>
            </p>
            <p>
                <label>Price ($):</label><br>
                <input type="number" step="0.01" name="addon_price" value="0" required>
            </p>
            <p><button type="submit" name="qiog_add_addon" class="button button-primary">Add Add-on</button></p>
        </form>

        <h2>Existing Add-ons</h2>
        <ul>
            <?php foreach ($addons as $addon): ?>
                <li><?php echo esc_html($addon->name . ' ($' . $addon->price . ')'); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
}
