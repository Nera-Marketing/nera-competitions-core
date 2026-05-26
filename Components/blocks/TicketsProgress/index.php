<?php
namespace Nera\Components\TicketsProgress;

if (!defined('ABSPATH')) {
    exit;
}

function get_data(array $args = []): array
{
    $sold      = (int) ($args['sold'] ?? 0);
    $max       = (int) ($args['max'] ?? 0);
    $progress  = (int) ($args['progress'] ?? 0);
    $remaining = (int) ($args['remaining'] ?? 0);

    return [
        'sold'               => $sold,
        'max'                => $max,
        'progress'           => $progress,
        'remaining'          => $remaining,
        'is_low_stock'       => (bool) ($args['is_low_stock'] ?? false),
        'sold_formatted'     => number_format($sold),
        'max_formatted'      => number_format($max),
        'remaining_formatted' => number_format($remaining),
    ];
}
