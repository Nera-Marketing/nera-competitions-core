<?php
/**
 * ACF Field Group: Closed Prizes page settings.
 *
 * Per-page empty-state copy for the Nera Closed Prizes template.
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
  'key' => 'group_closed_prizes_page',
  'title' => __('Closed Prizes Settings', 'nera-competitions'),
  'fields' => [
    [
      'key' => 'field_closed_prizes_tab_empty',
      'label' => __('Empty state', 'nera-competitions'),
      'type' => 'tab',
      'placement' => 'top',
    ],
    [
      'key' => 'field_closed_prizes_empty_heading',
      'label' => __('Heading', 'nera-competitions'),
      'name' => 'closed_prizes_empty_heading',
      'type' => 'text',
      'instructions' => __(
        'Shown on this page when there are no closed prizes yet.',
        'nera-competitions',
      ),
      'default_value' => __('No closed prizes yet', 'nera-competitions'),
      'placeholder' => __('No closed prizes yet', 'nera-competitions'),
    ],
    [
      'key' => 'field_closed_prizes_empty_description',
      'label' => __('Description', 'nera-competitions'),
      'name' => 'closed_prizes_empty_description',
      'type' => 'textarea',
      'instructions' => __(
        'Supporting text below the empty-state heading.',
        'nera-competitions',
      ),
      'default_value' => __(
        'Check back after our competitions have drawn their winners.',
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
        'value' => 'page-templates/closed-prizes-template.php',
      ],
    ],
  ],
  'menu_order' => 0,
  'position' => 'normal',
  'style' => 'default',
  'label_placement' => 'top',
  'instruction_placement' => 'label',
  'active' => true,
  'description' => __('Settings for the Closed Prizes page template', 'nera-competitions'),
]);
