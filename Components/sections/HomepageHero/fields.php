<?php
namespace Nera\Components\HomepageHero;

if (!defined('ABSPATH')) exit;

function get_acf_layout(): array {
    return [
        'key'        => 'layout_HomepageHero',
        'name'       => 'HomepageHero',
        'label'      => __('Homepage Hero', 'nera-competitions-standard'),
        'display'    => 'block',
        'sub_fields' => nera_with_heading_fields([
            [
                'key'           => 'field_pc_homepage_hero_title',
                'label'         => __('Title', 'nera-competitions-standard'),
                'name'          => 'title',
                'type'          => 'text',
                'instructions'  => __('Main heading for the hero section.', 'nera-competitions-standard'),
                'default_value' => 'Win Your Dream',
            ],
            [
                'key'           => 'field_pc_homepage_hero_highlight',
                'label'         => __('Highlight Text', 'nera-competitions-standard'),
                'name'          => 'highlight',
                'type'          => 'text',
                'instructions'  => __('Highlighted part of the title (gradient text).', 'nera-competitions-standard'),
                'default_value' => 'Lifestyle.',
            ],
            [
                'key'           => 'field_pc_homepage_hero_description',
                'label'         => __('Description', 'nera-competitions-standard'),
                'name'          => 'description',
                'type'          => 'textarea',
                'instructions'  => __('Subtitle description.', 'nera-competitions-standard'),
                'default_value' => "Experience the thrill of high-end giveaways with the UK's most exclusive prize competition platform.",
                'rows'          => 3,
            ],
            [
                'key'           => 'field_pc_homepage_hero_cta_text',
                'label'         => __('Primary CTA Text', 'nera-competitions-standard'),
                'name'          => 'cta_text',
                'type'          => 'text',
                'default_value' => 'View Active Giveaways',
            ],
            [
                'key'           => 'field_pc_homepage_hero_cta_url',
                'label'         => __('Primary CTA URL', 'nera-competitions-standard'),
                'name'          => 'cta_url',
                'type'          => 'text',
                'default_value' => '/shop/',
            ],
            [
                'key'           => 'field_pc_homepage_hero_secondary_text',
                'label'         => __('Secondary CTA Text', 'nera-competitions-standard'),
                'name'          => 'secondary_text',
                'type'          => 'text',
                'default_value' => 'Recent Winners',
            ],
            [
                'key'           => 'field_pc_homepage_hero_secondary_url',
                'label'         => __('Secondary CTA URL', 'nera-competitions-standard'),
                'name'          => 'secondary_url',
                'type'          => 'text',
                'default_value' => '#winners',
            ],
            [
                'key'           => 'field_pc_homepage_hero_image',
                'label'         => __('Hero Image', 'nera-competitions-standard'),
                'name'          => 'image',
                'type'          => 'image',
                'return_format' => 'url',
                'preview_size'  => 'medium',
                'library'       => 'all',
            ],
            [
                'key'           => 'field_pc_homepage_hero_winner_name',
                'label'         => __('Last Winner Name', 'nera-competitions-standard'),
                'name'          => 'winner_name',
                'type'          => 'text',
                'default_value' => 'Sarah M.',
            ],
            [
                'key'           => 'field_pc_homepage_hero_winner_prize',
                'label'         => __('Last Winner Prize', 'nera-competitions-standard'),
                'name'          => 'winner_prize',
                'type'          => 'text',
                'default_value' => 'Won This Prize',
            ],
        ], 'HomepageHero'),
        'min' => '',
        'max' => '',
    ];
}
