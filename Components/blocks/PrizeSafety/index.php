<?php
namespace Nera\Components\PrizeSafety;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Free safety equipment included with a prize (display only).
 *
 * @param array $args {
 *   @type WC_Product|null $product Lottery product.
 * }
 * @return array{
 *   enabled: bool,        // required — false → render nothing
 *   title: string,        // required — heading ("Included free with this prize" when admin left it empty)
 *   description: string,  // required — may be ''
 *   items: list<array{icon:string,title:string,description:string}>, // required — icon is a Material Symbol name
 *   i18n: array<string,string> // required — free
 * }
 */
function get_data(array $args = []): array
{
    $product = $args['product'] ?? null;
    $config  = ($product && function_exists('nera_prize_safety_config'))
        ? nera_prize_safety_config((int) $product->get_id())
        : ['enabled' => false, 'title' => '', 'description' => '', 'items' => []];

    return [
        'enabled'     => (bool) $config['enabled'],
        'title'       => (string) $config['title'],
        'description' => (string) $config['description'],
        'items'       => (array) $config['items'],
        'i18n'        => [
            'free' => function_exists('nera_prize_addons_label') ? nera_prize_addons_label('safety_free') : __('Free', 'nera-competitions'),
        ],
    ];
}
