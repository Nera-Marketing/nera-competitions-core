<?php
namespace Nera\Components\Stats;

if (!defined('ABSPATH')) exit;

/**
 * @param array $args
 * @return array{
 *   total_winners: string, // required, default '150' — winners count for count-up target
 *   total_value: string,   // required, default '2' — prize value (£M) for count-up target
 *   secure_entry: string,  // required, default '100' — secure entry % for count-up target
 *   tp_score: string,      // required, default '4.8' — Trustpilot score display
 *   tp_reviews: string,    // required, default '1,250' — Trustpilot review count display
 * }
 */
function get_data(array $args = []): array
{
    return [
        'total_winners' => nera_component_field($args, 'winners',  'stat_winners', '150'),
        'total_value'   => nera_component_field($args, 'value',    'stat_value',   '2'),
        'secure_entry'  => nera_component_field($args, 'secure',   'stat_secure',  '100'),
        'tp_score'      => nera_component_field($args, 'tp_score', 'tp_score',     '4.8'),
        'tp_reviews'    => nera_component_field($args, 'tp_reviews','tp_reviews',   '1,250'),
    ];
}
