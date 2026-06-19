<?php
namespace Nera\Components\HowItWorksDraw;

if (!defined('ABSPATH')) exit;

function get_acf_layout(): array
{
    return [
        'key'        => 'layout_HowItWorksDraw',
        'name'       => 'HowItWorksDraw',
        'label'      => __('How It Works — Draw Process', 'nera-competitions-standard'),
        'display'    => 'block',
        'sub_fields' => nera_with_heading_fields([
            [
                'key'           => 'field_pc_HowItWorksDraw_eyebrow',
                'label'         => __('Eyebrow Label', 'nera-competitions-standard'),
                'name'          => 'eyebrow',
                'type'          => 'text',
                'default_value' => 'Fair & Transparent',
            ],
            [
                'key'           => 'field_pc_HowItWorksDraw_title',
                'label'         => __('Section Title', 'nera-competitions-standard'),
                'name'          => 'title',
                'type'          => 'text',
                'default_value' => 'The Draw Process',
            ],
            [
                'key'          => 'field_pc_HowItWorksDraw_content',
                'label'        => __('Content', 'nera-competitions-standard'),
                'name'         => 'content',
                'type'         => 'wysiwyg',
                'media_upload' => 0,
                'toolbar'      => 'basic',
                'rows'         => 6,
            ],
            [
                'key'           => 'field_pc_HowItWorksDraw_image',
                'label'         => __('Section Image', 'nera-competitions-standard'),
                'name'          => 'image',
                'type'          => 'image',
                'return_format' => 'array',
                'preview_size'  => 'medium',
            ],
            [
                'key'           => 'field_pc_HowItWorksDraw_placeholder_title',
                'label'         => __('Placeholder Title', 'nera-competitions-standard'),
                'name'          => 'placeholder_title',
                'type'          => 'text',
                'default_value' => 'Live Draw Streams',
                'instructions'  => __('Shown when no image is uploaded.', 'nera-competitions-standard'),
            ],
            [
                'key'           => 'field_pc_HowItWorksDraw_placeholder_text',
                'label'         => __('Placeholder Text', 'nera-competitions-standard'),
                'name'          => 'placeholder_text',
                'type'          => 'text',
                'default_value' => 'Watch us live on Facebook and Instagram',
            ],
            [
                'key'           => 'field_pc_HowItWorksDraw_placeholder_icon',
                'label'         => __('Placeholder Icon', 'nera-competitions-standard'),
                'name'          => 'placeholder_icon',
                'type'          => 'text',
                'default_value' => 'videocam',
                'instructions'  => __('Material Symbols icon name (e.g. "videocam", "live_tv").', 'nera-competitions-standard'),
            ],
        ], 'HowItWorksDraw'),
        'min' => '',
        'max' => '',
    ];
}
