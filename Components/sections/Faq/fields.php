<?php
namespace Nera\Components\Faq;

if (!defined('ABSPATH')) exit;

function get_acf_layout(): array {
    return [
        'key'        => 'layout_Faq',
        'name'       => 'Faq',
        'label'      => __('FAQ', 'nera-competitions-standard'),
        'display'    => 'block',
        'sub_fields' => [
            [
                'key'           => 'field_pc_faq_title',
                'label'         => __('Section Title', 'nera-competitions-standard'),
                'name'          => 'title',
                'type'          => 'text',
                'default_value' => 'Frequently Asked Questions',
            ],
            [
                'key'          => 'field_pc_faq_list',
                'label'        => __('FAQs', 'nera-competitions-standard'),
                'name'         => 'list',
                'type'         => 'repeater',
                'layout'       => 'row',
                'button_label' => __('Add Question', 'nera-competitions-standard'),
                'sub_fields'   => [
                    [
                        'key'   => 'field_pc_faq_question',
                        'label' => __('Question', 'nera-competitions-standard'),
                        'name'  => 'question',
                        'type'  => 'text',
                    ],
                    [
                        'key'   => 'field_pc_faq_answer',
                        'label' => __('Answer', 'nera-competitions-standard'),
                        'name'  => 'answer',
                        'type'  => 'textarea',
                        'rows'  => 3,
                    ],
                ],
            ],
        ],
        'min' => '',
        'max' => '',
    ];
}
