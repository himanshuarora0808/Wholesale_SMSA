<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Adds wholesale-price in Price column on product listing pages.
 *
 * @class    CED_CWSM_ProductListingPage_Customization
 * @version  2.0.8
 * @package  wholesale-market/adminSide
 * @package Class
 */
class CED_CWSM_ProductListingPage_Customization {

	public function __construct() {
		if ( is_admin() ) {
			$this->add_hooks_and_filters();
		}
	}

	/**
	 * This function adds hooks and filter.
	 *
	 * @name add_hooks_and_filters()

	 * @link  http://www.cedcommerce.com/
	 */
	public function add_hooks_and_filters() {
		$proceed = get_option( CED_CWSM_PREFIX . 'show_in_price_column', false );
		if ( empty( $proceed ) || 'yes' != $proceed ) {
			return;
		}
		add_filter( 'manage_edit-product_columns', array( $this, 'add_wholesale_price_column' ), 15 );
		add_action( 'manage_product_posts_custom_column', array( $this, 'show_wholesale_price_column_value' ), 10, 2 );
	}

	/**
	 * This function adds column to show wholesale price on product listing Page.
	 *
	 * @name add_wholesale_price_column()

	 * @link  http://www.cedcommerce.com/
	 */
	public function add_wholesale_price_column( $columns ) {
		$columns['ced_cwsm_ws_price'] = __( 'Wholesale Price', 'wholesale-market' );
		return $columns;
	}

	/**
	 * This function show wholesale price to Wholesale-Price column on product listing Page.
	 *
	 * @name show_wholesale_price_column_value()

	 * @link  http://www.cedcommerce.com/
	 */
	public function show_wholesale_price_column_value( $column, $postid ) {
		if ( 'ced_cwsm_ws_price' == $column ) {
			global $globalCWSM;
			$product = wc_get_product( $postid );
			if ( $product->is_type( 'variable' ) ) {
				$variationsIdsArray = $product->get_available_variations();
				$priceRange         = array();

				foreach ( $variationsIdsArray as $key => $variation ) {
					$variation_id   = $variation['variation_id'];
					$wholesalePrice = $globalCWSM->getWholesalePrice( $variation_id );
					$priceRange[]   = $wholesalePrice;
				}

				$priceRange = array_filter( $priceRange );
				$priceRange = array_values( $priceRange );
				sort( $priceRange );
				$arrayLength = count( $priceRange );

				if ( $arrayLength > 1 ) {
					if ( $priceRange[0] != $priceRange[ count( $priceRange ) - 1 ] ) {
						$wholesalePriceRange = wc_price( $priceRange[0] ) . '-' . wc_price( $priceRange[ $arrayLength - 1 ] );
					} else {
						$wholesalePriceRange = wc_price( $priceRange[0] );
					}
				} elseif ( 1 == $arrayLength ) {
					$wholesalePriceRange = wc_price( $priceRange[0] );
				}

				if ( ! empty( $wholesalePriceRange ) ) {
					echo '<p style="color:purple;font-weight:bold;">' . esc_attr( $wholesalePriceRange ) . '</p>';
				} else {
					echo '<p style="color:purple;font-weight:bold;">-</p>';
				}
			} else {
				$wholesalePrice = $globalCWSM->getWholesalePrice( $postid );

				if ( $wholesalePrice ) {
					$wholesalePrice = wc_price( $wholesalePrice );
					echo '<p style="color:purple;font-weight:bold;">' . esc_attr( $wholesalePrice ) . '</p>';
				} else {
					echo '<p style="color:purple;font-weight:bold;">-</p>';
				}
			}
		}
	}
}
// Create an instance of class
new CED_CWSM_ProductListingPage_Customization();

