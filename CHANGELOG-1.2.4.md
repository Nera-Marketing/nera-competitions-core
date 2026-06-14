# Nera Competitions Standard 1.2.4

## Instant-win result overlay — revamped prize screen

- **`lty-result-screens/templates/instant-win-won.php`:** won prizes now render in a responsive grid — 2 per row, image-left / text-right on desktop; single-column, stacked & centered on mobile. One unified card layout for every prize type (product, wallet credit, coupon, message).
- **Pagination (`lty-result-screens/assets/js/lty-result-screens.js`):** client-side paging at 6 prizes per page, mirroring the winners modal — a "N prizes · Page X of Y" subline plus a Previous / page-dots / Next footer with arrow-key support. Degrades gracefully when JS is disabled (all cards shown, controls hidden).
- **Card animation:** staggered fade/slide-in (`rs-card-enter`) on open and on each page change; respects `prefers-reduced-motion`.
- **Larger top-right close (X)** button on the win overlay (reuses `.lty-rs-close-x`).
- **Mobile image fix:** the theme's `.woocommerce img { height:100% }` rule outranked Tailwind's `.h-16` and ballooned prize thumbnails inside the mobile flex-column card, overlapping content. New `.lty-rs-prize-img` rule pins thumbnails to a fixed 64×64 square.
- **Asset cache-busting:** the `lty-result-screens` CSS/JS now enqueue with `filemtime()` versions so rebuilt assets load reliably (previously pinned to the static `NERA_VERSION`).
