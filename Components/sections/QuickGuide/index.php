<?php
namespace Nera\Components\QuickGuide;

if (!defined('ABSPATH')) exit;

/**
 * @param array $args
 * @return array{
 *   title: string,        // required, default 'How to Play' — section heading
 *   subtitle: string,     // required, default 'Win your dream prizes in just three simple steps' — section subheading
 *   steps: list<array{    // required, default 3-item list — guide step cards
 *     number: string,     // e.g. '01'
 *     icon: string,       // trusted HTML — inline SVG string
 *     title: string,
 *     description: string,
 *   }>,
 *   cta_url: string,      // required, always home_url('/all-competitions') — CTA href
 * }
 */
function get_data(array $args = []): array
{
    $default_steps = [
        [
            'number' => '01',
            'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h6"/><circle cx="12" cy="12" r="2"/><path d="m16 8-2.6 2.6"/><circle cx="18" cy="6" r="3"/></svg>',
            'title'  => __('Select Prize', 'nera-competitions'),
            'description' => __('Browse our active luxury giveaways and choose the prize you want to win most.', 'nera-competitions'),
        ],
        [
            'number' => '02',
            'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg>',
            'title'  => __('Choose Tickets', 'nera-competitions'),
            'description' => __('Select how many entries you want. Each ticket increases your chances of holding the winning number.', 'nera-competitions'),
        ],
        [
            'number' => '03',
            'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
            'title'  => __('Wait for Draw', 'nera-competitions'),
            'description' => __('Answer the skill-based question correctly and wait for the live draw. Good luck!', 'nera-competitions'),
        ],
    ];

    $steps_raw = nera_component_field($args, 'steps', 'guide_steps', null);
    $steps = (is_array($steps_raw) && !empty($steps_raw)) ? $steps_raw : $default_steps;

    return [
        'title'    => nera_component_field($args, 'title',    'guide_title',    __('How to Play', 'nera-competitions')),
        'subtitle' => nera_component_field($args, 'subtitle', 'guide_subtitle', __('Win your dream prizes in just three simple steps', 'nera-competitions')),
        'steps'    => $steps,
        'cta_url'  => home_url('/all-competitions'),
    ];
}
