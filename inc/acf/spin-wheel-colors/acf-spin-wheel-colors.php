<?php
/**
 * ACF — Spin To Win wheel colours.
 *
 * Adds a "Wheel Colours" sub-page under Theme Settings so the client can
 * recolour the Spin To Win wheel (slices, rim, bulbs + highlight layers,
 * pointer, backing, text) without a code change. Values are read by
 * inc/spin-wheel-colors.php and printed as `--stw-wheel-*` CSS variable
 * overrides on `#nera-spin-root`, the override contract already exposed by
 * the nera-spin-to-win plugin
 * (see wp-content/plugins/nera-spin-to-win/src/spin-to-win.css).
 *
 * Pure ACF registration (options page + field group) — no other hooks.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
    exit();
}

// Only show this settings page when the Spin To Win plugin is active.
if (!defined('NERA_STW_VERSION')) {
    return;
}

if (function_exists('acf_add_options_page')) {
    // Ensure the shared Theme Settings parent exists.
    if (!function_exists('acf_get_options_page') || !acf_get_options_page('theme-settings')) {
        acf_add_options_page([
            'page_title' => 'Theme Settings',
            'menu_title' => 'Theme Settings',
            'menu_slug'  => 'theme-settings',
            'capability' => 'edit_posts',
            'redirect'   => false,
        ]);
    }

    acf_add_options_sub_page([
        'page_title'  => 'Wheel Colours',
        'menu_title'  => 'Wheel Colours',
        'menu_slug'   => 'spin-wheel-colors',
        'parent_slug' => 'theme-settings',
    ]);
}

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group([
        'key'    => 'group_spin_wheel_colors',
        'title'  => 'Spin To Win — Wheel Colours',
        'fields' => [
            [
                'key'           => 'field_spin_wheel_segment_a_color',
                'label'         => 'Wheel Slice Colour 1',
                'name'          => 'spin_wheel_segment_a_color',
                'type'          => 'color_picker',
                'default_value' => '#c0172e',
            ],
            [
                'key'           => 'field_spin_wheel_segment_b_color',
                'label'         => 'Wheel Slice Colour 2',
                'name'          => 'spin_wheel_segment_b_color',
                'type'          => 'color_picker',
                'default_value' => '#e8950a',
            ],
            [
                'key'           => 'field_spin_wheel_rim_color',
                'label'         => 'Wheel Outer Ring',
                'name'          => 'spin_wheel_rim_color',
                'type'          => 'color_picker',
                'default_value' => '#f59e0b',
            ],
            [
                'key'           => 'field_spin_wheel_bulb_color',
                'label'         => 'Light Bulbs',
                'name'          => 'spin_wheel_bulb_color',
                'type'          => 'color_picker',
                'default_value' => '#fbbf24',
            ],
            [
                'key'           => 'field_spin_wheel_bulb_mid_color',
                'label'         => 'Bulb Mid Highlight',
                'name'          => 'spin_wheel_bulb_mid_color',
                'type'          => 'color_picker',
                'default_value' => '#fde68a',
            ],
            [
                'key'           => 'field_spin_wheel_bulb_hot_color',
                'label'         => 'Bulb Hot Spot',
                'name'          => 'spin_wheel_bulb_hot_color',
                'type'          => 'color_picker',
                'default_value' => '#fffde7',
            ],
            [
                'key'           => 'field_spin_wheel_bulb_specular_color',
                'label'         => 'Bulb Specular',
                'name'          => 'spin_wheel_bulb_specular_color',
                'type'          => 'color_picker',
                'default_value' => '#ffffff',
            ],
            [
                'key'           => 'field_spin_wheel_pointer_color',
                'label'         => 'Pointer / Arrow',
                'name'          => 'spin_wheel_pointer_color',
                'type'          => 'color_picker',
                'default_value' => '#dc2626',
            ],
            [
                'key'           => 'field_spin_wheel_base_color',
                'label'         => 'Wheel Backing',
                'name'          => 'spin_wheel_base_color',
                'type'          => 'color_picker',
                'default_value' => '#7b0d1e',
            ],
            [
                'key'           => 'field_spin_wheel_label_color',
                'label'         => 'Wheel Text Colour',
                'name'          => 'spin_wheel_label_color',
                'type'          => 'color_picker',
                'default_value' => '#ffffff',
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'options_page',
                    'operator' => '==',
                    'value'    => 'spin-wheel-colors',
                ],
            ],
        ],
        'menu_order'      => 0,
        'style'           => 'default',
        'label_placement' => 'top',
        'active'          => true,
    ]);
}

/**
 * Print a "Reset to Default (Red/Gold)" button on the Wheel Colours options
 * page. Purely a client-side convenience — fills the colour pickers with
 * the plugin's original red/gold defaults; the client still has to click
 * "Update" to save. Values must match the `default_value` entries above and
 * the plugin's own hardcoded defaults in spin-to-win.css.
 */
function nera_spin_wheel_colors_reset_button()
{
    if (empty($_GET['page']) || sanitize_text_field(wp_unslash($_GET['page'])) !== 'spin-wheel-colors') {
        return;
    }

    $defaults = [
        'field_spin_wheel_segment_a_color'      => '#c0172e',
        'field_spin_wheel_segment_b_color'      => '#e8950a',
        'field_spin_wheel_rim_color'            => '#f59e0b',
        'field_spin_wheel_bulb_color'           => '#fbbf24',
        'field_spin_wheel_bulb_mid_color'       => '#fde68a',
        'field_spin_wheel_bulb_hot_color'       => '#fffde7',
        'field_spin_wheel_bulb_specular_color'  => '#ffffff',
        'field_spin_wheel_pointer_color'        => '#dc2626',
        'field_spin_wheel_base_color'           => '#7b0d1e',
        'field_spin_wheel_label_color'          => '#ffffff',
    ];
    ?>
    <script>
    (function ($) {
        $(function () {
            var defaults = <?php echo wp_json_encode($defaults); ?>;
            var $box = $('#acf-group_spin_wheel_colors .inside').first();
            if (!$box.length) {
                return;
            }
            var $button = $('<button type="button" class="button">Reset to Default (Red/Gold)</button>');
            $button.on('click', function (e) {
                e.preventDefault();
                $.each(defaults, function (key, hex) {
                    var $input = $('input.wp-color-picker[name="acf[' + key + ']"]');
                    if ($input.length) {
                        $input.wpColorPicker('color', hex);
                    }
                });
            });
            $box.prepend($('<p style="margin:16px 0 20px 0;"></p>').append($button));
        });
    })(jQuery);
    </script>
    <?php
}
add_action('admin_footer', 'nera_spin_wheel_colors_reset_button');

/**
 * Print a "Live Preview" wheel beside the colour pickers on the Wheel
 * Colours options page.
 *
 * A schematic SVG wheel (slices, ring, bulbs, pointer, backing, text) is
 * nested inside the ACF postbox `.inside` in a two-column grid — fields on
 * the left, sticky preview on the right (~400px) — so pickers and wheel
 * share one frame. Starts painted with the client's currently saved
 * colours, then repaints live as they drag any of the colour pickers —
 * before they click "Update".
 *
 * IMPORTANT: ACF's own colour_picker field type already sets a `change`
 * option on the wpColorPicker instance (it syncs the picked colour into a
 * hidden input, which is what actually gets saved). We must not overwrite
 * that option — we read it first and chain our own callback after it, so
 * saving still works and the preview stays in sync.
 */
function nera_spin_wheel_colors_live_preview()
{
    if (empty($_GET['page']) || sanitize_text_field(wp_unslash($_GET['page'])) !== 'spin-wheel-colors') {
        return;
    }

    // Field name => [ACF field key, CSS variable, fallback default].
    $fields = [
        'spin_wheel_segment_a_color'     => ['field_spin_wheel_segment_a_color', '--sw-a', '#c0172e'],
        'spin_wheel_segment_b_color'     => ['field_spin_wheel_segment_b_color', '--sw-b', '#e8950a'],
        'spin_wheel_rim_color'           => ['field_spin_wheel_rim_color', '--sw-ring', '#f59e0b'],
        'spin_wheel_bulb_color'          => ['field_spin_wheel_bulb_color', '--sw-bulb', '#fbbf24'],
        'spin_wheel_bulb_mid_color'      => ['field_spin_wheel_bulb_mid_color', '--sw-bulb-mid', '#fde68a'],
        'spin_wheel_bulb_hot_color'      => ['field_spin_wheel_bulb_hot_color', '--sw-bulb-hot', '#fffde7'],
        'spin_wheel_bulb_specular_color' => ['field_spin_wheel_bulb_specular_color', '--sw-bulb-specular', '#ffffff'],
        'spin_wheel_pointer_color'       => ['field_spin_wheel_pointer_color', '--sw-pointer', '#dc2626'],
        'spin_wheel_base_color'          => ['field_spin_wheel_base_color', '--sw-base', '#7b0d1e'],
        'spin_wheel_label_color'         => ['field_spin_wheel_label_color', '--sw-label', '#ffffff'],
    ];

    $initial_style = '';
    $field_key_to_css_var = [];
    foreach ($fields as $field_name => $meta) {
        [$field_key, $css_var, $default] = $meta;
        $saved = function_exists('get_field') ? get_field($field_name, 'option') : '';
        $color = sanitize_hex_color((string) $saved);
        $initial_style .= $css_var . ':' . ($color ?: $default) . ';';
        $field_key_to_css_var[$field_key] = $css_var;
    }
    ?>
    <style>
    .nera-spin-colors-layout {
        display: grid;
        grid-template-columns: 4fr 6fr;
        gap: 20px;
        align-items: center;
    }
    .nera-spin-colors-fields {
        min-width: 0;
        padding: 8px 16px 16px;
    }
    #nera-spin-preview-card {
        position: sticky;
        top: 46px;
        width: 100%;
        min-width: 0;
    }
    #nera-spin-preview-card .nera-spin-preview-body {
        padding: 0;
        text-align: center;
    }
    #nera-spin-preview-svg {
        display: block;
        width: 100%;
        max-width: 560px;
        height: auto;
        margin: 0 auto;
    }
    @media (max-width: 1100px) {
        .nera-spin-colors-layout {
            grid-template-columns: 1fr;
        }
        #nera-spin-preview-card {
            position: static;
        }
    }
    </style>

    <div id="nera-spin-preview-card" role="complementary" aria-label="Live wheel colour preview">
        <div class="nera-spin-preview-body">
            <svg id="nera-spin-preview-svg" viewBox="0 0 100 100" width="100%" height="100%" style="<?php echo esc_attr($initial_style); ?>">
                <circle cx="50" cy="50" r="48" fill="var(--sw-base)" />
                <path d="M50,50 L89.84,27.00 A46,46 0 0,1 89.84,73.00 Z" fill="var(--sw-a)" />
                <path d="M50,50 L89.84,73.00 A46,46 0 0,1 50.00,96.00 Z" fill="var(--sw-b)" />
                <path d="M50,50 L50.00,96.00 A46,46 0 0,1 10.16,73.00 Z" fill="var(--sw-a)" />
                <path d="M50,50 L10.16,73.00 A46,46 0 0,1 10.16,27.00 Z" fill="var(--sw-b)" />
                <path d="M50,50 L10.16,27.00 A46,46 0 0,1 50.00,4.00 Z" fill="var(--sw-a)" />
                <path d="M50,50 L50.00,4.00 A46,46 0 0,1 89.84,27.00 Z" fill="var(--sw-b)" />
                <circle cx="50" cy="50" r="46" fill="none" stroke="var(--sw-ring)" stroke-width="1.5" />
                <!-- Sample prize labels — colour controlled by the "Wheel Text Colour" field. -->
                <g fill="var(--sw-label)" font-family="sans-serif" font-size="2.8" font-weight="700" text-anchor="middle">
                    <text x="82.00" y="50.00" transform="rotate(0 82.00 50.00)">Prize</text>
                    <text x="66.00" y="77.71" transform="rotate(60 66.00 77.71)">Prize</text>
                    <text x="34.00" y="77.71" transform="rotate(-60 34.00 77.71)">Prize</text>
                    <text x="18.00" y="50.00" transform="rotate(0 18.00 50.00)">Prize</text>
                    <text x="34.00" y="22.29" transform="rotate(60 34.00 22.29)">Prize</text>
                    <text x="66.00" y="22.29" transform="rotate(-60 66.00 22.29)">Prize</text>
                </g>
                <?php
                // Multi-layer rim bulbs — mirrors plugin WheelChrome.jsx layers
                // (rim halo + core + mid + hot + specular).
                $preview_bulbs = [
                    [96.00, 50.00],
                    [89.84, 73.00],
                    [73.00, 89.84],
                    [50.00, 96.00],
                    [27.00, 89.84],
                    [10.16, 73.00],
                    [4.00, 50.00],
                    [10.16, 27.00],
                    [27.00, 10.16],
                    [50.00, 4.00],
                    [73.00, 10.16],
                    [89.84, 27.00],
                ];
                foreach ($preview_bulbs as [$bx, $by]) :
                    ?>
                <g>
                    <circle cx="<?php echo esc_attr((string) $bx); ?>" cy="<?php echo esc_attr((string) $by); ?>" r="3.8" fill="var(--sw-ring)" opacity="0.22" />
                    <circle cx="<?php echo esc_attr((string) $bx); ?>" cy="<?php echo esc_attr((string) $by); ?>" r="2.6" fill="var(--sw-bulb)" opacity="0.55" />
                    <circle cx="<?php echo esc_attr((string) $bx); ?>" cy="<?php echo esc_attr((string) $by); ?>" r="1.5" fill="var(--sw-bulb-mid)" />
                    <circle cx="<?php echo esc_attr((string) $bx); ?>" cy="<?php echo esc_attr((string) $by); ?>" r="0.85" fill="var(--sw-bulb-hot)" opacity="0.9" />
                    <circle cx="<?php echo esc_attr((string) ($bx - 0.45)); ?>" cy="<?php echo esc_attr((string) ($by - 0.45)); ?>" r="0.3" fill="var(--sw-bulb-specular)" opacity="0.75" />
                </g>
                    <?php
                endforeach;
                ?>
                <circle cx="50" cy="50" r="11" fill="var(--sw-bulb)" stroke="#fff" stroke-width="1" />
                <path d="M45,2 L55,2 L50,14 Z" fill="var(--sw-pointer)" stroke="var(--sw-ring)" stroke-width="0.6" />
            </svg>
        </div>
    </div>

    <script>
    (function ($) {
        var fieldKeyToCssVar = <?php echo wp_json_encode($field_key_to_css_var); ?>;

        // ACF's colour_picker field type has `wait: "load"` — it initialises
        // wpColorPicker() later, and our inline script (printed on the
        // `admin_footer` action) can run before ACF's own core JS has even
        // defined `window.acf`. Hooking the same `color_picker_args` filter
        // ACF applies right before calling wpColorPicker() guarantees correct
        // timing per-field either way, and lets us chain after ACF's own
        // callback (which saves the value into the hidden input — must not
        // be overwritten). We just need to make sure `acf.addFilter` itself
        // is registered before ACF consumes it, hence the small poll below.
        function registerColorPickerFilter() {
            if (!window.acf || typeof acf.addFilter !== 'function') {
                return false;
            }
            acf.addFilter('color_picker_args', function (args, field) {
                var cssVar = fieldKeyToCssVar[field.get('key')];
                if (!cssVar) {
                    return args;
                }
                var acfChange = args.change;
                args.change = function (event, ui) {
                    if (typeof acfChange === 'function') {
                        acfChange.call(this, event, ui);
                    }
                    var svg = document.getElementById('nera-spin-preview-svg');
                    if (svg) {
                        svg.style.setProperty(cssVar, ui.color.toString());
                    }
                };
                return args;
            });
            return true;
        }

        if (!registerColorPickerFilter()) {
            var tries = 0;
            var poll = setInterval(function () {
                tries++;
                if (registerColorPickerFilter() || tries > 100) {
                    clearInterval(poll);
                }
            }, 20);
        }

        $(function () {
            // Nest the preview inside the ACF postbox so fields + wheel share
            // one frame. Reset button (prepended earlier in admin_footer)
            // ends up in the left column with the colour pickers.
            var $inside = $('#acf-group_spin_wheel_colors .inside').first();
            var $card = $('#nera-spin-preview-card');
            if ($inside.length && $card.length) {
                var $fields = $('<div class="nera-spin-colors-fields"></div>');
                $inside.children().appendTo($fields);
                var $layout = $('<div class="nera-spin-colors-layout"></div>');
                $layout.append($fields).append($card);
                $inside.append($layout);
            }
        });
    })(jQuery);
    </script>
    <?php
}
add_action('admin_footer', 'nera_spin_wheel_colors_live_preview');
