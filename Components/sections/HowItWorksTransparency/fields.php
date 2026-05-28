<?php
namespace Nera\Components\HowItWorksTransparency;

if (!defined('ABSPATH')) exit;

function get_acf_layout(): array
{
    return [
        'key'        => 'layout_HowItWorksTransparency',
        'name'       => 'HowItWorksTransparency',
        'label'      => __('How It Works — Transparency', 'nera-competitions-standard'),
        'display'    => 'block',
        'sub_fields' => [
            [
                'key'           => 'field_pc_HowItWorksTransparency_title',
                'label'         => __('Section Title', 'nera-competitions-standard'),
                'name'          => 'title',
                'type'          => 'text',
                'default_value' => 'Transparency & Fairness',
            ],
            [
                'key'   => 'field_pc_HowItWorksTransparency_subtitle',
                'label' => __('Subtitle', 'nera-competitions-standard'),
                'name'  => 'subtitle',
                'type'  => 'textarea',
                'rows'  => 2,
            ],
            [
                'key'          => 'field_pc_HowItWorksTransparency_features',
                'label'        => __('Features', 'nera-competitions-standard'),
                'name'         => 'features',
                'type'         => 'repeater',
                'instructions' => __('Override default transparency features (leave empty to use defaults).', 'nera-competitions-standard'),
                'max'          => 6,
                'layout'       => 'table',
                'button_label' => __('Add Feature', 'nera-competitions-standard'),
                'sub_fields'   => [
                    [
                        'key'           => 'field_pc_HowItWorksTransparency_feature_icon',
                        'label'         => __('Icon', 'nera-competitions-standard'),
                        'name'          => 'icon',
                        'type'          => 'text',
                        'default_value' => 'verified_user',
                        'instructions'  => __('Material Symbols icon name (e.g. "verified_user", "diversity_3", "shield_with_heart").', 'nera-competitions-standard'),
                    ],
                    [
                        'key'   => 'field_pc_HowItWorksTransparency_feature_title',
                        'label' => __('Title', 'nera-competitions-standard'),
                        'name'  => 'title',
                        'type'  => 'text',
                    ],
                    [
                        'key'   => 'field_pc_HowItWorksTransparency_feature_description',
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
