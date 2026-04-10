<?php
/**
 * Follow Us on Socials Section
 *
 * Social media follow banner for the homepage
 * Features dark blue gradient background with social platform links
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

// Section content from ACF
$banner = [
  'badge' => get_field('promo_badge') ?: __('Stay Connected', 'nera-competitions'),
  'title' => get_field('promo_title') ?: __('Follow us on socials', 'nera-competitions'),
  'description' =>
    get_field('promo_description') ?:
    __('Follow us for updates, new competitions and giveaways.', 'nera-competitions'),
  'background_image' =>
    get_field('promo_bg_image') ?:
    'https://images.unsplash.com/photo-1603584173870-7f23fdae1b7a?w=1200&h=400&fit=crop',
];

// Social links from ACF repeater
$social_links = get_field('promo_social_links');

// Fallback defaults if no social links configured
if (empty($social_links)) {
  $social_links = [
    ['platform' => 'facebook', 'url' => '#'],
    ['platform' => 'instagram', 'url' => '#'],
  ];
}

/**
 * Get SVG icon markup for a social platform
 */
if (!function_exists('nera_get_social_icon')):
  function nera_get_social_icon($platform)
  {
    $icons = [
      'facebook' =>
        '<svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
      'instagram' =>
        '<svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z"/></svg>',
      'twitter' =>
        '<svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
      'youtube' =>
        '<svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>',
      'tiktok' =>
        '<svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>',
    ];

    return isset($icons[$platform]) ? $icons[$platform] : '';
  }
endif;

/**
 * Get display label for a social platform
 */
if (!function_exists('nera_get_social_label')):
  function nera_get_social_label($platform)
  {
    $labels = [
      'facebook' => __('Facebook', 'nera-competitions'),
      'instagram' => __('Instagram', 'nera-competitions'),
      'twitter' => __('Twitter/X', 'nera-competitions'),
      'youtube' => __('YouTube', 'nera-competitions'),
      'tiktok' => __('TikTok', 'nera-competitions'),
    ];

    return isset($labels[$platform]) ? $labels[$platform] : ucfirst($platform);
  }
endif;
?>

<section class="promo-banner-section py-8 lg:py-12" id="social-follow" data-aos="fade-up">
  <div class="max-w-6xl mx-auto px-4 lg:px-8">

    <!-- Banner Container -->
    <div class="promo-banner relative overflow-hidden rounded-2xl lg:rounded-3xl bg-[#1a1a4e]">

      <!-- Background with Gradient Overlay -->
      <div class="absolute inset-0">
        <img src="<?php echo esc_url($banner['background_image']); ?>" alt=""
          class="w-full h-full object-cover object-center" aria-hidden="true">
        <!-- Dark Blue Gradient Overlay -->
        <div class="absolute inset-0 bg-gradient-to-r from-[#1a1a4e]/95 via-[#1a1a4e]/90 to-[#1a1a4e]/80"></div>
      </div>

      <!-- Content -->
      <div class="relative z-10 px-8 py-12 lg:px-12 lg:py-16 flex flex-col items-center text-center">

        <!-- Badge -->
        <span
          class="inline-block px-4 py-1.5 mb-6 text-xs font-bold text-white uppercase tracking-wider bg-primary rounded-full">
          <?php echo esc_html($banner['badge']); ?>
        </span>

        <!-- Title -->
        <h2 class="font-heading text-3xl lg:text-4xl xl:text-5xl font-bold mb-4 leading-tight !text-white">
          <?php echo esc_html($banner['title']); ?>
        </h2>

        <!-- Description -->
        <p class="text-base lg:text-lg text-white/80 mb-10 leading-relaxed max-w-lg">
          <?php echo esc_html($banner['description']); ?>
        </p>

        <!-- Social Links -->
        <?php if (!empty($social_links)): ?>
          <div class="flex flex-wrap justify-center gap-5 sm:gap-8">
            <?php foreach ($social_links as $link):

              $platform = $link['platform'] ?? '';
              $url = $link['url'] ?? '#';
              $icon = nera_get_social_icon($platform);
              $label = nera_get_social_label($platform);

              if (empty($platform)) {
                continue;
              }
              ?>
              <a href="<?php echo esc_url($url); ?>"
                target="_blank"
                rel="noopener noreferrer"
                class="group flex flex-col items-center gap-3 transition-all duration-300 hover:-translate-y-1"
                aria-label="<?php echo esc_attr(
                  sprintf(__('Follow us on %s', 'nera-competitions'), $label),
                ); ?>">

                <!-- Icon Container -->
                <span
                  class="flex items-center justify-center w-14 h-14 rounded-full bg-surface/10 backdrop-blur-sm text-white group-hover:bg-surface/20 group-hover:scale-110 transition-all duration-300">
                  <?php echo $icon; ?>
                </span>

                <!-- Label -->
                <span class="text-sm font-medium text-white/80 group-hover:text-white transition-colors duration-300">
                  <?php echo esc_html($label); ?>
                </span>

              </a>
            <?php
            endforeach; ?>
          </div>
        <?php endif; ?>

      </div>

    </div>

  </div>
</section>
