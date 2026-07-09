<?php
/**
 * ACF — Winners Theme Settings (site-wide).
 *
 * Adds a "Winners" sub-page under Theme Settings for copy used on the
 * Winners (Dynamic) page empty state.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

if (function_exists('acf_add_options_page')) {
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
    'page_title'  => 'Winners Settings',
    'menu_title'  => 'Winners',
    'menu_slug'   => 'winners-settings',
    'parent_slug' => 'theme-settings',
  ]);
}

if (function_exists('acf_add_local_field_group')) {
  acf_add_local_field_group([
    'key'    => 'group_winners_theme_settings',
    'title'  => __('Winners — Dynamic page', 'nera-competitions'),
    'fields' => [
      [
        'key'       => 'field_winners_dynamic_tab_empty',
        'label'     => __('Empty state', 'nera-competitions'),
        'type'      => 'tab',
        'placement' => 'top',
      ],
      [
        'key'           => 'field_winners_dynamic_empty_heading',
        'label'         => __('Heading', 'nera-competitions'),
        'name'          => 'winners_dynamic_empty_heading',
        'type'          => 'text',
        'instructions'  => __(
          'Shown on the Winners (Dynamic) page when there are no winners at all.',
          'nera-competitions',
        ),
        'default_value' => __('No winners to show yet', 'nera-competitions'),
        'placeholder'   => __('No winners to show yet', 'nera-competitions'),
      ],
      [
        'key'           => 'field_winners_dynamic_empty_description',
        'label'         => __('Description', 'nera-competitions'),
        'name'          => 'winners_dynamic_empty_description',
        'type'          => 'textarea',
        'instructions'  => __(
          'Supporting text below the empty-state heading.',
          'nera-competitions',
        ),
        'default_value' => __(
          'Winners appear here once competitions have ended and winners are selected in the giveaway settings.',
          'nera-competitions',
        ),
        'rows'          => 3,
        'new_lines'     => '',
      ],
    ],
    'location' => [
      [
        [
          'param'    => 'options_page',
          'operator' => '==',
          'value'    => 'winners-settings',
        ],
      ],
    ],
    'menu_order'            => 0,
    'position'              => 'normal',
    'style'                 => 'default',
    'label_placement'       => 'top',
    'instruction_placement' => 'label',
    'active'                => true,
    'description'           => __(
      'Shown on the Winners (Dynamic) page when there are no winners at all.',
      'nera-competitions',
    ),
  ]);
}

/**
 * Empty-state copy for the Winners (Dynamic) grid.
 *
 * @return array{heading: string, description: string}
 */
function nera_get_winners_dynamic_empty_copy()
{
  $heading = function_exists('get_field')
    ? get_field('winners_dynamic_empty_heading', 'option')
    : '';
  $description = function_exists('get_field')
    ? get_field('winners_dynamic_empty_description', 'option')
    : '';

  $heading = is_string($heading) ? trim($heading) : '';
  $description = is_string($description) ? trim($description) : '';

  return [
    'heading' => $heading !== ''
      ? $heading
      : __('No winners to show yet', 'nera-competitions'),
    'description' => $description !== ''
      ? $description
      : __(
        'Winners appear here once competitions have ended and winners are selected in the giveaway settings.',
        'nera-competitions',
      ),
  ];
}
