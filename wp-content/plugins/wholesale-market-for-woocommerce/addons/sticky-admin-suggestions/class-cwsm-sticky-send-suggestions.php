<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Adds wholesale-price in Price column on product listing pages.
 *
 * @class    CED_CWSM_Sticky_Send_Suggestions
 * @version  2.0.8
 * @package  wholesale-market/adminSide
 * @package Class
 */
class CED_CWSM_Sticky_Send_Suggestions {
	/**
	 * This is construct of class
	 *
	 * @link plugins@cedcommerce.com
	 */
	public function __construct() {
		if ( is_admin() ) {
			$this->add_hooks_and_filters();
		}
	}

	/**
	 * This function adds hooks and filter.
	 *
	 * @name add_hooks_and_filters()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function add_hooks_and_filters() {
		add_action( 'ced_cwsm_send_suggetion_sticky_form', array( $this, 'ced_cwsm_send_suggetion_sticky_form' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'ced_cwsm_sticky_suggestions_admin_enqueue_scripts' ) );

	}

	/**
	 * This function is used to enqueque the scripts.
	 *
	 * @name ced_cwsm_sticky_suggestions_admin_enqueue_scripts()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_sticky_suggestions_admin_enqueue_scripts() {
		$req_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( $_SERVER['REQUEST_URI'] ) : '';
		if ( ! strpos( $req_uri, 'page=wholesale_market&tab=ced_cwsm_basic&section=ced_cwsm_admin_suggestions_module' ) && strpos( $req_uri, 'page=wholesale_market' ) ) {
			wp_enqueue_script( 'ced_cwsm_send_mail', plugin_dir_url( __FILE__ ) . 'js/ced_cwsm_send_mail.js', array( 'jquery' ), '1.0', true );
			wp_localize_script(
				'ced_cwsm_send_mail',
				'ced_cwsm_send_mail_js_ajax',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
				)
			);
			wp_enqueue_style( 'ced_cwsm_send_mail_css', plugin_dir_url( __FILE__ ) . 'css/ced_cwsm_send_mail.css', array(), '1.0.1', true );
		}
	}

	/**
	 * This function is used to display the suggestions form in settings tab.
	 *
	 * @name ced_cwsm_send_suggetion_sticky_form()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_send_suggetion_sticky_form() {
		$req_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( $_SERVER['REQUEST_URI'] ) : '';
		if ( ! strpos( $req_uri, 'page=wholesale_market&tab=ced_cwsm_basic&section=ced_cwsm_admin_suggestions_module' ) && strpos( $req_uri, 'page=wholesale_market' ) ) {
			?>
			<h3 class="ced_cwsm_suggestion_sticky_header" id="ced_cwsm_suggestion_div">
				<span>+</span>
				<?php esc_attr_e( 'Send Us Suggestions', 'wholesale-market' ); ?>
			</h3>
			<div class="ced_cwsm_suggestion_wrapper" >
				<div class="updated_msg" id="ced_cwsm_mail_success_msg" style="display:none">
					<p>
						<strong>
							<?php esc_attr_e( 'Your suggestion has been successfully send.', 'wholesale-market' ); ?>
						</strong>
					</p>
				</div>
				<div class="error_msg" id="ced_cwsm_mail_failure_msg" style="display:none">
					<p>
						<strong>
							<?php esc_attr_e( 'Some error occured. Please try again.', 'wholesale-market' ); ?>
						</strong>
					</p>
				</div>
				<div class="error_msg" id="ced_cwsm_mail_empty_msg" style="display:none">
					<p>
						<strong>
							<?php esc_attr_e( 'Both fields are required. Please try again. ', 'wholesale-market' ); ?>
						</strong>
					</p>
				</div>

				<div class="ced_cwsm_mail_msg">
					<p>
						<?php esc_attr_e( 'You can send your suggestion and query regarding Wholesale-Market here.', 'wholesale-market' ); ?>
					</p>
				</div>
				<div>
					<p>
						<label><?php esc_attr_e( 'Enter suggestion title here', 'wholesale-market' ); ?></label>
						<input type="text" id="ced_cwsm_suggestion_title">
					</p>
					<p>
						<label><?php esc_attr_e( 'Enter suggestion detail here', 'wholesale-market' ); ?></label>
						<textarea id="ced_cwsm_suggestion_detail" rows="10" cols="90"></textarea>
					</p>
					<p>	
						<label></label>
						<button class="button-primary" id="ced_cwsm_send_mail"><?php esc_attr_e( 'Send Suggestion', 'wholesale-market' ); ?></button><img id="ced_cwsm_send_loading" style="display: none;" src="<?php echo esc_url( CED_CWSM_PLUGIN_DIR_URL . 'assets/images/ajax-loader.gif' ); ?>">
					</p>
				</div>
			</div> 

			<?php
		}
	}
}
// Create instance of class
new CED_CWSM_Sticky_Send_Suggestions();
?>
