<?php
namespace Nera\Components\AboutUsPage;

if (!defined('ABSPATH')) exit;

function get_data(array $args = []): array
{
    $hero_image = nera_component_field($args, 'hero_image', 'about_hero_image', null);

    return [
        'hero_eyebrow'        => nera_component_field($args, 'hero_eyebrow', 'about_hero_eyebrow',
                                     __('About us', 'nera-competitions')),
        'title'               => nera_component_field($args, 'title', 'about_title',
                                     get_the_title()),
        'hero_tagline'        => nera_component_field($args, 'hero_tagline', 'about_hero_tagline',
                                     __('Building a community rooted in transparency, trust, and exciting opportunities for everyone.', 'nera-competitions')),
        'hero_image_url'      => is_array($hero_image) ? ($hero_image['url'] ?? '') : (string) $hero_image,
        'hero_image_alt'      => is_array($hero_image) ? ($hero_image['alt'] ?? '') : '',
        'narrative'           => wp_kses_post(nera_component_field($args, 'narrative', 'about_narrative', '')),
        'story_left_title'    => nera_component_field($args, 'story_left_title', 'about_story_left_title',
                                     __('Our story', 'nera-competitions')),
        'story_left_content'  => wp_kses_post(nera_component_field($args, 'story_left_content', 'about_story_left_content', '')),
        'story_right_title'   => nera_component_field($args, 'story_right_title', 'about_story_right_title',
                                     __('What drives us', 'nera-competitions')),
        'story_right_content' => wp_kses_post(nera_component_field($args, 'story_right_content', 'about_story_right_content', '')),
        'cta_heading'         => nera_component_field($args, 'cta_heading', 'about_cta_heading',
                                     __('Join the community', 'nera-competitions')),
        'cta_description'     => nera_component_field($args, 'cta_description', 'about_cta_description',
                                     __('Be part of a transparent, supportive journey where everyone has a chance to win.', 'nera-competitions')),
        'cta_primary_text'    => nera_component_field($args, 'cta_primary_text', 'about_cta_primary_btn_text',
                                     __('Explore competitions', 'nera-competitions')),
        'cta_primary_url'     => nera_component_field($args, 'cta_primary_url', 'about_cta_primary_btn_url',
                                     home_url('/shop/')),
        'cta_secondary_text'  => nera_component_field($args, 'cta_secondary_text', 'about_cta_secondary_btn_text',
                                     __('Get in touch', 'nera-competitions')),
        'cta_secondary_url'   => nera_component_field($args, 'cta_secondary_url', 'about_cta_secondary_btn_url',
                                     home_url('/contact/')),
        'i18n' => [
            'image_placeholder'       => __('Image placeholder', 'nera-competitions'),
            'narrative_label'         => __('Our narrative', 'nera-competitions'),
            'narrative_placeholder'   => __('More about us is coming soon…', 'nera-competitions'),
            'story_left_placeholder'  => __('We will share more about our journey here.', 'nera-competitions'),
            'story_right_placeholder' => __('Insights and values will appear here.', 'nera-competitions'),
        ],
    ];
}
