<?php
/**
 * ACF Field Group: Entry List Listing page settings.
 *
 * Per-page empty-state copy for the Nera Entry List Listing template.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

if (!function_exists('acf_add_local_field_group')) {
  return;
}

acf_add_local_field_group([
  'key' => 'group_entry_list_listing_page',
  'title' => __('Entry List Settings', 'nera-competitions'),
  'fields' => [
    [
      'key' => 'field_entry_list_tab_empty',
      'label' => __('Empty state', 'nera-competitions'),
      'type' => 'tab',
      'placement' => 'top',
    ],
    [
      'key' => 'field_entry_list_empty_heading',
      'label' => __('Heading', 'nera-competitions'),
      'name' => 'entry_list_empty_heading',
      'type' => 'text',
      'instructions' => __(
        'Shown on this page when there are no competitions with participant lists yet.',
        'nera-competitions',
      ),
      'default_value' => __('No competitions found', 'nera-competitions'),
      'placeholder' => __('No competitions found', 'nera-competitions'),
    ],
    [
      'key' => 'field_entry_list_empty_description',
      'label' => __('Description', 'nera-competitions'),
      'name' => 'entry_list_empty_description',
      'type' => 'textarea',
      'instructions' => __(
        'Supporting text below the empty-state heading.',
        'nera-competitions',
      ),
      'default_value' => __(
        'There are no participant lists available yet. Please check back soon.',
        'nera-competitions',
      ),
      'rows' => 3,
      'new_lines' => '',
    ],
  ],
  'location' => [
    [
      [
        'param' => 'page_template',
        'operator' => '==',
        'value' => 'page-templates/entry-list-listing-template.php',
      ],
    ],
  ],
  'menu_order' => 0,
  'position' => 'normal',
  'style' => 'default',
  'label_placement' => 'top',
  'instruction_placement' => 'label',
  'active' => true,
  'description' => __('Settings for the Entry List Listing page template', 'nera-competitions'),
]);
