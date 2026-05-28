<?php
/**
 * How It Works Template Part - Enhanced Premium Version
 *
 * Features a stunning step-by-step process section with:
 * - Animated connecting path with flowing dots
 * - Glassmorphic step cards with hover effects
 * - Scroll-triggered staggered animations
 * - Floating particle background
 *
 * @package Nera_Competitions
 *
 * Available variables:
 * $args['steps']           - Array of steps (optional, uses default if not provided)
 * $args['title']           - Section title (optional)
 * $args['subtitle']        - Section subtitle (optional)
 * $args['badge']           - Badge label next to pulse dot (optional)
 * $args['cta_button_text'] - CTA button label (optional)
 * $args['cta_url']         - CTA button URL (optional)
 * $args['cta_target']      - Link target e.g. _blank (optional)
 * $args['cta_footer_text'] - Small line under CTA (optional)
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

// Get arguments with defaults
$title = isset($args['title']) ? $args['title'] : __('How It Works', 'nera-competitions');
$subtitle = isset($args['subtitle'])
  ? $args['subtitle']
  : __('Win amazing prizes in just 4 simple steps', 'nera-competitions');
$badge = isset($args['badge'])
  ? $args['badge']
  : __('Simple Process', 'nera-competitions');

$default_steps = function_exists('nera_get_hiw_default_steps') ? nera_get_hiw_default_steps() : [];
$steps =
  isset($args['steps']) && is_array($args['steps']) && !empty($args['steps'])
    ? $args['steps']
    : $default_steps;

$cta_button_text = isset($args['cta_button_text'])
  ? $args['cta_button_text']
  : __('Start Winning Today', 'nera-competitions');
$cta_url = isset($args['cta_url']) ? $args['cta_url'] : '';
if ($cta_url === '' && function_exists('wc_get_page_id')) {
  $cta_url = (string) get_permalink(wc_get_page_id('shop'));
}
if ($cta_url === '') {
  $cta_url = home_url('/');
}
$cta_footer_text = isset($args['cta_footer_text'])
  ? $args['cta_footer_text']
  : __('Join thousands of winners • New competitions added daily', 'nera-competitions');
$cta_target = isset($args['cta_target']) ? (string) $args['cta_target'] : '';
?>

<section class="how-it-works-section relative py-20 lg:py-32 overflow-hidden" id="how-it-works">

  <!-- Animated Background -->
  <div class="absolute inset-0 bg-gradient-to-b from-gray-50 via-surface to-gray-50"></div>

  <!-- Floating Particles Background -->
  <div class="how-it-works-particles absolute inset-0 overflow-hidden pointer-events-none">
    <div class="particle particle-1"></div>
    <div class="particle particle-2"></div>
    <div class="particle particle-3"></div>
    <div class="particle particle-4"></div>
    <div class="particle particle-5"></div>
    <div class="particle particle-6"></div>
  </div>

  <!-- Decorative Grid Pattern -->
  <div class="absolute inset-0 opacity-[0.02]"
    style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23000000&quot; fill-opacity=&quot;1&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');">
  </div>

  <div class="max-w-7xl mx-auto px-4 lg:px-0 relative z-10">

    <!-- Section Header with Animation -->
    <div class="text-center mb-16 lg:mb-24 how-it-works-header" data-animate="fade-in-up">

      <!-- Badge -->
      <div class="inline-flex items-center gap-2 px-4 py-2 mb-6 rounded-full bg-primary/5 border border-primary/10">
        <span class="relative flex h-2 w-2">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
          <span class="relative inline-flex rounded-full h-2 w-2 bg-primary"></span>
        </span>
        <span class="text-sm font-semibold text-primary uppercase tracking-wider"><?php echo esc_html(
          $badge,
        ); ?></span>
      </div>

      <h2 class="font-heading text-4xl lg:text-5xl xl:text-6xl font-bold text-text-primary mb-6 leading-tight">
        <?php echo esc_html($title); ?>
      </h2>

      <p class="text-lg lg:text-xl text-text-secondary text-center leading-relaxed">
        <?php echo esc_html($subtitle); ?>
      </p>
    </div>

    <!-- Steps Container -->
    <div class="relative">

      <!-- Animated Connection Path (Desktop) -->
      <div class="hidden lg:block absolute top-[120px] left-0 right-0 z-0">
        <svg class="w-full h-20" viewBox="0 0 1200 80" preserveAspectRatio="none" fill="none"
          xmlns="http://www.w3.org/2000/svg">
          <!-- Background Path -->
          <path class="how-it-works-path-bg"
            d="M 150 40 Q 300 40 400 40 Q 500 40 600 40 Q 700 40 800 40 Q 900 40 1050 40" stroke="#E2E8F0"
            stroke-width="3" stroke-linecap="round" stroke-dasharray="8 8" fill="none" />
          <!-- Animated Path -->
          <path class="how-it-works-path-animated"
            d="M 150 40 Q 300 40 400 40 Q 500 40 600 40 Q 700 40 800 40 Q 900 40 1050 40" stroke="url(#pathGradient)"
            stroke-width="3" stroke-linecap="round" fill="none" />
          <!-- Gradient Definition -->
          <defs>
            <linearGradient id="pathGradient" x1="0%" y1="0%" x2="100%" y2="0%">
              <stop offset="0%" stop-color="#8B5CF6" />
              <stop offset="33%" stop-color="#3B82F6" />
              <stop offset="66%" stop-color="#10B981" />
              <stop offset="100%" stop-color="#F59E0B" />
            </linearGradient>
          </defs>
          <!-- Flowing Dots -->
          <circle class="flow-dot flow-dot-1" r="6" fill="#8B5CF6">
            <animateMotion dur="4s" repeatCount="indefinite"
              path="M 150 40 Q 300 40 400 40 Q 500 40 600 40 Q 700 40 800 40 Q 900 40 1050 40" />
          </circle>
          <circle class="flow-dot flow-dot-2" r="6" fill="#3B82F6">
            <animateMotion dur="4s" repeatCount="indefinite" begin="1s"
              path="M 150 40 Q 300 40 400 40 Q 500 40 600 40 Q 700 40 800 40 Q 900 40 1050 40" />
          </circle>
          <circle class="flow-dot flow-dot-3" r="6" fill="#10B981">
            <animateMotion dur="4s" repeatCount="indefinite" begin="2s"
              path="M 150 40 Q 300 40 400 40 Q 500 40 600 40 Q 700 40 800 40 Q 900 40 1050 40" />
          </circle>
        </svg>
      </div>

      <!-- Steps Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
        <?php
        $step_number = 1;
        foreach ($steps as $step):

          $delay = ($step_number - 1) * 150;
          $color = isset($step['color']) ? $step['color'] : 'from-primary to-primary-dark';
          $bg_color = isset($step['bg_color']) ? $step['bg_color'] : 'bg-primary/10';
          ?>
          <div class="how-it-works-step relative group" data-animate="fade-in-up" data-delay="<?php echo $delay; ?>">
            <!-- Step Card -->
            <div class="relative h-full pt-4 pr-4">

              <!-- Step Number Badge (positioned outside card to prevent clipping) -->
              <div
                class="absolute top-0 right-0 w-12 h-12 bg-gradient-to-br <?php echo esc_attr(
                  $color,
                ); ?> rounded-2xl flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-primary/25 rotate-12 group-hover:rotate-0 transition-transform duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] z-20 will-change-transform">
                <?php echo esc_html($step_number); ?>
              </div>

              <!-- Glassmorphic Card -->
              <div
                class="how-it-works-card relative h-full bg-surface/90 backdrop-blur-sm rounded-3xl p-8 border border-gray-100 shadow-lg shadow-gray-100/40 transition-[transform,box-shadow,border-color] duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:shadow-xl group-hover:shadow-gray-200/60 group-hover:-translate-y-1 group-hover:border-gray-200 overflow-hidden [contain:layout_paint]">

                <!-- Shine Effect (short, avoids long paint on hover) -->
                <div
                  class="pointer-events-none absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] overflow-hidden rounded-3xl">
                  <div
                    class="absolute inset-0 bg-gradient-to-r from-transparent via-white/15 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] motion-reduce:transition-none motion-reduce:translate-x-0 motion-reduce:group-hover:translate-x-0">
                  </div>
                </div>

                <!-- Icon Container -->
                <div class="relative mb-6">
                  <div
                    class="w-20 h-20 <?php echo esc_attr(
                      $bg_color,
                    ); ?> rounded-2xl flex items-center justify-center group-hover:scale-105 transition-transform duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] will-change-transform">
                    <div class="text-text-secondary group-hover:text-primary transition-colors duration-250 ease-[cubic-bezier(0.22,1,0.36,1)]">
                      <?php echo $step['icon']; ?>
                    </div>
                  </div>
                  <!-- Subtle ring on hover (no infinite ping — cheaper than animate-ping-slow) -->
                  <div
                    class="pointer-events-none absolute inset-0 w-20 h-20 rounded-2xl ring-0 ring-primary/25 group-hover:ring-2 transition-[box-shadow] duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]"
                    aria-hidden="true"></div>
                </div>

                <!-- Title -->
                <h3 class="text-xl font-bold text-text-primary mb-3 group-hover:text-text-primary transition-colors duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]">
                  <?php echo esc_html($step['title']); ?>
                </h3>

                <!-- Description -->
                <p class="text-text-secondary leading-relaxed text-sm">
                  <?php echo esc_html($step['description']); ?>
                </p>

              </div>

            </div>

            <!-- Mobile Connector -->
            <?php if ($step_number < count($steps)): ?>
              <div class="flex justify-center py-6 lg:hidden">
                <div class="flex flex-col items-center gap-1">
                  <div class="w-0.5 h-8 bg-gradient-to-b <?php echo esc_attr(
                    $color,
                  ); ?> rounded-full"></div>
                  <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                  </svg>
                </div>
              </div>
            <?php endif; ?>

          </div>
          <?php $step_number++;
        endforeach;
        ?>
      </div>
    </div>

    <!-- CTA Section -->
    <div class="text-center mt-16 lg:mt-24" data-animate="fade-in-up" data-delay="600">
      <a href="<?php echo esc_url($cta_url); ?>"
        class="inline-flex mb-5 items-center gap-3 px-8 py-4 bg-gradient-to-r from-primary to-primary-dark text-white font-semibold rounded-2xl shadow-lg shadow-primary/25 hover:shadow-xl hover:shadow-primary/30 hover:-translate-y-1 transition-all duration-300 group"<?php echo $cta_target === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
        <span><?php echo esc_html($cta_button_text); ?></span>
        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24"
          stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
        </svg>
      </a>
      <p class="text-sm text-text-secondary">
        <?php echo esc_html($cta_footer_text); ?>
      </p>
    </div>

  </div>
</section>
