<?php
/**
 * ACF Field Group — CashFlow Info
 *
 * Adds a "CashFlow Info" tab to the WooCommerce options page
 * (Theme Settings → WooCommerce, options_page === 'acf-options-woocommerce').
 *
 * Controls the copy for the CashFlows "Cards" payment method at checkout:
 *   - Use custom info (switch) → when ON, the title + description below are used;
 *                                when OFF, the gateway's own CashFlows defaults show.
 *   - Title                    → the bigger heading / radio label.
 *   - Description              → the customer message shown beneath the heading.
 *
 * Field name prefix: cashflow_*
 * Read via: get_field('cashflow_*', 'option')
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
    exit();
}

if (!function_exists('acf_add_local_field_group')) {
    return;
}

/**
 * Only expose the CashFlow Info tab when the CashFlows payment plugin is active.
 * The plugin defines ICCF_VERSION on load and registers the cashflows_card
 * gateway class when WooCommerce is active.
 */
if (!defined('ICCF_VERSION') && !class_exists('cashflows_card')) {
    return;
}

acf_add_local_field_group([
    'key' => 'group_nera_cashflow_info',
    'title' => 'CashFlow Info',

    'fields' => [

        // ── Tab: CashFlow Info ──────────────────────────────────────────────
        [
            'key' => 'field_cashflow_info_tab',
            'label' => 'CashFlow Info',
            'name' => '',
            'type' => 'tab',
            'placement' => 'top',
            'endpoint' => 0,
        ],

        // ── Use custom info (yes/no switch) ─────────────────────────────────
        [
            'key' => 'field_cashflow_custom_info',
            'label' => 'Use custom info',
            'name' => 'cashflow_custom_info',
            'type' => 'true_false',
            'instructions' =>
                'When ON, the Title and Description below are shown for the CashFlows card payment method at checkout. When OFF, the gateway\'s own CashFlows default copy is used.',
            'ui' => 1,
            'ui_on_text' => 'Yes',
            'ui_off_text' => 'No',
            'default_value' => 1,
            'wrapper' => ['width' => '100'],
        ],

        // ── Title (bigger heading) ──────────────────────────────────────────
        [
            'key' => 'field_cashflow_title',
            'label' => 'Title',
            'name' => 'cashflow_title',
            'type' => 'text',
            'instructions' =>
                'The bigger heading shown for the CashFlows card payment method at checkout.',
            'required' => 0,
            'default_value' => 'Pay with Apple / Google Pay ( Debit & Credit )',
            'placeholder' => 'Pay with Apple / Google Pay ( Debit & Credit )',
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_cashflow_custom_info',
                        'operator' => '==',
                        'value' => '1',
                    ],
                ],
            ],
            'wrapper' => ['width' => '100'],
        ],

        // ── Description (customer message) ──────────────────────────────────
        [
            'key' => 'field_cashflow_description',
            'label' => 'Description',
            'name' => 'cashflow_description',
            'type' => 'textarea',
            'instructions' =>
                'The message shown beneath the heading when the card payment method is selected. Leave a blank line between paragraphs.',
            'required' => 0,
            'default_value' =>
                "You will taken to a third party checkout page, please do not refresh. You will be bought back to the website when payment is processed and tickets will be allocated.\n\nThe bigger heading will be Pay with Apple / Google Pay ( Debit & Credit )",
            'new_lines' => '',
            'rows' => 5,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_cashflow_custom_info',
                        'operator' => '==',
                        'value' => '1',
                    ],
                ],
            ],
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

    'menu_order' => 5,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'active' => true,
]);
