<?php
/**
 * Spin To Win wheel colours — runtime wiring (hooks; side-effects on include).
 *
 * Emits the client-editable wheel colours (see
 * inc/acf/spin-wheel-colors/acf-spin-wheel-colors.php) as `--stw-wheel-*`
 * CSS variable overrides on `#nera-spin-root`, the override contract already
 * exposed by the nera-spin-to-win plugin
 * (wp-content/plugins/nera-spin-to-win/src/spin-to-win.css).
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
    exit();
}

/**
 * Print `--stw-wheel-*` CSS variable overrides on `#nera-spin-root`, sourced
 * from the "Wheel Colours" theme settings sub-page. Only prints on the Spin
 * To Win route, where `#nera-spin-root` exists.
 */
function nera_print_spin_wheel_color_vars()
{
    if (!function_exists('get_field')) {
        return;
    }

    if (absint(get_query_var('nera_spin_product')) < 1) {
        return;
    }

    $map = [
        'spin_wheel_segment_a_color' => '--stw-wheel-segment-a',
        'spin_wheel_segment_b_color' => '--stw-wheel-segment-b',
        'spin_wheel_rim_color'       => '--stw-wheel-rim',
        'spin_wheel_bulb_color'      => '--stw-wheel-bulb',
        'spin_wheel_pointer_color'   => '--stw-wheel-pointer',
        'spin_wheel_base_color'      => '--stw-wheel-base',
        'spin_wheel_label_color'     => '--stw-wheel-label',
    ];

    $css = '';
    foreach ($map as $field_name => $css_var) {
        $color = sanitize_hex_color((string) get_field($field_name, 'option'));
        if ($color) {
            $css .= $css_var . ':' . $color . ';';
        }
    }

    if ($css === '') {
        return;
    }

    // $css is built entirely from sanitize_hex_color()-validated values above.
    echo '<style id="nera-spin-wheel-color-vars">#nera-spin-root{' . $css . '}</style>' . "\n";
}
add_action('wp_head', 'nera_print_spin_wheel_color_vars', 20);
