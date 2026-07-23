<?php
if (!defined('ABSPATH')) exit;
// Shim: forward to the unified CompetitionCard Twig component.
if (function_exists('nera_render_component')) {
    $args      = isset($args) && is_array($args) ? $args : [];
    $cta_mode  = (isset($args['cta_mode']) && $args['cta_mode'] === 'ajax') ? 'ajax' : 'link';
    nera_render_component('CompetitionCard', [
        'product'         => $args['product'] ?? null,
        'button_variant'  => 'buy_tickets',
        'button_mode'     => $cta_mode,
        'highlight_badge' => $args['highlight_badge'] ?? '',
    ]);
}
