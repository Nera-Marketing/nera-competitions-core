<?php
namespace Nera\Components\AddToCartButton;

if (!defined('ABSPATH')) {
    exit;
}

function get_data(array $args = []): array
{
    return [
        'product_id'       => (int) ($args['product_id'] ?? 0),
        'is_expired'       => (bool) ($args['is_expired'] ?? false),
        'is_manual_ticket' => (bool) ($args['is_manual_ticket'] ?? false),
        'label_active'     => (string) ($args['label_active'] ?? __('Enter Now', 'nera-competitions')),
        'label_ended'      => (string) ($args['label_ended'] ?? __('Competition Ended', 'nera-competitions')),
    ];
}
