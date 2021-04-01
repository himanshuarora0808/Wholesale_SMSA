<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Adds "Quantity Multiplier" condition to core plugin using hooks and filters of that.
 *
 * @class    CED_CWSM_Quantity_Multiplier_Module
 * @version  1.0.2
 * @package  wholesale-market-product-addon/addons/pro-qty-multiplier
 * @package Class
 */
?>
<?php
class CED_CWSM_Quantity_Multiplier_Module {

	public function __construct() {

		global $ced_cwsm_product_addon_license; // it must be dynamic for each extension
		$license_key = get_option( 'ced_cwsm_product_addon_license_key', false );
		$module_name = get_option( 'ced_cwsm_product_addon_license_module', false );
		if ( is_array( $ced_cwsm_product_addon_license ) ) {
			$http_host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( $_SERVER['HTTP_HOST'] ) : '';
			if ( $ced_cwsm_product_addon_license['license'] == $license_key && $ced_cwsm_product_addon_license['module_name'] == $module_name && $ced_cwsm_product_addon_license['domain'] == $http_host ) {
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

		/* admin panel */
		add_action( 'admin_enqueue_scripts', array( $this, 'ced_cwsm_qty_multiplier_module_admin_enqueue_scripts' ) );

		add_filter( 'ced_cwsm_append_product_addon_sections', array( $this, 'ced_cwsm_qty_multiplier_module_add_section' ), 10.1, 1 );
		add_filter( 'ced_cwsm_append_product_addon_settings', array( $this, 'ced_cwsm_qty_multiplier_module_add_setting' ), 10, 2 );

		if ( ! $this->is_pro_qty_multiplier_condition_enabled() ) {
			return;
		}

		/* frontEnd */
		add_action( 'wp_enqueue_scripts', array( $this, 'ced_cwsm_qty_multiplier_module_frontEnd_enqueue_scripts' ) );

		add_filter( 'woocommerce_quantity_input_args', array( $this, 'woocommerce_quantity_input_args' ), 10, 2 );
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'woocommerce_add_to_cart_validation' ), 10, 5 );

		add_filter( 'woocommerce_loop_add_to_cart_args', array( $this, 'woocommerce_loop_add_to_cart_args' ), 10, 2 );
		add_filter( 'woocommerce_update_cart_validation', array( $this, 'woocommerce_update_cart_validation' ), 10, 4 );

		/* admin panel */
		add_action( 'ced_cwsm_add_simple_product_meta_fields', array( $this, 'ced_cwsm_qty_multiplier_module_add_simplePro_meta_field' ), 10, 3 );
		add_action( 'ced_cwsm_save_added_simple_product_meta_fields', array( $this, 'ced_cwsm_qty_multiplier_module_save_simplePro_meta_field' ), 10, 2 );
		add_action( 'ced_cwsm_add_variation_product_meta_fields', array( $this, 'ced_cwsm_qty_multiplier_module_add_variationPro_meta_fields' ), 10, 4 );
		add_action( 'ced_cwsm_save_added_variation_product_meta_fields', array( $this, 'ced_cwsm_qty_multiplier_module_save_variationPro_meta_field' ), 10, 2 );

		// doubt
		// add_filter('ced_cwsm_check_to_apply_wholesale_price', array($this,'ced_cwsm_qty_multiplier_module_check_to_apply_wholesale_price'),10,3);

		add_filter( 'ced_cwsm_alter_common_fields_for_all_variations', array( $this, 'ced_cwsm_alter_common_fields_for_all_variations' ), 11, 1 );

	}





	public function ced_cwsm_alter_common_fields_for_all_variations( $commonFields ) {
		$attribute_description = 'Set A Multiplier So That Product Can Be Buyed In Bunch Of That Multiplier Only. For Example, If Multiplier=3, Then Product Can Be Buyed In Quanity 3,6,9,12,And So On.';
		$commonFields[]        = array(
			'id'                => 'ced_cwsm_qty_to_buy_multiplier',
			'label'             => __( 'Quantity Multiplier', 'wholesale-market' ),
			'desc_tip'          => true,
			'description'       => __( $attribute_description, 'wholesale-market' ),
			'type'              => 'number',
			'custom_attributes' => array( 'min' => '0' ),
			'data_type'         => 'stock',
			'value'             => '',
		);

		return $commonFields;
	}

	/**
	 * This function adds meta field to enter product quantity multiplier for simple product.
	 *
	 * @name ced_cwsm_qty_multiplier_module_add_simplePro_meta_field()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_qty_multiplier_module_add_simplePro_meta_field( $post, $thepostid, $role_identifier = '' ) {
		if ( wc_get_product( $thepostid )->is_type( 'simple' ) ) {
			$attribute_description = 'Set A Multiplier So That Product Can Be Buyed In Bunch Of That Multiplier Only. For Example, If Multiplier=3, Then Product Can Be Buyed In Quanity 3,6,9,12,And So On.';
			woocommerce_wp_text_input(
				array(
					'id'                => 'ced_cwsm_qty_to_buy_multiplier' . $role_identifier,
					'label'             => __( 'Quantity Multiplier', 'wholesale-market' ),
					'desc_tip'          => true,
					'description'       => __( $attribute_description, 'wholesale-market' ),
					'type'              => 'number',
					'custom_attributes' => array( 'min' => '0' ),
					'data_type'         => 'stock',
				)
			);
		}
	}

	/**
	 * This function saves product quantity multiplier for simple product.
	 *
	 * @name ced_cwsm_qty_multiplier_module_save_simplePro_meta_field()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_qty_multiplier_module_save_simplePro_meta_field( $post_id, $role_identifier = '' ) {
		if ( ! isset( $_POST['woocommerce_meta_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ) ) ) {
			return;
		}
		$ced_cwsm_qty_to_buy_multiplier = isset( $_POST[ 'ced_cwsm_qty_to_buy_multiplier' . $role_identifier ] ) ? sanitize_text_field( $_POST[ 'ced_cwsm_qty_to_buy_multiplier' . $role_identifier ] ) : '';
		update_post_meta( $post_id, 'ced_cwsm_qty_to_buy_multiplier' . $role_identifier, wc_format_decimal( $ced_cwsm_qty_to_buy_multiplier ) );

		// if(isset($_POST['ced_cwsm_qty_to_buy_multiplier'])) {
		// if($_POST['ced_cwsm_qty_to_buy_multiplier'] === '') {
		// $ced_cwsm_qty_to_buy_multiplier = "";
		// }
		// else {
		// $ced_cwsm_qty_to_buy_multiplier = sanitize_text_field($_POST['ced_cwsm_qty_to_buy_multiplier']);
		// }
		// update_post_meta( $thepostid, 'ced_cwsm_qty_to_buy_multiplier', $ced_cwsm_qty_to_buy_multiplier );
		// }
	}


	/**
	 * This function adds meta field to enter product quantity multiplier for variable product.
	 *
	 * @name ced_cwsm_qty_multiplier_module_add_variationPro_meta_fields()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_qty_multiplier_module_add_variationPro_meta_fields( $loop, $variation_data, $variation, $role_identifier = '' ) {
		global $globalCWSM;
		$varitionMinQty        = get_post_meta( $variation->ID, 'ced_cwsm_qty_to_buy_multiplier' . $role_identifier, true );
		$attribute_description = 'Set A Multiplier So That Product Can Be Buyed In Bunch Of That Multiplier Only. For Example, If Multiplier=3, Then Product Can Be Buyed In Quanity 3,6,9,12,And So On.';
		if ( $globalCWSM->isHaveToRenderMetaField( 'ced_cwsm_qty_to_buy_multiplier' ) ) {
			?>
			<p class="form-field ced_cwsm_qty_to_buy_multiplier_field<?php echo esc_attr( $role_identifier ); ?> ">
				<label><?php echo esc_attr_e( 'Quantity Multiplier', 'wholesale-market' ) . ':'; ?></label>
				<input type="text" name="ced_cwsm_qty_to_buy_multiplier<?php echo esc_attr( $role_identifier ); ?>[<?php echo esc_attr( $variation->ID ); ?>]" value="
				<?php
				if ( isset( $varitionMinQty ) ) {
					echo esc_attr( $varitionMinQty );}
				?>
					" class="wc_input_decimal"  />
					<?php echo wc_help_tip( __( $attribute_description, 'woocommerce' ) ); ?>
				</p>
				<?php
		}
	}

	/**
	 * This function saves product quantity multiplier for variable product.
	 *
	 * @name ced_cwsm_qty_multiplier_module_save_variationPro_meta_field()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_qty_multiplier_module_save_variationPro_meta_field( $variationId, $role_identifier = '' ) {
		if ( ! isset( $_POST['woocommerce_meta_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ) ) ) {
			return;
		}
		$qty_multiplier = ( '' === ( isset( $_POST[ 'ced_cwsm_qty_to_buy_multiplier' . $role_identifier ][ $variationId ] ) ? sanitize_text_field( $_POST[ 'ced_cwsm_qty_to_buy_multiplier' . $role_identifier ][ $variationId ] ) : '' ) ) ? '' : sanitize_text_field( wc_format_decimal( $_POST[ 'ced_cwsm_qty_to_buy_multiplier' . $role_identifier ][ $variationId ] ) );
		update_post_meta( $variationId, 'ced_cwsm_qty_to_buy_multiplier' . $role_identifier, $qty_multiplier );
	}



	/**
	 * This function enqueue script on product single page(in case of variable product) and works for handling multiplier stuff for variations.
	 *
	 * @name ced_cwsm_qty_multiplier_module_frontEnd_enqueue_scripts()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_qty_multiplier_module_frontEnd_enqueue_scripts() {
		if ( ! $this->is_pro_qty_multiplier_condition_enabled() ) {
			return;
		}
		global $product;
		if ( is_product() ) {
			global $post;
			// print_r($post->id);die;
			$variationInfo = array();

			$product = wc_get_product( get_the_ID() );
			if ( $product->get_type() == 'simple' ) {
				return;
			}
			$variations = $product->get_available_variations();
			foreach ( $variations as $variation ) {
				$variation_id                   = $variation['variation_id'];
				$ced_cwsm_qty_to_buy_multiplier = $this->fetch_pro_qty_multiplier( $variation_id );
				$ced_cwsm_qty_to_buy_multiplier = (int) $ced_cwsm_qty_to_buy_multiplier;
				if ( $ced_cwsm_qty_to_buy_multiplier < 1 ) {
					$ced_cwsm_qty_to_buy_multiplier = 1;
				}
				$variationInfo[ $variation_id ] = array(
					'ced_cwsm_qty_to_buy_multiplier' => $ced_cwsm_qty_to_buy_multiplier,
				);

			}

			wp_enqueue_script( 'ced_cwsm_qty_to_buy_multiplier_variation', plugins_url( 'js/cwsm_qty_to_buy_multiplier_variation.js', __FILE__ ), array( 'jquery' ), '1.0', true );
			wp_localize_script(
				'ced_cwsm_qty_to_buy_multiplier_variation',
				'ced_cwsm_qty_to_buy_multiplier_variation_AJAX',
				array(
					'ajax_url'      => admin_url( 'admin-ajax.php' ),
					'variationInfo' => json_encode( $variationInfo ),
				)
			);
		}
	}

	/**
	 * This function set product qty in multiplier on shop page.
	 *
	 * @name woocommerce_loop_add_to_cart_args()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function woocommerce_loop_add_to_cart_args( $args, $product ) {

		$ced_cwsm_qty_to_buy_multiplier = $this->fetch_pro_qty_multiplier( $product->get_id() );
		if ( '0' != $ced_cwsm_qty_to_buy_multiplier && '' != $ced_cwsm_qty_to_buy_multiplier ) {
			$args['quantity'] = $ced_cwsm_qty_to_buy_multiplier;
		}
		return $args;
	}

	/**
	 * This function manages step as multiplier on cart page and product single page.
	 *
	 * @name woocommerce_quantity_input_args()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function woocommerce_quantity_input_args( $args, $product ) {

		if ( $product->get_type() == 'simple' ) {

			if ( is_product() || is_cart() ) {

				$product_id = $product->get_id();
				if ( isset( $product_id ) ) {
					$product_id = $product->get_id();
				}

				$ced_cwsm_qty_to_buy_multiplier = $this->fetch_pro_qty_multiplier( $product_id );
				if ( isset( $ced_cwsm_qty_to_buy_multiplier ) && ! empty( $ced_cwsm_qty_to_buy_multiplier ) ) {
					$ced_cwsm_qty_to_buy_multiplier = (int) $ced_cwsm_qty_to_buy_multiplier;
					if ( $ced_cwsm_qty_to_buy_multiplier < 1 ) {
						return $args;
					}
					if ( is_product() ) {
						$args['input_value'] = $ced_cwsm_qty_to_buy_multiplier;
					}
					$args['min_value'] = $ced_cwsm_qty_to_buy_multiplier;
					$args['step']      = $ced_cwsm_qty_to_buy_multiplier;
				}
				// return $args;

			}
		} elseif ( $product->get_type() == 'variable' ) {

			if ( ! $this->is_pro_qty_multiplier_condition_enabled() ) {
				return;
			}

			$attributes = $product->get_default_attributes();

			foreach ( $attributes as $key => $value ) {
				if ( strpos( $key, 'attribute_' ) === 0 ) {
					continue;
				}

				unset( $attributes[ $key ] );
				$attributes[ sprintf( 'attribute_%s', $key ) ] = $value;
			}

			if ( class_exists( 'WC_Data_Store' ) ) {

				$data_store   = WC_Data_Store::load( 'product' );
				$variation_id = $data_store->find_matching_product_variation( $product, $attributes );

			} else {

				$variation_id = $product->get_matching_variation( $attributes );

			}

			// if(isset($variation_id) && !empty($variation_id)){
			// $args['min_value'] = get_post_meta($variation_id, 'ced_cwsm_qty_to_buy_multiplier', true);
			// $args['step'] = get_post_meta($variation_id, 'ced_cwsm_qty_to_buy_multiplier', true);
			// return $args;
			// }

			$ced_cwsm_qty_to_buy_multiplier = get_post_meta( $variation_id, 'ced_cwsm_qty_to_buy_multiplier', true );
			if ( isset( $ced_cwsm_qty_to_buy_multiplier ) && ! empty( $ced_cwsm_qty_to_buy_multiplier ) ) {
				$ced_cwsm_qty_to_buy_multiplier = (int) $ced_cwsm_qty_to_buy_multiplier;
				if ( $ced_cwsm_qty_to_buy_multiplier < 1 ) {
					return $args;
				}
				$args['input_value'] = $ced_cwsm_qty_to_buy_multiplier;
				$args['min_value']   = $ced_cwsm_qty_to_buy_multiplier;
				$args['step']        = $ced_cwsm_qty_to_buy_multiplier;
			}
		} elseif ( $product->get_type() == 'variation' ) {

			$variation_id = $product->get_id();

			if ( isset( $variation_id ) && ! empty( $variation_id ) ) {
				// $ced_cwsm_qty_to_buy_multiplier=get_post_meta($variation_id, 'ced_cwsm_qty_to_buy_multiplier', true);

				$ced_cwsm_qty_to_buy_multiplier = $this->fetch_pro_qty_multiplier( $variation_id );

				if ( isset( $ced_cwsm_qty_to_buy_multiplier ) && ! empty( $ced_cwsm_qty_to_buy_multiplier ) ) {

					$ced_cwsm_qty_to_buy_multiplier = (int) $ced_cwsm_qty_to_buy_multiplier;
					if ( $ced_cwsm_qty_to_buy_multiplier < 1 ) {
						return $args;
					}

					$args['step'] = $ced_cwsm_qty_to_buy_multiplier;
				}
			}
		}

		return $args;
	}

	/**
	 * This function validates quantity against multiplier during add to cart.
	 *
	 * @name woocommerce_add_to_cart_validation()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function woocommerce_add_to_cart_validation( $validationValue, $product_id, $quantity, $variation_id = null, $variations = null ) {

		$product_id = $product_id;
		if ( ! is_null( $variation_id ) ) {
			$product_id = $variation_id;
		}

		$min_qty_condition_enabled = get_option( 'ced_cwsm_enable_minQty' );
		if ( 'yes' == $min_qty_condition_enabled ) {
			$min_qty_to_buy = get_post_meta( $product_id, 'ced_cwsm_min_qty_to_buy' );
			if ( isset( $min_qty_to_buy ) && ! empty( $min_qty_to_buy ) ) {
				if ( $quantity < $min_qty_to_buy[0] ) {
					$errorMsgForQtyMismatch = $this->fetchErrorMsg( $product_id, $quantity, $min_qty_to_buy[0] );
					wc_add_notice( __( $errorMsgForQtyMismatch, 'wholesale-market' ), 'error' );
					return false;
				}
			}
		} else {
			die;
			$ced_cwsm_qty_to_buy_multiplier = $this->fetch_pro_qty_multiplier( $product_id );
			if ( isset( $ced_cwsm_qty_to_buy_multiplier ) && ! empty( $ced_cwsm_qty_to_buy_multiplier ) && '0' != $ced_cwsm_qty_to_buy_multiplier ) {
				$ced_cwsm_qty_to_buy_multiplier = $this->fetch_pro_qty_multiplier( $product_id );
				$ced_cwsm_qty_to_buy_multiplier = (int) $ced_cwsm_qty_to_buy_multiplier;
				$quantity                       = (int) $quantity;
				if ( 0 != $quantity % $ced_cwsm_qty_to_buy_multiplier ) {
					$errorMsgForQtyMismatch = $this->fetchErrorMsg( $product_id, $quantity, $ced_cwsm_qty_to_buy_multiplier );
					wc_add_notice( __( $errorMsgForQtyMismatch, 'wholesale-market' ), 'error' );
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * This function validates quantity against multiplier during cart update.
	 *
	 * @name woocommerce_update_cart_validation()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function woocommerce_update_cart_validation( $validationValue, $cart_item_key, $values, $quantity ) {
		$product_id = $values['product_id'];
		if ( isset( $values['variation_id'] ) && ! empty( $values['variation_id'] ) ) {
			$product_id = $values['variation_id'];
		}

		$ced_cwsm_qty_to_buy_multiplier = $this->fetch_pro_qty_multiplier( $product_id );
		if ( isset( $ced_cwsm_qty_to_buy_multiplier ) && ! empty( $ced_cwsm_qty_to_buy_multiplier ) && '0' != $ced_cwsm_qty_to_buy_multiplier ) {
			$ced_cwsm_qty_to_buy_multiplier = (int) $ced_cwsm_qty_to_buy_multiplier;
			$quantity                       = (int) $quantity;
			if ( 0 != $quantity % $ced_cwsm_qty_to_buy_multiplier ) {
				$errorMsgForQtyMismatch = $this->fetchErrorMsg( $product_id, $quantity, $ced_cwsm_qty_to_buy_multiplier );
				wc_add_notice( __( $errorMsgForQtyMismatch, 'wholesale-market' ), 'error' );
				return false;
			}
		}
		return true;
	}

	/**
	 * This function fetches product quantity multiplier.
	 *
	 * @name fetch_pro_qty_multiplier()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function fetch_pro_qty_multiplier( $product_id ) {
		$ced_cwsm_qty_to_buy_multiplier = '0';

		global $globalCWSM;
		// global $globalCWSM;
		$wholesalePrice = $globalCWSM->getWholesalePrice( $product_id );
		if ( '0' == $wholesalePrice || '' == $wholesalePrice ) {
			return $ced_cwsm_qty_to_buy_multiplier;
		}
		if ( $globalCWSM->isCurrentUserIsWholesaleUser() ) {

			if ( $this->is_pro_qty_multiplier_condition_enabled() ) {

				$ced_cwsm_qty_multiplier_pick_from = get_option( 'ced_cwsm_radio_qty_multiplier_picker', false );
				if ( 'ced_cwsm_qty_multiplier_pick_from_common_field' == $ced_cwsm_qty_multiplier_pick_from ) {
					$ced_cwsm_qty_to_buy_multiplier = get_option( 'ced_cwsm_qty_to_buy_multiplier_common', false );
				} else {
					$ced_cwsm_qty_to_buy_multiplier = get_post_meta( $product_id, 'ced_cwsm_qty_to_buy_multiplier', true );
					$ced_cwsm_qty_to_buy_multiplier = apply_filters( 'ced_cwsm_alter_qty_to_buy_multiplier', $ced_cwsm_qty_to_buy_multiplier, $product_id );
				}
			}
		}
		$ced_cwsm_qty_to_buy_multiplier = apply_filters( 'ced_cwsm_alter_qty_to_buy_multiplier_final', $ced_cwsm_qty_to_buy_multiplier, $product_id );
		return $ced_cwsm_qty_to_buy_multiplier;
	}

	/**
	 * This function check whether product qty multiplier condition is enabled or not.
	 *
	 * @name is_pro_qty_multiplier_condition_enabled()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function is_pro_qty_multiplier_condition_enabled() {
		$ced_cwsm_enable_qty_multiplier = get_option( 'ced_cwsm_enable_qty_multiplier', false );

		if ( isset( $ced_cwsm_enable_qty_multiplier ) && ! empty( $ced_cwsm_enable_qty_multiplier ) && 'yes' == $ced_cwsm_enable_qty_multiplier ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * This function fetch error message to show in case of violating multiplier condition.
	 *
	 * @name fetchErrorMsg()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function fetchErrorMsg( $product_id, $quantity, $minquant = '' ) {
		$errorMsg = 'Minimum Quantity to Buy Should be More than or Equal to ' . $minquant;
		$errorMsg = apply_filters( 'ced_cwsm_alter_error_message_for_pro_qty_multiplier', $errorMsg, $product_id, $quantity );
		return $errorMsg;
	}


	/**
	 * This function includes custom js needed by module.
	 *
	 * @name ced_cwsm_qty_multiplier_module_admin_enqueue_scripts()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_qty_multiplier_module_admin_enqueue_scripts() {
		$req_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( $_SERVER['REQUEST_URI'] ) : '';
		if ( strpos( $req_uri, '&section=ced_cwsm_qty_multiplier_module' ) ) {
			wp_enqueue_script( 'ced_cwsm_pro_qty_multiplier_module_js', plugins_url( 'js/cwsm_pro_qty_multiplier_module_js.js', __FILE__ ), array( 'jquery' ), '1.0', true );
		}
	}


	/**
	 * This function adds section on wholesale market tab.
	 *
	 * @name ced_cwsm_qty_multiplier_module_add_section()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_qty_multiplier_module_add_section( $sections ) {
		$sections['ced_cwsm_qty_multiplier_module'] = __( 'Quantity Multiplier', 'wholesale-market' );
		return $sections;
	}

	/**
	 * This function adds settings on wholesale market tab.
	 *
	 * @name ced_cwsm_qty_multiplier_module_add_setting()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_qty_multiplier_module_add_setting( $settingReceived, $current_section ) {
		if ( 'ced_cwsm_qty_multiplier_module' == $current_section ) {
			$settings = apply_filters(
				'ced_cwsm_min_checkout_price_module_setting',
				array(
					'section_title-1'                   => array(
						'name' => __( 'Product Quantity Multiplier', 'wholesale-market' ),
						'type' => 'title',
						'desc' => __( 'Settings regarding product quantity multiplier are listed below.<br/>Utilizing this option you can set a multiplier so that product can be buyed in bunch of that multiplier only. For example, if multiplier=3, then product can be buyed in quanity 3,6,9,12,and so on.', 'wholesale-market' ),
						'id'   => 'wc_cwsm_setting_tab_section_title-1',
					),
					'ced_cwsm_enable_qty_multiplier'    => array(
						'title'   => __( 'Enable Quantity Multiplier', 'wholesale-market' ),
						'desc'    => __( 'Enable To Activate Product Quantity Multiplier', 'wholesale-market' ),
						'id'      => 'ced_cwsm_enable_qty_multiplier',
						'type'    => 'checkbox',
						'default' => 'no',
					),
					'ced_cwsm_qty_multiplier_pick_from' => array(
						'title'    => __( 'Quantity Multiplier Selection Mode', 'wholesale-market' ),
						'desc'     => __( 'This Controls From Where To Pick Quantity Multiplier', 'wholesale-market' ),
						'id'       => 'ced_cwsm_radio_qty_multiplier_picker',
						'default'  => 'ced_cwsm_qty_multiplier_pick_from_product_panel',
						'type'     => 'radio',
						'options'  => array(
							'ced_cwsm_qty_multiplier_pick_from_product_panel' => __( 'Pick Quantity Multiplier From Product Panel', 'wholesale-market' ),
							'ced_cwsm_qty_multiplier_pick_from_common_field' => __( 'Set Common Quantity Multiplier For All Products', 'wholesale-market' ),
						),
						'desc_tip' => true,
						'autoload' => false,
					),
					'ced_cwsm_qty_to_buy_multiplier_common' => array(
						'title'             => __( 'Common Multiplier', 'wholesale-market' ),
						'desc'              => __( 'This Is To Set A Common Quantity Multiplier For All Products At Once', 'wholesale-market' ),
						'id'                => 'ced_cwsm_qty_to_buy_multiplier_common',
						'css'               => 'width:80px;',
						'type'              => 'number',
						'custom_attributes' => array(
							'min'  => 0,
							'step' => 1,
						),
						'default'           => '0',
						'desc_tip'          => true,
					),
					'section_end-1'                     => array(
						'type' => 'sectionend',
						'id'   => 'wc_ced_ws_setting_tab_section_end-1',
					),
				)
			);
			return $settings;
		}
		return $settingReceived;
	}

}
new CED_CWSM_Quantity_Multiplier_Module();
?>
