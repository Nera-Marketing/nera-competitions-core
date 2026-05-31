<?php
/**
 * Site-wide content container max-width (Customizer → CSS variable).
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

/** Minimum custom container width (px). */
const NERA_SITE_CONTAINER_MIN_PX = 960;

/** Maximum custom container width (px). */
const NERA_SITE_CONTAINER_MAX_PX = 1920;

/**
 * Allowed preset keys for nera_site_container_preset.
 *
 * @return string[]
 */
function nera_site_container_preset_keys(): array
{
  return ['1280', '1400', '1536', 'custom'];
}

/**
 * @return string Preset theme_mod (default 1280).
 */
function nera_get_site_container_preset(): string
{
  $preset = get_theme_mod('nera_site_container_preset', '1280');

  if (!is_string($preset) || !in_array($preset, nera_site_container_preset_keys(), true)) {
    return '1280';
  }

  return $preset;
}

/**
 * Custom width in px when preset is "custom"; clamped to allowed range.
 *
 * @return int
 */
function nera_get_site_container_custom_px(): int
{
  $px = (int) get_theme_mod('nera_site_container_custom_px', 1280);

  return max(NERA_SITE_CONTAINER_MIN_PX, min(NERA_SITE_CONTAINER_MAX_PX, $px));
}

/**
 * CSS length for --nera-site-container-max (no user-controlled raw strings).
 */
function nera_get_site_container_max_css(): string
{
  $preset = nera_get_site_container_preset();

  $value = match ($preset) {
    '1400' => '1400px',
    '1536' => '96rem',
    'custom' => nera_get_site_container_custom_px() . 'px',
    default => '80rem',
  };

  /**
   * Filter the site container max-width CSS value.
   *
   * @param string $value CSS length (e.g. 80rem, 1400px).
   */
  return (string) apply_filters('nera_site_container_max_css', $value);
}

/**
 * @param mixed $value Raw control value.
 */
function nera_sanitize_site_container_preset($value): string
{
  if (!is_string($value)) {
    return '1280';
  }

  return in_array($value, nera_site_container_preset_keys(), true) ? $value : '1280';
}

/**
 * @param mixed $value Raw control value.
 */
function nera_sanitize_site_container_custom_px($value): int
{
  $px = (int) $value;

  return max(NERA_SITE_CONTAINER_MIN_PX, min(NERA_SITE_CONTAINER_MAX_PX, $px));
}

/**
 * Output :root { --nera-site-container-max } after main Vite CSS.
 */
function nera_print_site_container_root_css(): void
{
  $max_css = nera_get_site_container_max_css();
  echo '<style id="nera-container-vars">:root{--nera-site-container-max:' .
    esc_attr($max_css) .
    ';}</style>';
}

/**
 * Output :root { --nera-site-container-max } after main Vite CSS (production).
 */
function nera_enqueue_site_container_css(): void
{
  if (nera_is_vite_dev_server_running()) {
    return;
  }

  $deps = ['nera-style'];
  $css_files = nera_get_vite_css_files('src/main.js');
  if (!empty($css_files)) {
    $deps[] = 'nera-vite-css-0';
  }

  wp_register_style('nera-container-vars', false, $deps, NERA_VERSION);
  wp_enqueue_style('nera-container-vars');

  $max_css = nera_get_site_container_max_css();
  $inline = sprintf(':root{--nera-site-container-max:%s;}', esc_attr($max_css));
  wp_add_inline_style('nera-container-vars', $inline);
}
add_action('wp_enqueue_scripts', 'nera_enqueue_site_container_css', 20);

/**
 * Vite dev: main CSS loads via JS — inject container variable in head.
 */
function nera_enqueue_site_container_css_dev(): void
{
  if (!nera_is_vite_dev_server_running()) {
    return;
  }

  add_action('wp_head', 'nera_print_site_container_root_css', 5);
}
add_action('wp_enqueue_scripts', 'nera_enqueue_site_container_css_dev', 20);
