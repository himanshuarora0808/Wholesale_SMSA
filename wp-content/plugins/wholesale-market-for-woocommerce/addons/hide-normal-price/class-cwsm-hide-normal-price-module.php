<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Hide Normal Price Form Wholesale Users.
 *
 * @class    CED_CWSM_Hide_Normal_Price_Module
 * @version  1.0.2
 * @package  wholesale-market-product-addon/addons/hide-normal-price
 */
?>
<?php
class CED_CWSM_Hide_Normal_Price_Module {

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

		add_filter( 'ced_cwsm_append_product_addon_sections', array( $this, 'ced_cwsm_hide_normal_price_module_add_section' ), 10.1, 1 );
		add_filter( 'ced_cwsm_append_product_addon_settings', array( $this, 'ced_cwsm_hide_normal_price_module_add_setting' ), 10, 2 );

		$ced_cwsm_enable_hide_normal_price = get_option( 'ced_cwsm_enable_hide_normal_price', false );
		if ( empty( $ced_cwsm_enable_hide_normal_price ) || 'no' == $ced_cwsm_enable_hide_normal_price ) {
			return;
		}

		add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'remove_woocommerce_template_loop_price' ), 1 );
		add_action( 'woocommerce_single_product_summary', array( $this, 'remove_woocommerce_template_single_price' ), 1 );
		add_filter( 'ced_cwsm_alter_variation_price', array( $this, 'remove_woocommerce_variation_price' ), 10, 3 );
	}

	/**
	 * This function remove woocommerce price on shop page.
	 *
	 * @name remove_woocommerce_template_loop_price()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function remove_woocommerce_template_loop_price() {
		global $globalCWSM;
		if ( $globalCWSM->isCurrentUserIsWholesaleUser() ) {
			remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
		}
	}

	/**
	 * This function remove woocommerce price from product's single page.
	 *
	 * @name remove_woocommerce_template_single_price()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function remove_woocommerce_template_single_price() {
		global $globalCWSM;
		if ( $globalCWSM->isCurrentUserIsWholesaleUser() ) {
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
		}
	}

	/**
	 * This function remove woocommerce variation price from variable product's variation section on product single page.
	 *
	 * @name remove_woocommerce_variation_price()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function remove_woocommerce_variation_price( $price_html, $variation, $customTextForWholesalePrice ) {
		global $globalCWSM;
		if ( $globalCWSM->isCurrentUserIsWholesaleUser() ) {
			return '<span class="price">' . $customTextForWholesalePrice . '</span>';
		}
	}

	/**
	 * This function adds section on wholesale market tab.
	 *
	 * @name ced_cwsm_hide_normal_price_module_add_section()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_hide_normal_price_module_add_section( $sections ) {
		$sections['ced_cwsm_hide_normal_price_module'] = __( 'Hide Normal Price', 'wholesale-market' );
		return $sections;
	}

	/**
	 * This function adds settings on wholesale market tab.
	 *
	 * @name ced_cwsm_hide_normal_price_module_add_setting()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_hide_normal_price_module_add_setting( $settingReceived, $current_section ) {
		if ( 'ced_cwsm_hide_normal_price_module' == $current_section ) {
			$settings = apply_filters(
				'ced_cwsm_hide_normal_price_setting',
				array(
					'section_title-1'                   => array(
						'name' => __( 'Hide Normal Price Across Shop For Wholesale-Users', 'wholesale-market' ),
						'type' => 'title',
						'desc' => __( 'Settings regarding hide normal price across shop for wholesale-users are listed below.<br/>Utilizing this option you hide normal WooCommerce-Pricing for Wholesale-Users.', 'wholesale-market' ),
						'id'   => 'wc_cwsm_setting_tab_section_title-1',
					),
					'ced_cwsm_enable_hide_normal_price' => array(
						'title'   => __( 'Enable Hide Normal Price Across Shop For Wholesale-Users', 'wholesale-market' ),
						'desc'    => __( 'Enable To Activate Hide Normal Price Across Shop Condition ', 'wholesale-market' ),
						'id'      => 'ced_cwsm_enable_hide_normal_price',
						'type'    => 'checkbox',
						'default' => 'no',
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
new CED_CWSM_Hide_Normal_Price_Module();

