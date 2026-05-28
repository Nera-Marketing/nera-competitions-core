<?php
namespace Nera\Components\HowItWorksDraw;

if (!defined('ABSPATH')) exit;

/**
 * @param array $args
 * @return array{
 *   eyebrow: string,           // required, default 'Fair & Transparent' — esc_html eyebrow label
 *   title: string,             // required, default 'The Draw Process' — esc_html section heading
 *   content: string,           // required, default HTML paragraph — wp_kses_post body copy
 *   image_url: string,         // required, default '' — esc_url image src (empty = placeholder shown)
 *   image_alt: string,         // required, default '' — esc_attr image alt text
 *   placeholder_title: string, // required, default 'Live Draw Streams' — esc_html placeholder heading
 *   placeholder_text: string,  // required, default 'Watch us live on Facebook…' — esc_html placeholder body
 *   placeholder_icon: string,  // required, default 'videocam' — esc_html Material Symbol name
 * }
 */
function get_data(array $args = []): array
{
    $eyebrow = nera_component_field($args, 'eyebrow', 'hiw_draw_eyebrow',
        __('Fair & Transparent', 'nera-competitions'));
    $title   = nera_component_field($args, 'title', 'hiw_draw_title',
        __('The Draw Process', 'nera-competitions'));

    $raw_content = nera_component_field($args, 'content', 'hiw_draw_content', null);
    $content = $raw_content ? wp_kses_post($raw_content) : '';

    if ($content === '') {
        $content = wp_kses_post(
            __('Our draws are conducted with absolute transparency. We use the <strong>Google Random Number Generator</strong> to ensure every entry has an equal and fair chance of winning.', 'nera-competitions')
        ) . '<p>' . esc_html__('Join us live on our social media channels for every draw! We broadcast the entire process in real-time, announcing winners as they happen and celebrating with our community.', 'nera-competitions') . '</p>';
    }

    // draw_image: ACF returns array with 'url'/'alt', or legacy string URL
    $draw_image = nera_component_field($args, 'image', 'hiw_draw_image', null);
    $image_url  = '';
    $image_alt  = '';
    if (is_array($draw_image) && !empty($draw_image['url'])) {
        $image_url = $draw_image['url'];
        $image_alt = $draw_image['alt'] ?? '';
    } elseif (is_string($draw_image) && $draw_image !== '') {
        $image_url = $draw_image;
    }

    $placeholder_title = nera_component_field($args, 'placeholder_title', 'hiw_draw_placeholder_title',
        __('Live Draw Streams', 'nera-competitions'));
    $placeholder_text  = nera_component_field($args, 'placeholder_text', 'hiw_draw_placeholder_text',
        __('Watch us live on Facebook and Instagram', 'nera-competitions'));
    $placeholder_icon_raw = nera_component_field($args, 'placeholder_icon', 'hiw_draw_placeholder_icon', '');
    $placeholder_icon = (is_string($placeholder_icon_raw) && trim($placeholder_icon_raw) !== '')
        ? trim($placeholder_icon_raw)
        : 'videocam';

    return [
        'eyebrow'           => esc_html($eyebrow),
        'title'             => esc_html($title),
        'content'           => $content,
        'image_url'         => esc_url($image_url),
        'image_alt'         => esc_attr($image_alt),
        'placeholder_title' => esc_html($placeholder_title),
        'placeholder_text'  => esc_html($placeholder_text),
        'placeholder_icon'  => esc_html($placeholder_icon),
    ];
}
