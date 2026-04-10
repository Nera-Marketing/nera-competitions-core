<?php
/**
 * About/Who We Are Section - Premium Blue Design
 *
 * A refined 50/50 layout matching the site's blue color palette.
 * Fully editable through ACF Pro with sophisticated visual design.
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

// Get ACF fields with fallbacks
$badge = get_field('about_badge') ?: __('Who We Are', 'nera-competitions');
$title =
  get_field('about_title') ?: __('Your Trusted Partner in Premium Giveaways', 'nera-competitions');
$subtitle =
  get_field('about_subtitle') ?:
  __('Bringing dreams to life, one competition at a time.', 'nera-competitions');
$description =
  get_field('about_description') ?:
  __(
    'We\'re passionate about creating life-changing moments through fair, transparent, and exciting prize competitions. With over 150 winners and £2M+ in prizes awarded, we\'ve built a trusted community of dreamers and winners.',
    'nera-competitions',
  );
$features = get_field('about_features');
$show_cta = get_field('about_show_cta');
$cta_text = get_field('about_cta_text') ?: __('Learn More About Us', 'nera-competitions');
$cta_url = get_field('about_cta_url') ?: '/about/';
$image = get_field('about_image');
$image_position = get_field('about_image_position') ?: 'right';
$background = get_field('about_background') ?: 'gradient';

// Determine background class
$bg_class = 'bg-surface';
if ($background === 'gray') {
  $bg_class = 'bg-gray-50';
} elseif ($background === 'gradient') {
  $bg_class = 'bg-gradient-to-b from-white via-indigo-50/30 to-white';
}

// Determine layout order classes
$text_order = $image_position === 'right' ? 'order-1' : 'order-2';
$image_order = $image_position === 'right' ? 'order-2' : 'order-1';
?>

<section class="about-section-premium py-16 lg:py-28 <?php echo esc_attr(
  $bg_class,
); ?> relative overflow-hidden"
  id="about-us" data-aos="fade-up">

  <!-- Decorative Background Elements -->
  <div class="absolute inset-0 pointer-events-none overflow-hidden opacity-30">
    <!-- Blue accent orb -->
    <div
      class="absolute <?php echo $image_position === 'right'
        ? 'top-1/4 -left-32'
        : 'top-1/4 -right-32'; ?> w-96 h-96 bg-gradient-to-br from-primary/20 via-indigo-500/10 to-transparent rounded-full blur-3xl">
    </div>
    <!-- Secondary orb -->
    <div
      class="absolute bottom-1/4 <?php echo $image_position === 'right'
        ? 'right-0'
        : 'left-0'; ?> w-80 h-80 bg-gradient-to-tl from-primary/10 to-transparent rounded-full blur-3xl">
    </div>
  </div>

  <div class="max-w-6xl mx-auto px-4 lg:px-8 relative z-10">

    <!-- 50/50 Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">

      <!-- Text Content Column -->
      <div class="<?php echo esc_attr($text_order); ?> space-y-6 about-text-content">

        <!-- Badge with blue accent -->
        <?php if ($badge): ?>
          <div
            class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full bg-gradient-to-r from-primary/10 to-indigo-500/10 border border-primary/20 about-badge">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="text-primary">
              <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"
                fill="currentColor" />
            </svg>
            <span class="text-xs font-bold uppercase tracking-widest text-primary"><?php echo esc_html(
              $badge,
            ); ?></span>
          </div>
        <?php endif; ?>

        <!-- Title -->
        <h2
          class="font-heading text-4xl lg:text-5xl xl:text-6xl font-black text-text-primary leading-[1.1] tracking-tight about-title">
          <?php echo esc_html($title); ?>
        </h2>

        <!-- Subtitle with blue accent -->
        <?php if ($subtitle): ?>
          <p class="text-xl lg:text-2xl font-semibold text-primary leading-relaxed about-subtitle">
            <?php echo esc_html($subtitle); ?>
          </p>
        <?php endif; ?>

        <!-- Decorative divider -->
        <div class="flex items-center gap-3 about-divider">
          <div class="w-12 h-px bg-gradient-to-r from-primary to-transparent"></div>
          <div class="w-2 h-2 rounded-full bg-primary"></div>
          <div class="w-12 h-px bg-gradient-to-l from-primary to-transparent"></div>
        </div>

        <!-- Description -->
        <div class="text-lg text-text-secondary leading-relaxed prose prose-lg max-w-none about-description">
          <?php echo wp_kses_post($description); ?>
        </div>

        <!-- Feature List -->
        <?php if ($features && is_array($features)): ?>
          <ul class="space-y-3.5 pt-2 about-features">
            <?php foreach ($features as $index => $feature): ?>
              <li class="flex items-start gap-3.5 group about-feature-item"
                style="animation-delay: <?php echo $index * 0.1; ?>s;">
                <span
                  class="flex-shrink-0 w-7 h-7 rounded-full bg-primary/10 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white group-hover:scale-110 transition-all duration-300">
                  <span class="material-symbols-outlined" style="font-size: 16px; font-variation-settings: 'FILL' 1;">
                    <?php echo esc_html($feature['icon'] ?: 'check_circle'); ?>
                  </span>
                </span>
                <span class="text-base lg:text-lg font-medium text-text-primary pt-0.5">
                  <?php echo esc_html($feature['text']); ?>
                </span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>

        <!-- CTA Button -->
        <?php if ($show_cta): ?>
          <div class="mt-5 about-cta">
            <a href="<?php echo esc_url($cta_url); ?>"
              class="inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-primary to-indigo-600 text-white font-semibold rounded-xl shadow-lg shadow-primary/25 hover:shadow-xl hover:shadow-primary/30 hover:-translate-y-1 transition-all duration-300 group relative overflow-hidden">
              <span class="relative z-10"><?php echo esc_html($cta_text); ?></span>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                class="relative z-10 transition-transform duration-300 group-hover:translate-x-1">
                <path d="M5 12h14M12 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              <!-- Shine effect -->
              <span
                class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700"></span>
            </a>
          </div>
        <?php endif; ?>

      </div>

      <!-- Image Column -->
      <div class="<?php echo esc_attr($image_order); ?> relative about-image-container">

        <!-- Decorative frame element -->
        <div class="absolute -inset-6 border border-primary/10 rounded-3xl transform rotate-2 about-frame"></div>
        <div class="absolute -inset-4 bg-gradient-to-br from-primary/5 to-indigo-500/5 rounded-3xl blur-2xl opacity-60">
        </div>

        <!-- Main image container -->
        <div
          class="relative rounded-2xl overflow-hidden shadow-2xl shadow-primary/10 group border-4 border-white about-image-wrapper">
          <?php if ($image && isset($image['url'])): ?>
            <div class="aspect-[4/5] overflow-hidden">
              <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr(
  $image['alt'] ?: $title,
); ?>"
                class="w-full h-full object-cover transition-transform duration-[800ms] ease-out group-hover:scale-105 about-image"
                loading="lazy" />
            </div>
          <?php else: ?>
            <!-- Placeholder -->
            <div
              class="aspect-[4/5] bg-gradient-to-br from-gray-100 via-indigo-50/30 to-gray-50 flex items-center justify-center">
              <div class="text-center space-y-4">
                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="0.5"
                  class="text-gray-300 mx-auto">
                  <rect x="3" y="3" width="18" height="18" rx="2" />
                  <circle cx="8.5" cy="8.5" r="1.5" />
                  <polyline points="21 15 16 10 5 21" />
                </svg>
                <p class="text-sm text-gray-400 font-medium">Image placeholder</p>
              </div>
            </div>
          <?php endif; ?>

          <!-- Subtle gradient overlay -->
          <div
            class="absolute inset-0 bg-gradient-to-t from-primary/5 via-transparent to-transparent pointer-events-none">
          </div>
        </div>

        <!-- Floating credibility badge -->
        <div
          class="absolute -bottom-8 -left-8 bg-surface rounded-2xl p-6 lg:p-8 shadow-2xl shadow-primary/10 border border-indigo-100 hidden lg:block about-floating-badge">
          <div class="flex items-center gap-5">
            <div
              class="w-16 h-16 bg-gradient-to-br from-primary to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-primary/30">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                <path
                  d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
              </svg>
            </div>
            <div>
              <div class="text-4xl font-black text-text-primary font-heading">150<span class="text-primary">+</span>
              </div>
              <div class="text-xs font-bold text-text-secondary uppercase tracking-wider mt-1">Happy Winners</div>
            </div>
          </div>
        </div>

        <!-- Decorative corner accent -->
        <div
          class="absolute top-4 right-4 w-16 h-16 border-t-2 border-r-2 border-primary/20 rounded-tr-2xl about-corner-accent">
        </div>

      </div>

    </div>

  </div>
</section>