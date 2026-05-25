<?php
namespace Nera\Components\HomepageHero;

if (!defined('ABSPATH')) exit;

function get_data(array $args = []): array
{
    $winner_name = nera_component_field($args, 'winner_name', 'last_winner_name', 'Sarah M.');

    return [
        'title'       => nera_component_field($args, 'title',       'hero_title',       __('Win Your Dream', 'nera-competitions')),
        'highlight'   => nera_component_field($args, 'highlight',   'hero_highlight',   __('Lifestyle.', 'nera-competitions')),
        'description' => nera_component_field($args, 'description', 'hero_description', __(
            "Experience the thrill of high-end giveaways with the UK's most exclusive prize competition platform. Because you deserve a chance to win.",
            'nera-competitions'
        )),
        'cta' => [
            'text' => nera_component_field($args, 'cta_text', 'hero_cta_text', __('View Active Giveaways', 'nera-competitions')),
            'url'  => nera_component_field($args, 'cta_url',  'hero_cta_url',  null) ?: get_permalink(wc_get_page_id('shop')),
        ],
        'secondary' => [
            'text' => nera_component_field($args, 'secondary_text', 'hero_secondary_text', __('Recent Winners', 'nera-competitions')),
            'url'  => nera_component_field($args, 'secondary_url',  'hero_secondary_url',  '#winners'),
        ],
        'image' => nera_component_field($args, 'image', 'hero_image', null),
        'last_winner' => [
            'name'    => $winner_name,
            'initial' => mb_substr($winner_name, 0, 1),
            'prize'   => nera_component_field($args, 'winner_prize', 'last_winner_prize', 'Won This Prize'),
        ],
        'i18n' => [
            'badge'       => __('Premium Giveaways', 'nera-competitions'),
            'last_winner' => __('Last Winner:', 'nera-competitions'),
        ],
    ];
}
