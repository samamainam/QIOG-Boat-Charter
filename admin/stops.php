<?php
if (!defined('ABSPATH'))
    exit;

function qiog_charter_stops_page()
{
    global $wpdb;
    $table = $wpdb->prefix . 'qiog_charter_stops';

    // Handle form submission
    if (isset($_POST['qiog_add_stop'])) {
        $name = sanitize_text_field($_POST['stop_name']);
        $duration = intval($_POST['stop_duration']);
        $wpdb->insert($table, ['name' => $name, 'duration' => $duration]);
        echo '<div class="notice notice-success"><p>Stop added successfully.</p></div>';
    }

    $stops = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");
    ?>
    <div class="wrap">
        <h1>Stops</h1>
        <form method="POST">
            <p>
                <label>Stop Name:</label><br>
                <input type="text" name="stop_name" required>
            </p>
            <p>
                <label>Duration (minutes):</label><br>
                <input type="number" name="stop_duration" value="60" required>
            </p>
            <p><button type="submit" name="qiog_add_stop" class="button button-primary">Add Stop</button></p>
        </form>

        <h2>Existing Stops</h2>
        <ul>
            <?php foreach ($stops as $stop): ?>
                <li><?php echo esc_html($stop->name . ' (' . $stop->duration . ' min)'); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
}
