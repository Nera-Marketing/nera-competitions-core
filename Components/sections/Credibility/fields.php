<?php
namespace Nera\Components\Credibility;

if (!defined('ABSPATH')) exit;

function get_acf_layout(): array {
    return [
        'key'        => 'layout_Credibility',
        'name'       => 'Credibility',
        'label'      => __('Credibility', 'nera-competitions-standard'),
        'display'    => 'block',
        'sub_fields' => [
            [
                'key'          => 'field_pc_credibility_items',
                'label'        => __('Trust Items', 'nera-competitions-standard'),
                'name'         => 'items',
                'type'         => 'repeater',
                'instructions' => __('Add trust/credibility items. Uses Material Symbols icon names (e.g., "lock", "verified"). Leave empty to use defaults.', 'nera-competitions-standard'),
                'layout'       => 'table',
                'button_label' => __('Add Item', 'nera-competitions-standard'),
                'max'          => 6,
                'sub_fields'   => [
                    [
                        'key'           => 'field_pc_credibility_item_icon',
                        'label'         => __('Icon', 'nera-competitions-standard'),
                        'name'          => 'icon',
                        'type'          => 'text',
                        'instructions'  => __('Material Symbol name (e.g., "lock", "verified", "emoji_events")', 'nera-competitions-standard'),
                        'default_value' => 'check_circle',
                    ],
                    [
                        'key'          => 'field_pc_credibility_item_label',
                        'label'        => __('Label', 'nera-competitions-standard'),
                        'name'         => 'label',
                        'type'         => 'text',
                        'instructions' => __('Short trust label (e.g., "Secure Payments")', 'nera-competitions-standard'),
                    ],
                ],
            ],
        ],
        'min' => '',
        'max' => '',
    ];
}
