<?php
namespace Nera\Components\Credibility;

if (!defined('ABSPATH')) exit;

function get_data(array $args = []): array
{
    $default_items = [
        ['icon' => 'lock',        'label' => __('Secure Payments',    'nera-competitions')],
        ['icon' => 'verified',    'label' => __('UK Compliant',       'nera-competitions')],
        ['icon' => 'visibility',  'label' => __('Transparent Draws',  'nera-competitions')],
        ['icon' => 'emoji_events','label' => __('Real Winners',        'nera-competitions')],
        ['icon' => 'headset_mic', 'label' => __('Fast Support',       'nera-competitions')],
    ];

    $items = [];
    if (function_exists('have_rows') && have_rows('credibility_items')) {
        while (have_rows('credibility_items')) {
            the_row();
            $items[] = [
                'icon'  => get_sub_field('icon') ?: 'check_circle',
                'label' => get_sub_field('label') ?: '',
            ];
        }
    }

    return ['items' => !empty($items) ? $items : $default_items];
}
