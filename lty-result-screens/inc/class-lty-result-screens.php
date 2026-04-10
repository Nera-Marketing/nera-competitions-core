<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LTY_Result_Screens {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'woocommerce_thankyou', array( $this, 'render_result_overlay' ), 10, 1 );
		// Priority 10: load before theme Tailwind (priority 15) so generic .flex/.hidden
		// from this bundle do not override the header's hidden/lg:flex breakpoint rules.
		add_action( 'wp_enqueue_scripts',   array( $this, 'enqueue_assets' ), 10 );
		add_action( 'acf/init',             array( $this, 'register_acf' ) );
	}

	public function register_acf() {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_local_field_group( array(
			'key'    => 'group_lty_rs_settings',
			'title'  => 'Result Screens',
			'fields' => array(

				// ── Win screen ────────────────────────────────────────────────
				array(
					'key'   => 'field_lty_rs_tab_win',
					'label' => 'Win screen',
					'type'  => 'tab',
				),
				array(
					'key'           => 'field_lty_rs_win_heading',
					'label'         => 'Heading',
					'name'          => 'lty_rs_win_heading',
					'type'          => 'text',
					'default_value' => "You've won!",
				),
				array(
					'key'           => 'field_lty_rs_win_email_note',
					'label'         => 'Email confirmation note',
					'name'          => 'lty_rs_win_email_note',
					'type'          => 'text',
					'default_value' => 'A confirmation email is on its way to you.',
				),
				array(
					'key'           => 'field_lty_rs_win_button',
					'label'         => 'Button text',
					'name'          => 'lty_rs_win_button',
					'type'          => 'text',
					'default_value' => 'Claim my prize!',
				),

				// ── No-win screen ─────────────────────────────────────────────
				array(
					'key'   => 'field_lty_rs_tab_no_win',
					'label' => 'No-win screen',
					'type'  => 'tab',
				),
				array(
					'key'           => 'field_lty_rs_no_win_heading',
					'label'         => 'Heading',
					'name'          => 'lty_rs_no_win_heading',
					'type'          => 'text',
					'default_value' => 'Thanks for entering!',
				),
				array(
					'key'           => 'field_lty_rs_no_win_message',
					'label'         => 'Message',
					'name'          => 'lty_rs_no_win_message',
					'type'          => 'textarea',
					'instructions'  => 'Shown below the heading.',
					'default_value' => "Not this time \xe2\x80\x94 but every entry brings you closer. There are always more competitions to enter!",
					'rows'          => 3,
					'new_lines'     => 'br',
				),
				array(
					'key'           => 'field_lty_rs_no_win_button',
					'label'         => 'Button text',
					'name'          => 'lty_rs_no_win_button',
					'type'          => 'text',
					'default_value' => 'Browse more competitions',
				),
				array(
					'key'          => 'field_lty_rs_browse_url',
					'label'        => 'Button URL',
					'name'         => 'lty_rs_browse_url',
					'type'         => 'url',
					'instructions' => 'Defaults to the WooCommerce shop page if left blank.',
					'placeholder'  => 'https://',
				),

				// ── Prize draw screen ─────────────────────────────────────────
				array(
					'key'   => 'field_lty_rs_tab_draw',
					'label' => 'Prize draw screen',
					'type'  => 'tab',
				),
				array(
					'key'           => 'field_lty_rs_draw_heading',
					'label'         => 'Heading',
					'name'          => 'lty_rs_draw_heading',
					'type'          => 'text',
					'default_value' => "You're in the draw!",
				),
				array(
					'key'           => 'field_lty_rs_draw_subtext',
					'label'         => 'Subtext',
					'name'          => 'lty_rs_draw_subtext',
					'type'          => 'text',
					'default_value' => "Your entry is confirmed \xe2\x80\x94 fingers crossed!",
				),
				array(
					'key'           => 'field_lty_rs_draw_good_luck',
					'label'         => 'Good luck text',
					'name'          => 'lty_rs_draw_good_luck',
					'type'          => 'text',
					'default_value' => 'Good luck!',
				),
				array(
					'key'           => 'field_lty_rs_draw_button',
					'label'         => 'Button text',
					'name'          => 'lty_rs_draw_button',
					'type'          => 'text',
					'default_value' => 'Got it!',
				),

			),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'acf-options-woocommerce',
					),
				),
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'theme-settings',
					),
				),
			),
		) );
	}

	public function enqueue_assets() {
		if ( ! is_wc_endpoint_url( 'order-received' ) ) {
			return;
		}
		wp_enqueue_style(
			'lty-result-screens',
			LTY_RS_URL . 'assets/css/lty-result-screens.css',
			array(),
			LTY_RS_VERSION
		);
		wp_enqueue_script(
			'lty-result-screens',
			LTY_RS_URL . 'assets/js/lty-result-screens.js',
			array(),
			LTY_RS_VERSION,
			true
		);
	}

	public function render_result_overlay( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		// Scan all items and prioritise: instant-win won > instant-win no-win > prize draw.
		$instant_win_won     = array(); // log_ids of won prizes
		$instant_win_no_win  = null;    // product with no instant win match
		$prize_draw_product  = null;    // regular lottery product

		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();
			if ( ! $product || ! lty_is_lottery_product( $product ) ) {
				continue;
			}

			if ( $product->is_instant_winner() ) {
				$log_ids = lty_get_instant_winner_log_ids_by_order_id( $order_id, $product->get_id(), 'lty_won' );
				if ( ! empty( $log_ids ) ) {
					// Won — highest priority, no need to keep scanning.
					$instant_win_won = $log_ids;
					break;
				}
				// No win yet — record but keep scanning in case another product won.
				if ( null === $instant_win_no_win ) {
					$instant_win_no_win = $product;
				}
			} elseif ( $this->is_spin_to_win_product( $product ) ) {
				// Spin To Win products should not show the generic prize draw overlay.
				continue;
			} elseif ( null === $prize_draw_product ) {
				$prize_draw_product = $product;
			}
		}

		if ( ! empty( $instant_win_won ) ) {
			$args = array( 'log_ids' => $instant_win_won, 'order' => $order );
			if ( apply_filters( 'lty_rs_show_overlay', true, 'instant-win-won', $order ) ) {
				$this->render_template( 'instant-win-won.php', $args );
			}
		} elseif ( null !== $instant_win_no_win ) {
			$args = array( 'order' => $order, 'product' => $instant_win_no_win );
			if ( apply_filters( 'lty_rs_show_overlay', true, 'instant-win-no-win', $order ) ) {
				$this->render_template( 'instant-win-no-win.php', $args );
			}
		} elseif ( null !== $prize_draw_product ) {
			$args = array( 'order' => $order, 'product' => $prize_draw_product );
			if ( apply_filters( 'lty_rs_show_overlay', true, 'prize-draw', $order ) ) {
				$this->render_template( 'prize-draw-good-luck.php', $args );
			}
		}
	}

	/**
	 * Check if a lottery product is configured for Spin To Win.
	 *
	 * @param WC_Product $product Product object.
	 * @return bool
	 */
	private function is_spin_to_win_product( $product ) {
		if ( ! is_object( $product ) || ! method_exists( $product, 'get_id' ) ) {
			return false;
		}

		$product_id = absint( $product->get_id() );
		if ( $product_id < 1 ) {
			return false;
		}

		if ( class_exists( 'Nera_STW_Product_Meta' ) && method_exists( 'Nera_STW_Product_Meta', 'is_enabled' ) ) {
			return (bool) Nera_STW_Product_Meta::is_enabled( $product_id );
		}

		return 'yes' === get_post_meta( $product_id, '_nera_stw_enabled', true );
	}

	/**
	 * Render a template file, with support for theme overrides.
	 *
	 * Themes can override bundled templates by placing files under:
	 *   {stylesheet}/lty-result-screens/{template}
	 *
	 * @param string $template Filename relative to /templates/.
	 * @param array  $args     Variables to extract into template scope.
	 */
	private function render_template( $template, $args = array() ) {
		$theme_override = get_stylesheet_directory() . '/lty-result-screens/' . $template;
		$default_template = LTY_RS_PATH . 'templates/' . $template;

		$template_path = file_exists( $theme_override ) ? $theme_override : $default_template;

		if ( ! file_exists( $template_path ) ) {
			return;
		}

		extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract
		include $template_path;
	}
}
