<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Core class wrapping all plugin functionality.
 *
 * @class    CED_CWSM_Core_Class
 * @version  2.0.8
 * @package Class
 */
class CED_CWSM_Core_Class {

	public function __construct() {
		define( 'CED_CWSM_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
		define( 'CED_CWSM_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );

		$this->ced_cwsm_include_required_files();
		$this->ced_cwsm_make_objects_of_classes_used();
		$this->ced_cwsm_add_hooks_and_action();
	}

	/**
	 * This function includes neccessary files required'.
	 *
	 * @name cwsm_include_required_files()
	 *
	 * @link: http://www.cedcommerce.com/
	 */
	public function ced_cwsm_include_required_files() {

		require_once CED_CWMAD_PRODUCT_ADDON_PLUGIN_DIR_PATH . 'core/adminSettingsCore/class-cwsm-admin-settings.php';
		require_once CED_CWMAD_PRODUCT_ADDON_PLUGIN_DIR_PATH . 'core/adminSide/class-cwsm-basic-settings.php';
		require_once CED_CWMAD_PRODUCT_ADDON_PLUGIN_DIR_PATH . 'core/adminSide/class-cwsm-productListingPage-customization.php';
		require_once CED_CWMAD_PRODUCT_ADDON_PLUGIN_DIR_PATH . 'core/adminSide/class-cwsm-add-product-meta-fields.php';
		require_once CED_CWMAD_PRODUCT_ADDON_PLUGIN_DIR_PATH . 'core/frontEnd/class-cwsm-show-wholesale-price-on-frontEnd.php';
		require_once CED_CWMAD_PRODUCT_ADDON_PLUGIN_DIR_PATH . 'core/frontEnd/class-cwsm-manage-wholesale-price.php';
		require_once CED_CWMAD_PRODUCT_ADDON_PLUGIN_DIR_PATH . 'addons/min-product-qty-module/class-cwsm-min-product-qty-module.php';
		require_once CED_CWMAD_PRODUCT_ADDON_PLUGIN_DIR_PATH . 'addons/admin-suggestions/class-cwsm-admin-suggestions.php';

		/**
		*Core
		 *
		* @since  1.0.8
		*/
		require_once CED_CWMAD_PRODUCT_ADDON_PLUGIN_DIR_PATH . 'core/frontEnd/class-cwsm-custom-cart-page-message.php';
		require_once CED_CWMAD_PRODUCT_ADDON_PLUGIN_DIR_PATH . 'addons/csv-import-export/class-cwsm-csv-import-export.php';
		require_once CED_CWMAD_PRODUCT_ADDON_PLUGIN_DIR_PATH . 'addons/wholesale-request/wholesale-request-addon.php';
		require_once CED_CWMAD_PRODUCT_ADDON_PLUGIN_DIR_PATH . 'addons/wholesale-advertisement/wholesale-advertisement-addon.php';

		/**
		*Core
		 *
		* @since  1.0.12
		*/
		require_once CED_CWMAD_PRODUCT_ADDON_PLUGIN_DIR_PATH . 'addons/min-checkout-price-module/class-cwsm-min-checkout-price-module.php';

		/**
		*Core
		 *
		* @since  2.0.0
		*/
		require_once CED_CWMAD_PRODUCT_ADDON_PLUGIN_DIR_PATH . 'core/frontEnd/class-cwsm-manage-add-to-cart-button.php';

		/**
		*Core
		 *
		* @since  2.0.3
		*/
		require_once CED_CWMAD_PRODUCT_ADDON_PLUGIN_DIR_PATH . 'addons/sticky-admin-suggestions/class-cwsm-sticky-send-suggestions.php';
	}

	/**
	 * This function to extra links to plugin listing page'.
	 *
	 * @name ced_cwsm_add_plugin_row_meta()
	 *
	 * @link: http://www.cedcommerce.com/
	 */
	public function ced_cwsm_add_plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
		if ( CED_CWSM_PLUGIN_BASE_FILE == $plugin_file ) {
			$plugin_meta[] = '<a href="http://demo.cedcommerce.com/woocommerce/wholesale/wp-admin" target="_blank">' . __( 'Demo : Backend', 'caf_txt_domain' ) . '</a>';
			$plugin_meta[] = '<a href="http://demo.cedcommerce.com/woocommerce/wholesale/" target="_blank">' . __( 'Demo: Frontend', 'caf_txt_domain' ) . '</a>';
			$plugin_meta[] = '<a href="http://demo.cedcommerce.com/woocommerce/wholesale/doc/index.html" target="_blank">' . __( 'Plugin Documentation', 'caf_txt_domain' ) . '</a>';
			$plugin_meta[] = '<a href="http://cedcommerce.com/woocommerce-extensions/" target="_blank">' . __( 'More Plugins By CedCommerce', 'caf_txt_domain' ) . '</a>';
		}
		return $plugin_meta;
	}

		/**
		 * This function makes object of various classes included in above method'.
		 *
		 * @name cwsm_make_objects_of_classes_used()
		 *
		 * @link: http://www.cedcommerce.com/
		 */
	public function ced_cwsm_make_objects_of_classes_used() {
		$this->ced_cwsm_add_product_meta_fields_OBJ = CED_CWSM_Add_Product_Meta_Fields::getInstance();

		$this->ced_cwsm_show_wholesale_price_on_frontEnd_OBJ = CED_CWSM_Show_Wholesale_Price_On_FrontEnd::getInstance();

		$this->ced_cwsm_manage_wholesale_price_OBJ = CED_CWSM_Manage_Wholesale_Price::getInstance();

	}

		/**
		 * This function hooks different events required by the plugin and used appropriate function to that'.
		 *
		 * @name cwsm_add_hooks_and_action()
		 *
		 * @link: http://www.cedcommerce.com/
		 */
	public function ced_cwsm_add_hooks_and_action() {
		/*
		* for text-domain
		*/
		add_action( 'plugins_loaded', array( $this, 'ced_cwsm_load_textdomain' ) );
		global $globalCWSM;

		// filter to add things on plugin listing page
		add_filter( 'plugin_row_meta', array( $this, 'ced_cwsm_add_plugin_row_meta' ), 10, 4 );

		if ( ! $globalCWSM->is_CWSM_plugin_active() ) {
			return;
		}

		// adding wholesale field for simple product
		add_action( 'woocommerce_product_options_general_product_data', array( $this->ced_cwsm_add_product_meta_fields_OBJ, 'ced_cwsm_create_simple_product_meta_fields' ) );
		// saving wholesale field for simple product
		add_action( 'woocommerce_process_product_meta_simple', array( $this->ced_cwsm_add_product_meta_fields_OBJ, 'ced_cwsm_save_simple_product_meta_fields' ), 10, 1 );

		// adding wholesale field for variable product
		add_action( 'woocommerce_product_after_variable_attributes', array( $this->ced_cwsm_add_product_meta_fields_OBJ, 'ced_cwsm_create_variation_product_meta_fields' ), 10, 3 );
		// saving wholesale field for variable product
		add_action( 'woocommerce_save_product_variation', array( $this->ced_cwsm_add_product_meta_fields_OBJ, 'ced_cwsm_save_variation_product_meta_fields' ), 10, 2 );
		add_action( 'woocommerce_process_product_meta_variable', array( $this->ced_cwsm_add_product_meta_fields_OBJ, 'ced_cwsm_save_variation_product_meta_fields' ), 10, 1 );

		// showing wholesale price in shop-page
		add_action( 'woocommerce_after_shop_loop_item_title', array( $this->ced_cwsm_show_wholesale_price_on_frontEnd_OBJ, 'ced_cwsm_show_wholesale_price_shop_page' ), 11 );
		// showing wholesale price in product single page
		add_action( 'woocommerce_single_product_summary', array( $this->ced_cwsm_show_wholesale_price_on_frontEnd_OBJ, 'ced_cwsm_show_wholesale_price_single_page' ), 11 );
		// showing wholesale price on changing variation on variable product single page
		add_filter( 'woocommerce_available_variation', array( $this->ced_cwsm_show_wholesale_price_on_frontEnd_OBJ, 'ced_cwsm_show_variation_wholesale_price' ), 10, 3 );

		// manage wholesale price
		add_action( 'woocommerce_before_calculate_totals', array( $this->ced_cwsm_manage_wholesale_price_OBJ, 'ced_cwsm_mange_wholesale_price' ) );

		// enqueue style for custom msg on cart page
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_woocommerce_setting_section_styles_and_scripts' ), 100 );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_common_styles_and_scripts' ), 100 );
	}

		/**
		 * Enqueues the script files.
		 *
		 * @link http://cedcommerce.com/
		 */
	public function enqueue_common_styles_and_scripts() {
		$screen      = get_current_screen();
		$screen_id   = $screen ? $screen->id : '';
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( $_SERVER['REQUEST_URI'] ) : '';
		if ( strpos( $request_uri, 'page=wholesale_market' ) ) {
			wp_enqueue_style( 'ced_cwsm_setting_panel_css', plugins_url( 'assets/css/setting-panel.css', __FILE__ ), array(), '1.0.0', true );
		} elseif ( in_array( $screen_id, array( 'product', 'edit-product' ) ) ) {
			wp_enqueue_style( 'ced_cwsm_product_panel_css', plugins_url( 'assets/css/product-panel.css', __FILE__ ), array( 'woocommerce_admin_styles', 'woocommerce_admin_menu_styles', array(), '1.0.0', true ), array(), '1.0.0', true );
		}
	}

		/**
		 * Enqueues the script files.
		 *
		 * @link http://cedcommerce.com/
		 */
	public function enqueue_woocommerce_setting_section_styles_and_scripts() {
		if ( isset( $_GET['page'] ) && 'wholesale_market' == $_GET['page'] && ! isset( $_GET['tab'] ) && ! isset( $_GET['section'] ) ) {
			wp_enqueue_script( 'custom_price_txt_script', plugins_url( 'assets/js/custom_price_txt_script.js', __FILE__ ), array( 'jquery' ), '1.0', true );
			wp_enqueue_style( 'ced_cwsm_error_msg_backend', plugins_url( 'assets/css/error_msg_backend.css', __FILE__ ), array(), '1.0.0', true );
		} elseif ( isset( $_GET['page'] ) && 'wholesale_market' == $_GET['page'] && isset( $_GET['tab'] ) && 'ced_cwsm_basic' == $_GET['tab'] && ! isset( $_GET['section'] ) ) {
			wp_enqueue_script( 'custom_price_txt_script', plugins_url( 'assets/js/custom_price_txt_script.js', __FILE__ ), array( 'jquery' ), '1.0', true );
			wp_enqueue_style( 'ced_cwsm_error_msg_backend', plugins_url( 'assets/css/error_msg_backend.css', __FILE__ ), array(), '1.0.0', true );
		} elseif ( isset( $_GET['page'] ) && 'wholesale_market' == $_GET['page'] && isset( $_GET['tab'] ) && 'ced_cwsm_basic' == $_GET['tab'] && isset( $_GET['section'] ) && 'ced_cwsm_general_module' == $_GET['section'] ) {
			wp_enqueue_script( 'custom_price_txt_script', plugins_url( 'assets/js/custom_price_txt_script.js', __FILE__ ), array( 'jquery' ), '1.0', true );
			wp_enqueue_style( 'ced_cwsm_error_msg_backend', plugins_url( 'assets/css/error_msg_backend.css', __FILE__ ), array(), '1.0.0', true );
		}

		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( $_SERVER['REQUEST_URI'] ) : '';
		if ( strpos( $request_uri, 'page=wholesale_market' ) ) {
			/* woocommerce style */
			wp_enqueue_style( 'woocommerce_admin_menu_styles' );
			wp_enqueue_style( 'woocommerce_admin_styles' );
			wp_enqueue_style( 'jquery-ui-style' );
			wp_enqueue_style( 'wp-color-picker' );

			/* woocommerce script */
			wp_enqueue_script( 'iris' );
			wp_enqueue_script( 'woocommerce_admin' );
			wp_enqueue_script( 'wc-enhanced-select' );
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'jquery-ui-autocomplete' );

			$locale  = localeconv();
			$decimal = isset( $locale['decimal_point'] ) ? $locale['decimal_point'] : '.';

			$params = array(
				/* translators: %s: Please enter in decimal format without thousand separators */
				'i18n_decimal_error'               => sprintf( __( 'Please enter in decimal (%s) format without thousand separators.', 'woocommerce' ), $decimal ),
				/* translators: %s: Please enter in monetary decimal format without thousand separators and currency symbols */
				'i18n_mon_decimal_error'           => sprintf( __( 'Please enter in monetary decimal (%s) format without thousand separators and currency symbols.', 'woocommerce' ), wc_get_price_decimal_separator() ),
				'i18n_country_iso_error'           => __( 'Please enter in country code with two capital letters.', 'woocommerce' ),
				'i18_sale_less_than_regular_error' => __( 'Please enter in a value less than the regular price.', 'woocommerce' ),
				'decimal_point'                    => $decimal,
				'mon_decimal_point'                => wc_get_price_decimal_separator(),
				'urls'                             => admin_url(),
				'strings'                          => '',
			);
			wp_localize_script( 'woocommerce_admin', 'woocommerce_admin', $params );
		}
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
		load_textdomain( $domain, CED_CWSM_PLUGIN_DIR_PATH . 'language/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( 'wholesale-market', false, plugin_basename( dirname( __FILE__ ) ) . '/language' );
	}
}

	/**
	 * Creating object for this class.
	 */
	new CED_CWSM_Core_Class();

