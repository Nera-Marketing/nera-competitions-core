<?php
namespace Nera\Components\HowItWorksPostal;

if (!defined('ABSPATH')) exit;

function get_data(array $args = []): array
{
    $title = nera_component_field($args, 'title', 'hiw_postal_title',
        __('Free Postal Entry Route', 'nera-competitions'));
    $intro = nera_component_field($args, 'intro', 'hiw_postal_intro',
        __('We offer a free entry route via post for all of our competitions.', 'nera-competitions'));
    $postal_note = nera_component_field($args, 'note', 'hiw_postal_note',
        __('Please note: One entry per postcard. Entries must be received before the competition closes.', 'nera-competitions'));

    $default_steps = [
        [
            'number' => '1',
            'title'  => __('Include Your Details', 'nera-competitions'),
            'icon'   => 'contact_page',
            'text'   => __('Send your name, address, date of birth, contact phone number, and the name of the competition you wish to enter.', 'nera-competitions'),
        ],
        [
            'number' => '2',
            'title'  => __('Send Your Postcard', 'nera-competitions'),
            'icon'   => 'mail',
            'text'   => __('Send your entry on an unenclosed postcard via first or second class post to our registered business address.', 'nera-competitions'),
        ],
        [
            'number' => '3',
            'title'  => __('We Process It', 'nera-competitions'),
            'icon'   => 'verified_user',
            'text'   => __('Once received, your entry will be processed and included in the draw just like a paid entry.', 'nera-competitions'),
        ],
    ];

    $acf_postal_steps = nera_component_field($args, 'steps', 'hiw_postal_steps', null);
    $postal_steps = [];
    if (!empty($acf_postal_steps) && is_array($acf_postal_steps)) {
        foreach ($acf_postal_steps as $i => $step) {
            if (!empty($step['text'])) {
                $default = isset($default_steps[$i]) ? $default_steps[$i] : $default_steps[0];
                $postal_steps[] = [
                    'number' => !empty($step['number']) ? esc_html($step['number']) : (string)($i + 1),
                    'title'  => esc_html(!empty($step['title']) ? $step['title'] : $default['title']),
                    'icon'   => esc_html(!empty($step['icon'])  ? $step['icon']  : $default['icon']),
                    'text'   => esc_html($step['text']),
                    'delay'  => $i * 100,
                ];
            }
        }
    }
    if (empty($postal_steps)) {
        foreach ($default_steps as $i => $step) {
            $postal_steps[] = array_merge($step, [
                'title'  => esc_html($step['title']),
                'icon'   => esc_html($step['icon']),
                'text'   => esc_html($step['text']),
                'delay'  => $i * 100,
            ]);
        }
    }

    $note_delay = count($postal_steps) * 100;

    return [
        'title'      => esc_html($title),
        'intro'      => esc_html($intro),
        'steps'      => $postal_steps,
        'note'       => esc_html($postal_note),
        'note_delay' => $note_delay,
    ];
}
