# Declare nera-checkout as an Alpine dependency, not just an early enqueue

The checkout form's `x-data="neraCheckout()"` throws `neraCheckout is not defined` the instant Alpine boots, on this site only. Nothing about the checkout is actually broken server-side — the whole `neraCheckout()` Alpine component (terms guard, the PayPal Smart Button's disabled-until-terms-accepted state, Place Order's own disabled state) simply never runs, silently: no console error a shopper would see, no notice, just a checkout page whose payment buttons don't respond to the terms checkbox at all.

`nera_enqueue_scripts()` already enqueues `nera-checkout` (step 4) before it builds `$alpine_component_deps` and registers `alpinejs-collapse` → `alpinejs` (step 6/7), with a comment saying it "must load before Alpine.js initializes." But being enqueued earlier in the same function call is not a dependency — nothing in `$alpine_component_deps` actually names `nera-checkout`, so WordPress has no reason to print it ahead of Alpine. The two are unrelated nodes in the script dependency graph, and their relative order falls out of whatever else is enqueuing scripts on `wp_enqueue_scripts` that request. On this site, `nera-age-shield-plugin` (and the rest of the compliance-plugin stack scoop.test doesn't carry) enqueues its own scripts on the same hook, and the resulting order happens to print `alpinejs` before `nera-checkout`. Same theme code, same comment, same intent — the plugin mix decides whether the race is ever visible.

**Make the dependency real.** `$alpine_component_deps` already has this exact pattern for `nera-alpine-product-gallery` (on product pages) and `nera-alpine-winners-page` (on the winners template) — components that must exist before Alpine evaluates their `x-data`. `nera-checkout` needed the same line and didn't have it.

## Consequences

- Fixed by adding `nera-checkout` to `$alpine_component_deps` when `is_checkout() && !is_order_received_page()`, mirroring the other two conditional entries already there.
- The theme's other Alpine components already declared here were unaffected — this only changes where `nera-checkout` prints relative to `alpinejs`.
- Confirmed live: before the fix, `window.neraCheckout` was undefined at Alpine's evaluation and the PayPal button's `ncs-ppcp-blocked` class never applied regardless of terms state. After, it toggles correctly with the checkbox (`aria-disabled` true/false, class added/removed) and the real PayPal approval popup opens once terms are accepted.
- The same unguarded ordering exists in `nera-competitions-standard` wherever it's deployed — ported the identical one-line fix to the scoop.test checkout to close the same latent race there before a future plugin addition trips it the same way.
