<?php
namespace Nera\Components\PromoBanner;

if (!defined('ABSPATH')) exit;

function get_acf_layout(): array {
    return [
        'key'        => 'layout_PromoBanner',
        'name'       => 'PromoBanner',
        'label'      => __('Promo Banner', 'nera-competitions-standard'),
        'display'    => 'block',
        'sub_fields' => nera_with_heading_fields([
            [
                'key'           => 'field_pc_promo_badge',
                'label'         => __('Badge Text', 'nera-competitions-standard'),
                'name'          => 'badge',
                'type'          => 'text',
                'default_value' => 'Stay Connected',
            ],
            [
                'key'           => 'field_pc_promo_title',
                'label'         => __('Title', 'nera-competitions-standard'),
                'name'          => 'title',
                'type'          => 'text',
                'default_value' => 'Follow us on socials',
            ],
            [
                'key'           => 'field_pc_promo_description',
                'label'         => __('Description', 'nera-competitions-standard'),
                'name'          => 'description',
                'type'          => 'textarea',
                'rows'          => 3,
                'default_value' => 'Follow us for updates, new competitions and giveaways.',
            ],
            [
                'key'          => 'field_pc_promo_bg_image',
                'label'        => __('Background Image', 'nera-competitions-standard'),
                'name'         => 'bg_image',
                'type'         => 'image',
                'return_format' => 'url',
                'instructions' => __('Optional background image for the social section.', 'nera-competitions-standard'),
            ],
            [
                'key'          => 'field_pc_promo_social_links',
                'label'        => __('Social Links', 'nera-competitions-standard'),
                'name'         => 'social_links',
                'type'         => 'repeater',
                'instructions' => __('Add your social media links. Choose a platform and enter the URL.', 'nera-competitions-standard'),
                'layout'       => 'table',
                'button_label' => __('Add Social Link', 'nera-competitions-standard'),
                'sub_fields'   => [
                    [
                        'key'     => 'field_pc_promo_social_platform',
                        'label'   => __('Platform', 'nera-competitions-standard'),
                        'name'    => 'platform',
                        'type'    => 'select',
                        'choices' => [
                            'facebook'  => 'Facebook',
                            'instagram' => 'Instagram',
                            'twitter'   => 'Twitter/X',
                            'youtube'   => 'YouTube',
                            'tiktok'    => 'TikTok',
                        ],
                    ],
                    [
                        'key'   => 'field_pc_promo_social_url',
                        'label' => __('URL', 'nera-competitions-standard'),
                        'name'  => 'url',
                        'type'  => 'url',
                    ],
                ],
            ],
        ], 'PromoBanner'),
        'min' => '',
        'max' => '',
    ];
}
