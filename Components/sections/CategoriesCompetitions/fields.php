<?php
namespace Nera\Components\CategoriesCompetitions;

if (!defined('ABSPATH')) exit;

function get_acf_layout(): array {
    return [
        'key'        => 'layout_CategoriesCompetitions',
        'name'       => 'CategoriesCompetitions',
        'label'      => __('Categories & Competitions', 'nera-competitions-standard'),
        'display'    => 'block',
        'sub_fields' => nera_with_heading_fields([
            [
                'key'           => 'field_pc_categories_title',
                'label'         => __('Section Title', 'nera-competitions-standard'),
                'name'          => 'title',
                'type'          => 'text',
                'default_value' => 'Find Your Dream Prize',
            ],
            [
                'key'   => 'field_pc_categories_subtitle',
                'label' => __('Section Subtitle', 'nera-competitions-standard'),
                'name'  => 'subtitle',
                'type'  => 'text',
            ],
        ], 'CategoriesCompetitions'),
        'min' => '',
        'max' => '',
    ];
}
