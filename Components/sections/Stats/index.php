<?php
namespace Nera\Components\Stats;

if (!defined('ABSPATH')) exit;

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
