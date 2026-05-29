<?php
/**
 * Nera Component System
 *
 * Scans Components/{Name}/index.php, provides nera_render_component() helper,
 * and registers renderComponent() as a Twig function via Timber.
 */

if (!defined('ABSPATH')) exit;

add_action('after_setup_theme', function () {
    $parent_dir = get_template_directory() . '/Components';
    if (!is_dir($parent_dir)) return;

    // Build the set of component names already loaded from the parent so we
    // can skip child index.php files that share a name (preventing a fatal
    // "Cannot redeclare" error on the shared namespace + get_data() function).
    $parent_names = [];
    foreach (glob($parent_dir . '/*/*/index.php') ?: [] as $component_index) {
        $parent_names[basename(dirname($component_index))] = true;
        require_once $component_index;
    }
    foreach (glob($parent_dir . '/*/*/fields.php') ?: [] as $component_fields) {
        require_once $component_fields;
    }

    // When a child theme is active, also load its component index.php files —
    // but only for names that do NOT already exist in the parent.
    $child_dir = get_stylesheet_directory() . '/Components';
    if (get_stylesheet_directory() !== get_template_directory() && is_dir($child_dir)) {
        foreach (glob($child_dir . '/*/*/index.php') ?: [] as $component_index) {
            $name = basename(dirname($component_index));
            if (!isset($parent_names[$name])) {
                require_once $component_index;
            }
            // Collision: parent already loaded this name — skip silently.
        }
    }
});

add_filter('timber/twig', function (\Twig\Environment $twig) {
    $twig->addFunction(new \Twig\TwigFunction('renderComponent', 'nera_render_component'));
    return $twig;
});

function nera_get_components_with_script(): array {
    $components_dir = get_template_directory() . '/Components';
    $map = [];
    foreach (glob($components_dir . '/*/*/script.js') ?: [] as $script_path) {
        $name = basename(dirname($script_path));
        $map[$name] = $name;
    }
    return $map;
}

function nera_render_component(string $name, array $args = []): void
{
    static $path_index = null;
    if ($path_index === null) {
        $path_index = [];

        // Parent components are the authoritative source.
        foreach (glob(get_template_directory() . '/Components/*/*', GLOB_ONLYDIR) ?: [] as $dir) {
            $path_index[basename($dir)] = $dir;
        }

        // When a child theme is active, register child-only component dirs.
        // For names already in the parent index the parent entry is kept (no
        // overwrite) — this avoids a fatal "Cannot redeclare" and lets Timber's
        // default child-first twig resolution handle view overrides naturally.
        if (get_stylesheet_directory() !== get_template_directory()) {
            foreach (glob(get_stylesheet_directory() . '/Components/*/*', GLOB_ONLYDIR) ?: [] as $dir) {
                $component_name = basename($dir);
                if (!isset($path_index[$component_name])) {
                    $path_index[$component_name] = $dir;
                }
                // Collision: parent already registered — skip silently.
            }
        }
    }

    if (!isset($path_index[$name])) return;

    $index    = $path_index[$name] . '/index.php';
    $template = 'Components/' . basename(dirname($path_index[$name])) . '/' . $name . '/template.twig';

    $data = [];
    if (file_exists($index)) {
        $fn = 'Nera\\Components\\' . $name . '\\get_data';
        if (function_exists($fn)) {
            $data = $fn($args);
        }
    }

    $data = array_merge($data, $args);

    // Allow child themes / plugins to filter a component's context without
    // overriding the twig template or get_data(). Per-component filter fires
    // first, then the global one. Both are pure pass-through when unhooked.
    $data = apply_filters("nera_component_data_{$name}", $data, $args);
    $data = apply_filters('nera_component_data', $data, $name, $args);

    if (!class_exists('\\Timber\\Timber')) {
        return;
    }

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
        if ($value === false || $value === 0 || $value === '0') {
            return $value;
        }
    }
    if (function_exists('get_field')) {
        $value = get_field($legacy_key);
        if ($value !== null && $value !== '') {
            return $value;
        }
        if ($value === false || $value === 0 || $value === '0') {
            $post_id = get_queried_object_id() ?: get_the_ID();
            if ($post_id && metadata_exists('post', (int) $post_id, $legacy_key)) {
                return $value;
            }
        }
    }
    return $default;
}

add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) return;

    $components_dir = get_template_directory() . '/Components';
    $layouts = [];

    foreach (glob($components_dir . '/*/*', GLOB_ONLYDIR) ?: [] as $dir) {
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
