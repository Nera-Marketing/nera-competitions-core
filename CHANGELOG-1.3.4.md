# Nera Competitions Standard 1.3.4

## Bug Fix — STW CTA banner not appearing on result screens

- **`lty-result-screens/inc/class-lty-result-screens.php`:** `collect_spin_links()` was
  guarded by `lty_is_lottery_product( $product )`, which returns `false` for Spin To Win
  Wheel products (they are not a WooCommerce Lottery product type). This caused the STW
  Wheel item in an order to be skipped unconditionally, leaving `$spin_links` empty and
  the STW CTA banner never rendering on any result screen overlay (instant-win-won,
  instant-win-no-win, prize-draw-good-luck).

  Fix: the `lty_is_lottery_product` check and its corresponding `function_exists` guard
  are removed from `collect_spin_links()`. The loop now iterates all order items and
  collects spin links for any product that passes `Nera_STW_Product_Meta::is_enabled()`,
  regardless of product type.
