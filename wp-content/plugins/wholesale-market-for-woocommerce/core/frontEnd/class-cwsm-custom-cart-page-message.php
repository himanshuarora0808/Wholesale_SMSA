<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Cart Page Custom Message Handler.
 *
 * @class    CED_CWSM_Custom_Cart_Page_Message
 * @version  2.0.8
 * @package  wholesale-market/frontEnd
 * @package Class
 */
class CED_CWSM_Custom_Cart_Page_Message {
	/**
	 * CED_CWSM_Custom_Cart_Page_Message Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since  1.0.8
	 */
	private function init_hooks() {
		add_action( 'woocommerce_before_cart', array( $this, 'ced_cwsm_woocommerce_before_cart' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'cwsm_trigger_update_cart_script' ) );
	}

	public function cwsm_trigger_update_cart_script() {
		global $globalCWSM;
		// check for plugin activation and wholesale-user
		if ( ! $globalCWSM->is_CWSM_plugin_active() || ! $globalCWSM->isCurrentUserIsWholesaleUser() ) {
			return;
		}
		// Enqueue of scripts
		if ( is_cart() ) {
			wp_enqueue_script( 'ced_cwsm_trigger_update_cart_js', plugins_url( 'js/trigger_update_cart.js', __FILE__ ), array( 'jquery', 'wc-cart-fragments', 'wc-cart' ), '1.0', true );
			wp_enqueue_style( 'ced_cwsm_cart_page_message_style', plugins_url( 'css/cart_page_message_style.css', __FILE__ ), array(), '1.0.0', true );
		}
	}

	/**
	 * This function hooks into the begining of cart page to show customize message to user.
	 *
	 * @name ced_cwsm_woocommerce_before_cart()

	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_woocommerce_before_cart() {
		global $woocommerce,$globalCWSM;

		$arrayToKeepTrackOfProductsMsgAddedFor = array();

		/* check for plugin activation and wholesale-user */
		if ( ! $globalCWSM->is_CWSM_plugin_active() || ! $globalCWSM->isCurrentUserIsWholesaleUser() ) {
			return;
		}

		$cart = $woocommerce->cart->get_cart();

		if ( is_array( $cart ) && ( count( $cart ) > 0 ) ) {
			$cross_img_url       = CED_CWSM_PLUGIN_DIR_URL . 'assets/images/cross.png';
			$htmlToRender        = '';
			$customMessageToShow = '';
			foreach ( $cart as $cart_item ) {
				$product_id   = $cart_item['product_id'];
				$variation_id = $cart_item['variation_id'];
				$quantity     = (int) $cart_item['quantity'];
				if ( WC()->version < '3.0.0' ) {
					$price = $cart_item['data']->price;
				} else {
					$price = $cart_item['data']->get_price();
				}
				if ( WC()->version < '3.0.0' ) {
					$product_name = $cart_item['data']->post->post_title;
				} else {
					$ced_product_name = $cart_item['data']->get_data();
					$product_name     = $ced_product_name['name'];
				}

				$proceed = true;
				$proceed = apply_filters( 'ced_cwsm_is_render_msg_line_on_cart_page', $proceed, $product_id, $variation_id, $quantity, $price, $arrayToKeepTrackOfProductsMsgAddedFor );
				if ( $proceed ) {
					$productKeyToAppend                      = ( '0' != $variation_id ) ? $variation_id : $product_id;
					$productKeyToAppend                      = apply_filters( 'ced_cwsm_decide_productId_to_consider_on_cart_page', $productKeyToAppend, $product_id, $variation_id );
					$arrayToKeepTrackOfProductsMsgAddedFor[] = $productKeyToAppend;
					$customMessageToShow                     = $this->cwsm_generate_msg_about_wholesale_price_application( $product_id, $variation_id, $quantity, $price, $product_name, $cart_item, $arrayToKeepTrackOfProductsMsgAddedFor );
				}

				if ( '' != $customMessageToShow ) {
					$htmlToRender .= '<div class="custom-msg-wrapper">';
					$htmlToRender .= $customMessageToShow;
					$htmlToRender .= '<div class="cwsm_cross_div"><img src="' . $cross_img_url . '"></div>';
					$htmlToRender .= '</div>';
				}
			}
			echo ( $htmlToRender );
		}
	}

	/**
	 * This function works as helper function and return success and error message to ced_cwsm_woocommerce_before_cart function.
	 *
	 * @name cwsm_generate_msg_about_wholesale_price_application()

	 * @link  http://www.cedcommerce.com/
	 */
	public function cwsm_generate_msg_about_wholesale_price_application( $product_id, $variation_id, $quantity, $price, $product_name, $cart_item, $arrayToKeepTrackOfProductsMsgAddedFor ) {

		global $globalCWSM;

		$msgToReturn = '';
		$product     = new WC_Product( $product_id );
		if ( '0' != $variation_id ) {
			$wholesale_price = $globalCWSM->getWholesalePrice( $variation_id );
		} else {
			$wholesale_price = $globalCWSM->getWholesalePrice( $product_id );
		}

		if ( ! isset( $wholesale_price ) || empty( $wholesale_price ) || '' == $wholesale_price || '0' == $wholesale_price ) {
			return '';
		}

		$args['price'] = $wholesale_price;
		$args['qty']   = 1;

		if ( WC()->version < '3.3.0' ) {
			$extra_info = WC()->cart->get_item_data( $cart_item, true );
		} else {
			$extra_info = wc_get_formatted_cart_item_data( $cart_item, true );
		}

		if ( '' != $extra_info ) {
			$isRenderProductExtraInfo = true;
			$isRenderProductExtraInfo = apply_filters( 'ced_cwsm_alter_product_name_on_cart_page', $isRenderProductExtraInfo, $arrayToKeepTrackOfProductsMsgAddedFor );
			if ( $isRenderProductExtraInfo ) {
				$product_name .= '[ ' . $extra_info . ']';
			}
		}

		$productIDToConsider = ( '0' != $variation_id ) ? $variation_id : $product_id;
		$productIDToConsider = apply_filters( 'ced_cwsm_decide_productId_to_consider_on_cart_page', $productIDToConsider, $product_id, $variation_id );

		$min_qty  = $globalCWSM->getMinQtyToBuy( $productIDToConsider );
		$quantity = apply_filters( 'ced_cwsm_alter_quantity_in_cart_for_cart_page_message', $quantity, $productIDToConsider, $arrayToKeepTrackOfProductsMsgAddedFor );

		if ( $min_qty ) {
			$qty_difference = (int) $min_qty - (int) $quantity;
			if ( $qty_difference > 0 ) { // failure text case
				$wsp_applied_txt = get_option( CED_CWSM_PREFIX . 'wsp_applied_failure_txt' );
				$wsp_applied_txt = str_replace( '{*wm_product_name}', $product_name, $wsp_applied_txt );

				if ( WC()->version < '3.0.0' ) {
					$wsp_applied_txt = str_replace( '{*wm_price}', woocommerce_price( $product->get_price_including_tax( 1, $wholesale_price ) ), $wsp_applied_txt );
				} else {
					$wsp_applied_txt = str_replace( '{*wm_price}', wc_price( wc_get_price_including_tax( $product, $args ) ), $wsp_applied_txt );
				}
				$wsp_applied_txt = str_replace( '{*wm_min_qty}', $min_qty, $wsp_applied_txt );
				$wsp_applied_txt = str_replace( '{*wm_qty_diff}', $qty_difference, $wsp_applied_txt );
				$wsp_applied_txt = str_replace( '{*wm_cart_qty}', $quantity, $wsp_applied_txt );
				$msgToReturn     = '<div class="cwsm_custom_msg wholesale-error">' . $wsp_applied_txt . '</div>';
			} else { // success text case
				$wsp_applied_txt = get_option( CED_CWSM_PREFIX . 'wsp_applied_success_txt' );
				$wsp_applied_txt = str_replace( '{*wm_product_name}', $product_name, $wsp_applied_txt );
				if ( WC()->version < '3.0.0' ) {

					$wsp_applied_txt = str_replace( '{*wm_price}', woocommerce_price( $product->get_price_including_tax( 1, $wholesale_price ) ), $wsp_applied_txt );
				} else {

					$wsp_applied_txt = str_replace( '{*wm_price}', wc_price( wc_get_price_including_tax( $product, $args ) ), $wsp_applied_txt );
				}

				$wsp_applied_txt = str_replace( '{*wm_min_qty}', $min_qty, $wsp_applied_txt );
				$wsp_applied_txt = str_replace( '{*wm_qty_diff}', $qty_difference, $wsp_applied_txt );
				$wsp_applied_txt = str_replace( '{*wm_cart_qty}', $quantity, $wsp_applied_txt );
				$msgToReturn     = '<div class="cwsm_custom_msg woocommerce-success">' . $wsp_applied_txt . '</div>';
			}
		} else { // default text case
			$wsp_applied_txt = get_option( CED_CWSM_PREFIX . 'default_wsp_applied_txt' );
			$wsp_applied_txt = str_replace( '{*wm_product_name}', $product_name, $wsp_applied_txt );
			if ( WC()->version < '3.0.0' ) {
				$wsp_applied_txt = str_replace( '{*wm_price}', woocommerce_price( $product->get_price_including_tax( 1, $wholesale_price ) ), $wsp_applied_txt );
			} else {
				$wsp_applied_txt = str_replace( '{*wm_price}', wc_price( wc_get_price_including_tax( $product, $args ) ), $wsp_applied_txt );

			}
			$msgToReturn = '<div class="cwsm_custom_msg woocommerce-success">' . $wsp_applied_txt . '</div>';
		}

		$msgToReturn = apply_filters( 'ced_cwsm_alter_cart_page_msg', $msgToReturn, $wsp_applied_txt, $product_name, $wholesale_price, $arrayToKeepTrackOfProductsMsgAddedFor );
		return $msgToReturn;
	}

	/**
	 * This function comes into action when cart loaded using AJAX (fix for new WooCommerce Update).
	 *
	 * @name ced_cwsm_add_to_cart_fragments()

	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_add_to_cart_fragments( $fragments ) {
		global $globalCWSM;
		// check for plugin activation and wholesale-user
		if ( ! $globalCWSM->is_CWSM_plugin_active() || ! $globalCWSM->isCurrentUserIsWholesaleUser() ) {
			return $fragments;
		}
		// Creating object of ced_cwsm_woocommerce_before_cart()
		ob_start();
		$this->ced_cwsm_woocommerce_before_cart();
		$fragments['div.custom-msg-wrapper'] = ob_get_clean();
		return $fragments;
	}
}
// Create an instance of class
new CED_CWSM_Custom_Cart_Page_Message();

