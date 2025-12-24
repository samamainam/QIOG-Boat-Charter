<?php
if (!defined('ABSPATH')) {
    exit;
}


function qiog_create_required_pages()
{
    // ONLY builder page now
    $page = [
        'option_key' => 'qiog_builder_page_id',
        'title' => 'Build Your Charter',
        'slug' => 'build-your-charter',
        'content' => '[qiog_build_charter]',
    ];

    $existing_page_id = get_option($page['option_key']);
    if ($existing_page_id && get_post_status($existing_page_id)) {
        return;
    }

    $existing_page = get_page_by_path($page['slug']);

    if ($existing_page) {
        update_option($page['option_key'], $existing_page->ID);
        return;
    }

    $page_id = wp_insert_post([
        'post_name' => $page['slug'],
        'post_title' => $page['title'],
        'post_content' => $page['content'],
        'post_status' => 'publish',
        'post_type' => 'page',
    ]);

    if (!is_wp_error($page_id)) {
        update_option($page['option_key'], $page_id);
    }
}



function qiog_sync_checkout_page($old_options, $new_options)
{
    if (empty($new_options['checkout_page_slug'])) {
        return;
    }

    $new_slug = sanitize_title($new_options['checkout_page_slug']);
    $old_slug = isset($old_options['checkout_page_slug'])
        ? sanitize_title($old_options['checkout_page_slug'])
        : '';

    // No change → do nothing
    if ($new_slug === $old_slug) {
        return;
    }

    $old_page_id = absint(get_option('qiog_generated_checkout_page_id'));

    // Delete old generated checkout page
    if ($old_page_id && get_post($old_page_id)) {
        wp_delete_post($old_page_id, true);
    }

    // Create new checkout page
    $new_page_id = wp_insert_post([
        'post_title' => ucwords(str_replace('-', ' ', $new_slug)),
        'post_name' => $new_slug,
        'post_content' => '[qiog_charter_checkout]',
        'post_status' => 'publish',
        'post_type' => 'page',
    ]);

    if (!is_wp_error($new_page_id)) {
        update_option('qiog_generated_checkout_page_id', $new_page_id);
    }
}
