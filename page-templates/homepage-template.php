<?php
/**
 * Template Name: Nera Homepage
 * Template Post Type: page
 *
 * Premium competition/giveaway homepage template
 * Matches the Boutique Giveaways design
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

get_header();
?>

<main id="main" class="nera-homepage" role="main">

  <?php
  $homepage_section_map = [
    'credibility' => 'template-parts/homepage/credibility-section',
    'stats' => 'template-parts/homepage/stats-section',
    'featured_competitions' => 'template-parts/homepage/featured-competitions',
    'promo_banner' => 'template-parts/homepage/promo-banner',
    'testimonials' => 'template-parts/homepage/testimonials-section',
    'winners' => 'template-parts/homepage/winners-section',
    'quick_guide' => 'template-parts/homepage/quick-guide',
    'about' => 'template-parts/homepage/about-section',
    'categories' => 'template-parts/homepage/categories-competitions',
    'faq' => 'template-parts/homepage/faq-section',
  ];

  $sections = get_field('homepage_sections');

  if (is_array($sections) && !empty($sections)) {
    foreach ($sections as $row) {
      if (empty($row['show_section'])) {
        continue;
      }
      $slug = isset($row['section']) ? $row['section'] : '';

      if ($slug === 'hero') {
        nera_render_component('HomepageHero');
        continue;
      }

      $part = isset($homepage_section_map[$slug]) ? $homepage_section_map[$slug] : null;
      if ($part) {
        get_template_part($part);
      }
    }
  } else {
    nera_render_component('HomepageHero');
    get_template_part('template-parts/homepage/credibility-section');
    get_template_part('template-parts/homepage/featured-competitions');
    get_template_part('template-parts/homepage/promo-banner');
    get_template_part('template-parts/homepage/testimonials-section');
    get_template_part('template-parts/homepage/winners-section');
    get_template_part('template-parts/homepage/quick-guide');
    get_template_part('template-parts/homepage/about-section');
    get_template_part('template-parts/homepage/categories-competitions');
    get_template_part('template-parts/homepage/faq-section');
  }
  ?>

</main>

<?php get_footer();
?>
