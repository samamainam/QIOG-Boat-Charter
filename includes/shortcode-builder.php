<?php
if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('qiog_build_charter', 'qiog_render_charter_builder');

function qiog_render_charter_builder()
{
    ob_start();
    ?>
    <div id="qiog-charter-builder">

        <h2>Build Your Charter</h2>

        <div class="qiog-builder-layout">
            <!-- Left Column: Available Items -->
            <div class="qiog-column qiog-available-column">
                <!-- Available Stops -->
                <div class="qiog-section">
                    <h3>Available Stops</h3>
                    <div class="qiog-stops available-stops"></div>
                </div>

                <!-- Available Addons -->
                <div class="qiog-section">
                    <h3>Available Add-ons</h3>
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
                        <span>$<span id="qiog-base-price">900</span></span>
                    </div>
                    <div class="qiog-summary-row">
                        <span>Add-ons Total:</span>
                        <span>$<span id="qiog-addon-total">0</span></span>
                    </div>
                    <hr>
                    <div class="qiog-summary-row qiog-total-row">
                        <strong>Total:</strong>
                        <strong>$<span id="qiog-grand-total">900</span></strong>
                    </div>
                </div>

                <button id="qiog-checkout-btn" class="qiog-checkout-btn">
                    Continue to Checkout
                </button>
            </div>
        </div>

    </div>
    <?php
    return ob_get_clean();
}