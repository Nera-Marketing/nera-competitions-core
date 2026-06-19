<?php
/**
 * Heading style — runtime wiring (hooks; side-effects on include).
 *
 *  - Injects resolved per-section heading overrides into every section
 *    component's Twig context via the global `nera_component_data` filter.
 *  - Emits the global default font + accent colour as CSS variables in <head>.
 *  - Loads custom Google Font families (global + per-section) for the page.
 *
 * Pure value helpers live in inc/helpers/heading-style.php; global ACF defaults
 * in inc/acf/heading-style/acf-heading-style.php.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
    exit();
}

/**
 * Merge heading style data into a section component's Twig context.
 * Exposes (consumed by section templates, all |raw-safe / pre-sanitised):
 *   - heading_highlight    : trailing accent text (autoescaped in template)
 *   - heading_font_style   : "font-family: 'Sora', sans-serif" or '' (per-section override only)
 *   - heading_accent_style : "color: #84cc16" or "color: var(--heading-accent)"
 *
 * @param array  $data
 * @param string $name Component name.
 * @param array  $args
 * @return array
 */
function nera_inject_heading_style($data, $name, $args)
{
    static $sections = null;
    if ($sections === null) {
        $sections = array_flip(nera_heading_style_sections());
    }
    if (!isset($sections[$name])) {
        return $data;
    }

    $hs = nera_resolve_heading_style(is_array($args) ? $args : []);

    $font_style = $hs['font_family'] !== '' ? 'font-family: ' . $hs['font_family'] : '';

    $accent = $hs['accent_color'] !== '' ? sanitize_hex_color($hs['accent_color']) : '';
    $accent_style = $accent ? 'color: ' . $accent : 'color: var(--heading-accent)';

    $data['heading_highlight']    = $hs['highlight'];
    $data['heading_font_style']   = $font_style;
    $data['heading_accent_style'] = $accent_style;

    return $data;
}
add_filter('nera_component_data', 'nera_inject_heading_style', 10, 3);

/**
 * Print the global heading font + accent colour as CSS variables.
 * Overrides the --font-heading token (so all `font-heading` headings update)
 * and sets --heading-accent (default highlight colour).
 */
function nera_print_heading_style_vars()
{
    if (!function_exists('get_field')) {
        return;
    }

    $font   = (string) (get_field('heading_default_font', 'option') ?: 'poppins');
    $custom = (string) get_field('heading_default_font_custom', 'option');
    $family = nera_heading_font_family($font, $custom);

    $accent = sanitize_hex_color((string) get_field('heading_default_accent_color', 'option'));

    $css = '';
    if ($family !== '') {
        $css .= '--font-heading:' . $family . ';';
    }
    if ($accent) {
        $css .= '--heading-accent:' . $accent . ';';
    }
    if ($css === '') {
        return;
    }

    // $family comes from a controlled map / sanitised custom name; $accent is a validated hex.
    echo '<style id="nera-heading-style-vars">:root{' . $css . '}</style>' . "\n";
}
add_action('wp_head', 'nera_print_heading_style_vars', 20);

/**
 * Enqueue custom Google Font families used by the heading system
 * (global default + any per-section override on the current page).
 * Curated fonts (Poppins, Playfair, Sora, Hanken Grotesk) are loaded in nera_enqueue_styles().
 */
function nera_enqueue_custom_heading_fonts()
{
    if (is_admin() || !function_exists('get_field')) {
        return;
    }

    $families = [];

    // Global default custom font.
    if ((string) get_field('heading_default_font', 'option') === 'custom') {
        $fam = nera_heading_font_google_family('custom', (string) get_field('heading_default_font_custom', 'option'));
        if ($fam !== '') {
            $families[$fam] = true;
        }
    }

    // Per-section custom fonts on the current page.
    if (is_singular()) {
        $rows = get_field('page_components', get_queried_object_id());
        if (is_array($rows)) {
            foreach ($rows as $row) {
                if (($row['heading_font'] ?? '') === 'custom') {
                    $fam = nera_heading_font_google_family('custom', (string) ($row['heading_font_custom'] ?? ''));
                    if ($fam !== '') {
                        $families[$fam] = true;
                    }
                }
            }
        }
    }

    if (empty($families)) {
        return;
    }

    $parts = [];
    foreach (array_keys($families) as $fam) {
        $parts[] = 'family=' . str_replace(' ', '+', $fam);
    }

    $url = 'https://fonts.googleapis.com/css2?' . implode('&', $parts) . '&display=swap';
    wp_enqueue_style('nera-heading-custom-fonts', $url, [], null);
}
add_action('wp_enqueue_scripts', 'nera_enqueue_custom_heading_fonts', 20);
