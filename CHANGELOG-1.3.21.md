# Nera Competitions Standard 1.3.21

## Feature — Editable "Enter By Post" link text

Site-wide ACF control for the postal entry CTA label under the Add to Cart
button on single product pages.

- **`inc/acf/postal-entry/acf-postal-entry.php`:** Theme Settings → Postal Entry
  `postal_entry_link_text` text field (default `ENTER BY POST`).
- **`template-parts/single-product/purchase-card-body-inner.php`:** Renders the
  option value with a fallback to the existing i18n string.
