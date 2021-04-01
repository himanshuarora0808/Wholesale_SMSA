<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Wholesale-Price Handler for wholesale-users.
 *
 * @class    CED_CWSM_Manage_Wholesale_Price
 * @version  2.0.8
 * @package  wholesale-market/frontEnd
 * @package Class
 */
class CED_CWSM_Manage_Wholesale_Price {
	// store the single instance
	private static $_instance;
	/*
	 * Get an instance of the database
	 * @return database
	 */
	public static function getInstance() {
		if ( ! self::$_instance instanceof self ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * This function handle and manages wholesale-price in cart for wholesale-users.
	 *
	 * @name cwsm_mange_wholesale_price()
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_mange_wholesale_price( $cartData ) {

		global $globalCWSM;
		// checks if the current user is a wholesale user or not
		if ( $globalCWSM->isCurrentUserIsWholesaleUser() ) {

			foreach ( $cartData->cart_contents as $key => $value ) {

				// code to extract product-id :: start
				$productId = $value['product_id'];
				if ( 0 != $value['variation_id'] ) {
					$productId = $value['variation_id'];
				}
				// code to extract product-id :: end

				$wholesalePrice      = $globalCWSM->getWholesalePrice( $productId );
				$applyWholesalePrice = true;
				$applyWholesalePrice = apply_filters( 'ced_cwsm_avoid_wholesale_price_to_apply', $applyWholesalePrice, $productId, $value );

				$applyWholesalePrice = apply_filters( 'ced_cwsm_check_to_apply_wholesale_price', $applyWholesalePrice, $productId, $value );

				if ( ! empty( $wholesalePrice ) && $applyWholesalePrice ) {
					if ( WC()->version < '3.0.0' ) {
						$value['data']->price      = $wholesalePrice;
						$value['data']->sale_price = $wholesalePrice;
					} else {
						$value['data']->set_price( $wholesalePrice );
						$value['data']->set_sale_price( $wholesalePrice );
					}
				}
				unset( $wholesalePrice );
			}
		}
	}
}

