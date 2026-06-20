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
 * Curated heading-highlight font-weight choices for the ACF select fields.
 * Limited to weights loaded for every curated font (see nera_enqueue_styles()):
 * Poppins/Playfair/Sora/Hanken all ship 400;600;700;800, so any choice renders.
 *
 * @param bool $include_inherit Prepend the per-section "Inherit (global)" option.
 * @return array<string,string> weight => label
 */
function nera_heading_font_weight_choices(bool $include_inherit = false): array
{
    $choices = [
        '400'    => 'Regular (400)',
        '600'    => 'Semibold (600)',
        '700'    => 'Bold (700)',
        '800'    => 'Extrabold (800)',
        'custom' => 'Custom (number)',
    ];

    if ($include_inherit) {
        return ['inherit' => __('Inherit (global default)', 'nera-competitions')] + $choices;
    }

    return $choices;
}

/**
 * Resolve a heading-highlight font-weight slug (+ custom value) to a valid int.
 * Returns 0 for inherit / empty / out-of-range so callers can fall back.
 *
 * @param string $slug   '400'..'800', 'custom', 'inherit', or ''.
 * @param string $custom Raw numeric value used when $slug === 'custom'.
 * @return int 1–1000, or 0 when nothing valid to apply.
 */
function nera_heading_font_weight(string $slug, string $custom = ''): int
{
    if ($slug === 'custom') {
        $w = (int) $custom;
    } elseif ($slug !== '' && $slug !== 'inherit') {
        $w = (int) $slug;
    } else {
        return 0; // inherit / empty
    }

    return ($w >= 1 && $w <= 1000) ? $w : 0; // clamp to CSS font-weight range
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
            'label'         => __('Highlight Font', 'nera-competitions'),
            'name'          => 'heading_font',
            'type'          => 'select',
            'choices'       => nera_heading_font_choices(true),
            'default_value' => 'inherit',
            'allow_null'    => 0,
            'ui'            => 0,
            'instructions'  => __('Font for this section\'s heading highlight. Overrides the global highlight font.', 'nera-competitions'),
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
            'key'           => $p . 'heading_font_weight',
            'label'         => __('Highlight Font Weight', 'nera-competitions'),
            'name'          => 'heading_font_weight',
            'type'          => 'select',
            'choices'       => nera_heading_font_weight_choices(true),
            'default_value' => 'inherit',
            'allow_null'    => 0,
            'ui'            => 0,
            'instructions'  => __('Font weight for this section\'s heading highlight. Overrides the global highlight weight.', 'nera-competitions'),
        ],
        [
            'key'               => $p . 'heading_font_weight_custom',
            'label'             => __('Custom Font Weight', 'nera-competitions'),
            'name'              => 'heading_font_weight_custom',
            'type'              => 'number',
            'instructions'      => __('Numeric font weight (1–1000), e.g. 350, 500, 900.', 'nera-competitions'),
            'min'               => 1,
            'max'               => 1000,
            'conditional_logic' => [
                [
                    ['field' => $p . 'heading_font_weight', 'operator' => '==', 'value' => 'custom'],
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
 * Insert the per-section heading-style fields into the layout, split across two
 * ACF tabs:
 *   - "Content": the content-type heading field ("Heading Highlight") is placed
 *     immediately after the section's primary Title/Heading sub-field, so it sits
 *     next to the title it accents.
 *   - "Styles": the visual override fields (highlight font, font weight, accent
 *     colour, and their conditional custom inputs) are appended under a dedicated
 *     tab so they don't clutter the content fields.
 *
 * Tabs are presentational only — field names are unchanged, so saved data and
 * front-end rendering are unaffected. A "Content" tab is prepended only when the
 * layout doesn't already start with its own tab (e.g. the Contact component keeps
 * its existing Hero/Contact Info/Form tab bar and just gains a "Styles" tab).
 *
 * @param array  $sub_fields Existing top-level sub-fields of the layout.
 * @param string $slug       Component slug (for unique field keys).
 * @return array
 */
function nera_with_heading_fields(array $sub_fields, string $slug): array
{
    $p = "field_pc_{$slug}_";

    // Partition the heading fields: styling/UI overrides go under "Styles",
    // everything else (the "Heading Highlight" text) stays inline as content.
    $style_names = [
        'heading_font',
        'heading_font_custom',
        'heading_font_weight',
        'heading_font_weight_custom',
        'heading_accent_color',
    ];
    $content_heading = [];
    $style_heading   = [];
    foreach (nera_heading_style_fields($slug) as $f) {
        $name = is_array($f) && isset($f['name']) ? (string) $f['name'] : '';
        if (in_array($name, $style_names, true)) {
            $style_heading[] = $f;
        } else {
            $content_heading[] = $f;
        }
    }

    // Insert the content-group heading field(s) after the Title/Heading sub-field.
    $insert_at = null;
    foreach ($sub_fields as $i => $f) {
        $name = is_array($f) && isset($f['name']) ? (string) $f['name'] : '';
        if ($name === 'title' || $name === 'heading' || preg_match('/_(title|heading)$/', $name)) {
            $insert_at = $i + 1;
            break;
        }
    }
    if ($insert_at === null) {
        $sub_fields = array_merge($sub_fields, $content_heading); // fallback: append
    } else {
        array_splice($sub_fields, $insert_at, 0, $content_heading);
    }

    // Wrap the content fields in a "Content" tab — but only if the layout isn't
    // already tabbed, so a component defining its own tabs keeps its tab bar.
    $first = $sub_fields[0] ?? null;
    $already_tabbed = is_array($first) && isset($first['type']) && $first['type'] === 'tab';
    if (!$already_tabbed) {
        array_unshift($sub_fields, [
            'key'       => $p . 'tab_content',
            'label'     => __('Content', 'nera-competitions'),
            'name'      => 'tab_content',
            'type'      => 'tab',
            'placement' => 'top',
        ]);
    }

    // Append the "Styles" tab followed by the styling override fields.
    $sub_fields[] = [
        'key'       => $p . 'tab_styles',
        'label'     => __('Styles', 'nera-competitions'),
        'name'      => 'tab_styles',
        'type'      => 'tab',
        'placement' => 'top',
    ];
    foreach ($style_heading as $f) {
        $sub_fields[] = $f;
    }

    return $sub_fields;
}

/**
 * Resolve a section's per-instance heading overrides from its ACF flexible-content row.
 * Returns override-only values; empty strings mean "inherit the global default"
 * (the global highlight font is applied via --heading-highlight-font and the accent via var(--heading-accent)).
 *
 * @param array $args Component args (expects 'acf_row').
 * @return array{highlight:string,accent_color:string,font_family:string,font_custom:string,font_slug:string,font_weight:string}
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

    $weight_slug   = isset($row['heading_font_weight']) ? (string) $row['heading_font_weight'] : 'inherit';
    $weight_custom = isset($row['heading_font_weight_custom']) ? (string) $row['heading_font_weight_custom'] : '';
    $weight        = nera_heading_font_weight($weight_slug, $weight_custom);
    $font_weight   = $weight ? (string) $weight : '';

    return [
        'highlight'    => $highlight,
        'accent_color' => $accent,
        'font_family'  => $font_family,
        'font_custom'  => $font_custom,
        'font_slug'    => $font_slug,
        'font_weight'  => $font_weight,
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
