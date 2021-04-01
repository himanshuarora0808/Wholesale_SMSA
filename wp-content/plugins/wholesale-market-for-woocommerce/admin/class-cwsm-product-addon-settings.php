<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Adds minimum product quantity condition to core plugin using hooks and filters of that.
 *
 * @class    CED_CWSM_Product_Addon_Settings
 * @version  1.0.2
 * @package  wholesale-market-product-addon/admin
 */

class CED_CWSM_Product_Addon_Settings {

	public function __construct() {
		$this->add_required_hooks_and_filters();

	}

	/**
	 * This function hooks into all filters and actions available in core plugin.
	 *
	 * @name add_required_hooks_and_filters()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function add_required_hooks_and_filters() {
		global $ced_cwsm_current_tab;
		if ( 'ced_cwsm_product_addon' == $ced_cwsm_current_tab ) {
			add_filter( 'ced_cwsm_sections_' . $ced_cwsm_current_tab, array( $this, 'ced_cwsm_add_sections' ), 10, 1 );
			add_filter( 'ced_cwsm_settings_' . $ced_cwsm_current_tab, array( $this, 'ced_cwsm_add_settings' ), 10, 1 );

			add_filter( 'ced_cwsm_append_product_addon_settings', array( $this, 'ced_cwsm_show_license_setting' ), 10, 2 );
		}
	}


	public function ced_cwsm_add_sections( $sections ) {

		global $ced_cwsm_product_addon_license; // it must be dynamic for each extension
		$license_key  = get_option( 'ced_cwsm_product_addon_license_key', false );
		$module_name  = get_option( 'ced_cwsm_product_addon_license_module', false );
		$service_host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( $_SERVER['HTTP_HOST'] ) : '';
		if ( is_array( $ced_cwsm_product_addon_license ) ) {
			if ( $ced_cwsm_product_addon_license['license'] == $license_key && $ced_cwsm_product_addon_license['module_name'] == $module_name && $ced_cwsm_product_addon_license['domain'] == $service_host ) {
				return $sections;
			}
		}
		$sections = apply_filters( 'ced_cwsm_append_product_addon_sections', $sections );
		return $sections;
	}

	public function ced_cwsm_add_settings( $settings ) {
		global $ced_cwsm_current_section;
		$settings = apply_filters( 'ced_cwsm_append_product_addon_settings', $settings, $ced_cwsm_current_section );
		return $settings;
	}

	public function ced_cwsm_show_license_setting( $settingReceived, $current_section ) {
		if ( 'ced_cwsm_product_addon_license_panel' == $current_section ) {
			$GLOBALS['hide_save_button'] = true;
			require_once CED_CWMAD_PRODUCT_ADDON_PLUGIN_DIR_PATH . 'addons/license/license.php';
			$settings = array();
			return $settings;
		}
		return $settingReceived;
	}
}

