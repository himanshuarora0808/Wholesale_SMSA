<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Adds minimum product quantity condition to core plugin using hooks and filters of that.
 *
 * @class    CED_CWSM_Min_Product_Qty_Module
 * @version  2.0.8
 * @package  wholesale-market/min-product-qty-module
 * @package Class
 */
class CED_CWSM_Min_Product_Qty_Module {

	public $wholesalePriceApplied = '';
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
		add_action( 'admin_enqueue_scripts', array( $this, 'ced_cwsm_min_pro_qty_module_admin_enqueue_scripts' ) );

		add_filter( 'woocommerce_get_sections_ced_cwsm_plugin', array( $this, 'ced_cwsm_min_pro_qty_module_add_section' ), 10, 1 );
		add_filter( 'woocommerce_get_settings_ced_cwsm_plugin', array( $this, 'ced_cwsm_min_pro_qty_module_add_setting' ), 10, 2 );

		add_filter( 'ced_cwsm_append_basic_sections', array( $this, 'ced_cwsm_min_pro_qty_module_add_section' ), 10, 1 );
		add_filter( 'ced_cwsm_append_basic_settings', array( $this, 'ced_cwsm_min_pro_qty_module_add_setting' ), 10, 2 );

		add_filter( 'ced_cwsm_add_options_to_delete_filter_dec', array( $this, 'ced_cwsm_min_pro_qty_module_add_options_deleted' ), 10, 1 );
		add_filter( 'ced_cwsm_add_meta_keys_to_be_deleted_dec', array( $this, 'ced_cwsm_min_pro_qty_module_add_meta_keys_to_be_deleted' ), 10, 1 );
		add_filter( 'ced_cwsm_add_options_to_delete_filter', array( $this, 'ced_cwsm_min_pro_qty_module_add_options_deleted' ), 10, 1 );
		add_filter( 'ced_cwsm_add_meta_keys_to_be_deleted', array( $this, 'ced_cwsm_min_pro_qty_module_add_meta_keys_to_be_deleted' ), 10, 1 );

		$proceed = get_option( CED_CWSM_PREFIX . 'enable_minQty' );
		if ( empty( $proceed ) || 'no' == $proceed ) {
			return;
		}

		add_action( 'ced_cwsm_add_simple_product_meta_fields', array( $this, 'ced_cwsm_min_pro_qty_module_add_simplePro_meta_field' ), 10, 3 );
		add_action( 'ced_cwsm_save_added_simple_product_meta_fields', array( $this, 'ced_cwsm_min_pro_qty_module_save_simplePro_meta_field' ), 10, 2 );
		add_action( 'ced_cwsm_add_variation_product_meta_fields', array( $this, 'ced_cwsm_min_pro_qty_module_add_variationPro_meta_fields' ), 10, 4 );
		add_action( 'ced_cwsm_save_added_variation_product_meta_fields', array( $this, 'ced_cwsm_min_pro_qty_module_save_variationPro_meta_field' ), 10, 2 );

		add_filter( 'ced_cwsm_avoid_wholesale_price_to_apply', array( $this, 'ced_cwsm_avoid_wholesale_price_to_apply' ), 10, 3 );

		add_filter( 'ced_cwsm_alter_common_fields_for_all_variations', array( $this, 'ced_cwsm_alter_common_fields_for_all_variations' ), 10, 1 );

		add_filter( 'ced_cwsm_isHaveToRender_ced_cwsm_min_qty_to_buy', array( $this, 'ced_cwsm_isHaveToRender_ced_cwsm_min_qty_to_buy' ), 10, 1 );

		add_filter( 'ced_cwsm_alter_auto_check_fields', array( $this, 'ced_cwsm_alter_auto_check_fields' ), 10, 1 );
	}

	/**
	 * This function is used to make minimum field enable or disable in product.
	 *
	 * @name ced_cwsm_alter_auto_check_fields()
	 *
	 * @param array $auto_check_fields
	 * @return array $auto_check_fields
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_alter_auto_check_fields( $auto_check_fields ) {
		$auto_check_fields[] = 'ced_cwsm_min_qty_to_buy_enable';
		return $auto_check_fields;
	}

	/**
	 * This function is used to apply custom field in product.
	 *
	 * @name ced_cwsm_alter_common_fields_for_all_variations()
	 *
	 * @param array $commonFields
	 * @return array $commonFields
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_alter_common_fields_for_all_variations( $commonFields ) {
		$attribute_description = 'Customer Will Have To Buy Minimum That Amount Of This Product, Then Wholesale-Price Will Be Applicable.';
		$commonFields[]        = array(
			'id'                => 'ced_cwsm_min_qty_to_buy',
			'label'             => __( 'Minimum Quantity To Buy', 'wholesale-market' ),
			'desc_tip'          => true,
			'description'       => __( $attribute_description, 'wholesale-market' ),
			'type'              => 'number',
			'custom_attributes' => array( 'step' => '1' ),
			'data_type'         => 'stock',
			'value'             => '',
		);
		return $commonFields;
	}

	/**
	 * This function checks whether wholesale price to apply or not.
	 *
	 * @name ced_cwsm_avoid_wholesale_price_to_apply()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_avoid_wholesale_price_to_apply( $applyWholesalePrice, $productId, $proInfo ) {
		global $globalCWSM;
		$minQtyToBuy = $globalCWSM->getMinQtyToBuy( $productId );

		if ( $minQtyToBuy ) {
			$productQtyPurchased = $proInfo['quantity'];
			if ( $productQtyPurchased >= $minQtyToBuy ) {
				$applyWholesalePrice = true;
			} else {
				$applyWholesalePrice = false;
			}
		}
		return $applyWholesalePrice;
	}

	/**
	 * This function adds meta field to enter minimum product quantity for simple product.
	 *
	 * @name cwsm_min_pro_qty_module_add_simplePro_meta_field()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_min_pro_qty_module_add_simplePro_meta_field( $post, $thepostid, $role_identifier = '' ) {
		if ( WC()->version < '3.0.0' ) {
			if ( get_product( $thepostid )->is_type( 'simple' ) ) {
				$attribute_description = 'Customer Will Have To Buy Minimum That Amount Of This Product, Then Wholesale-Price Will Be Applicable.';
				woocommerce_wp_text_input(
					array(
						'id'                => 'ced_cwsm_min_qty_to_buy' . $role_identifier,
						'label'             => __( 'Minimum Quantity To Buy', 'wholesale-market' ),
						'desc_tip'          => true,
						'description'       => __( $attribute_description, 'wholesale-market' ),
						'type'              => 'number',
						'custom_attributes' => array( 'step' => '1' ),
						'data_type'         => 'stock',
					)
				);
			}
		} else {
			if ( wc_get_product( $thepostid )->is_type( 'simple' ) ) {
				$attribute_description = 'Customer Will Have To Buy Minimum That Amount Of This Product, Then Wholesale-Price Will Be Applicable.';
				woocommerce_wp_text_input(
					array(
						'id'                => 'ced_cwsm_min_qty_to_buy' . $role_identifier,
						'label'             => __( 'Minimum Quantity To Buy', 'wholesale-market' ),
						'desc_tip'          => true,
						'description'       => __( $attribute_description, 'wholesale-market' ),
						'type'              => 'number',
						'custom_attributes' => array( 'step' => '1' ),
						'data_type'         => 'stock',
					)
				);
			}
		}
	}

	/**
	 * This function saves minimum product quantity for simple product.
	 *
	 * @name cwsm_min_pro_qty_module_save_simplePro_meta_field()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_min_pro_qty_module_save_simplePro_meta_field( $post_id, $role_identifier = '' ) {
		if ( ! isset( $_POST['woocommerce_meta_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ) ) ) {
			return;
		}
		$ced_cwsm_min_qty_to_buy = isset( $_POST[ 'ced_cwsm_min_qty_to_buy' . $role_identifier ] ) ? sanitize_text_field( $_POST[ 'ced_cwsm_min_qty_to_buy' . $role_identifier ] ) : '';
		update_post_meta( $post_id, 'ced_cwsm_min_qty_to_buy' . $role_identifier, wc_format_decimal( $ced_cwsm_min_qty_to_buy ) );
	}

	/**
	 * This function adds meta field to enter minimum product quantity for variable product.
	 *
	 * @name cwsm_min_pro_qty_module_add_variationPro_meta_fields()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_min_pro_qty_module_add_variationPro_meta_fields( $loop, $variation_data, $variation, $role_identifier = '' ) {
		global $globalCWSM;
		$variationMinQty       = get_post_meta( $variation->ID, 'ced_cwsm_min_qty_to_buy' . $role_identifier, true );
		$attribute_description = 'Customer Will Have To Buy Minimum That Amount Of This Product, Then Wholesale-Price Will Be Applicable.';
		if ( $globalCWSM->isHaveToRenderMetaField( 'ced_cwsm_min_qty_to_buy' ) ) {
			?>
			<p class="form-field ced_cwsm_min_qty_to_buy<?php echo esc_attr( $role_identifier ); ?> ">
				<label><?php echo esc_attr_e( 'Minimum Quantity To Buy', 'wholesale-market' ) . ':'; ?></label>
				<input type="text" size="5" name="ced_cwsm_min_qty_to_buy<?php echo esc_attr( $role_identifier ); ?>[<?php echo esc_attr( $variation->ID ); ?>]" value="
																					<?php
																					if ( isset( $variationMinQty ) ) {
																						echo esc_attr( $variationMinQty );}
																					?>
				" class="wc_input_decimal"  />
				<?php echo wc_help_tip( __( $attribute_description, 'wholesale-market' ) ); ?>
			</p>
			<?php
		}
	}

	/**
	 * This function store minimum product quantity for variable product.
	 *
	 * @name ced_cwsm_isHaveToRender_ced_cwsm_min_qty_to_buy()
	 *
	 * @param array $isHaveTo Render
	 * @return array $isHaveTo Render
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_isHaveToRender_ced_cwsm_min_qty_to_buy( $isHaveToRender ) {
		global $post,$globalCWSM;
		$product           = wc_get_product( $post->ID );
		$commonFields      = $globalCWSM->getCommonFieldsForAllVariations();
		$commonFieldsFound = false;
		foreach ( $commonFields as $commonField ) {
			if ( 'yes' == get_option( $commonField['id'] . '_enable', false ) ) {
				$commonFieldsFound = true;
				break;
			}
		}
	}

	/**
	 * This function saves minimum product quantity for variable product.
	 *
	 * @name cwsm_min_pro_qty_module_save_variationPro_meta_field()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_min_pro_qty_module_save_variationPro_meta_field( $variationId, $role_identifier = '' ) {
		if ( ! isset( $_POST['woocommerce_meta_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ) ) ) {
			return;
		}
		update_post_meta( $variationId, 'ced_cwsm_min_qty_to_buy' . $role_identifier, ( '' === isset( $_POST[ 'ced_cwsm_min_qty_to_buy' . $role_identifier ][ $variationId ] ) ) ? '' : sanitize_text_field( wc_format_decimal( $_POST[ 'ced_cwsm_min_qty_to_buy' . $role_identifier ][ $variationId ] ) ) );
	}


	/**
	 * This function includes custom js needed by module.
	 *
	 * @name cwsm_min_pro_qty_module_admin_enqueue_scripts()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_min_pro_qty_module_admin_enqueue_scripts() {
		$req_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( $_SERVER['REQUEST_URI'] ) : '';
		if ( strpos( $req_uri, '&section=ced_cwsm_min_pro_qty_module' ) ) {
			wp_enqueue_script( 'ced_cwsm_min_pro_qty_module_js', plugins_url( 'js/cwsm_min_pro_qty_module_js.js', __FILE__ ), array( 'jquery' ), '1.0', true );
		}
	}

	/**
	 * This function adds section on wholesale market tab.
	 *
	 * @name cwsm_min_pro_qty_module_add_section()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_min_pro_qty_module_add_section( $sections ) {
		$sections['ced_cwsm_min_pro_qty_module'] = __( 'Product Quantity', 'wholesale-market' );
		return $sections;
	}

	/**
	 * This function adds settings on wholesale market tab.
	 *
	 * @name cwsm_min_pro_qty_module_add_setting()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_min_pro_qty_module_add_setting( $settingReceived, $current_section ) {
		if ( 'ced_cwsm_min_pro_qty_module' == $current_section ) {
			$settings = apply_filters(
				'ced_cwsm_min_pro_qty_module_setting',
				array(

					'section_title-2'                  => array(
						'name' => __( 'Product Quantity Section', 'wholesale-market' ),
						'type' => 'title',
						'desc' => __( 'Settings regarding minimum product quantity to buy are listed below', 'wholesale-market' ) . '<br/>' . __( 'Buyer has to buy minimum some amount of particular product, then only wholesale price will be apply to buyers cart.', 'wholesale-market' ),
						'id'   => 'wc_cwsm_setting_tab_section_title-2',
					),
					'enable/disable-2'                 => array(
						'title'   => __( 'Enable Minimum Quantity', 'wholesale-market' ),
						'desc'    => __( 'To Activate Minimum Quantity Condition To Apply Wholesale-Price', 'wholesale-market' ),
						'id'      => 'ced_cwsm_enable_minQty',
						'default' => 'no',
						'type'    => 'checkbox',
					),
					'minQtyController'                 => array(
						'title'    => __( 'Minimum Quantity Selection Mode', 'wholesale-market' ),
						'desc'     => __( 'This controls from where to pick minimum quantity', 'wholesale-market' ),
						'id'       => 'ced_cwsm_radio_minQty_picker',
						'default'  => 'ced_cwsm_radio_individual_minQty',
						'type'     => 'radio',
						'options'  => array(
							'ced_cwsm_radio_individual_minQty' => __( 'Pick Minimum Quantity From Product Panel', 'wholesale-market' ),
							'ced_cwsm_radio_common_minQty' => __( 'Set Common Minimum Quantity For All Products', 'wholesale-market' ),
						),
						'desc_tip' => true,
						'autoload' => false,
					),
					'minQty'                           => array(
						'title'             => __( 'Common Minimum Quantity', 'wholesale-market' ),
						'desc'              => 'This is to set a common minimum quantity to all products at once',
						'id'                => 'ced_cwsm_central_min_qty',
						'css'               => 'width:80px;',
						'type'              => 'number',
						'custom_attributes' => array(
							'min'  => 0,
							'step' => 1,
						),
						'default'           => '0',
						'desc_tip'          => true,
					),
					'section_end-1'                    => array(
						'type' => 'sectionend',
						'id'   => 'wc_ced_ws_setting_tab_section_end-1',
					),
					'info-text'                        => array(
						'name' => __( 'Customize Message Regarding Wholesale-Price On Cart Page', 'wholesale-market' ),
						'type' => 'title',
						'desc' => __( 'Using below options cart-page-messages regarding wholesale-market can be customize. You can give custom message to be prompt to users regarding wholesale-price applied or not.', 'wholesale-market' ) . '<span class="cwsm_hglyt_span">' . __( 'List of shortcode that can be used to customize the message are listed below :', 'wholesale-market' ) . '<span>1. <span class="cwsm-l-span">{*wm_product_name}</span> => <span class="cwsm-r-span">' . __( 'Product Name', 'wholesale-market' ) . '</span></span> <span>2. <span class="cwsm-l-span">{*wm_min_qty}</span> => <span class="cwsm-r-span">' . __( 'Product Minimum Quantity To Buy', 'wholesale-market' ) . '</span> </span> <span>3. <span class="cwsm-l-span">{*wm_price}</span> => <span class="cwsm-r-span">' . __( 'Wholesale Price', 'wholesale-market' ) . '</span> </span> <span>4. <span class="cwsm-l-span">{*wm_cart_qty}</span> => <span class="cwsm-r-span">' . __( 'Product Quantity In Cart', 'wholesale-market' ) . ' </span></span> <span>5. <span class="cwsm-l-span">{*wm_qty_diff}</span> => <span class="cwsm-r-span">' . __( 'Difference Between (Product Minimum Quantity To Buy) And (Product Quantity In Cart)', 'wholesale-market' ) . '</span></span></span>',
						'id'   => 'ced_cwsm_custm_cart_page_msg_header',
					),
					'ced_cwsm_default_wsp_applied_txt' => array(
						'title'    => __( 'Default Wholesale-Price-Applied Text', 'wholesale-market' ),
						'desc'     => __( 'This text is used when "Enable Minimum Quantity" is disabled', 'wholesale-market' ) . '<br/>' . __( 'Here please use {*wm_product_name} and {*wm_price} only.', 'wholesale-market' ),
						'desc_tip' => true,
						// 'desc'     => __('This text is used when "Enable minimum Quantity" is disabled. <br/>Use {*wm_product_name} for product-name. Use {*wm_price} for wholesale-price.','wholesale-market'),
						'id'       => 'ced_cwsm_default_wsp_applied_txt',
						'default'  => __( 'Wholesale price is applied successfully on "{*wm_product_name}".', 'wholesale-market' ),
						'type'     => 'textarea',
						'css'      => 'width:450px; height: 70px;',
						'autoload' => false,
					),
					'ced_cwsm_wsp_applied_success_txt' => array(
						'title'    => __( 'Custom Wholesale-Price-Applied Success Message Text On Cart Page', 'wholesale-market' ),
						// 'desc'     => __('Use {*wm_product_name} for product-name. Use {*wm_min_qty} for product minimum quantity to buy. Use {*wm_price} for wholesale-price. Use {*wm_cart_qty} for product quantity in cart. Use {*wm_qty_diff} for difference between (product minimum quantity to buy) and (product quantity in cart).','wholesale-market'),
						'id'       => 'ced_cwsm_wsp_applied_success_txt',
						'default'  => __( 'Wholesale price is applied successfully on "{*wm_product_name}" as {*wm_min_qty} or more units are in cart.', 'wholesale-market' ),
						'type'     => 'textarea',
						'css'      => 'width:450px; height: 70px;',
						'autoload' => false,
					),
					'ced_cwsm_wsp_applied_failure_txt' => array(
						'title'    => __( 'Custom Wholesale-Price-Applied Failure Message Text On Cart Page', 'wholesale-market' ),
						// 'desc'     => __('Use {*wm_product_name} for product-name. Use {*wm_min_qty} for product minimum quantity to buy. Use {*wm_price} for wholesale-price. Use {*wm_cart_qty} for product quantity in cart. Use {*wm_qty_diff} for difference between (product minimum quantity to buy) and (product quantity in cart).','wholesale-market'),
						'id'       => 'ced_cwsm_wsp_applied_failure_txt',
						'default'  => __( 'Wholesale price will be applicable on "{*wm_product_name}" after buying {*wm_qty_diff} more units.', 'wholesale-market' ),
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

	/**
	 * This function adds meta keys to be deleted.
	 *
	 * @name cwsm_min_pro_qty_module_add_meta_keys_to_be_deleted()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_min_pro_qty_module_add_meta_keys_to_be_deleted( $metaFieldsToBeDeleted ) {
		array_push( $metaFieldsToBeDeleted, 'ced_cwsm_min_qty_to_buy' );
		return $metaFieldsToBeDeleted;
	}

	/**
	 * This function adds options to be deleted.
	 *
	 * @name cwsm_min_pro_qty_module_add_options_deleted()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_min_pro_qty_module_add_options_deleted( $optionsToDelArray ) {
		$optionsAdded = array(
			'ced_cwsm_enable_minQty',
			'ced_cwsm_radio_minQty_picker',
			'ced_cwsm_central_min_qty',
		);

		$optionsAdded = apply_filters( 'ced_cwsm_min_pro_qty_module_add_options_to_delete', $optionsAdded );

		foreach ( $optionsAdded as $option ) {
			array_push( $optionsToDelArray, $option );
		}
		return $optionsToDelArray;
	}
}
// Create instance of class
new CED_CWSM_Min_Product_Qty_Module();
?>
