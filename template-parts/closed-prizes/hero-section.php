<?php
/**
 * Closed Prizes - Hero Section
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

$page_title   = get_the_title();
$page_excerpt = get_the_excerpt();

if (empty($page_excerpt)) {
  $page_excerpt = __('Browse our past competitions and see the lucky winners.', 'nera-competitions');
}
?>

<section class="py-16 md:py-20 bg-background-light">
  <div class="max-w-[1200px] mx-auto px-4 lg:px-8">
    <div class="text-center max-w-2xl mx-auto">

      <div class="flex justify-center mb-6" data-aos="fade-up">
        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gray-100 text-text-secondary text-sm font-semibold">
          <span class="material-symbols-outlined text-base" style="font-variation-settings:'FILL' 1">trophy</span>
          <?php esc_html_e('Past Competitions', 'nera-competitions'); ?>
        </span>
      </div>

      <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-text-primary tracking-tight mb-4"
        data-aos="fade-up" data-aos-delay="100">
        <?php echo esc_html($page_title); ?>
      </h1>

      <p class="text-lg md:text-xl text-text-secondary font-medium" data-aos="fade-up" data-aos-delay="200">
        <?php echo esc_html($page_excerpt); ?>
      </p>

    </div>
  </div>
</section>
