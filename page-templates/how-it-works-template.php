<?php
/**
 * Template Name: How It Works Page
 * Template Post Type: page
 *
 * Guide to the draw process, entries, and fairness.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

get_header();

$hero_title = get_field('hiw_hero_title') ?: get_the_title();
$hero_subtitle =
  get_field('hiw_hero_subtitle') ?:
  __('Win your dream prizes in just 4 simple steps', 'nera-competitions');
$hero_badge = get_field('hiw_hero_badge') ?: __('Simple & Fair', 'nera-competitions');

$acf_hero_steps = get_field('hiw_hero_steps');
$hero_steps = nera_get_hiw_merged_hero_steps($acf_hero_steps);

$hero_cta_button_text = __('Start Winning Today', 'nera-competitions');
$hero_cta_url = '';
if (function_exists('wc_get_page_id')) {
  $hero_cta_url = (string) get_permalink(wc_get_page_id('shop'));
}
if ($hero_cta_url === '') {
  $hero_cta_url = home_url('/');
}
$hero_cta_target = '';

$hiw_cta_button_link = get_field('hiw_cta_button_link');
if (is_array($hiw_cta_button_link) && !empty($hiw_cta_button_link['url'])) {
  $parsed_cta_url = esc_url_raw($hiw_cta_button_link['url']);
  if ($parsed_cta_url !== '') {
    $hero_cta_url = $parsed_cta_url;
  }
  if (!empty($hiw_cta_button_link['title'])) {
    $hero_cta_button_text = $hiw_cta_button_link['title'];
  }
  if (!empty($hiw_cta_button_link['target']) && $hiw_cta_button_link['target'] === '_blank') {
    $hero_cta_target = '_blank';
  }
}

$hiw_cta_footer_text = get_field('hiw_cta_footer_text');
$hero_cta_footer_text =
  $hiw_cta_footer_text !== null && $hiw_cta_footer_text !== ''
    ? $hiw_cta_footer_text
    : __('Join thousands of winners • New competitions added daily', 'nera-competitions');

$draw_title = get_field('hiw_draw_title') ?: __('The Draw Process', 'nera-competitions');
$draw_content = get_field('hiw_draw_content');
$draw_image = get_field('hiw_draw_image');
$draw_image_url = '';
$draw_image_alt = '';
if (is_array($draw_image) && !empty($draw_image['url'])) {
  $draw_image_url = $draw_image['url'];
  $draw_image_alt = isset($draw_image['alt']) ? $draw_image['alt'] : '';
} elseif (is_string($draw_image) && $draw_image !== '') {
  $draw_image_url = $draw_image;
  $draw_image_alt = '';
}

$hiw_draw_eyebrow = get_field('hiw_draw_eyebrow');
$draw_eyebrow =
  $hiw_draw_eyebrow !== null && $hiw_draw_eyebrow !== ''
    ? $hiw_draw_eyebrow
    : __('Fair & Transparent', 'nera-competitions');

$hiw_draw_placeholder_title = get_field('hiw_draw_placeholder_title');
$draw_placeholder_title =
  $hiw_draw_placeholder_title !== null && $hiw_draw_placeholder_title !== ''
    ? $hiw_draw_placeholder_title
    : __('Live Draw Streams', 'nera-competitions');

$hiw_draw_placeholder_text = get_field('hiw_draw_placeholder_text');
$draw_placeholder_text =
  $hiw_draw_placeholder_text !== null && $hiw_draw_placeholder_text !== ''
    ? $hiw_draw_placeholder_text
    : __('Watch us live on Facebook and Instagram', 'nera-competitions');

$hiw_draw_placeholder_icon = get_field('hiw_draw_placeholder_icon');
$draw_placeholder_icon_raw = is_string($hiw_draw_placeholder_icon) ? trim($hiw_draw_placeholder_icon) : '';
$draw_placeholder_icon = $draw_placeholder_icon_raw !== '' ? $draw_placeholder_icon_raw : 'videocam';

$postal_title = get_field('hiw_postal_title') ?: __('Free Postal Entry Route', 'nera-competitions');
$hiw_postal_intro = get_field('hiw_postal_intro');
$postal_intro =
  $hiw_postal_intro !== null && $hiw_postal_intro !== ''
    ? $hiw_postal_intro
    : __(
      'We offer a free entry route via post for all of our competitions.',
      'nera-competitions',
    );
$acf_postal_steps = get_field('hiw_postal_steps');
$postal_note = get_field('hiw_postal_note');

$default_postal_steps = [
  [
    'number' => '1',
    'title' => __('Include Your Details', 'nera-competitions'),
    'icon' => 'contact_page',
    'text' => __(
      'Send your name, address, date of birth, contact phone number, and the name of the competition you wish to enter.',
      'nera-competitions',
    ),
  ],
  [
    'number' => '2',
    'title' => __('Send Your Postcard', 'nera-competitions'),
    'icon' => 'mail',
    'text' => __(
      'Send your entry on an unenclosed postcard via first or second class post to our registered business address.',
      'nera-competitions',
    ),
  ],
  [
    'number' => '3',
    'title' => __('We Process It', 'nera-competitions'),
    'icon' => 'verified_user',
    'text' => __(
      'Once received, your entry will be processed and included in the draw just like a paid entry.',
      'nera-competitions',
    ),
  ],
];

$postal_steps = [];
if (!empty($acf_postal_steps) && is_array($acf_postal_steps)) {
  foreach ($acf_postal_steps as $i => $step) {
    if (!empty($step['text'])) {
      $default = isset($default_postal_steps[$i]) ? $default_postal_steps[$i] : $default_postal_steps[0];
      $postal_steps[] = [
        'number' => !empty($step['number']) ? $step['number'] : $i + 1,
        'title' => !empty($step['title']) ? $step['title'] : $default['title'],
        'icon' => !empty($step['icon']) ? $step['icon'] : $default['icon'],
        'text' => $step['text'],
      ];
    }
  }
}
if (empty($postal_steps)) {
  $postal_steps = $default_postal_steps;
}

$trans_title = get_field('hiw_transparency_title') ?: __('Transparency & Fairness', 'nera-competitions');
$hiw_transparency_subtitle = get_field('hiw_transparency_subtitle');
$trans_subtitle =
  $hiw_transparency_subtitle !== null && $hiw_transparency_subtitle !== ''
    ? $hiw_transparency_subtitle
    : __(
      'We pride ourselves on being a registered UK business that operates with full integrity and a passion for giving back.',
      'nera-competitions',
    );
$acf_trans_features = get_field('hiw_transparency_features');

$default_trans_features = [
  [
    'icon' => 'verified_user',
    'title' => __('Fully Insured', 'nera-competitions'),
    'description' => __(
      'We are a legally registered UK business, fully insured and compliant with all regulations.',
      'nera-competitions',
    ),
  ],
  [
    'icon' => 'diversity_3',
    'title' => __('Community Focused', 'nera-competitions'),
    'description' => __(
      'Our mission is to support our community and provide life-changing opportunities for everyone.',
      'nera-competitions',
    ),
  ],
  [
    'icon' => 'shield_with_heart',
    'title' => __('Secure & Safe', 'nera-competitions'),
    'description' => __(
      'We use industry-standard security protocols to ensure your data and entries are always protected.',
      'nera-competitions',
    ),
  ],
];

$trans_features = [];
if (!empty($acf_trans_features) && is_array($acf_trans_features)) {
  foreach ($acf_trans_features as $i => $feature) {
    $default = isset($default_trans_features[$i]) ? $default_trans_features[$i] : $default_trans_features[0];
    $trans_features[] = [
      'icon' => !empty($feature['icon']) ? $feature['icon'] : $default['icon'],
      'title' => !empty($feature['title']) ? $feature['title'] : $default['title'],
      'description' => !empty($feature['description']) ? $feature['description'] : $default['description'],
    ];
  }
} else {
  $trans_features = $default_trans_features;
}
?>

<main id="main" class="how-it-works-page bg-background-light font-body" role="main">

  <?php
  get_template_part('template-parts/how-it-works', null, [
    'title' => $hero_title,
    'subtitle' => $hero_subtitle,
    'badge' => $hero_badge,
    'steps' => $hero_steps,
    'cta_button_text' => $hero_cta_button_text,
    'cta_url' => $hero_cta_url,
    'cta_target' => $hero_cta_target,
    'cta_footer_text' => $hero_cta_footer_text,
  ]);
  ?>

  <section
    class="py-20 lg:py-32 relative overflow-hidden border-t border-gray-200 bg-surface"
    aria-labelledby="hiw-draw-heading">
    <div class="max-w-7xl mx-auto px-4 lg:px-8 relative z-10">
      <div class="grid lg:grid-cols-2 gap-16 items-center">
        <div data-aos="fade-right">
          <span
            class="inline-block bg-primary/10 text-primary py-1.5 px-4 rounded-full text-[11px] tracking-[2px] uppercase mb-6 font-medium">
            <?php echo esc_html($draw_eyebrow); ?>
          </span>
          <h2 id="hiw-draw-heading" class="font-heading text-4xl lg:text-5xl text-text-primary mb-8 leading-tight">
            <?php echo esc_html($draw_title); ?>
          </h2>
          <div
            class="max-w-none prose prose-sm prose-headings:font-heading prose-p:text-text-secondary prose-strong:text-primary leading-relaxed">
            <?php if ($draw_content): ?>
              <?php echo wp_kses_post($draw_content); ?>
            <?php else: ?>
              <p>
                <?php
                echo wp_kses_post(
                  __(
                    'Our draws are conducted with absolute transparency. We use the <strong>Google Random Number Generator</strong> to ensure every entry has an equal and fair chance of winning.',
                    'nera-competitions',
                  ),
                );
                ?>
              </p>
              <p>
                <?php
                esc_html_e(
                  'Join us live on our social media channels for every draw! We broadcast the entire process in real-time, announcing winners as they happen and celebrating with our community.',
                  'nera-competitions',
                );
                ?>
              </p>
            <?php endif; ?>
          </div>
        </div>
        <div class="relative" data-aos="fade-left">
          <?php if (!empty($draw_image_url)): ?>
            <div class="rounded-3xl overflow-hidden shadow-2xl border border-gray-200">
              <img
                src="<?php echo esc_url($draw_image_url); ?>"
                alt="<?php echo esc_attr($draw_image_alt); ?>"
                class="w-full h-auto"
                loading="lazy"
                decoding="async" />
            </div>
          <?php else: ?>
            <div
              class="aspect-video bg-gray-100 rounded-3xl border border-gray-200 flex items-center justify-center">
              <div class="text-center p-8">
                <div
                  class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6">
                  <span class="material-symbols-outlined text-primary text-5xl" aria-hidden="true"><?php echo esc_html(
                    $draw_placeholder_icon,
                  ); ?></span>
                </div>
                <h3 class="text-xl font-bold text-text-primary mb-2">
                  <?php echo esc_html($draw_placeholder_title); ?>
                </h3>
                <p class="text-sm text-text-secondary">
                  <?php echo esc_html($draw_placeholder_text); ?>
                </p>
              </div>
            </div>
          <?php endif; ?>
          <div
            class="absolute -bottom-6 -right-6 w-32 h-32 bg-primary/10 blur-3xl rounded-full -z-10 pointer-events-none"
            aria-hidden="true"></div>
        </div>
      </div>
    </div>
  </section>

  <section class="py-20 lg:py-32 relative bg-background-dark" aria-labelledby="hiw-postal-heading">
    <div class="max-w-7xl mx-auto px-4 lg:px-8 relative z-10">
      <div class="text-center mb-16" data-aos="fade-up">
        <h2 id="hiw-postal-heading" class="font-heading text-4xl lg:text-5xl text-white mb-6">
          <?php echo esc_html($postal_title); ?>
        </h2>
        <p class="text-white/80 text-lg">
          <?php echo esc_html($postal_intro); ?>
        </p>
      </div>

      <div class="grid md:grid-cols-3 gap-6 lg:gap-8">
        <?php
        $delay = 0;
        foreach ($postal_steps as $step):
          $icon = isset($step['icon']) ? $step['icon'] : 'mail';
          $title = isset($step['title']) ? $step['title'] : '';
          ?>
          <div
            class="group relative bg-surface/95 backdrop-blur-sm border border-white/10 rounded-3xl p-8 overflow-hidden shadow-lg transition-all duration-300 ease-out hover:-translate-y-2 hover:border-primary/30 hover:shadow-xl hover:shadow-primary/10"
            data-aos="fade-up"
            data-aos-delay="<?php echo esc_attr((string) $delay); ?>">
            <div
              class="absolute inset-0 opacity-0 group-hover:opacity-100 overflow-hidden rounded-3xl pointer-events-none transition-opacity duration-300">
              <div
                class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700 ease-out"></div>
            </div>
            <div class="relative z-10">
              <div class="flex items-start justify-between mb-6">
                <div class="relative">
                  <div
                    class="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center shrink-0 transition-transform duration-300 group-hover:scale-110 group-hover:bg-primary/15">
                    <span class="material-symbols-outlined text-primary text-4xl" aria-hidden="true"><?php echo esc_html(
                      $icon,
                    ); ?></span>
                  </div>
                </div>
                <div
                  class="w-10 h-10 rounded-full flex items-center justify-center font-heading font-bold text-sm shrink-0 bg-gradient-to-br from-primary to-primary-dark text-white rotate-12 group-hover:rotate-0 transition-transform duration-300">
                  <?php echo esc_html((string) $step['number']); ?>
                </div>
              </div>
              <?php if ($title): ?>
                <h3 class="text-lg font-bold text-text-primary mb-3">
                  <?php echo esc_html($title); ?>
                </h3>
              <?php endif; ?>
              <p class="text-text-secondary leading-relaxed text-sm">
                <?php echo esc_html($step['text']); ?>
              </p>
            </div>
          </div>
          <?php
          $delay += 100;
        endforeach;
        ?>
      </div>

      <div
        class="mt-12 p-6 bg-surface/90 backdrop-blur-sm border border-primary/20 rounded-2xl flex items-center gap-4"
        data-aos="fade-up"
        data-aos-delay="<?php echo esc_attr((string) $delay); ?>">
        <span class="material-symbols-outlined text-primary shrink-0 text-2xl" aria-hidden="true">info</span>
        <span class="text-sm text-text-secondary italic">
          <?php
          echo esc_html(
            $postal_note ?:
              __(
                'Please note: One entry per postcard. Entries must be received before the competition closes.',
                'nera-competitions',
              ),
          );
          ?>
        </span>
      </div>
    </div>
  </section>

  <section class="py-20 lg:py-32 relative overflow-hidden bg-background-light" aria-labelledby="hiw-trans-heading">
    <div class="max-w-7xl mx-auto px-4 lg:px-8 relative z-10">
      <div class="text-center mb-16" data-aos="fade-up">
        <h2 id="hiw-trans-heading" class="font-heading text-4xl lg:text-5xl text-text-primary mb-6">
          <?php echo esc_html($trans_title); ?>
        </h2>
        <p class="text-text-secondary text-lg max-w-2xl mx-auto">
          <?php echo esc_html($trans_subtitle); ?>
        </p>
      </div>

      <div class="grid md:grid-cols-3 gap-8">
        <?php foreach ($trans_features as $feature): ?>
          <div
            class="bg-surface border border-gray-200 p-8 rounded-3xl shadow-sm transition-all duration-500 hover:border-primary/30 hover:shadow-lg hover:-translate-y-1"
            data-aos="fade-up">
            <div class="text-primary mb-6">
              <span class="material-symbols-outlined text-5xl" aria-hidden="true"><?php echo esc_html(
                $feature['icon'],
              ); ?></span>
            </div>
            <h3 class="text-xl font-bold mb-4 text-text-primary">
              <?php echo esc_html($feature['title']); ?>
            </h3>
            <p class="text-sm text-text-secondary leading-relaxed">
              <?php echo esc_html($feature['description']); ?>
            </p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

</main>

<?php get_footer();
