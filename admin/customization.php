<?php
if (!defined('ABSPATH'))
    exit;

// Register settings
add_action('admin_init', 'qiog_register_customization_settings');

function qiog_register_customization_settings()
{
    register_setting('qiog_customization_group', 'qiog_customization_options', 'qiog_sanitize_customization_options');
}

function qiog_sanitize_customization_options($input)
{
    $sanitized = array();

    // Colors
    $sanitized['primary_color'] = sanitize_hex_color($input['primary_color']);
    $sanitized['secondary_color'] = sanitize_hex_color($input['secondary_color']);
    $sanitized['accent_color'] = sanitize_hex_color($input['accent_color']);

    // Text fields
    $sanitized['site_title'] = sanitize_text_field($input['site_title']);
    $sanitized['charter_heading'] = sanitize_text_field($input['charter_heading']);
    $sanitized['stops_label'] = sanitize_text_field($input['stops_label']);
    $sanitized['addons_label'] = sanitize_text_field($input['addons_label']);
    $sanitized['checkout_button_text'] = sanitize_text_field($input['checkout_button_text']);
    $sanitized['success_message'] = sanitize_textarea_field($input['success_message']);

    // Section headings
    $sanitized['stops_section_heading'] = sanitize_text_field($input['stops_section_heading']);
    $sanitized['packages_section_heading'] = sanitize_text_field($input['packages_section_heading']);
    $sanitized['addons_section_heading'] = sanitize_text_field($input['addons_section_heading']);
    $sanitized['summary_section_heading'] = sanitize_text_field($input['summary_section_heading']);


    // Pricing
    $sanitized['base_price_3_stops'] = absint($input['base_price_3_stops']);
    $sanitized['base_price_4_stops'] = absint($input['base_price_4_stops']);
    $sanitized['max_stops'] = absint($input['max_stops']);

    // Feature toggles
    $sanitized['show_summary_icons'] = isset($input['show_summary_icons']) ? 'yes' : 'no';
    $sanitized['enable_upgrade_option'] = isset($input['enable_upgrade_option']) ? 'yes' : 'no';
    $sanitized['show_empty_state'] = isset($input['show_empty_state']) ? 'yes' : 'no';
    $sanitized['enable_quantity_controls'] = isset($input['enable_quantity_controls']) ? 'yes' : 'no';

    // Pickup location
    $sanitized['pickup_lat'] = isset($input['pickup_lat']) ? floatval($input['pickup_lat']) : '';
    $sanitized['pickup_lng'] = isset($input['pickup_lng']) ? floatval($input['pickup_lng']) : '';
    $sanitized['pickup_label'] = sanitize_text_field($input['pickup_label']);


    // Email settings
    $sanitized['admin_email'] = sanitize_email($input['admin_email']);
    $sanitized['enable_customer_email'] = isset($input['enable_customer_email']) ? 'yes' : 'no';
    $sanitized['email_subject'] = sanitize_text_field($input['email_subject']);

    return $sanitized;
}

// Handle reset
add_action('admin_init', 'qiog_handle_customization_reset');

function qiog_handle_customization_reset()
{
    if (isset($_GET['page']) && $_GET['page'] === 'qiog_charter_customization' && isset($_GET['reset']) && current_user_can('manage_options')) {
        check_admin_referer('qiog_reset_settings', 'qiog_reset_nonce');
        delete_option('qiog_customization_options');
        wp_redirect(admin_url('admin.php?page=qiog_charter_customization&settings-updated=1'));
        exit;
    }
}

// Render customization page
function qiog_charter_customization_page()
{
    $options = get_option('qiog_customization_options', array());

    // Default values
    $defaults = array(
        'primary_color' => '#4CAF50',
        'secondary_color' => '#2196F3',
        'accent_color' => '#FF5722',
        'site_title' => 'Charter Booking System',
        'charter_heading' => 'Build Your Charter',
        'stops_label' => 'Available Stops',
        'addons_label' => 'Available Add-ons',
        'stops_section_heading' => 'Select Your Stops',
        'packages_section_heading' => 'Choose from Preset Packages',
        'addons_section_heading' => 'Choose Additional Add-ons',
        'summary_section_heading' => 'Charter Summary',
        'pickup_lat' => '',
        'pickup_lng' => '',
        'pickup_label' => 'Pickup Location',
        'checkout_button_text' => 'Confirm Booking',
        'success_message' => 'Booking confirmed! We\'ll contact you shortly.',
        'base_price_3_stops' => 900,
        'base_price_4_stops' => 1100,
        'max_stops' => 3,
        'show_summary_icons' => 'yes',
        'enable_upgrade_option' => 'yes',
        'show_empty_state' => 'yes',
        'enable_quantity_controls' => 'yes',
        'admin_email' => get_option('admin_email'),
        'enable_customer_email' => 'yes',
        'email_subject' => 'Your Charter Booking Confirmation'
    );

    $options = wp_parse_args($options, $defaults);

    // Generate reset URL with nonce
    $reset_url = wp_nonce_url(
        add_query_arg('reset', '1', admin_url('admin.php?page=qiog_charter_customization')),
        'qiog_reset_settings',
        'qiog_reset_nonce'
    );

    ?>
    <div class="wrap qiog-admin-wrap">
        <h1>
            <span class="dashicons dashicons-admin-customizer"></span>
            Charter Customization Settings
        </h1>

        <?php if (isset($_GET['settings-updated'])): ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>Settings saved successfully!</strong></p>
            </div>
        <?php endif; ?>

        <form method="post" action="options.php" class="qiog-admin-form">
            <?php settings_fields('qiog_customization_group'); ?>

            <div class="qiog-admin-grid">

                <!-- Color Settings -->
                <div class="qiog-admin-section">
                    <h2><span class="dashicons dashicons-art"></span> Color Scheme</h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="primary_color">Primary Color</label></th>
                            <td>
                                <input type="color" id="primary_color" name="qiog_customization_options[primary_color]"
                                    value="<?php echo esc_attr($options['primary_color']); ?>" class="qiog-color-picker">
                                <p class="description">Used for buttons, selected stops, and primary accents</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="secondary_color">Secondary Color</label></th>
                            <td>
                                <input type="color" id="secondary_color" name="qiog_customization_options[secondary_color]"
                                    value="<?php echo esc_attr($options['secondary_color']); ?>" class="qiog-color-picker">
                                <p class="description">Used for add-ons and secondary elements</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="accent_color">Accent Color</label></th>
                            <td>
                                <input type="color" id="accent_color" name="qiog_customization_options[accent_color]"
                                    value="<?php echo esc_attr($options['accent_color']); ?>" class="qiog-color-picker">
                                <p class="description">Used for highlights and special elements</p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Text Settings -->
                <div class="qiog-admin-section">
                    <h2><span class="dashicons dashicons-editor-textcolor"></span> Text & Labels</h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="site_title">Site Title</label></th>
                            <td>
                                <input type="text" id="site_title" name="qiog_customization_options[site_title]"
                                    value="<?php echo esc_attr($options['site_title']); ?>" class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="charter_heading">Charter Page Heading</label></th>
                            <td>
                                <input type="text" id="charter_heading" name="qiog_customization_options[charter_heading]"
                                    value="<?php echo esc_attr($options['charter_heading']); ?>" class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="stops_label">Stops Section Label</label></th>
                            <td>
                                <input type="text" id="stops_label" name="qiog_customization_options[stops_label]"
                                    value="<?php echo esc_attr($options['stops_label']); ?>" class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="addons_label">Add-ons Section Label</label></th>
                            <td>
                                <input type="text" id="addons_label" name="qiog_customization_options[addons_label]"
                                    value="<?php echo esc_attr($options['addons_label']); ?>" class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="checkout_button_text">Checkout Button Text</label></th>
                            <td>
                                <input type="text" id="checkout_button_text"
                                    name="qiog_customization_options[checkout_button_text]"
                                    value="<?php echo esc_attr($options['checkout_button_text']); ?>" class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="success_message">Success Message</label></th>
                            <td>
                                <textarea id="success_message" name="qiog_customization_options[success_message]" rows="3"
                                    class="large-text"><?php echo esc_textarea($options['success_message']); ?></textarea>
                                <p class="description">Message shown after successful booking</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="stops_section_heading">Stops Section Heading</label></th>
                            <td>
                                <input type="text" id="stops_section_heading"
                                    name="qiog_customization_options[stops_section_heading]"
                                    value="<?php echo esc_attr($options['stops_section_heading']); ?>" class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="packages_section_heading">Packages Section Heading</label></th>
                            <td>
                                <input type="text" id="packages_section_heading"
                                    name="qiog_customization_options[packages_section_heading]"
                                    value="<?php echo esc_attr($options['packages_section_heading']); ?>"
                                    class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="addons_section_heading">Add-ons Section Heading</label></th>
                            <td>
                                <input type="text" id="addons_section_heading"
                                    name="qiog_customization_options[addons_section_heading]"
                                    value="<?php echo esc_attr($options['addons_section_heading']); ?>"
                                    class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="summary_section_heading">Summary Section Heading</label></th>
                            <td>
                                <input type="text" id="summary_section_heading"
                                    name="qiog_customization_options[summary_section_heading]"
                                    value="<?php echo esc_attr($options['summary_section_heading']); ?>"
                                    class="regular-text">
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Pricing Settings -->
                <div class="qiog-admin-section">
                    <h2><span class="dashicons dashicons-money-alt"></span> Pricing Configuration</h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="base_price_3_stops">Base Price (3 Stops)</label></th>
                            <td>
                                <input type="number" id="base_price_3_stops"
                                    name="qiog_customization_options[base_price_3_stops]"
                                    value="<?php echo esc_attr($options['base_price_3_stops']); ?>" min="0" step="50"
                                    class="small-text">
                                <span class="description">$</span>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="base_price_4_stops">Base Price (4 Stops)</label></th>
                            <td>
                                <input type="number" id="base_price_4_stops"
                                    name="qiog_customization_options[base_price_4_stops]"
                                    value="<?php echo esc_attr($options['base_price_4_stops']); ?>" min="0" step="50"
                                    class="small-text">
                                <span class="description">$</span>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="max_stops">Maximum Stops (Default)</label></th>
                            <td>
                                <input type="number" id="max_stops" name="qiog_customization_options[max_stops]"
                                    value="<?php echo esc_attr($options['max_stops']); ?>" min="1" max="10"
                                    class="small-text">
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Feature Toggles -->
                <div class="qiog-admin-section">
                    <h2><span class="dashicons dashicons-admin-settings"></span> Feature Settings</h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row">Visual Features</th>
                            <td>
                                <fieldset>
                                    <label>
                                        <input type="checkbox" <?php checked($options['show_summary_icons'], 'yes'); ?>
                                            name="qiog_customization_options[show_summary_icons]">
                                        Show icons in summary sections
                                    </label><br>
                                    <label>
                                        <input type="checkbox" <?php checked($options['show_empty_state'], 'yes'); ?>
                                            name="qiog_customization_options[show_empty_state]">
                                        Show empty state messages
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Functionality</th>
                            <td>
                                <fieldset>
                                    <label>
                                        <input type="checkbox" name="qiog_customization_options[enable_upgrade_option]"
                                            <?php checked($options['enable_upgrade_option'], 'yes'); ?>>
                                        Enable upgrade to 4 stops option
                                    </label><br>
                                    <label>
                                        <input type="checkbox" name="qiog_customization_options[enable_quantity_controls]"
                                            <?php checked($options['enable_quantity_controls'], 'yes'); ?>>
                                        Enable quantity controls for add-ons
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Pickup Location Settings -->
                <div class="qiog-admin-section">
                    <h2><span class="dashicons dashicons-location"></span> Pickup Location</h2>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="pickup_label">Pickup Label</label>
                            </th>
                            <td>
                                <input type="text" id="pickup_label" name="qiog_customization_options[pickup_label]"
                                    value="<?php echo esc_attr($options['pickup_label']); ?>" class="regular-text">
                                <p class="description">Text shown on the map popup</p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="pickup_lat">Latitude</label>
                            </th>
                            <td>
                                <input type="number" step="0.00000001" id="pickup_lat"
                                    name="qiog_customization_options[pickup_lat]"
                                    value="<?php echo esc_attr($options['pickup_lat']); ?>" class="regular-text">
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="pickup_lng">Longitude</label>
                            </th>
                            <td>
                                <input type="number" step="0.00000001" id="pickup_lng"
                                    name="qiog_customization_options[pickup_lng]"
                                    value="<?php echo esc_attr($options['pickup_lng']); ?>" class="regular-text">
                                <p class="description">
                                    Tip: Paste coordinates from Google Maps (Right click → “What’s here?”)
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>


                <!-- Email Settings -->
                <div class="qiog-admin-section">
                    <h2><span class="dashicons dashicons-email"></span> Email Configuration</h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="admin_email">Admin Email</label></th>
                            <td>
                                <input type="email" id="admin_email" name="qiog_customization_options[admin_email]"
                                    value="<?php echo esc_attr($options['admin_email']); ?>" class="regular-text">
                                <p class="description">Email address to receive booking notifications</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="email_subject">Email Subject</label></th>
                            <td>
                                <input type="text" id="email_subject" name="qiog_customization_options[email_subject]"
                                    value="<?php echo esc_attr($options['email_subject']); ?>" class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Customer Notifications</th>
                            <td>
                                <label>
                                    <input type="checkbox" <?php checked($options['enable_customer_email'], 'yes'); ?>
                                        name="qiog_customization_options[enable_customer_email]">
                                    Send confirmation email to customers
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>

            </div>

            <div class="qiog-submit-wrapper">
                <?php submit_button('Save All Settings', 'primary large', 'submit', false); ?>
                <button type="button" class="button button-secondary"
                    onclick="if(confirm('Reset all settings to defaults?')) { window.location.href='<?php echo esc_url($reset_url); ?>'; }">
                    Reset to Defaults
                </button>
            </div>
        </form>

        <!-- Preview Section -->
        <div class="qiog-admin-section qiog-preview-section">
            <h2><span class="dashicons dashicons-visibility"></span> Live Preview</h2>
            <div class="qiog-preview-box">
                <div class="preview-item" style="background: <?php echo esc_attr($options['primary_color']); ?>;">
                    Primary Color
                </div>
                <div class="preview-item" style="background: <?php echo esc_attr($options['secondary_color']); ?>;">
                    Secondary Color
                </div>
                <div class="preview-item" style="background: <?php echo esc_attr($options['accent_color']); ?>;">
                    Accent Color
                </div>
            </div>
            <p class="description">Preview how your colors will look</p>
        </div>

    </div>

    <style>
        .qiog-admin-wrap {
            background: #f0f0f1;
            margin: -20px -20px 0 -42px;
            padding: 30px 40px;
        }

        .qiog-admin-wrap h1 {
            color: #1d2327;
            font-size: 28px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .qiog-admin-wrap h1 .dashicons {
            font-size: 32px;
            width: 32px;
            height: 32px;
        }

        .qiog-admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .qiog-admin-section {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .qiog-admin-section h2 {
            margin: 0 0 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f1;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .qiog-admin-section h2 .dashicons {
            color: #2271b1;
        }

        .qiog-color-picker {
            height: 40px;
            width: 80px;
            border: 2px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
        }

        .qiog-submit-wrapper {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .qiog-preview-section {
            margin-top: 20px;
        }

        .qiog-preview-box {
            display: flex;
            gap: 15px;
            margin: 15px 0;
        }

        .preview-item {
            flex: 1;
            padding: 30px 20px;
            color: white;
            text-align: center;
            border-radius: 6px;
            font-weight: 600;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }
    </style>
    <?php
}