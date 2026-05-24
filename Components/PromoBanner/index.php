<?php
namespace Nera\Components\PromoBanner;

if (!defined('ABSPATH')) exit;

function get_data(array $args = []): array
{
    $default_links = [
        ['platform' => 'facebook',  'url' => '#'],
        ['platform' => 'instagram', 'url' => '#'],
    ];

    $raw_links = get_field('promo_social_links') ?: $default_links;
    $links = [];
    foreach ($raw_links as $row) {
        $platform = $row['platform'] ?? '';
        if (empty($platform)) continue;
        $links[] = [
            'platform' => $platform,
            'url'      => $row['url'] ?? '#',
            'icon'     => nera_get_social_icon($platform),  // raw SVG
            'label'    => nera_get_social_label($platform),
        ];
    }

    return [
        'badge'       => get_field('promo_badge')       ?: __('Stay Connected', 'nera-competitions'),
        'title'       => get_field('promo_title')       ?: __('Follow us on socials', 'nera-competitions'),
        'description' => get_field('promo_description') ?: __('Follow us for updates, new competitions and giveaways.', 'nera-competitions'),
        'bg_image'    => get_field('promo_bg_image')    ?: 'https://images.unsplash.com/photo-1603584173870-7f23fdae1b7a?w=1200&h=400&fit=crop',
        'links'       => $links,
    ];
}
