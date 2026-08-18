# Nera Competitions Standard 1.3.24

## Feature — Editable attribution page URL slug

ACF control on the Attribution Page Editor for the public path of the
virtual Nera Marketing attribution route.

- **`inc/acf/attribution/acf-attribution.php`:** `attr_page_slug` text field
  (default `competition-website-by-nera-marketing`), visible above the content
  tabs; slug is sanitized on save.
- **`functions.php`:** Rewrite uses the saved slug, flushes rules when it
  changes, and 301s the previous (and original default) slug to the new URL.
- **`page-templates/nera-marketing-attribution.php`:** JSON-LD page URL follows
  the current slug.
