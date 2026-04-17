# Extending nera-competitions-standard

This document is the **extension contract** for child themes built on `nera-competitions-standard`. Read it before writing any CSS or PHP overrides in a child theme.

---

## 1. How the architecture works

```
nera-competitions-standard (parent)
  frontend/src/main.css  → @theme tokens + Tailwind utilities
  *.php templates        → utility classes only (semantic tokens, no raw palette)

child-theme/
  frontend/src/main.css  → @import 'tailwindcss'; @theme { /* overrides */ }
  assets/css/*.css       → scoped overrides targeting .ncs-* hooks (no !important)
```

The parent's Vite build is loaded first; the child enqueues its stylesheet after. TailwindCSS v4's `@theme` cascade means child `@theme` blocks override parent tokens — and every utility class that references those tokens automatically repaints.

---

## 2. Semantic color tokens

Override any of these in your child `@theme` block. All parent templates and component styles use these tokens — never raw Tailwind palette names.

### Brand

| Token | Parent default | Role |
|---|---|---|
| `--color-primary` | `#1313ec` | Buttons, links, focus rings, gradients |
| `--color-primary-dark` | `#0d0db3` | Hover state for primary |
| `--color-on-primary` | `#ffffff` | Text/icons on top of `--color-primary` surfaces |
| `--color-accent` | `#1313ec` | Accent highlights (CTA, featured badges) |
| `--color-accent-dark` | `#0d0db3` | Hover state for accent |
| `--color-secondary` | `#f4f7ff` | Subtle section backgrounds, hover fills |

### Canvas & surfaces

| Token | Parent default | Role |
|---|---|---|
| `--color-background-light` | `#f6f6f8` | Page canvas — `body` background |
| `--color-background-dark` | `#101022` | Dark-section backgrounds |
| `--color-surface` | `#ffffff` | Cards, modals, header bar |

### Text

| Token | Parent default | Role |
|---|---|---|
| `--color-text-primary` | `#0d0d1b` | Body copy, headings |
| `--color-text-secondary` | `#64748b` | Captions, labels, muted text |

### Status base

| Token | Parent default | Role |
|---|---|---|
| `--color-success` | `#10b981` | Progress bars, positive icons |
| `--color-danger` | `#dc2626` | Errors, required markers |
| `--color-warning` | `#f77f00` | Pending states, caution |
| `--color-info` | `#3b82f6` | Informational elements |

### Status triplets (badges / pills)

Use these for status badges instead of raw `blue-*` / `green-*`. All six triplets are overridable together in one `@theme` block.

| Token | Parent default | Role |
|---|---|---|
| `--color-success-bg` | `#d1fae5` | Badge background |
| `--color-success-border` | `#6ee7b7` | Badge border |
| `--color-success-text` | `#065f46` | Badge text |
| `--color-danger-bg` | `#fee2e2` | — |
| `--color-danger-border` | `#fca5a5` | — |
| `--color-danger-text` | `#991b1b` | — |
| `--color-warning-bg` | `#ffedd5` | — |
| `--color-warning-border` | `#fdba74` | — |
| `--color-warning-text` | `#9a3412` | — |
| `--color-info-bg` | `#dbeafe` | — |
| `--color-info-border` | `#93c5fd` | — |
| `--color-info-text` | `#1e40af` | — |

### Neutral scale

`gray-50` … `gray-900` are **intentionally re-mappable**. Override all 10 stops in your `@theme` to shift the entire neutral palette (e.g. warm tan, dark charcoal).

---

## 3. Typography tokens

| Token | Parent default |
|---|---|
| `--font-heading` | `'Poppins', system-ui, sans-serif` |
| `--font-body` | `'Poppins', system-ui, sans-serif` |
| `--font-size-xs` … `--font-size-6xl` | Standard scale |

Override `--font-heading` and `--font-body` in your child `@theme` to switch typefaces globally.

---

## 4. BEM slot classes (`.ncs-*` hooks)

Parent templates add `.ncs-*` classes to the ~15 components child themes most often need to reshape. Target these in your child CSS instead of overriding utility classes.

> **Added in Phase C.** Hooks not yet present in parent templates are listed here as the planned contract.

| BEM class | Component | Template |
|---|---|---|
| `.ncs-hero` | Homepage hero strip | `template-parts/homepage/hero-section.php` |
| `.ncs-hero__title` | Hero heading | same |
| `.ncs-hero__cta` | Hero CTA button | same |
| `.ncs-product-card` | Competition card | `template-parts/product-listing/product-card.php` |
| `.ncs-product-card__title` | Card title | same |
| `.ncs-product-card__price` | Card price | same |
| `.ncs-page-hero` | Cart/checkout hero strip | `template-parts/checkout/*`, `woocommerce/cart/*` |
| `.ncs-payment-method` | Checkout payment tile | `woocommerce/checkout/payment-method.php` |
| `.ncs-wallet-balance` | Checkout wallet card | `template-parts/checkout/wallet-balance.php` |
| `.ncs-cart-item` | Cart item row | `template-parts/cart/cart-item.php` |
| `.ncs-account-nav__link` | My Account nav link | `woocommerce/myaccount/dashboard.php` |
| `.ncs-dashboard-welcome` | My Account welcome hero | `woocommerce/myaccount/dashboard.php` |
| `.ncs-stat-card` | Dashboard stat tile | same |
| `.ncs-stat-card__icon` | Stat tile icon | same |
| `.ncs-order-status` | Order status badge | `woocommerce/myaccount/orders.php`, `view-order.php` |
| `.ncs-order-status--{paid\|pending\|refunded\|…}` | Status modifier | same |
| `.ncs-progress` | Competition progress bar | `template-parts/single-product/progress-bar.php` |
| `.ncs-progress__fill` | Progress fill element | same |
| `.ncs-countdown` | Countdown timer | `template-parts/single-product/countdown-timer.php` |
| `.ncs-site-footer` | Site footer strip | `footer.php`, `template-parts/footer.php` |
| `.ncs-form-input` | Checkout / account input | checkout + my-account edit forms |
| `.ncs-badge` | Generic badge / pill | dashboards, product card |
| `.ncs-badge--{info\|success\|warning\|danger}` | Badge modifier | same |

**Style these hooks at equal specificity — no `!important` needed:**

```css
/* child-theme/assets/css/brand-overrides.css */

.ncs-hero {
  background: linear-gradient(135deg, var(--color-primary), var(--color-accent));
}

.ncs-hero__title {
  color: var(--color-on-primary);
}
```

---

## 5. Color-utility mapping (Phase B reference)

When replacing legacy raw utilities in parent templates, use this table:

| Old utility | Semantic replacement |
|---|---|
| `*-indigo-*`, `*-blue-500`, `*-blue-600` | `*-primary`, `*-primary-dark` |
| `*-slate-50`, `*-gray-50` (page bg) | `*-background-light` |
| `*-white` (elevated panels) | `*-surface` |
| `*-blue-50 / *-blue-100 / *-blue-700` (info) | `*-info-bg / *-info-border / *-info-text` |
| `*-green-50 / *-green-200 / *-green-700` | `*-success-bg / *-success-border / *-success-text` |
| `*-yellow-*`, `*-orange-*` (pending/warning) | `*-warning-bg / *-warning-border / *-warning-text` |
| `*-red-*` | `*-danger-bg / *-danger-border / *-danger-text` |
| `*-purple-*` (CTA accent) | `*-accent` |
| `*-gray-50 … *-gray-900` (neutral chrome) | **keep as-is** |

---

## 6. Child theme quick-start

### Minimal setup

```css
/* child-theme/frontend/src/main.css */
@import 'tailwindcss';

@source "../../**/*.php";
@source "../assets/**/*.js";

@theme {
  /* Brand palette */
  --color-primary: #d8b582;
  --color-primary-dark: #b8923a;
  --color-on-primary: #0c0b09;
  --color-accent: #9b5039;

  /* Canvas / surfaces */
  --color-background-light: #0c0b09;
  --color-surface: #1e1c18;

  /* Text */
  --color-text-primary: #d8b582;
  --color-text-secondary: #a89572;

  /* Typography */
  --font-heading: 'Playfair Display', serif;
  --font-body: 'DM Sans', sans-serif;
}
```

That's it. Every parent template and component re-paints automatically.

### When to add scoped CSS

Reach for a scoped CSS file **only** for:
- Gradient or image-based backgrounds that can't be expressed as a token
- WooCommerce / plugin injected styles (they ship their own cascade)
- Pseudo-element `::before` / `::after` decoration

Target `.ncs-*` hooks — never target parent utility classes directly.

### Enqueue discipline

```php
// child functions.php
add_action('wp_enqueue_scripts', function () {
    // Parent bundle is already enqueued by the parent theme
    wp_enqueue_style(
        'child-brand',
        get_stylesheet_directory_uri() . '/assets/css/brand.css',
        ['nera-main'],   // depends on parent bundle
        filemtime(get_stylesheet_directory() . '/assets/css/brand.css'),
    );
});
```

---

## 7. Build-time lint

The parent Vite build runs `frontend/scripts/lint-templates.js` on every `yarn dev` and `yarn build`. It reports any PHP template that contains a forbidden raw-palette color utility.

- **Phase A/B**: `mode: 'warn'` — violations are printed; build succeeds.
- **Post-Phase B**: `mode: 'error'` — violations block the build. Edit `frontend/vite.config.js` to flip.

If you add a new component to the parent and trigger a violation, replace the raw utility with the appropriate semantic token before committing.
