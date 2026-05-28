<?php
namespace Nera\Components\Testimonials;

if (!defined('ABSPATH')) exit;

/**
 * @param array $args
 * @return array{
 *   title: string,       // required, default 'Stories of the Circle' — section heading
 *   subtitle: string,    // required, default 'Step inside the lives…' — section subheading
 *   list: list<array{    // required, default 2-item list — testimonial cards
 *     name: string,
 *     avatar: string,    // image URL or '' (fallback: initial shown)
 *     quote: string,
 *     prize: string,
 *     initial: string,   // derived — first character of name, always present
 *   }>,
 * }
 */
function get_data(array $args = []): array
{
    $default_list = [
        [
            'name'   => 'James Robinson',
            'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop&crop=face',
            'quote'  => "I still wake up and check my driveway to make sure it's not a dream. The whole process was seamless and the phone call from the team was the best moment of my year.",
            'prize'  => 'BMW M4 Competition',
        ],
        [
            'name'   => 'Sarah Lewis',
            'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&h=150&fit=crop&crop=face',
            'quote'  => "Winning the £10k cash meant I could finally take my family on the holiday we've been putting off for five years. It truly changed everything for us this summer.",
            'prize'  => '£10,000 Tax-Free Cash',
        ],
    ];

    $list_raw = nera_component_field($args, 'list', 'testimonials_list', null);
    $list = (is_array($list_raw) && !empty($list_raw)) ? $list_raw : $default_list;

    foreach ($list as &$item) {
        $item['initial'] = mb_substr($item['name'] ?? '', 0, 1);
    }
    unset($item);

    return [
        'title'    => nera_component_field($args, 'title',    'testimonials_title',    __('Stories of the Circle', 'nera-competitions')),
        'subtitle' => nera_component_field($args, 'subtitle', 'testimonials_subtitle', __('Step inside the lives of those who dared to dream. Real people, life-changing moments.', 'nera-competitions')),
        'list'     => $list,
    ];
}
