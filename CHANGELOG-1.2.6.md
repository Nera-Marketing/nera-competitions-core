# Nera Competitions Standard 1.2.6

## Product title — name only

- **`Components/blocks/ProductTitle/template.twig`:** single-product heading now shows the product name only (removed the "Win a {name}" prefix).

## Entry List tab — global default + per-product override

- **`inc/acf/woocommerce/acf-woocommerce.php`:** new site-wide **Show Entry List Tab** toggle under Theme Settings → WooCommerce.
- **`inc/acf/single-product/acf-single-product.php`:** per-product control changed from true/false to a select — inherit site default, show, or hide.
- **`inc/woocommerce.php`:** `nera_show_entry_list_tab()` resolves global + override; legacy `true_false` postmeta still supported.
- **`template-parts/single-product/tabs.php`:** Entry List tab and panel render only when `nera_show_entry_list_tab()` returns true.

## Listing Visibility — WooCommerce settings

- **`inc/acf/listing-visibility/acf-listing-visibility.php`:** field group moved from a standalone Theme Settings submenu onto Theme Settings → WooCommerce (alongside other shop options).

## Internal cleanup

- Removed dev-only scripts `tests/listing-visibility-smoke-test.php` and `tests/seed-listing-visibility.php`.
