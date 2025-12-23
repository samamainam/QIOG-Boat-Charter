<?php
if (!defined('ABSPATH'))
    exit;


add_action('admin_init', 'qiog_register_email_settings');

function qiog_register_email_settings()
{
    register_setting('qiog_email_settings', 'qiog_email_settings');

    add_settings_section(
        'qiog_email_main',
        'Email Notifications',
        null,
        'qiog-email-settings'
    );

    add_settings_field(
        'enable_admin_email',
        'Enable Admin Email',
        'qiog_checkbox_field',
        'qiog-email-settings',
        'qiog_email_main',
        ['key' => 'enable_admin_email']
    );

    add_settings_field(
        'enable_customer_email',
        'Enable Customer Email',
        'qiog_checkbox_field',
        'qiog-email-settings',
        'qiog_email_main',
        ['key' => 'enable_customer_email']
    );

    add_settings_field(
        'admin_subject',
        'Admin Email Subject',
        'qiog_text_field',
        'qiog-email-settings',
        'qiog_email_main',
        ['key' => 'admin_subject']
    );

    add_settings_field(
        'customer_subject',
        'Customer Email Subject',
        'qiog_text_field',
        'qiog-email-settings',
        'qiog_email_main',
        ['key' => 'customer_subject']
    );

    add_settings_field(
        'admin_template',
        'Admin Email Template',
        'qiog_email_editor_field',
        'qiog-email-settings',
        'qiog_email_main',
        [
            'key' => 'admin_template',
            'label' => 'Admin receives this email'
        ]
    );

    add_settings_field(
        'customer_template',
        'Customer Email Template',
        'qiog_email_editor_field',
        'qiog-email-settings',
        'qiog_email_main',
        [
            'key' => 'customer_template',
            'label' => 'Customer receives this email'
        ]
    );

}


function qiog_checkbox_field($args)
{
    $options = get_option('qiog_email_settings', []);
    $checked = isset($options[$args['key']]) ? 'checked' : '';
    echo "<input type='checkbox' name='qiog_email_settings[{$args['key']}]' value='1' $checked />";
}

function qiog_text_field($args)
{
    $options = get_option('qiog_email_settings', []);
    $value = esc_attr($options[$args['key']] ?? '');
    echo "<input type='text' class='regular-text' name='qiog_email_settings[{$args['key']}]' value='$value' />";
}

function qiog_textarea_field($args)
{
    $options = get_option('qiog_email_settings', []);
    $value = esc_textarea($options[$args['key']] ?? '');
    echo "<textarea rows='8' cols='70' name='qiog_email_settings[{$args['key']}]'>$value</textarea>";
}


function qiog_charter_email_settings_page()
{
    ?>
    <div class="wrap">
        <h1>Charter Email Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('qiog_email_settings');
            do_settings_sections('qiog-email-settings');
            submit_button();
            ?>
            <p><strong>Available placeholders:</strong><br>
                {{customer_name}}, {{customer_email}}, {{customer_phone}},
                {{package_name}}, {{stops}}, {{addons}},
                {{base_price}}, {{addon_total}}, {{grand_total}},
                {{notes}}, {{booking_id}}, {{site_name}}
            </p>
        </form>
    </div>
    <?php
}


function qiog_email_editor_field($args)
{
    $options = get_option('qiog_email_settings', []);
    $content = $options[$args['key']] ?? '';

    $editor_id = 'qiog_' . esc_attr($args['key']);

    echo '<p style="margin-bottom:8px;color:#555;">' . esc_html($args['label']) . '</p>';

    wp_editor(
        $content,
        $editor_id,
        [
            'textarea_name' => "qiog_email_settings[{$args['key']}]",
            'textarea_rows' => 12,
            'media_buttons' => true,
            'teeny' => false,
            'quicktags' => true,
            'tinymce' => [
                'toolbar1' => 'bold italic underline | alignleft aligncenter alignright | bullist numlist | link unlink | code',
                'toolbar2' => '',
            ],
        ]
    );

    qiog_email_placeholder_buttons();
}



function qiog_email_placeholder_buttons()
{
    $placeholders = [
        'customer_name',
        'customer_email',
        'customer_phone',
        'package_name',
        'stops',
        'addons',
        'base_price',
        'addon_total',
        'grand_total',
        'notes',
        'booking_id',
        'site_name',
    ];

    echo '<div style="margin:10px 0;">';
    echo '<strong>Insert Placeholder:</strong><br>';

    foreach ($placeholders as $ph) {
        echo '<button type="button" class="button qiog-insert-placeholder" data-placeholder="{{' . esc_attr($ph) . '}}">
            {{' . esc_html($ph) . '}}
        </button> ';
    }

    echo '</div>';
}

add_action('admin_footer', function () {
    ?>
    <script>
        jQuery(document).on('click', '.qiog-insert-placeholder', function () {
            const placeholder = jQuery(this).data('placeholder');

            if (typeof tinymce !== 'undefined' && tinymce.activeEditor) {
                tinymce.activeEditor.insertContent(placeholder);
            } else if (typeof QTags !== 'undefined') {
                QTags.insertContent(placeholder);
            }
        });
    </script>
    <?php
});
