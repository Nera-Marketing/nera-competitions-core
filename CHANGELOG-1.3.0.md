# Nera Competitions Standard 1.3.0

## Heading Style system — global default + per-section overrides

- **`inc/acf/heading-style/acf-heading-style.php`:** new "Headings" sub-page under Theme Settings — site-wide default heading font + accent colour.
- **`inc/helpers/heading-style.php`:** pure helpers — curated heading-font list (+ custom Google Font), per-section ACF override field defs, override resolution.
- **`inc/heading-style.php`:** runtime wiring — injects resolved per-section overrides into every section component's Twig context (`nera_component_data`), emits the global default font/accent as CSS vars in `<head>`, loads custom Google Fonts (global + per-section).
- Two-tone section headings wired across section components (`Components/sections/*` `template.twig` + `fields.php`).

## Single-product — details-first mobile purchase card

- **`template-parts/single-product/purchase-card.php`:** refactored into an orchestrator that delegates inner content to partials by section (`full` / `header` / `body`).
- **`template-parts/single-product/purchase-card-header-inner.php`** + **`purchase-card-body-inner.php`:** new reusable inner partials.
- **`inc/acf/woocommerce/acf-woocommerce.php`** + **`inc/acf/single-product/acf-single-product.php`:** site-wide + per-product mobile layout option (default / details-first); `nera_get_mobile_card_layout()` resolves default vs override.
- **`woocommerce/single-product.php`:** renders split header/gallery/body segments on mobile when layout is `details_first`, preserving the existing default order otherwise.
- **`frontend/src/sections/single-product-unified-mobile.css`:** styles header/gallery/body as one rounded card on mobile via `data-ncs-unified-mobile` hooks.

## Winners (Dynamic) — per-page winner-type visibility

- **`inc/acf/winners-dynamic/acf-winners-dynamic.php`:** new per-page control (`winners_dynamic_show_types`) selecting which winner types show (Live draw / Instant Win).
- **`template-parts/winners-dynamic/winners-grid.php`** + **`inc/woocommerce.php`:** thread allowed types through merged entries, counts, the filter whitelist and the paginated dataset — a disabled type's posts are never queried, its filter tab is hidden, the tab bar hides when only one type remains, and allowed types are enforced server-side in the load-more AJAX.

## Internal

- **`inc/legacy-acf-visibility.php`:** hide legacy per-page ACF metaboxes once a page uses the Page Components builder (kept visible when `page_components` is empty so each template's legacy fallback layout stays editable).
