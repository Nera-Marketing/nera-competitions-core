<?php
namespace Nera\Components\Stats;

if (!defined('ABSPATH')) exit;

function get_acf_layout(): array {
    return [
        'key'        => 'layout_Stats',
        'name'       => 'Stats',
        'label'      => __('Stats', 'nera-competitions-standard'),
        'display'    => 'block',
        'sub_fields' => [
            [
                'key'           => 'field_pc_stats_winners',
                'label'         => __('Total Winners', 'nera-competitions-standard'),
                'name'          => 'winners',
                'type'          => 'text',
                'default_value' => '150',
            ],
            [
                'key'           => 'field_pc_stats_value',
                'label'         => __('Total Value Given (Millions)', 'nera-competitions-standard'),
                'name'          => 'value',
                'type'          => 'text',
                'instructions'  => __('e.g., 2 for £2M+', 'nera-competitions-standard'),
                'default_value' => '2',
            ],
            [
                'key'           => 'field_pc_stats_secure',
                'label'         => __('Secure Entry %', 'nera-competitions-standard'),
                'name'          => 'secure',
                'type'          => 'text',
                'default_value' => '100',
            ],
            [
                'key'           => 'field_pc_stats_tp_score',
                'label'         => __('Trustpilot Score', 'nera-competitions-standard'),
                'name'          => 'tp_score',
                'type'          => 'text',
                'default_value' => '4.8',
            ],
            [
                'key'           => 'field_pc_stats_tp_reviews',
                'label'         => __('Trustpilot Reviews Count', 'nera-competitions-standard'),
                'name'          => 'tp_reviews',
                'type'          => 'text',
                'default_value' => '1,250',
            ],
        ],
        'min' => '',
        'max' => '',
    ];
}
