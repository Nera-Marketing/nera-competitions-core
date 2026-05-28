<?php
namespace Nera\Components\QuantitySelector;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param array $args
 * @return array{
 *   min: int,              // required, default 1 — minimum quantity for the number input
 *   max: int,              // required, default 1 — maximum quantity for the number input
 *   quick_add: list<int>,  // required, default [5,10,20] — amounts rendered as quick-add buttons
 *   default: int,          // required, default 1 — initial value of the quantity input
 * }
 */
function get_data(array $args = []): array
{
    return [
        'min'       => (int) ($args['min'] ?? 1),
        'max'       => (int) ($args['max'] ?? 1),
        'quick_add' => $args['quick_add'] ?? [5, 10, 20],
        'default'   => (int) ($args['default'] ?? 1),
    ];
}
