# Nera Competitions Standard 1.3.3

## Spin To Win (STW) CTA — result-screen overlays

- **`lty-result-screens/inc/class-lty-result-screens.php`:** new private
  `collect_spin_links()` helper iterates order items, checks `Nera_STW_Product_Meta::is_enabled()`
  and calls `nera_stw_get_spin_url()` to build a `{url, label, count}` array.
  `count_order_spins()` + `get_spin_eyebrow_text()` derive the eyebrow copy ("You also
  have X spin(s)…"). `$spin_links` and `$spin_eyebrow_text` are now forwarded to every
  result-screen template.
- **`lty-result-screens/templates/instant-win-won.php`:** STW banner (`lty-rs-spin-banner`)
  renders below the claim button when `$spin_links` is non-empty. Close (`×`) button
  repositioned outside the scrollable content area (above confetti layer) so it is always
  accessible. Prize cards switch between three layout variants depending on prize count and
  type: single wallet prize gets a centred large display; single physical/coupon prize gets
  a full-width card with larger image and optional copy-code button; multiple prizes keep
  the existing two-column grid. Grid class is now dynamic (`$prizes_grid_class`) — single
  prizes no longer use the 2-col grid wrapper.
- **`lty-result-screens/templates/instant-win-no-win.php`:** STW banner added below
  "Browse Competitions" button when `$spin_links` is non-empty.
- **`lty-result-screens/templates/prize-draw-good-luck.php`:** STW banner added below
  the "Got it" button when `$spin_links` is non-empty.
- **`lty-result-screens/src/input.css`:** new utility classes — `.lty-rs-spin-banner`,
  `.lty-rs-spin-banner__eyebrow`, `.lty-rs-spin-wheel` (wheel emoji via `::before`),
  `.lty-rs-btn-spin`, `.lty-rs-wallet-prize--inline`, `.lty-rs-wallet-prize__row`,
  `.lty-rs-wallet-prize__amount`, `.lty-rs-wallet-prize__label`, `.lty-rs-close-x`
  (absolute-positioned × button outside scroll area).

## Author Avatar — blog post card & single post

- **`inc/acf/user/acf-user-profile.php`:** new ACF field group `group_nera_user_profile`
  registered on all user forms. Adds an "Author Profile Picture" image field
  (`author_profile_picture`) — returns array, accepts jpg/jpeg/png/webp, previews at
  thumbnail size.
- **`inc/helpers/author-avatar.php`:** three new pure helper functions:
    - `nera_get_user_profile_picture_url( $user_id, $size )` — resolves the ACF image
      field (handles numeric ID, array with `sizes`, or raw URL); maps `$size` to the
      closest registered WP image size via `nera_author_avatar_image_size()`.
    - `nera_get_author_initials( $user_id )` — up to two uppercase initials from
      `display_name`; returns `?` for unknown users.
    - `nera_render_author_avatar( $user_id, $size, $args )` — returns an `<img>` tag
      when an ACF image exists, or an initials `<span>` placeholder with
      `bg-gradient-to-br from-primary to-primary-dark` when it does not. No Gravatar
      dependency.
- **`functions.php`:** `require_once` for `inc/acf/user/acf-user-profile.php` and
  `inc/helpers/author-avatar.php` added after the attribution ACF group.
- **`single-post.php`:** both author byline blocks (hero dark variant + article light
  variant) replace `get_avatar()` with `nera_render_author_avatar()`, adding
  `object-cover` to the class list.
- **`template-parts/blog/post-card.php`:** featured card variant gains an author row
  (avatar at 32 px + display name) above the post title.
