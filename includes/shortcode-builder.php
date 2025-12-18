<?php
if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('qiog_build_charter', 'qiog_render_charter_builder');

function qiog_render_charter_builder()
{
    // Get customization options
    $options = get_option('qiog_customization_options', array());
    $primary_color = isset($options['primary_color']) ? $options['primary_color'] : '#4CAF50';
    $secondary_color = isset($options['secondary_color']) ? $options['secondary_color'] : '#2196F3';
    $charter_heading = isset($options['charter_heading']) ? $options['charter_heading'] : 'Build Your Charter';
    $stops_label = isset($options['stops_label']) ? $options['stops_label'] : 'Available Stops';
    $addons_label = isset($options['addons_label']) ? $options['addons_label'] : 'Available Add-ons';
    $base_price_3 = isset($options['base_price_3_stops']) ? $options['base_price_3_stops'] : 900;
    $base_price_4 = isset($options['base_price_4_stops']) ? $options['base_price_4_stops'] : 1100;
    $max_stops = isset($options['max_stops']) ? $options['max_stops'] : 3;

    ob_start();
    ?>
    <style>
        #qiog-charter-builder {
            --primary-color:
                <?php echo esc_attr($primary_color); ?>
            ;
            --secondary-color:
                <?php echo esc_attr($secondary_color); ?>
            ;
        }
    </style>

    <div id="qiog-charter-builder" data-base-price-3="<?php echo esc_attr($base_price_3); ?>"
        data-base-price-4="<?php echo esc_attr($base_price_4); ?>" data-max-stops="<?php echo esc_attr($max_stops); ?>">

        <h2><?php echo esc_html($charter_heading); ?></h2>

        <div class="qiog-builder-layout">
            <!-- Left Column: Available Items -->
            <div class="qiog-column qiog-available-column">
                <!-- Available Stops -->
                <div class="qiog-section">
                    <h3><?php echo esc_html($stops_label); ?></h3>
                    <div class="qiog-stops available-stops"></div>
                </div>

                <!-- Available Addons -->
                <div class="qiog-section">
                    <h3><?php echo esc_html($addons_label); ?></h3>
                    <div class="qiog-addons available-addons"></div>
                </div>
            </div>

            <!-- Right Column: Selected Items & Summary -->
            <div class="qiog-column qiog-selected-column">
                <!-- Selected Stops -->
                <div class="qiog-section">
                    <div class="qiog-section-header">
                        <h3>Your Tour</h3>
                        <div id="qiog-upgrade-wrapper" style="display:none;">
                            <button id="qiog-upgrade-btn" class="upgrade-btn">
                                + Add Stop
                            </button>
                        </div>
                    </div>
                    <div class="qiog-stops selected-stops qiog-drop-zone">
                        <div class="qiog-empty-state">Drag stops here</div>
                    </div>
                </div>

                <!-- Selected Addons -->
                <div class="qiog-section">
                    <h3>Selected Add-ons</h3>
                    <div class="qiog-addons selected-addons qiog-drop-zone">
                        <div class="qiog-empty-state">Drag add-ons here</div>
                    </div>
                </div>

                <!-- Pricing Summary -->
                <div class="qiog-section qiog-pricing-summary">
                    <h3>Charter Summary</h3>
                    <div class="qiog-summary-row">
                        <span>Stops:</span>
                        <span id="qiog-stop-count">0</span>
                    </div>
                    <div class="qiog-summary-row">
                        <span>Base Price:</span>
                        <span>$<span id="qiog-base-price"><?php echo esc_html($base_price_3); ?></span></span>
                    </div>
                    <div class="qiog-summary-row">
                        <span>Add-ons Total:</span>
                        <span>$<span id="qiog-addon-total">0</span></span>
                    </div>
                    <hr>
                    <div class="qiog-summary-row qiog-total-row">
                        <strong>Total:</strong>
                        <strong>$<span id="qiog-grand-total"><?php echo esc_html($base_price_3); ?></span></strong>
                    </div>
                </div>

                <button id="qiog-checkout-btn" class="qiog-checkout-btn">
                    Continue to Checkout
                </button>
            </div>
        </div>

    </div>

    <script>
        jQuery(document).ready(function ($) {
            // Get customization values from data attributes
            var builderEl = $('#qiog-charter-builder');
            var basePriceFor3 = parseInt(builderEl.data('base-price-3')) || 900;
            var basePriceFor4 = parseInt(builderEl.data('base-price-4')) || 1100;
            var maxStopsConfig = parseInt(builderEl.data('max-stops')) || 3;

            // Update global variables in your main script
            if (typeof window.qiogConfig === 'undefined') {
                window.qiogConfig = {};
            }
            window.qiogConfig.basePriceFor3 = basePriceFor3;
            window.qiogConfig.basePriceFor4 = basePriceFor4;
            window.qiogConfig.maxStops = maxStopsConfig;
        });
    </script>
    <?php
    return ob_get_clean();
}