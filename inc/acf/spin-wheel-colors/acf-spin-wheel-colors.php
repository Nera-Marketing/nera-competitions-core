<?php
/**
 * ACF — Spin To Win wheel colours.
 *
 * Adds a "Wheel Colours" sub-page under Theme Settings so the client can
 * recolour the Spin To Win wheel (slices, rim, bulbs, pointer, backing, text)
 * without a code change. Values are read by inc/spin-wheel-colors.php and
 * printed as `--stw-wheel-*` CSS variable overrides on `#nera-spin-root`,
 * the override contract already exposed by the nera-spin-to-win plugin
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
    // Diagram showing which field controls which part of the wheel — lives in the
    // parent theme so it always resolves via get_template_directory_uri(), no
    // matter which child theme is active, and ships with parent theme releases.
    $spin_wheel_colors_guide_url = get_template_directory_uri() . '/assets/images/spin-wheel-colours-guide.png';

    acf_add_local_field_group([
        'key'    => 'group_spin_wheel_colors',
        'title'  => 'Spin To Win — Wheel Colours',
        'fields' => [
            [
                'key'     => 'field_spin_wheel_colors_intro',
                'label'   => '',
                'name'    => '',
                'type'    => 'message',
                'message' => 'The Spin To Win wheel has 7 colourable parts: the two alternating slice colours, the outer ring, the light bulbs, the pointer, the backing behind the wheel, and the prize-name text on the wheel. Changes here apply to every Spin To Win wheel on the site — see them live on any "Spin To Win" competition page.'
                    . '<br><br><a href="' . esc_url($spin_wheel_colors_guide_url) . '" target="_blank" rel="noopener">'
                    . '<img src="' . esc_url($spin_wheel_colors_guide_url) . '" alt="Diagram showing which colour field controls which part of the Spin To Win wheel" style="max-width:640px;width:100%;height:auto;border-radius:12px;border:1px solid #e2e8f0;margin-top:4px;" />'
                    . '</a>'
                    . '<p style="margin-top:8px;font-size:12px;color:#6b7280;">Click the image to view it full size.</p>',
            ],
            [
                'key'           => 'field_spin_wheel_segment_a_color',
                'label'         => 'Wheel Slice Colour 1',
                'name'          => 'spin_wheel_segment_a_color',
                'type'          => 'color_picker',
                'instructions'  => "First of the two alternating colours used for the wheel's prize slices. Slices alternate between this colour and Slice Colour 2 going around the wheel.",
                'default_value' => '#c0172e',
                'wrapper'       => ['width' => '50'],
            ],
            [
                'key'           => 'field_spin_wheel_segment_b_color',
                'label'         => 'Wheel Slice Colour 2',
                'name'          => 'spin_wheel_segment_b_color',
                'type'          => 'color_picker',
                'instructions'  => 'Second of the two alternating colours used for the wheel\'s prize slices — alternates with Slice Colour 1.',
                'default_value' => '#e8950a',
                'wrapper'       => ['width' => '50'],
            ],
            [
                'key'           => 'field_spin_wheel_rim_color',
                'label'         => 'Wheel Outer Ring',
                'name'          => 'spin_wheel_rim_color',
                'type'          => 'color_picker',
                'instructions'  => "Colour of the decorative ring/border around the outside edge of the wheel, including the light-bulb glow around it.",
                'default_value' => '#f59e0b',
                'wrapper'       => ['width' => '50'],
            ],
            [
                'key'           => 'field_spin_wheel_bulb_color',
                'label'         => 'Light Bulbs',
                'name'          => 'spin_wheel_bulb_color',
                'type'          => 'color_picker',
                'instructions'  => "Colour of the small light bulbs dotted around the wheel's rim, and the small cap in the centre of the wheel.",
                'default_value' => '#fbbf24',
                'wrapper'       => ['width' => '50'],
            ],
            [
                'key'           => 'field_spin_wheel_pointer_color',
                'label'         => 'Pointer / Arrow',
                'name'          => 'spin_wheel_pointer_color',
                'type'          => 'color_picker',
                'instructions'  => 'Colour of the pointer (arrow) at the top of the wheel that indicates the winning slice.',
                'default_value' => '#dc2626',
                'wrapper'       => ['width' => '50'],
            ],
            [
                'key'           => 'field_spin_wheel_base_color',
                'label'         => 'Wheel Backing',
                'name'          => 'spin_wheel_base_color',
                'type'          => 'color_picker',
                'instructions'  => 'Deep background colour that sits behind the wheel graphic, visible in the gaps and shadow behind it.',
                'default_value' => '#7b0d1e',
                'wrapper'       => ['width' => '50'],
            ],
            [
                'key'           => 'field_spin_wheel_label_color',
                'label'         => 'Wheel Text Colour',
                'name'          => 'spin_wheel_label_color',
                'type'          => 'color_picker',
                'instructions'  => 'Colour of the prize-name text printed on each wheel slice. Keep it readable against the slice colours chosen above — white/light works best on dark slices, dark works best on light slices.',
                'default_value' => '#ffffff',
                'wrapper'       => ['width' => '50'],
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
 * page. Purely a client-side convenience — fills the 7 colour pickers with
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
        'field_spin_wheel_segment_a_color' => '#c0172e',
        'field_spin_wheel_segment_b_color' => '#e8950a',
        'field_spin_wheel_rim_color'       => '#f59e0b',
        'field_spin_wheel_bulb_color'      => '#fbbf24',
        'field_spin_wheel_pointer_color'   => '#dc2626',
        'field_spin_wheel_base_color'      => '#7b0d1e',
        'field_spin_wheel_label_color'     => '#ffffff',
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
            $box.prepend($('<p style="margin:16px 0 20px 15px;"></p>').append($button));
        });
    })(jQuery);
    </script>
    <?php
}
add_action('admin_footer', 'nera_spin_wheel_colors_reset_button');

/**
 * Print a floating "Live Preview" wheel on the Wheel Colours options page.
 *
 * A small schematic SVG wheel (slices, ring, bulbs, pointer, backing, text)
 * pinned to the bottom-right of the viewport. Starts painted with the
 * client's currently saved colours, then repaints live as they drag any of
 * the 7 colour pickers — before they click "Update".
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
        'spin_wheel_segment_a_color' => ['field_spin_wheel_segment_a_color', '--sw-a', '#c0172e'],
        'spin_wheel_segment_b_color' => ['field_spin_wheel_segment_b_color', '--sw-b', '#e8950a'],
        'spin_wheel_rim_color'       => ['field_spin_wheel_rim_color', '--sw-ring', '#f59e0b'],
        'spin_wheel_bulb_color'      => ['field_spin_wheel_bulb_color', '--sw-bulb', '#fbbf24'],
        'spin_wheel_pointer_color'   => ['field_spin_wheel_pointer_color', '--sw-pointer', '#dc2626'],
        'spin_wheel_base_color'      => ['field_spin_wheel_base_color', '--sw-base', '#7b0d1e'],
        'spin_wheel_label_color'     => ['field_spin_wheel_label_color', '--sw-label', '#ffffff'],
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
    #nera-spin-preview-card {
        position: fixed;
        right: 20px;
        bottom: 20px;
        z-index: 100000;
        width: 210px;
        background: #fff;
        border: 1px solid #c3c4c7;
        border-radius: 8px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.18);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }
    #nera-spin-preview-card .nera-spin-preview-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 12px;
        border-bottom: 1px solid #e2e8f0;
        font-size: 12px;
        font-weight: 600;
        color: #1d2327;
    }
    #nera-spin-preview-card .nera-spin-preview-toggle {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 16px;
        line-height: 1;
        color: #646970;
        padding: 0 2px;
    }
    #nera-spin-preview-card .nera-spin-preview-body {
        padding: 14px;
        text-align: center;
    }
    #nera-spin-preview-card.is-collapsed .nera-spin-preview-body {
        display: none;
    }
    @media (max-width: 782px) {
        #nera-spin-preview-card {
            width: 150px;
        }
    }
    </style>

    <div id="nera-spin-preview-card" role="complementary" aria-label="Live wheel colour preview">
        <div class="nera-spin-preview-head">
            <span>Live Preview</span>
            <button type="button" class="nera-spin-preview-toggle" aria-label="Toggle live preview">&#8722;</button>
        </div>
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
                <g fill="var(--sw-label)" font-family="sans-serif" font-size="6.5" font-weight="700" text-anchor="middle">
                    <text x="82.00" y="50.00" transform="rotate(0 82.00 50.00)">PRIZE</text>
                    <text x="66.00" y="77.71" transform="rotate(60 66.00 77.71)">PRIZE</text>
                    <text x="34.00" y="77.71" transform="rotate(-60 34.00 77.71)">PRIZE</text>
                    <text x="18.00" y="50.00" transform="rotate(0 18.00 50.00)">PRIZE</text>
                    <text x="34.00" y="22.29" transform="rotate(60 34.00 22.29)">PRIZE</text>
                    <text x="66.00" y="22.29" transform="rotate(-60 66.00 22.29)">PRIZE</text>
                </g>
                <circle cx="96.00" cy="50.00" r="2.6" fill="var(--sw-bulb)" />
                <circle cx="89.84" cy="73.00" r="2.6" fill="var(--sw-bulb)" />
                <circle cx="73.00" cy="89.84" r="2.6" fill="var(--sw-bulb)" />
                <circle cx="50.00" cy="96.00" r="2.6" fill="var(--sw-bulb)" />
                <circle cx="27.00" cy="89.84" r="2.6" fill="var(--sw-bulb)" />
                <circle cx="10.16" cy="73.00" r="2.6" fill="var(--sw-bulb)" />
                <circle cx="4.00" cy="50.00" r="2.6" fill="var(--sw-bulb)" />
                <circle cx="10.16" cy="27.00" r="2.6" fill="var(--sw-bulb)" />
                <circle cx="27.00" cy="10.16" r="2.6" fill="var(--sw-bulb)" />
                <circle cx="50.00" cy="4.00" r="2.6" fill="var(--sw-bulb)" />
                <circle cx="73.00" cy="10.16" r="2.6" fill="var(--sw-bulb)" />
                <circle cx="89.84" cy="27.00" r="2.6" fill="var(--sw-bulb)" />
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
            var $card = $('#nera-spin-preview-card');
            $card.find('.nera-spin-preview-toggle').on('click', function () {
                $card.toggleClass('is-collapsed');
                $(this).html($card.hasClass('is-collapsed') ? '&#43;' : '&#8722;');
            });
        });
    })(jQuery);
    </script>
    <?php
}
add_action('admin_footer', 'nera_spin_wheel_colors_live_preview');
