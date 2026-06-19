<?php
namespace Nera\Components\About;

if (!defined('ABSPATH')) exit;

function get_acf_layout(): array {
    return [
        'key'        => 'layout_About',
        'name'       => 'About',
        'label'      => __('About', 'nera-competitions-standard'),
        'display'    => 'block',
        'sub_fields' => nera_with_heading_fields([
            [
                'key'           => 'field_pc_about_badge',
                'label'         => __('Badge Label', 'nera-competitions-standard'),
                'name'          => 'badge',
                'type'          => 'text',
                'instructions'  => __('Optional badge text (e.g., "Who We Are", "Our Story").', 'nera-competitions-standard'),
                'default_value' => 'Who We Are',
            ],
            [
                'key'           => 'field_pc_about_title',
                'label'         => __('Section Title', 'nera-competitions-standard'),
                'name'          => 'title',
                'type'          => 'text',
                'instructions'  => __('Main heading for the About section.', 'nera-competitions-standard'),
                'default_value' => 'Your Trusted Partner in Premium Giveaways',
            ],
            [
                'key'           => 'field_pc_about_subtitle',
                'label'         => __('Subtitle', 'nera-competitions-standard'),
                'name'          => 'subtitle',
                'type'          => 'text',
                'instructions'  => __('Brief tagline or subtitle.', 'nera-competitions-standard'),
                'default_value' => 'Bringing dreams to life, one competition at a time.',
            ],
            [
                'key'          => 'field_pc_about_description',
                'label'        => __('Description', 'nera-competitions-standard'),
                'name'         => 'description',
                'type'         => 'wysiwyg',
                'instructions' => __('Main descriptive text (supports paragraphs).', 'nera-competitions-standard'),
                'default_value' => "We're passionate about creating life-changing moments through fair, transparent, and exciting prize competitions. With over 150 winners and £2M+ in prizes awarded, we've built a trusted community of dreamers and winners.",
                'media_upload' => 0,
                'toolbar'      => 'basic',
                'rows'         => 6,
            ],
            [
                'key'          => 'field_pc_about_features',
                'label'        => __('Feature List', 'nera-competitions-standard'),
                'name'         => 'features',
                'type'         => 'repeater',
                'instructions' => __('Key features or values (optional, up to 6 recommended).', 'nera-competitions-standard'),
                'layout'       => 'table',
                'button_label' => __('Add Feature', 'nera-competitions-standard'),
                'max'          => 6,
                'sub_fields'   => [
                    [
                        'key'           => 'field_pc_about_feature_icon',
                        'label'         => __('Icon', 'nera-competitions-standard'),
                        'name'          => 'icon',
                        'type'          => 'text',
                        'instructions'  => __('Material Symbol icon name (e.g., "verified", "shield", "star").', 'nera-competitions-standard'),
                        'default_value' => 'check_circle',
                    ],
                    [
                        'key'           => 'field_pc_about_feature_text',
                        'label'         => __('Text', 'nera-competitions-standard'),
                        'name'          => 'text',
                        'type'          => 'text',
                        'default_value' => 'Feature text here',
                    ],
                ],
            ],
            [
                'key'           => 'field_pc_about_show_cta',
                'label'         => __('Show CTA Button', 'nera-competitions-standard'),
                'name'          => 'show_cta',
                'type'          => 'true_false',
                'default_value' => 1,
                'ui'            => 1,
            ],
            [
                'key'               => 'field_pc_about_cta_text',
                'label'             => __('CTA Button Text', 'nera-competitions-standard'),
                'name'              => 'cta_text',
                'type'              => 'text',
                'default_value'     => 'Learn More About Us',
                'conditional_logic' => [
                    [
                        [
                            'field'    => 'field_pc_about_show_cta',
                            'operator' => '==',
                            'value'    => '1',
                        ],
                    ],
                ],
            ],
            [
                'key'               => 'field_pc_about_cta_url',
                'label'             => __('CTA Button URL', 'nera-competitions-standard'),
                'name'              => 'cta_url',
                'type'              => 'text',
                'default_value'     => '/about/',
                'conditional_logic' => [
                    [
                        [
                            'field'    => 'field_pc_about_show_cta',
                            'operator' => '==',
                            'value'    => '1',
                        ],
                    ],
                ],
            ],
            [
                'key'           => 'field_pc_about_image',
                'label'         => __('Section Image', 'nera-competitions-standard'),
                'name'          => 'image',
                'type'          => 'image',
                'instructions'  => __('Main image for the About section (recommended: 800x1000px or larger, 4:5 ratio).', 'nera-competitions-standard'),
                'return_format' => 'array',
                'preview_size'  => 'medium',
                'library'       => 'all',
            ],
            [
                'key'           => 'field_pc_about_image_position',
                'label'         => __('Image Position', 'nera-competitions-standard'),
                'name'          => 'image_position',
                'type'          => 'select',
                'instructions'  => __('Choose whether image appears on left or right side.', 'nera-competitions-standard'),
                'choices'       => [
                    'right' => 'Right (Text Left)',
                    'left'  => 'Left (Text Right)',
                ],
                'default_value' => 'right',
            ],
            [
                'key'           => 'field_pc_about_background',
                'label'         => __('Background Style', 'nera-competitions-standard'),
                'name'          => 'background',
                'type'          => 'select',
                'instructions'  => __('Section background color.', 'nera-competitions-standard'),
                'choices'       => [
                    'white'    => 'White',
                    'gray'     => 'Light Gray',
                    'gradient' => 'Gradient (White to Blue) - Recommended',
                ],
                'default_value' => 'gradient',
            ],
        ], 'About'),
        'min' => '',
        'max' => '',
    ];
}
