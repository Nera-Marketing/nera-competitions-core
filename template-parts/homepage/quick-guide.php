<?php
/**
 * Quick Guide - How to Play Section
 *
 * A minimalist 3-step guide based on the Stitch "Competition Home Minimalist Light" design.
 * Clean, simple layout with icons and hover effects.
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

// Quick guide steps
$steps = get_field('guide_steps');

if (empty($steps)) {
  $steps = [
    [
      'number' => '01',
      'icon' =>
        '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h6"/><circle cx="12" cy="12" r="2"/><path d="m16 8-2.6 2.6"/><circle cx="18" cy="6" r="3"/></svg>',
      'title' => __('Select Prize', 'nera-competitions'),
      'description' => __(
        'Browse our active luxury giveaways and choose the prize you want to win most.',
        'nera-competitions',
      ),
    ],
    [
      'number' => '02',
      'icon' =>
        '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg>',
      'title' => __('Choose Tickets', 'nera-competitions'),
      'description' => __(
        'Select how many entries you want. Each ticket increases your chances of holding the winning number.',
        'nera-competitions',
      ),
    ],
    [
      'number' => '03',
      'icon' =>
        '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
      'title' => __('Wait for Draw', 'nera-competitions'),
      'description' => __(
        'Answer the skill-based question correctly and wait for the live draw. Good luck!',
        'nera-competitions',
      ),
    ],
  ];
}
?>

<section class="quick-guide-section py-16 lg:py-24 bg-gradient-to-b from-white to-gray-50" id="quick-guide"
  data-aos="fade-up">
  <div class="max-w-6xl mx-auto px-4 lg:px-8">

    <!-- Section Header -->
    <div class="text-center mb-12 lg:mb-16">
      <span
        class="inline-block px-4 py-1.5 mb-4 text-sm font-semibold text-primary bg-primary/5 rounded-full border border-primary/10">
        <?php esc_html_e('Quick Guide', 'nera-competitions'); ?>
      </span>
      <h2 class="font-heading text-3xl lg:text-4xl xl:text-5xl font-bold text-text-primary mb-4">
        <?php echo esc_html(get_field('guide_title') ?: __('How to Play', 'nera-competitions')); ?>
      </h2>
      <p class="text-lg text-text-secondary text-center">
        <?php echo esc_html(
          get_field('guide_subtitle') ?:
          __('Win your dream prizes in just three simple steps', 'nera-competitions'),
        ); ?>
      </p>
    </div>

    <!-- Steps Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
      <?php foreach ($steps as $index => $step): ?>
        <div class="quick-guide-card group relative">
          <!-- Card -->
          <div
            class="relative h-full bg-surface rounded-2xl p-8 border border-gray-100 shadow-sm transition-all duration-300 hover:shadow-xl hover:border-primary/20 hover:-translate-y-1">

            <!-- Step Number -->
            <div
              class="absolute -top-3 -right-3 w-12 h-12 bg-primary text-white rounded-xl flex items-center justify-center font-bold text-sm shadow-lg shadow-primary/25 group-hover:scale-110 transition-transform duration-300">
              <?php echo esc_html($step['number']); ?>
            </div>

            <!-- Icon -->
            <div
              class="w-16 h-16 mb-6 rounded-xl bg-primary/5 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-all duration-300">
              <?php echo $step['icon']; ?>
            </div>

            <!-- Title -->
            <h3 class="text-xl font-bold text-text-primary mb-3">
              <?php echo esc_html($step['title']); ?>
            </h3>

            <!-- Description -->
            <p class="text-text-secondary leading-relaxed">
              <?php echo esc_html($step['description']); ?>
            </p>

          </div>

          <!-- Connector Arrow (visible on desktop, between cards) -->
          <?php if ($index < count($steps) - 1): ?>
            <div class="hidden md:flex absolute top-1/2 -right-4 lg:-right-5 transform -translate-y-1/2 z-10">
              <svg class="w-6 h-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- CTA Button -->
    <div class="text-center mt-12">
      <a href="<?php echo esc_url(home_url('/all-competitions')); ?>"
        class="inline-flex items-center gap-2 px-8 py-4 bg-primary text-white font-semibold rounded-xl shadow-lg shadow-primary/25 hover:bg-primary-dark hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
        <span><?php esc_html_e('Browse Competitions', 'nera-competitions'); ?></span>
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
        </svg>
      </a>
    </div>

  </div>
</section>