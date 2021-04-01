<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Adds "Range Wise Pricing For Wholesale Users" condition to core plugin using hooks and filters of that.
 *
 * @class    CED_CWSM_Range_Wise_Pricing_Module
 * @version  1.0.2
 * @package  wholesale-market-product-addon/addons/range-wise-pricing
 * @package Class
 */
?>
<?php
class CED_CWSM_Range_Wise_Pricing_Module {

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

		add_filter( 'ced_cwsm_append_product_addon_sections', array( $this, 'ced_cwsm_append_product_addon_sections' ), 10.1, 1 );
		add_filter( 'ced_cwsm_append_product_addon_settings', array( $this, 'ced_cwsm_append_product_addon_settings' ), 10, 2 );

		add_action( 'admin_enqueue_scripts', array( $this, 'ws_setting_page_enqueue_scripts' ) );

		$ced_cwsm_enable_range_wise_pricing = get_option( 'ced_cwsm_enable_range_wise_pricing', false );
		if ( empty( $ced_cwsm_enable_range_wise_pricing ) || 'no' == $ced_cwsm_enable_range_wise_pricing ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'product_edit_page_enqueue_scripts' ) );

		add_action( 'ced_cwsm_add_simple_product_meta_fields', array( $this, 'ced_cwsm_add_simple_product_meta_fields' ), 50, 3 );
		add_action( 'ced_cwsm_save_added_simple_product_meta_fields', array( $this, 'ced_cwsm_save_added_simple_product_meta_fields' ), 50, 2 );

		add_action( 'ced_cwsm_add_variation_product_meta_fields', array( $this, 'ced_cwsm_add_variation_product_meta_fields' ), 10, 4 );
		add_action( 'ced_cwsm_save_added_variation_product_meta_fields', array( $this, 'ced_cwsm_save_added_variation_product_meta_fields' ), 10, 2 );

		/* consider variation as one condition */
		add_filter( 'ced_cwsm_alter_common_fields_for_all_variations', array( $this, 'ced_cwsm_alter_common_fields_for_all_variations' ), 11, 1 );
		add_action( 'ced_cwsm_render_common_fields_for_all_variations', array( $this, 'ced_cwsm_render_common_fields_for_all_variations' ), 15, 2 );
		add_action( 'ced_cwsm_save_common_fields_for_all_variations', array( $this, 'ced_cwsm_save_common_fields_for_all_variations' ), 50, 3 );

		add_filter( 'ced_cwsm_alter_wholesale_price', array( $this, 'ced_cwsm_alter_wholesale_price' ), 10, 2 );

	}

	/**
	 * This function is used for enqueue_scripts .
	 *
	 * @name ws_setting_page_enqueue_scripts()
	 *
	 * @link  http://www.cedcommerce.com/
	 */

	public function ws_setting_page_enqueue_scripts() {
		$req_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( $_SERVER['REQUEST_URI'] ) : '';
		if ( strpos( $req_uri, 'page=wholesale_market&tab=ced_cwsm_product_addon&section=ced_cwsm_range_wise_pricing_module' ) !== false ) {
			wp_enqueue_script( 'ced_cwsm_range_wise_pricing_setting_page_js', plugins_url( 'js/range_wise_pricing_setting_page_js.js', __FILE__ ), array( 'jquery' ), '1.0', true );
			wp_localize_script(
				'ced_cwsm_range_wise_pricing_setting_page_js',
				'ced_cwsm_range_wise_pricing_setting_page_js_AJAX',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
				)
			);
		}
	}

	/**
	 * This function is used displaying html for variable products .
	 *
	 * @name ced_cwsm_alter_common_fields_for_all_variations()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_alter_common_fields_for_all_variations( $commonFields ) {
		$attribute_description = 'Enable Range-Wise-Pricing.';
		$commonFields[]        = array(
			'id'          => 'ced_cwsm_enable_range_wise_pricing',
			'label'       => __( 'Range-Wise-Pricing', 'wholesale-market' ),
			'desc_tip'    => true,
			'description' => __( $attribute_description, 'wholesale-market' ),
			'type'        => 'table',
			'value'       => '',
		);
		return $commonFields;
	}

	/**
	 * This function is used for saving common fiels for variations .
	 *
	 * @name ced_cwsm_save_common_fields_for_all_variations()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_save_common_fields_for_all_variations( $commonFields, $productID, $role_identifier = '' ) {

		if ( ! isset( $_POST['woocommerce_meta_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ) ) ) {
			return;
		}
		global $globalCWSM;
		if ( ! $globalCWSM->isHaveToRenderMetaField( 'ced_cwsm_enable_range_wise_pricing' ) ) {
			$valueToSave = ( isset( $_POST[ 'ced_cwsm_enable_range_wise_pricing' . $role_identifier ] ) ) ? 'yes' : '';
			update_post_meta( $productID, 'ced_cwsm_enable_range_wise_pricing' . $role_identifier, $valueToSave );
			if ( isset( $_POST[ 'ced_cwsm_range_min_qty' . $role_identifier ] ) ) {
				$arrayToUse            = array();
				$ced_cwsm_rnge_min_qty = isset( $_POST[ 'ced_cwsm_range_min_qty' . $role_identifier ] ) ? sanitize_text_field( $_POST[ 'ced_cwsm_range_min_qty' . $role_identifier ] ) : '';
				foreach ( $ced_cwsm_rnge_min_qty as $key => $value ) {
					$arrayToUse[] = array(
						'minQty' => isset( $_POST[ 'ced_cwsm_range_min_qty' . $role_identifier ][ $key ] ) ? sanitize_text_field( $_POST[ 'ced_cwsm_range_min_qty' . $role_identifier ][ $key ] ) : '',
						'maxQty' => isset( $_POST[ 'ced_cwsm_range_max_qty' . $role_identifier ][ $key ] ) ? sanitize_text_field( $_POST[ 'ced_cwsm_range_max_qty' . $role_identifier ][ $key ] ) : '',
						'value'  => isset( $_POST[ 'ced_cwsm_range_valueToUse' . $role_identifier ][ $key ] ) ? sanitize_text_field( $_POST[ 'ced_cwsm_range_valueToUse' . $role_identifier ][ $key ] ) : '',
					);
				}

				update_post_meta( $productID, 'ced_cwsm_range_wise_pricing_table' . $role_identifier, $arrayToUse );
			}
		}
	}

	/**
	 * This function is used to render range wise pricing fields .
	 *
	 * @name ced_cwsm_render_common_fields_for_all_variations()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_render_common_fields_for_all_variations( $commonFields, $role_identifier = '' ) {
		global $post;
		$pricing_type = 'Price/Discount';
		$minQtyName   = 'ced_cwsm_range_min_qty' . $role_identifier;
		$maxQtyName   = 'ced_cwsm_range_max_qty' . $role_identifier;
		$valueToUse   = 'ced_cwsm_range_valueToUse' . $role_identifier;

		$attribute_description = 'Enable Range-Wise-Pricing.';

		$ced_cwsm_enable_range_wise_pricing = get_post_meta( $post->ID, 'ced_cwsm_enable_range_wise_pricing' . $role_identifier, true );
		if ( 'yes' == $ced_cwsm_enable_range_wise_pricing ) {
			$ced_cwsm_enable_range_wise_pricing = 'checked="checked"';
		}
		?>
		<div class="ced_cwsm_range_wise_pricing_wrapper_div">
			<h4 class="ced_cwsm_range_wise_pricing_heading"><span>+</span><?php esc_attr_e( 'Range Wise Pricing', 'woocommerce-market' ); ?></h4>
			<div class="ced_cwsm_range_wise_pricing_content_div" style="display:none;">
				<div class="ced_cwsm_container_section">
					<p class="">
						<label for="ced_cwsm_enable_range_wise_pricing"><?php esc_attr_e( 'Enable Range-Wise-Pricing', 'woocommerce-market' ); ?></label>
						<input class="checkbox" name="ced_cwsm_enable_range_wise_pricing<?php echo esc_attr( $role_identifier ); ?>" <?php echo esc_attr( $ced_cwsm_enable_range_wise_pricing ); ?> type="checkbox"> 
						<?php echo wc_help_tip( __( $attribute_description, 'woocommerce' ) ); ?>
					</p>
					<span class="button button-primary ced_cwsm_add_range_row" ><?php esc_attr_e( 'Add Row', 'woocommerce-market' ); ?></span>
				</div>	
				<table class="wp-list-table widefat fixed striped" >
					<tr>
						<th><?php esc_attr_e( 'Minimum Quantity', 'woocommerce-market' ); ?></th>
						<th><?php esc_attr_e( 'Maximum Quantity', 'woocommerce-market' ); ?></th>
						<th><?php esc_attr_e( $pricing_type, 'woocommerce-market' ); ?></th>
						<th><?php esc_attr_e( 'Action', 'woocommerce-market' ); ?></th>
					</tr>
					<?php
					$ced_cwsm_range_wise_pricing_table = get_post_meta( $post->ID, 'ced_cwsm_range_wise_pricing_table' . $role_identifier, true );
					// print_r($ced_cwsm_range_wise_pricing_table);

					if ( is_array( $ced_cwsm_range_wise_pricing_table ) && ! empty( $ced_cwsm_range_wise_pricing_table ) ) {
						foreach ( $ced_cwsm_range_wise_pricing_table as $key => $value ) {
							?>
							<tr>
								<td><input type="number" name="<?php esc_attr_e( $minQtyName ); ?>[]" value="<?php echo esc_attr( $value['minQty'] ); ?>"></td>
								<td><input type="number" name="<?php esc_attr_e( $maxQtyName ); ?>[]" value="<?php echo esc_attr( $value['maxQty'] ); ?>"></td>
								<td><input type="text" class="wc_input_price" name="<?php esc_attr_e( $valueToUse ); ?>[]" value="<?php echo esc_attr( $value['value'] ); ?>"></td>
								<td>
									<?php
									if ( 0 != $key ) {
										?>
										<span class="button button-primary ced_cwsm_delete_range_row">Delete</span>
										<?php
									}
									?>
								</td>
							</tr>
							<?php
						}
					} else {
						?>
						<tr>
							<td><input type="number" name="<?php esc_attr_e( $minQtyName ); ?>[]"></td>
							<td><input type="number" name="<?php esc_attr_e( $maxQtyName ); ?>[]"></td>
							<td><input type="text" class="wc_input_price" name="<?php esc_attr_e( $valueToUse ); ?>[]"></td>
							<td>
							</td>
						</tr>
						<?php
					}
					?>
				</table>
			</div>
		</div>
		<?php
	}



	/*** For Variable Product ***/
	public function ced_cwsm_save_added_variation_product_meta_fields( $variationId, $role_identifier = '' ) {
		if ( ! isset( $_POST['woocommerce_meta_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ) ) ) {
			return;
		}
		global $globalCWSM;
		if ( $globalCWSM->isHaveToRenderMetaField( 'ced_cwsm_enable_range_wise_pricing' ) ) {
			$valueToSave = ( isset( $_POST[ 'ced_cwsm_enable_range_wise_pricing' . $role_identifier ][ $variationId ] ) ) ? 'yes' : '';
			update_post_meta( $variationId, 'ced_cwsm_enable_range_wise_pricing' . $role_identifier, $valueToSave );
			if ( isset( $_POST[ 'ced_cwsm_range_min_qty' . $role_identifier ][ $variationId ] ) ) {
				$arrayToUse           = array();
				$ced_wsm_rage_min_qty = isset( $_POST[ 'ced_cwsm_range_min_qty' . $role_identifier ][ $variationId ] ) ? sanitize_text_field( $_POST[ 'ced_cwsm_range_min_qty' . $role_identifier ][ $variationId ] ) : '';
				foreach ( $ced_wsm_rage_min_qty as $key => $value ) {
					$arrayToUse[] = array(
						'minQty' => isset( $_POST[ 'ced_cwsm_range_min_qty' . $role_identifier ][ $variationId ][ $key ] ) ? sanitize_text_field( $_POST[ 'ced_cwsm_range_min_qty' . $role_identifier ][ $variationId ][ $key ] ) : '',
						'maxQty' => isset( $_POST[ 'ced_cwsm_range_max_qty' . $role_identifier ][ $variationId ][ $key ] ) ? sanitize_text_field( $_POST[ 'ced_cwsm_range_max_qty' . $role_identifier ][ $variationId ][ $key ] ) : '',
						'value'  => isset( $_POST[ 'ced_cwsm_range_valueToUse' . $role_identifier ][ $variationId ][ $key ] ) ? sanitize_text_field( $_POST[ 'ced_cwsm_range_valueToUse' . $role_identifier ][ $variationId ][ $key ] ) : '',
					);
				}
				update_post_meta( $variationId, 'ced_cwsm_range_wise_pricing_table' . $role_identifier, $arrayToUse );
			}
		}
	}

	public function ced_cwsm_add_variation_product_meta_fields( $loop, $variation_data, $variation, $role_identifier = '' ) {
		if ( ! isset( $_POST['woocommerce_meta_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ) ) ) {
			return;
		}
		global $globalCWSM;

		$ced_cwsm_enable_range_wise_pricing = get_post_meta( $variation->ID, 'ced_cwsm_enable_range_wise_pricing' . $role_identifier, true );
		if ( 'yes' == $ced_cwsm_enable_range_wise_pricing ) {
			$ced_cwsm_enable_range_wise_pricing = 'checked="checked"';
		}

		$pricing_type = 'Price/Discount';
		$minQtyName   = 'ced_cwsm_range_min_qty' . $role_identifier;
		$maxQtyName   = 'ced_cwsm_range_max_qty' . $role_identifier;
		$valueToUse   = 'ced_cwsm_range_valueToUse' . $role_identifier;

		$attribute_description = 'Enable Range-Wise-Pricing.';

		if ( $globalCWSM->isHaveToRenderMetaField( 'ced_cwsm_enable_range_wise_pricing' ) ) {
			?>
			<div class="ced_cwsm_range_wise_pricing_wrapper_div">
				<h4 class="ced_cwsm_range_wise_pricing_heading"><span>+</span><?php esc_attr_e( 'Range Wise Pricing', 'woocommerce-market' ); ?></h4>
				<div class="ced_cwsm_range_wise_pricing_content_div" style="display:none;">
					<div class="ced_cwsm_container_section">
						<p class="">
							<label for="ced_cwsm_enable_range_wise_pricing<?php echo esc_attr( $role_identifier ); ?>[<?php echo esc_attr( $variation->ID ); ?>]">Enable Range-Wise-Pricing</label>
							<input class="checkbox" name="ced_cwsm_enable_range_wise_pricing<?php echo esc_attr( $role_identifier ); ?>[<?php echo esc_attr( $variation->ID ); ?>]" <?php echo esc_attr( $ced_cwsm_enable_range_wise_pricing ); ?> type="checkbox"> 
							<?php echo wc_help_tip( __( $attribute_description, 'woocommerce' ) ); ?>
						</p>
						<span class="button button-primary ced_cwsm_add_range_row"><?php esc_attr_e( 'Add Row', 'woocommerce-market' ); ?></span>
					</div>
					<table class="wp-list-table widefat fixed striped" >
						<tr>
							<th><?php esc_attr_e( 'Minimum Quantity', 'woocommerce-market' ); ?></th>
							<th><?php esc_attr_e( 'Maximum Quantity', 'woocommerce-market' ); ?></th>
							<th><?php esc_attr_e( $pricing_type, 'woocommerce-market' ); ?></th>
							<th><?php esc_attr_e( 'Action', 'woocommerce-market' ); ?></th>
						</tr>
						<?php
						$ced_cwsm_range_wise_pricing_table = get_post_meta( $variation->ID, 'ced_cwsm_range_wise_pricing_table' . $role_identifier, true );
						if ( is_array( $ced_cwsm_range_wise_pricing_table ) && ! empty( $ced_cwsm_range_wise_pricing_table ) ) {
							foreach ( $ced_cwsm_range_wise_pricing_table as $key => $value ) {

								?>
								<tr>
									<td><input type="number" name="<?php esc_attr_e( $minQtyName ); ?>[<?php echo esc_attr( $variation->ID ); ?>][]" value="<?php echo esc_attr( $value['minQty'] ); ?>"></td>
									<td><input type="number" name="<?php esc_attr_e( $maxQtyName ); ?>[<?php echo esc_attr( $variation->ID ); ?>][]" value="<?php echo esc_attr( $value['maxQty'] ); ?>"></td>
									<td><input type="text" class="wc_input_price" name="<?php esc_attr_e( $valueToUse ); ?>[<?php echo esc_attr( $variation->ID ); ?>][]" value="<?php echo esc_attr( $value['value'] ); ?>"></td>
									<td>
										<?php
										if ( 0 != $key ) {
											?>
											<span class="button button-primary ced_cwsm_delete_range_row">Delete</span>
											<?php
										}
										?>
									</td>
								</tr>
								<?php
							}
						} else {
							?>
							<tr>
								<td><input type="number" name="<?php esc_attr_e( $minQtyName ); ?>[<?php echo esc_attr( $variation->ID ); ?>][]"></td>
								<td><input type="number" name="<?php esc_attr_e( $maxQtyName ); ?>[<?php echo esc_attr( $variation->ID ); ?>][]"></td>
								<td><input type="text" class="wc_input_price" name="<?php esc_attr_e( $valueToUse ); ?>[<?php echo esc_attr( $variation->ID ); ?>][]"></td>
								<td>
								</td>
							</tr>
							<?php
						}
						?>
					</table>
				</div>
			</div>
			<?php
		}
	}


	/*** For Simple Product ***/

	/**
	 * This function saves meta-fields for simple products.
	 *
	 * @name ced_cwsm_save_added_simple_product_meta_fields()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_save_added_simple_product_meta_fields( $post_id, $role_identifier = '' ) {
		if ( ! isset( $_POST['woocommerce_meta_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ) ) ) {
			return;
		}
		$valueToSave = ( isset( $_POST[ 'ced_cwsm_enable_range_wise_pricing' . $role_identifier ] ) ) ? 'yes' : '';
		update_post_meta( $post_id, 'ced_cwsm_enable_range_wise_pricing' . $role_identifier, $valueToSave );

		if ( isset( $_POST[ 'ced_cwsm_range_min_qty' . $role_identifier ] ) ) {
			$arrayToUse             = array();
			$ced_cwsm_range_min_qty = isset( $_POST[ 'ced_cwsm_range_min_qty' . $role_identifier ] ) ? sanitize_text_field( $_POST[ 'ced_cwsm_range_min_qty' . $role_identifier ] ) : '';
			foreach ( $ced_cwsm_range_min_qty as $key => $value ) {
				$arrayToUse[] = array(
					'minQty' => isset( $_POST[ 'ced_cwsm_range_min_qty' . $role_identifier ][ $key ] ) ? sanitize_text_field( $_POST[ 'ced_cwsm_range_min_qty' . $role_identifier ][ $key ] ) : '',
					'maxQty' => isset( $_POST[ 'ced_cwsm_range_max_qty' . $role_identifier ][ $key ] ) ? sanitize_text_field( $_POST[ 'ced_cwsm_range_max_qty' . $role_identifier ][ $key ] ) : '',
					'value'  => isset( $_POST[ 'ced_cwsm_range_valueToUse' . $role_identifier ][ $key ] ) ? sanitize_text_field( $_POST[ 'ced_cwsm_range_valueToUse' . $role_identifier ][ $key ] ) : '',
				);
			}
			update_post_meta( $post_id, 'ced_cwsm_range_wise_pricing_table' . $role_identifier, $arrayToUse );
		}
	}

	/**
	 * This function creates meta-fields for simple products.
	 *
	 * @name ced_cwsm_add_simple_product_meta_fields()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_add_simple_product_meta_fields( $post, $thepostid, $role_identifier = '' ) {
		if ( ! isset( $_POST['woocommerce_meta_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ) ) ) {

			return;
		}
		global $post, $thepostid;

		if ( wc_get_product( $thepostid )->is_type( 'simple' ) ) {
			$pricing_type = 'Price/Discount';
			$minQtyName   = 'ced_cwsm_range_min_qty' . $role_identifier;
			$maxQtyName   = 'ced_cwsm_range_max_qty' . $role_identifier;
			$valueToUse   = 'ced_cwsm_range_valueToUse' . $role_identifier;
			?>
			<div class="ced_cwsm_range_wise_pricing_wrapper_div">
				<h4 class="ced_cwsm_range_wise_pricing_heading"><span>+</span><?php esc_attr_e( 'Range Wise Pricing', 'wholesale-market' ); ?></h4>
				<div class="ced_cwsm_range_wise_pricing_content_div" style="display:none;">
					<div class="ced_cwsm_container_section">
						<?php
						$attribute_description              = 'Enable Range-Wise-Pricing.';
						$ced_cwsm_enable_range_wise_pricing = get_post_meta( $thepostid, 'ced_cwsm_enable_range_wise_pricing' . $role_identifier, true );
						if ( 'yes' == $ced_cwsm_enable_range_wise_pricing ) {
							$ced_cwsm_enable_range_wise_pricing = 'checked="checked"';
						}
						?>
						<p class="">
							<label for="ced_cwsm_enable_range_wise_pricing<?php echo esc_attr( $role_identifier ); ?>"><?php esc_attr_e( 'Enable Range-Wise-Pricing', 'wholesale-market' ); ?></label>
							<input class="checkbox" name="ced_cwsm_enable_range_wise_pricing<?php echo esc_attr( $role_identifier ); ?>" <?php echo esc_attr( $ced_cwsm_enable_range_wise_pricing ); ?> type="checkbox"> 
							<?php echo wc_help_tip( __( $attribute_description, 'woocommerce' ) ); ?>
						</p>	
						<span class="button button-primary ced_cwsm_add_range_row" ><?php esc_attr_e( 'Add Row', 'wholesale-market' ); ?></span>
					</div>	
					<table class="wp-list-table widefat fixed striped" >
						<tr>
							<th><?php esc_attr_e( 'Minimum Quantity', 'wholesale-market' ); ?></th>
							<th><?php esc_attr_e( 'Maximum Quantity', 'wholesale-market' ); ?></th>
							<th><?php esc_attr_e( $pricing_type, 'wholesale-market' ); ?></th>
							<th><?php esc_attr_e( 'Action', 'wholesale-market' ); ?></th>
						</tr>
						<?php
						$ced_cwsm_range_wise_pricing_table = get_post_meta( $thepostid, 'ced_cwsm_range_wise_pricing_table' . $role_identifier, true );
						if ( is_array( $ced_cwsm_range_wise_pricing_table ) && ! empty( $ced_cwsm_range_wise_pricing_table ) ) {
							foreach ( $ced_cwsm_range_wise_pricing_table as $key => $value ) {

								?>
								<tr>
									<td><input type="number" name="<?php esc_attr_e( $minQtyName ); ?>[]" value="<?php echo esc_attr( $value['minQty'] ); ?>"></td>
									<td><input type="number" name="<?php esc_attr_e( $maxQtyName ); ?>[]" value="<?php echo esc_attr( $value['maxQty'] ); ?>"></td>
									<td><input type="text" class="wc_input_price" name="<?php esc_attr_e( $valueToUse ); ?>[]" value="<?php echo esc_attr( $value['value'] ); ?>"></td>
									<td>
										<?php
										if ( 0 != $key ) {
											?>
											<span class="button button-primary ced_cwsm_delete_range_row">Delete</span>
											<?php
										}
										?>
									</td>
								</tr>
								<?php
							}
						} else {
							?>
							<tr>
								<td><input type="number" name="<?php esc_attr_e( $minQtyName ); ?>[]"></td>
								<td><input type="number" name="<?php esc_attr_e( $maxQtyName ); ?>[]"></td>
								<td><input type="text" class="wc_input_price" name="<?php esc_attr_e( $valueToUse ); ?>[]"></td>
								<td>
								</td>
							</tr>
							<?php
						}
						?>
					</table>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * This function enqueue_scripts after editing product.
	 *
	 * @name product_edit_page_enqueue_scripts()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function product_edit_page_enqueue_scripts() {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';
		if ( in_array( $screen_id, array( 'product', 'edit-product' ) ) ) {
			wp_enqueue_script( 'ced_cwsm_range_wise_pricing_js', plugins_url( 'js/cwsm_range_wise_pricing_js.js', __FILE__ ), array( 'jquery' ), '1.0', true );
			wp_localize_script(
				'ced_cwsm_range_wise_pricing_js',
				'ced_cwsm_range_wise_pricing_js_AJAX',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
				)
			);
		}
	}

	/**
	 * This function alter wholesale price.
	 *
	 * @name ced_cwsm_alter_wholesale_price()
	 *
	 * @link  http://www.cedcommerce.com/
	 */

	public function ced_cwsm_alter_wholesale_price( $wholesalePrice, $productId ) {
		$productIdReceived = $productId;
		global $woocommerce,$globalCWSM;

		$role_identifier = $globalCWSM->getCurrentWholesaleUserRole();
		( 'ced_cwsm_wholesale_user' == $role_identifier ) ? $role_identifier = '' : $role_identifier = '_' . $role_identifier;

		if ( ! $globalCWSM->isHaveToRenderMetaField( 'ced_cwsm_enable_range_wise_pricing' ) ) {
			$_product  = wc_get_product( $productId );
			$productId = $_product->get_id();
		}

		if ( is_cart() ) {
			$cartRef = $woocommerce->cart->cart_contents;
			foreach ( $cartRef as $cartkey => $cartvalue ) {
				$currentID = $cartvalue['product_id'];
				if ( $globalCWSM->isHaveToRenderMetaField( 'ced_cwsm_enable_range_wise_pricing' ) ) {
					if ( isset( $cartvalue['variation_id'] ) && ! empty( $cartvalue['variation_id'] ) ) {
						$currentID = $cartvalue['variation_id'];
					}
				} elseif ( $productId == $cartvalue['variation_id'] ) {
					$productId = $cartvalue['product_id'];

				}

				if ( $currentID == $productId && get_post_meta( $productId, 'ced_cwsm_enable_range_wise_pricing' . $role_identifier, true ) == 'yes' ) {
					$quantity                          = (int) $cartvalue['quantity'];
					$ced_cwsm_range_wise_pricing_table = get_post_meta( $productId, 'ced_cwsm_range_wise_pricing_table' . $role_identifier, true );
					foreach ( $ced_cwsm_range_wise_pricing_table as $key => $value ) {
						if ( -1 != (int) $value['maxQty'] ) {
							if ( $quantity >= (int) $value['minQty'] && $quantity <= (int) $value['maxQty'] ) {
								$wholesalePrice = $this->getCalculatedWholesalePrice( (float) $value['value'], $productIdReceived );
								break;
							}
						} else {
							$wholesalePrice = $this->getCalculatedWholesalePrice( (int) $value['value'], $productIdReceived );
						}
					}
					break;
				}
			}
		} else {
			$quantity = 1;
			if ( get_post_meta( $productId, 'ced_cwsm_enable_range_wise_pricing' . $role_identifier, true ) == 'yes' ) {
				$ced_cwsm_range_wise_pricing_table = get_post_meta( $productId, 'ced_cwsm_range_wise_pricing_table' . $role_identifier, true );
				foreach ( $ced_cwsm_range_wise_pricing_table as $key => $value ) {
					if ( -1 != (int) $value['maxQty'] ) {
						if ( $quantity >= (int) $value['minQty'] && $quantity <= (int) $value['maxQty'] ) {
							$wholesalePrice = $this->getCalculatedWholesalePrice( (int) $value['value'], $productIdReceived );
							break;
						}
					} else {
						$wholesalePrice = $this->getCalculatedWholesalePrice( (int) $value['value'], $productIdReceived );
					}
				}
			}
		}
		return $wholesalePrice;
	}
	/**
	 * This function returns calculated wholesale price.
	 *
	 * @name getCalculatedWholesalePrice()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function getCalculatedWholesalePrice( $value, $productId ) {
		$ced_cwsm_range_wise_pricing_type = get_option( 'ced_cwsm_range_wise_pricing_type', false );
		if ( 'ced_cwsm_range_wise_pricing_type_price' == $ced_cwsm_range_wise_pricing_type ) {
			return $value;
		} else {
			$ced_cwsm_range_wise_pricing_discount_on = get_option( 'ced_cwsm_range_wise_pricing_discount_on', false );
			$_product                                = wc_get_product( $productId );
			if ( 'ced_cwsm_range_wise_pricing_discount_on_applicable_price' == $ced_cwsm_range_wise_pricing_discount_on ) {
				$regular_price = floatval( $_product->get_price() );
			} else {
				$regular_price = floatval( $_product->get_regular_price() );
			}

			$ced_cwsm_range_wise_pricing_discount_type = get_option( 'ced_cwsm_range_wise_pricing_discount_type', false );
			if ( 'ced_cwsm_range_wise_pricing_discount_type_fixed' == $ced_cwsm_range_wise_pricing_discount_type ) {
				return ( $regular_price - $value );
			} else {
				return ( ( $regular_price ) - ( ( $value * $regular_price ) / 100 ) );
			}
		}
	}

	/**
	 * This function adds section on wholesale market tab.
	 *
	 * @name ced_cwsm_append_product_addon_sections()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_append_product_addon_sections( $sections ) {
		$sections['ced_cwsm_range_wise_pricing_module'] = __( 'Range Wise Pricing', 'wholesale-market' );
		return $sections;
	}

	/**
	 * This function adds settings on wholesale market tab.
	 *
	 * @name ced_cwsm_append_product_addon_settings()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_append_product_addon_settings( $settingReceived, $current_section ) {
		if ( 'ced_cwsm_range_wise_pricing_module' == $current_section ) {
			$settings = apply_filters(
				'ced_cwsm_range_wise_pricing_setting',
				array(
					'section_title-1'                    => array(
						'name' => __( 'Range Wise Pricing', 'wholesale-market' ),
						'type' => 'title',
						'desc' => __( 'Settings regarding "Range Wise Pricing" for wholesale-users are listed below.<br/>Utilizing this option you can set "Range-Wise-Pricing" Scheme for a product For Wholesale Users.<br/>Use "-1" in last row of range-wise-pricing.', 'wholesale-market' ),
						'id'   => 'wc_cwsm_setting_tab_section_title-1',
					),
					'ced_cwsm_enable_range_wise_pricing' => array(
						'title'   => __( 'Enable Range Wise Pricing For Wholesale-Users', 'wholesale-market' ),
						'desc'    => __( 'Enable To Activate Range Wise Pricing Condition ', 'wholesale-market' ),
						'id'      => 'ced_cwsm_enable_range_wise_pricing',
						'type'    => 'checkbox',
						'default' => 'no',
					),
					'ced_cwsm_range_wise_pricing_type'   => array(
						'title'    => __( 'Price Or Discount ?', 'wholesale-market' ),
						'desc'     => __( 'Select What You Want To Enter Here.<br/>Select Price, If You Want To Enter Range-Wise Price.<br/>Select Discount, If You Want To Enter Range-Wise Discount.', 'wholesale-market' ),
						'id'       => 'ced_cwsm_range_wise_pricing_type',
						'default'  => '',
						'type'     => 'select',
						'class'    => 'wc-enhanced-select',
						'desc_tip' => false,
						'options'  => array(
							'ced_cwsm_range_wise_pricing_type_price'      => __( 'Price', 'wholesale-market' ),
							'ced_cwsm_range_wise_pricing_type_discount'   => __( 'Discount', 'wholesale-market' ),
						),
					),
					'ced_cwsm_range_wise_pricing_discount_type' => array(
						'title'    => __( 'Discount Type ?', 'wholesale-market' ),
						'desc'     => __( 'Select Your Discount Type Here.<br/>Select Percentage Discount, If You Want To Enter Range-Wise Percentage Discount.<br/>Select Fixed Price Discount, If You Want To Enter Range-Wise Fixed Price Discount.', 'wholesale-market' ),
						'id'       => 'ced_cwsm_range_wise_pricing_discount_type',
						'default'  => '',
						'type'     => 'select',
						'class'    => 'wc-enhanced-select',
						'desc_tip' => false,
						'options'  => array(
							'ced_cwsm_range_wise_pricing_discount_type_percentage'      => __( 'Percentage Discount', 'wholesale-market' ),
							'ced_cwsm_range_wise_pricing_discount_type_fixed'   => __( 'Fixed Price Discount', 'wholesale-market' ),
						),
					),
					'ced_cwsm_range_wise_pricing_discount_on' => array(
						'title'    => __( 'On Which Price To Apply ?', 'wholesale-market' ),
						'desc'     => __( '1. Apply On Product Regular Price :: Discount Will Be Applicable On Product\'s Regular Price.<br/>2. Apply On Product Applicable Price :: Discount Will Be Applicable On Product\'s Sale Price, If It\'s Not Available Then On Product\'s Regular Price.', 'wholesale-market' ),
						'id'       => 'ced_cwsm_range_wise_pricing_discount_on',
						'default'  => 'ced_cwsm_range_wise_pricing_discount_on_regular_price',
						'type'     => 'select',
						'class'    => 'wc-enhanced-select',
						'desc_tip' => false,
						'options'  => array(
							'ced_cwsm_range_wise_pricing_discount_on_regular_price'      => __( 'Apply On Product Regular Price', 'wholesale-market' ),
							'ced_cwsm_range_wise_pricing_discount_on_applicable_price'   => __( 'Apply On Product Applicable Price', 'wholesale-market' ),
						),
					),
					'section_end-1'                      => array(
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
new CED_CWSM_Range_Wise_Pricing_Module();
?>
