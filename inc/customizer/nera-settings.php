<?php
/**
 * Nera Settings — WordPress Customizer panel (shop listing + quantity layout).
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

/**
 * Whether a theme_mod key exists in the active theme's mods array.
 *
 * @param string $name Theme mod name.
 */
function nera_theme_mod_is_set(string $name): bool
{
  $mods = get_theme_mods();

  return is_array($mods) && array_key_exists($name, $mods);
}

/**
 * One-time migration from ACF (shop page meta + quantity option) into theme_mod.
 */
function nera_migrate_customizer_settings_from_acf(): void
{
  if (get_option('nera_customizer_settings_migrated')) {
    return;
  }

  $page_id = nera_shop_page_id();

  if (!nera_theme_mod_is_set('nera_shop_grid_columns')) {
    $grid = 3;
    if ($page_id > 0 && function_exists('get_field')) {
      $grid_raw = get_field('shop_grid_columns', $page_id);
      $grid = (int) $grid_raw === 4 ? 4 : 3;
    }
    set_theme_mod('nera_shop_grid_columns', $grid);
  }

  if (!nera_theme_mod_is_set('nera_shop_card_layout')) {
    $layout = 'classic';
    if ($page_id > 0 && function_exists('get_field')) {
      $layout_raw = get_field('shop_card_layout', $page_id);
      $layout = $layout_raw === 'portrait' ? 'portrait' : 'classic';
    }
    set_theme_mod('nera_shop_card_layout', $layout);
  }

  if (!nera_theme_mod_is_set('nera_shop_card_image_aspect_ratio')) {
    $aspect = '';
    if ($page_id > 0 && function_exists('get_field')) {
      $aspect_raw = get_field('shop_card_image_aspect_ratio', $page_id);
      if (is_string($aspect_raw)) {
        $sanitized = nera_sanitize_aspect_ratio($aspect_raw);
        $aspect = $sanitized ?? '';
      }
    }
    set_theme_mod('nera_shop_card_image_aspect_ratio', $aspect);
  }

  if (!nera_theme_mod_is_set('nera_quantity_selector_layout')) {
    $qty = 'buttons';
    if (function_exists('get_field')) {
      $qty_raw = get_field('quantity_selector_layout', 'option');
      if (is_string($qty_raw) && in_array($qty_raw, ['buttons', 'slider'], true)) {
        $qty = $qty_raw;
      }
    }
    set_theme_mod('nera_quantity_selector_layout', $qty);
  }

  update_option('nera_customizer_settings_migrated', '1', false);
}
add_action('after_setup_theme', 'nera_migrate_customizer_settings_from_acf', 5);

/**
 * @param mixed $value Raw control value.
 */
function nera_sanitize_customizer_shop_grid_columns($value): int
{
  return (int) $value === 4 ? 4 : 3;
}

/**
 * @param mixed $value Raw control value.
 */
function nera_sanitize_customizer_shop_card_layout($value): string
{
  return $value === 'portrait' ? 'portrait' : 'classic';
}

/**
 * @param mixed $value Raw control value.
 */
function nera_sanitize_customizer_aspect_ratio_mod($value): string
{
  if (!is_string($value)) {
    return '';
  }

  $value = trim($value);
  if ($value === '') {
    return '';
  }

  $sanitized = nera_sanitize_aspect_ratio($value);

  return $sanitized ?? '';
}

/**
 * @param mixed $value Raw control value.
 */
function nera_sanitize_customizer_quantity_layout($value): string
{
  return $value === 'slider' ? 'slider' : 'buttons';
}

/**
 * Register Nera Settings panel, sections, and controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 */
function nera_customize_register_settings(WP_Customize_Manager $wp_customize): void
{
  $wp_customize->add_panel('nera_settings', [
    'title' => __('Nera Settings', 'nera-competitions'),
    'description' => __(
      'Site content max width, shop listing layout for the All Competitions page, and the site-wide quantity selector default.',
      'nera-competitions',
    ),
    'priority' => 160,
    'capability' => 'edit_theme_options',
  ]);

  $wp_customize->add_section('nera_site_layout', [
    'title' => __('Site layout', 'nera-competitions'),
    'panel' => 'nera_settings',
    'description' => __(
      'Maximum width for header, footer, and all sections using the Tailwind container class.',
      'nera-competitions',
    ),
    'priority' => 5,
  ]);

  $wp_customize->add_setting('nera_site_container_preset', [
    'default' => '1280',
    'sanitize_callback' => 'nera_sanitize_site_container_preset',
    'transport' => 'refresh',
  ]);

  $wp_customize->add_control('nera_site_container_preset', [
    'label' => __('Content max width', 'nera-competitions'),
    'description' => __(
      'Applies site-wide to container-aligned content (header, footer, page sections).',
      'nera-competitions',
    ),
    'section' => 'nera_site_layout',
    'type' => 'select',
    'choices' => [
      '1280' => __('Standard — 1280px', 'nera-competitions'),
      '1400' => __('Wide — 1400px', 'nera-competitions'),
      '1536' => __('Extra wide — 1536px', 'nera-competitions'),
      'custom' => __('Custom (px)', 'nera-competitions'),
    ],
  ]);

  $wp_customize->add_setting('nera_site_container_custom_px', [
    'default' => 1280,
    'sanitize_callback' => 'nera_sanitize_site_container_custom_px',
    'transport' => 'refresh',
  ]);

  $wp_customize->add_control('nera_site_container_custom_px', [
    'label' => __('Custom max width (px)', 'nera-competitions'),
    'description' => sprintf(
      /* translators: 1: min px, 2: max px */
      __('Used when Content max width is Custom. Allowed range: %1$d–%2$d.', 'nera-competitions'),
      NERA_SITE_CONTAINER_MIN_PX,
      NERA_SITE_CONTAINER_MAX_PX,
    ),
    'section' => 'nera_site_layout',
    'type' => 'number',
    'input_attrs' => [
      'min' => NERA_SITE_CONTAINER_MIN_PX,
      'max' => NERA_SITE_CONTAINER_MAX_PX,
      'step' => 1,
    ],
    'active_callback' => static function (\WP_Customize_Control $control): bool {
      $preset = $control->manager->get_setting('nera_site_container_preset');

      return $preset && $preset->value() === 'custom';
    },
  ]);

  $wp_customize->add_section('nera_shop_listing', [
    'title' => __('Shop Listing', 'nera-competitions'),
    'panel' => 'nera_settings',
    'description' => __(
      'Controls competition cards on the WooCommerce shop / All Competitions archive.',
      'nera-competitions',
    ),
    'priority' => 10,
  ]);

  $wp_customize->add_setting('nera_shop_grid_columns', [
    'default' => 3,
    'sanitize_callback' => 'nera_sanitize_customizer_shop_grid_columns',
    'transport' => 'postMessage',
  ]);

  $wp_customize->add_control('nera_shop_grid_columns', [
    'label' => __('Grid columns (desktop)', 'nera-competitions'),
    'description' => __('Number of competition cards per row on large screens.', 'nera-competitions'),
    'section' => 'nera_shop_listing',
    'type' => 'select',
    'choices' => [
      '3' => __('3 per row (default)', 'nera-competitions'),
      '4' => __('4 per row', 'nera-competitions'),
    ],
  ]);

  $wp_customize->add_setting('nera_shop_card_layout', [
    'default' => 'classic',
    'sanitize_callback' => 'nera_sanitize_customizer_shop_card_layout',
    'transport' => 'postMessage',
  ]);

  $wp_customize->add_control('nera_shop_card_layout', [
    'label' => __('Card layout', 'nera-competitions'),
    'description' => __(
      'Classic keeps the landscape image and side-by-side footer. Portrait stacks the footer and uses a taller image preset when aspect ratio is empty.',
      'nera-competitions',
    ),
    'section' => 'nera_shop_listing',
    'type' => 'select',
    'choices' => [
      'classic' => __('Classic (landscape image, side-by-side footer)', 'nera-competitions'),
      'portrait' => __('Portrait (stacked footer)', 'nera-competitions'),
    ],
  ]);

  $wp_customize->add_setting('nera_shop_card_image_aspect_ratio', [
    'default' => '',
    'sanitize_callback' => 'nera_sanitize_customizer_aspect_ratio_mod',
    'transport' => 'postMessage',
  ]);

  $wp_customize->add_control('nera_shop_card_image_aspect_ratio', [
    'label' => __('Featured image aspect ratio', 'nera-competitions'),
    'description' => __(
      'CSS aspect-ratio value. Leave empty to use the default for the selected card layout (classic: 5/3 mobile, 4/3 from sm; portrait: 4/5). Examples: 4/5, 16/9, 1.25',
      'nera-competitions',
    ),
    'section' => 'nera_shop_listing',
    'type' => 'text',
    'input_attrs' => [
      'placeholder' => '4/5',
    ],
  ]);

  $wp_customize->add_section('nera_woocommerce', [
    'title' => __('WooCommerce', 'nera-competitions'),
    'panel' => 'nera_settings',
    'priority' => 20,
  ]);

  $wp_customize->add_setting('nera_quantity_selector_layout', [
    'default' => 'buttons',
    'sanitize_callback' => 'nera_sanitize_customizer_quantity_layout',
    'transport' => 'refresh',
  ]);

  $wp_customize->add_control('nera_quantity_selector_layout', [
    'label' => __('Quantity Selector Layout', 'nera-competitions'),
    'description' => __(
      'Site-wide default for choosing ticket quantity on the purchase card (auto-assign products only). Manual Browse & Choose products are unaffected. Products can override under Competition Settings. To preview, open a competition product in the customizer preview.',
      'nera-competitions',
    ),
    'section' => 'nera_woocommerce',
    'type' => 'select',
    'choices' => [
      'buttons' => __('Buttons (+ quick add)', 'nera-competitions'),
      'slider' => __('Slider', 'nera-competitions'),
    ],
  ]);

  if (!isset($wp_customize->selective_refresh)) {
    return;
  }

  $wp_customize->selective_refresh->add_partial('nera_shop_listing_grid', [
    'selector' => '#advanced-filter-grid',
    'settings' => [
      'nera_shop_grid_columns',
      'nera_shop_card_layout',
      'nera_shop_card_image_aspect_ratio',
    ],
    'container_inclusive' => true,
    'render_callback' => 'nera_customizer_render_shop_listing_grid',
    'fallback_refresh' => true,
  ]);
}
add_action('customize_register', 'nera_customize_register_settings');

/**
 * Selective-refresh output for #advanced-filter-grid (shop / All Competitions).
 */
function nera_customizer_render_shop_listing_grid(): void
{
  if (!function_exists('nera_advanced_filter_competitions_wp_query_args')) {
    return;
  }

  $url_category_slugs = [];
  if (isset($_GET['product_cat'])) {
    $url_category_slugs = nera_advanced_filter_whitelist_category_slugs(
      wp_unslash($_GET['product_cat']),
    );
  }

  $filter_competitions_args = nera_advanced_filter_competitions_wp_query_args($url_category_slugs, 1);
  $competitions = new WP_Query($filter_competitions_args);
  $lg_class = nera_shop_listing_grid_lg_class();

  echo '<div class="grid grid-cols-1 md:grid-cols-2 ' . esc_attr($lg_class);
  echo ' gap-2.5 sm:gap-4 lg:gap-6 transition-opacity duration-200"';
  echo ' id="advanced-filter-grid"';
  echo ' :class="{ \'opacity-50 pointer-events-none\': gridLoading }"';
  echo ' data-aos="fade-up" data-aos-duration="600" data-aos-delay="150">';

  echo nera_advanced_filter_render_grid_html($competitions);
  wp_reset_postdata();

  echo '</div>';
}

/**
 * Prefer shop archive as customizer preview when no URL is set yet.
 */
function nera_customize_controls_enqueue_shop_preview(): void
{
  if (!function_exists('wc_get_page_id')) {
    return;
  }

  $shop_page_id = (int) wc_get_page_id('shop');
  if ($shop_page_id <= 0) {
    return;
  }

  $shop_url = get_permalink($shop_page_id);
  if (!$shop_url) {
    return;
  }

  wp_add_inline_script(
    'customize-controls',
    '(function(wp){if(!wp||!wp.customize||!wp.customize.previewer){return;}' .
      'wp.customize.bind("ready",function(){' .
      'var url=' .
      wp_json_encode($shop_url) .
      ';' .
      'if(!wp.customize.previewer.previewUrl.get()){wp.customize.previewer.previewUrl.set(url);}' .
      '});})(window.wp);',
  );
}
add_action('customize_controls_enqueue_scripts', 'nera_customize_controls_enqueue_shop_preview');
