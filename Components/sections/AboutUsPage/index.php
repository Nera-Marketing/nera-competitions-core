<?php
namespace Nera\Components\AboutUsPage;

if (!defined('ABSPATH')) exit;

/**
 * @param array $args
 * @return array{
 *   hero_eyebrow: string,         // required, default 'About us' — eyebrow label above hero title
 *   title: string,                // required, default get_the_title() — page/hero heading
 *   hero_tagline: string,         // required, default 'Building a community…' — italic subtitle in hero
 *   hero_image_url: string,       // required, default '' — hero image src (empty = placeholder shown)
 *   hero_image_alt: string,       // required, default '' — hero image alt text
 *   narrative: string,            // required, default '', wp_kses_post — prose narrative block (may be empty)
 *   story_left_title: string,     // required, default 'Our story' — left story card heading
 *   story_left_content: string,   // required, default '', wp_kses_post — left story card body
 *   story_right_title: string,    // required, default 'What drives us' — right story card heading
 *   story_right_content: string,  // required, default '', wp_kses_post — right story card body
 *   cta_heading: string,          // required, default 'Join the community' — CTA section heading
 *   cta_description: string,      // required, default 'Be part of…' — CTA body text
 *   cta_primary_text: string,     // required, default 'Explore competitions' — primary button label
 *   cta_primary_url: string,      // required, default home_url('/shop/') — primary button href
 *   cta_secondary_text: string,   // required, default 'Get in touch' — secondary button label
 *   cta_secondary_url: string,    // required, default home_url('/contact/') — secondary button href
 *   i18n: array{
 *     image_placeholder: string,       // 'Image placeholder'
 *     narrative_label: string,         // 'Our narrative' (sr-only label)
 *     narrative_placeholder: string,   // shown when narrative is empty
 *     story_left_placeholder: string,  // shown when story_left_content is empty
 *     story_right_placeholder: string, // shown when story_right_content is empty
 *   },                            // required — translated UI strings
 * }
 */
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
