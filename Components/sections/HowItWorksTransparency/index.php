<?php
namespace Nera\Components\HowItWorksTransparency;

if (!defined('ABSPATH')) exit;

/**
 * @param array $args
 * @return array{
 *   title: string,         // required, default 'Transparency & Fairness' — esc_html section heading
 *   subtitle: string,      // required, default 'We pride ourselves…' — esc_html section subheading
 *   features: list<array{  // required, default 3-item list — trust feature cards
 *     icon: string,        // esc_html Material Symbol name
 *     title: string,       // esc_html card heading
 *     description: string, // esc_html card body text
 *   }>,
 * }
 */
function get_data(array $args = []): array
{
    $title    = nera_component_field($args, 'title', 'hiw_transparency_title',
        __('Transparency & Fairness', 'nera-competitions'));
    $subtitle = nera_component_field($args, 'subtitle', 'hiw_transparency_subtitle',
        __('We pride ourselves on being a registered UK business that operates with full integrity and a passion for giving back.', 'nera-competitions'));

    $default_features = [
        [
            'icon'        => 'verified_user',
            'title'       => __('Fully Insured', 'nera-competitions'),
            'description' => __('We are a legally registered UK business, fully insured and compliant with all regulations.', 'nera-competitions'),
        ],
        [
            'icon'        => 'diversity_3',
            'title'       => __('Community Focused', 'nera-competitions'),
            'description' => __('Our mission is to support our community and provide life-changing opportunities for everyone.', 'nera-competitions'),
        ],
        [
            'icon'        => 'shield_with_heart',
            'title'       => __('Secure & Safe', 'nera-competitions'),
            'description' => __('We use industry-standard security protocols to ensure your data and entries are always protected.', 'nera-competitions'),
        ],
    ];

    $acf_features = nera_component_field($args, 'features', 'hiw_transparency_features', null);
    $features = [];
    if (!empty($acf_features) && is_array($acf_features)) {
        foreach ($acf_features as $i => $feature) {
            $default = isset($default_features[$i]) ? $default_features[$i] : $default_features[0];
            $features[] = [
                'icon'        => esc_html(!empty($feature['icon'])        ? $feature['icon']        : $default['icon']),
                'title'       => esc_html(!empty($feature['title'])       ? $feature['title']       : $default['title']),
                'description' => esc_html(!empty($feature['description']) ? $feature['description'] : $default['description']),
            ];
        }
    } else {
        foreach ($default_features as $feature) {
            $features[] = [
                'icon'        => esc_html($feature['icon']),
                'title'       => esc_html($feature['title']),
                'description' => esc_html($feature['description']),
            ];
        }
    }

    return [
        'title'    => esc_html($title),
        'subtitle' => esc_html($subtitle),
        'features' => $features,
    ];
}
