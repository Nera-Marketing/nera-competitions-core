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
    'stats'                => 'template-parts/homepage/stats-section',
    'featured_competitions'=> 'template-parts/homepage/featured-competitions',
    'winners'              => 'template-parts/homepage/winners-section',
    'about'                => 'template-parts/homepage/about-section',
    'categories'           => 'template-parts/homepage/categories-competitions',
  ];

  $component_map = [
    'hero'        => 'HomepageHero',
    'credibility' => 'Credibility',
    'testimonials'=> 'Testimonials',
    'quick_guide' => 'QuickGuide',
    'faq'         => 'Faq',
    'promo_banner'=> 'PromoBanner',
  ];

  $sections = get_field('homepage_sections');

  if (is_array($sections) && !empty($sections)) {
    foreach ($sections as $row) {
      if (empty($row['show_section'])) {
        continue;
      }
      $slug = isset($row['section']) ? $row['section'] : '';

      if (isset($component_map[$slug])) {
        nera_render_component($component_map[$slug]);
        continue;
      }

      $part = isset($homepage_section_map[$slug]) ? $homepage_section_map[$slug] : null;
      if ($part) {
        get_template_part($part);
      }
    }
  } else {
    nera_render_component('HomepageHero');
    nera_render_component('Credibility');
    get_template_part('template-parts/homepage/featured-competitions');
    nera_render_component('PromoBanner');
    nera_render_component('Testimonials');
    get_template_part('template-parts/homepage/winners-section');
    nera_render_component('QuickGuide');
    get_template_part('template-parts/homepage/about-section');
    get_template_part('template-parts/homepage/categories-competitions');
    nera_render_component('Faq');
  }
  ?>

</main>

<?php get_footer();
?>
