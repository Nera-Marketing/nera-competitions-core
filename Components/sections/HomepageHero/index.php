<?php
namespace Nera\Components\HomepageHero;

if (!defined('ABSPATH')) exit;

/**
 * @param array $args
 * @return array{
 *   title: string,       // required, default 'Win Your Dream' (only when never saved; '' = hidden) — main headline (line 1)
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
 *   image: string,       // required, default '' — resolved image URL; empty string when none (placeholder shown; no hero when title, highlight, description and image are all '')
 *   last_winner: array{  // required — last winner badge data
 *     name: string,      // default 'Sarah M.' only when never saved; '' hides the last winner badge
 *     initial: string,   // first character of name ('' when name is '')
 *     prize: string,     // default 'Won This Prize'
 *   },
 *   i18n: array{         // required — translated UI strings
 *     badge: string,     // 'Premium Giveaways'
 *     last_winner: string, // 'Last Winner:'
 *   },
 * }
 */
/**
 * Read one hero field. A value saved on the component row wins, even when
 * blank (a blank field hides its element). With no row value, the page's
 * legacy field is used when it was ever saved, again blank included. The
 * default only applies when neither was ever saved.
 *
 * @param array  $args       Component args (`acf_row` when rendered as a row).
 * @param string $row_key    Sub field name on the component row.
 * @param string $legacy_key Legacy page field name.
 * @param mixed  $default    Value when the field was never saved.
 * @return mixed
 */
function field(array $args, string $row_key, string $legacy_key, $default = '')
{
    $row = $args['acf_row'] ?? null;
    if (is_array($row) && array_key_exists($row_key, $row) && $row[$row_key] !== null) {
        $value = $row[$row_key];
        return is_string($value) ? trim($value) : ($value ?: '');
    }
    if (!function_exists('get_field')) {
        return $default;
    }
    $post_id = nera_component_context_post_id() ?: (get_queried_object_id() ?: get_the_ID());
    if (!$post_id || !metadata_exists('post', (int) $post_id, $legacy_key)) {
        return $default;
    }
    $value = get_field($legacy_key, (int) $post_id);
    return is_string($value) ? trim($value) : ($value ?: '');
}

function get_data(array $args = []): array
{
    $winner_name = (string) field($args, 'winner_name', 'last_winner_name', 'Sarah M.');

    return [
        'title'       => (string) field($args, 'title',       'hero_title',       __('Win Your Dream', 'nera-competitions')),
        'highlight'   => (string) field($args, 'highlight',   'hero_highlight',   __('Lifestyle.', 'nera-competitions')),
        'description' => (string) field($args, 'description', 'hero_description', __(
            "Experience the thrill of high-end giveaways with the UK's most exclusive prize competition platform. Because you deserve a chance to win.",
            'nera-competitions'
        )),
        'cta' => [
            'text' => (string) field($args, 'cta_text', 'hero_cta_text', __('View Active Giveaways', 'nera-competitions')),
            'url'  => nera_component_field($args, 'cta_url',  'hero_cta_url',  null) ?: get_permalink(wc_get_page_id('shop')),
        ],
        'secondary' => [
            'text' => (string) field($args, 'secondary_text', 'hero_secondary_text', __('Recent Winners', 'nera-competitions')),
            'url'  => nera_component_field($args, 'secondary_url',  'hero_secondary_url',  '#winners'),
        ],
        'image' => (function () use ($args): string {
            $raw = field($args, 'image', 'hero_image', null);
            if (is_array($raw)) {
                $url = $raw['sizes']['large'] ?? $raw['url'] ?? '';
                return $url ? esc_url($url) : '';
            }
            if (is_numeric($raw) && (int) $raw > 0) {
                $url = wp_get_attachment_image_url((int) $raw, 'large');
                return $url ? esc_url($url) : '';
            }
            if (is_string($raw) && $raw !== '') {
                return esc_url($raw);
            }
            return '';
        })(),
        'last_winner' => [
            'name'    => $winner_name,
            'initial' => $winner_name !== '' ? mb_substr($winner_name, 0, 1) : '',
            'prize'   => (string) field($args, 'winner_prize', 'last_winner_prize', 'Won This Prize'),
        ],
        'i18n' => [
            'badge'       => __('Premium Giveaways', 'nera-competitions'),
            'last_winner' => __('Last Winner:', 'nera-competitions'),
        ],
    ];
}
