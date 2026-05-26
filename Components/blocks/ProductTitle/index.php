<?php
namespace Nera\Components\ProductTitle;

if (!defined('ABSPATH')) {
    exit;
}

function get_data(array $args = []): array
{
    return [
        'name'        => (string) ($args['name'] ?? ''),
        'is_sold_out' => (bool) ($args['is_sold_out'] ?? false),
    ];
}
