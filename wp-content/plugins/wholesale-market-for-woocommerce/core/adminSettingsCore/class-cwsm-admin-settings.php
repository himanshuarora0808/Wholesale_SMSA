<?php
/**
 * WooCommerce Admin Settings Class
 *
 * @package Class
 * @package  WooCommerce/Admin
 * @version  2.0.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Admin_Settings' ) ) {
	require_once ABSPATH . 'wp-content/plugins/woocommerce/includes/admin/class-wc-admin-settings.php';
}

add_action( 'admin_menu', 'my_plugin_menu' );
/**
 * This function is used to add Wholesale Market menu in the plugin.
 *
 * @name my_plugin_menu()

 * @link  http://www.cedcommerce.com/
 */
function my_plugin_menu() {
	add_menu_page( __( 'Wholesale Market', 'wholesale-market' ), __( 'Wholesale Market', 'wholesale-market' ), 'manage_options', 'wholesale_market', 'my_plugin_options', null, '55.5' );
}
/**
 * This function is used to add instance of class.
 *
 * @name my_plugin_options()

 * @link  http://www.cedcommerce.com/
 */
function my_plugin_options() {
	CED_CWSM_Admin_Settings::output();
}

class CED_CWSM_Admin_Settings {

	/**
	 * Setting pages.
	 *
	 * @var array
	 */
	private static $settings = array();

	/**
	 * Error messages.
	 *
	 * @var array
	 */
	private static $errors = array();

	/**
	 * Update messages.
	 *
	 * @var array
	 */
	private static $messages = array();

	/**
	 * Settings page.
	 *
	 * Handles the display of the main woocommerce settings page in admin.
	 */
	public static function output() {
		if ( ! session_id() ) {
			session_start();
		}
		$http_host   = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( $_SERVER['HTTP_HOST'] ) : '';
		$REQUEST_URI = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( $_SERVER['REQUEST_URI'] ) : '';

		if ( ! isset( $_SESSION['ced_hpul_hide_email'] ) ) :
			$actual_link = 'http://' . $http_host . $REQUEST_URI;
			$urlvars     = parse_url( $actual_link );
			$url_params  = $urlvars['query'];
			?>
		<div class="ced_hpul_email_image">
			<div class="ced_hpul_email_main_content">
				<div class="ced_hpul_cross_image">
					<a class="button-primary ced_hpul_cross_image" href="?<?php echo esc_attr( $url_params ); ?>&ced_hpul_close=true">x</a>
				</div>
				<div class="ced-recom">
					<h4>Cedcommerce recommendations for you </h4>
				</div>
				<div class="wramvp_main_content__col">
					<p> 
						Looking forward to evolve your eCommerce?
						<a href="http://bit.ly/2LB1lZV" target="_blank">Sell on the TOP Marketplaces</a>
					</p>
					<div class="wramvp_img_banner">
						<a target="_blank" href="http://bit.ly/2LB1lZV"><img alt="market-place" src="<?php echo esc_url( plugins_url() . '/wholesale-market-for-woocommerce/assets/images/market-place-2.jpg' ); ?>"></a> 
					</div>
				</div>
				<div class="wramvp_main_content__col">
					<p> 
						Leverage auto-syncing centralized order management and more with our
						<a href="http://bit.ly/2LB71TJ" target="_blank">Integration Extensions</a> 
					</p>
					<div class="wramvp_img_banner">
						<a target="_blank" href="http://bit.ly/2LB71TJ"><img alt="market-place" src="<?php echo esc_url( plugins_url() . '/wholesale-market-for-woocommerce/assets/images/market-place.jpg' ); ?>"></a> 
					</div>
				</div>
				<div class="clear"></div>
				<div class="wramvp-support">
					<ul>
						<li>
							<span class="wramvp-support__left">Contact Us :-</span>
							<span class="wramvp-support__right"><a href="mailto:support@cedcommerce.com"> support@cedcommerce.com </a></span>
						</li>
						<li>
							<span class="wramvp-support__left">Get expert's advice :-</span>
							<span class="wramvp-support__right"><a href="https://join.skype.com/bovbEZQAR4DC"> Join Us</a></span>
						</li>
					</ul>
				</div>
			</div>
		</div>
	<?php endif;
		global $ced_cwsm_current_section, $ced_cwsm_current_tab, $ced_cwsm_page;
		$ced_cwsm_page = 'wholesale_market';
		// Send Suggestion Sticky Header
		do_action( 'ced_cwsm_send_suggetion_sticky_form' );
		// Get current tab/section
		$ced_cwsm_current_tab     = empty( $_GET['tab'] ) ? 'ced_cwsm_basic' : sanitize_title( $_GET['tab'] );
		$ced_cwsm_current_section = empty( $_REQUEST['section'] ) ? '' : sanitize_title( $_REQUEST['section'] );
		// Get tabs for the settings page
		$tabs                   = array();
		$tabs['ced_cwsm_basic'] = __( 'Basic Features', 'wholesale-market' );
		$tabs                   = apply_filters( 'ced_cwsm_settings_tabs_array', $tabs );

		do_action( 'after_ced_cwsm_admin_settings_initiated' );

		include 'html-setting-tabbed-header.php';
	}

	/**
	 * This function is used to display the settings tab-Wholesale Market of the plugin.
	 *
	 * @name get_sections()

	 * @link  http://www.cedcommerce.com/
	 */
	public static function get_sections() {
		global $ced_cwsm_current_tab;
		return apply_filters( 'ced_cwsm_sections_' . $ced_cwsm_current_tab, array() );
	}

	/**
	 * This function is used to display the settings on the settings tab of the plugin.
	 *
	 * @name output_sections()

	 * @link  http://www.cedcommerce.com/
	 */
	public static function output_sections() {
		global $ced_cwsm_current_section, $ced_cwsm_current_tab, $ced_cwsm_page;

		$sections = self::get_sections();

		if ( empty( $sections ) || 0 === count( $sections ) ) {
			return;
		}

		if ( '' == $ced_cwsm_current_section ) {
			foreach ( $sections as $key => $value ) {
				$ced_cwsm_current_section = $key;
				break;
			}
		}

		echo '<ul class="subsubsub">';

		$array_keys = array_keys( $sections );

		foreach ( $sections as $id => $label ) {
			echo '<li><a href="' . esc_url( admin_url( 'admin.php?page=' . $ced_cwsm_page . '&tab=' . $ced_cwsm_current_tab . '&section=' . sanitize_title( $id ) ) ) . '" class="' . ( $ced_cwsm_current_section == $id ? 'current' : '' ) . '">' . esc_attr( $label ) . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
		}
		echo '</ul><br class="clear" />';
	}

	/**
	 * This function is used to record the settings made on the settings tab of the plugin.
	 *
	 * @name get_sttings()

	 * @link  http://www.cedcommerce.com/
	 */
	public static function get_settings() {
		global $ced_cwsm_current_tab;
		return apply_filters( 'ced_cwsm_settings_' . $ced_cwsm_current_tab, array() );
	}

	/**
	 * This function is used to call the changes made on the settings tab of the plugin.
	 *
	 * @name output_settings()

	 * @link  http://www.cedcommerce.com/
	 */
	public static function output_settings() {
		$settings = self::get_settings();
		WC_Admin_Settings::output_fields( $settings );
	}

	/**
	 * This function is used to save the settings made on the settings tab of the plugin.
	 *
	 * @name save()

	 * @link  http://www.cedcommerce.com/
	 */
	public static function save() {
		$allow = true;
		$allow = apply_filters( 'ced_cwsm_allow_submit', $allow );
		if ( $allow ) {
			$settings = self::get_settings();
			WC_Admin_Settings::save_fields( $settings );
			self::add_message( __( 'Your settings have been saved.', 'wholesale-market' ) );
		}
		if ( false == $allow ) {
			self::add_message( __( 'Your settings have been saved.', 'wholesale-market' ) );
		}
	}

	/**
	 * Add a message.
	 *
	 * @param string $text
	 */
	public static function add_message( $text ) {
		self::$messages[] = $text;
	}

	/**
	 * Add an error.
	 *
	 * @param string $text
	 */
	public static function add_error( $text ) {
		self::$errors[] = $text;
	}

	/**
	 * Output messages + errors.
	 *
	 * @return string
	 */
	public static function show_messages() {
		if ( count( self::$errors ) > 0 ) {
			foreach ( self::$errors as $error ) {
				echo '<div id="message" class="error inline"><p><strong>' . esc_html( $error ) . '</strong></p></div>';
			}
		} elseif ( count( self::$messages ) > 0 ) {
			foreach ( self::$messages as $message ) {
				echo '<div id="message" class="updated inline"><p><strong>' . esc_html( $message ) . '</strong></p></div>';
			}
		}
	}

}
?>
