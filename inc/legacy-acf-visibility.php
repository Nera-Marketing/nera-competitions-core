<?php
/**
 * Hide legacy per-page ACF metaboxes when a page is driven by Timber Page Components.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
    exit();
}

/**
 * Remove legacy per-page ACF metaboxes once the page uses the Page Components
 * builder. Keeps them visible when page_components is empty so each template's
 * legacy fallback layout stays editable.
 *
 * Each legacy group is bound (by ACF location) to its own page template, so
 * removing all of them globally — only when components are present — is safe.
 */
add_action('add_meta_boxes', function () {
    global $post;
    if (!$post instanceof WP_Post || $post->post_type !== 'page') {
        return;
    }
    if (!function_exists('get_field')) {
        return;
    }

    $rows = get_field('page_components', $post->ID);
    if (!is_array($rows) || empty($rows)) {
        return;
    }

    $legacy_group_keys = [
        'group_how_it_works_page',
        'group_about_us_page',
    ];
    foreach ($legacy_group_keys as $key) {
        remove_meta_box('acf-' . $key, 'page', 'normal');
    }
}, 20); // after ACF registers its metaboxes (priority 10)
