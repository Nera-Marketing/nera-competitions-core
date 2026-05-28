<?php
namespace Nera\Components\HowItWorksHero;

if (!defined('ABSPATH')) exit;

/**
 * @param array $args
 * @return array{
 *   title: string,           // required, default get_the_title() — esc_html page/section heading
 *   subtitle: string,        // required, default 'Win your dream prizes in just 4 simple steps' — esc_html subheading
 *   badge: string,           // required, default 'Simple & Fair' — esc_html badge text
 *   steps: list<array{       // required, default 4-item list from nera_get_hiw_merged_hero_steps()
 *     icon: string,          // trusted HTML — inline SVG or wp_get_attachment_image output
 *     color: string,         // Tailwind gradient classes e.g. 'from-accent to-accent'
 *     bg_color: string,      // Tailwind bg class e.g. 'bg-accent/10'
 *     title: string,
 *     description: string,
 *     delay: int,            // animation delay in ms (index × 150)
 *   }>,
 *   cta_url: string,         // required, default WC shop permalink — esc_url CTA href
 *   cta_button_text: string, // required, default 'Start Winning Today' — esc_html CTA label
 *   cta_target: string,      // required, default '' — '_blank' or '' for rel/target attribute
 *   cta_footer_text: string, // required, default 'Join thousands of winners…' — esc_html note below CTA
 * }
 */
function get_data(array $args = []): array
{
    $title    = nera_component_field($args, 'hero_title',    'hiw_hero_title',    get_the_title());
    $subtitle = nera_component_field($args, 'hero_subtitle', 'hiw_hero_subtitle', __('Win your dream prizes in just 4 simple steps', 'nera-competitions'));
    $badge    = nera_component_field($args, 'hero_badge',    'hiw_hero_badge',    __('Simple & Fair', 'nera-competitions'));

    $acf_hero_steps = nera_component_field($args, 'hero_steps', 'hiw_hero_steps', null);
    $steps_raw      = nera_get_hiw_merged_hero_steps($acf_hero_steps);

    // Attach staggered animation delay (ms) to each step
    $steps = [];
    foreach ($steps_raw as $i => $step) {
        $step['delay'] = $i * 150;
        $steps[]       = $step;
    }

    // CTA defaults
    $cta_button_text = __('Start Winning Today', 'nera-competitions');
    $cta_url         = '';
    $cta_target      = '';

    if (function_exists('wc_get_page_id')) {
        $cta_url = (string) get_permalink(wc_get_page_id('shop'));
    }
    if ($cta_url === '') {
        $cta_url = home_url('/');
    }

    // Per-instance ACF link field overrides
    $cta_button_link = nera_component_field($args, 'cta_button_link', 'hiw_cta_button_link', null);
    if (is_array($cta_button_link) && !empty($cta_button_link['url'])) {
        $parsed = esc_url_raw($cta_button_link['url']);
        if ($parsed !== '') $cta_url = $parsed;
        if (!empty($cta_button_link['title']))  $cta_button_text = $cta_button_link['title'];
        if (!empty($cta_button_link['target']) && $cta_button_link['target'] === '_blank') $cta_target = '_blank';
    }

    $cta_footer_text = nera_component_field(
        $args,
        'cta_footer_text',
        'hiw_cta_footer_text',
        __('Join thousands of winners • New competitions added daily', 'nera-competitions')
    );

    return [
        'title'           => esc_html($title),
        'subtitle'        => esc_html($subtitle),
        'badge'           => esc_html($badge),
        'steps'           => $steps,       // icon is trusted HTML (SVG or wp_get_attachment_image)
        'cta_url'         => esc_url($cta_url),
        'cta_button_text' => esc_html($cta_button_text),
        'cta_target'      => $cta_target === '_blank' ? '_blank' : '',
        'cta_footer_text' => esc_html($cta_footer_text),
    ];
}
