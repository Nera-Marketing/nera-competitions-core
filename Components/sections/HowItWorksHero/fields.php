<?php
namespace Nera\Components\HowItWorksHero;

if (!defined('ABSPATH')) exit;

function get_acf_layout(): array
{
    return [
        'key'        => 'layout_HowItWorksHero',
        'name'       => 'HowItWorksHero',
        'label'      => __('How It Works — Hero', 'nera-competitions-standard'),
        'display'    => 'block',
        'sub_fields' => [
            [
                'key'           => 'field_pc_HowItWorksHero_hero_title',
                'label'         => __('Title', 'nera-competitions-standard'),
                'name'          => 'hero_title',
                'type'          => 'text',
                'default_value' => 'How It Works',
            ],
            [
                'key'           => 'field_pc_HowItWorksHero_hero_subtitle',
                'label'         => __('Subtitle', 'nera-competitions-standard'),
                'name'          => 'hero_subtitle',
                'type'          => 'textarea',
                'rows'          => 2,
            ],
            [
                'key'           => 'field_pc_HowItWorksHero_hero_badge',
                'label'         => __('Badge Label', 'nera-competitions-standard'),
                'name'          => 'hero_badge',
                'type'          => 'text',
                'default_value' => 'Simple & Fair',
            ],
            [
                'key'          => 'field_pc_HowItWorksHero_hero_steps',
                'label'        => __('Steps', 'nera-competitions-standard'),
                'name'         => 'hero_steps',
                'type'         => 'repeater',
                'instructions' => __('Override default step content (leave empty to use defaults).', 'nera-competitions-standard'),
                'max'          => 4,
                'layout'       => 'block',
                'button_label' => __('Add Step', 'nera-competitions-standard'),
                'sub_fields'   => [
                    [
                        'key'   => 'field_pc_HowItWorksHero_step_title',
                        'label' => __('Step Title', 'nera-competitions-standard'),
                        'name'  => 'title',
                        'type'  => 'text',
                    ],
                    [
                        'key'   => 'field_pc_HowItWorksHero_step_description',
                        'label' => __('Step Description', 'nera-competitions-standard'),
                        'name'  => 'description',
                        'type'  => 'textarea',
                        'rows'  => 3,
                    ],
                    [
                        'key'           => 'field_pc_HowItWorksHero_step_icon',
                        'label'         => __('Step Icon Image', 'nera-competitions-standard'),
                        'name'          => 'step_icon',
                        'type'          => 'image',
                        'return_format' => 'array',
                        'preview_size'  => 'thumbnail',
                    ],
                ],
            ],
            [
                'key'   => 'field_pc_HowItWorksHero_cta_button_link',
                'label' => __('CTA Button Link', 'nera-competitions-standard'),
                'name'  => 'cta_button_link',
                'type'  => 'link',
            ],
            [
                'key'   => 'field_pc_HowItWorksHero_cta_footer_text',
                'label' => __('CTA Footer Text', 'nera-competitions-standard'),
                'name'  => 'cta_footer_text',
                'type'  => 'text',
            ],
        ],
        'min' => '',
        'max' => '',
    ];
}
