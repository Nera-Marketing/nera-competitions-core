# Nera Dark Yellow

Child theme for **Nera Competitions Standard** (`nera-competitions-standard`). Dark page canvas with yellow brand accents — token-driven per [`EXTENDING.md`](../../EXTENDING.md).

## Install

1. Ensure the parent theme folder is named `nera-competitions-standard` under `wp-content/themes/`.
2. Copy this directory to `wp-content/themes/nera-dark-yellow/`:

   ```bash
   cp -R starters/nera-dark-yellow /path/to/wp-content/themes/nera-dark-yellow
   ```

3. Activate **Nera Dark Yellow** in **Appearance → Themes**.
4. (Optional) Build child Vite tokens:

   ```bash
   cd wp-content/themes/nera-dark-yellow
   yarn install
   yarn build
   ```

   Without a build, `assets/css/brand.css` is used as-is (already committed).

## Palette

| Token | Value | Role |
|---|---|---|
| `--color-primary` | `#F5C518` | Buttons, links, progress, accents |
| `--color-on-primary` | `#0B0B0F` | Text on yellow surfaces |
| `--color-background-light` | `#0B0B0F` | Page canvas |
| `--color-surface` | `#16161C` | Cards, header, elevated panels |
| `--color-text-primary` | `#F7F7F5` | Headings / body |
| `--color-text-secondary` | `#A8A29E` | Muted labels |

Gray `50–900` is remapped for a dark shell. Component knobs live in `assets/css/child-tokens.css`.

## Layout

Parent templates and Timber components are reused unchanged. Override a view only when markup must change:

```
nera-dark-yellow/Components/sections/HomepageHero/template.twig  ← optional
```

## Filters

- `nera_advanced_filter_category_colors` / `nera_competition_card_category_colors` — brighter accents on dark
- `nera_competition_card_fallback_accent` → `#F5C518`
