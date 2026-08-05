<?php
/**
 * Nera Dark Yellow — child theme bootstrap.
 *
 * Parent: nera-competitions-standard (Template header in style.css).
 * Branding is token-driven per EXTENDING.md — no template copies required.
 *
 * @package Nera_Dark_Yellow
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
  exit();
}

define('NERA_DY_VERSION', '1.0.0');
define('NERA_DY_DIR', get_stylesheet_directory());
define('NERA_DY_URI', get_stylesheet_directory_uri());

/**
 * Parent Vite CSS handle(s) registered as nera-vite-css-{n}.
 *
 * @return string[]
 */
function nera_dy_parent_vite_style_deps()
{
  $deps = ['nera-style'];

  if (!function_exists('wp_styles')) {
    return $deps;
  }

  $styles = wp_styles();
  if (!$styles || empty($styles->registered)) {
    return $deps;
  }

  foreach (array_keys($styles->registered) as $handle) {
    if (strpos($handle, 'nera-vite-css-') === 0) {
      $deps[] = $handle;
    }
  }

  return $deps;
}

/**
 * Enqueue brand tokens after the parent Vite bundle.
 */
function nera_dy_enqueue_styles()
{
  $deps = nera_dy_parent_vite_style_deps();

  // Prefer Vite build output when present; fall back to committed brand.css.
  $vite_css = NERA_DY_DIR . '/frontend/dist/assets/main.css';
  $brand_css = NERA_DY_DIR . '/assets/css/brand.css';
  $tokens_css = NERA_DY_DIR . '/assets/css/child-tokens.css';

  $manifest = NERA_DY_DIR . '/frontend/dist/.vite/manifest.json';
  $brand_url = NERA_DY_URI . '/assets/css/brand.css';
  $brand_ver = file_exists($brand_css) ? filemtime($brand_css) : NERA_DY_VERSION;

  if (file_exists($manifest)) {
    $json = json_decode((string) file_get_contents($manifest), true);
    if (is_array($json)) {
      $entry = $json['src/main.js'] ?? $json['frontend/src/main.js'] ?? null;
      if (is_array($entry) && !empty($entry['css'][0])) {
        $rel = ltrim((string) $entry['css'][0], '/');
        $built = NERA_DY_DIR . '/frontend/dist/' . $rel;
        if (file_exists($built)) {
          $brand_url = NERA_DY_URI . '/frontend/dist/' . $rel;
          $brand_ver = filemtime($built);
        }
      }
    }
  } elseif (file_exists($vite_css)) {
    $brand_url = NERA_DY_URI . '/frontend/dist/assets/main.css';
    $brand_ver = filemtime($vite_css);
  }

  wp_enqueue_style('nera-dy-brand', $brand_url, $deps, $brand_ver);

  if (file_exists($tokens_css)) {
    wp_enqueue_style(
      'nera-dy-tokens',
      NERA_DY_URI . '/assets/css/child-tokens.css',
      ['nera-dy-brand'],
      filemtime($tokens_css),
    );
  }
}
add_action('wp_enqueue_scripts', 'nera_dy_enqueue_styles', 100);

/**
 * Category accents tuned for dark + yellow surfaces.
 *
 * @param array<string, string> $colors
 * @return array<string, string>
 */
function nera_dy_category_colors($colors)
{
  return array_merge(
    is_array($colors) ? $colors : [],
    [
      'cars' => '#60A5FA',
      'cash' => '#34D399',
      'luxury' => '#F5C518',
      'electronics' => '#FBBF24',
      'travel' => '#F472B6',
      'tech' => '#22D3EE',
      'gadgets' => '#FB923C',
      'watches' => '#A78BFA',
      'lifestyle' => '#2DD4BF',
    ],
  );
}
add_filter('nera_advanced_filter_category_colors', 'nera_dy_category_colors');
add_filter('nera_competition_card_category_colors', 'nera_dy_category_colors');

/**
 * Fallback card accent when no category match.
 *
 * @return string
 */
function nera_dy_fallback_accent()
{
  return '#F5C518';
}
add_filter('nera_competition_card_fallback_accent', 'nera_dy_fallback_accent');
