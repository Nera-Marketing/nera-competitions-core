<?php
/**
 * FAQ Section Template Part
 *
 * Frequently Asked Questions accordion
 * Refactored to use AlpineJS for smooth interactions
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

// FAQ data
$faqs = get_field('faq_list');

if (empty($faqs)) {
  $faqs = [
    [
      'question' => __('Is it legal to enter UK?', 'nera-competitions'),
      'answer' => __(
        'Yes, our competitions are fully legal in the UK. We operate as a prize competition which requires entrants to demonstrate skill by answering a question correctly. This is fully compliant with UK gambling laws and we are registered with the relevant authorities.',
        'nera-competitions',
      ),
    ],
    [
      'question' => __('How do you draw winners?', 'nera-competitions'),
      'answer' => __(
        'All our draws are conducted live on Facebook and YouTube using a certified random number generator. The draw process is completely transparent and all entries are verified before the winner is announced. You can watch previous draws on our social media channels.',
        'nera-competitions',
      ),
    ],
    [
      'question' => __('When are the draws done?', 'nera-competitions'),
      'answer' => __(
        'Draws are typically conducted when all tickets have sold or when the competition end date is reached. We announce draw times in advance via email and social media so you never miss the excitement. Most draws happen weekly on Sunday evenings.',
        'nera-competitions',
      ),
    ],
    [
      'question' => __('How do I receive my prize?', 'nera-competitions'),
      'answer' => __(
        'For physical prizes, we arrange delivery to your door completely free of charge. Cash prizes are transferred directly to your bank account within 48 hours. For larger prizes like cars, we can either deliver to your address or arrange collection from a convenient location.',
        'nera-competitions',
      ),
    ],
  ];
}
?>

<section class="faq-section py-20 bg-surface" id="faq" data-aos="fade-up">
  <div class="max-w-7xl mx-auto px-4 lg:px-0">

    <!-- Section Header -->
    <div class="text-center mb-12">
      <h2 class="font-heading text-4xl font-semibold text-text-primary leading-tight tracking-tight">
        <?php echo esc_html(
          get_field('faq_title') ?: __('Frequently Asked Questions', 'nera-competitions'),
        ); ?>
      </h2>
    </div>

    <!-- FAQ Accordion -->
    <div x-data="{ activeAccordion: null }">
      <?php foreach ($faqs as $index => $faq): ?>
        <div class="faq-item border-b border-gray-200">
          <button
            class="faq-item__question w-full flex justify-between items-center py-6 bg-transparent border-none cursor-pointer text-left focus:outline-none"
            @click="activeAccordion = activeAccordion === <?php echo $index; ?> ? null : <?php echo $index; ?>"
            :aria-expanded="activeAccordion === <?php echo $index; ?>"
            aria-controls="faq-answer-<?php echo esc_attr($index); ?>">
            <span class="text-base font-semibold text-text-primary"><?php echo esc_html(
              $faq['question'],
            ); ?></span>
            <span class="faq-item__icon transition-transform duration-300"
              :class="{ 'rotate-180': activeAccordion === <?php echo $index; ?> }">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 9l6 6 6-6" />
              </svg>
            </span>
          </button>
          <div class="faq-item__answer overflow-hidden" id="faq-answer-<?php echo esc_attr(
            $index,
          ); ?>"
            x-show="activeAccordion === <?php echo $index; ?>" x-collapse x-cloak>
            <p class="pb-6 text-text-secondary leading-relaxed"><?php echo esc_html(
              $faq['answer'],
            ); ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Contact Link -->
    <div class="text-center mt-12">
      <p class="text-text-secondary">
        <?php _e('Still have questions?', 'nera-competitions'); ?>
        <a href="<?php echo esc_url(get_permalink(get_page_by_path('contact'))); ?>"
          class="inline-flex items-center gap-2 text-primary font-semibold ml-2 hover:gap-3 transition-all duration-200">
          <?php _e('Contact our support team', 'nera-competitions'); ?>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M5 12h14M12 5l7 7-7 7" />
          </svg>
        </a>
      </p>
    </div>

  </div>
</section>