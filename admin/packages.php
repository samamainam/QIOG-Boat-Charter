<?php
if (!defined('ABSPATH'))
    exit;

function qiog_charter_packages_page()
{
    global $wpdb;
    $packages_table = $wpdb->prefix . 'qiog_charter_packages';
    $stops_table = $wpdb->prefix . 'qiog_charter_stops';
    $addons_table = $wpdb->prefix . 'qiog_charter_addons';
    $package_stops_table = $wpdb->prefix . 'qiog_package_stops';
    $package_addons_table = $wpdb->prefix . 'qiog_package_addons';

    // Handle delete request
    if (isset($_GET['delete_package'])) {
        $del_id = intval($_GET['delete_package']);
        if (check_admin_referer('qiog_delete_package_' . $del_id)) {
            $wpdb->delete($packages_table, ['id' => $del_id]);
            $wpdb->delete($package_stops_table, ['package_id' => $del_id]);
            $wpdb->delete($package_addons_table, ['package_id' => $del_id]);
            echo '<div class="notice notice-success"><p>Package deleted successfully.</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>Invalid request.</p></div>';
        }
    }

    // Handle add/update request
    if (isset($_POST['qiog_save_package'])) {
        if (check_admin_referer('qiog_save_package', 'qiog_save_package_nonce')) {
            $package_id = isset($_POST['package_id']) ? intval($_POST['package_id']) : null;
            $name = sanitize_text_field($_POST['package_name']);
            $description = sanitize_textarea_field($_POST['package_description']);
            $price = floatval($_POST['package_price']);
            $selected_stops = isset($_POST['selected_stops']) ? array_map('intval', $_POST['selected_stops']) : [];
            $selected_addons = isset($_POST['selected_addons']) ? array_map('intval', $_POST['selected_addons']) : [];

            if (empty($name)) {
                echo '<div class="notice notice-error"><p>Package name is required.</p></div>';
            } else {
                if ($package_id) {
                    // Update existing package
                    $wpdb->update($packages_table, ['name' => $name, 'description' => $description, 'price' => $price], ['id' => $package_id]);
                    echo '<div class="notice notice-success"><p>Package updated successfully.</p></div>';
                } else {
                    // Insert new package
                    $wpdb->insert($packages_table, ['name' => $name, 'description' => $description, 'price' => $price]);
                    $package_id = $wpdb->insert_id;
                    echo '<div class="notice notice-success"><p>Package created successfully.</p></div>';
                }

                // Update package stops
                $wpdb->delete($package_stops_table, ['package_id' => $package_id]);
                foreach ($selected_stops as $stop_id) {
                    $wpdb->insert($package_stops_table, ['package_id' => $package_id, 'stop_id' => $stop_id]);
                }

                // Update package addons
                $wpdb->delete($package_addons_table, ['package_id' => $package_id]);
                foreach ($selected_addons as $addon_id) {
                    $wpdb->insert($package_addons_table, ['package_id' => $package_id, 'addon_id' => $addon_id]);
                }
            }
        } else {
            echo '<div class="notice notice-error"><p>Invalid request.</p></div>';
        }
    }

    // Determine if we're editing or creating
    $editing_package = null;
    $package_stops = [];
    $package_addons = [];
    if (isset($_GET['edit_package'])) {
        $edit_id = intval($_GET['edit_package']);
        $editing_package = $wpdb->get_row($wpdb->prepare("SELECT * FROM $packages_table WHERE id = %d", $edit_id));
        $package_stops = $wpdb->get_col($wpdb->prepare("SELECT stop_id FROM $package_stops_table WHERE package_id = %d", $edit_id));
        $package_addons = $wpdb->get_col($wpdb->prepare("SELECT addon_id FROM $package_addons_table WHERE package_id = %d", $edit_id));
    }

    // Get all stops and addons for selection
    $all_stops = $wpdb->get_results("SELECT * FROM $stops_table ORDER BY name ASC");
    $all_addons = $wpdb->get_results("SELECT * FROM $addons_table ORDER BY name ASC");

    // Get all packages for display
    $packages = $wpdb->get_results("SELECT * FROM $packages_table ORDER BY created_at DESC");
    ?>
    <div class="wrap">
        <h1>Packages</h1>

        <form method="POST" style="background: #f9f9f9; padding: 20px; margin: 20px 0; border: 1px solid #ddd; border-radius: 5px;">
            <?php wp_nonce_field('qiog_save_package', 'qiog_save_package_nonce'); ?>
            
            <?php if ($editing_package): ?>
                <h2>Edit Package</h2>
                <input type="hidden" name="package_id" value="<?php echo esc_attr($editing_package->id); ?>">
            <?php else: ?>
                <h2>Create New Package</h2>
            <?php endif; ?>

            <p>
                <label for="package_name"><strong>Package Name:</strong></label><br>
                <input type="text" id="package_name" name="package_name" required 
                    value="<?php echo esc_attr($editing_package ? $editing_package->name : ''); ?>" 
                    style="width: 100%; padding: 8px; margin: 5px 0;">
            </p>

            <p>
                <label for="package_description"><strong>Description:</strong></label><br>
                <textarea id="package_description" name="package_description" rows="3" 
                    style="width: 100%; padding: 8px; margin: 5px 0;"><?php echo esc_textarea($editing_package ? $editing_package->description : ''); ?></textarea>
            </p>

            <p>
                <label for="package_price"><strong>Price ($):</strong></label><br>
                <input type="number" id="package_price" name="package_price" step="0.01" required 
                    value="<?php echo esc_attr($editing_package ? $editing_package->price : '0'); ?>" 
                    style="width: 100%; padding: 8px; margin: 5px 0;">
            </p>

            <p>
                <label><strong>Include Stops:</strong></label><br>
                <div style="background: white; border: 1px solid #ccc; padding: 10px; max-height: 200px; overflow-y: auto; margin: 5px 0;">
                    <?php if (!empty($all_stops)): ?>
                        <?php foreach ($all_stops as $stop): ?>
                            <label style="display: block; margin: 5px 0;">
                                <input type="checkbox" name="selected_stops[]" value="<?php echo esc_attr($stop->id); ?>" 
                                    <?php checked(in_array($stop->id, $package_stops)); ?>>
                                <?php echo esc_html($stop->name . ' (' . $stop->duration . ' min)'); ?>
                            </label>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: #999;">No stops available. Add some in the Stops section first.</p>
                    <?php endif; ?>
                </div>
            </p>

            <p>
                <label><strong>Include Add-ons:</strong></label><br>
                <div style="background: white; border: 1px solid #ccc; padding: 10px; max-height: 200px; overflow-y: auto; margin: 5px 0;">
                    <?php if (!empty($all_addons)): ?>
                        <?php foreach ($all_addons as $addon): ?>
                            <label style="display: block; margin: 5px 0;">
                                <input type="checkbox" name="selected_addons[]" value="<?php echo esc_attr($addon->id); ?>" 
                                    <?php checked(in_array($addon->id, $package_addons)); ?>>
                                <?php echo esc_html($addon->name . ' ($' . $addon->price . ')'); ?>
                            </label>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: #999;">No add-ons available. Add some in the Add-ons section first.</p>
                    <?php endif; ?>
                </div>
            </p>

            <p>
                <button type="submit" name="qiog_save_package" class="button button-primary">
                    <?php echo $editing_package ? 'Update Package' : 'Create Package'; ?>
                </button>
                <?php if ($editing_package): ?>
                    <a class="button" href="<?php echo esc_url(remove_query_arg('edit_package')); ?>">Cancel</a>
                <?php endif; ?>
            </p>
        </form>

        <h2>Existing Packages</h2>
        <?php if (!empty($packages)): ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Stops</th>
                        <th>Add-ons</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($packages as $package): ?>
                        <?php 
                        $pkg_stops = $wpdb->get_col($wpdb->prepare("SELECT stop_id FROM $package_stops_table WHERE package_id = %d", $package->id));
                        $pkg_addons = $wpdb->get_col($wpdb->prepare("SELECT addon_id FROM $package_addons_table WHERE package_id = %d", $package->id));
                        ?>
                        <tr>
                            <td><strong><?php echo esc_html($package->name); ?></strong></td>
                            <td><?php echo esc_html($package->description); ?></td>
                            <td>$<?php echo esc_html($package->price); ?></td>
                            <td><?php echo count($pkg_stops); ?> stop(s)</td>
                            <td><?php echo count($pkg_addons); ?> add-on(s)</td>
                            <td>
                                <a href="<?php echo esc_url(add_query_arg('edit_package', $package->id)); ?>" class="button button-small">Edit</a>
                                <a href="<?php echo esc_url(wp_nonce_url(add_query_arg('delete_package', $package->id), 'qiog_delete_package_' . $package->id)); ?>" class="button button-small" onclick="return confirm('Delete this package?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No packages created yet. Create one above.</p>
        <?php endif; ?>
    </div>
    <?php
}
