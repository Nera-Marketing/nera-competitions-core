<?php
/**
 * Nera Component System
 *
 * Scans Components/{Name}/index.php, provides nera_render_component() helper,
 * and registers renderComponent() as a Twig function via Timber.
 */

if (!defined('ABSPATH')) exit;

add_action('after_setup_theme', function () {
    $components_dir = get_template_directory() . '/Components';
    if (!is_dir($components_dir)) return;

    foreach (glob($components_dir . '/*/index.php') ?: [] as $component_index) {
        require_once $component_index;
    }
});

add_filter('timber/twig', function (\Twig\Environment $twig) {
    $twig->addFunction(new \Twig\TwigFunction('renderComponent', 'nera_render_component'));
    return $twig;
});

function nera_get_components_with_script(): array {
    $components_dir = get_template_directory() . '/Components';
    $map = [];
    foreach (glob($components_dir . '/*/script.js') ?: [] as $script_path) {
        $name = basename(dirname($script_path));
        $map[$name] = $name;
    }
    return $map;
}

function nera_render_component(string $name, array $args = []): void
{
    $index = get_template_directory() . '/Components/' . $name . '/index.php';
    $template = 'Components/' . $name . '/template.twig'; // Timber resolves from Timber::$dirname

    $data = [];
    if (file_exists($index)) {
        $fn = 'Nera\\Components\\' . $name . '\\get_data';
        if (function_exists($fn)) {
            $data = $fn($args);
        }
    }

    $data = array_merge($data, $args);

    \Timber\Timber::render($template, $data);
}
