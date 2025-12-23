<?php
if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('qiog_build_charter', 'qiog_render_charter_builder');

function qiog_render_charter_builder()
{
    // Get customization options
    $options = get_option('qiog_customization_options', array());
    $charter_heading = isset($options['charter_heading']) ? $options['charter_heading'] : 'Build Your Charter';
    $stops_label = isset($options['stops_label']) ? $options['stops_label'] : 'Available Stops';
    $addons_label = isset($options['addons_label']) ? $options['addons_label'] : 'Available Add-ons';
    $base_price_3 = isset($options['base_price_3_stops']) ? $options['base_price_3_stops'] : 900;
    $base_price_4 = isset($options['base_price_4_stops']) ? $options['base_price_4_stops'] : 1100;
    $max_stops = isset($options['max_stops']) ? $options['max_stops'] : 3;

    $stops_section_heading = isset($options['stops_section_heading']) ? $options['stops_section_heading'] : 'Select Your Stops';
    $packages_section_heading = isset($options['packages_section_heading']) ? $options['packages_section_heading'] : 'Choose from Preset Packages';
    $addons_section_heading = isset($options['addons_section_heading']) ? $options['addons_section_heading'] : 'Choose Additional Add-ons';
    $summary_section_heading = isset($options['summary_section_heading']) ? $options['summary_section_heading'] : 'Charter Summary';

    ob_start();
    ?>


    <div id="qiog-charter-builder" data-base-price-3="<?php echo esc_attr($base_price_3); ?>"
        data-base-price-4="<?php echo esc_attr($base_price_4); ?>" data-max-stops="<?php echo esc_attr($max_stops); ?>">

        <div class="qiog-builder-wrapper">
            <div class="qiog-builder-layout">
                <div class="qiog-content-column">
                    <div class="qiog-row">
                        <div class="qiog-charter-heading">
                            <h2><?php echo esc_html($charter_heading); ?></h2>
                        </div>
                    </div>
                    <!-- Row 1: Stops Heading -->
                    <div class="qiog-row">
                        <div class="qiog-section-divider">
                            <h3 class="qiog-main-heading"><?php echo esc_html($stops_section_heading); ?></h3>
                        </div>
                    </div>
                    <!-- Row 2: Stops (left: available, right: selected) -->
                    <div class="qiog-row">
                        <div class="qiog-column">
                            <div class="qiog-section">
                                <h3><?php echo esc_html($stops_label); ?></h3>
                                <div class="qiog-stops available-stops"></div>
                            </div>
                        </div>
                        <div class="qiog-column">
                            <div class="qiog-section">
                                <div class="qiog-section-header">
                                    <h3>Your Tour</h3>
                                    <div id="qiog-upgrade-wrapper" style="display:none;">
                                        <button id="qiog-upgrade-btn" class="upgrade-btn">+ Add Stop</button>
                                    </div>
                                </div>
                                <div class="qiog-stops selected-stops qiog-drop-zone">
                                    <div class="qiog-empty-state">Drag stops here</div>
                                </div>
                                <button id="qiog-clear-stops" class="clear-selection-btn" style="margin-top:10px;">Clear
                                    Selected Stops</button>
                            </div>
                        </div>
                    </div>
                    <!-- Row 3: OR Divider -->
                    <div class="qiog-row">
                        <div class="qiog-section-divider-or">
                            <span class="divider-line"></span>
                            <span class="divider-text">OR</span>
                            <span class="divider-line"></span>
                        </div>
                    </div>
                    <!-- Row 4: Packages Heading -->
                    <div class="qiog-row">
                        <div class="qiog-section-divider">
                            <h3 class="qiog-main-heading"><?php echo esc_html($packages_section_heading); ?></h3>
                        </div>
                    </div>
                    <!-- Row 5: Packages -->
                    <div class="qiog-row">
                        <div class="qiog-section">
                            <h3>Packages</h3>
                            <div class="qiog-packages available-packages"></div>
                        </div>
                    </div>
                    <!-- Row 6: Map -->
                    <div class="qiog-row">
                        <div class="qiog-section">
                            <div id="qiog-map"></div>
                        </div>
                    </div>
                    <!-- Row 7: Add-ons Heading -->
                    <div class="qiog-row">
                        <div class="qiog-section-divider">
                            <h3 class="qiog-main-heading"><?php echo esc_html($addons_section_heading); ?></h3>
                        </div>
                    </div>
                    <!-- Row 8: Add-ons (left: available, right: selected) -->
                    <div class="qiog-row">
                        <div class="qiog-column">
                            <div class="qiog-section">
                                <h3><?php echo esc_html($addons_label); ?></h3>
                                <div class="qiog-addons available-addons"></div>
                            </div>
                        </div>
                        <div class="qiog-column">
                            <div class="qiog-section">
                                <div class="qiog-section-header">
                                    <h3>Selected Add-ons</h3>
                                </div>
                                <div class="qiog-addons selected-addons qiog-drop-zone">
                                    <div class="qiog-empty-state">Drag add-ons here</div>
                                </div>
                                <button id="qiog-clear-addons" class="clear-selection-btn" style="margin-top:10px;">Clear
                                    Selected
                                    Add-ons</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="qiog-summary-column">
                    <!-- RIGHT COLUMN: Sticky Summary -->
                    <div class="qiog-summary-sticky-wrapper">
                        <div class="qiog-pricing-summary">
                            <div class="qiog-section-divider">
                                <h3 class="qiog-main-heading"><?php echo esc_html($summary_section_heading); ?></h3>
                            </div>
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
                            <div class="qiog-summary-row qiog-total-row">
                                <strong>Total:</strong>
                                <strong>$<span id="qiog-grand-total"><?php echo esc_html($base_price_3); ?></span></strong>
                            </div>
                            <button id="qiog-checkout-btn" class="qiog-checkout-btn">Continue to Checkout →</button>
                        </div>
                    </div>
                </div>
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