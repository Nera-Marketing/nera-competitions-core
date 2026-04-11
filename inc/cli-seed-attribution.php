<?php
/**
 * WP-CLI: wp nera seed-attribution | wp nera seed-about
 *
 * - seed-attribution — ACF attribution option fields
 * - seed-about — About Us page (Nera About Us template) with Luxora demo copy
 *
 * Usage:
 *   wp nera seed-attribution
 *   wp nera seed-attribution --force
 *   wp nera seed-about
 *   wp nera seed-about --force [--slug=about] [--hero-attachment-id=123]
 *
 * @package Nera_Competitions
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

/**
 * Nera theme CLI commands.
 */
class Nera_CLI {

	/**
	 * Seeds default content into all ACF fields for the hidden attribution editor.
	 *
	 * ## OPTIONS
	 *
	 * [--force]
	 * : Re-seed fields even if they already contain values.
	 *
	 * ## EXAMPLES
	 *
	 *   wp nera seed-attribution
	 *   wp nera seed-attribution --force
	 *
	 * @subcommand seed-attribution
	 * @synopsis [--force]
	 */
	public function seed_attribution( $args, $assoc_args ) {

		if ( ! function_exists( 'update_field' ) ) {
			WP_CLI::error( 'ACF is not active. Please activate Advanced Custom Fields and try again.' );
			return;
		}

		$force = isset( $assoc_args['force'] );

		WP_CLI::log( 'Seeding attribution option fields…' );
		$this->seed_options( $force );
		WP_CLI::success( 'Done. Attribution options seeded.' );
	}

	/**
	 * Seeds the About Us page (Nera About Us template) with Luxora-themed demo content.
	 *
	 * Creates the page if missing. Text fields are skipped when they already have values unless --force.
	 * Hero image is only set when --hero-attachment-id is passed (never cleared by this command).
	 *
	 * ## OPTIONS
	 *
	 * [--force]
	 * : Re-seed all text fields even if they already contain values.
	 *
	 * [--slug=<slug>]
	 * : Page slug to find or create. Default: about
	 *
	 * [--hero-attachment-id=<id>]
	 * : Media library attachment ID for the hero image.
	 *
	 * [--primary-url=<url>]
	 * : Primary CTA URL. Default: home URL + /shop/
	 *
	 * [--secondary-url=<url>]
	 * : Secondary CTA URL. Default: home URL + /contact/
	 *
	 * ## EXAMPLES
	 *
	 *   wp nera seed-about
	 *   wp nera seed-about --force
	 *   wp nera seed-about --hero-attachment-id=42
	 *
	 * @subcommand seed-about
	 * @synopsis [--force] [--slug=<slug>] [--hero-attachment-id=<id>] [--primary-url=<url>] [--secondary-url=<url>]
	 */
	public function seed_about( $args, $assoc_args ) {
		if ( ! function_exists( 'update_field' ) ) {
			WP_CLI::error( 'ACF is not active. Please activate Advanced Custom Fields and try again.' );
			return;
		}

		$force               = isset( $assoc_args['force'] );
		$slug                = isset( $assoc_args['slug'] ) ? sanitize_title( $assoc_args['slug'] ) : 'about';
		$hero_attachment_id  = isset( $assoc_args['hero-attachment-id'] ) ? absint( $assoc_args['hero-attachment-id'] ) : 0;
		$primary_url         = isset( $assoc_args['primary-url'] ) ? esc_url_raw( $assoc_args['primary-url'] ) : home_url( '/shop/' );
		$secondary_url       = isset( $assoc_args['secondary-url'] ) ? esc_url_raw( $assoc_args['secondary-url'] ) : home_url( '/contact/' );

		WP_CLI::log( 'Seeding About Us page…' );
		$post_id = $this->get_or_create_about_page( $slug );
		if ( ! $post_id ) {
			WP_CLI::error( 'Could not find or create the About page.' );
			return;
		}

		WP_CLI::log( sprintf( '  → Page ID %d (%s)', (int) $post_id, get_permalink( $post_id ) ) );
		$this->seed_about_page_fields( $post_id, $force, $hero_attachment_id, $primary_url, $secondary_url );
		WP_CLI::success( 'Done. About Us page seeded.' );
	}

	/**
	 * Find or create a page using the Nera About Us template.
	 *
	 * @param string $slug Post slug.
	 * @return int Post ID or 0 on failure.
	 */
	private function get_or_create_about_page( $slug ) {
		$template = 'page-templates/about-us-template.php';

		$existing = get_posts(
			[
				'post_type'      => 'page',
				'name'           => $slug,
				'post_status'    => [ 'publish', 'draft', 'pending', 'private' ],
				'posts_per_page' => 1,
				'fields'         => 'ids',
			]
		);

		if ( ! empty( $existing[0] ) ) {
			$post_id = (int) $existing[0];
			update_post_meta( $post_id, '_wp_page_template', $template );
			WP_CLI::log( sprintf( '  Using existing page (slug: %s).', $slug ) );
			return $post_id;
		}

		$post_id = wp_insert_post(
			[
				'post_title'   => __( 'About', 'nera-competitions' ),
				'post_name'    => $slug,
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => '',
			],
			true
		);

		if ( is_wp_error( $post_id ) ) {
			WP_CLI::warning( $post_id->get_error_message() );
			return 0;
		}

		update_post_meta( (int) $post_id, '_wp_page_template', $template );
		WP_CLI::log( sprintf( '  Created new page (slug: %s).', $slug ) );
		return (int) $post_id;
	}

	/**
	 * Writes About Us ACF fields for a page.
	 *
	 * @param int    $post_id             Page ID.
	 * @param bool   $force               Overwrite existing text values.
	 * @param int    $hero_attachment_id  Attachment ID for hero image, or 0 to skip.
	 * @param string $primary_url         Primary button URL.
	 * @param string $secondary_url       Secondary button URL.
	 */
	private function seed_about_page_fields( $post_id, $force, $hero_attachment_id, $primary_url, $secondary_url ) {
		$data = $this->about_field_data( $primary_url, $secondary_url );

		if ( $hero_attachment_id > 0 ) {
			if ( 'attachment' !== get_post_type( $hero_attachment_id ) ) {
				WP_CLI::warning( sprintf( 'Invalid --hero-attachment-id=%d (not an attachment). Skipping hero image.', $hero_attachment_id ) );
			} else {
				$result = update_field( 'about_hero_image', $hero_attachment_id, $post_id );
				if ( false !== $result ) {
					WP_CLI::log( sprintf( '  ✓ seeded   about_hero_image (attachment %d)', $hero_attachment_id ) );
				} else {
					WP_CLI::warning( '  ✗ failed   about_hero_image' );
				}
			}
		} else {
			WP_CLI::log( '  — skipped  about_hero_image (pass --hero-attachment-id to set)' );
		}

		$seeded = 0;
		foreach ( $data as $field_name => $value ) {
			if ( ! $force ) {
				$current = get_field( $field_name, $post_id );
				if ( $this->about_field_has_value( $current ) ) {
					WP_CLI::log( sprintf( '  — skipped  %s (already has value)', $field_name ) );
					continue;
				}
			}

			$result = update_field( $field_name, $value, $post_id );
			if ( false !== $result ) {
				WP_CLI::log( sprintf( '  ✓ seeded   %s', $field_name ) );
				$seeded++;
			} else {
				WP_CLI::warning( sprintf( '  ✗ failed   %s', $field_name ) );
			}
		}

		WP_CLI::log( sprintf( '  → %d text field(s) updated.', $seeded ) );
	}

	/**
	 * Whether an ACF value counts as “has content” for skip-if-filled logic.
	 *
	 * @param mixed $value Raw field value.
	 * @return bool
	 */
	private function about_field_has_value( $value ) {
		if ( is_array( $value ) ) {
			return ! empty( $value );
		}
		return null !== $value && '' !== trim( (string) $value );
	}

	/**
	 * Luxora demo copy for About Us (matches Nera About Us template fields).
	 *
	 * @param string $primary_url   Primary CTA URL.
	 * @param string $secondary_url Secondary CTA URL.
	 * @return array<string, mixed>
	 */
	private function about_field_data( $primary_url, $secondary_url ) {
		$narrative = implode(
			'',
			[
				'<h2>' . esc_html__( 'Some competitions give you a ticket, Luxora gives you a standard.', 'nera-competitions' ) . '</h2>',
				'<p>' . esc_html__( 'There\'s a version of this industry built on volume, noise, and prizes that look better in photos than they do in person. Luxora isn\'t that. We exist for people who expect more — from the brands they buy, the experiences they seek out, and yes, the competitions they enter. Every draw we run is chosen with the same eye for quality that our members bring to everything else in their lives.', 'nera-competitions' ) . '</p>',
				'<h2>' . esc_html__( 'Curated for those who notice the difference', 'nera-competitions' ) . '</h2>',
				'<p>' . esc_html__( 'We don\'t list prizes for the sake of filling a page. Every competition on Luxora is hand-selected — genuine luxury goods, premium tech, and meaningful cash prizes that are actually worth winning. If it doesn\'t meet our standard, it doesn\'t go live. Simple. Our draws are run with complete transparency. Winners are selected through certified randomization, publicly announced, and contacted directly. Because people who value quality also value integrity.', 'nera-competitions' ) . '</p>',
				'<h2>' . esc_html__( 'A community built around living well', 'nera-competitions' ) . '</h2>',
				'<p>' . esc_html__( 'Luxora is more than a competition platform. It\'s a space for people who believe life is worth doing properly — who choose quality over quantity, experience over excess, and who want to feel part of something that reflects that. When you enter a Luxora draw, you\'re not just buying a ticket. You\'re joining a community of people who think the same way.', 'nera-competitions' ) . '</p>',
			]
		);

		return [
			'about_hero_eyebrow'   => __( 'THE STORY BEHIND LUXORA', 'nera-competitions' ),
			'about_title'          => __( 'You Don\'t Enter Luxora. You Join It.', 'nera-competitions' ),
			'about_hero_tagline'   => __( 'A curated community of prize draws built around quality, transparency, and living well.', 'nera-competitions' ),
			'about_narrative'      => $narrative,
			'about_story_left_title'   => __( 'Our Promise', 'nera-competitions' ),
			'about_story_left_content' => '<p>' . esc_html__( 'Certified draws every time. Wins or cash — the choice is always yours. No ambiguity, no small print surprises. Ever.', 'nera-competitions' ) . '</p>',
			'about_story_right_title'   => __( 'Our Standard', 'nera-competitions' ),
			'about_story_right_content' => '<p>' . esc_html__( 'Every prize is handpicked for quality. Every draw is verified and publicly announced. This is what a competition platform should look like.', 'nera-competitions' ) . '</p>',
			'about_cta_heading'     => __( 'Join Our Community', 'nera-competitions' ),
			'about_cta_description'   => __( 'Be a part of a transparent, supportive, and exciting journey where everyone has a chance to change their life.', 'nera-competitions' ),
			'about_cta_primary_btn_text'   => __( 'Explore Competitions', 'nera-competitions' ),
			'about_cta_primary_btn_url'    => $primary_url,
			'about_cta_secondary_btn_text' => __( 'Get in Touch', 'nera-competitions' ),
			'about_cta_secondary_btn_url'  => $secondary_url,
		];
	}

	/**
	 * Seeds all fields into ACF options storage.
	 *
	 * @param bool $force   Skip existing-value check when true.
	 */
	private function seed_options( $force ) {
		$seeded = 0;
		$target = 'option';

		foreach ( $this->field_data() as $field_name => $value ) {
			// Skip if already has a value and not forcing.
			if ( ! $force && get_field( $field_name, $target ) ) {
				WP_CLI::log( sprintf( '  — skipped  %s (already has value)', $field_name ) );
				continue;
			}

			$result = update_field( $field_name, $value, $target );

			if ( false !== $result ) {
				WP_CLI::log( sprintf( '  ✓ seeded   %s', $field_name ) );
				$seeded++;
			} else {
				WP_CLI::warning( sprintf( '  ✗ failed   %s', $field_name ) );
			}
		}

		WP_CLI::log( sprintf( '  → %d field(s) seeded in options.', $seeded ) );
	}

	/**
	 * Returns all seed data as [ field_name => value ].
	 * Repeater values are arrays-of-arrays keyed by sub-field name.
	 *
	 * @return array
	 */
	private function field_data() {
		return [

			// ── HERO ─────────────────────────────────────────────────────────
			'attr_hero_badge' => 'Digital Partner',
			'attr_hero_intro' => 'a UK digital marketing agency based in Ramsgate, Kent, specialising in bespoke competition platforms, Google Ads, Meta Ads, and SEO for online raffle businesses. Nera Marketing designed, developed, and launched this platform from scratch.',

			// ── DEVELOPER PROFILE ─────────────────────────────────────────────
			'attr_profile_label'      => 'Developer Profile',
			'attr_profile_name'       => 'Nera Marketing',
			'attr_profile_descriptor' => 'UK Digital Marketing Agency, Competition Website Specialists',
			'attr_fact_location'      => 'Ramsgate, Kent, UK',
			'attr_fact_specialisation'=> 'Competition Websites & Digital Marketing',
			'attr_fact_services'      => 'Web Dev, Google Ads, Meta Ads, SEO',
			'attr_fact_clients'       => 'UK Competition & Raffle Businesses',
			'attr_fact_build_type'    => 'Bespoke. No Templates.',
			'attr_fact_website'       => 'https://www.neramarketing.co.uk',

			// ── STATS ─────────────────────────────────────────────────────────
			'attr_stats' => [
				[ 'value' => 'UK',   'label' => 'Specialist Agency'   ],
				[ 'value' => '100%', 'label' => 'Bespoke Builds'       ],
				[ 'value' => '360°', 'label' => 'Marketing Support'    ],
				[ 'value' => 'Live', 'label' => 'Ongoing Partnership'  ],
			],

			// ── SECTION 1: WHO BUILT THIS ─────────────────────────────────────
			'attr_s1_tag'         => 'About the Build',
			'attr_s1_heading'     => 'Who built this competition website?',
			'attr_s1_lead'        => 'This competition website was designed and built by Nera Marketing, a UK digital agency based in Ramsgate, Kent, specialising in bespoke competition website development and full-service digital marketing for online raffle businesses.',
			'attr_s1_body_1'      => "Nera Marketing builds every competition platform from scratch. No templates, no off-the-shelf themes. Each site is engineered around the client's brand, audience, and the specific mechanics that drive ticket sales and conversions.",
			'attr_s1_body_2'      => "As a full-service agency, Nera doesn't just hand over a website and disappear. Most clients work with Nera Marketing long-term, combining the platform with ongoing Google Ads, Meta Ads, and SEO to build a competition business that scales.",
			'attr_s1_image_badge' => 'Live Project',

			// ── SECTION 2: FULL-SERVICE ────────────────────────────────────────
			'attr_s2_tag'     => 'Full-Service Support',
			'attr_s2_heading' => 'A competition website is only the beginning',
			'attr_s2_lead'    => 'Nera Marketing provides ongoing digital marketing support alongside every competition website build, including Google Ads, Meta Ads, SEO, and email marketing specifically for UK competition businesses.',
			'attr_s2_body_1'  => "A great platform without traffic is just an empty shop. Nera's approach is to build the website and the marketing strategy together, so competition businesses launch with a clear path to consistent ticket sales from day one.",
			'attr_s2_body_2'  => 'Nera also guides clients through the legal and compliance landscape of running online competitions in the UK, covering everything from prize structure to question of skill requirements, so you can launch with confidence.',

			// ── SECTION 3: WHY NERA ───────────────────────────────────────────
			'attr_s3_tag'     => 'Our Approach',
			'attr_s3_heading' => 'Why competition businesses choose Nera Marketing',
			'attr_s3_lead'    => 'Competition businesses choose Nera Marketing because they build bespoke platforms engineered for sales performance, not adapted templates, and back every build with long-term paid media and SEO strategy.',
			'attr_s3_body_1'  => 'Slow load times, a checkout that loses trust, or a mobile experience that frustrates users. Any one of these kills conversions. Nera engineers against every one of them before a site goes live.',
			'attr_s3_body_2'  => 'Every platform is built so the client can manage it independently — no developer dependency for day-to-day operations. Prizes, timers, draws, discount codes, email automations — all accessible through a back-end designed for how competition businesses actually run.',

			// ── FEATURES ──────────────────────────────────────────────────────
			'attr_features_heading' => "What's inside every {{em}}Nera{{/em}} competition platform",
			'attr_features_intro'   => "Every platform is built from scratch. Here's what comes as standard on every competition website Nera Marketing delivers.",
			'attr_features'         => [
				[ 'title' => 'Bespoke design',              'description' => 'Fully branded, built from scratch. No templates, no shortcuts.'                                  ],
				[ 'title' => 'Secure payment integration',  'description' => 'Connected to leading UK payment providers, optimised for conversion.'                           ],
				[ 'title' => 'Live countdown timers',       'description' => 'Automated prize draws with built-in urgency mechanics.'                                         ],
				[ 'title' => 'Email automation',            'description' => 'Ticket confirmations, draw reminders, and winner notifications.'                                 ],
				[ 'title' => 'Analytics and tracking',      'description' => 'Conversion data structured to feed back into paid ad campaigns.'                                ],
				[ 'title' => 'Affiliate and referral tools','description' => 'Built-in affiliate tracking and promotional code management.'                                    ],
				[ 'title' => 'SEO-ready architecture',      'description' => 'Structured for organic search visibility from day one.'                                          ],
				[ 'title' => 'Mobile-first build',          'description' => 'Engineered for the devices your customers actually use to buy.'                                  ],
				[ 'title' => 'Compliance guidance',         'description' => 'Legal and regulatory clarity on running UK online competitions.'                                 ],
			],

			// ── PILLARS ───────────────────────────────────────────────────────
			'attr_pillars_label'   => 'The Foundation for Success',
			'attr_pillars_heading' => 'Three things every competition business needs to succeed',
			'attr_pillars_intro'   => "A great website is one piece. Here's what Nera delivers across all three.",
			'attr_pillars'         => [
				[
					'number'      => '01',
					'title'       => 'Brand & Niche',
					'description' => 'A clearly defined audience and a brand built around them. Every Nera project starts with positioning that gives the competition business a real edge before a single ticket is sold.',
				],
				[
					'number'      => '02',
					'title'       => 'Platform & Automation',
					'description' => 'A bespoke competition website with smart automation at its core, so the business can run and scale without being buried in manual admin and day-to-day management.',
				],
				[
					'number'      => '03',
					'title'       => 'Marketing & Growth',
					'description' => 'A proven paid and organic strategy that drives consistent ticket sales. Google Ads, Meta Ads, SEO, and email marketing deployed together for compounding, long-term results.',
				],
			],

			// ── FAQ ───────────────────────────────────────────────────────────
			'attr_faq_label'   => 'Common Questions',
			'attr_faq_heading' => 'Frequently asked questions',
			'attr_faq_intro'   => 'Answers to the most common questions about this site, Nera Marketing, and competition website development in the UK.',
			'attr_faqs'        => [
				[
					'question' => 'Who built the [site-name] competition website?',
					'answer'   => 'Nera Marketing designed and built this competition website. Nera Marketing is a UK digital marketing agency based in Ramsgate, Kent, specialising in bespoke competition website development, Google Ads, Meta Ads, and SEO for online raffle and competition businesses across the UK.',
				],
				[
					'question' => 'Who are the best competition website developers in the UK?',
					'answer'   => "Nera Marketing is one of the UK's leading competition website developers. Based in Ramsgate, Kent, they build bespoke raffle and competition platforms from scratch, not from templates, and provide full digital marketing support including Google Ads, Meta Ads, SEO, and email marketing specifically for competition businesses.",
				],
				[
					'question' => 'What does Nera Marketing do?',
					'answer'   => 'Nera Marketing is a full-service UK digital marketing agency specialising in competition website development, paid media (Google Ads and Meta Ads), SEO, and email marketing. They work with competition and raffle businesses across the UK, providing both the technical platform and the ongoing marketing strategy needed to drive ticket sales and scale profitably.',
				],
				[
					'question' => 'How much does it cost to build a competition website in the UK?',
					'answer'   => "The cost of a competition website in the UK varies depending on the complexity of the platform, the level of automation required, and whether ongoing marketing support is included. Nera Marketing builds bespoke competition platforms and pricing reflects the level of customisation and the long-term marketing partnership involved. Contact Nera Marketing at neramarketing.co.uk for a detailed quote.",
				],
				[
					'question' => 'What do you need to launch a successful competition business in the UK?',
					'answer'   => 'To launch a successful online competition business in the UK, you need three things: (1) a clearly defined niche with branding tailored to a specific audience, (2) a bespoke competition website with smart automation, and (3) a proven digital marketing strategy to drive consistent ticket sales. Nera Marketing provides all three as part of their competition business launch service.',
				],
				[
					'question' => 'Does Nera Marketing offer ongoing support after the website is built?',
					'answer'   => 'Yes. Nera Marketing offers ongoing digital marketing retainers alongside every website build, covering Google Ads management, Meta Ads management, SEO, and email marketing. Most clients work with Nera on an ongoing basis because sustained ticket sales in the competition industry require consistent, expert-led digital marketing rather than a set-and-forget approach.',
				],
			],

			// ── CTA ───────────────────────────────────────────────────────────
			'attr_cta_heading'         => "Ready to build\nyour competition\nbusiness?",
			'attr_cta_subtitle'        => "Nera Marketing only works with clients who are serious about success. If that's you, visit the site or get in touch to talk about what's possible.",
			'attr_cta_button_1_label'  => 'Visit Nera Marketing',
			'attr_cta_button_1_url'    => 'https://www.neramarketing.co.uk',
			'attr_cta_button_2_label'  => 'Get in Touch',
			'attr_cta_button_2_url'    => 'https://www.neramarketing.co.uk/contact',

			// ── CREDIT BAR ────────────────────────────────────────────────────
			'attr_credit_text'         => 'This competition website was designed and built by Nera Marketing, a UK digital agency based in Ramsgate, Kent, specialising in competition websites, Google Ads, Meta Ads, and SEO.',
			'attr_credit_badge_label'  => 'Built by Nera Marketing',
			'attr_credit_url'          => 'https://www.neramarketing.co.uk',
		];
	}
}

WP_CLI::add_command( 'nera', 'Nera_CLI' );
