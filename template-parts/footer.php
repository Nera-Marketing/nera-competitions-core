<?php
/**
 * Custom Footer Template Part
 * 
 * White footer with multi-column layout and dark accent bar
 * 
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit;
}

// Get site info
$site_name = get_bloginfo('name');
$site_url = home_url('/');
$current_year = date('Y');

// Social URLs (get_theme_mod defaults; no Customizer UI)
$facebook_url = get_theme_mod('nera_facebook_url', '#');
$instagram_url = get_theme_mod('nera_instagram_url', '#');
$twitter_url = get_theme_mod('nera_twitter_url', '#');
$youtube_url = get_theme_mod('nera_youtube_url', '#');
?>

<footer class="bg-surface border-t border-gray-200" id="site-footer">

  <!-- Main Footer -->
  <div class="max-w-7xl mx-auto px-4 lg:px-8 py-12 lg:py-16 text-text-secondary">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8 lg:gap-12">

      <!-- Brand Column (Spans 2) -->
      <div class="lg:col-span-2">
        <?php if (is_active_sidebar('footer-1')): ?>
          <?php dynamic_sidebar('footer-1'); ?>
        <?php endif; ?>
      </div>

      <!-- Quick Links -->
      <div>
        <?php if (is_active_sidebar('footer-2')): ?>
          <?php dynamic_sidebar('footer-2'); ?>
        <?php endif; ?>
      </div>

      <!-- Support -->
      <div>
        <?php if (is_active_sidebar('footer-3')): ?>
          <?php dynamic_sidebar('footer-3'); ?>
        <?php endif; ?>
      </div>

      <!-- Legal -->
      <div>
        <?php if (is_active_sidebar('footer-4')): ?>
          <?php dynamic_sidebar('footer-4'); ?>
        <?php endif; ?>
      </div>

    </div>
  </div>

  <!-- Dark Bottom Bar -->
  <div class="bg-background-dark">
    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-4">
      <div class="flex flex-col md:flex-row items-center justify-between gap-4">

        <!-- Copyright -->
        <div class="text-text-secondary text-sm">
          <?php
          $copyright_text = get_field('footer_copyright', 'option');
          if ($copyright_text) {
            echo str_replace('{year}', date('Y'), $copyright_text);
          } else {
            echo '&copy; ' . date('Y') . ' ' . get_bloginfo('name') . '. ' . __('All rights reserved.', 'nera-competitions');
          }
          ?>
        </div>

        <!-- Payment Methods / Bottom Right -->
        <div class="flex items-center gap-4">
          <?php
          $bottom_right = get_field('footer_bottom_right', 'option');
          if ($bottom_right) {
            echo $bottom_right;
          } else {
            // Default content if field is empty (matching original design as fallback)
            ?>
            <span class="text-gray-500 text-xs"><?php _e('Secure payments:', 'nera-competitions'); ?></span>
            <div class="text-gray-400 text-xs font-medium flex gap-2">
              <span>Visa</span> <span>Mastercard</span> <span>PayPal</span> <span>Apple Pay</span>
            </div>
            <?php
          }
          ?>
        </div>

      </div>
    </div>
  </div>

  <!-- Scroll to Top Button -->
  <button id="nera-scroll-top"
    class="fixed bottom-8 cursor-pointer right-8 z-[100] p-3 bg-gray-900 text-white rounded-full shadow-xl hover:bg-black hover:shadow-xl hover:scale-110 hover:-translate-y-1 transform transition-all duration-300 translate-y-20 opacity-0 invisible border border-gray-800"
    aria-label="<?php esc_attr_e('Scroll to top', 'nera-competitions'); ?>">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
      stroke-width="2.5">
      <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18" />
    </svg>
  </button>

</footer>