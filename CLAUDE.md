# Nera Competitions Standard Theme

A premium WordPress child theme for competition/giveaway websites, built on the Astra parent theme with TailwindCSS v4 and Vite.

## Quick Reference

| Category      | Details                           |
| ------------- | --------------------------------- |
| Theme Type    | Astra Child Theme                 |
| CSS Framework | TailwindCSS v4 (CSS-first config) |
| Build Tool    | Vite 6.x                          |
| PHP Version   | 7.4+                              |
| Key Plugins   | WooCommerce, WooCommerce Lottery  |
| Text Domain   | `nera-competitions`               |

## Project Structure

```
competitions-standard/
├── frontend/                   # Vite, Tailwind, theme JS/CSS, Vue entries
│   ├── package.json
│   ├── yarn.lock
│   ├── vite.config.js
│   ├── dist/                   # Vite build output (generated)
│   ├── assets/
│   │   ├── css/                # Editor CSS, archives, etc.
│   │   └── js/                 # JS modules (Alpine, bundled + enqueued)
│   ├── src/
│   │   ├── main.js             # Vite entry (imports CSS + assets/js)
│   │   └── main.css            # TailwindCSS entry with @theme config
│   ├── components/             # Vue SFCs
│   └── instant-wins-vue-init.js
├── inc/                        # PHP includes
│   ├── brand-presets.php       # Brand preset definitions
│   ├── customizer.php          # WordPress Customizer settings
│   ├── giveaway-custom.php     # Lottery plugin customizations
│   ├── menu-walkers.php        # Custom menu walker classes
│   └── woocommerce.php         # WooCommerce customizations
├── page-templates/
│   └── homepage-template.php   # Main homepage template
├── reference/
│   ├── docs/homepage.md        # Design specifications
│   └── images/                 # Reference screenshots
├── package.json                # Theme-root shim: `yarn build` → frontend/ build
├── template-parts/
│   ├── header.php              # Site header template
│   ├── footer.php              # Site footer template
│   ├── competition-card.php    # Competition product card
│   ├── countdown-timer.php     # Countdown timer component
│   ├── how-it-works.php        # How to enter section
│   ├── winner-showcase.php     # Winner display component
│   └── homepage/               # Homepage sections
│       ├── hero-section.php
│       ├── stats-section.php
│       ├── featured-competitions.php
│       ├── promo-banner.php
│       ├── testimonials-section.php
│       ├── winners-section.php
│       ├── quick-guide.php
│       ├── live-draw-section.php
│       ├── newsletter-section.php
│       ├── trust-section.php
│       └── faq-section.php
├── .claude/
│   ├── settings.json           # Claude Code plugin settings
│   └── commands/               # Custom slash commands
├── functions.php               # Theme bootstrap and asset loading
├── header.php                  # HTML head and body opening
├── footer.php                  # Footer and body closing
└── style.css                   # WordPress theme header
```

## Development Workflow

### Prerequisites

- Node.js 18+
- Yarn (package manager for `frontend/`)
- WordPress with Astra theme
- WooCommerce (optional but recommended)

### Development Commands

```bash
cd frontend

# Install dependencies
yarn install

# Start Vite dev server (HMR enabled)
yarn dev

# Build for production (or `yarn build` from theme root with package.json shim)
yarn build

# Preview production build
yarn preview
```

### Vite Dev Server

The dev server runs at `http://localhost:5173`. Enable dev mode in WordPress:

```php
// In wp-config.php
define('NERA_DEV_MODE', true);
```

When `NERA_DEV_MODE` is true and Vite server is running, assets load from the dev server with hot module replacement. Otherwise, assets load from `frontend/dist/`.

## CSS Architecture

### TailwindCSS v4 Configuration

All theme tokens are defined in `frontend/src/main.css` using the CSS-first `@theme` syntax:

```css
@import 'tailwindcss';

@source "../../**/*.php";
@source "../assets/**/*.js";

@theme {
  --color-primary: #1313ec;
  --color-secondary: #f4f7ff;
  --font-heading: 'Plus Jakarta Sans', sans-serif;
  /* ... more tokens */
}
```

### Key Design Tokens

| Token                    | Default   | Usage                     |
| ------------------------ | --------- | ------------------------- |
| `--color-primary`        | `#1313ec` | Buttons, links, accents   |
| `--color-secondary`      | `#F4F7FF` | Section backgrounds       |
| `--color-text-primary`   | `#0d0d1b` | Headings, body text       |
| `--color-text-secondary` | `#64748b` | Subtitles, labels         |
| `--color-success`        | `#10B981` | Progress, positive states |
| `--color-danger`         | `#dc2626` | Urgency, errors           |

### Custom Utility Classes

Defined with `@utility` in `main.css`:

- `animate-pulse-slow`, `animate-float`, `animate-shimmer`, `animate-fade-in-up`
- `text-gradient-primary`, `bg-gradient-primary`, `bg-gradient-dark`
- `shadow-primary`, `btn-base`, `progress-bar-base`

### Astra Overrides

The theme dequeues Astra styles and uses TailwindCSS exclusively. Astra overrides are at the bottom of `main.css` to ensure Tailwind utilities work correctly.

### CSS & Styling Rules (Mandatory)

**All styling must use TailwindCSS.** These rules are enforced across the entire theme:

1. **Use Tailwind utility classes in templates.** Write layout and style directly as classes on HTML elements in PHP files. Never reach for raw CSS when a Tailwind class exists.

2. **No `<style>` blocks in PHP files.** If a component needs a named selector (pseudo-elements, `:nth-child()`, third-party plugin overrides, animation state classes), move it to `frontend/src/main.css`.

3. **Use `@apply` in `main.css`.** When a rule must live in `main.css`, declare properties with `@apply` instead of raw CSS:

   ```css
   .nera-toast__close {
     @apply shrink-0 w-6 h-6 flex items-center justify-center text-text-secondary
            cursor-pointer rounded-sm transition-all duration-200;
   }
   .nera-toast__close:hover {
     @apply text-text-primary bg-gray-100;
   }
   ```

4. **Inline `style=""` only for dynamic values.** The only permitted inline styles are values that cannot be known at compile time:
   - PHP-computed image URLs (`background-image: url(...)`)
   - PHP-computed widths or colors (`width: $progress%; color: $accent_color`)
   - JS-controlled accordion state (`max-height`, toggled at runtime)
   - Material Symbols `font-variation-settings`
   - Per-item `animation-delay` computed in a loop

   Static values that have a Tailwind equivalent **must** use the class instead.

5. **Keep these as raw CSS (no `@apply` equivalent):**
   - `@keyframes` blocks
   - `animation`, `animation-delay`, `animation-play-state` properties
   - `stroke-dasharray` / `stroke-dashoffset` (SVG)
   - `filter: drop-shadow(...)` with custom values
   - `transform-style: preserve-3d` / `perspective`
   - `::before` / `::after` pseudo-element `content`

6. **Admin & editor CSS are exceptions:**
   - `assets/css/editor.css` — Gutenberg block editor runs in an iframe; Tailwind utilities are not available there.
   - `assets/css/admin-brand-manager.css` — WordPress admin pages; Tailwind is not compiled for `wp-admin`.

## PHP Conventions

### Template Parts

Use `get_template_part()` with the theme's structure:

```php
// Homepage sections
get_template_part('template-parts/homepage/hero-section');

// Reusable components with arguments
get_template_part('template-parts/competition-card', null, [
  'product_id' => $product->get_id(),
  'show_countdown' => true,
]);
```

### Customizer Settings

All customizer settings are in `inc/customizer.php`. Access values with:

```php
$value = get_theme_mod('nera_setting_name', 'default_value');
```

Key setting prefixes:

- `nera_brand_*` - Brand colors and preset
- `nera_hero_*` - Hero section content
- `nera_header_*` - Header options
- `nera_footer_*` - Footer options

### Brand Presets System

Brand presets in `inc/brand-presets.php` define complete theme configurations:

```php
'preset_key' => array(
    'name' => 'Display Name',
    'colors' => array('primary' => '#HEX', ...),
    'fonts' => array('heading' => 'Font Name', 'body' => 'Font Name'),
    'styles' => array('border_radius' => 'rounded', 'card_shadow' => 'medium'),
)
```

Available presets: `default`, `luxury`, `modern_blue`, `neon_nights`, `forest_green`

## JavaScript Patterns

### Module Structure

All JS modules use the IIFE pattern with auto-initialization:

```javascript
(function() {
    'use strict';

    const CONFIG = { selectors: {...}, classes: {...} };
    let state = { initialized: false };

    function init() {
        if (state.initialized) return;
        // Setup code
        state.initialized = true;
    }

    // Auto-init on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Public API
    window.ModuleName = { init, ... };
})();
```

### Key Modules

| Module             | Purpose                   | Data Attributes                |
| ------------------ | ------------------------- | ------------------------------ |
| `countdown.js`     | Timer functionality       | `data-countdown`, `data-style` |
| `homepage.js`      | Swiper carousels, filters | `.swiper`, `data-filter`       |
| `animations.js`    | AOS scroll animations     | `data-aos`                     |
| `scroll-to-top.js` | Scroll button visibility  | `#scroll-to-top`               |

### External Libraries

- **Swiper 11** - Loaded from CDN for carousels
- **Material Symbols** - Icon font from Google Fonts

## Creating New Components

### New Homepage Section

1. Create template in `template-parts/homepage/[name]-section.php`
2. Add to `page-templates/homepage-template.php`
3. Add customizer settings in `inc/customizer.php` if needed
4. Use Tailwind utility classes for styling

### New Reusable Component

1. Create template in `template-parts/[name].php`
2. Accept `$args` parameter with defaults
3. Follow BEM-like naming: `.nera-component__element--modifier`

### New JavaScript Module

1. Create file in `frontend/assets/js/[module].js`
2. Add import in `frontend/src/main.js`
3. Follow IIFE pattern with public API on `window`

## WooCommerce Integration

The theme integrates with WooCommerce for competition products:

- Products use the "lottery" type from WooCommerce Lottery plugin
- Competition end dates stored in `_lty_end_date_gmt` meta
- Custom product card template in `template-parts/competition-card.php`
- WooCommerce customizations in `inc/woocommerce.php`

## Common Tasks

### Change Brand Colors

1. Go to **Appearance > Customize > Nera Theme Options > Brand Identity**
2. Select a preset or choose "Custom" for manual colors
3. Or use the **Brand Manager** at **Appearance > Brand Manager**

### Add New Competition Card Style

1. Add new style option in `inc/customizer.php` under `nera_card_style`
2. Add corresponding CSS in `frontend/src/main.css`
3. Update `template-parts/competition-card.php` to apply the class

### Modify Homepage Layout

1. Edit `page-templates/homepage-template.php`
2. Reorder, add, or remove `get_template_part()` calls
3. Each section is independent and can be rearranged

## Debugging

### Enable WordPress Debug Mode

```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('NERA_DEV_MODE', true);
```

### Check Asset Loading

1. Verify Vite manifest exists at `frontend/dist/.vite/manifest.json`
2. Check browser console for script errors
3. Inspect Network tab for failed asset loads

### Customizer Issues

1. Verify setting registered in `nera_customize_register()`
2. Check `nera_customizer_css()` outputs the CSS variable
3. Clear caches (browser, WordPress, CDN)

## File Naming Conventions

| Type               | Pattern                 | Example                |
| ------------------ | ----------------------- | ---------------------- |
| Homepage section   | `[name]-section.php`    | `hero-section.php`     |
| Template part      | `[component-name].php`  | `competition-card.php` |
| JavaScript module  | `[feature].js`          | `countdown.js`         |
| Customizer setting | `nera_[section]_[name]` | `nera_hero_title`      |
| CSS class          | `nera-[component]`      | `nera-card`            |

## Security Considerations

- Always escape output: `esc_html()`, `esc_attr()`, `esc_url()`
- Sanitize input: `sanitize_text_field()`, `sanitize_hex_color()`, `absint()`
- Use nonces for forms and AJAX
- Validate capabilities before actions

## Performance Notes

- Scripts load in footer with `true` parameter
- CSS loaded via Vite manifest for optimal caching
- Images should be appropriately sized
- Use `transients` for expensive queries
- Swiper loaded from CDN for caching benefits

## Available Slash Commands

The `.claude/commands/` directory contains predefined workflows:

- `/brand-preset` - Create new brand color preset
- `/homepage-section` - Add new homepage section
- `/template-part` - Create reusable component
- `/js-module` - Create JavaScript module
- `/css-component` - Add CSS component styles
- `/customizer-setting` - Add Customizer option
- `/debug-theme` - Troubleshoot theme issues
- `/woocommerce-integration` - Add WooCommerce customizations
