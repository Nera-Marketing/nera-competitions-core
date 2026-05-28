<?php
namespace Nera\Components\HomepageHero;

if (!defined('ABSPATH')) exit;

/**
 * @param array $args
 * @return array{
 *   title: string,       // required, default 'Win Your Dream' — main headline (line 1)
 *   highlight: string,   // required, default 'Lifestyle.' — gradient-coloured headline (line 2)
 *   description: string, // required, default long tagline — body copy below headline
 *   cta: array{          // required — primary CTA button
 *     text: string,      // default 'View Active Giveaways'
 *     url: string,       // default WC shop permalink
 *   },
 *   secondary: array{    // required — secondary CTA link
 *     text: string,      // default 'Recent Winners'
 *     url: string,       // default '#winners'
 *   },
 *   image: string,       // required, default '' — resolved image URL; empty string when none (placeholder shown)
 *   last_winner: array{  // required — last winner badge data
 *     name: string,      // default 'Sarah M.'
 *     initial: string,   // first character of name
 *     prize: string,     // default 'Won This Prize'
 *   },
 *   i18n: array{         // required — translated UI strings
 *     badge: string,     // 'Premium Giveaways'
 *     last_winner: string, // 'Last Winner:'
 *   },
 * }
 */
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
        'image' => (function () use ($args): string {
            $raw = nera_component_field($args, 'image', 'hero_image', null);
            if (is_array($raw)) {
                $url = $raw['sizes']['large'] ?? $raw['url'] ?? '';
                return $url ? esc_url($url) : '';
            }
            if (is_int($raw) && $raw > 0) {
                $url = wp_get_attachment_image_url($raw, 'large');
                return $url ? esc_url($url) : '';
            }
            if (is_string($raw) && $raw !== '') {
                return esc_url($raw);
            }
            return '';
        })(),
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
