# Nera Competitions Standard 1.2.3

## Component system — child-theme standalone components

- **`lib/components.php`:** child-theme component `fields.php` files are now loaded alongside `index.php` (names not present in the parent), so child-only components can register their own ACF sub-fields.
- **`lib/components.php`:** child-theme-only components that define `get_acf_layout()` are now registered as layouts in the `page_components` flexible content (Page Components "Add Component" picker). Parent component names win on collision — duplicate layouts are skipped, mirroring the existing `index.php` collision guard. `.not-top-level` sentinel is respected for child components too.
- Fixes child sites whose pages are composed from standalone child-theme Timber Components (e.g. Rusboy homepage) losing all components after a parent theme update, which previously wiped the equivalent local-only patch.
- `EXTENDING.md` updated: child standalone components are now editor-pickable; documented collision rules.
