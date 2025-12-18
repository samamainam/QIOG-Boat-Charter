<?php
if (!defined('ABSPATH'))
    exit;

add_shortcode('qiog_charter_checkout', 'qiog_render_checkout_page');

function qiog_render_checkout_page()
{
    // Get customization options
    $options = get_option('qiog_customization_options', array());
    $primary_color = isset($options['primary_color']) ? $options['primary_color'] : '#4CAF50';
    $secondary_color = isset($options['secondary_color']) ? $options['secondary_color'] : '#2196F3';
    $button_text = isset($options['checkout_button_text']) ? $options['checkout_button_text'] : 'Confirm Booking';
    $success_message = isset($options['success_message']) ? $options['success_message'] : 'Booking confirmed! We\'ll contact you shortly.';
    $show_summary_icons = isset($options['show_summary_icons']) ? $options['show_summary_icons'] : 'yes';

    ob_start();
    ?>
    <style>
        .qiog-checkout {
            --primary-color:
                <?php echo esc_attr($primary_color); ?>
            ;
            --secondary-color:
                <?php echo esc_attr($secondary_color); ?>
            ;
        }
    </style>
    <div class="qiog-checkout">
        <div class="qiog-checkout-container">
            <div class="qiog-checkout-grid">
                <!-- Left Column: Booking Summary -->
                <div class="qiog-checkout-left">
                    <div class="qiog-summary-card">
                        <div class="qiog-summary-header">
                            <?php if ($show_summary_icons === 'yes'): ?>
                                <svg class="qiog-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 11l3 3L22 4"></path>
                                    <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"></path>
                                </svg>
                            <?php endif; ?>
                            <h2>Booking Summary</h2>
                        </div>
                        <div id="qiog-booking-summary" class="qiog-summary-content">
                            <div class="qiog-loading">
                                <div class="qiog-spinner"></div>
                                <p>Loading booking data...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Customer Form -->
                <div class="qiog-checkout-right">
                    <div class="qiog-form-card">
                        <div class="qiog-form-header">
                            <?php if ($show_summary_icons === 'yes'): ?>
                                <svg class="qiog-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            <?php endif; ?>
                            <h2>Customer Details</h2>
                        </div>

                        <form id="qiog-customer-form">
                            <div class="qiog-form-group">
                                <label for="full_name">Full Name *</label>
                                <input type="text" id="full_name" name="full_name" class="qiog-input" required
                                    placeholder="John Doe">
                            </div>

                            <div class="qiog-form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" id="email" name="email" class="qiog-input" required
                                    placeholder="john@example.com">
                            </div>

                            <div class="qiog-form-group">
                                <label for="phone">Phone Number *</label>
                                <input type="tel" id="phone" name="phone" class="qiog-input" required
                                    placeholder="+1 (555) 123-4567">
                            </div>

                            <div class="qiog-form-group">
                                <label for="notes">Additional Notes (Optional)</label>
                                <textarea id="notes" name="notes" class="qiog-textarea" rows="4"
                                    placeholder="Any special requests or requirements..."></textarea>
                            </div>

                            <button type="submit" class="qiog-submit-btn"
                                data-success-message="<?php echo esc_attr($success_message); ?>">
                                <span class="btn-text"><?php echo esc_html($button_text); ?></span>
                                <span class="btn-loader" style="display:none;">
                                    <span class="spinner"></span> Processing...
                                </span>
                            </button>
                        </form>

                        <div id="qiog-checkout-message"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        jQuery(document).ready(function ($) {
            let bookingData = sessionStorage.getItem('qiog_booking');

            if (!bookingData) {
                $('#qiog-booking-summary').html(`
                    <div class="qiog-no-data">
                        <svg class="qiog-icon-large" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <p>No booking data found.</p>
                        <p class="sub-text">Please build your charter first.</p>
                    </div>
                `);
                $('#qiog-customer-form').parent().html(`
                    <div class="qiog-form-disabled">
                        <p>Please configure your charter before proceeding to checkout.</p>
                        <a href="<?php echo esc_url(get_permalink(get_page_by_path('build-charter'))); ?>" class="qiog-back-btn">
                            ← Back to Charter Builder
                        </a>
                    </div>
                `);
                return;
            }

            bookingData = JSON.parse(bookingData);

            // Build summary HTML
            let html = '<div class="qiog-summary-section">';
            html += '<div class="qiog-section-title">';
            html += '<svg class="qiog-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">';
            html += '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"></path>';
            html += '<circle cx="12" cy="10" r="3"></circle>';
            html += '</svg>';
            html += '<h3>Tour Stops (' + bookingData.stops_count + ')</h3>';
            html += '</div>';
            html += '<ul class="qiog-stops-list">';
            bookingData.stops.forEach((stop, index) => {
                html += '<li><span class="stop-number">' + (index + 1) + '</span>' + stop + '</li>';
            });
            html += '</ul></div>';

            html += '<div class="qiog-summary-section">';
            html += '<div class="qiog-section-title">';
            html += '<svg class="qiog-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">';
            html += '<circle cx="12" cy="12" r="10"></circle>';
            html += '<line x1="12" y1="16" x2="12" y2="12"></line>';
            html += '<line x1="12" y1="8" x2="12.01" y2="8"></line>';
            html += '</svg>';
            html += '<h3>Add-ons</h3>';
            html += '</div>';

            if (bookingData.addons.length > 0) {
                html += '<ul class="qiog-addons-list">';
                bookingData.addons.forEach(addon => {
                    html += '<li>';
                    html += '<span class="addon-name">' + addon.name + '</span>';
                    html += '<span class="addon-details">× ' + addon.qty + ' <span class="price">$' + addon.price + '</span></span>';
                    html += '</li>';
                });
                html += '</ul>';
            } else {
                html += '<p class="qiog-no-items">No add-ons selected.</p>';
            }
            html += '</div>';

            html += '<div class="qiog-pricing-breakdown">';
            html += '<div class="pricing-row">';
            html += '<span>Base Price:</span>';
            html += '<span>$' + bookingData.base_price + '</span>';
            html += '</div>';
            html += '<div class="pricing-row">';
            html += '<span>Add-ons Total:</span>';
            html += '<span>$' + bookingData.addon_total + '</span>';
            html += '</div>';
            html += '<div class="pricing-row total-row">';
            html += '<strong>Grand Total:</strong>';
            html += '<strong class="total-amount">$' + bookingData.grand_total + '</strong>';
            html += '</div>';
            html += '</div>';

            $('#qiog-booking-summary').html(html);

            // Form submission with loading state
            $('#qiog-customer-form').on('submit', function (e) {
                e.preventDefault();

                let $btn = $(this).find('.qiog-submit-btn');
                let $btnText = $btn.find('.btn-text');
                let $btnLoader = $btn.find('.btn-loader');
                let successMsg = $btn.data('success-message');

                $btn.prop('disabled', true);
                $btnText.hide();
                $btnLoader.show();

                let formData = $(this).serializeArray();
                formData.push(
                    { name: "booking", value: JSON.stringify(bookingData) },
                    { name: "action", value: "qiog_save_customer" }
                );

                $.post('<?php echo admin_url('admin-ajax.php'); ?>', formData, function (response) {
                    if (response.success) {
                        $('#qiog-checkout-message').html(`
                            <div class="qiog-success-message">
                                <svg class="qiog-icon-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 11.08V12a10 10 0 11-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                <h3>Success!</h3>
                                <p>${successMsg}</p>
                            </div>
                        `);
                        $('#qiog-customer-form').fadeOut();
                        sessionStorage.removeItem('qiog_booking');
                    } else {
                        $('#qiog-checkout-message').html(`
                            <div class="qiog-error-message">
                                <p>Error saving booking. Please try again.</p>
                            </div>
                        `);
                        $btn.prop('disabled', false);
                        $btnText.show();
                        $btnLoader.hide();
                    }
                }).fail(function () {
                    $('#qiog-checkout-message').html(`
                        <div class="qiog-error-message">
                            <p>Connection error. Please try again.</p>
                        </div>
                    `);
                    $btn.prop('disabled', false);
                    $btnText.show();
                    $btnLoader.hide();
                });
            });
        });
    </script>
    <?php
    return ob_get_clean();
}