<?php
namespace Nera\Components\TicketBundles;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * LFW Predefined Buttons (ticket packs) for the purchase card.
 *
 * @param array $args {
 *   @type WC_Product|null $product Lottery product (required for live data).
 * }
 * @return array{
 *   enabled: bool,
 *   with_quantity_selector: bool,
 *   heading: string,
 *   show_discount_tag: bool,
 *   base_price: float,
 *   buttons: list<array{
 *     id: int|string,
 *     quantity: int,
 *     per_ticket_amount: float,
 *     total_amount_html: string,
 *     label_html: string,
 *     badge_html: string
 *   }>,
 *   i18n: array<string,string>
 * }
 */
function get_data(array $args = []): array
{
    $product = $args['product'] ?? null;
    $empty   = [
        'enabled'                 => false,
        'with_quantity_selector'  => false,
        'heading'                 => '',
        'show_discount_tag'       => false,
        'base_price'              => 0.0,
        'buttons'                 => [],
        'i18n'                    => [
            'select_pack' => __('Please select a ticket pack', 'nera-competitions'),
        ],
    ];

    if (!$product || !is_object($product)) {
        return $empty;
    }

    if (!method_exists($product, 'is_predefined_button_enabled')
        || !method_exists($product, 'can_display_predefined_buttons')
        || !$product->is_predefined_button_enabled()
        || !$product->can_display_predefined_buttons()
    ) {
        return $empty;
    }

    $with_qty = method_exists($product, 'can_display_predefined_with_quantity_selector')
        && $product->can_display_predefined_with_quantity_selector();

    $show_tag = method_exists($product, 'can_display_predefined_buttons_discount_tag')
        && $product->can_display_predefined_buttons_discount_tag();

    $heading = function_exists('lty_get_predefined_buttons_heading')
        ? lty_get_predefined_buttons_heading()
        : __('Choose an option', 'nera-competitions');

    $base_price = (float) $product->get_price();
    $buttons    = [];
    $rules      = method_exists($product, 'get_predefined_buttons_rule')
        ? $product->get_predefined_buttons_rule()
        : [];

    if (is_array($rules)) {
        foreach ($rules as $button_id => $button_data) {
            if (method_exists($product, 'is_valid_to_display_predefined_button')
                && !$product->is_valid_to_display_predefined_button($button_id)
            ) {
                continue;
            }

            $qty = method_exists($product, 'get_predefined_buttons_ticket_quantity')
                ? (int) $product->get_predefined_buttons_ticket_quantity($button_id)
                : 0;

            if ($qty < 1) {
                continue;
            }

            $per_ticket = method_exists($product, 'get_predefined_buttons_per_ticket_amount')
                ? (float) $product->get_predefined_buttons_per_ticket_amount($button_id)
                : $base_price;

            $label_html = method_exists($product, 'get_predefined_button_label')
                ? $product->get_predefined_button_label($button_id, $qty)
                : sprintf(
                    /* translators: 1: ticket quantity 2: formatted price */
                    __('Buy %1$d ticket(s) for %2$s', 'nera-competitions'),
                    $qty,
                    wc_price($qty * $per_ticket)
                );

            $badge_html = '';
            if ($show_tag && method_exists($product, 'get_predefined_button_badge_label')) {
                $badge_html = (string) $product->get_predefined_button_badge_label($button_id, $qty);
            }

            $buttons[] = [
                'id'                 => $button_id,
                'quantity'           => $qty,
                'per_ticket_amount'  => $per_ticket,
                'total_amount_html'  => wc_price($qty * $per_ticket),
                'label_html'         => $label_html,
                'badge_html'         => $badge_html,
            ];
        }
    }

    if (empty($buttons)) {
        return $empty;
    }

    $alert = get_option(
        'lty_settings_predefined_buttons_alert_error_message',
        __('Please select an option', 'nera-competitions')
    );

    return [
        'enabled'                => true,
        'with_quantity_selector' => $with_qty,
        'heading'                => $heading,
        'show_discount_tag'      => $show_tag,
        'base_price'             => $base_price,
        'buttons'                => $buttons,
        'i18n'                   => [
            'select_pack' => is_string($alert) && $alert !== ''
                ? $alert
                : __('Please select a ticket pack', 'nera-competitions'),
        ],
    ];
}
