<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://cedcommerce.com/
 * @since             1.0.0
 * @package           woocommerce_smsa_shipping_integration
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce SMSA Shipping Integration
 * Plugin URI:        https://cedcommerce.com/
 * Description:       With this you can create Shipment directly with SMSA, generate invoice, download labels.
 * Version:           2.0.0
 * Author:            cedcommerce
 * Author URI:        http://cedcommerce.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woocommerce_smsa_shipping_integration
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woocommerce_smsa_shipping_integration-activator.php
 */
function activate_woocommerce_smsa_shipping_integration() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce_smsa_shipping_integration-activator.php';
	woocommerce_smsa_shipping_integration_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woocommerce_smsa_shipping_integration-deactivator.php
 */
function deactivate_woocommerce_smsa_shipping_integration() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce_smsa_shipping_integration-deactivator.php';
	woocommerce_smsa_shipping_integration_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_woocommerce_smsa_shipping_integration' );
register_deactivation_hook( __FILE__, 'deactivate_woocommerce_smsa_shipping_integration' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce_smsa_shipping_integration.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_woocommerce_smsa_shipping_integration() {

	$plugin = new woocommerce_smsa_shipping_integration();
	$plugin->run();

}
run_woocommerce_smsa_shipping_integration();
