<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Adds "Consider Variations As One Module" condition to core plugin using hooks and filters of that.
 *
 * @class    CED_CWSM_Consider_Variations_As_One_Module
 * @version  1.0.2
 * @package  wholesale-market-product-addon/addons/consider-variations-as-one
 * @package Class
 */
?>
<?php
class CED_CWSM_Consider_Variations_As_One_Module {

	public function __construct() {

		global $ced_cwsm_product_addon_license; // it must be dynamic for each extension
		$license_key = get_option( 'ced_cwsm_product_addon_license_key', false );
		$module_name = get_option( 'ced_cwsm_product_addon_license_module', false );
		if ( is_array( $ced_cwsm_product_addon_license ) ) {
			$HTTP_HOST = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( $_SERVER['HTTP_HOST'] ) : '';
			if ( $ced_cwsm_product_addon_license['license'] == $license_key && $ced_cwsm_product_addon_license['module_name'] == $module_name && $ced_cwsm_product_addon_license['domain'] == $HTTP_HOST ) {
				$this->add_hooks_and_filters();
			}
		}
		$this->add_hooks_and_filters();

	}

	/**
	 * This function hooks into all filters and actions available in core plugin.
	 *
	 * @name add_hooks_and_filters()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function add_hooks_and_filters() {

		add_filter( 'woocommerce_product_data_tabs', array( $this, 'woocommerce_product_data_tabs' ), 10, 1 );
		add_action( 'woocommerce_product_data_panels', array( $this, 'woocommerce_product_data_panels' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'woocommerce_process_product_meta' ) );

		/* admin panel */
		add_action( 'admin_enqueue_scripts', array( $this, 'ced_cwsm_consider_variations_as_one_module_admin_enqueue_scripts' ) );
		add_filter( 'ced_cwsm_append_product_addon_sections', array( $this, 'ced_cwsm_consider_variations_as_one_module_add_section' ), 10, 1 );
		add_filter( 'ced_cwsm_append_product_addon_settings', array( $this, 'ced_cwsm_consider_variations_as_one_module_add_setting' ), 10, 2 );

		add_filter( 'ced_cwsm_alter_qty_to_buy_multiplier', array( $this, 'ced_cwsm_alter_qty_to_buy_multiplier' ), 10, 2 );

		$ced_cwsm_enable_consider_variations_as_one = get_option( 'ced_cwsm_enable_consider_variations_as_one', false );
		if ( empty( $ced_cwsm_enable_consider_variations_as_one ) || 'no' == $ced_cwsm_enable_consider_variations_as_one ) {
			return;
		}
		/** Hooks and filters to alter cart page message */
		add_filter( 'ced_cwsm_is_render_msg_line_on_cart_page', array( $this, 'ced_cwsm_is_render_msg_line_on_cart_page' ), 10, 6 );
		add_filter( 'ced_cwsm_decide_productId_to_consider_on_cart_page', array( $this, 'ced_cwsm_decide_productId_to_consider_on_cart_page' ), 10, 3 );
		add_filter( 'ced_cwsm_alter_product_name_on_cart_page', array( $this, 'ced_cwsm_alter_product_name_on_cart_page' ), 10, 2 );

		add_filter( 'ced_cwsm_isHaveToRender_ced_cwsm_min_qty_to_buy', array( $this, 'ced_cwsm_isHaveToRender_ced_cwsm_min_qty_to_buy' ), 10, 1 );

		/** Hooks and filters to manage price */
		add_filter( 'ced_cwsm_alter_quantity_in_cart_for_cart_page_message', array( $this, 'ced_cwsm_alter_quantity_in_cart_for_cart_page_message' ), 10, 3 );
		add_filter( 'ced_cwsm_avoid_wholesale_price_to_apply', array( $this, 'ced_cwsm_avoid_wholesale_price_to_apply' ), 10, 3 );
	}

	public function ced_cwsm_alter_qty_to_buy_multiplier( $ced_cwsm_qty_to_buy_multiplier, $product_id ) {
		global $globalCWSM;
		$role_identifier = $globalCWSM->getCurrentWholesaleUserRole();
		( 'ced_cwsm_wholesale_user' == $role_identifier ) ? $role_identifier = '' : $role_identifier = '_' . $role_identifier;

		$ced_cwsm_qty_to_buy_multiplier_enable = get_option( 'ced_cwsm_qty_to_buy_multiplier_enable', false );
		if ( 'yes' == $ced_cwsm_qty_to_buy_multiplier_enable ) {
			$_product = wc_get_product( $product_id );

			$prod_id = $_product->get_id();
			if ( isset( $prod_id ) ) {

				if ( 'variation' == $_product->get_type() || 'variable' == $_product->get_type() ) {
					$product_id = $_product->get_parent_id();
				} else {
					$product_id = $_product->get_id();
				}

				$ced_cwsm_qty_to_buy_multiplier = get_post_meta( $product_id, 'ced_cwsm_qty_to_buy_multiplier' . $role_identifier, true );
			}
			// print_r($ced_cwsm_qty_to_buy_multiplier);die;
		}
		return $ced_cwsm_qty_to_buy_multiplier;
	}


	public function ced_cwsm_isHaveToRender_ced_cwsm_min_qty_to_buy( $isHaveToRender ) {
		return false;
	}

	/**
	 * This function check whether to render message on cart-page or not, used to avoid multiple message for different variations of a variable product.
	 *
	 * @name ced_cwsm_is_render_msg_line_on_cart_page()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_is_render_msg_line_on_cart_page( $proceed, $product_id, $variation_id, $quantity, $price, $arrayToKeepTrackOfProductsMsgAddedFor ) {
		if ( in_array( $product_id, $arrayToKeepTrackOfProductsMsgAddedFor ) ) {
			return false;
		}
		return $proceed;
	}

	/**
	 * This function set parent product-id of variation product as key to consider.
	 *
	 * @name ced_cwsm_decide_productId_to_consider_on_cart_page()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_decide_productId_to_consider_on_cart_page( $productKeyToAppend, $product_id, $variation_id ) {
		return $product_id;
	}

	/**
	 * This function avoids attributes name after variable product name on cart page message.
	 *
	 * @name ced_cwsm_alter_product_name_on_cart_page()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_alter_product_name_on_cart_page( $isRenderProductExtraInfo, $arrayToKeepTrackOfProductsMsgAddedFor ) {
		return false;
	}


	/**
	 * This function alters quantity on cart page, i.e., sum up all quantity of variations as one.
	 *
	 * @name ced_cwsm_alter_quantity_in_cart_for_cart_page_message()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_alter_quantity_in_cart_for_cart_page_message( $quantity, $productIDToConsider, $arrayToKeepTrackOfProductsMsgAddedFor ) {
		global $woocommerce;
		$cart     = $woocommerce->cart->cart_contents;
		$finalQty = 0;
		if ( in_array( $productIDToConsider, $arrayToKeepTrackOfProductsMsgAddedFor ) ) {
			foreach ( $cart as $cart_item ) {
				$product_id = $cart_item['product_id'];
				if ( $product_id == $productIDToConsider ) {
					$quantity  = (int) $cart_item['quantity'];
					$finalQty += $quantity;
				}
			}
			return $finalQty;
		}
		return $quantity;
	}

	/**
	 * This function check whether to apply wholesale-price or not, i.e., sum of all variations of a product matches the min qty to buy or not.
	 *
	 * @name ced_cwsm_avoid_wholesale_price_to_apply()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_avoid_wholesale_price_to_apply( $applyWholesalePrice, $productId, $value ) {
		$productId = isset( $value['product_id'] ) ? $value['product_id'] : $productId;
		global $woocommerce,$globalCWSM;
		$cart    = $woocommerce->cart->cart_contents;
		$min_qty = $globalCWSM->getMinQtyToBuy( $productId );

		if ( isset( $value['variation_id'] ) && '0' != $value['variation_id'] ) {
			$quantity = 0;
			foreach ( $cart as $cart_item ) {
				if ( $productId == $cart_item['product_id'] ) {
					$temp_quantity = (int) $cart_item['quantity'];
					$quantity     += $temp_quantity;
				}
			}
			if ( $min_qty ) {
				$qty_difference = (int) $min_qty - (int) $quantity;
				if ( $qty_difference > 0 ) { // failure case
					return false;
				} else { // success case
					return true;
				}
			} else {
				return $applyWholesalePrice;
			}
		}
		return $applyWholesalePrice;
	}


	/**
	 * This function adds tab to variable product section to show meta-fields common to all variations.
	 *
	 * @name woocommerce_product_data_tabs()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function woocommerce_product_data_tabs( $product_data_tabs ) {
		global $post,$globalCWSM;
		$product = wc_get_product( get_the_ID() );
		if ( $product->is_type( 'simple' ) ) {
			return $product_data_tabs;
		}
		$commonFields      = $globalCWSM->getCommonFieldsForAllVariations();
		$commonFieldsFound = false;
		foreach ( $commonFields as $commonField ) {
			if ( get_option( $commonField['id'] . '_enable', false ) == 'yes' ) {
				$commonFieldsFound = true;
				break;
			}
		}

		// $ced_cwsm_enable_consider_variations_as_one = get_option('ced_cwsm_enable_consider_variations_as_one', false);
		// $ced_cwsm_min_qty_to_buy = get_option('ced_cwsm_enable_minQty', false);
		// if( !empty($ced_cwsm_enable_consider_variations_as_one) && $ced_cwsm_enable_consider_variations_as_one == "yes" && $product->product_type == "variable" && !empty($ced_cwsm_min_qty_to_buy) && $ced_cwsm_min_qty_to_buy == "yes") {
		// $product_data_tabs['ced_cwsm_wholesale_setting_tab'] = array(
		// 'label' => __( 'Wholesale Market', 'wholesale-market' ),
		// 'target' => 'ced_cwsm_wholesale_setting_tab',
		// );
		// }
		if ( $commonFieldsFound ) {
			$product_data_tabs['ced_cwsm_wholesale_setting_tab'] = array(
				'label'  => __( 'Wholesale Market', 'wholesale-market' ),
				'target' => 'ced_cwsm_wholesale_setting_tab',
			);
		}
		return $product_data_tabs;
	}

	/**
	 * This function adds panel to variable product section to show meta-fields common to all variations.
	 *
	 * @name woocommerce_product_data_panels()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function woocommerce_product_data_panels() {
		// global $post;
		// $product = wc_get_product($post->id);
		// $ced_cwsm_enable_consider_variations_as_one = get_option('ced_cwsm_enable_consider_variations_as_one', false);
		// $ced_cwsm_min_qty_to_buy = get_option('ced_cwsm_enable_minQty', false);
		// if( !empty($ced_cwsm_enable_consider_variations_as_one) && $ced_cwsm_enable_consider_variations_as_one == "yes" && $product->product_type == "variable" && !empty($ced_cwsm_min_qty_to_buy) && $ced_cwsm_min_qty_to_buy == "yes") {
		global $post,$globalCWSM;
		$product = wc_get_product( get_the_ID() );

		if ( $product->is_type( 'simple' ) ) {
			return;
		} else {
			$commonFields = $globalCWSM->getCommonFieldsForAllVariations();

			$commonFieldsFound = false;
			foreach ( $commonFields as $commonField ) {
				if ( get_option( $commonField['id'] . '_enable', false ) == 'yes' ) {
					// print_r($commonField);die("dsfs");
					$commonFieldsFound = true;
					break;
				}
			}
			if ( $commonFieldsFound ) {
				?>
				<div id="ced_cwsm_wholesale_setting_tab" class="panel woocommerce_options_panel ced_cwsm_fields_wrapper">
					<?php
					echo '<div class="ced_cwsm_user_section_div">';
					echo '<h3>' . esc_attr_e( 'Wholesale-User Related Options', 'wholesale-market' ) . '</h3>';
					foreach ( $commonFields as $commonField ) {
						if ( ! $globalCWSM->isHaveToRenderMetaField( $commonField['id'] ) ) {
							$previousValue         = get_post_meta( get_the_ID(), $commonField['id'], true );
							$attribute_description = $commonField['description'];
							if ( 'number' == $commonField['type'] ) {
								woocommerce_wp_text_input(
									array(
										'id'          => $commonField['id'],
										'label'       => $commonField['label'],
										'desc_tip'    => true,
										'description' => $attribute_description,
										'type'        => 'number',
										'custom_attributes' => array(
											'min'  => '0',
											'step' => '1',
										),
										'data_type'   => 'stock',
										'value'       => $previousValue,
									)
								);
							} elseif ( 'checkbox' == $commonField['type'] ) {
								woocommerce_wp_checkbox(
									array(
										'id'          => $commonField['id'],
										'label'       => $commonField['label'],
										'desc_tip'    => true,
										'description' => __( $attribute_description, 'wholesale-market' ),
									)
								);
							} else {
								do_action( 'ced_cwsm_render_common_fields_for_all_variations', $commonField, '' );
							}
						}
					}
					echo '</div>';
					do_action( 'ced_cwsm_render_common_fields_for_all_variations_for_different_wholesale_role', $commonFields );
					?>
				</div>
				<?php
			}
		}
	}

	/**
	 * This function saves meta-fields common to all variations.
	 *
	 * @name woocommerce_process_product_meta()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function woocommerce_process_product_meta( $productID ) {
		global $globalCWSM;
		if ( ! isset( $_POST['woocommerce_meta_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ) ) ) {
			$commonFields = $globalCWSM->getCommonFieldsForAllVariations();
		}
		$commonFields = $globalCWSM->getCommonFieldsForAllVariations();
		foreach ( $commonFields as $commonField ) {
			$valueToSave = ( isset( $_POST[ $commonField['id'] ] ) ) ? sanitize_text_field( wc_format_decimal( $_POST[ $commonField['id'] ] ) ) : '0';
			update_post_meta( $productID, $commonField['id'], $valueToSave );
		}
		do_action( 'ced_cwsm_save_common_fields_for_all_variations', $commonFields, $productID, '' );

		do_action( 'ced_cwsm_save_common_fields_for_all_variations_for_different_wholesale_role', $productID );

	}


	/**
	 * This function saves meta-fields common to all variations.
	 *
	 * @name ced_cwsm_consider_variations_as_one_module_admin_enqueue_scripts()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_consider_variations_as_one_module_admin_enqueue_scripts() {

		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( $_SERVER['REQUEST_URI'] ) : '';
		if ( strpos( $request_uri, '&section=ced_cwsm_consider_variations_as_one_module' ) ) {
			wp_enqueue_script( 'ced_cwsm_consider_variations_as_one_js', plugins_url( 'js/consider_variations_as_one.js', __FILE__ ), array( 'jquery' ), '1.0', true );
			$auto_check_fields = array();
			$auto_check_fields = apply_filters( 'ced_cwsm_alter_auto_check_fields', $auto_check_fields );
			$auto_check_fields = json_encode( $auto_check_fields );
			wp_localize_script(
				'ced_cwsm_consider_variations_as_one_js',
				'ced_cwsm_consider_variations_as_one_js_ajax',
				array(
					'ajax_url'          => admin_url( 'admin-ajax.php' ),
					'auto_check_fields' => $auto_check_fields,
				)
			);
		}
	}
	/**
	 * This function adds section on wholesale market tab.
	 *
	 * @name ced_cwsm_consider_variations_as_one_module_add_section()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_consider_variations_as_one_module_add_section( $sections ) {
		$sections['ced_cwsm_consider_variations_as_one_module'] = __( 'Consider Variations As One', 'wholesale-market' );
		return $sections;
	}


	/**
	 * This function adds settings on wholesale market tab.
	 *
	 * @name ced_cwsm_consider_variations_as_one_module_add_setting()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_consider_variations_as_one_module_add_setting( $settingReceived, $current_section ) {
		global $globalCWSM;
		$commonFields = $globalCWSM->getCommonFieldsForAllVariations();

		if ( 'ced_cwsm_consider_variations_as_one_module' == $current_section ) {

			$settings   = array();
			$settings[] = array(
				'name' => __( 'Consider Variations As One', 'wholesale-market' ),
				'type' => 'title',
				'desc' => __( 'Settings regarding Consider Variations As One are listed below.<br/>Utilizing this option you can set common fields for all variations of a variable product.', 'wholesale-market' ),
				'id'   => 'wc_cwsm_setting_tab_section_title-1',
			);
			$settings[] = array(
				'title'   => __( 'Enable Consider Variations As One For Minimum Quantity To Buy Condition', 'wholesale-market' ),
				'desc'    => __( 'Enable To Consider Different Variations Of A Variable Product As One, In Case Of Minimum Quantity To Buy Condition.', 'wholesale-market' ),
				'id'      => 'ced_cwsm_enable_consider_variations_as_one',
				'type'    => 'checkbox',
				'default' => 'no',
			);

			if ( ! empty( $commonFields ) ) {
				$counter = 0;
				$title   = 'Fields To Set Common For Variations Of A Variable Product';
				foreach ( $commonFields as $commonField ) {
					$checkboxgroup = '';
					if ( 0 == $counter ) {
						$checkboxgroup = 'start';
					} elseif ( count( $commonFields ) - 1 == $counter ) {
						$checkboxgroup = 'end';
					}
					$settings[] = array(
						'title'         => __( $title, 'wholesale-market' ),
						'desc'          => __( $commonField['label'], 'wholesale-market' ),
						'id'            => $commonField['id'] . '_enable',
						'default'       => 'no',
						'type'          => 'checkbox',
						'checkboxgroup' => $checkboxgroup,
					);
					$counter++;
				}
			}
			$settings[] = array(
				'type' => 'sectionend',
				'id'   => 'wc_ced_ws_setting_tab_section_end-1',
			);
			$settings   = apply_filters( 'ced_cwsm_consider_variations_as_one_module_setting', $settings );
			return $settings;
		}
		return $settingReceived;
	}

}
new CED_CWSM_Consider_Variations_As_One_Module();
?>
