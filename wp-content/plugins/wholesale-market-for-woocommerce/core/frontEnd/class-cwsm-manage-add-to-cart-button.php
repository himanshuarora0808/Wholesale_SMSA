<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manage add to cart button on shop page and product single page when regular price of product is not available.
 *
 * @class    CED_CWSM_Manage_Add_To_Cart_Button
 * @version  2.0.8
 * @package  wholesale-market/core/frontEnd
 * @package Class
 */
class CED_CWSM_Manage_Add_To_Cart_Button {

	/**
	 * CED_CWSM_Manage_Add_To_Cart_Button Constructor.
	 */
	public function __construct() {
		$this->add_hooks_and_filters();
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since  1.0.8
	 */
	private function add_hooks_and_filters() {
		/* for simple product */
		add_filter( 'woocommerce_is_purchasable', array( $this, 'woocommerce_is_purchasable' ), 30, 2 );

		/* for variable product */
		add_filter( 'woocommerce_variation_is_visible', array( $this, 'woocommerce_variation_is_visible' ), 30, 4 );
		add_filter( 'woocommerce_variation_is_purchasable', array( $this, 'woocommerce_variation_is_purchasable' ), 10, 2 );
	}

	/**
	 * This function manage add-to-cart link for variable product.
	 *
	 * @name woocommerce_variation_is_visible()
	 * @link  http://www.cedcommerce.com/
	 */
	public function woocommerce_variation_is_visible( $visible, $variation_id, $id, $thisRef ) {
		if ( true == $visible ) {
			return $visible;
		}

		// Published == enabled checkbox
		if ( 'publish' != get_post_status( $thisRef->get_id() ) ) {
			$visible = false;
		} elseif ( '' === $thisRef->get_price() ) {
			global $globalCWSM;
			$wholesalePrice = $globalCWSM->getWholesalePrice( $variation_id );
			if ( ! empty( $wholesalePrice ) && '' != $wholesalePrice && '0' != $wholesalePrice ) {
				$visible = true;
			} else {
				$visible = false;
			}
		}
		return $visible;
	}

	/**
	 * This function manage add-to-cart link for variable product.
	 *
	 * @name woocommerce_variation_is_purchasable()

	 * @link  http://www.cedcommerce.com/
	 */
	public function woocommerce_variation_is_purchasable( $purchasable, $thisRef ) {
		if ( true == $purchasable ) {
			return $purchasable;
		}

		if ( 'publish' != get_post_status( $thisRef->get_id() ) ) {
			$purchasable = false;
		} else {
			global $globalCWSM;
			$wholesalePrice = $globalCWSM->getWholesalePrice( $thisRef->get_id() );
			if ( ! empty( $wholesalePrice ) && '' != $wholesalePrice && '0' != $wholesalePrice ) {
				$purchasable = true;
			} else {
				$purchasable = false;
			}
		}
		return $purchasable;
		// var_dump($purchasable); die();
	}

	/**
	 * This function manage add-to-cart link for simple product.
	 *
	 * @name woocommerce_is_purchasable()
	 * @link  http://www.cedcommerce.com/
	 */
	public function woocommerce_is_purchasable( $purchasable, $thisRef ) {
		if ( true == $purchasable ) {
			return $purchasable;
		}

		// Retrieve product status according to Woo version.
		if ( WC()->version < '3.0.0' ) {
			$post_status = $thisRef->post->post_status;
		} else {
			$post_status = $thisRef->get_status();
		}

		// Products must exist of course
		if ( ! $thisRef->exists() ) {
			$purchasable = false;
			// Other products types need a price to be set
		} elseif ( '' === $thisRef->get_price() ) {
			global $globalCWSM;
			$wholesalePrice = $globalCWSM->getWholesalePrice( $thisRef->get_id() );
			if ( ! empty( $wholesalePrice ) && '' != $wholesalePrice && '0' != $wholesalePrice ) {
				$purchasable = true;
			} else {
				$purchasable = false;
			}
			// Check the product is published
		} elseif ( 'publish' !== $post_status && ! current_user_can( 'edit_post', $thisRef->get_id() ) ) {
			$purchasable = false;
		}
		return $purchasable;
	}
}
// Create an instance of class
new CED_CWSM_Manage_Add_To_Cart_Button();

