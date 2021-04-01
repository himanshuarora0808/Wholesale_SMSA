<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

define( 'CED_WURA_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'CED_WURA_DIR_URL', plugin_dir_url( __FILE__ ) );

/**
 * Core class wrapping all plugin functionality.
 *
 * @class    CED_CWSM_Core_Class
 * @version  2.0.8
 * @package Class
 */
class CED_Wholesale_User_Register_Addon {

	public function __construct() {
		include_once CED_WURA_DIR_PATH . '/include/class-wholesale-user-register-addon.php';
		include_once CED_WURA_DIR_PATH . '/admin/class-wholesale-user-register-notification.php';
		include_once CED_WURA_DIR_PATH . '/admin/class-wholesale-request-function.php';
		include_once CED_WURA_DIR_PATH . '/admin/class-wholesale-price-tax.php';
		include_once CED_WURA_DIR_PATH . '/admin/class-wholesale-order-coloumn.php';
		include_once CED_WURA_DIR_PATH . '/widgets/wholesale-request-widgets.php';
	}
}
// Create instance of class
new CED_Wholesale_User_Register_Addon();

