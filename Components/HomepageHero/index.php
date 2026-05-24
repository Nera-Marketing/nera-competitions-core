<?php
namespace Nera\Components\HomepageHero;

if (!defined('ABSPATH')) exit;

function get_data(): array
{
    $last_winner_name = get_field('last_winner_name') ?: 'Sarah M.';

    return [
        'title'       => get_field('hero_title')       ?: __('Win Your Dream', 'nera-competitions'),
        'highlight'   => get_field('hero_highlight')   ?: __('Lifestyle.', 'nera-competitions'),
        'description' => get_field('hero_description') ?: __(
            "Experience the thrill of high-end giveaways with the UK's most exclusive prize competition platform. Because you deserve a chance to win.",
            'nera-competitions'
        ),
        'cta' => [
            'text' => get_field('hero_cta_text') ?: __('View Active Giveaways', 'nera-competitions'),
            'url'  => get_field('hero_cta_url')  ?: get_permalink(wc_get_page_id('shop')),
        ],
        'secondary' => [
            'text' => get_field('hero_secondary_text') ?: __('Recent Winners', 'nera-competitions'),
            'url'  => get_field('hero_secondary_url')  ?: '#winners',
        ],
        'image' => get_field('hero_image'),
        'last_winner' => [
            'name'    => $last_winner_name,
            'initial' => mb_substr($last_winner_name, 0, 1),
            'prize'   => get_field('last_winner_prize') ?: 'Won This Prize',
        ],
        'i18n' => [
            'badge'       => __('Premium Giveaways', 'nera-competitions'),
            'last_winner' => __('Last Winner:', 'nera-competitions'),
        ],
    ];
}
