<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Adds minimum product quantity condition to core plugin using hooks and filters of that.
 *
 * @class    CED_CWSM_Basic_Settings
 * @version  2.0.8
 * @package  wholesale-market/min-product-qty-module
 * @package Class
 */
class CED_CWSM_Basic_Settings {

	public $_sectionID = 'ced_cwsm_general_module';
	public $_sectionName;
	/**
	 * Functionalities initializes over here
	 *
	 * @return void
	 */
	public function __construct() {
		$this->_sectionName = __( 'General', 'wholesale-market' );
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
		if ( 'ced_cwsm_basic' == $ced_cwsm_current_tab ) {
			add_filter( 'ced_cwsm_sections_' . $ced_cwsm_current_tab, array( $this, 'ced_cwsm_add_sections' ), 10, 1 );
			add_filter( 'ced_cwsm_settings_' . $ced_cwsm_current_tab, array( $this, 'ced_cwsm_add_settings' ), 10, 1 );
		}
	}

	public function ced_cwsm_add_sections( $sections ) {
		$sections[ $this->_sectionID ] = $this->_sectionName;
		$sections                      = apply_filters( 'ced_cwsm_append_basic_sections', $sections );
		return $sections;
	}

	/**
	 * This function is used to add the settings tab in the plugin
	 *
	 * @name ced_cwsm_add_settings()
	 *
	 * @param array $settings
	 * @return array $settings
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_add_settings( $settings ) {
		global $ced_cwsm_current_section;
		if ( $ced_cwsm_current_section == $this->_sectionID ) {

			$settings = apply_filters(
				'ced_cwsm_alter_' . $this->_sectionID . '_settings',
				array(
					'top-label'                  => array(
						'name' => __( 'General Configurations', 'wholesale-market' ),
						'type' => 'title',
						'desc' => __( 'General configurations regarding the plugins are listed below.', 'wholesale-market' ),
						'id'   => 'wc_ced_ws_setting_tab_section_title-1',
					),
					'enable-disable-plugin'      => array(
						'title'   => __( 'Enable', 'wholesale-market' ),
						'desc'    => __( 'Enable Wholesale Market Features', 'wholesale-market' ),
						'id'      => 'ced_cwsm_enable_wholesale_market',
						'type'    => 'checkbox',
						'default' => 'yes',
					),
					'who-can-see'                => array(
						'title'    => __( 'Who Can See Wholesale Price', 'wholesale-market' ),
						'desc'     => __( 'This controls what kind of users will able to see wholesale-price on the frontend of the store.', 'wholesale-market' ),
						'id'       => 'ced_cwsm_radio_whoCanSee',
						'default'  => '_cwsm_radio_showAll',
						'type'     => 'radio',
						'options'  => array(
							'_cwsm_radio_showAll'    => __( 'Display Wholesale-Price To All Users', 'wholesale-market' ),
							'_cwsm_radio_showWSuser' => __( 'Display Wholesale-Price To Wholesale-Customer Only', 'wholesale-market' ),
						),
						'desc_tip' => true,
						'autoload' => false,
					),
					'where-to-show'              => array(
						'title'    => __( 'Show Wholesale-Price On Product Listing Page', 'wholesale-market' ),
						'desc'     => __( 'Show In Price Column', 'wholesale-market' ),
						'id'       => 'ced_cwsm_show_in_price_column',
						'default'  => 'no',
						'type'     => 'checkbox',
						'autoload' => false,
						'desc_tip' => __( 'This option will show wholesale price in price-column of product-listing page', 'wholesale-market' ),
					),
					'keep-plugin-setting'        => array(
						'title'    => __( 'Keep Plugin Settings On Deactivation', 'wholesale-market' ),
						'desc'     => __( 'Save Plugin Setting Even After Deactivation', 'wholesale-market' ),
						'id'       => 'ced_cwsm_keep_plugin_setting',
						'default'  => 'no',
						'type'     => 'checkbox',
						'autoload' => false,
						'desc_tip' => __( 'If you check this your plugin setting will be saved and used later on while activating plugin after deactivation. Otherwise your setting will be lost and you have to fill settings again.', 'wholesale-market' ),
					),
					'keep-products-meta-fields'  => array(
						'title'    => __( 'Keep Products Meta Fields Added By Plugin On Deactivation', 'wholesale-market' ),
						'desc'     => __( 'Save Products Meta-Fields Even After Deactivation', 'wholesale-market' ),
						'id'       => 'ced_cwsm_keep_products_meta_fields',
						'default'  => 'no',
						'type'     => 'checkbox',
						'autoload' => false,
						'desc_tip' => __( 'If you check this your products meta fields added by the plugin will be saved and used later on while activating plugin after deactivation. Otherwise your setting will be lost and you have to fill settings again.', 'wholesale-market' ),
					),
					'section_end-1'              => array(
						'type' => 'sectionend',
						'id'   => 'wc_ced_ws_setting_tab_section_end-1',
					),
					'info-text'                  => array(
						'name' => __( 'Customize Wholesale-Price Text Section', 'wholesale-market' ),
						'type' => 'title',
						'desc' => __( '"Custom Wholesale-Price Text On Shop Page" and "Custom Wholesale-Price Text On Product Single Page" will be used in only when Minimum Product To Buy is set. Otherwise "Default Wholesale-Price Text" will be used.', 'wholesale-market' ),
						'id'   => 'ced_cwsm_custm_msg_header',
					),
					'default-wm-price-txt'       => array(
						'title'    => __( 'Default Wholesale-Price Text', 'wholesale-market' ),
						'desc'     => __( 'Don\'t remove {*wm_price} from text-area. It is used to read wholesale-price. You are however free to add text before and after {*wm_price}', 'wholesale-market' ),
						'id'       => 'ced_cwsm_default_wm_price_txt',
						'default'  => __( 'Wholesale Price : {*wm_price}', 'wholesale-market' ),
						'type'     => 'textarea',
						'css'      => 'width:450px; height: 70px;',
						'autoload' => false,
					),
					'customize-shop-page-txt'    => array(
						'title'    => __( 'Custom Wholesale-Price Text On Shop Page', 'wholesale-market' ),
						'desc'     => __( 'Use {*wm_price} for wholesale-price and {*wm_min_qty} for product minimum quantity to buy.', 'wholesale-market' ),
						'id'       => 'ced_cwsm_custm_shop_txt',
						'default'  => __( 'Wholesale Price : {*wm_price}', 'wholesale-market' ),
						'type'     => 'textarea',
						'css'      => 'width:450px; height: 70px;',
						'autoload' => false,
					),
					'customize-product-page-txt' => array(
						'title'    => __( 'Custom Wholesale-Price Text On Product Single Page', 'wholesale-market' ),
						'desc'     => __( 'Use {*wm_price} for wholesale-price and {*wm_min_qty} for product minimum quantity to buy.', 'wholesale-market' ),
						'id'       => 'ced_cwsm_custm_product_txt',
						'default'  => __( 'Wholesale Price : {*wm_price}', 'wholesale-market' ),
						'type'     => 'textarea',
						'css'      => 'width:450px; height: 70px;',
						'autoload' => false,
					),
					'section_end-2'              => array(
						'type' => 'sectionend',
						'id'   => 'wc_ced_ws_setting_tab_section_end-2',
					),
				)
			);
		} else {
			$settings = apply_filters( 'ced_cwsm_append_basic_settings', $settings, $ced_cwsm_current_section );
		}
		return $settings;
	}
}?>
<?php
add_action( 'after_ced_cwsm_admin_settings_initiated', 'initiate_ced_cwsm_basic_settings' );

function initiate_ced_cwsm_basic_settings() {
	// Creating instance of the class
	new CED_CWSM_Basic_Settings();
}
