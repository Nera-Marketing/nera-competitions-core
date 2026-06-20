# Nera Competitions Standard 1.3.2

## Heading Style — highlight font-weight control

- **`inc/helpers/heading-style.php`:** two new helpers — `nera_heading_font_weight_choices()`
  (curated 400/600/700/800 + custom select options, optional "Inherit" for per-section
  use) and `nera_heading_font_weight()` (resolves a slug + custom value to a clamped
  1–1000 int, 0 for inherit/empty). `nera_resolve_heading_style()` now returns a
  `font_weight` key.
- **`inc/acf/heading-style/acf-heading-style.php`:** Theme Settings → Headings gains
  "Default Highlight Font Weight" (select, default Bold/700) plus a conditional
  "Custom Font Weight" number field (1–1000).
- **Per-section fields (`inc/helpers/heading-style.php`):** each section gains a
  "Highlight Font Weight" override (default "Inherit (global default)") with its own
  conditional custom-number field.
- **`inc/heading-style.php`:** `nera_print_heading_style_vars()` now emits
  `--heading-highlight-weight` (global default, falls back to 700);
  `nera_inject_heading_style()` folds weight into `heading_accent_style`
  (`color: …; font-family: …; font-weight: …`), using a per-section literal or
  `var(--heading-highlight-weight)`.
- **ACF UX — Content / Styles tabs:** `nera_with_heading_fields()` splits the
  per-section heading fields across two tabs — the "Heading Highlight" text stays inline
  under "Content"; the visual overrides (highlight font, font weight, accent colour +
  custom inputs) move under a dedicated "Styles" tab. Tabs are presentational only —
  field names unchanged, so saved data and rendering are unaffected. Components that
  already define their own tab bar (e.g. Contact) keep it and just gain the "Styles" tab.
- **Section templates:** `Components/sections/{AboutUsPage,HowItWorksDraw,HowItWorksPostal,
  HowItWorksTransparency}/template.twig` and `page-templates/about-us-template.php` now
  set an explicit `font-bold` on base headings, so the heading base stays bold while the
  highlight weight is controlled independently.
