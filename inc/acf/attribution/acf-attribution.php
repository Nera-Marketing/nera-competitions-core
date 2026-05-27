<?php
/**
 * ACF Field Group: Nera Attribution Page
 *
 * Registers all editable fields for the "Competition Website by Nera Marketing"
 * page template. Organised into tabs matching each page section.
 *
 * @package Nera_Competitions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

if ( ! defined( 'NERA_ATTRIBUTION_OPTIONS_SLUG' ) ) {
	define( 'NERA_ATTRIBUTION_OPTIONS_SLUG', 'nera-attr-8d2k7v1q' );
}

function nera_register_attribution_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	if ( function_exists( 'acf_add_options_page' ) ) {
		acf_add_options_page( [
			'page_title' => __( 'Attribution Page Editor', 'nera-competitions' ),
			'menu_title' => __( 'Attribution Page Editor', 'nera-competitions' ),
			'menu_slug'  => NERA_ATTRIBUTION_OPTIONS_SLUG,
			'capability' => 'manage_options',
			'redirect'   => false,
			'position'   => null,
		] );
	}

	acf_add_local_field_group( [
		'key'                   => 'group_attribution_page',
		'title'                 => __( 'Attribution Page Content', 'nera-competitions' ),
		'fields'                => [

			// ── TAB: HERO ────────────────────────────────────────────────────
			[
				'key'       => 'field_attr_tab_hero',
				'label'     => __( 'Hero', 'nera-competitions' ),
				'type'      => 'tab',
				'placement' => 'top',
			],
			[
				'key'           => 'field_attr_hero_badge',
				'label'         => __( 'Badge Label', 'nera-competitions' ),
				'name'          => 'attr_hero_badge',
				'type'          => 'text',
				'instructions'  => __( 'Small label shown above the hero heading.', 'nera-competitions' ),
				'default_value' => 'Digital Partner',
				'placeholder'   => 'Digital Partner',
			],
			[
				'key'           => 'field_attr_hero_intro',
				'label'         => __( 'Intro Paragraph (after site name)', 'nera-competitions' ),
				'name'          => 'attr_hero_intro',
				'type'          => 'textarea',
				'instructions'  => __( 'Text that follows "[Site Name]\'s competition website was built by Nera Marketing," in the hero. Plain text only.', 'nera-competitions' ),
				'rows'          => 3,
				'default_value' => 'a UK digital marketing agency based in Ramsgate, Kent, specialising in bespoke competition platforms and SEO for online raffle businesses. Nera Marketing designed, developed, and launched this platform from scratch.',
			],

			// ── TAB: DEVELOPER PROFILE ────────────────────────────────────────
			[
				'key'       => 'field_attr_tab_profile',
				'label'     => __( 'Developer Profile', 'nera-competitions' ),
				'type'      => 'tab',
				'placement' => 'top',
			],
			[
				'key'           => 'field_attr_profile_label',
				'label'         => __( 'Section Label', 'nera-competitions' ),
				'name'          => 'attr_profile_label',
				'type'          => 'text',
				'default_value' => 'Developer Profile',
			],
			[
				'key'           => 'field_attr_profile_name',
				'label'         => __( 'Agency Name', 'nera-competitions' ),
				'name'          => 'attr_profile_name',
				'type'          => 'text',
				'default_value' => 'Nera Marketing',
			],
			[
				'key'           => 'field_attr_profile_descriptor',
				'label'         => __( 'Agency Descriptor', 'nera-competitions' ),
				'name'          => 'attr_profile_descriptor',
				'type'          => 'text',
				'default_value' => 'UK Digital Marketing Agency, Competition Website Specialists',
			],
			[
				'key'           => 'field_attr_fact_location',
				'label'         => __( 'Fact: Location', 'nera-competitions' ),
				'name'          => 'attr_fact_location',
				'type'          => 'text',
				'default_value' => '73 The Laurels, Manston Business Park, Kent, CT12 5NQ',
			],
			[
				'key'           => 'field_attr_fact_location_url',
				'label'         => __( 'Fact: Location Maps URL', 'nera-competitions' ),
				'name'          => 'attr_fact_location_url',
				'type'          => 'url',
				'instructions'  => __( 'Google Maps link for the location text. Leave empty to show plain text.', 'nera-competitions' ),
				'default_value' => 'https://www.google.com/maps/place/Nera+Marketing/@51.350383,1.3175544,18z/data=!4m6!3m5!1s0x4e7d95186ba33a85:0xd77ad4849b141ba9!8m2!3d51.350383!4d1.3199362!16s%2Fg%2F11y22tjdkq',
			],
			[
				'key'           => 'field_attr_fact_specialisation',
				'label'         => __( 'Fact: Specialisation', 'nera-competitions' ),
				'name'          => 'attr_fact_specialisation',
				'type'          => 'text',
				'default_value' => 'Competition Websites & Digital Marketing',
			],
			[
				'key'           => 'field_attr_fact_services',
				'label'         => __( 'Fact: Services', 'nera-competitions' ),
				'name'          => 'attr_fact_services',
				'type'          => 'text',
				'default_value' => 'Web Dev, SEO, Email',
			],
			[
				'key'           => 'field_attr_fact_clients',
				'label'         => __( 'Fact: Clients', 'nera-competitions' ),
				'name'          => 'attr_fact_clients',
				'type'          => 'text',
				'default_value' => 'UK Competition & Raffle Businesses',
			],
			[
				'key'           => 'field_attr_fact_build_type',
				'label'         => __( 'Fact: Build Type', 'nera-competitions' ),
				'name'          => 'attr_fact_build_type',
				'type'          => 'text',
				'default_value' => 'Bespoke. No Templates.',
			],
			[
				'key'           => 'field_attr_fact_website',
				'label'         => __( 'Fact: Website URL', 'nera-competitions' ),
				'name'          => 'attr_fact_website',
				'type'          => 'url',
				'default_value' => 'https://www.neramarketing.co.uk',
				'placeholder'   => 'https://www.neramarketing.co.uk',
			],

			// ── TAB: STATS ────────────────────────────────────────────────────
			[
				'key'       => 'field_attr_tab_stats',
				'label'     => __( 'Stats', 'nera-competitions' ),
				'type'      => 'tab',
				'placement' => 'top',
			],
			[
				'key'          => 'field_attr_stats',
				'label'        => __( 'Stat Bar Items', 'nera-competitions' ),
				'name'         => 'attr_stats',
				'type'         => 'repeater',
				'instructions' => __( 'Exactly 4 items display side-by-side. Add the headline value and its label.', 'nera-competitions' ),
				'layout'       => 'table',
				'button_label' => __( 'Add Stat', 'nera-competitions' ),
				'sub_fields'   => [
					[
						'key'         => 'field_attr_stat_value',
						'label'       => __( 'Value', 'nera-competitions' ),
						'name'        => 'value',
						'type'        => 'text',
						'placeholder' => 'UK',
						'column_width' => '30',
					],
					[
						'key'         => 'field_attr_stat_label',
						'label'       => __( 'Label', 'nera-competitions' ),
						'name'        => 'label',
						'type'        => 'text',
						'placeholder' => 'Specialist Agency',
						'column_width' => '70',
					],
				],
			],

			// ── TAB: SECTION 1 ───────────────────────────────────────────────
			[
				'key'       => 'field_attr_tab_s1',
				'label'     => __( 'Section 1 — Who Built This', 'nera-competitions' ),
				'type'      => 'tab',
				'placement' => 'top',
			],
			[
				'key'           => 'field_attr_s1_tag',
				'label'         => __( 'Section Tag', 'nera-competitions' ),
				'name'          => 'attr_s1_tag',
				'type'          => 'text',
				'default_value' => 'About the Build',
			],
			[
				'key'           => 'field_attr_s1_heading',
				'label'         => __( 'Heading', 'nera-competitions' ),
				'name'          => 'attr_s1_heading',
				'type'          => 'text',
				'default_value' => 'Who built this competition website?',
			],
			[
				'key'           => 'field_attr_s1_lead',
				'label'         => __( 'Lead Paragraph (AEO direct answer)', 'nera-competitions' ),
				'name'          => 'attr_s1_lead',
				'type'          => 'textarea',
				'instructions'  => __( 'Short direct-answer sentence shown with a left accent border. Use [site-name] as a placeholder for the site name.', 'nera-competitions' ),
				'rows'          => 3,
				'default_value' => 'This competition website was designed and built by Nera Marketing, a UK digital agency based in Ramsgate, Kent, specialising in bespoke competition website development and full-service digital marketing for online raffle businesses.',
			],
			[
				'key'           => 'field_attr_s1_body_1',
				'label'         => __( 'Body Paragraph 1', 'nera-competitions' ),
				'name'          => 'attr_s1_body_1',
				'type'          => 'textarea',
				'rows'          => 3,
				'default_value' => 'Nera Marketing builds every competition platform from scratch. No templates, no off-the-shelf themes. Each site is engineered around the client\'s brand, audience, and the specific mechanics that drive ticket sales and conversions.',
			],
			[
				'key'           => 'field_attr_s1_body_2',
				'label'         => __( 'Body Paragraph 2', 'nera-competitions' ),
				'name'          => 'attr_s1_body_2',
				'type'          => 'textarea',
				'rows'          => 3,
				'default_value' => 'As a full-service agency, Nera doesn\'t just hand over a website and disappear. Most clients work with Nera Marketing long-term, combining the platform with ongoing SEO and email marketing to build a competition business that scales.',
			],

			// ── TAB: SECTION 2 ───────────────────────────────────────────────
			[
				'key'       => 'field_attr_tab_s2',
				'label'     => __( 'Section 2 — Full-Service', 'nera-competitions' ),
				'type'      => 'tab',
				'placement' => 'top',
			],
			[
				'key'           => 'field_attr_s2_tag',
				'label'         => __( 'Section Tag', 'nera-competitions' ),
				'name'          => 'attr_s2_tag',
				'type'          => 'text',
				'default_value' => 'Full-Service Support',
			],
			[
				'key'           => 'field_attr_s2_heading',
				'label'         => __( 'Heading', 'nera-competitions' ),
				'name'          => 'attr_s2_heading',
				'type'          => 'text',
				'default_value' => 'A competition website is only the beginning',
			],
			[
				'key'           => 'field_attr_s2_lead',
				'label'         => __( 'Lead Paragraph (AEO direct answer)', 'nera-competitions' ),
				'name'          => 'attr_s2_lead',
				'type'          => 'textarea',
				'rows'          => 3,
				'default_value' => 'Nera Marketing provides ongoing digital marketing support alongside every competition website build, including SEO and email marketing specifically for UK competition businesses.',
			],
			[
				'key'           => 'field_attr_s2_body_1',
				'label'         => __( 'Body Paragraph 1', 'nera-competitions' ),
				'name'          => 'attr_s2_body_1',
				'type'          => 'textarea',
				'rows'          => 3,
				'default_value' => 'A great platform without traffic is just an empty shop. Nera\'s approach is to build the website and the marketing strategy together, so competition businesses launch with a clear path to consistent ticket sales from day one.',
			],
			[
				'key'           => 'field_attr_s2_body_2',
				'label'         => __( 'Body Paragraph 2', 'nera-competitions' ),
				'name'          => 'attr_s2_body_2',
				'type'          => 'textarea',
				'rows'          => 3,
				'default_value' => 'Nera also guides clients through the legal and compliance landscape of running online competitions in the UK, covering everything from prize structure to question of skill requirements, so you can launch with confidence.',
			],

			// ── TAB: SECTION 3 ───────────────────────────────────────────────
			[
				'key'       => 'field_attr_tab_s3',
				'label'     => __( 'Section 3 — Why Nera', 'nera-competitions' ),
				'type'      => 'tab',
				'placement' => 'top',
			],
			[
				'key'           => 'field_attr_s3_tag',
				'label'         => __( 'Section Tag', 'nera-competitions' ),
				'name'          => 'attr_s3_tag',
				'type'          => 'text',
				'default_value' => 'Our Approach',
			],
			[
				'key'           => 'field_attr_s3_heading',
				'label'         => __( 'Heading', 'nera-competitions' ),
				'name'          => 'attr_s3_heading',
				'type'          => 'text',
				'default_value' => 'Why competition businesses choose Nera Marketing',
			],
			[
				'key'           => 'field_attr_s3_lead',
				'label'         => __( 'Lead Paragraph (AEO direct answer)', 'nera-competitions' ),
				'name'          => 'attr_s3_lead',
				'type'          => 'textarea',
				'rows'          => 3,
				'default_value' => 'Competition businesses choose Nera Marketing because they build bespoke platforms engineered for sales performance, not adapted templates, and back every build with long-term SEO and digital marketing strategy.',
			],
			[
				'key'           => 'field_attr_s3_body_1',
				'label'         => __( 'Body Paragraph 1', 'nera-competitions' ),
				'name'          => 'attr_s3_body_1',
				'type'          => 'textarea',
				'rows'          => 3,
				'default_value' => 'Slow load times, a checkout that loses trust, or a mobile experience that frustrates users. Any one of these kills conversions. Nera engineers against every one of them before a site goes live.',
			],
			[
				'key'           => 'field_attr_s3_body_2',
				'label'         => __( 'Body Paragraph 2', 'nera-competitions' ),
				'name'          => 'attr_s3_body_2',
				'type'          => 'textarea',
				'rows'          => 3,
				'default_value' => 'Every platform is built so the client can manage it independently — no developer dependency for day-to-day operations. Prizes, timers, draws, discount codes, email automations — all accessible through a back-end designed for how competition businesses actually run.',
			],

			// ── TAB: FEATURES ────────────────────────────────────────────────
			[
				'key'       => 'field_attr_tab_features',
				'label'     => __( 'Features Grid', 'nera-competitions' ),
				'type'      => 'tab',
				'placement' => 'top',
			],
			[
				'key'           => 'field_attr_features_heading',
				'label'         => __( 'Section Heading', 'nera-competitions' ),
				'name'          => 'attr_features_heading',
				'type'          => 'text',
				'instructions'  => __( 'Use {{em}} around the word you want italicised in primary colour, e.g. "What\'s inside every {{em}}Nera{{/em}} competition platform".', 'nera-competitions' ),
				'default_value' => "What's inside every {{em}}Nera{{/em}} competition platform",
			],
			[
				'key'           => 'field_attr_features_intro',
				'label'         => __( 'Section Intro', 'nera-competitions' ),
				'name'          => 'attr_features_intro',
				'type'          => 'textarea',
				'rows'          => 2,
				'default_value' => 'Every platform is built from scratch. Here\'s what comes as standard on every competition website Nera Marketing delivers.',
			],
			[
				'key'          => 'field_attr_features',
				'label'        => __( 'Features', 'nera-competitions' ),
				'name'         => 'attr_features',
				'type'         => 'repeater',
				'instructions' => __( 'Icons are assigned automatically by position (1–9). Title and description are editable here.', 'nera-competitions' ),
				'layout'       => 'row',
				'button_label' => __( 'Add Feature', 'nera-competitions' ),
				'sub_fields'   => [
					[
						'key'   => 'field_attr_feature_title',
						'label' => __( 'Title', 'nera-competitions' ),
						'name'  => 'title',
						'type'  => 'text',
					],
					[
						'key'   => 'field_attr_feature_description',
						'label' => __( 'Description', 'nera-competitions' ),
						'name'  => 'description',
						'type'  => 'textarea',
						'rows'  => 2,
					],
				],
			],

			// ── TAB: PILLARS ─────────────────────────────────────────────────
			[
				'key'       => 'field_attr_tab_pillars',
				'label'     => __( 'Three Pillars', 'nera-competitions' ),
				'type'      => 'tab',
				'placement' => 'top',
			],
			[
				'key'           => 'field_attr_pillars_label',
				'label'         => __( 'Section Label', 'nera-competitions' ),
				'name'          => 'attr_pillars_label',
				'type'          => 'text',
				'default_value' => 'The Foundation for Success',
			],
			[
				'key'           => 'field_attr_pillars_heading',
				'label'         => __( 'Heading', 'nera-competitions' ),
				'name'          => 'attr_pillars_heading',
				'type'          => 'text',
				'default_value' => 'Three things every competition business needs to succeed',
			],
			[
				'key'           => 'field_attr_pillars_intro',
				'label'         => __( 'Intro Text', 'nera-competitions' ),
				'name'          => 'attr_pillars_intro',
				'type'          => 'text',
				'default_value' => 'A great website is one piece. Here\'s what Nera delivers across all three.',
			],
			[
				'key'          => 'field_attr_pillars',
				'label'        => __( 'Pillars', 'nera-competitions' ),
				'name'         => 'attr_pillars',
				'type'         => 'repeater',
				'layout'       => 'row',
				'button_label' => __( 'Add Pillar', 'nera-competitions' ),
				'sub_fields'   => [
					[
						'key'          => 'field_attr_pillar_number',
						'label'        => __( 'Number', 'nera-competitions' ),
						'name'         => 'number',
						'type'         => 'text',
						'placeholder'  => '01',
						'column_width' => '10',
					],
					[
						'key'          => 'field_attr_pillar_title',
						'label'        => __( 'Title', 'nera-competitions' ),
						'name'         => 'title',
						'type'         => 'text',
						'column_width' => '30',
					],
					[
						'key'          => 'field_attr_pillar_description',
						'label'        => __( 'Description', 'nera-competitions' ),
						'name'         => 'description',
						'type'         => 'textarea',
						'rows'         => 2,
						'column_width' => '60',
					],
				],
			],

			// ── TAB: FAQ ─────────────────────────────────────────────────────
			[
				'key'       => 'field_attr_tab_faq',
				'label'     => __( 'FAQ', 'nera-competitions' ),
				'type'      => 'tab',
				'placement' => 'top',
			],
			[
				'key'           => 'field_attr_faq_label',
				'label'         => __( 'Section Label', 'nera-competitions' ),
				'name'          => 'attr_faq_label',
				'type'          => 'text',
				'default_value' => 'Common Questions',
			],
			[
				'key'           => 'field_attr_faq_heading',
				'label'         => __( 'Heading', 'nera-competitions' ),
				'name'          => 'attr_faq_heading',
				'type'          => 'text',
				'default_value' => 'Frequently asked questions',
			],
			[
				'key'           => 'field_attr_faq_intro',
				'label'         => __( 'Intro Text', 'nera-competitions' ),
				'name'          => 'attr_faq_intro',
				'type'          => 'textarea',
				'rows'          => 2,
				'default_value' => 'Answers to the most common questions about this site, Nera Marketing, and competition website development in the UK.',
			],
			[
				'key'          => 'field_attr_faqs',
				'label'        => __( 'FAQ Items', 'nera-competitions' ),
				'name'         => 'attr_faqs',
				'type'         => 'repeater',
				'instructions' => __( 'All items appear on the page and in the page schema. Use [site-name] in question text as a placeholder for the site name.', 'nera-competitions' ),
				'layout'       => 'row',
				'button_label' => __( 'Add FAQ', 'nera-competitions' ),
				'sub_fields'   => [
					[
						'key'   => 'field_attr_faq_question',
						'label' => __( 'Question', 'nera-competitions' ),
						'name'  => 'question',
						'type'  => 'text',
					],
					[
						'key'   => 'field_attr_faq_answer',
						'label' => __( 'Answer', 'nera-competitions' ),
						'name'  => 'answer',
						'type'  => 'textarea',
						'rows'  => 3,
					],
				],
			],

			// ── TAB: CTA ─────────────────────────────────────────────────────
			[
				'key'       => 'field_attr_tab_cta',
				'label'     => __( 'CTA', 'nera-competitions' ),
				'type'      => 'tab',
				'placement' => 'top',
			],
			[
				'key'           => 'field_attr_cta_heading',
				'label'         => __( 'Heading', 'nera-competitions' ),
				'name'          => 'attr_cta_heading',
				'type'          => 'textarea',
				'instructions'  => __( 'Multi-line heading. Each line break becomes a new line in the heading.', 'nera-competitions' ),
				'rows'          => 3,
				'default_value' => "Ready to build\nyour competition\nbusiness?",
			],
			[
				'key'           => 'field_attr_cta_subtitle',
				'label'         => __( 'Subtitle', 'nera-competitions' ),
				'name'          => 'attr_cta_subtitle',
				'type'          => 'textarea',
				'rows'          => 2,
				'default_value' => 'Nera Marketing only works with clients who are serious about success. If that\'s you, visit the site or get in touch to talk about what\'s possible.',
			],
			[
				'key'           => 'field_attr_cta_btn1_label',
				'label'         => __( 'Button 1 Label', 'nera-competitions' ),
				'name'          => 'attr_cta_button_1_label',
				'type'          => 'text',
				'default_value' => 'Visit Nera Marketing',
			],
			[
				'key'           => 'field_attr_cta_btn1_url',
				'label'         => __( 'Button 1 URL', 'nera-competitions' ),
				'name'          => 'attr_cta_button_1_url',
				'type'          => 'url',
				'default_value' => 'https://neramarketing.co.uk/competition-websites/',
			],
			[
				'key'           => 'field_attr_cta_btn2_label',
				'label'         => __( 'Button 2 Label', 'nera-competitions' ),
				'name'          => 'attr_cta_button_2_label',
				'type'          => 'text',
				'default_value' => 'Get in Touch',
			],
			[
				'key'           => 'field_attr_cta_btn2_url',
				'label'         => __( 'Button 2 URL', 'nera-competitions' ),
				'name'          => 'attr_cta_button_2_url',
				'type'          => 'url',
				'default_value' => 'https://neramarketing.co.uk/competition-websites/contact-us/',
			],

			// ── TAB: CREDIT BAR ──────────────────────────────────────────────
			[
				'key'       => 'field_attr_tab_credit',
				'label'     => __( 'Credit Bar', 'nera-competitions' ),
				'type'      => 'tab',
				'placement' => 'top',
			],
			[
				'key'           => 'field_attr_credit_text',
				'label'         => __( 'Credit Text', 'nera-competitions' ),
				'name'          => 'attr_credit_text',
				'type'          => 'textarea',
				'instructions'  => __( 'Plain text. "Nera Marketing" in the text is automatically hyperlinked to the Credit URL below.', 'nera-competitions' ),
				'rows'          => 2,
				'default_value' => 'This competition website was designed and built by Nera Marketing, a UK digital agency based in Ramsgate, Kent, specialising in competition websites and SEO.',
			],
			[
				'key'           => 'field_attr_credit_badge_label',
				'label'         => __( 'Badge Label', 'nera-competitions' ),
				'name'          => 'attr_credit_badge_label',
				'type'          => 'text',
				'default_value' => 'Built by Nera Marketing',
			],
			[
				'key'           => 'field_attr_credit_url',
				'label'         => __( 'Credit URL', 'nera-competitions' ),
				'name'          => 'attr_credit_url',
				'type'          => 'url',
				'default_value' => 'https://www.neramarketing.co.uk',
			],

		],
		'location'              => [
			[
				[
					'param'    => 'options_page',
					'operator' => '==',
					'value'    => NERA_ATTRIBUTION_OPTIONS_SLUG,
				],
			],
		],
		'menu_order'            => 0,
		'position'              => 'normal',
		'style'                 => 'default',
		'label_placement'       => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen'        => [ 'the_content', 'featured_image' ],
		'active'                => true,
		'description'           => __( 'All editable content for the Nera Marketing attribution page.', 'nera-competitions' ),
	] );
}
add_action( 'acf/init', 'nera_register_attribution_fields' );

/**
 * Hide attribution editor from the admin sidebar.
 * The page remains accessible directly via /wp-admin/admin.php?page=...
 */
function nera_hide_attribution_options_menu() {
	remove_menu_page( NERA_ATTRIBUTION_OPTIONS_SLUG );
	remove_submenu_page( 'options-general.php', NERA_ATTRIBUTION_OPTIONS_SLUG );
}
add_action( 'admin_menu', 'nera_hide_attribution_options_menu', 999 );
