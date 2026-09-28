<?php
namespace Nera\Components\PrizeAddOns;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Optional paid add-ons for a prize, shown above the entry form.
 *
 * Options already in the cart are pre-ticked. Options the signed-in customer
 * already paid for are shown as "Purchased" and cannot be ticked (option A).
 * Totals here are for display only; the cart prices every line on the server.
 *
 * @param array $args {
 *   @type WC_Product|null $product Lottery product.
 * }
 * @return array{
 *   enabled: bool,          // required — false → render nothing
 *   product_id: int,        // required — draw ID, read by the purchase card on submit
 *   dom_id: string,         // required — unique id prefix for labels / aria-controls
 *   title: string,          // required — may be ''
 *   description: string,    // required — may be ''
 *   options: list<array{id:string,title:string,description:string,price:float,price_html:string,purchased:bool,selected:bool}>, // required
 *   option_count: int,      // required
 *   purchased_count: int,   // required — options bought in earlier paid orders
 *   available_count: int,   // required — options that can still be ticked
 *   bundle: array{available:bool,offer_html:string}, // required — offer_html is escaped HTML
 *   notice: string,         // required — '' or the "already bought" note
 *   initial: array{subtotal_text:string,total_text:string,is_full_bundle:bool}, // required — server-rendered totals before Alpine boots
 *   config_json: string,    // required — JSON for the neraPrizeAddons Alpine component
 *   i18n: array<string,string> // required — eyebrow, total, non_refundable, purchased, select_all, hide
 * }
 */
function get_data(array $args = []): array
{
    $product = $args['product'] ?? null;
    $empty   = [
        'enabled'         => false,
        'product_id'      => 0,
        'dom_id'          => '',
        'title'           => '',
        'description'     => '',
        'options'         => [],
        'option_count'    => 0,
        'purchased_count' => 0,
        'available_count' => 0,
        'bundle'          => ['available' => false, 'offer_html' => ''],
        'notice'          => '',
        'initial'         => ['subtotal_text' => '', 'total_text' => '', 'is_full_bundle' => false],
        'config_json'     => '{}',
        'i18n'            => [],
    ];

    if (!$product || !is_object($product) || !function_exists('nera_prize_addons_config')) {
        return $empty;
    }

    $product_id = (int) $product->get_id();
    $config     = nera_prize_addons_config($product_id);
    if (!$config['enabled']) {
        return $empty;
    }

    $purchased = array_fill_keys(nera_prize_addons_current_user_purchased($product_id), true);
    $line      = nera_prize_addons_find_cart_line($product_id);
    $in_cart   = $line ? array_fill_keys((array) ($line[1][NERA_PRIZE_ADDON_CART_KEY]['option_ids'] ?? []), true) : [];

    $options         = [];
    $js_options      = [];
    $selected        = [];
    $purchased_count = 0;
    foreach ($config['options'] as $id => $option) {
        $is_purchased = isset($purchased[$id]);
        $is_selected  = !$is_purchased && isset($in_cart[$id]);
        if ($is_purchased) {
            $purchased_count++;
        } else {
            $js_options[] = ['id' => $id, 'title' => $option['title'], 'price' => $option['price']];
        }
        if ($is_selected) {
            $selected[] = $id;
        }
        $options[] = [
            'id'          => $id,
            'title'       => $option['title'],
            'description' => $option['description'],
            'price'       => $option['price'],
            'price_html'  => wc_price($option['price']),
            'purchased'   => $is_purchased,
            'selected'    => $is_selected,
        ];
    }

    $option_count     = count($options);
    $available_count  = $option_count - $purchased_count;
    $bundle_available = null !== $config['bundle_price'] && 0 === $purchased_count;

    $offer_html = '';
    if ($bundle_available) {
        $offer_html = sprintf(
            esc_html(nera_prize_addons_label('bundle_offer')),
            $option_count,
            '<strong>' . esc_html(nera_prize_addons_money_text((float) $config['bundle_price'])) . '</strong>',
            esc_html(nera_prize_addons_money_text($config['total'] - (float) $config['bundle_price']))
        );
    }

    $notice = '';
    if ($purchased_count > 0) {
        $notice = 0 === $available_count
            ? nera_prize_addons_label('all_purchased')
            : sprintf(nera_prize_addons_label('partial'), $purchased_count, $option_count);
    }

    $quote = nera_prize_addons_quote($product_id, $selected, array_keys($purchased));

    $i18n = [
        'eyebrow'        => nera_prize_addons_label('eyebrow'),
        'total'          => nera_prize_addons_label('total'),
        'non_refundable' => nera_prize_addons_label('non_refundable'),
        'purchased'      => nera_prize_addons_label('purchased'),
        'select_all'     => nera_prize_addons_label('select_all'),
        'hide'           => nera_prize_addons_label('hide'),
    ];

    $js_config = [
        'options'     => $js_options,
        'selected'    => $selected,
        'bundlePrice' => $bundle_available ? (float) $config['bundle_price'] : null,
        'currency'    => [
            'symbol'    => html_entity_decode(get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8'),
            'position'  => (string) get_option('woocommerce_currency_pos', 'left'),
            'decimals'  => (int) wc_get_price_decimals(),
            'thousand'  => wc_get_price_thousand_separator(),
            'decimal'   => wc_get_price_decimal_separator(),
        ],
        'i18n'        => [
            'hide'            => nera_prize_addons_label('hide'),
            'show'            => nera_prize_addons_label('show'),
            'selectAll'       => nera_prize_addons_label('select_all'),
            'clear'           => nera_prize_addons_label('clear'),
            'summarySelected' => nera_prize_addons_label('summary_selected'),
            'summaryNone'     => nera_prize_addons_label('summary_none'),
            'allPurchased'    => nera_prize_addons_label('all_purchased'),
        ],
    ];

    return [
        'enabled'         => true,
        'product_id'      => $product_id,
        'dom_id'          => 'ncs-prize-addons-' . $product_id,
        'title'           => $config['title'],
        'description'     => $config['description'],
        'options'         => $options,
        'option_count'    => $option_count,
        'purchased_count' => $purchased_count,
        'available_count' => $available_count,
        'bundle'          => ['available' => $bundle_available, 'offer_html' => $offer_html],
        'notice'          => $notice,
        'initial'         => [
            'subtotal_text'  => nera_prize_addons_money_text($quote['subtotal']),
            'total_text'     => nera_prize_addons_money_text($quote['total']),
            'is_full_bundle' => $quote['full_bundle'],
        ],
        'config_json'     => (string) wp_json_encode($js_config),
        'i18n'            => $i18n,
    ];
}
