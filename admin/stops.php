<?php
if (!defined('ABSPATH'))
    exit;

function qiog_charter_stops_page()
{
    global $wpdb;
    $table = $wpdb->prefix . 'qiog_charter_stops';

    /* --------------------
       ADD STOP
    -------------------- */
    if (isset($_POST['qiog_add_stop']) && check_admin_referer('qiog_add_stop', 'qiog_add_stop_nonce')) {
        $wpdb->insert($table, [
            'name' => sanitize_text_field($_POST['stop_name']),
            'image' => isset($_POST['stop_image']) ? intval($_POST['stop_image']) : null,
            'description' => sanitize_textarea_field($_POST['stop_description']),
            'duration' => intval($_POST['stop_duration']),
            'lat' => isset($_POST['stop_lat']) ? floatval($_POST['stop_lat']) : null,
            'lng' => isset($_POST['stop_lng']) ? floatval($_POST['stop_lng']) : null,
        ]);


        echo '<div class="notice notice-success"><p>Stop added successfully.</p></div>';
    }

    /* --------------------
    DELETE STOP (and remove from packages)
 -------------------- */
    if (isset($_GET['delete_stop'])) {
        $del_id = intval($_GET['delete_stop']);

        if (check_admin_referer('qiog_delete_stop_' . $del_id)) {

            $stops_table = $wpdb->prefix . 'qiog_charter_stops';
            $package_stops_table = $wpdb->prefix . 'qiog_package_stops';

            // 1️⃣ Remove stop from all packages (pivot table)
            $wpdb->delete(
                $package_stops_table,
                ['stop_id' => $del_id],
                ['%d']
            );

            // 2️⃣ (Optional) delete stop image
            $stop = $wpdb->get_row(
                $wpdb->prepare("SELECT image FROM $stops_table WHERE id = %d", $del_id)
            );

            if ($stop && !empty($stop->image)) {
                wp_delete_attachment((int) $stop->image, true);
            }

            // 3️⃣ Delete the stop itself
            $wpdb->delete(
                $stops_table,
                ['id' => $del_id],
                ['%d']
            );

            echo '<div class="notice notice-success"><p>Stop deleted and removed from all packages.</p></div>';
        }
    }



    /* --------------------
       UPDATE STOP
    -------------------- */
    if (isset($_POST['qiog_update_stop']) && check_admin_referer('qiog_update_stop', 'qiog_update_stop_nonce')) {
        $wpdb->update(
            $table,
            [
                'name' => sanitize_text_field($_POST['stop_name']),
                'image' => isset($_POST['stop_image']) ? intval($_POST['stop_image']) : null,
                'description' => sanitize_textarea_field($_POST['stop_description']),
                'duration' => intval($_POST['stop_duration']),
                'lat' => isset($_POST['stop_lat']) ? floatval($_POST['stop_lat']) : null,
                'lng' => isset($_POST['stop_lng']) ? floatval($_POST['stop_lng']) : null,
            ],
            ['id' => intval($_POST['stop_id'])]
        );


        echo '<div class="notice notice-success"><p>Stop updated successfully.</p></div>';
    }

    $stops = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");

    // Editing?
    $editing_stop = null;
    if (isset($_GET['edit_stop'])) {
        $editing_stop = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE id = %d", intval($_GET['edit_stop']))
        );
    }
    ?>

    <div class="wrap">
        <h1>Stops</h1>

        <form method="POST">
            <?php
            if ($editing_stop) {
                wp_nonce_field('qiog_update_stop', 'qiog_update_stop_nonce');
                echo '<input type="hidden" name="stop_id" value="' . esc_attr($editing_stop->id) . '">';
            } else {
                wp_nonce_field('qiog_add_stop', 'qiog_add_stop_nonce');
            }
            ?>

            <p>
                <label>Stop Name</label><br>
                <input type="text" name="stop_name" required value="<?php echo esc_attr($editing_stop->name ?? ''); ?>">
            </p>

            <p>
                <label>Duration (minutes)</label><br>
                <input type="number" name="stop_duration" required
                    value="<?php echo esc_attr($editing_stop->duration ?? 60); ?>">
            </p>

            <p>
                <label>Latitude</label><br>
                <input type="number" step="0.00000001" name="stop_lat"
                    value="<?php echo esc_attr($editing_stop->lat ?? ''); ?>">
            </p>

            <p>
                <label>Longitude</label><br>
                <input type="number" step="0.00000001" name="stop_lng"
                    value="<?php echo esc_attr($editing_stop->lng ?? ''); ?>">
            </p>

            <p>
                <label>Short Description</label><br>
                <textarea name="stop_description" rows="3" style="width:100%;"
                    placeholder="Short description shown in map popup"><?php
                    echo esc_textarea($editing_stop->description ?? '');
                    ?></textarea>
            </p>

            <p>
                <label>Featured Image</label><br>
                <?php
                $image_id = $editing_stop->image ?? '';
                $image_url = $image_id ? wp_get_attachment_url($image_id) : '';
                ?>
            <div class="qiog-image-wrapper">
                <img id="stop-image-preview" src="<?php echo esc_url($image_url); ?>"
                    style="max-width:150px; <?php echo $image_url ? '' : 'display:none;'; ?>">
            </div>
            <input type="hidden" name="stop_image" id="stop-image-id" value="<?php echo esc_attr($image_id); ?>">
            <button type="button" class="button"
                id="upload-stop-image"><?php echo $image_url ? 'Change Image' : 'Select Image'; ?></button>
            <button type="button" class="button" id="remove-stop-image" <?php echo $image_url ? '' : 'style="display:none;"'; ?>>Remove Image</button>
            </p>


            <p>
                <button type="submit" name="<?php echo $editing_stop ? 'qiog_update_stop' : 'qiog_add_stop'; ?>"
                    class="button button-primary">
                    <?php echo $editing_stop ? 'Update Stop' : 'Add Stop'; ?>
                </button>

                <?php if ($editing_stop): ?>
                    <a class="button" href="<?php echo esc_url(remove_query_arg('edit_stop')); ?>">Cancel</a>
                <?php endif; ?>
            </p>
        </form>

        <h2>Existing Stops</h2>
        <ul>
            <?php foreach ($stops as $stop): ?>
                <li>
                    <strong><?php echo esc_html($stop->name); ?></strong>
                    (<?php echo intval($stop->duration); ?> min)
                    <?php if ($stop->lat && $stop->lng): ?>
                        — 📍 <?php echo esc_html($stop->lat . ', ' . $stop->lng); ?>
                    <?php endif; ?>
                    —
                    <a href="<?php echo esc_url(add_query_arg('edit_stop', $stop->id)); ?>">Edit</a>
                    —
                    <a href="<?php echo esc_url(
                        wp_nonce_url(add_query_arg('delete_stop', $stop->id), 'qiog_delete_stop_' . $stop->id)
                    ); ?>" onclick="return confirm('Delete this stop?');">Delete</a>
                    <?php if (!empty($stop->description)): ?>
                        <br><b>Description:</b> <em><?php echo esc_html($stop->description); ?></em>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Image Upload Script -->
    <script>
        jQuery(document).ready(function ($) {
            var frame;
            $('#upload-stop-image').on('click', function (e) {
                e.preventDefault();
                if (frame) frame.open();
                else {
                    frame = wp.media({
                        title: 'Select Stop Image',
                        button: { text: 'Use this image' },
                        multiple: false
                    });
                    frame.on('select', function () {
                        var attachment = frame.state().get('selection').first().toJSON();
                        $('#stop-image-id').val(attachment.id);
                        $('#stop-image-preview').attr('src', attachment.url).show();
                        $('#remove-stop-image').show();
                    });
                    frame.open();
                }
            });

            $('#remove-stop-image').on('click', function () {
                $('#stop-image-id').val('');
                $('#stop-image-preview').hide();
                $(this).hide();
            });
        });
    </script>
    <?php


}
