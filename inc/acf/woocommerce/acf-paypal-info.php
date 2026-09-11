<?php
/**
 * ACF Field Group — PayPal Info
 *
 * Adds a "PayPal Info" tab to the WooCommerce options page
 * (Theme Settings → WooCommerce, options_page === 'acf-options-woocommerce').
 *
 * Controls the customer-facing copy for the PayPal gateway at checkout — the
 * "Pay via PayPal." line that appears under the PayPal radio once it is
 * selected. Leaving the field empty keeps whatever PayPal Payments itself is
 * configured to say, so the tab adds an override rather than a second source
 * of truth.
 *
 * Field name prefix: paypal_*
 * Read via: get_field('paypal_*', 'option') — see nera_ppcp_gateway_description()
 * in inc/paypal-payments.php, which is the only place that should read it.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
    exit();
}

if (!function_exists('acf_add_local_field_group')) {
    return;
}

/*
 * Only expose the tab when PayPal is actually in play, so the WooCommerce
 * options page does not offer copy for a gateway no customer can see.
 *
 * Two conditions, because either one alone is misleading: the constant is
 * defined at plugin load and tells us the gateway class exists at all, while
 * the gateway settings row carries the Enable/Disable switch from
 * WooCommerce → Settings → Payments. A deactivated plugin leaves its settings
 * row behind, and an installed-but-disabled gateway renders nothing.
 */
if (!defined('PPCP_PAYPAL_BN_CODE')
    && !class_exists('WooCommerce\PayPalCommerce\WcGateway\Gateway\PayPalGateway')) {
    return;
}

// Option key is WC_Payment_Gateway::get_option_key() for PayPalGateway::ID.
$nera_ppcp_settings = get_option('woocommerce_ppcp-gateway_settings', []);

if (!is_array($nera_ppcp_settings) || 'yes' !== ($nera_ppcp_settings['enabled'] ?? 'no')) {
    return;
}

acf_add_local_field_group([
    'key' => 'group_nera_paypal_info',
    'title' => 'PayPal Info',

    'fields' => [

        // ── Tab: PayPal Info ────────────────────────────────────────────────
        [
            'key' => 'field_paypal_info_tab',
            'label' => 'PayPal Info',
            'name' => '',
            'type' => 'tab',
            'placement' => 'top',
            'endpoint' => 0,
        ],

        // ── Description (the "Pay via PayPal." line) ────────────────────────
        [
            'key' => 'field_paypal_gateway_description',
            'label' => 'PayPal Description',
            'name' => 'paypal_gateway_description',
            'type' => 'textarea',
            'instructions' =>
                'The message shown under the PayPal payment method at checkout — by default "Pay via PayPal.". Leave empty to keep the text set in WooCommerce → Settings → Payments → PayPal. Leave a blank line between paragraphs.',
            'required' => 0,
            'default_value' => '',
            'placeholder' => 'Pay via PayPal.',
            'new_lines' => '',
            'rows' => 4,
            'wrapper' => ['width' => '100'],
        ],

    ],

    'location' => [
        [
            [
                'param' => 'options_page',
                'operator' => '==',
                'value' => 'acf-options-woocommerce',
            ],
        ],
    ],

    'menu_order' => 6,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'active' => true,
]);
