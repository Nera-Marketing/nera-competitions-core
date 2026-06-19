<?php
/**
 * ACF — Heading Style global settings.
 *
 * Adds a "Headings" sub-page under Theme Settings with the site-wide default
 * heading font + accent colour. Per-section overrides live on each section
 * component's Flexible Content layout (see nera_heading_style_fields()).
 *
 * Pure ACF registration (options page + field group) — no other hooks.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
    exit();
}

if (function_exists('acf_add_options_page')) {
    // Ensure the shared Theme Settings parent exists.
    if (!function_exists('acf_get_options_page') || !acf_get_options_page('theme-settings')) {
        acf_add_options_page([
            'page_title' => 'Theme Settings',
            'menu_title' => 'Theme Settings',
            'menu_slug'  => 'theme-settings',
            'capability' => 'edit_posts',
            'redirect'   => false,
        ]);
    }

    acf_add_options_sub_page([
        'page_title'  => 'Heading Style',
        'menu_title'  => 'Headings',
        'menu_slug'   => 'heading-style',
        'parent_slug' => 'theme-settings',
    ]);
}

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group([
        'key'    => 'group_heading_style',
        'title'  => 'Heading Style',
        'fields' => [
            [
                'key'           => 'field_heading_default_font',
                'label'         => 'Default Highlight Font',
                'name'          => 'heading_default_font',
                'type'          => 'select',
                'instructions'  => 'Font for the highlighted (accent) part of section headings. Sections can override this individually.',
                'choices'       => function_exists('nera_heading_font_choices')
                    ? nera_heading_font_choices(false)
                    : ['poppins' => 'Poppins'],
                'default_value' => 'poppins',
                'allow_null'    => 0,
                'ui'            => 0,
            ],
            [
                'key'               => 'field_heading_default_font_custom',
                'label'             => 'Custom Google Font',
                'name'              => 'heading_default_font_custom',
                'type'              => 'text',
                'instructions'      => 'Google Font family, e.g. "Bricolage Grotesque" or "Bricolage Grotesque:wght@700;800".',
                'conditional_logic' => [
                    [
                        ['field' => 'field_heading_default_font', 'operator' => '==', 'value' => 'custom'],
                    ],
                ],
            ],
            [
                'key'           => 'field_heading_default_accent_color',
                'label'         => 'Default Accent Colour',
                'name'          => 'heading_default_accent_color',
                'type'          => 'color_picker',
                'instructions'  => 'Colour used for the highlighted (trailing) part of section headings. Sections can override this.',
                'default_value' => '#84cc16',
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'options_page',
                    'operator' => '==',
                    'value'    => 'heading-style',
                ],
            ],
        ],
        'menu_order'      => 0,
        'style'           => 'default',
        'label_placement' => 'top',
        'active'          => true,
    ]);
}
