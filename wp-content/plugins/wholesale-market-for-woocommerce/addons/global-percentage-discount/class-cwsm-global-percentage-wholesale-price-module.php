<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Adds "Global Percentage Discount Across Shop For Wholesale Users" condition to core plugin using hooks and filters of that.
 *
 * @class    CED_CWSM_Global_Percentage_Wholesale_Price_Module
 * @version  1.0.2
 * @package  wholesale-market-product-addon/addons/global-percentage-discount
 * @package Class
 */
?>
<?php
class CED_CWSM_Global_Percentage_Wholesale_Price_Module {

	public function __construct() {

		global $ced_cwsm_product_addon_license; // it must be dynamic for each extension
		$license_key = get_option( 'ced_cwsm_product_addon_license_key', false );
		$module_name = get_option( 'ced_cwsm_product_addon_license_module', false );
		if ( is_array( $ced_cwsm_product_addon_license ) ) {
			$http_req = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( $_SERVER['HTTP_HOST'] ) : '';
			if ( $ced_cwsm_product_addon_license['license'] == $license_key && $ced_cwsm_product_addon_license['module_name'] == $module_name && $ced_cwsm_product_addon_license['domain'] == $http_req ) {
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

		add_filter( 'ced_cwsm_append_product_addon_sections', array( $this, 'ced_cwsm_global_percentage_wholesale_price_module_add_section' ), 10.1, 1 );
		add_filter( 'ced_cwsm_append_product_addon_settings', array( $this, 'ced_cwsm_global_percentage_wholesale_price_module_add_setting' ), 10, 2 );

		// add_filter( 'ced_cwsm_avoid_wholesale_price_to_apply', array( $this, 'ced_cwsm_avoid_wholesale_price_to_apply' ), 11, 3 );

		// add_filter( 'ced_cwsm_check_to_apply_wholesale_price', array( $this, 'ced_cwsm_check_to_apply_wholesale_price' ), 11, 3 );
		add_filter( 'ced_cwsm_alter_wholesale_price', array( $this, 'ced_cwsm_alter_wholesale_price' ), 11, 2 );

		add_filter( 'ced_cwsm_alter_wholesale_price_on_cart_msg', array( $this, 'ced_cwsm_alter_wholesale_price' ), 11, 2 );
	}

	/**
	 * This function adds section on wholesale market tab.
	 *
	 * @name ced_cwsm_global_percentage_wholesale_price_module_add_section()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_global_percentage_wholesale_price_module_add_section( $sections ) {
		$sections['ced_cwsm_global_percentage_wholesale_price_module'] = __( 'Global Percentage(%) Discount', 'wholesale-market' );
		return $sections;
	}

	/**
	 * This function adds settings on wholesale market tab.
	 *
	 * @name ced_cwsm_global_percentage_wholesale_price_module_add_setting()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_global_percentage_wholesale_price_module_add_setting( $settingReceived, $current_section ) {
		if ( 'ced_cwsm_global_percentage_wholesale_price_module' == $current_section ) {
			$settings = apply_filters(
				'ced_cwsm_min_checkout_price_module_setting',
				array(
					'section_title-1'                => array(
						'name' => __( 'Global Percentage(%) Discount Across Shop For Wholesale-Users', 'wholesale-market' ),
						'type' => 'title',
						'desc' => __( 'Settings regarding global percentage(%) discount across shop for wholesale-users are listed below.<br/>Utilizing this option you can set a global discount on regular shop-price(i.e., wholesale-price) for wholesale-customers on all availabale product\'s in your shop at once.', 'wholesale-market' ),
						'id'   => 'wc_cwsm_setting_tab_section_title-2',
					),
					'ced_cwsm_enable_GPDASFWSU'      => array(
						'title'   => __( 'Enable Global Percentage(%) Discount Across Shop For Wholesale-Users', 'wholesale-market' ),
						'desc'    => __( 'Enable To Activate Global Percentage Discount Across Shop Condition ', 'wholesale-market' ),
						'id'      => 'ced_cwsm_enable_GPDASFWSU',
						'type'    => 'checkbox',
						'default' => 'no',
					),
					'ced_cwsm_GPDASFWSU'             => array(
						'title'             => __( 'GLOBAL Percentage(%) DISCOUNT', 'wholesale-market' ),
						'desc'              => __( 'Wholesale-Users Will Get That Much Amount Of Discount, In Comparision To Regular-Customers.', 'wholesale-market' ),
						'id'                => 'ced_cwsm_GPDASFWSU',
						'css'               => 'width:80px;',
						'type'              => 'number',
						'custom_attributes' => array(
							'min'  => '0',
							'step' => 'any',
						),
						'default'           => '0',
						'desc_tip'          => true,
					),
					'ced_cwsm_GPDASFWSU_which_price' => array(
						'title'    => __( 'On Which Price To Apply ?', 'wholesale-market' ),
						'desc'     => __( '1. Apply On Product Regular Price :: Percentage(%) Discount Will Be Applicable On Product\'s Regular Price.<br/>2. Apply On Product Applicable Price :: Percentage(%) Discount Will Be Applicable On Product\'s Sale Price, If It\'s Not Available Then On Product\'s Regular Price.', 'wholesale-market' ),
						'id'       => 'ced_cwsm_GPDASFWSU_which_price',
						'default'  => 'ced_cwsm_GPDASFWSU_regular_price',
						'type'     => 'select',
						'class'    => 'wc-enhanced-select',
						'desc_tip' => false,
						'options'  => array(
							'ced_cwsm_GPDASFWSU_regular_price'      => __( 'Apply On Product Regular Price', 'wholesale-market' ),
							'ced_cwsm_GPDASFWSU_applicable_price'   => __( 'Apply On Product Applicable Price', 'wholesale-market' ),
						),
					),
					'ced_cwsm_GPDASFWSU_give_priority_to_ws_price' => array(
						'title'   => __( 'Enable To Give Priority To Wholesale Price Entered At Product Panel', 'wholesale-market' ),
						'desc'    => __( 'Enable To Give Priority To Wholesale Price Entered At Product Panel.<br/>That Will Apply Wholesale-Price, If Present At Product Panel. i.e, Not Let Global Percentage(%) Discount Condition To Override Wholesale-Price Present At Product Panel.', 'wholesale-market' ),
						'id'      => 'ced_cwsm_GPDASFWSU_give_priority_to_ws_price',
						'type'    => 'checkbox',
						'default' => 'no',
					),
					'section_end-1'                  => array(
						'type' => 'sectionend',
						'id'   => 'wc_ced_ws_setting_tab_section_end-1',
					),
				)
			);
			return $settings;
		}
		return $settingReceived;
	}

	/**
	 * This function alter product query on shop page.
	 *
	 * @name ced_cwsm_alter_wholesale_price()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_avoid_wholesale_price_to_apply( $applyWholesalePrice, $productId, $value ) {
		if ( $this->if_ced_cwsm_enable_GPDASFWSU() ) {
			return $applyWholesalePrice;
		}
	}

	/**
	 * This function alter product query on shop page.
	 *
	 * @name ced_cwsm_alter_wholesale_price()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_alter_wholesale_price( $wholesalePrice, $productId ) {
		$ced_cwsm_GPDASFWSU_give_priority_to_ws_price = get_option( 'ced_cwsm_GPDASFWSU_give_priority_to_ws_price', false );
		if ( 'yes' == $ced_cwsm_GPDASFWSU_give_priority_to_ws_price ) {
			if ( '' != $wholesalePrice && '0' != $wholesalePrice ) {
				return $wholesalePrice;
			}
		}

		if ( $this->if_ced_cwsm_enable_GPDASFWSU() ) {
			$priceToApply = $this->ced_cwsm_get_wholesale_price_after_global_percentage( $productId );
			return $priceToApply;
		}
		return $wholesalePrice;
	}


	/**
	 * This function check whether "Global Percentage Discount Across Shop For Wholesale Users" condition is enable or not .
	 *
	 * @name if_ced_cwsm_enable_GPDASFWSU()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function if_ced_cwsm_enable_GPDASFWSU() {
		$ced_cwsm_enable_GPDASFWSU = get_option( 'ced_cwsm_enable_GPDASFWSU' );
		if ( isset( $ced_cwsm_enable_GPDASFWSU ) && ! empty( $ced_cwsm_enable_GPDASFWSU ) && 'yes' == $ced_cwsm_enable_GPDASFWSU ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * This function calculates wholesale-price for product keeping GPDASFWSU in mind.
	 *
	 * @name ced_cwsm_get_wholesale_price_after_global_percentage()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_get_wholesale_price_after_global_percentage( $productId ) {
		$_product = wc_get_product( $productId );

		$ced_cwsm_GPDASFWSU_which_price = get_option( 'ced_cwsm_GPDASFWSU_which_price' );
		if ( 'ced_cwsm_GPDASFWSU_applicable_price' == $ced_cwsm_GPDASFWSU_which_price ) {
			$regular_price = floatval( $_product->get_price() );
		} else {
			$regular_price = floatval( $_product->get_regular_price() );
		}

		$ced_cwsm_GPDASFWSU = get_option( 'ced_cwsm_GPDASFWSU' );
		$ced_cwsm_GPDASFWSU = floatval( $ced_cwsm_GPDASFWSU );
		if ( 0 != $ced_cwsm_GPDASFWSU ) {
			$priceToApply = ( ( $regular_price ) - ( ( $ced_cwsm_GPDASFWSU * $regular_price ) / 100 ) );
			return $priceToApply;
		}
	}

}
new CED_CWSM_Global_Percentage_Wholesale_Price_Module();

