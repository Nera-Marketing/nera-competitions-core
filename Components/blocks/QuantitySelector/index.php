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
 *   layout: string,        // required, default buttons — buttons|slider
 *   discount_markers: list<array{qty: int, label_html: string, percent: float}>, // optional — LFW range-slider pack tags
 *   i18n: array<string,string> // required — localised labels for aria/text: tickets, decrease_quantity, increase_quantity, number_of_tickets, edit_quantity
 * }
 */
function get_data(array $args = []): array
{
    $layout = $args['layout'] ?? 'buttons';
    if (!in_array($layout, ['buttons', 'slider'], true)) {
        $layout = 'buttons';
    }

    $markers = [];
    if ($layout === 'slider' && !empty($args['discount_markers']) && is_array($args['discount_markers'])) {
        foreach ($args['discount_markers'] as $marker) {
            if (!is_array($marker)) {
                continue;
            }
            $qty = (int) ($marker['qty'] ?? 0);
            if ($qty < 1) {
                continue;
            }
            $markers[] = [
                'qty'        => $qty,
                'label_html' => (string) ($marker['label_html'] ?? ''),
                'percent'    => (float) ($marker['percent'] ?? 0),
            ];
        }
    }

    return [
        'min'               => (int) ($args['min'] ?? 1),
        'max'               => (int) ($args['max'] ?? 1),
        'quick_add'         => $args['quick_add'] ?? [5, 10, 20],
        'default'           => (int) ($args['default'] ?? 1),
        'layout'            => $layout,
        'discount_markers'  => $markers,
        'i18n'              => [
            'tickets'           => __('tickets', 'nera-competitions'),
            'decrease_quantity' => __('Decrease quantity', 'nera-competitions'),
            'increase_quantity' => __('Increase quantity', 'nera-competitions'),
            'number_of_tickets' => __('Number of tickets', 'nera-competitions'),
            'edit_quantity'     => __('Edit quantity', 'nera-competitions'),
        ],
    ];
}
