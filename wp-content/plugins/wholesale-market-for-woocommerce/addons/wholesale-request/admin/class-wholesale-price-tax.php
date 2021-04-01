<?php
/**
 * Exit if accessed directly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Wholesale_Price_Tax' ) ) {
	/**
	 * This is class for add feature of register user as wholesale user .
	 *
	 * @name    Wholesale_user_register_notification
	 * @package Class
	 */

	class Wholesale_Price_Tax {
		/**
		 * This is construct of class
		 *
		 * @link http://cedcommerce.com/
		 */
		public function __construct() {
			add_filter( 'ced_cwsm_append_basic_sections', array( $this, 'ced_wura_wholesale_tax_section' ), 10, 1 );
			add_filter( 'ced_cwsm_append_basic_settings', array( $this, 'ced_wura_wholesale_tax_setting' ), 10, 2 );
			add_filter( 'woocommerce_product_is_taxable', array( $this, 'ced_wura_wholesale_tax_disable' ), 10, 2 );
		}

		/**
		 * This function sets price when tax is disabled
		 *
		 * @name ced_wura_wholesale_tax_disable()
		 *
		 * @link  http://www.cedcommerce.com/
		 */
		public function ced_wura_wholesale_tax_disable( $taxable, $product ) {
			$current_user_id = get_current_user_id();
			if ( $current_user_id > 0 ) {
				$current_user_info = get_userdata( $current_user_id );
				$current_user_role = $current_user_info->roles;
				if ( in_array( 'ced_cwsm_wholesale_user', $current_user_role ) ) {
					$enable = get_option( 'ced_cwsm_wholesale_tax_exclude', false );
					if ( isset( $enable ) && ! empty( $enable ) ) {
						if ( 'yes' === $enable ) {
							$taxable = false;
						}
					}
				}
			}
			return $taxable;
		}

		/**
		 * This function is used to add tax setting
		 *
		 * @name ced_wura_wholesale_tax_setting()
		 *
		 * @link  http://www.cedcommerce.com/
		 */

		public function ced_wura_wholesale_tax_setting( $settingReceived, $current_section ) {
			if ( 'ced_wura_wholesale_tax' == $current_section ) {
				$settings = apply_filters(
					'ced_cwsm_ced_wura_wholesale_tax_module_setting',
					array(
						'wholesale_tax_title'       => array(
							'name' => __( 'Product Tax Section', 'wholesale-market' ),
							'type' => 'title',
							'desc' => __( 'Settings regarding include/exclude tax in wholesale price of product. According to following setting taxes is applied to product.', 'wholesale-market' ),
							'id'   => 'wc_cwsm_wholesale_tax_section_title',
						),

						'enable_wholesale_tax'      => array(
							'title'   => __( 'Exclude Tax For Wholesale User', 'wholesale-market' ),
							'desc'    => __( 'Exclude Tax For Wholesale User', 'wholesale-market' ),
							'id'      => 'ced_cwsm_wholesale_tax_exclude',
							'default' => 'no',
							'type'    => 'checkbox',
						),
						'wholesale_tax_section_end' => array(
							'type' => 'sectionend',
							'id'   => 'wc_ced_ws_wholesale_tax_section_end',
						),
					)
				);
				return $settings;
			}
			return $settingReceived;
		}

		public function ced_wura_wholesale_tax_section( $sections ) {
			$sections['ced_wura_wholesale_tax'] = __( 'Tax', 'wholesale-market' );
			return $sections;
		}
	}
	new Wholesale_Price_Tax();
}

