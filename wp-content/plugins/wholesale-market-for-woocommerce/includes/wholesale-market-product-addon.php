<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'CED_CWSM_Product_Addon' ) ) {
	class CED_CWSM_Product_Addon {

		public function __construct() {
			$this->define_constants();
			$this->add_hooks_and_filters();
			$this->include_required_files();
		}


		/**
		 * This function defines neccessary constants to be used throughout the plugin.
		 *
		 * @name define_constants()
		 *
		 * @link: http://www.cedcommerce.com/
		 */
		public function define_constants() {
			define( 'WHOLESALE-MARKET', 'wholesale-market-product-addon' );
			define( 'CED_CWSM_PRODUCT_ADDON_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
			define( 'CED_CWSM_PRODUCT_ADDON_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
		}

		/**
		 * This function includes neccessary files required.
		 *
		 * @name include_required_files()
		 *
		 * @link: http://www.cedcommerce.com/
		 */
		public function include_required_files() {
			$this->setLicense();
			require_once CED_CWMAD_PRODUCT_ADDON_PLUGIN_DIR_PATH . 'admin/class-cwsm-product-addon-settings.php';
			require_once CED_CWMAD_PRODUCT_ADDON_PLUGIN_DIR_PATH . 'addons/global-percentage-discount/class-cwsm-global-percentage-wholesale-price-module.php';
			require_once CED_CWMAD_PRODUCT_ADDON_PLUGIN_DIR_PATH . 'addons/pro-qty-multiplier/class-cwsm-quantity-multiplier-module.php';
			require_once CED_CWMAD_PRODUCT_ADDON_PLUGIN_DIR_PATH . 'addons/consider-variations-as-one/class-cwsm-consider-variations-as-one-module.php';
			require_once CED_CWMAD_PRODUCT_ADDON_PLUGIN_DIR_PATH . 'addons/hide-normal-price/class-cwsm-hide-normal-price-module.php';
			require_once CED_CWMAD_PRODUCT_ADDON_PLUGIN_DIR_PATH . 'addons/range-wise-pricing/class-cwsm-range-wise-pricing-module.php';
			require_once CED_CWMAD_PRODUCT_ADDON_PLUGIN_DIR_PATH . 'addons/wholesale-exclusive-product/class-cwsm-wholesale-exclusive-products.php';

		}


		public function add_hooks_and_filters() {
			$plugin = plugin_basename( __FILE__ );
			add_filter( "plugin_action_links_$plugin", array( $this, 'ced_cwsm_plugin_show_settings_link' ) );
			add_action( 'plugins_loaded', array( $this, 'ced_cwsm_load_textdomain' ) );

			add_filter( 'ced_cwsm_settings_tabs_array', array( $this, 'ced_cwsm_add_product_addon_tab' ) );

			add_action( 'after_ced_cwsm_admin_settings_initiated', array( $this, 'initiate_ced_cwsm_product_addon_settings' ) );

			// add_action( 'wp_ajax_ced_cwsm_product_addon_validate_licensce', array( $this, 'ced_cwsm_product_addon_validate_licensce') );

			// add_action( 'plugins_loaded', array( $this, 'checkForPluginUpdate') );
		}

		public function setLicense() {
			global $ced_cwsm_product_addon_license;
			$ced_cwsm_product_addon_license = get_option( 'ced_cwsm_product_addon_license', null );
			if ( ! empty( $ced_cwsm_product_addon_license ) ) {
				$response = json_decode( $ced_cwsm_product_addon_license, true );
				$ced_hash = '';

				if ( isset( $response['hash'] ) && isset( $response['level'] ) ) {
					$ced_hash  = $response['hash'];
					$ced_level = $response['level'];
					$i         = 1;
					for ( $i = 1;$i <= $ced_level;$i++ ) {
						$ced_hash = base64_decode( $ced_hash );
					}
				}
				$ced_cwsm_product_addon_license = json_decode( $ced_hash, true );
			}
		}

		/*
		function checkForPluginUpdate() {
			$referer = $_SERVER['HTTP_HOST'];
			$postdata = http_build_query(array('action' => 'update', 'referer'=>$referer));
			require_once CED_CWSM_PRODUCT_ADDON_PLUGIN_DIR_PATH.'plugin-updates/plugin-update-checker.php';
			$PluginUpdateChecker = PucFactory::buildUpdateChecker(
				"http://demo.cedcommerce.com/woocommerce/update_notifications/wholesale-market-product-addon/update.php?$postdata",
				CED_CWSM_PRODUCT_ADDON_PLUGIN_DIR_PATH.'wholesale-market-product-addon.php'
			);
		}*/


		public function ced_cwsm_product_addon_validate_licensce() {

			require_once CED_CWMAD_PRODUCT_ADDON_PLUGIN_DIR_PATH . 'addons/license/licensing_setting.php';
			$ced_cwsm_product_addon_licence = Ced_CWSM_Product_Addon_Licence_Settings::getInstance();
			$response                       = $ced_cwsm_product_addon_licence->ced_cwsm_product_addon_validate_licensce_callback();
			echo json_encode( $response );
			wp_die();
		}

		/**
		 * This function to load text-domain of the plugin'.
		 *
		 * @name ced_cwsm_load_textdomain()
		 *
		 * @link: http://www.cedcommerce.com/
		 */
		public function ced_cwsm_load_textdomain() {
			$domain = 'wholesale-market';
			$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
			load_textdomain( $domain, CED_CWSM_PRODUCT_ADDON_PLUGIN_DIR_PATH . 'language/' . $domain . '-' . $locale . '.mo' );
			load_plugin_textdomain( 'wholesale-market', false, plugin_basename( dirname( __FILE__ ) ) . '/language' );
		}

		public function ced_cwsm_plugin_show_settings_link( $links ) {
			$settings_link = '<a href="admin.php?page=wholesale_market&tab=ced_cwsm_product_addon">Go To Settings</a>';
			array_unshift( $links, $settings_link );
			return $links;
		}

		public function ced_cwsm_add_product_addon_tab( $tabs ) {
			$tabs['ced_cwsm_product_addon'] = __( 'Product Addon', 'wholesale-market' );
			return $tabs;
		}

		public function initiate_ced_cwsm_product_addon_settings() {
			new CED_CWSM_Product_Addon_Settings();
		}

	}
}
$product_addon = new CED_CWSM_Product_Addon();
/*
if ( in_array('wholesale-market/wholesale-market.php', apply_filters('active_plugins', get_option('active_plugins')))) {
	new CED_CWSM_Product_Addon();
}
else {
	//Wholesale-Market is not activated
	//uninstall your plugin
	add_action( 'admin_init', 'ced_cwsm_product_addon_activation_failure' );
	function ced_cwsm_product_addon_activation_failure() {
		deactivate_plugins( plugin_basename( __FILE__ ) );
	}

	add_action( 'admin_notices', 'ced_cwsm_product_addon_activation_failure_admin_notice' );
	function ced_cwsm_product_addon_activation_failure_admin_notice() {
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php _e( 'Please Install Wholesale-Market First.', 'wholesale-market-user-addon' ); ?></p>
		</div>
		<style>div#message.updated{ display: none; }</style>
		<?php
	}
}*/

