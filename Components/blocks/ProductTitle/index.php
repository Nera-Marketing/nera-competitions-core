<?php
namespace Nera\Components\ProductTitle;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param array $args
 * @return array{
 *   name: string,        // required, default '' — product/competition name used in the page heading
 *   is_sold_out: bool,   // required, default false — shows "Sold Out" badge when true
 * }
 */
function get_data(array $args = []): array
{
    return [
        'name'        => (string) ($args['name'] ?? ''),
        'is_sold_out' => (bool) ($args['is_sold_out'] ?? false),
    ];
}
