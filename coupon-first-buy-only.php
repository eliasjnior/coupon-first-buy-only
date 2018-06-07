<?php

/**
 * Plugin Name: Coupon First Buy Only for WooCommerce
 * Description: Add an option to allow use a coupon for first buy only.
 * Author: Elias Júnior
 * Author URI: https://eliasjr.io/
 * Version: 1.0.0
 * Requires at least: 4.4
 * Tested up to: 4.9
 * Text Domain: coupon-first-buy-only
 * Domain Path: /languages/
 * WC requires at least: 3.0
 * WC tested up to: 3.4
 */

// Prevent run outside.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CouponFirstBuyOnly.
 */
class CouponFirstBuyOnly {

	/**
	 * Key to store the coupon data in database.
	 */
	const FIRST_BUY_ONLY_KEY = 'first_buy_only';

	/**
	 * @var CouponFirstBuyOnly Plugin instance.
	 */
	private static $instance = null;

	/**
	 * CouponFirstBuyOnly constructor.
	 */
	private function __construct() {
		add_filter( 'woocommerce_coupon_is_valid', array( $this, 'coupon_is_valid' ), 10, 3 );
		add_action( 'woocommerce_coupon_options', array( $this, 'coupon_options' ), 10, 2 );
		add_action( 'woocommerce_coupon_options_save', array( $this, 'options_save' ), 10, 2 );
		add_action( 'plugins_loaded', array( $this, 'text_domain' ) );

		if ( is_admin() ) {
			add_action( 'admin_notices', array( $this, 'wrong_woocommerce_option_notice' ) );
		}
	}

	/**
	 * Get the plugin instance.
	 *
	 * @return CouponFirstBuyOnly.
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Load text domain.
	 */
	public function text_domain() {
		load_plugin_textdomain( 'coupon-first-buy-only', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Add elements to coupon edit page.
	 *
	 * @param $coupon_id
	 */
	public function coupon_options( $coupon_id ) {
		$first_buy_only = $this->get_coupon_meta( $coupon_id );

		// Add a checkbox
		woocommerce_wp_checkbox(
			array(
				'id'          => $this::FIRST_BUY_ONLY_KEY,
				'label'       => __( 'First buy only', 'coupon-first-buy-only' ),
				'description' => __( 'Check if you want that coupon is valid only for the first buy.', 'coupon-first-buy-only' ),
				'value'       => wc_bool_to_string( ! ! $first_buy_only ),
			)
		);
	}

	/**
	 * Process the coupon edit page save.
	 *
	 * @param $post_id
	 */
	public function options_save( $post_id ) {
		if ( isset( $_POST['first_buy_only'] ) ) {
			update_post_meta( $post_id, $this::FIRST_BUY_ONLY_KEY, wc_string_to_bool( $_POST[ $this::FIRST_BUY_ONLY_KEY ] ) );
		} else {
			delete_post_meta( $post_id, $this::FIRST_BUY_ONLY_KEY );
		}
	}

	/**
	 * Check if a coupon is valid.
	 *
	 * @param $is_valid boolean
	 * @param $coupon WC_Coupon
	 *
	 * @return boolean
	 * @throws Exception: If coupon is invalid.
	 */
	public function coupon_is_valid( $is_valid, $coupon ) {
		$first_buy_only = $this->get_coupon_meta( $coupon->get_id() );

		// Check if is valid
		// Check if first buy only option is checked
		// Check if the user have previous orders
		if ( $is_valid && $first_buy_only && $this->get_current_user_orders() ) {
			throw new Exception( __( 'This coupon is valid for first buy only.', 'coupon-first-buy-only' ), 100 );
		}

		return $is_valid;
	}

	/**
	 * Get the orders of current user.
	 *
	 * @return WC_Order[]
	 */
	public function get_current_user_orders() {
		$order_statuses   = array( 'wc-on-hold', 'wc-processing', 'wc-completed' );
		$customer_user_id = get_current_user_id();

		$customer_orders = wc_get_orders( array(
			'meta_key'    => '_customer_user',
			'meta_value'  => $customer_user_id,
			'post_status' => $order_statuses,
			'numberposts' => - 1
		) );

		return $customer_orders;
	}

	/**
	 * Add a notice if guest checkout is enabled.
	 */
	public function wrong_woocommerce_option_notice() {
		if ( get_option( 'woocommerce_enable_guest_checkout' ) !== 'no' ) {
			include dirname( __FILE__ ) . '/includes/html-wrong-woocommerce-option-notice.php';
		}
	}

	/**
	 * Get meta data for a coupon.
	 *
	 * @param $coupon_id string
	 *
	 * @return string|null
	 */
	private function get_coupon_meta( $coupon_id ) {
		return get_post_meta( $coupon_id, $this::FIRST_BUY_ONLY_KEY, true );
	}

}

// Init the plugin.
CouponFirstBuyOnly::get_instance();