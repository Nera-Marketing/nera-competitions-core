<?php
namespace Nera\Components\PromoBanner;

if (!defined('ABSPATH')) exit;

/**
 * @param array $args
 * @return array{
 *   badge: string,       // required, default 'Stay Connected' — eyebrow badge text
 *   title: string,       // required, default 'Follow us on socials' — banner heading
 *   description: string, // required, default 'Follow us for updates…' — banner body text
 *   bg_image: string,    // required, default Unsplash URL — background image src
 *   links: list<array{   // required, default [{facebook,#},{instagram,#}] — social link items
 *     platform: string,  // e.g. 'facebook'
 *     url: string,       // href
 *     icon: string,      // trusted HTML SVG from nera_get_social_icon()
 *     label: string,     // display label from nera_get_social_label()
 *   }>,
 * }
 */
function get_data(array $args = []): array
{
    $default_links = [
        ['platform' => 'facebook',  'url' => '#'],
        ['platform' => 'instagram', 'url' => '#'],
    ];

    $raw_links = nera_component_field($args, 'social_links', 'promo_social_links', null);
    if (!is_array($raw_links) || empty($raw_links)) {
        $raw_links = $default_links;
    }

    $links = [];
    foreach ($raw_links as $row) {
        if (!is_array($row)) continue;
        $platform = $row['platform'] ?? '';
        if (empty($platform)) continue;
        $links[] = [
            'platform' => $platform,
            'url'      => $row['url'] ?? '#',
            'icon'     => nera_get_social_icon($platform),
            'label'    => nera_get_social_label($platform),
        ];
    }

    return [
        'badge'       => nera_component_field($args, 'badge',       'promo_badge',       __('Stay Connected', 'nera-competitions')),
        'title'       => nera_component_field($args, 'title',       'promo_title',       __('Follow us on socials', 'nera-competitions')),
        'description' => nera_component_field($args, 'description', 'promo_description', __('Follow us for updates, new competitions and giveaways.', 'nera-competitions')),
        'bg_image'    => nera_component_field($args, 'bg_image',    'promo_bg_image',    'https://images.unsplash.com/photo-1603584173870-7f23fdae1b7a?w=1200&h=400&fit=crop'),
        'links'       => $links,
    ];
}
