<?php
/**
 * Stories of the Circle - Testimonials Section Template Part
 *
 * Winner stories and testimonials based on Stitch "Competition Home Minimalist Light" design
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

// Stories data matching Stitch design, allow override from ACF
$stories = get_field('testimonials_list');

if (empty($stories)) {
  $stories = [
    [
      'name' => 'James Robinson',
      'avatar' =>
        'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop&crop=face',
      'quote' =>
        "I still wake up and check my driveway to make sure it's not a dream. The whole process was seamless and the phone call from the team was the best moment of my year.",
      'prize' => 'BMW M4 Competition',
    ],
    [
      'name' => 'Sarah Lewis',
      'avatar' =>
        'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&h=150&fit=crop&crop=face',
      'quote' =>
        "Winning the £10k cash meant I could finally take my family on the holiday we've been putting off for five years. It truly changed everything for us this summer.",
      'prize' => '£10,000 Tax-Free Cash',
    ],
  ];
}
?>

<section class="stories-section py-16 lg:py-24" id="stories" data-aos="fade-up">
  <div class="max-w-6xl mx-auto px-4 lg:px-8">

    <!-- Section Header -->
    <div class="text-center mb-12 lg:mb-16">
      <span
        class="inline-block px-4 py-1.5 mb-4 text-sm font-semibold text-primary bg-primary/5 rounded-full border border-primary/10">
        <?php esc_html_e('Winner Stories', 'nera-competitions'); ?>
      </span>
      <h2 class="font-heading text-3xl lg:text-4xl xl:text-5xl font-bold text-text-primary mb-4">
        <?php echo esc_html(
          get_field('testimonials_title') ?: __('Stories of the Circle', 'nera-competitions'),
        ); ?>
      </h2>
      <p class="text-lg text-text-secondary text-center">
        <?php echo esc_html(
          get_field('testimonials_subtitle') ?:
          __(
            'Step inside the lives of those who dared to dream. Real people, life-changing moments.',
            'nera-competitions',
          ),
        ); ?>
      </p>
    </div>

    <!-- Stories Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
      <?php foreach ($stories as $index => $story): ?>
        <div class="story-card group relative">
          <!-- Card -->
          <div
            class="relative h-full bg-surface rounded-2xl p-8 lg:p-10 border border-gray-100 shadow-sm transition-all duration-300 hover:shadow-xl hover:border-primary/10 hover:-translate-y-1">

            <!-- Quote Icon -->
            <div class="absolute top-6 right-6 w-12 h-12 bg-primary/5 rounded-full flex items-center justify-center">
              <svg class="w-6 h-6 text-primary" viewBox="0 0 24 24" fill="currentColor">
                <path
                  d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
              </svg>
            </div>

            <!-- Quote Text -->
            <blockquote class="text-lg lg:text-xl text-text-primary leading-relaxed mb-8 pr-12">
              "<?php echo esc_html($story['quote']); ?>"
            </blockquote>

            <!-- Author Info -->
            <div class="flex items-center gap-4">
              <!-- Avatar -->
              <div
                class="w-14 h-14 rounded-full overflow-hidden bg-gradient-to-br from-primary to-primary-dark flex items-center justify-center shadow-lg shadow-primary/20">
                <?php if (!empty($story['avatar'])): ?>
                  <img src="<?php echo esc_url($story['avatar']); ?>" alt="<?php echo esc_attr(
  $story['name'],
); ?>"
                    class="w-full h-full object-cover">
                <?php else: ?>
                  <span class="text-xl font-bold text-white">
                    <?php echo esc_html(substr($story['name'], 0, 1)); ?>
                  </span>
                <?php endif; ?>
              </div>

              <!-- Name & Prize -->
              <div class="flex flex-col">
                <span class="text-base font-bold text-text-primary"><?php echo esc_html(
                  $story['name'],
                ); ?></span>
                <span class="text-sm text-text-secondary">
                  Won: <span class="text-primary font-medium"><?php echo esc_html(
                    $story['prize'],
                  ); ?></span>
                </span>
              </div>
            </div>

          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- CTA Link -->
    <div class="text-center mt-10">
      <a href="#winners"
        class="inline-flex items-center gap-2 text-primary font-semibold hover:gap-3 transition-all duration-200">
        <?php esc_html_e('Read more winner stories', 'nera-competitions'); ?>
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
        </svg>
      </a>
    </div>

  </div>
</section>