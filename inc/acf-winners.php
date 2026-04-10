<?php
/**
 * ACF Field Group: Winners Page Content
 *
 * Registers custom fields for the Winners page template.
 *
 * @package Nera_Competitions
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
  exit(); // Exit if accessed directly
}

/**
 * Register ACF field group for Winners page
 */
function nera_register_winners_fields()
{
  if (!function_exists('acf_add_local_field_group')) {
    return;
  }

  acf_add_local_field_group([
    'key' => 'group_winners_page',
    'title' => __('Winners Page Content', 'nera-competitions'),
    'fields' => [
      // Hero Section Tab
      [
        'key' => 'field_winners_tab_hero',
        'label' => __('Hero Section', 'nera-competitions'),
        'type' => 'tab',
        'placement' => 'top',
      ],
      [
        'key' => 'field_winners_heading',
        'label' => __('Page Heading', 'nera-competitions'),
        'name' => 'winners_heading',
        'type' => 'text',
        'instructions' => __('Main heading for the winners page', 'nera-competitions'),
        'default_value' => __('Recent Winners', 'nera-competitions'),
        'placeholder' => __('Recent Winners', 'nera-competitions'),
      ],
      [
        'key' => 'field_winners_subheading',
        'label' => __('Subheading', 'nera-competitions'),
        'name' => 'winners_subheading',
        'type' => 'text',
        'instructions' => __('Optional subtitle (appears above main heading)', 'nera-competitions'),
        'default_value' => __('Our Lucky Winners', 'nera-competitions'),
        'placeholder' => __('Our Lucky Winners', 'nera-competitions'),
      ],
      [
        'key' => 'field_winners_description',
        'label' => __('Description', 'nera-competitions'),
        'name' => 'winners_description',
        'type' => 'textarea',
        'instructions' => __('Supporting text below the heading', 'nera-competitions'),
        'rows' => 3,
      ],

      // Winners List Tab
      [
        'key' => 'field_winners_tab_list',
        'label' => __('Winners List', 'nera-competitions'),
        'type' => 'tab',
        'placement' => 'top',
      ],
      [
        'key' => 'field_winners_per_page',
        'label' => __('Winners Per Page', 'nera-competitions'),
        'name' => 'winners_per_page',
        'type' => 'number',
        'instructions' => __(
          'Number of winners to show initially (before load more)',
          'nera-competitions',
        ),
        'default_value' => 12,
        'min' => 4,
        'max' => 48,
        'step' => 4,
      ],
      [
        'key' => 'field_winners_list',
        'label' => __('Winners', 'nera-competitions'),
        'name' => 'winners_list',
        'type' => 'repeater',
        'instructions' => __('Add winner entries', 'nera-competitions'),
        'layout' => 'row',
        'button_label' => __('Add Winner', 'nera-competitions'),
        'sub_fields' => [
          [
            'key' => 'field_winner_name',
            'label' => __('Winner Name', 'nera-competitions'),
            'name' => 'name',
            'type' => 'text',
            'required' => 1,
            'placeholder' => __('John Doe', 'nera-competitions'),
          ],
          [
            'key' => 'field_winner_prize',
            'label' => __('Prize Won', 'nera-competitions'),
            'name' => 'prize',
            'type' => 'text',
            'required' => 1,
            'placeholder' => __('£50,000 Cash Prize', 'nera-competitions'),
          ],
          [
            'key' => 'field_winner_date',
            'label' => __('Date Won', 'nera-competitions'),
            'name' => 'date',
            'type' => 'date_picker',
            'display_format' => 'd/m/Y',
            'return_format' => 'F j, Y',
            'first_day' => 1,
          ],
          [
            'key' => 'field_winner_image',
            'label' => __('Prize Photo', 'nera-competitions'),
            'name' => 'image',
            'type' => 'image',
            'instructions' => __('Upload a photo of the prize or winner', 'nera-competitions'),
            'return_format' => 'array',
            'preview_size' => 'medium',
            'library' => 'all',
          ],
          [
            'key' => 'field_winner_quote',
            'label' => __('Winner Quote', 'nera-competitions'),
            'name' => 'quote',
            'type' => 'textarea',
            'instructions' => __('Short message or quote from the winner', 'nera-competitions'),
            'rows' => 2,
            'placeholder' => __(
              'I can\'t believe I won! This is life-changing!',
              'nera-competitions',
            ),
          ],
          [
            'key' => 'field_winner_category',
            'label' => __('Win Category', 'nera-competitions'),
            'name' => 'category',
            'type' => 'select',
            'choices' => [],
            'default_value' => 'live-draw',
            'return_format' => 'value',
          ],
        ],
      ],

      // Display Settings Tab
      [
        'key' => 'field_winners_tab_display',
        'label' => __('Display Settings', 'nera-competitions'),
        'type' => 'tab',
        'placement' => 'top',
      ],
      [
        'key' => 'field_win_categories',
        'label' => __('Win Categories', 'nera-competitions'),
        'name' => 'win_categories',
        'type' => 'repeater',
        'instructions' => __(
          'Define the category options shown in the Win Category dropdown and filter tabs. Add, edit, or remove rows as needed.',
          'nera-competitions',
        ),
        'layout' => 'row',
        'button_label' => __('Add Category', 'nera-competitions'),
        'sub_fields' => [
          [
            'key' => 'field_win_category_value',
            'label' => __('Value (slug)', 'nera-competitions'),
            'name' => 'value',
            'type' => 'text',
            'required' => 1,
            'placeholder' => __('e.g. live-draw', 'nera-competitions'),
            'instructions' => __('URL-friendly slug used in filters', 'nera-competitions'),
          ],
          [
            'key' => 'field_win_category_label',
            'label' => __('Label', 'nera-competitions'),
            'name' => 'label',
            'type' => 'text',
            'required' => 1,
            'placeholder' => __('e.g. Live Draw', 'nera-competitions'),
            'instructions' => __('Display name in dropdown and filter tabs', 'nera-competitions'),
          ],
        ],
      ],
      [
        'key' => 'field_winners_show_filters',
        'label' => __('Show Filter Tabs', 'nera-competitions'),
        'name' => 'winners_show_filters',
        'type' => 'true_false',
        'instructions' => __(
          'Toggle visibility of category filter tabs (All / Live Draws / Instant Wins)',
          'nera-competitions',
        ),
        'default_value' => 1,
        'ui' => 1,
      ],
      [
        'key' => 'field_winners_show_quotes',
        'label' => __('Show Winner Quotes', 'nera-competitions'),
        'name' => 'winners_show_quotes',
        'type' => 'true_false',
        'instructions' => __('Toggle visibility of winner quotes on cards', 'nera-competitions'),
        'default_value' => 1,
        'ui' => 1,
      ],
    ],
    'location' => [
      [
        [
          'param' => 'page_template',
          'operator' => '==',
          'value' => 'page-templates/winners-template.php',
        ],
      ],
    ],
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => ['the_content', 'featured_image'],
    'active' => true,
    'description' => __('Custom fields for the Winners page template', 'nera-competitions'),
  ]);
}
add_action('acf/init', 'nera_register_winners_fields');

/**
 * Get Win Categories from ACF repeater (value => label)
 *
 * @param int|null $page_id Winners page ID. If null, finds page by template.
 * @return array Associative array of value => label.
 */
function nera_get_win_categories($page_id = null)
{
  if (!$page_id) {
    $pages = get_pages([
      'meta_key' => '_wp_page_template',
      'meta_value' => 'page-templates/winners-template.php',
      'number' => 1,
    ]);
    $page_id = !empty($pages) ? $pages[0]->ID : 0;
  }
  $rows = $page_id ? get_field('win_categories', $page_id) : [];
  $out = [];
  if (is_array($rows) && !empty($rows)) {
    foreach ($rows as $row) {
      $v = sanitize_key(str_replace(' ', '-', trim($row['value'] ?? '')));
      $l = sanitize_text_field(trim($row['label'] ?? ''));
      if ($v && $l) {
        $out[$v] = $l;
      }
    }
  }
  return !empty($out) ? $out : ['live-draw' => 'Live Draw', 'instant-win' => 'Instant Win'];
}

add_filter('acf/load_field/key=field_winner_category', 'nera_load_win_category_choices');
function nera_load_win_category_choices($field)
{
  $field['choices'] = nera_get_win_categories();
  return $field;
}
