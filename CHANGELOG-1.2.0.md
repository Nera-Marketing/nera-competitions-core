# Nera Competitions Standard 1.2.0

## Purchase card & quantity

- **Slider quantity selector** on competition purchase cards: new `QuantitySelector` block, `frontend/assets/js/quantity-control.js`, and styles in `frontend/src/main.css`.
- **Global ACF setting** to choose quantity control style (classic vs slider) in WooCommerce + single-product field groups (`inc/acf/woocommerce/acf-woocommerce.php`, `inc/acf/single-product/acf-single-product.php`); wired via `inc/woocommerce.php` and `template-parts/single-product/purchase-card.php`.
- `EXTENDING.md` updated for child themes overriding quantity UI.

## Shop & listings

- **Advanced filter grid:** keeps **2 columns on tablet** breakpoints (fix for layout regression).

## Fixes

- Single product page and **toast message** behavior corrections.
- Slider quantity UX polish (`update slider` follow-up).

## Theme maintenance

- Removed duplicate `woocommerce/single-product.php` and `woocommerce/single-product/competitions.php` overrides (routing consolidated elsewhere).
