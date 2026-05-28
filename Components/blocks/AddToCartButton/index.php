<?php
namespace Nera\Components\AddToCartButton;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param array $args
 * @return array{
 *   product_id: int,        // required, default 0 — WooCommerce product ID for the add-to-cart value attr
 *   is_expired: bool,       // required, default false — disables button and swaps to ended label
 *   is_manual_ticket: bool, // required, default false — reserved flag (not consumed by twig)
 *   label_active: string,   // required, default 'Enter Now' — CTA text when competition is live
 *   label_ended: string,    // required, default 'Competition Ended' — CTA text when expired
 * }
 */
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
