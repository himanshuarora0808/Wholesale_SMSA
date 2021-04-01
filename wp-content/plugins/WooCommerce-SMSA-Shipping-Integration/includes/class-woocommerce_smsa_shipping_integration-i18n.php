<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://cedcommerce.com/
 * @since      1.0.0
 *
 * @package    woocommerce_smsa_shipping_integration
 * @subpackage woocommerce_smsa_shipping_integration/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    woocommerce_smsa_shipping_integration
 * @subpackage woocommerce_smsa_shipping_integration/includes
 * @author     cedcommerce <webmaster@cedcommerce.com>
 */
class woocommerce_smsa_shipping_integration_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'woocommerce_smsa_shipping_integration',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
