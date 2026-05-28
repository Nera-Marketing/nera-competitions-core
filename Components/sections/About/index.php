<?php
namespace Nera\Components\About;

if (!defined('ABSPATH')) exit;

/**
 * @param array $args
 * @return array{
 *   badge: string,            // required, default 'Who We Are' — eyebrow badge text
 *   title: string,            // required, default 'Your Trusted Partner…' — section heading
 *   subtitle: string,         // required, default 'Bringing dreams…' — subheading below title
 *   description: string,      // required, default paragraph, wp_kses_post — body copy
 *   features: list<array{icon:string,text:string,delay:string}>, // required, default [] — feature bullet list
 *   show_cta: bool,           // required, default true — whether to render CTA button
 *   cta_text: string,         // required, default 'Learn More About Us' — CTA label
 *   cta_url: string,          // required, default '/about/' — CTA href
 *   image_url: string,        // required, default '' — resolved image src (empty = placeholder shown)
 *   image_alt: string,        // required, default title value — img alt text
 *   bg_class: string,         // required, derived from 'background' ACF field — section background Tailwind class
 *   text_order: string,       // required, derived from 'image_position' — order-1 or order-2
 *   image_order: string,      // required, derived from 'image_position' — order-2 or order-1
 *   orb_top_class: string,    // required, derived from 'image_position' — decorative orb top position classes
 *   orb_bottom_class: string, // required, derived from 'image_position' — decorative orb bottom position classes
 * }
 */
function get_data(array $args = []): array
{
    $image_position = nera_component_field($args, 'image_position', 'about_image_position', 'right');
    $background     = nera_component_field($args, 'background',     'about_background',     'gradient');

    $bg_class = 'bg-surface';
    if ($background === 'gray') {
        $bg_class = 'bg-gray-50';
    } elseif ($background === 'gradient') {
        $bg_class = 'bg-gradient-to-b from-surface via-secondary/30 to-surface';
    }

    $text_order  = $image_position === 'right' ? 'order-1' : 'order-2';
    $image_order = $image_position === 'right' ? 'order-2' : 'order-1';

    $orb_top_class    = $image_position === 'right' ? 'top-1/4 -left-32' : 'top-1/4 -right-32';
    $orb_bottom_class = $image_position === 'right' ? 'right-0' : 'left-0';

    $features_raw        = nera_component_field($args, 'features', 'about_features', []);
    $features_normalised = [];
    if (is_array($features_raw)) {
        foreach ($features_raw as $idx => $feature) {
            if (!is_array($feature)) continue;
            $features_normalised[] = [
                'icon'  => (!empty($feature['icon']) ? $feature['icon'] : 'check_circle'),
                'text'  => $feature['text'] ?? '',
                'delay' => ($idx * 0.1) . 's',
            ];
        }
    }

    $image     = nera_component_field($args, 'image', 'about_image', null);
    $image_url = is_array($image) && isset($image['url']) ? $image['url'] : '';
    $image_alt = is_array($image) && isset($image['alt']) && $image['alt']
        ? $image['alt']
        : (nera_component_field($args, 'title', 'about_title', '') ?: '');

    return [
        'badge'            => nera_component_field($args, 'badge',    'about_badge',    __('Who We Are', 'nera-competitions')),
        'title'            => nera_component_field($args, 'title',    'about_title',    __('Your Trusted Partner in Premium Giveaways', 'nera-competitions')),
        'subtitle'         => nera_component_field($args, 'subtitle', 'about_subtitle', __('Bringing dreams to life, one competition at a time.', 'nera-competitions')),
        'description'      => wp_kses_post(nera_component_field($args, 'description', 'about_description', __("We're passionate about creating life-changing moments through fair, transparent, and exciting prize competitions. With over 150 winners and £2M+ in prizes awarded, we've built a trusted community of dreamers and winners.", 'nera-competitions'))),
        'features'         => $features_normalised,
        'show_cta'         => (bool) nera_component_field($args, 'show_cta',  'about_show_cta',  1),
        'cta_text'         => nera_component_field($args, 'cta_text', 'about_cta_text', __('Learn More About Us', 'nera-competitions')),
        'cta_url'          => nera_component_field($args, 'cta_url',  'about_cta_url',  '/about/'),
        'image_url'        => $image_url,
        'image_alt'        => $image_alt,
        'bg_class'         => $bg_class,
        'text_order'       => $text_order,
        'image_order'      => $image_order,
        'orb_top_class'    => $orb_top_class,
        'orb_bottom_class' => $orb_bottom_class,
    ];
}
