<?php
if (!defined('ABSPATH'))
    exit;


// Render admin dashboard
function qiog_render_admin_dashboard()
{
    global $wpdb;
    $table = $wpdb->prefix . 'qiog_charter_bookings';

    $bookings = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");

    ?>
    <div class="wrap">
        <h1>Boat Charter Bookings</h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Stops</th>
                    <th>Add-ons</th>
                    <th>Stops Count</th>
                    <th>Base Price</th>
                    <th>Add-ons Total</th>
                    <th>Grand Total</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($bookings): ?>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?php echo esc_html($booking->id); ?></td>
                            <td><?php echo esc_html($booking->full_name); ?></td>
                            <td><?php echo esc_html($booking->email); ?></td>
                            <td><?php echo esc_html($booking->phone); ?></td>
                            <td>
                                <?php
                                $stops = json_decode($booking->stops, true);
                                if ($stops)
                                    echo implode(', ', $stops);
                                ?>
                            </td>
                            <td>
                                <?php
                                $addons = json_decode($booking->addons, true);
                                if ($addons) {
                                    $addon_list = [];
                                    foreach ($addons as $addon) {
                                        $addon_list[] = $addon['name'] . ' x' . $addon['qty'];
                                    }
                                    echo implode(', ', $addon_list);
                                }
                                ?>
                            </td>
                            <td><?php echo esc_html($booking->stops_count); ?></td>
                            <td>$<?php echo esc_html($booking->base_price); ?></td>
                            <td>$<?php echo esc_html($booking->addon_total); ?></td>
                            <td>$<?php echo esc_html($booking->grand_total); ?></td>
                            <td><?php echo esc_html($booking->created_at); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11">No bookings found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}
