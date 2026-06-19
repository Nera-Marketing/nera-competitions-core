<?php
/**
 * Heading style helpers — pure utilities (no hooks, no side-effects on include).
 *
 * Powers the editor-driven two-tone section headings:
 *   - curated heading font list (+ custom Google Font),
 *   - per-section ACF override field definitions,
 *   - resolution of per-section overrides into render-ready values.
 *
 * Global defaults live on the "Heading Style" options page
 * (inc/acf/heading-style/acf-heading-style.php) and are emitted as CSS vars by
 * inc/heading-style.php. This file only deals with values, never WP state changes.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
    exit();
}

/**
 * Curated heading font choices for the ACF select fields.
 *
 * @param bool $include_inherit Prepend the per-section "Inherit (global)" option.
 * @return array<string,string> slug => label
 */
function nera_heading_font_choices(bool $include_inherit = false): array
{
    $choices = [
        'poppins'  => 'Poppins',
        'sora'     => 'Sora',
        'hanken'   => 'Hanken Grotesk',
        'playfair' => 'Playfair Display',
        'custom'   => 'Custom (Google Font)',
    ];

    if ($include_inherit) {
        return ['inherit' => __('Inherit (global default)', 'nera-competitions')] + $choices;
    }

    return $choices;
}

/**
 * Map a font slug to a CSS font-family stack.
 *
 * @param string $slug   One of the keys from nera_heading_font_choices().
 * @param string $custom Raw custom value, e.g. "Bricolage Grotesque:wght@700;800" or "Bricolage Grotesque".
 * @return string CSS font-family value, e.g. "'Sora', sans-serif". Empty string for unknown/inherit.
 */
function nera_heading_font_family(string $slug, string $custom = ''): string
{
    switch ($slug) {
        case 'poppins':
            return "'Poppins', sans-serif";
        case 'sora':
            return "'Sora', sans-serif";
        case 'hanken':
            return "'Hanken Grotesk', sans-serif";
        case 'playfair':
            return "'Playfair Display', serif";
        case 'custom':
            $name = nera_heading_custom_font_name($custom);
            return $name !== '' ? "'{$name}', sans-serif" : '';
        default:
            return '';
    }
}

/**
 * Extract the bare family name from a custom Google Font value.
 * Accepts "Family Name", "Family Name:wght@400;700", etc.
 *
 * @param string $custom
 * @return string Trimmed family name (no weight spec). Empty if blank.
 */
function nera_heading_custom_font_name(string $custom): string
{
    $custom = trim($custom);
    if ($custom === '') {
        return '';
    }
    // Drop any ":wght@..." / ":ital,wght@..." suffix.
    $name = explode(':', $custom, 2)[0];
    return trim($name);
}

/**
 * Build the Google Fonts `family=` query segment for a font slug.
 * Returns '' for fonts already enqueued elsewhere (poppins, playfair) and for inherit/unknown.
 *
 * @param string $slug
 * @param string $custom
 * @return string e.g. "Sora:wght@400;600;700;800" (un-encoded) or '' when nothing to load.
 */
function nera_heading_font_google_family(string $slug, string $custom = ''): string
{
    switch ($slug) {
        case 'sora':
            return 'Sora:wght@400;600;700;800';
        case 'hanken':
            return 'Hanken Grotesk:wght@400;600;700;800';
        case 'custom':
            $custom = trim($custom);
            if ($custom === '') {
                return '';
            }
            // If no weight spec given, request a sensible bold range.
            return strpos($custom, ':') !== false
                ? $custom
                : $custom . ':wght@400;600;700;800';
        // poppins + playfair are already loaded by nera_enqueue_styles().
        default:
            return '';
    }
}

/**
 * ACF sub-field definitions for per-section heading overrides.
 * Appended to each section component's Flexible Content layout `sub_fields`.
 *
 * Keys are prefixed with the component slug so they stay globally unique
 * (ACF requires unique field keys), matching the repo's field_pc_<Name>_<field> convention.
 *
 * @param string $slug Component slug (PascalCase component name, e.g. 'HowItWorksDraw').
 * @return array<int,array<string,mixed>>
 */
function nera_heading_style_fields(string $slug): array
{
    $p = "field_pc_{$slug}_";

    return [
        [
            'key'          => $p . 'heading_highlight',
            'label'        => __('Heading Highlight', 'nera-competitions'),
            'name'         => 'heading_highlight',
            'type'         => 'text',
            'instructions' => __('Optional trailing words shown in the accent colour after the title (e.g. title "Everything up" + highlight "for grabs.").', 'nera-competitions'),
        ],
        [
            'key'           => $p . 'heading_font',
            'label'         => __('Heading Font', 'nera-competitions'),
            'name'          => 'heading_font',
            'type'          => 'select',
            'choices'       => nera_heading_font_choices(true),
            'default_value' => 'inherit',
            'allow_null'    => 0,
            'ui'            => 0,
            'instructions'  => __('Override the global heading font for this section only.', 'nera-competitions'),
        ],
        [
            'key'               => $p . 'heading_font_custom',
            'label'             => __('Custom Google Font', 'nera-competitions'),
            'name'              => 'heading_font_custom',
            'type'              => 'text',
            'instructions'      => __('Google Font family, e.g. "Bricolage Grotesque" or "Bricolage Grotesque:wght@700;800".', 'nera-competitions'),
            'conditional_logic' => [
                [
                    ['field' => $p . 'heading_font', 'operator' => '==', 'value' => 'custom'],
                ],
            ],
        ],
        [
            'key'          => $p . 'heading_accent_color',
            'label'        => __('Heading Accent Colour', 'nera-competitions'),
            'name'         => 'heading_accent_color',
            'type'         => 'color_picker',
            'instructions' => __('Override the global accent colour for the highlight in this section. Leave empty to inherit.', 'nera-competitions'),
        ],
    ];
}

/**
 * Insert the per-section heading-style fields immediately after the section's
 * primary Title/Heading sub-field, so "Heading Highlight" sits next to the title
 * it accents. Falls back to appending if no title/heading field is found.
 *
 * @param array  $sub_fields Existing top-level sub-fields of the layout.
 * @param string $slug       Component slug (for unique field keys).
 * @return array
 */
function nera_with_heading_fields(array $sub_fields, string $slug): array
{
    $heading_fields = nera_heading_style_fields($slug);

    $insert_at = null;
    foreach ($sub_fields as $i => $f) {
        $name = is_array($f) && isset($f['name']) ? (string) $f['name'] : '';
        if ($name === 'title' || $name === 'heading' || preg_match('/_(title|heading)$/', $name)) {
            $insert_at = $i + 1;
            break;
        }
    }

    if ($insert_at === null) {
        return array_merge($sub_fields, $heading_fields); // fallback: append
    }

    array_splice($sub_fields, $insert_at, 0, $heading_fields);
    return $sub_fields;
}

/**
 * Resolve a section's per-instance heading overrides from its ACF flexible-content row.
 * Returns override-only values; empty strings mean "inherit the global default"
 * (the global font is applied via --font-heading and the accent via var(--heading-accent)).
 *
 * @param array $args Component args (expects 'acf_row').
 * @return array{highlight:string,accent_color:string,font_family:string,font_custom:string,font_slug:string}
 */
function nera_resolve_heading_style(array $args): array
{
    $row = isset($args['acf_row']) && is_array($args['acf_row']) ? $args['acf_row'] : [];

    $highlight = isset($row['heading_highlight']) ? (string) $row['heading_highlight'] : '';

    $font_slug   = isset($row['heading_font']) ? (string) $row['heading_font'] : 'inherit';
    $font_custom = isset($row['heading_font_custom']) ? (string) $row['heading_font_custom'] : '';

    $font_family = '';
    if ($font_slug !== '' && $font_slug !== 'inherit') {
        $font_family = nera_heading_font_family($font_slug, $font_custom);
    }

    $accent = isset($row['heading_accent_color']) ? trim((string) $row['heading_accent_color']) : '';

    return [
        'highlight'    => $highlight,
        'accent_color' => $accent,
        'font_family'  => $font_family,
        'font_custom'  => $font_custom,
        'font_slug'    => $font_slug,
    ];
}

/**
 * Section component names that receive the heading treatment.
 * Stats + Credibility have no heading and are intentionally excluded.
 *
 * @return string[]
 */
function nera_heading_style_sections(): array
{
    return [
        'HowItWorksHero',
        'HowItWorksDraw',
        'HowItWorksPostal',
        'HowItWorksTransparency',
        'HomepageHero',
        'About',
        'Faq',
        'FeaturedCompetitions',
        'Testimonials',
        'QuickGuide',
        'PromoBanner',
        'CategoriesCompetitions',
        'Contact',
        'AboutUsPage',
    ];
}
