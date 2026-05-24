<?php
namespace Nera\Components\Stats;

if (!defined('ABSPATH')) exit;

function get_data(array $args = []): array
{
    return [
        'total_winners' => get_field('stat_winners') ?: '150',
        'total_value'   => get_field('stat_value')   ?: '2',
        'secure_entry'  => get_field('stat_secure')  ?: '100',
        'tp_score'      => get_field('tp_score')     ?: '4.8',
        'tp_reviews'    => get_field('tp_reviews')   ?: '1,250',
    ];
}
