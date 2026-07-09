<?php
/**
 * ACF Field Group: Winners (Dynamic) Settings
 *
 * Per-page control for the "Nera Winners (Dynamic)" template — winner types
 * to show and empty-state copy when there are no winners.
 *
 * @package Nera_Competitions
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
  exit(); // Exit if accessed directly
}

/**
 * Register ACF field group for the dynamic Winners page.
 */
function nera_register_winners_dynamic_fields()
{
  if (!function_exists('acf_add_local_field_group')) {
    return;
  }

  acf_add_local_field_group([
    'key' => 'group_winners_dynamic_settings',
    'title' => __('Winners Dynamic Settings', 'nera-competitions'),
    'fields' => [
      [
        'key' => 'field_winners_dynamic_tab_types',
        'label' => __('Display', 'nera-competitions'),
        'type' => 'tab',
        'placement' => 'top',
      ],
      [
        'key' => 'field_winners_dynamic_show_types',
        'label' => __('Winner types to show', 'nera-competitions'),
        'name' => 'winners_dynamic_show_types',
        'type' => 'checkbox',
        'instructions' => __(
          'Choose which winner types appear on this page. Untick a type to hide it from this page entirely — its cards, count and filter tab are all removed. If only one type is ticked, the filter tabs are hidden.',
          'nera-competitions',
        ),
        'choices' => [
          'main' => __('Live draw winners', 'nera-competitions'),
          'instant' => __('Instant Win winners', 'nera-competitions'),
        ],
        'default_value' => ['main', 'instant'],
        'allow_custom' => 0,
        'layout' => 'vertical',
        'return_format' => 'value',
      ],
      [
        'key' => 'field_winners_dynamic_tab_empty',
        'label' => __('Empty state', 'nera-competitions'),
        'type' => 'tab',
        'placement' => 'top',
      ],
      [
        'key' => 'field_winners_dynamic_empty_heading',
        'label' => __('Heading', 'nera-competitions'),
        'name' => 'winners_dynamic_empty_heading',
        'type' => 'text',
        'instructions' => __(
          'Shown on this page when there are no winners at all.',
          'nera-competitions',
        ),
        'default_value' => __('No winners to show yet', 'nera-competitions'),
        'placeholder' => __('No winners to show yet', 'nera-competitions'),
      ],
      [
        'key' => 'field_winners_dynamic_empty_description',
        'label' => __('Description', 'nera-competitions'),
        'name' => 'winners_dynamic_empty_description',
        'type' => 'textarea',
        'instructions' => __(
          'Supporting text below the empty-state heading.',
          'nera-competitions',
        ),
        'default_value' => __(
          'Winners appear here once competitions have ended and winners are selected in the giveaway settings.',
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
          'value' => 'page-templates/winners-dynamic-template.php',
        ],
      ],
    ],
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'active' => true,
    'description' => __('Settings for the dynamic Winners page template', 'nera-competitions'),
  ]);
}
add_action('acf/init', 'nera_register_winners_dynamic_fields');

/**
 * Empty-state copy for the Winners (Dynamic) grid.
 *
 * Reads per-page ACF first, then falls back to legacy option values, then defaults.
 *
 * @param int|null $page_id Page ID. Defaults to the current queried/post ID.
 * @return array{heading: string, description: string}
 */
function nera_get_winners_dynamic_empty_copy($page_id = null)
{
  if ($page_id === null) {
    $page_id = get_queried_object_id() ?: get_the_ID();
  }
  $page_id = absint($page_id);

  $heading = '';
  $description = '';

  if (function_exists('get_field') && $page_id > 0) {
    $heading = get_field('winners_dynamic_empty_heading', $page_id);
    $description = get_field('winners_dynamic_empty_description', $page_id);
  }

  $heading = is_string($heading) ? trim($heading) : '';
  $description = is_string($description) ? trim($description) : '';

  // Legacy Theme Settings / WooCommerce option values (pre per-page move).
  if (($heading === '' || $description === '') && function_exists('get_field')) {
    if ($heading === '') {
      $opt_heading = get_field('winners_dynamic_empty_heading', 'option');
      $heading = is_string($opt_heading) ? trim($opt_heading) : '';
    }
    if ($description === '') {
      $opt_description = get_field('winners_dynamic_empty_description', 'option');
      $description = is_string($opt_description) ? trim($opt_description) : '';
    }
  }

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
