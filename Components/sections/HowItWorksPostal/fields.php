<?php
namespace Nera\Components\HowItWorksPostal;

if (!defined('ABSPATH')) exit;

function get_acf_layout(): array
{
    return [
        'key'        => 'layout_HowItWorksPostal',
        'name'       => 'HowItWorksPostal',
        'label'      => __('How It Works — Postal Entry', 'nera-competitions-standard'),
        'display'    => 'block',
        'sub_fields' => [
            [
                'key'           => 'field_pc_HowItWorksPostal_title',
                'label'         => __('Section Title', 'nera-competitions-standard'),
                'name'          => 'title',
                'type'          => 'text',
                'default_value' => 'Free Postal Entry Route',
            ],
            [
                'key'   => 'field_pc_HowItWorksPostal_intro',
                'label' => __('Intro Text', 'nera-competitions-standard'),
                'name'  => 'intro',
                'type'  => 'textarea',
                'rows'  => 2,
            ],
            [
                'key'          => 'field_pc_HowItWorksPostal_steps',
                'label'        => __('Steps', 'nera-competitions-standard'),
                'name'         => 'steps',
                'type'         => 'repeater',
                'instructions' => __('Override default postal steps (leave empty to use defaults).', 'nera-competitions-standard'),
                'layout'       => 'block',
                'button_label' => __('Add Step', 'nera-competitions-standard'),
                'sub_fields'   => [
                    [
                        'key'   => 'field_pc_HowItWorksPostal_step_number',
                        'label' => __('Number', 'nera-competitions-standard'),
                        'name'  => 'number',
                        'type'  => 'text',
                    ],
                    [
                        'key'   => 'field_pc_HowItWorksPostal_step_title',
                        'label' => __('Title', 'nera-competitions-standard'),
                        'name'  => 'title',
                        'type'  => 'text',
                    ],
                    [
                        'key'           => 'field_pc_HowItWorksPostal_step_icon',
                        'label'         => __('Icon', 'nera-competitions-standard'),
                        'name'          => 'icon',
                        'type'          => 'text',
                        'instructions'  => __('Material Symbols icon name (e.g. "mail", "contact_page", "verified_user").', 'nera-competitions-standard'),
                        'default_value' => 'mail',
                    ],
                    [
                        'key'   => 'field_pc_HowItWorksPostal_step_text',
                        'label' => __('Description', 'nera-competitions-standard'),
                        'name'  => 'text',
                        'type'  => 'textarea',
                        'rows'  => 3,
                    ],
                ],
            ],
            [
                'key'   => 'field_pc_HowItWorksPostal_note',
                'label' => __('Note / Disclaimer', 'nera-competitions-standard'),
                'name'  => 'note',
                'type'  => 'textarea',
                'rows'  => 2,
            ],
        ],
        'min' => '',
        'max' => '',
    ];
}
