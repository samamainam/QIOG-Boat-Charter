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

    // Handle delete request
    if (isset($_GET['delete_addon'])) {
        $del_id = intval($_GET['delete_addon']);
        if (check_admin_referer('qiog_delete_addon_' . $del_id)) {
            $wpdb->delete($table, ['id' => $del_id]);
            echo '<div class="notice notice-success"><p>Add-on deleted successfully.</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>Invalid request.</p></div>';
        }
    }

    // Handle update request
    if (isset($_POST['qiog_update_addon'])) {
        if (check_admin_referer('qiog_update_addon', 'qiog_update_addon_nonce')) {
            $id = intval($_POST['addon_id']);
            $name = sanitize_text_field($_POST['addon_name']);
            $price = floatval($_POST['addon_price']);
            $wpdb->update($table, ['name' => $name, 'price' => $price], ['id' => $id]);
            echo '<div class="notice notice-success"><p>Add-on updated successfully.</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>Invalid update request.</p></div>';
        }
    }

    $addons = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");
    ?>
    <div class="wrap">
        <h1>Add-ons</h1>
        <?php
        // If editing, show edit form populated with values
        $editing_addon = null;
        if (isset($_GET['edit_addon'])) {
            $edit_id = intval($_GET['edit_addon']);
            $editing_addon = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $edit_id));
        }

        if ($editing_addon): ?>
            <form method="POST">
                <?php wp_nonce_field('qiog_update_addon', 'qiog_update_addon_nonce'); ?>
                <input type="hidden" name="addon_id" value="<?php echo esc_attr($editing_addon->id); ?>">
                <p>
                    <label>Add-on Name:</label><br>
                    <input type="text" name="addon_name" required value="<?php echo esc_attr($editing_addon->name); ?>">
                </p>
                <p>
                    <label>Price ($):</label><br>
                    <input type="number" step="0.01" name="addon_price" value="<?php echo esc_attr($editing_addon->price); ?>"
                        required>
                </p>
                <p>
                    <button type="submit" name="qiog_update_addon" class="button button-primary">Update Add-on</button>
                    <a class="button" href="<?php echo esc_url(remove_query_arg('edit_addon')); ?>">Cancel</a>
                </p>
            </form>
        <?php else: ?>
            <form method="POST">
                <?php wp_nonce_field('qiog_add_addon', 'qiog_add_addon_nonce'); ?>
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
        <?php endif; ?>

        <h2>Existing Add-ons</h2>
        <ul>
            <?php foreach ($addons as $addon): ?>
                <li>
                    <?php echo esc_html($addon->name . ' ($' . $addon->price . ')'); ?>
                    - <a href="<?php echo esc_url(add_query_arg('edit_addon', $addon->id)); ?>">Edit</a>
                    - <a href="<?php echo esc_url(wp_nonce_url(add_query_arg('delete_addon', $addon->id), 'qiog_delete_addon_' . $addon->id)); ?>"
                        onclick="return confirm('Delete this add-on?');">Delete</a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
}
