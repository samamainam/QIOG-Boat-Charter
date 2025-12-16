<?php
if (!defined('ABSPATH'))
    exit;

add_shortcode('qiog_charter_checkout', 'qiog_render_checkout_page');

function qiog_render_checkout_page()
{
    ob_start();
    ?>
    <div class="qiog-checkout">
        <h2>Booking Summary</h2>

        <div id="qiog-booking-summary">
            <p>Loading booking data...</p>
        </div>

        <hr>

        <h2>Customer Details</h2>
        <form id="qiog-customer-form">
            <p>
                <label>Full Name</label><br>
                <input type="text" name="full_name" required>
            </p>
            <p>
                <label>Email</label><br>
                <input type="email" name="email" required>
            </p>
            <p>
                <label>Phone</label><br>
                <input type="text" name="phone" required>
            </p>
            <p>
                <label>Notes (optional)</label><br>
                <textarea name="notes"></textarea>
            </p>
            <button type="submit">Confirm Booking</button>
        </form>

        <div id="qiog-checkout-message"></div>
    </div>

    <script>
        jQuery(document).ready(function ($) {
            let bookingData = sessionStorage.getItem('qiog_booking');
            if (!bookingData) {
                $('#qiog-booking-summary').html('<p>No booking data found. Please build your charter first.</p>');
                $('#qiog-customer-form').hide();
                return;
            }

            bookingData = JSON.parse(bookingData);

            let html = '<h4>Stops (' + bookingData.stops_count + ')</h4><ul>';
            bookingData.stops.forEach(stop => {
                html += '<li>' + stop + '</li>';
            });
            html += '</ul>';

            html += '<h4>Add-ons</h4>';
            if (bookingData.addons.length > 0) {
                html += '<ul>';
                bookingData.addons.forEach(addon => {
                    html += '<li>' + addon.name + ' × ' + addon.qty + ' ($' + addon.price + ' each)</li>';
                });
                html += '</ul>';
            } else {
                html += '<p>No add-ons selected.</p>';
            }

            html += '<p><strong>Base Price:</strong> $' + bookingData.base_price + '</p>';
            html += '<p><strong>Add-ons Total:</strong> $' + bookingData.addon_total + '</p>';
            html += '<p><strong>Grand Total:</strong> $' + bookingData.grand_total + '</p>';

            $('#qiog-booking-summary').html(html);
        });
    </script>
    <?php
    return ob_get_clean();
}
