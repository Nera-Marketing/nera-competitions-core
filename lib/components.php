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
    foreach (glob($components_dir . '/*/fields.php') ?: [] as $component_fields) {
        require_once $component_fields;
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

function nera_render_page_components(): bool {
    $rows = function_exists('get_field') ? get_field('page_components') : null;
    if (!is_array($rows) || empty($rows)) {
        return false;
    }

    $rendered_any = false;
    foreach ($rows as $row) {
        $layout = $row['acf_fc_layout'] ?? '';
        if (!$layout) continue;

        nera_render_component($layout, ['acf_row' => $row]);
        $rendered_any = true;
    }

    return $rendered_any;
}

function nera_component_field(array $args, string $row_key, string $legacy_key, $default = null) {
    $row = $args['acf_row'] ?? null;
    if (is_array($row) && array_key_exists($row_key, $row)) {
        $value = $row[$row_key];
        if ($value !== '' && $value !== null) {
            return $value;
        }
    }
    if (function_exists('get_field')) {
        $value = get_field($legacy_key);
        if ($value !== false && $value !== null && $value !== '') {
            return $value;
        }
    }
    return $default;
}

add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) return;

    $components_dir = get_template_directory() . '/Components';
    $layouts = [];

    foreach (glob($components_dir . '/*', GLOB_ONLYDIR) ?: [] as $dir) {
        $name = basename($dir);

        if (file_exists($dir . '/.not-top-level')) continue;

        $fn = "Nera\\Components\\{$name}\\get_acf_layout";
        if (!function_exists($fn)) continue;

        $layout = $fn();
        if (is_array($layout) && !empty($layout['name'])) {
            $layouts[] = $layout;
        }
    }

    if (empty($layouts)) return;

    acf_add_local_field_group([
        'key'      => 'group_page_components',
        'title'    => __('Page Components', 'nera-competitions-standard'),
        'fields'   => [
            [
                'key'          => 'field_page_components',
                'label'        => __('Components', 'nera-competitions-standard'),
                'name'         => 'page_components',
                'type'         => 'flexible_content',
                'instructions' => __("Add components to compose this page. Leave empty to use the page template's default layout.", 'nera-competitions-standard'),
                'button_label' => __('Add Component', 'nera-competitions-standard'),
                'layouts'      => $layouts,
            ],
        ],
        'location' => [[
            ['param' => 'post_type', 'operator' => '==', 'value' => 'page'],
        ]],
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,
    ]);
});
