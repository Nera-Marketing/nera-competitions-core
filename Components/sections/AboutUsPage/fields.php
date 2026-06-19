<?php
namespace Nera\Components\AboutUsPage;

if (!defined('ABSPATH')) exit;

function get_acf_layout(): array
{
    return [
        'key'        => 'layout_AboutUsPage',
        'name'       => 'AboutUsPage',
        'label'      => 'About Us Page',
        'display'    => 'block',
        'sub_fields' => nera_with_heading_fields([
            ['key' => 'field_pc_AboutUsPage_hero_eyebrow',        'name' => 'hero_eyebrow',        'label' => 'Hero Eyebrow',        'type' => 'text'],
            ['key' => 'field_pc_AboutUsPage_title',               'name' => 'title',               'label' => 'Title',               'type' => 'text'],
            ['key' => 'field_pc_AboutUsPage_hero_tagline',        'name' => 'hero_tagline',        'label' => 'Hero Tagline',        'type' => 'textarea'],
            ['key' => 'field_pc_AboutUsPage_hero_image',          'name' => 'hero_image',          'label' => 'Hero Image',          'type' => 'image', 'return_format' => 'array'],
            ['key' => 'field_pc_AboutUsPage_narrative',           'name' => 'narrative',           'label' => 'Narrative',           'type' => 'wysiwyg'],
            ['key' => 'field_pc_AboutUsPage_story_left_title',    'name' => 'story_left_title',    'label' => 'Story Left Title',    'type' => 'text'],
            ['key' => 'field_pc_AboutUsPage_story_left_content',  'name' => 'story_left_content',  'label' => 'Story Left Content',  'type' => 'wysiwyg'],
            ['key' => 'field_pc_AboutUsPage_story_right_title',   'name' => 'story_right_title',   'label' => 'Story Right Title',   'type' => 'text'],
            ['key' => 'field_pc_AboutUsPage_story_right_content', 'name' => 'story_right_content', 'label' => 'Story Right Content', 'type' => 'wysiwyg'],
            ['key' => 'field_pc_AboutUsPage_cta_heading',         'name' => 'cta_heading',         'label' => 'CTA Heading',         'type' => 'text'],
            ['key' => 'field_pc_AboutUsPage_cta_description',     'name' => 'cta_description',     'label' => 'CTA Description',     'type' => 'textarea'],
            ['key' => 'field_pc_AboutUsPage_cta_primary_text',    'name' => 'cta_primary_text',    'label' => 'CTA Primary Text',    'type' => 'text'],
            ['key' => 'field_pc_AboutUsPage_cta_primary_url',     'name' => 'cta_primary_url',     'label' => 'CTA Primary URL',     'type' => 'url'],
            ['key' => 'field_pc_AboutUsPage_cta_secondary_text',  'name' => 'cta_secondary_text',  'label' => 'CTA Secondary Text',  'type' => 'text'],
            ['key' => 'field_pc_AboutUsPage_cta_secondary_url',   'name' => 'cta_secondary_url',   'label' => 'CTA Secondary URL',   'type' => 'url'],
        ], 'AboutUsPage'),
    ];
}
