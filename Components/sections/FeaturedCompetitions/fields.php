<?php
namespace Nera\Components\FeaturedCompetitions;

if (!defined('ABSPATH')) exit;

function get_acf_layout(): array {
    return [
        'key'        => 'layout_FeaturedCompetitions',
        'name'       => 'FeaturedCompetitions',
        'label'      => __('Featured Competitions', 'nera-competitions-standard'),
        'display'    => 'block',
        'sub_fields' => [
            [
                'key'           => 'field_pc_featured_title',
                'label'         => __('Section Title', 'nera-competitions-standard'),
                'name'          => 'title',
                'type'          => 'text',
                'default_value' => 'Ending Soon',
            ],
            [
                'key'           => 'field_pc_featured_subtitle',
                'label'         => __('Section Subtitle', 'nera-competitions-standard'),
                'name'          => 'subtitle',
                'type'          => 'text',
                'default_value' => "Grab your tickets before they're gone forever.",
            ],
        ],
        'min' => '',
        'max' => '',
    ];
}
