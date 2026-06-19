# Nera Competitions Standard 1.3.1

## Heading Style — highlight-only font application

- **`inc/heading-style.php`:** the chosen heading font now paints only the
  highlighted (trailing accent) span of two-tone section headings instead of
  overriding the whole heading. `nera_print_heading_style_vars()` now emits
  `--heading-highlight-font` (was `--font-heading`), so the full heading keeps
  the `--font-heading` theme default. `nera_inject_heading_style()` folds the
  font into `heading_accent_style` (`color: …; font-family: …`) and drops the
  separate `heading_font_style` context var.
- **`frontend/src/main.css`:** new `:root` var `--heading-highlight-font`
  (defaults to `var(--font-heading)`).
- **`inc/acf/heading-style/acf-heading-style.php`:** Theme Settings → Headings
  field relabelled "Default Heading Font" → "Default Highlight Font";
  instructions clarified to highlight-only scope.
- **`inc/helpers/heading-style.php`:** per-section field relabelled "Heading
  Font" → "Highlight Font"; override-resolution docs updated for the
  highlight-only behaviour.
- **`Components/sections/*/template.twig` (14 sections):** removed the now-unused
  `heading_font_style` inline style from the section heading element; the
  highlight `<span>` keeps `heading_accent_style` (now carries colour + font).
