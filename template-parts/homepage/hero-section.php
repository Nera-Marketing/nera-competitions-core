<?php
/**
 * Hero Section Template Part
 *
 * Main hero banner for the homepage
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

// Hero content: a field saved on the page wins, even when blank (a blank
// field hides its element); the default only applies when never saved.
$hero_post_id = (int) (get_queried_object_id() ?: get_the_ID());
$hero_field = function (string $key, $default = '') use ($hero_post_id) {
  if (!$hero_post_id || !metadata_exists('post', $hero_post_id, $key)) {
    return $default;
  }
  $value = get_field($key, $hero_post_id);
  return is_string($value) ? trim($value) : ($value ?: '');
};

$hero_title = (string) $hero_field('hero_title', __('Win Your Dream', 'nera-competitions'));
$hero_highlight = (string) $hero_field('hero_highlight', __('Lifestyle.', 'nera-competitions'));
$hero_description = (string) $hero_field(
  'hero_description',
  __(
    'Experience the thrill of high-end giveaways with the UK\'s most exclusive prize competition platform. Because you deserve a chance to win.',
    'nera-competitions',
  ),
);
$hero_cta_text = (string) $hero_field('hero_cta_text', __('View Active Giveaways', 'nera-competitions'));
$hero_cta_url = get_field('hero_cta_url', $hero_post_id) ?: get_permalink(wc_get_page_id('shop'));
$hero_secondary_text = (string) $hero_field('hero_secondary_text', __('Recent Winners', 'nera-competitions'));
$hero_secondary_url = get_field('hero_secondary_url', $hero_post_id) ?: '#winners';

// Image
$hero_image = $hero_field('hero_image', '');

// Last winner
$last_winner_name = (string) $hero_field('last_winner_name', 'Sarah M.');
$last_winner_prize = (string) $hero_field('last_winner_prize', 'Won This Prize');

// Nothing to show: no hero at all.
if ($hero_title === '' && $hero_highlight === '' && $hero_description === '' && !$hero_image) {
  return;
}
?>

<section
  class="ncs-hero hero-section relative py-16 lg:py-24 bg-gradient-to-br from-surface via-gray-50 to-secondary overflow-hidden"
  id="hero" data-aos="fade-up" data-aos-duration="600">

  <div class="max-w-7xl mx-auto px-5 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">

      <!-- Content -->
      <div class="order-2 lg:order-1">

        <!-- Badge -->
        <span
          class="inline-flex items-center gap-2 px-4 py-2 bg-primary/10 text-primary rounded-full text-sm font-semibold mb-6">
          🏆
          <?php _e('Premium Giveaways', 'nera-competitions'); ?>
        </span>

        <!-- Title -->
        <?php if ($hero_title !== '' || $hero_highlight !== ''): ?>
        <h1 class="ncs-hero__title font-heading text-4xl md:text-5xl lg:text-6xl font-bold text-text-primary leading-tight mb-6">
          <?php echo esc_html($hero_title); ?>
          <?php if ($hero_highlight !== ''): ?>
          <?php if ($hero_title !== ''): ?><br><?php endif; ?>
          <span class="text-gradient-primary">
            <?php echo esc_html($hero_highlight); ?>
          </span>
          <?php endif; ?>
        </h1>
        <?php endif; ?>

        <!-- Description -->
        <?php if ($hero_description !== ''): ?>
        <p class="text-lg text-text-secondary leading-relaxed mb-8 max-w-lg">
          <?php echo esc_html($hero_description); ?>
        </p>
        <?php endif; ?>

        <!-- CTA Buttons -->
        <?php if ($hero_cta_text !== '' || $hero_secondary_text !== ''): ?>
        <div class="ncs-hero__cta flex flex-wrap gap-4 mb-8">
          <?php if ($hero_cta_text !== ''): ?>
          <a href="<?php echo esc_url($hero_cta_url); ?>"
            class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-primary text-white font-semibold rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all">
            <?php echo esc_html($hero_cta_text); ?>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M5 12h14M12 5l7 7-7 7" />
            </svg>
          </a>
          <?php endif; ?>
          <?php if ($hero_secondary_text !== ''): ?>
          <a href="<?php echo esc_url($hero_secondary_url); ?>"
            class="inline-flex items-center gap-2 px-8 py-4 border-2 border-gray-200 text-text-primary font-semibold rounded-xl hover:border-primary hover:text-primary transition-colors">
            <?php echo esc_html($hero_secondary_text); ?>
          </a>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Image -->
      <div class="order-1 lg:order-2 relative mb-6 md:mb-0">
        <div class="relative">
          <!-- Main Image -->
          <div class="relative rounded-2xl overflow-hidden shadow-2xl">
            <?php if ($hero_image): ?>
              <img src="<?php echo esc_url($hero_image); ?>" alt="<?php echo esc_attr(
  $hero_title,
); ?>"
                class="w-full h-auto">
            <?php else: ?>
              <div class="aspect-[4/3] bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
                <span class="text-6xl">🚗</span>
              </div>
            <?php endif; ?>
          </div>

          <!-- Winner Badge -->
          <?php if ($last_winner_name !== ''): ?>
          <div
            class="absolute bottom-3 right-3 sm:bottom-4 sm:right-4 lg:bottom-8 lg:-right-8 bg-surface rounded-xl sm:rounded-2xl shadow-xl p-2.5 sm:p-4 flex items-center gap-2 sm:gap-3">
            <div
              class="w-9 h-9 sm:w-12 sm:h-12 rounded-full bg-gradient-primary flex items-center justify-center text-white font-bold text-sm sm:text-base">
              <?php echo esc_html(mb_substr($last_winner_name, 0, 1)); ?>
            </div>
            <div>
              <span class="block text-xs sm:text-sm font-semibold text-text-primary">
                <?php _e('Last Winner:', 'nera-competitions'); ?>
              </span>
              <span class="block text-[10px] sm:text-xs text-text-secondary">
                <?php echo esc_html($last_winner_name); ?>
              </span>
            </div>
            <span class="text-success text-base sm:text-xl">✓</span>
          </div>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>

</section>