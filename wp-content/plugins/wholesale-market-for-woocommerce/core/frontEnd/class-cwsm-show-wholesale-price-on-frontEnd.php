<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Displays Wholesale-Price on front-end.
 *
 * @class    CED_CWSM_Show_Wholesale_Price_On_FrontEnd
 * @version  2.0.11
 * @package  wholesale-market/frontEnd
 * @package Class
 */
class CED_CWSM_Show_Wholesale_Price_On_FrontEnd {
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
	 * This function displays whoelsale-price on shop page.
	 *
	 * @name cwsm_show_wholesale_price_shop_page()

	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_show_wholesale_price_shop_page() {

		global  $globalCWSM,$product;
		$user_id = get_current_user_id();
		if ( 0 == $user_id ) {
			$price = wc_get_product( $product->get_id() );

			if ( $price->get_price() == '' || $price->get_price() == '0' ) {
				remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart' );
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
				remove_action( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
				remove_action( 'woocommerce_grouped_add_to_cart', 'woocommerce_grouped_add_to_cart', 30 );
				remove_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );
				remove_action( 'woocommerce_external_add_to_cart', 'woocommerce_external_add_to_cart', 30 );
				remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation', 10 );
				remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );

			}
		}

		if ( ! $globalCWSM->isActiveUserCanSeeWholesalePrice() ) {
			return;
		}

		if ( $product->is_type( 'simple' ) || $product->is_type( 'grouped' ) ) {   // for simple product
			if ( WC()->version < '3.0.0' ) {
					$productId = $product->id;
			} else {
				$productId = $product->get_id();
			}

			$wholesalePrice = $globalCWSM->getWholesalePrice( $productId );
			$args['price']  = $wholesalePrice;
			$args['qty']    = 1;
			if ( ! empty( $wholesalePrice ) ) {
				$customShopPageTxt = get_option( CED_CWSM_PREFIX . 'custm_shop_txt' );
				$deafultWSTxt      = get_option( CED_CWSM_PREFIX . 'default_wm_price_txt' );
				$isMinQtyEnable    = get_option( CED_CWSM_PREFIX . 'enable_minQty' );
				$minQtyPicker      = get_option( CED_CWSM_PREFIX . 'radio_minQty_picker' );
				if ( 'yes' == $isMinQtyEnable ) {
					if ( CED_CWSM_PREFIX . 'radio_individual_minQty' == $minQtyPicker ) {
						$minQtyCheck = (int) get_post_meta( $productId, 'ced_cwsm_min_qty_to_buy', true );
					} else {
						$minQtyCheck = (int) get_option( CED_CWSM_PREFIX . 'central_min_qty' );
					}
				}
				if ( empty( $minQtyCheck ) || 0 == $minQtyCheck || 'yes' != $isMinQtyEnable ) {
					if ( WC()->version < '3.0.0' ) {
						$deafultWSTxt = str_replace( '{*wm_price}', woocommerce_price( $product->get_price_including_tax( 1, $wholesalePrice ) ), $deafultWSTxt );
					} else {
						$deafultWSTxt = str_replace( '{*wm_price}', wc_price( wc_get_price_to_display( $product, $args ) ), $deafultWSTxt );
					}
					$deafultWSTxt = apply_filters( 'ced_cwsm_alter_price_text_on_shop_page', $deafultWSTxt, $productId );
					echo '<span class="price ced_cwsm_price">' . ( $deafultWSTxt ) . '</span>';
				} else {
					if ( WC()->version < '3.0.0' ) {
						$customShopPageTxt = str_replace( '{*wm_price}', woocommerce_price( $product->get_price_including_tax( 1, $wholesalePrice ) ), $customShopPageTxt );
					} else {

						$customShopPageTxt = str_replace( '{*wm_price}', wc_price( wc_get_price_to_display( $product, $args ) ), $customShopPageTxt );
					}
					$customShopPageTxt = str_replace( '{*wm_min_qty}', $minQtyCheck, $customShopPageTxt );
					$customShopPageTxt = apply_filters( 'ced_cwsm_alter_price_text_on_shop_page', $customShopPageTxt, $productId );
					echo '<span class="price ced_cwsm_price">' . ( $customShopPageTxt ) . '</span>';
				}
			}
		} elseif ( $product->is_type( 'variable' ) ) {      // for variable product
			$variationsIdsArray = $product->get_available_variations();
			$priceRange         = array();
			$minQtyRange        = array();

			foreach ( $variationsIdsArray as $key => $variation ) {
				$variation_id = $variation['variation_id'];
				/*?????*/
				$wholesalePrice = $globalCWSM->getWholesalePrice( $variation_id );

				$priceRange[] = $wholesalePrice;
				/*?????*/
				$minQtyRange[] = (int) get_post_meta( $variation_id, 'ced_cwsm_min_qty_to_buy', true );
			}

			$isMinQtyEnable = get_option( CED_CWSM_PREFIX . 'enable_minQty' );
			$minQtyPicker   = get_option( CED_CWSM_PREFIX . 'radio_minQty_picker' );
			if ( 'yes' == $isMinQtyEnable ) {
				if ( CED_CWSM_PREFIX . 'radio_individual_minQty' == $minQtyPicker ) {
					$minQtyRange = array_filter( $minQtyRange );
					$minQtyRange = array_values( $minQtyRange );
					sort( $minQtyRange );

					$arrayLengthminQty = count( $minQtyRange );

					if ( $arrayLengthminQty > 1 ) {
						if ( $minQtyRange[0] != $minQtyRange[ $arrayLengthminQty - 1 ] ) {
							$wholesaleMinQtyRange =
							$minQtyRange[0] . '-' . $minQtyRange[ $arrayLengthminQty - 1 ];
						} else {
							$wholesaleMinQtyRange = $minQtyRange[0];
						}
					} elseif ( 1 == $arrayLengthminQty ) {
						$wholesaleMinQtyRange = $minQtyRange[0];
					}
				} else {
					$wholesaleMinQtyRange = (int) get_option( CED_CWSM_PREFIX . 'central_min_qty' );
				}
			}

			$priceRange = array_filter( $priceRange );
			$priceRange = array_values( $priceRange );
			sort( $priceRange );
			$arrayLength = count( $priceRange );
			foreach ( $priceRange as $key => $value ) {

				$args[] = array(
					'price' => $value,
					'qty'   => 1,
				);
			}
			if ( $arrayLength > 1 ) {
				if ( $priceRange[0] != $priceRange[ count( $priceRange ) - 1 ] ) {
					if ( WC()->version < '3.0.0' ) {
						$wholesalePriceRange = woocommerce_price( $product->get_price_including_tax( 1, $priceRange[0] ) ) . '-' . woocommerce_price( $product->get_price_including_tax( 1, $priceRange[ $arrayLength - 1 ] ) );
					} else {
						$args1               = end( $args );
						$wholesalePriceRange = wc_price( wc_get_price_to_display( $product, $args[0] ) ) . '-' . wc_price( wc_get_price_to_display( $product, $args1 ) );
					}
				} else {
					if ( WC()->version < '3.0.0' ) {
						$wholesalePriceRange = woocommerce_price( $product->get_price_including_tax( 1, $priceRange[0] ) );
					} else {
						$wholesalePriceRange = wc_price( wc_get_price_to_display( $product, $args[0] ) );
					}
				}
			} elseif ( 1 == $arrayLength ) {
				if ( WC()->version < '3.0.0' ) {
					$wholesalePriceRange = woocommerce_price( $product->get_price_including_tax( 1, $priceRange[0] ) );
				} else {
					$wholesalePriceRange = wc_price( wc_get_price_to_display( $product, $args[0] ) );
				}
			}

			if ( ! empty( $wholesalePriceRange ) ) {
				$customShopPageTxt = get_option( CED_CWSM_PREFIX . 'custm_shop_txt' );
				$deafultWSTxt      = get_option( CED_CWSM_PREFIX . 'default_wm_price_txt' );

				if ( empty( $wholesaleMinQtyRange ) ) {
					$deafultWSTxt = str_replace( '{*wm_price}', $wholesalePriceRange, $deafultWSTxt );
					$deafultWSTxt = apply_filters( 'ced_cwsm_alter_price_text_on_shop_page', $deafultWSTxt, null );
					echo '<span class="price ced_cwsm_price">' . ( $deafultWSTxt ) . '</span>';
				} else {
					$customShopPageTxt = str_replace( '{*wm_price}', $wholesalePriceRange, $customShopPageTxt );
					$customShopPageTxt = str_replace( '{*wm_min_qty}', $wholesaleMinQtyRange, $customShopPageTxt );
					$customShopPageTxt = apply_filters( 'ced_cwsm_alter_price_text_on_shop_page', $customShopPageTxt, null );
					echo '<span class="price ced_cwsm_price">' . ( $customShopPageTxt ) . '</span>';
				}
			}
		}
	}

	/**
	 * This function displays whoelsale-price on product single page.
	 *
	 * @name cwsm_show_wholesale_price_single_page()

	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_show_wholesale_price_single_page() {
		global  $globalCWSM,$product;
		$user_id = get_current_user_id();
		if ( 0 == $user_id ) {
			$price = wc_get_product( $product->get_id() );

			if ( '' == $price->get_price() || '0' == $price->get_price() ) {
				remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart' );
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
				remove_action( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
				remove_action( 'woocommerce_grouped_add_to_cart', 'woocommerce_grouped_add_to_cart', 30 );
				remove_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );
				remove_action( 'woocommerce_external_add_to_cart', 'woocommerce_external_add_to_cart', 30 );
				remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation', 10 );
				remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );

			}
		}
		if ( ! $globalCWSM->isActiveUserCanSeeWholesalePrice() ) {
			return;
		}

		global $post;
		$productId = $post->ID;
		if ( WC()->version < '3.0.0' ) {
			$_product = get_product( $productId );
		} else {
			$_product = wc_get_product( $productId );
		}
		if ( $_product->is_type( 'variable' ) ) {
			$variations  = $_product->get_available_variations();
			$priceRange  = array();
			$minQtyRange = array();
			foreach ( $variations as $key => $variation ) {
				$variation_id   = $variation ['variation_id'];
				$wholesalePrice = $globalCWSM->getWholesalePrice( $variation_id );
				$priceRange[]   = $wholesalePrice;
				$minQtyRange[]  = (int) get_post_meta( $variation_id, 'ced_cwsm_min_qty_to_buy', true );
			}

			$isMinQtyEnable = get_option( CED_CWSM_PREFIX . 'enable_minQty' );
			$minQtyPicker   = get_option( CED_CWSM_PREFIX . 'radio_minQty_picker' );
			if ( 'yes' == $isMinQtyEnable ) {
				if ( CED_CWSM_PREFIX . 'radio_individual_minQty' == $minQtyPicker ) {
					$minQtyRange = array_filter( $minQtyRange );
					$minQtyRange = array_values( $minQtyRange );
					sort( $minQtyRange );

					$arrayLength = count( $minQtyRange );

					if ( $arrayLength > 1 ) {
						if ( $minQtyRange [0] != $minQtyRange [ count( $minQtyRange ) - 1 ] ) {
							$wholesaleMinQtyRange = $minQtyRange[0] . '-' . $minQtyRange [ $arrayLength - 1 ];
						} else {
							$wholesaleMinQtyRange = $minQtyRange[0];
						}
					} elseif ( 1 == $arrayLength ) {
						$wholesaleMinQtyRange = $minQtyRange[0];
					}
				} else {
					$wholesaleMinQtyRange = (int) get_option( CED_CWSM_PREFIX . 'central_min_qty' );
				}
			}

			$priceRange = array_filter( $priceRange );
			$priceRange = array_values( $priceRange );
			sort( $priceRange );
			$arrayLength = count( $priceRange );
			foreach ( $priceRange as $key => $value ) {
				$args[] = array(
					'price' => $value,
					'qty'   => 1,
				);
			}

			if ( $arrayLength > 1 ) {
				if ( $priceRange [0] != $priceRange [ count( $priceRange ) - 1 ] ) {
					if ( WC()->version < '3.0.0' ) {
						$wholesalePriceRange = woocommerce_price( $_product->get_price_including_tax( 1, $priceRange[0] ) ) . '-' . woocommerce_price( $_product->get_price_including_tax( 1, $priceRange[ $arrayLength - 1 ] ) );
					} else {
						$args1               = end( $args );
						$wholesalePriceRange = wc_price( wc_get_price_to_display( $_product, $args[0] ) ) . '-' . wc_price( wc_get_price_to_display( $_product, $args1 ) );
					}
				} else {
					if ( WC()->version < '3.0.0' ) {
						$wholesalePriceRange = woocommerce_price( $_product->get_price_including_tax( 1, $priceRange[0] ) );
					} else {
						$wholesalePriceRange = wc_price( wc_get_price_to_display( $_product, $args[0] ) );
					}
				}
			} elseif ( 1 == $arrayLength ) {
				if ( WC()->version < '3.0.0' ) {
					$wholesalePriceRange = woocommerce_price( $_product->get_price_including_tax( 1, $priceRange[0] ) );
				} else {
					$wholesalePriceRange = wc_price( wc_get_price_to_display( $_product, $args[0] ) );
				}
			}

			if ( ! empty( $wholesalePriceRange ) ) {
				$customShopPageTxt = get_option( CED_CWSM_PREFIX . 'custm_shop_txt' );
				$deafultWSTxt      = get_option( CED_CWSM_PREFIX . 'default_wm_price_txt' );

				if ( empty( $wholesaleMinQtyRange ) ) {
					$deafultWSTxt = str_replace( '{*wm_price}', $wholesalePriceRange, $deafultWSTxt );
					$deafultWSTxt = apply_filters( 'ced_cwsm_alter_price_text_on_single_page', $deafultWSTxt, null );
					echo '<p class="price">' . esc_attr( $deafultWSTxt ) . '</p>';
				} else {
					$customShopPageTxt = str_replace( '{*wm_price}', $wholesalePriceRange, $customShopPageTxt );
					$customShopPageTxt = str_replace( '{*wm_min_qty}', $wholesaleMinQtyRange, $customShopPageTxt );
					$customShopPageTxt = apply_filters( 'ced_cwsm_alter_price_text_on_single_page', $customShopPageTxt, null );
					echo '<p class="price">' . esc_attr( $customShopPageTxt ) . '</p>';
				}
			}
		} elseif ( $_product->is_type( 'simple' ) ) {
			$wholesalePrice = $globalCWSM->getWholesalePrice( $post->ID );
			$args['price']  = $wholesalePrice;
			$args['qty']    = 1;
			if ( ! empty( $wholesalePrice ) ) {
				$customProductPageTxt = get_option( CED_CWSM_PREFIX . 'custm_product_txt' );
				$deafultWSTxt         = get_option( CED_CWSM_PREFIX . 'default_wm_price_txt' );

				$isMinQtyEnable = get_option( CED_CWSM_PREFIX . 'enable_minQty' );
				$minQtyPicker   = get_option( CED_CWSM_PREFIX . 'radio_minQty_picker' );
				if ( 'yes' == $isMinQtyEnable ) {
					if ( CED_CWSM_PREFIX . 'radio_individual_minQty' == $minQtyPicker ) {
						$minQtyCheck = (int) get_post_meta( $post->ID, 'ced_cwsm_min_qty_to_buy', true );
					} else {
						$minQtyCheck = (int) get_option( CED_CWSM_PREFIX . 'central_min_qty' );
					}
				}

				if ( empty( $minQtyCheck ) || 0 == $minQtyCheck || 'yes' != $isMinQtyEnable ) {
					if ( WC()->version < '3.0.0' ) {
						$deafultWSTxt = str_replace( '{*wm_price}', woocommerce_price( $_product->get_price_including_tax( 1, $wholesalePrice ) ), $deafultWSTxt );
					} else {
						$deafultWSTxt = str_replace( '{*wm_price}', wc_price( wc_get_price_to_display( $_product, $args ) ), $deafultWSTxt );
					}
					$deafultWSTxt = apply_filters( 'ced_cwsm_alter_price_text_on_single_page', $deafultWSTxt, $post->ID );
					echo '<p class="price">' . ( $deafultWSTxt ) . '</p>';
				} else {
					if ( WC()->version < '3.0.0' ) {
						$customProductPageTxt = str_replace( '{*wm_price}', woocommerce_price( $_product->get_price_including_tax( 1, $wholesalePrice ) ), $customProductPageTxt );
					} else {
						$customProductPageTxt = str_replace( '{*wm_price}', wc_price( wc_get_price_to_display( $_product, $args ) ), $customProductPageTxt );
					}
					$customProductPageTxt = str_replace( '{*wm_min_qty}', $minQtyCheck, $customProductPageTxt );
					$customProductPageTxt = apply_filters( 'ced_cwsm_alter_price_text_on_single_page', $customProductPageTxt, $post->ID );
					echo '<p class="price">' . ( $customProductPageTxt ) . '</p>';
				}
			}
		}
	}

	/**
	 * This function displays whoelsale-price on changing variation product.
	 *
	 * @name cwsm_show_variation_wholesale_price()

	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_show_variation_wholesale_price( $htmlArray, $thisRef, $variationCopy ) {
		global $globalCWSM;
		if ( ! $globalCWSM->isActiveUserCanSeeWholesalePrice() ) {
			return $htmlArray;
		}

		$variationId          = $htmlArray ['variation_id'];
		$wholesalePrice       = $globalCWSM->getWholesalePrice( $variationId );
		$args['price']        = $wholesalePrice;
		$args['qty']          = 1;
		$customProductPageTxt = get_option( CED_CWSM_PREFIX . 'custm_product_txt' );
		$deafultWSTxt         = get_option( CED_CWSM_PREFIX . 'default_wm_price_txt' );

		$isMinQtyEnable = get_option( CED_CWSM_PREFIX . 'enable_minQty' );
		$minQtyPicker   = get_option( CED_CWSM_PREFIX . 'radio_minQty_picker' );
		if ( 'yes' == $isMinQtyEnable ) {
			if ( CED_CWSM_PREFIX . 'radio_individual_minQty' == $minQtyPicker ) {
				$minQtyCheck = (int) get_post_meta( $variationId, 'ced_cwsm_min_qty_to_buy', true );
			} else {
				$minQtyCheck = (int) get_option( CED_CWSM_PREFIX . 'central_min_qty' );
			}
		}

		if ( empty( $wholesalePrice ) ) {
			$htmlArray['price_html'] = apply_filters( 'ced_cwsm_alter_variation_price', $htmlArray ['price_html'], $variationCopy, $deafultWSTxt = '' );
			return $htmlArray;
		}

		if ( empty( $minQtyCheck ) || 0 == $minQtyCheck ) {
			if ( WC()->version < '3.0.0' ) {
				$deafultWSTxt = str_replace( '{*wm_price}', woocommerce_price( $thisRef->get_price_including_tax( 1, $wholesalePrice ) ), $deafultWSTxt );
			} else {
				$deafultWSTxt = str_replace( '{*wm_price}', wc_price( wc_get_price_to_display( $thisRef, $args ) ), $deafultWSTxt );
			}
			$htmlArray['price_html'] = '<span class="price">' . $variationCopy->get_price_html() . '<br/>' . $deafultWSTxt . '</span>';
			$htmlArray['price_html'] = apply_filters( 'ced_cwsm_alter_variation_price', $htmlArray ['price_html'], $variationCopy, $deafultWSTxt );
		} else {
			if ( WC()->version < '3.0.0' ) {
				$customProductPageTxt = str_replace( '{*wm_price}', woocommerce_price( $thisRef->get_price_including_tax( 1, $wholesalePrice ) ), $customProductPageTxt );
			} else {
				$customProductPageTxt = str_replace( '{*wm_price}', wc_price( wc_get_price_to_display( $thisRef, $args ) ), $customProductPageTxt );
			}
			$customProductPageTxt    = str_replace( '{*wm_min_qty}', $minQtyCheck, $customProductPageTxt );
			$htmlArray['price_html'] = '<span class="price">' . $variationCopy->get_price_html() . '<br/>' . $customProductPageTxt . '</span>';
			$htmlArray['price_html'] = apply_filters( 'ced_cwsm_alter_variation_price', $htmlArray ['price_html'], $variationCopy, $customProductPageTxt );
		}
		return $htmlArray;
	}
}

