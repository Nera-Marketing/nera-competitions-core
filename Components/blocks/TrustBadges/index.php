<?php
namespace Nera\Components\TrustBadges;

if (!defined('ABSPATH')) {
    exit;
}

function get_data(array $args = []): array
{
    $default_badges = [
        [
            'icon'  => 'check_circle',
            'label' => __('Guaranteed Draw', 'nera-competitions'),
        ],
        [
            'icon'  => 'lock',
            'label' => __('Secure Payment', 'nera-competitions'),
        ],
    ];

    return [
        'badges' => $args['badges'] ?? $default_badges,
    ];
}
