<?php
/**
 * ACf Footer Settings
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

if (function_exists('acf_add_options_page')) {
  // Check if Theme Settings page exists, if not create it
  if (!function_exists('acf_get_options_page') || !acf_get_options_page('theme-settings')) {
    acf_add_options_page([
      'page_title' => 'Theme Settings',
      'menu_title' => 'Theme Settings',
      'menu_slug' => 'theme-settings',
      'capability' => 'edit_posts',
      'redirect' => false,
    ]);
  }

  // Add Footer Settings Subpage
  acf_add_options_sub_page([
    'page_title' => 'Footer Settings',
    'menu_title' => 'Footer',
    'parent_slug' => 'theme-settings',
  ]);
}

if (function_exists('acf_add_local_field_group')) {
  acf_add_local_field_group([
    'key' => 'group_neracompetitions_footer',
    'title' => 'Footer Settings',
    'fields' => [
      [
        'key' => 'field_footer_legal_disclaimer',
        'label' => 'Legal Disclaimer',
        'name' => 'footer_legal_disclaimer',
        'type' => 'textarea',
        'instructions' => 'Shown above the copyright line in the footer bottom strip. Leave empty to use the theme default text.',
        'required' => 0,
        'conditional_logic' => 0,
        'wrapper' => [
          'width' => '100',
          'class' => '',
          'id' => '',
        ],
        'default_value' => '',
        'placeholder' => '',
        'maxlength' => '',
        'rows' => 4,
        'new_lines' => '',
      ],
      [
        'key' => 'field_footer_copyright',
        'label' => 'Copyright Text',
        'name' => 'footer_copyright',
        'type' => 'wysiwyg',
        'instructions' => 'Enter the copyright text. Use {year} for dynamic current year.',
        'required' => 0,
        'conditional_logic' => 0,
        'wrapper' => [
          'width' => '50',
          'class' => '',
          'id' => '',
        ],
        'default_value' => '&copy; {year} ' . get_bloginfo('name') . '. All rights reserved.',
        'tabs' => 'all',
        'toolbar' => 'basic',
        'media_upload' => 0,
        'delay' => 0,
      ],
      [
        'key' => 'field_footer_bottom_right',
        'label' => 'Bottom Right Content',
        'name' => 'footer_bottom_right',
        'type' => 'wysiwyg',
        'instructions' => 'Content for the bottom right area (e.g., Payment Icons).',
        'required' => 0,
        'conditional_logic' => 0,
        'wrapper' => [
          'width' => '50',
          'class' => '',
          'id' => '',
        ],
        'default_value' => '',
        'tabs' => 'all',
        'toolbar' => 'full',
        'media_upload' => 1,
        'delay' => 0,
      ],
    ],
    'location' => [
      [
        [
          'param' => 'options_page',
          'operator' => '==',
          'value' => 'acf-options-footer',
        ],
      ],
    ],
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => true,
    'description' => '',
  ]);
}
