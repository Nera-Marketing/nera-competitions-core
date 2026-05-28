<?php
namespace Nera\Components\TrustBadges;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param array $args
 * @return array{
 *   badges: list<array{icon: string, label: string}>, // required, default [{icon:'check_circle',label:'Guaranteed Draw'},{icon:'lock',label:'Secure Payment'}] — each icon is a Material Symbols ligature
 * }
 */
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
