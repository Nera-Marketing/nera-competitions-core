<?php
/**
 * Shop listing layout helpers (All Competitions / WC shop page; Customizer theme_mod).
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

/**
 * WooCommerce Shop page ID.
 */
function nera_shop_page_id(): int
{
  if (!function_exists('wc_get_page_id')) {
    return 0;
  }

  $id = (int) wc_get_page_id('shop');

  return $id > 0 ? $id : 0;
}

/**
 * Sanitize ACF aspect-ratio input for inline CSS.
 *
 * @param string $raw Raw field value.
 * @return string|null Valid CSS aspect-ratio or null.
 */
function nera_sanitize_aspect_ratio(string $raw): ?string
{
  $raw = trim($raw);
  if ($raw === '') {
    return null;
  }

  if (preg_match('/^(\d+(?:\.\d+)?)\s*\/\s*(\d+(?:\.\d+)?)$/', $raw, $m)) {
    $w = (float) $m[1];
    $h = (float) $m[2];
    if ($w > 0 && $h > 0) {
      return $w . '/' . $h;
    }

    return null;
  }

  if (preg_match('/^(\d+(?:\.\d+)?)$/', $raw, $m)) {
    $n = (float) $m[1];
    if ($n > 0) {
      return (string) $n;
    }
  }

  return null;
}

/**
 * Shop listing settings (Customizer theme_mod; ACF post meta fallback before migration).
 *
 * @return array{
 *   grid_columns: int,
 *   card_layout: string,
 *   image_aspect_ratio: string|null
 * }
 */
function nera_get_shop_listing_settings(): array
{
  static $settings = null;

  if (is_customize_preview()) {
    $settings = null;
  }

  if ($settings !== null) {
    return $settings;
  }

  $defaults = [
    'grid_columns' => 3,
    'card_layout' => 'classic',
    'image_aspect_ratio' => null,
  ];

  if (get_option('nera_customizer_settings_migrated') || nera_theme_mod_is_set('nera_shop_grid_columns')) {
    $grid_raw = get_theme_mod('nera_shop_grid_columns', 3);
    $grid_columns = (int) $grid_raw === 4 ? 4 : 3;

    $layout_raw = get_theme_mod('nera_shop_card_layout', 'classic');
    $card_layout = $layout_raw === 'portrait' ? 'portrait' : 'classic';

    $aspect_mod = get_theme_mod('nera_shop_card_image_aspect_ratio', '');
    $image_aspect_ratio = is_string($aspect_mod) && $aspect_mod !== ''
      ? nera_sanitize_aspect_ratio($aspect_mod)
      : null;

    $settings = [
      'grid_columns' => $grid_columns,
      'card_layout' => $card_layout,
      'image_aspect_ratio' => $image_aspect_ratio,
    ];

    return $settings;
  }

  $page_id = nera_shop_page_id();
  if ($page_id <= 0 || !function_exists('get_field')) {
    $settings = $defaults;

    return $settings;
  }

  $grid_raw = get_field('shop_grid_columns', $page_id);
  $grid_columns = (int) $grid_raw === 4 ? 4 : 3;

  $layout_raw = get_field('shop_card_layout', $page_id);
  $card_layout = $layout_raw === 'portrait' ? 'portrait' : 'classic';

  $aspect_raw = get_field('shop_card_image_aspect_ratio', $page_id);
  $image_aspect_ratio = is_string($aspect_raw)
    ? nera_sanitize_aspect_ratio($aspect_raw)
    : null;

  $settings = [
    'grid_columns' => $grid_columns,
    'card_layout' => $card_layout,
    'image_aspect_ratio' => $image_aspect_ratio,
  ];

  return $settings;
}

/**
 * Whether CompetitionCard should use shop listing layout data.
 */
function nera_is_shop_listing_context(): bool
{
  if (function_exists('is_shop') && is_shop() && !is_search()) {
    return true;
  }

  if (wp_doing_ajax() && isset($_REQUEST['action'])) {
    return (string) wp_unslash($_REQUEST['action']) === 'nera_advanced_filter_competitions';
  }

  return false;
}

/**
 * Tailwind class for advanced-filter grid columns at xl breakpoint.
 */
function nera_shop_listing_grid_lg_class(): string
{
  $settings = nera_get_shop_listing_settings();

  return ($settings['grid_columns'] ?? 3) === 4 ? 'xl:grid-cols-4' : 'xl:grid-cols-3';
}

add_filter('nera_component_data_CompetitionCard', function (array $data, array $args): array {
  if (empty($data) || !nera_is_shop_listing_context()) {
    return $data;
  }

  $settings = nera_get_shop_listing_settings();
  $data['card_layout'] = $settings['card_layout'];
  $data['image_aspect_ratio'] = $settings['image_aspect_ratio'];

  return $data;
}, 10, 2);

add_filter('nera_advanced_filter_posts_per_page', function (int $per_page): int {
  if (!nera_is_shop_listing_context()) {
    return $per_page;
  }

  $settings = nera_get_shop_listing_settings();

  return ($settings['grid_columns'] ?? 3) === 4 ? 12 : $per_page;
});
