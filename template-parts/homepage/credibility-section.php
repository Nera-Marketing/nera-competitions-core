<?php
/**
 * Credibility Section Template Part
 *
 * Generic trust bar with icon + label blocks.
 * Content editable via ACF repeater, falls back to defaults.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

// Default trust items (used when ACF repeater is empty)
$default_items = [
  ['icon' => 'lock', 'label' => __('Secure Payments', 'nera-competitions')],
  ['icon' => 'verified', 'label' => __('UK Compliant', 'nera-competitions')],
  ['icon' => 'visibility', 'label' => __('Transparent Draws', 'nera-competitions')],
  ['icon' => 'emoji_events', 'label' => __('Real Winners', 'nera-competitions')],
  ['icon' => 'headset_mic', 'label' => __('Fast Support', 'nera-competitions')],
];

// Get items from ACF repeater or use defaults
$credibility_items = [];
if (function_exists('have_rows') && have_rows('credibility_items')) {
  while (have_rows('credibility_items')) {
    the_row();
    $credibility_items[] = [
      'icon' => get_sub_field('icon') ?: 'check_circle',
      'label' => get_sub_field('label') ?: '',
    ];
  }
}

if (empty($credibility_items)) {
  $credibility_items = $default_items;
}
?>

<section class="credibility-section py-5 lg:py-6 bg-surface border-b border-gray-100" data-aos="fade-up">
	<div class="container mx-auto px-4 lg:px-0">
		<div class="flex flex-wrap items-center justify-around gap-x-8 gap-y-4 md:gap-x-12 lg:gap-x-16">

			<?php foreach ($credibility_items as $item):
     if (empty($item['label'])) {
       continue;
     } ?>
				<div class="credibility-item flex items-center gap-2.5 group">
					<span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-primary/10 text-primary transition-all duration-300 group-hover:bg-primary/15 group-hover:scale-105">
						<span class="material-symbols-outlined text-lg" style="font-variation-settings: 'FILL' 1;">
							<?php echo esc_html($item['icon']); ?>
						</span>
					</span>
					<span class="text-sm font-medium text-text-secondary tracking-wide transition-colors duration-300 group-hover:text-text-primary">
						<?php echo esc_html($item['label']); ?>
					</span>
				</div>
			<?php
   endforeach; ?>

		</div>
	</div>
</section>
