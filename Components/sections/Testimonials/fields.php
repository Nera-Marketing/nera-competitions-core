<?php
namespace Nera\Components\Testimonials;

if (!defined('ABSPATH')) exit;

function get_acf_layout(): array {
    return [
        'key'        => 'layout_Testimonials',
        'name'       => 'Testimonials',
        'label'      => __('Testimonials', 'nera-competitions-standard'),
        'display'    => 'block',
        'sub_fields' => [
            [
                'key'           => 'field_pc_testimonials_title',
                'label'         => __('Section Title', 'nera-competitions-standard'),
                'name'          => 'title',
                'type'          => 'text',
                'default_value' => 'Stories of the Circle',
            ],
            [
                'key'           => 'field_pc_testimonials_subtitle',
                'label'         => __('Section Subtitle', 'nera-competitions-standard'),
                'name'          => 'subtitle',
                'type'          => 'textarea',
                'rows'          => 2,
                'default_value' => 'Step inside the lives of those who dared to dream. Real people, life-changing moments.',
            ],
            [
                'key'          => 'field_pc_testimonials_list',
                'label'        => __('Testimonials List', 'nera-competitions-standard'),
                'name'         => 'list',
                'type'         => 'repeater',
                'layout'       => 'block',
                'button_label' => __('Add Testimonial', 'nera-competitions-standard'),
                'sub_fields'   => [
                    [
                        'key'   => 'field_pc_testimonials_name',
                        'label' => __('Name', 'nera-competitions-standard'),
                        'name'  => 'name',
                        'type'  => 'text',
                    ],
                    [
                        'key'           => 'field_pc_testimonials_avatar',
                        'label'         => __('Avatar', 'nera-competitions-standard'),
                        'name'          => 'avatar',
                        'type'          => 'image',
                        'return_format' => 'url',
                    ],
                    [
                        'key'   => 'field_pc_testimonials_quote',
                        'label' => __('Quote', 'nera-competitions-standard'),
                        'name'  => 'quote',
                        'type'  => 'textarea',
                        'rows'  => 3,
                    ],
                    [
                        'key'   => 'field_pc_testimonials_prize',
                        'label' => __('Prize Won', 'nera-competitions-standard'),
                        'name'  => 'prize',
                        'type'  => 'text',
                    ],
                ],
            ],
        ],
        'min' => '',
        'max' => '',
    ];
}
