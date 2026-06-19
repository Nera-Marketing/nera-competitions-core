<?php
/**
 * ACF Field Group: Winners (Dynamic) Settings
 *
 * Per-page control for the "Nera Winners (Dynamic)" template — chooses which
 * winner types (Live draw / Instant Win) the page surfaces.
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
