<?php
namespace Nera\Components\Credibility;

if (!defined('ABSPATH')) exit;

/**
 * @param array $args
 * @return array{
 *   items: list<array{icon:string, label:string}>, // required, default 5-item trust strip — credibility icons + labels
 * }
 */
function get_data(array $args = []): array
{
    $default_items = [
        ['icon' => 'lock',        'label' => __('Secure Payments',    'nera-competitions')],
        ['icon' => 'verified',    'label' => __('UK Compliant',       'nera-competitions')],
        ['icon' => 'visibility',  'label' => __('Transparent Draws',  'nera-competitions')],
        ['icon' => 'emoji_events','label' => __('Real Winners',        'nera-competitions')],
        ['icon' => 'headset_mic', 'label' => __('Fast Support',       'nera-competitions')],
    ];

    $items_raw = nera_component_field($args, 'items', 'credibility_items', null);

    if (is_array($items_raw) && !empty($items_raw)) {
        $items = [];
        foreach ($items_raw as $item) {
            $items[] = [
                'icon'  => $item['icon']  ?? 'check_circle',
                'label' => $item['label'] ?? '',
            ];
        }
        return ['items' => $items];
    }

    return ['items' => $default_items];
}
