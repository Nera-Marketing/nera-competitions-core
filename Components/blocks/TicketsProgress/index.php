<?php
namespace Nera\Components\TicketsProgress;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param array $args
 * @return array{
 *   sold: int,                // required, default 0 — raw sold ticket count (not consumed by twig directly)
 *   max: int,                 // required, default 0 — raw max ticket count (not consumed by twig directly)
 *   progress: int,            // required, default 0 — percentage 0-100 for the progress bar width
 *   remaining: int,           // required, default 0 — raw remaining count (not consumed by twig directly)
 *   is_low_stock: bool,       // required, default false — shows "Selling fast" warning row
 *   sold_formatted: string,   // required — number_format of sold; shown in "X / Y" label
 *   max_formatted: string,    // required — number_format of max; shown in "X / Y" label
 *   remaining_formatted: string, // required — number_format of remaining; shown in low-stock warning
 * }
 */
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
