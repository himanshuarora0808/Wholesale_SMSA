<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Adds minimum product quantity condition to core plugin using hooks and filters of that.
 *
 * @class    CED_CWSM_Min_Checkout_Price_Module
 * @version  2.0.8
 * @package  wholesale-market/min-product-qty-module
 */

class CED_CWSM_Min_Checkout_Price_Module {
	/**
	 * This is construct of class
	 *
	 * @link plugins@cedcommerce.com
	 */
	public function __construct() {
		$this->ced_cwsm_min_pro_qty_module_add_hooks_and_filters();
	}

	/**
	 * This function hooks into all filters and actions available in core plugin.
	 *
	 * @name cwsm_min_pro_qty_module_add_hooks_and_filters()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_min_pro_qty_module_add_hooks_and_filters() {

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_style_and_script_on_adminSide' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_style_and_script_on_frontEnd' ) );

		add_filter( 'woocommerce_get_sections_ced_cwsm_plugin', array( $this, 'ced_cwsm_min_checkout_price_module_add_section' ), 10.1, 1 );
		add_filter( 'woocommerce_get_settings_ced_cwsm_plugin', array( $this, 'ced_cwsm_min_checkout_price_module_add_setting' ), 10, 2 );

		add_filter( 'ced_cwsm_append_basic_sections', array( $this, 'ced_cwsm_min_checkout_price_module_add_section' ), 10, 1 );
		add_filter( 'ced_cwsm_append_basic_settings', array( $this, 'ced_cwsm_min_checkout_price_module_add_setting' ), 10, 2 );

		$ced_cwsm_enable_minCheckoutPrice = get_option( 'ced_cwsm_enable_minCheckoutPrice' );
		if ( isset( $ced_cwsm_enable_minCheckoutPrice ) && ! empty( $ced_cwsm_enable_minCheckoutPrice ) && 'yes' == $ced_cwsm_enable_minCheckoutPrice ) {
			add_action( 'woocommerce_before_cart', array( $this, 'woocommerce_before_cart_hidden_element' ) );
			add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'woocommerce_add_to_cart_fragments_hidden_element' ) );
			add_action( 'woocommerce_check_cart_items', array( $this, 'avoid_rendering_checkout_page' ) );
		}
	}

	/**
	 * This function adds meta field to enter minimum product quantity for variable product.
	 *
	 * @name cwsm_min_pro_qty_module_add_variationPro_meta_fields()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function enqueue_style_and_script_on_adminSide() {
		$urlToUse = CED_CWSM_PLUGIN_DIR_URL . 'assets/css/error_msg_bckend.css';
		if ( isset( $_GET['section'] ) && 'ced_cwsm_min_checkout_price_module' == $_GET['section'] ) {
			wp_enqueue_style( 'ced_cwsm_error_msg_backend', $urlToUse, array(), '1.0.0', true );
		}
	}


	/**
	 * This function adds meta field to enter minimum product quantity for variable product.
	 *
	 * @name cwsm_min_pro_qty_module_add_variationPro_meta_fields()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function enqueue_style_and_script_on_frontEnd() {
		if ( ! $this->isQualifyMinCheckoutPriceCondition() && is_cart() ) {
			wp_enqueue_script( 'ced_cwsm_min_checkout_price_js', plugins_url( 'js/min-checkout-price.js', __FILE__ ), array( 'jquery' ), '1.0', true );
		}
	}

	/**
	 * This function adds meta field to enter minimum product quantity for variable product.
	 *
	 * @name cwsm_min_pro_qty_module_add_variationPro_meta_fields()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function woocommerce_before_cart_hidden_element() {
		global $globalCWSM;
		if ( ! $globalCWSM->isCurrentUserIsWholesaleUser() ) {
			return;
		}

		$isQualifyMinCheckoutPriceCondition = 'false';
		if ( $this->isQualifyMinCheckoutPriceCondition() ) {
			$isQualifyMinCheckoutPriceCondition = 'true';
		}
		$errorMsg = $this->generateErrorMessage();
		echo '<div id="cwsm_min_checkout_price_error" style="display:none;">';
		echo '<input type="hidden" name="isQualifyMinCheckoutPriceCondition" id="isQualifyMinCheckoutPriceCondition" value="' . esc_attr( $isQualifyMinCheckoutPriceCondition ) . '">';
		echo '<div class="cwsm_custom_msg_error">' . esc_attr( $errorMsg ) . '</div>';
		echo '</div>';
	}

	/**
	 * This function adds meta field to enter minimum product quantity for variable product.
	 *
	 * @name cwsm_min_pro_qty_module_add_variationPro_meta_fields()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function woocommerce_add_to_cart_fragments_hidden_element( $fragments ) {
		global $globalCWSM;
		if ( ! $globalCWSM->isCurrentUserIsWholesaleUser() ) {
			return $fragments;
		}
		ob_start();
		$this->woocommerce_before_cart_hidden_element();
		$fragments['div#cwsm_min_checkout_price_error'] = ob_get_clean();
		return $fragments;
	}



	/**
	 * This function avoids rendering checkout page when min checkout price condition not meet.
	 *
	 * @name avoid_rendering_checkout_page()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function avoid_rendering_checkout_page( $returnErrorMsg = false ) {
		global $globalCWSM;
		if ( ! $globalCWSM->isCurrentUserIsWholesaleUser() ) {
			return;
		}
		if ( is_checkout() && ! $this->isQualifyMinCheckoutPriceCondition() ) {
			$errorMsg = $this->generateErrorMessage();
			wc_add_notice( $errorMsg, 'error' );
		}
	}

	/**
	 * This function generate customize error message for min checkout price condition not meet.
	 *
	 * @name generateErrorMessage()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function generateErrorMessage() {
		$ced_cwsm_min_checkout_price = get_option( 'ced_cwsm_minCheckoutPrice' );
		if ( isset( $ced_cwsm_min_checkout_price ) && ! empty( $ced_cwsm_min_checkout_price ) && '' != $ced_cwsm_min_checkout_price ) {
			$ced_cwsm_min_checkout_price = (float) trim( $ced_cwsm_min_checkout_price );
			if ( WC()->version < '3.0.0' ) {
				$ced_cwsm_min_checkout_price = woocommerce_price( $ced_cwsm_min_checkout_price );
			} else {
				$ced_cwsm_min_checkout_price = wc_price( $ced_cwsm_min_checkout_price );

			}
		}
		global $woocommerce;
		$cartRef    = $woocommerce->cart;
		$cart_total = (float) $cartRef->total;

		$errorMsg = get_option( 'ced_cwsm_minCheckoutPrice_failure_txt' );
		$errorMsg = str_replace( '{*wm_minCheckoutPrice}', $ced_cwsm_min_checkout_price, $errorMsg );
		$errorMsg = str_replace( '{*wm_cartTotalPrice}', $cart_total, $errorMsg );
		return $errorMsg;
	}

	/**
	 * This function checks whether minimum checkout price condition is applicable or not.
	 *
	 * @name isQualifyMinCheckoutPriceCondition()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function isQualifyMinCheckoutPriceCondition( $cartRef = null ) {
		if ( is_null( $cartRef ) ) {
			global $woocommerce;
			$cartRef = $woocommerce->cart;
		}
		$cart_total = (float) $cartRef->total;

		$ced_cwsm_min_checkout_price = get_option( 'ced_cwsm_minCheckoutPrice' );
		if ( isset( $ced_cwsm_min_checkout_price ) && ! empty( $ced_cwsm_min_checkout_price ) && '' != $ced_cwsm_min_checkout_price ) {
			$ced_cwsm_min_checkout_price = (float) trim( $ced_cwsm_min_checkout_price );
			if ( $cart_total < $ced_cwsm_min_checkout_price ) {
				return false;
			} else {
				return true;
			}
		}
		return true;
	}


	/**
	 * This function adds section on wholesale market tab.
	 *
	 * @name ced_cwsm_min_checkout_price_module_add_section()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_min_checkout_price_module_add_section( $sections ) {
		$sections['ced_cwsm_min_checkout_price_module'] = __( 'Checkout Price', 'wholesale-market' );
		return $sections;
	}

	/**
	 * This function adds settings on wholesale market tab.
	 *
	 * @name ced_cwsm_min_checkout_price_module_add_setting()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_min_checkout_price_module_add_setting( $settingReceived, $current_section ) {
		if ( 'ced_cwsm_min_checkout_price_module' == $current_section ) {
			$settings = apply_filters(
				'ced_cwsm_min_checkout_price_module_setting',
				array(
					'section_title-2'                  => array(
						'name' => __( 'Minimum Checkout Price Section', 'wholesale-market' ),
						'type' => 'title',
						'desc' => __( 'Settings regarding minimum checkout price are listed below.', 'wholesale-market' ) . '<br/>' . __( 'Buyer\'s cart total has to be minimum that, then only wholesale user will be able to checkout.', 'wholesale-market' ),
						'id'   => 'wc_cwsm_setting_tab_section_title-2',
					),
					'enable/disable-2'                 => array(
						'title'   => __( 'Enable Minimum Checkout Price', 'wholesale-market' ),
						'desc'    => __( 'Enable To Activate Minimum Checkout Price Condition', 'wholesale-market' ),
						'id'      => 'ced_cwsm_enable_minCheckoutPrice',
						'default' => 'no',
						'type'    => 'checkbox',
					),
					'minCheckoutPrice'                 => array(
						'title'    => __( 'Minimum Checkout Price', 'wholesale-market' ) . '(' . get_woocommerce_currency_symbol() . ')',
						'desc'     => 'This is to set a minimum checkout price for wholesale users',
						'id'       => 'ced_cwsm_minCheckoutPrice',
						'class'    => 'input-text wc_input_decimal',
						'css'      => 'width:80px;',
						'type'     => 'text',
						'desc_tip' => true,
					),
					'section_end-1'                    => array(
						'type' => 'sectionend',
						'id'   => 'wc_ced_ws_setting_tab_section_end-1',
					),
					'info-text'                        => array(
						'name' => __( 'Customize Error Message Regarding Failure Of Minimum Checkout Price Condition On Cart Page Or Checkout Page', 'wholesale-market' ),
						'type' => 'title',
						'desc' => __( 'Using below options error message regarding "Minimum Checkout Price" can be customize.', 'wholesale-market' ) . '<span class="cwsm_hglyt_span">' . __( 'List of shortcode that can be used to customize the message are listed below :', 'wholesale-market' ) . ' <span>1. <span class="cwsm-l-span">{*wm_minCheckoutPrice}</span> => <span class="cwsm-r-span">' . __( 'Minimum Checkout Price', 'wholesale-market' ) . '</span></span> <span>2. <span class="cwsm-l-span">{*wm_cartTotalPrice}</span> => <span class="cwsm-r-span">' . __( 'Cart Total Price', 'wholesale-market' ) . '</span> </span> </span>',
						'id'   => 'ced_cwsm_custm_cart_page_msg_header',
					),
					'ced_cwsm_wsp_applied_success_txt' => array(
						'title'    => __( 'Custom Failure Message For Minimum Checkout Price Condition', 'wholesale-market' ),
						'id'       => 'ced_cwsm_minCheckoutPrice_failure_txt',
						'default'  => __( 'Sorry you can\'t checkout as your cart total is less then {*wm_minCheckoutPrice}.', 'wholesale-market' ),
						'type'     => 'textarea',
						'css'      => 'width:450px; height: 70px;',
						'autoload' => false,
					),
					'section_end-2'                    => array(
						'type' => 'sectionend',
						'id'   => 'wc_ced_ws_setting_tab_section_end-2',
					),
				)
			);
			return $settings;
		}
		return $settingReceived;
	}
}
// Create instance of class
new CED_CWSM_Min_Checkout_Price_Module();

