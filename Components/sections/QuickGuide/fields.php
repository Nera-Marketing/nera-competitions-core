<?php
namespace Nera\Components\QuickGuide;

if (!defined('ABSPATH')) exit;

function get_acf_layout(): array {
    return [
        'key'        => 'layout_QuickGuide',
        'name'       => 'QuickGuide',
        'label'      => __('Quick Guide', 'nera-competitions-standard'),
        'display'    => 'block',
        'sub_fields' => [
            [
                'key'           => 'field_pc_guide_title',
                'label'         => __('Section Title', 'nera-competitions-standard'),
                'name'          => 'title',
                'type'          => 'text',
                'default_value' => 'How to Play',
            ],
            [
                'key'           => 'field_pc_guide_subtitle',
                'label'         => __('Section Subtitle', 'nera-competitions-standard'),
                'name'          => 'subtitle',
                'type'          => 'text',
                'default_value' => 'Win your dream prizes in just three simple steps',
            ],
            [
                'key'          => 'field_pc_guide_steps',
                'label'        => __('Steps', 'nera-competitions-standard'),
                'name'         => 'steps',
                'type'         => 'repeater',
                'layout'       => 'block',
                'max'          => 3,
                'button_label' => __('Add Step', 'nera-competitions-standard'),
                'sub_fields'   => [
                    [
                        'key'           => 'field_pc_guide_step_number',
                        'label'         => __('Number', 'nera-competitions-standard'),
                        'name'          => 'number',
                        'type'          => 'text',
                        'default_value' => '01',
                    ],
                    [
                        'key'          => 'field_pc_guide_step_icon',
                        'label'        => __('SVG Icon Code', 'nera-competitions-standard'),
                        'name'         => 'icon',
                        'type'         => 'textarea',
                        'rows'         => 3,
                        'instructions' => __('Paste SVG code here.', 'nera-competitions-standard'),
                    ],
                    [
                        'key'   => 'field_pc_guide_step_title',
                        'label' => __('Title', 'nera-competitions-standard'),
                        'name'  => 'title',
                        'type'  => 'text',
                    ],
                    [
                        'key'   => 'field_pc_guide_step_description',
                        'label' => __('Description', 'nera-competitions-standard'),
                        'name'  => 'description',
                        'type'  => 'textarea',
                        'rows'  => 2,
                    ],
                ],
            ],
        ],
        'min' => '',
        'max' => '',
    ];
}
