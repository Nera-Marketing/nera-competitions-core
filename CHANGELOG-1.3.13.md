# Nera Competitions Standard 1.3.13

## Feature — Hide Tickets Sold / Progress Bar

Site-wide and per-product control for the Tickets Sold label and progress bar on
competition cards and the purchase card.

- **`inc/acf/woocommerce/acf-woocommerce.php`:** Theme Settings → WooCommerce
  `show_tickets_progress` true/false toggle (default Visible).
- **`inc/acf/single-product/acf-single-product.php`:** Competition Settings
  override select — `inherit` / `show` / `hide` (default inherit).
- **`inc/woocommerce.php`:** `nera_show_tickets_progress()` resolves the global
  option with an optional per-product override.
- **Gated surfaces:** `CompetitionCard`, purchase card `TicketsProgress`, legacy
  `progress-bar.php`, FeaturedCompetitions placeholders, homepage featured PHP
  cards, entry-list and closed-prize cards, and `[competition_progress]`.
