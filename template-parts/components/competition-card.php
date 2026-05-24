<?php
if (!defined('ABSPATH')) exit;
// Backward-compat shim: forward get_template_part() callers to the Twig component.
$args = isset($args) && is_array($args) ? $args : [];
if (function_exists('nera_render_component')) {
    nera_render_component('CompetitionCard', $args);
}
