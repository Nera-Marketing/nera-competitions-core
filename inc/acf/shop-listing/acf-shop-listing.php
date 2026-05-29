<?php
/**
 * ACF — Shop listing layout (WooCommerce Shop page).
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

/**
 * Register custom ACF page_type value for the WooCommerce Shop page.
 *
 * @param array<string, string> $choices Existing page_type choices.
 * @return array<string, string>
 */
function nera_acf_location_page_type_choices_woo_shop(array $choices): array
{
  $choices['woo_shop_page'] = __('WooCommerce Shop Page', 'nera-competitions');

  return $choices;
}
add_filter('acf/location/rule_values/page_type', 'nera_acf_location_page_type_choices_woo_shop');

/**
 * Match custom woo_shop_page location rule against the current edit screen.
 *
 * @param bool  $match   Whether the rule matches.
 * @param array $rule    Location rule.
 * @param array $options Screen options (includes post_id).
 */
function nera_acf_location_match_page_type_woo_shop(bool $match, array $rule, array $options): bool
{
  if (($rule['value'] ?? '') !== 'woo_shop_page' || !isset($options['post_id'])) {
    return $match;
  }

  if (!function_exists('wc_get_page_id')) {
    return false;
  }

  $shop_page_id = (int) wc_get_page_id('shop');
  if ($shop_page_id <= 0) {
    return false;
  }

  $post_id = (int) $options['post_id'];

  if (($rule['operator'] ?? '==') === '!=') {
    return $post_id !== $shop_page_id;
  }

  return $post_id === $shop_page_id;
}
add_filter('acf/location/rule_match/page_type', 'nera_acf_location_match_page_type_woo_shop', 10, 3);

/**
 * Register Shop Listing field group on the WooCommerce Shop page.
 */
function nera_register_shop_listing_fields(): void
{
  if (!function_exists('acf_add_local_field_group')) {
    return;
  }

  $shop_page_id = function_exists('wc_get_page_id') ? (int) wc_get_page_id('shop') : 0;
  if ($shop_page_id <= 0) {
    return;
  }

  $location = [
    [
      [
        'param' => 'page',
        'operator' => '==',
        'value' => (string) $shop_page_id,
      ],
    ],
    [
      [
        'param' => 'page_type',
        'operator' => '==',
        'value' => 'woo_shop_page',
      ],
    ],
  ];

  acf_add_local_field_group([
    'key' => 'group_nera_shop_listing',
    'title' => __('Shop Listing', 'nera-competitions'),
    'fields' => [
      [
        'key' => 'field_shop_grid_columns',
        'label' => __('Grid columns (desktop)', 'nera-competitions'),
        'name' => 'shop_grid_columns',
        'type' => 'select',
        'instructions' => __('Number of competition cards per row on large screens.', 'nera-competitions'),
        'choices' => [
          '3' => __('3 per row (default)', 'nera-competitions'),
          '4' => __('4 per row', 'nera-competitions'),
        ],
        'default_value' => '3',
        'return_format' => 'value',
      ],
      [
        'key' => 'field_shop_card_layout',
        'label' => __('Card layout', 'nera-competitions'),
        'name' => 'shop_card_layout',
        'type' => 'select',
        'instructions' => __(
          'Classic keeps the landscape image and side-by-side footer. Portrait stacks the footer and uses a taller image preset when aspect ratio is empty.',
          'nera-competitions',
        ),
        'choices' => [
          'classic' => __('Classic (landscape image, side-by-side footer)', 'nera-competitions'),
          'portrait' => __('Portrait (stacked footer)', 'nera-competitions'),
        ],
        'default_value' => 'classic',
        'return_format' => 'value',
      ],
      [
        'key' => 'field_shop_card_image_aspect_ratio',
        'label' => __('Featured image aspect ratio', 'nera-competitions'),
        'name' => 'shop_card_image_aspect_ratio',
        'type' => 'text',
        'instructions' => __(
          'CSS aspect-ratio value. Leave empty to use the default for the selected card layout (classic: 5/3 mobile, 4/3 from sm; portrait: 4/5). Examples: 4/5, 16/9, 1.25',
          'nera-competitions',
        ),
        'placeholder' => '4/5',
      ],
    ],
    'location' => $location,
    'menu_order' => 5,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'active' => true,
  ]);
}
add_action('acf/init', 'nera_register_shop_listing_fields');

/**
 * Warn admins when the Shop page is not configured (field group cannot attach).
 */
function nera_shop_listing_admin_notice_missing_shop_page(): void
{
  if (!is_admin() || !current_user_can('manage_woocommerce')) {
    return;
  }

  $screen = function_exists('get_current_screen') ? get_current_screen() : null;
  if (!$screen || $screen->base !== 'post' || $screen->post_type !== 'page') {
    return;
  }

  $shop_page_id = function_exists('wc_get_page_id') ? (int) wc_get_page_id('shop') : 0;
  if ($shop_page_id > 0) {
    return;
  }

  echo '<div class="notice notice-warning"><p>';
  esc_html_e(
    'Nera Shop Listing fields require a WooCommerce Shop page. Set it under WooCommerce → Settings → Products.',
    'nera-competitions',
  );
  echo '</p></div>';
}
add_action('admin_notices', 'nera_shop_listing_admin_notice_missing_shop_page');
