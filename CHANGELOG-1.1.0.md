# Nera Competitions Standard 1.1.0

## Shop & listings

- Shop page ACF on the WooCommerce Shop page: grid columns, classic/portrait card layout, optional per-card image aspect ratio (`inc/acf/shop-listing/`, `inc/helpers/shop-listing.php`).
- Competition card portrait layout and configurable aspect ratio on shop/archive listings; portrait mode adjusts footer CTA/countdown stacking.
- Competition card progress bar: taller track, `.ncs-product-card__progress-track` / `__progress-fill`, accent via `--ncs-product-card-progress-accent`.

## Theme maintenance

- Default branding asset `logo.png` for Custom Logo / header use.
- Legacy frontend CSS archived under `frontend/assets/css/_archive/` (not loaded in production); `yarn.lock` refreshed.
- Homepage categories filter partial updated.
