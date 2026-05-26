<?php
namespace Nera\Components\TicketPrice;

if (!defined('ABSPATH')) {
    exit;
}

function get_data(array $args = []): array
{
    $price = $args['price'] ?? 0;

    return [
        'price_html' => wc_price($price),
    ];
}
