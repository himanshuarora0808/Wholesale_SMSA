<?php
/**
 * Exit if accessed directly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Wholesale_User_Register_Addon' ) ) {
	/**
	 * This is class for add feature of register user as wholesale user .
	 *
	 * @name    Wholesale_User_Register_Addon
	 * @package Class
	 */

	class Wholesale_User_Register_Addon {

		/**
		 * This is construct of class
		 *
		 * @link http://www.cedcommerce.com/
		 */
		public function __construct() {
			add_action( 'wp_enqueue_scripts', array( $this, 'ced_wura_checkout_register_script' ) );
			add_action( 'woocommerce_account_dashboard', array( $this, 'ced_wholesale_dashboard_request' ) );

			// add_action( 'woocommerce_account_dashboard', array($this,'ced_wholesale_show_msg_to_paynow' ));

			add_action( 'wp_ajax_nopriv_ced_wholesale_request_send', array( $this, 'ced_wholesale_request_send_callback' ) );
			add_action( 'wp_ajax_ced_wholesale_request_send', array( $this, 'ced_wholesale_request_send_callback' ) );
			add_shortcode( 'wholesale_request', array( $this, 'ced_wholesale_dashboard_request' ) );

			add_filter( 'ced_cwsm_append_basic_sections', array( $this, 'ced_cwsm_add_request_addon_basic_section' ), 10, 1 );
			add_filter( 'ced_cwsm_append_basic_settings', array( $this, 'ced_cwsm_add_request_addon_basic_settings' ), 10, 2 );
		}

		/**
		 * Function to append request role section
		 *
		 * @link http://www.cedcommerce.com/
		 */
		public function ced_cwsm_add_request_addon_basic_section( $sections ) {
			$sections ['ced_wmra_role_request_section'] = __( 'Role Management', 'wholesale-market' );
			return $sections;
		}

		/**
		 * Function to add a setting page to the Role request section
		 *
		 * @link http://www.cedcommerce.com/
		 */
		public function ced_cwsm_add_request_addon_basic_settings( $settingReceived, $current_section ) {
			if ( 'ced_wmra_role_request_section' == $current_section ) {
				$settings = array(
					'request_addon_section_title'          => array(
						'name' => __( 'Role Management Settings', 'wholesale-market' ),
						'type' => 'title',
						'desc' => '',
						'id'   => 'ced_cwsm_request_addon_section_title',
					),
					'ced_cwsm_request_role_addon_functionality' => array(
						'title'   => __( 'Enable the Request Role Widget', 'wholesale-market' ),
						'desc'    => __( 'Enable it for creating a role request widget (You can activate widget by Appearance > Widgets > Wholesale Request widget )', 'wholesale-market' ),
						'id'      => 'ced_cwsm_request_role_addon_functionality',
						'type'    => 'checkbox',
						'default' => 'no',
					),
					'ced_cwsm_request_role_myaccount_page' => array(
						'title'   => __( 'Enable the Request Role on My Account Page', 'wholesale-market' ),
						'desc'    => __( 'Enable it for allowing user to request role from My Account Page', 'wholesale-market' ),
						'id'      => 'ced_cwsm_request_role_myaccount_page',
						'type'    => 'checkbox',
						'default' => 'no',
					),
					'ced_cwsm_request_role_addon_directly' => array(
						'title'   => __( 'Assigning requested role directly', 'wholesale-market' ),
						'desc'    => __( 'Enable it for assigning the requested role directly', 'wholesale-market' ),
						'id'      => 'ced_cwsm_request_role_addon_directly',
						'type'    => 'checkbox',
						'default' => 'no',
					),
					'request_addon_section_end'            => array(
						'type' => 'sectionend',
						'id'   => 'ced_cwsm_request_addon__section_end',
					),
				);
				return $settings;
			}
			return $settingReceived;
		}

		public function ced_wholesale_request_bulkedit_save( $product ) {
			if ( isset( $_REQUEST['ced_cwsm_wholesale_price'] ) ) {
				if ( WC()->version < '3.0.0' ) {
					$id = $product->id;
				} else {
					$id = $product->get_id();
				}
				$price = isset( $_REQUEST['ced_cwsm_wholesale_price'] ) ? sanitize_text_field( $_REQUEST['ced_cwsm_wholesale_price'] ) : '';
				update_post_meta( $id, 'ced_cwsm_wholesale_price', $price );
			}

			if ( isset( $_REQUEST['ced_cwsm_min_qty_to_buy'] ) ) {
				if ( WC()->version < '3.0.0' ) {
					$id = $product->id;
				} else {
					$id = $product->get_id();
				}
				$qty = isset( $_REQUEST['ced_cwsm_min_qty_to_buy'] ) ? sanitize_text_field( $_REQUEST['ced_cwsm_min_qty_to_buy'] ) : '';
				update_post_meta( $id, 'ced_cwsm_min_qty_to_buy', $qty );
			}
		}

		public function ced_wholesale_request_bulkedit() {
			?>
			<label class="alignleft stock_qty_field">
				<span class="title"><?php esc_attr_e( 'Wholesale Price', 'wholesale-market' ); ?></span>
				<span class="input-text-wrap">
					<input type="text" placeholder="" value="" id="ced_cwsm_wholesale_price" name="ced_cwsm_wholesale_price" style="" class="short wc_input_price">
				</span>
			</label>
			<?php
			$min_qty = get_option( 'ced_cwsm_enable_minQty', false );
			if ( 1 == $min_qty ) {
				?>
				<label class="alignleft stock_qty_field">
					<span class="title"><?php esc_attr_e( 'Wholesale Quantity', 'wholesale-market' ); ?></span>
					<span class="input-text-wrap">
						<input type="number" step="1" placeholder="" value="0" id="ced_cwsm_min_qty_to_buy" name="ced_cwsm_min_qty_to_buy" style="" class="short wc_input_stock">
					</span>
				</label>
				<?php
			}
		}

		/**
		 * Function to manage the role request
		 *
		 * @link http://www.cedcommerce.com/
		 */
		public function ced_wholesale_request_send_callback() {
			$check_ajax = wp_verify_nonce( 'ced-register-ajax-seurity-string', 'ajax_nonce' );
			if ( $check_ajax ) {
				if ( isset( $_POST['wholesale_request'] ) && ! empty( $_POST['wholesale_request'] ) ) {
					$current_user_id = get_current_user_id();
					if ( $current_user_id > 0 ) {
						$select_data = isset( $_POST['subscription_data'] ) ? sanitize_text_field( $_POST['subscription_data'] ) : '';

						$current_user = wp_get_current_user();
						$user_mail    = $current_user->user_email;
						if ( get_option( 'ced_cwsm_request_role_addon_directly' ) == 'yes' ) {
							$user_data = new WP_User( $current_user_id );
							$user_data->add_role( isset( $_POST['role_required'] ) ? sanitize_text_field( $_POST['role_required'] ) : '' );
						} else {
							update_user_meta( $current_user_id, 'ced_wholesale_request', isset( $_POST['role_required'] ) ? sanitize_text_field( $_POST['role_required'] ) : '' );
						}
						do_action( 'ced_cwsm_handle_more_data', $select_data );
						// send notification mail to user and merchant
						$admin_name           = get_option( 'blogname' );
						$admin_email          = get_option( 'admin_email' );
						$notification_setting = get_option( 'ced_wura_notification', false );
						if ( isset( $notification_setting['name'] ) && ! empty( $notification_setting['name'] ) ) {
							$admin_name = $notification_setting['name'];
						}
						if ( isset( $notification_setting['mail'] ) && ! empty( $notification_setting['mail'] ) ) {
							$admin_email = $notification_setting['mail'];
						}
						$admin_subject = '';
						if ( isset( $notification_setting['admin_subject'] ) ) {
							$admin_subject = $notification_setting['admin_subject'];
						}
						$admin_message = '';
						if ( isset( $notification_setting['admin_message'] ) ) {
							$admin_message = $notification_setting['admin_message'];
						}

						$user_subject = '';
						if ( isset( $notification_setting['user_subject'] ) ) {
							$user_subject = $notification_setting['user_subject'];
						}

						$user_message = '';
						if ( isset( $notification_setting['user_message'] ) ) {
							$user_message = $notification_setting['user_message'];
						}

						// send mail to admin
						$headers = array( 'Content-Type: text/html; charset=UTF-8' );
						$to      = $admin_email;
						$subject = $admin_subject;
						$message = $admin_message;
						wp_mail( $to, $subject, stripslashes( $message ), $headers );

						// send mail to user

						$headers = array( 'Content-Type: text/html; charset=UTF-8', "From: $admin_name <$admin_email>" );
						$to      = $user_mail;
						$subject = $user_subject;
						$message = $user_message;
						wp_mail( $to, $subject, stripslashes( $message ), $headers );
					}
				}
			}
		}

		/**
		 * Function to show request role feature on dashboard page frontend(My Account Page)
		 *
		 * @link http://www.cedcommerce.com/
		 */
		public function ced_wholesale_dashboard_request() {
			$current_user_id   = get_current_user_id();
			$current_user_info = get_userdata( $current_user_id );
			$current_user_role = $current_user_info->roles;
			$user_roles        = get_option( 'ced_cwsm_wholesaleRolesArray' );
			global $globalCWSM;
			if ( get_option( 'ced_cwsm_request_role_myaccount_page' ) == 'yes' ) {
				if ( ! $globalCWSM->isCurrentUserIsWholesaleUser() ) {
					if ( ! get_user_meta( $current_user_id, 'ced_wholesale_request', true ) || get_user_meta( $current_user_id, 'ced_wholesale_request', true ) == 'cancel' ) {
						?>
						<div id="ced_dashboard_success_msg" style="display:none;">
							<span style="color:red !important;"><?php esc_attr_e( 'Role Successfully Requested', 'wholesale-market' ); ?></span>
						</div>
						<?php
						$payment_addon_enable = get_option( 'ced_cwsm_basic_settings_payment_enable' );
						if ( 'on' == $payment_addon_enable ) {
							?>
							<form action="" method="POST">
							<?php } ?>
							<div class="ced_cwsm_request_role">							
								<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide" style="padding-bottom:10px;">
									<select name="ced_cwsm_user_role" id="reg_role1">
										<option value=""><?php esc_attr_e( 'Select Role', 'wholesale-market' ); ?></option>
										<option value="ced_cwsm_wholesale_user"><?php esc_attr_e( 'Wholesale Customer', 'wholesale-market' ); ?></option>
										<?php
										foreach ( $user_roles as $key => $value ) {
											?>
											<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $value['name'] ); ?></option>
											<?php
										}
										?>
									</select>
								</p>
								<?php do_action( 'add_extra_field_on_registering_my_account_role' ); ?>
								<?php
								$payment_addon_enable = get_option( 'ced_cwsm_basic_settings_payment_enable' );
								if ( 'on' == $payment_addon_enable ) {
									?>
									<input type="submit" class="Button button" name="paynow_request" value="<?php esc_attr_e( 'Pay Now', 'wholesale-market' ); ?>" />
								</form>
							<?php } else { ?>
								<a class="ced_wm_wholesale_request1 button" style="padding:10px;" id="ced_wm_wholesale_request1" data-id="<?php echo esc_attr( $current_user_id ); ?>" href="javascript:void(0);"><?php esc_attr_e( 'Click Here', 'wholesale-market' ); ?></a>
							<?php } ?>
							<img src="<?php echo esc_url( CED_CWSM_PLUGIN_DIR_URL . 'assets/images/ajax-loader.gif' ); ?>" style="display:none;" width="30px" height="30px" id="ced_cwsm_ajax_loader1" >
						</div>
						<?php
					}
				}
			}
		}

		/**
		 * Function to include required scripts and css files
		 *
		 * @link http://www.cedcommerce.com/
		 */

		public function ced_wura_checkout_register_script() {
			// Register the script
			wp_register_script( 'ced_wura_wholesale_user_addon_script', CED_WURA_DIR_URL . '/assets/js/wholesale-user-register.js', array( 'jquery' ), '1.0.0', true );

			// Localize the script with new data
			$ced_wura_script = array(
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'select_all' => __( 'Please Select all fields', 'wholesale-market' ),
			);

			wp_localize_script( 'ced_wura_wholesale_user_addon_script', 'ced_wura_var', $ced_wura_script );

			// Enqueued script with localized data.
			wp_enqueue_script( 'ced_wura_wholesale_user_addon_script' );
			wp_enqueue_style( 'ced_wura_wholesale_user_addon_style', CED_WURA_DIR_URL . '/assets/css/wholesale-user-register.css', array(), '1.0.0', true );

			$ajax_nonce     = wp_create_nonce( 'ced-register-ajax-seurity-string' );
			$localize_array = array(
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'ajax_nonce' => $ajax_nonce,
			);
			wp_localize_script( 'ced_wura_wholesale_user_addon_script', 'Ced_register_action_handler', $localize_array );
		}
		public function ced_wholesale_show_msg_to_paynow() {
			$current_user_id   = get_current_user_id();
			$current_user_info = get_userdata( $current_user_id );
			$current_user_role = $current_user_info->roles;
			print_r( $current_user_role );
			$package    = get_user_meta( $current_user_id, 'ced_cwsm_user_role_package', true );
			$months     = get_user_meta( $current_user_id, 'ced_wholesale_request_month_period', true );
			$days       = get_user_meta( $current_user_id, 'ced_wholesale_request_day_period', true );
			$price      = get_user_meta( $current_user_id, 'ced_wholesale_request_price', true );
			$user_roles = get_option( 'ced_cwsm_wholesaleRolesArray' );
			// echo '<pre>';print_r($user_roles);
			// print_r($use=get_option('ced_cwsm_request_role_myaccount_page'));
			/*
			if ( get_option('ced_cwsm_request_role_myaccount_page' == 'yes')) {*/
				/*
				if ( get_option( ('ced_cwsm_request_role_myaccount_page' == 'yes') || get_option( 'ced_cwsm_request_role_addon_functionality' == 'yes' ) || get_option( 'ced_cwsm_request_role_addon_directly' == 'yes') ) ) {*/
					/*
					foreach ($current_user_role as $key => $value) {*/
			// echo '<pre>';print_r($value); //print_r($user_roles);
				// print_r(get_option('ced_cwsm_request_role_addon_functionality'));
			if ( get_option( 'ced_cwsm_request_role_addon_functionality' ) == 'yes' || get_option( 'ced_cwsm_request_role_myaccount_page' == 'yes' ) || get_option( 'ced_cwsm_request_role_addon_directly' == 'yes' ) ) {
				print_r( $current_user_id );
				print_r( get_user_meta( $current_user_id, 'ced_cwsm_request_for_role' ) );
				/*if(get_user_meta( $current_user_id , 'ced_cwsm_request_for_role', TRUE)!='') {echo "hi";*/
				if ( get_user_meta( $current_user_id, 'ced_cwsm_payement_status', true ) == 'no' ) {
					?>
								<div id="display_subs_msg">
									<p style="color:red !important;"><?php esc_attr_e( 'Your Package will not be applied until you dont pay for your requested package. Please pay to enjoy your package', 'wholesale-market' ); ?></p>
									<p>Your selected package :<?php echo esc_attr( $package ); ?></p>
									<p>Duration :<?php echo esc_attr( $months ); ?>months and <?php echo esc_attr( $days ); ?>days</p>
									<p>Price :<?php echo esc_attr( get_woocommerce_currency_symbol() ) . esc_attr( $price ); ?></p>
									<span><input type="submit" class="Button button" name="paynow" value="<?php esc_attr_e( 'Pay Now', 'wholesale-market' ); ?>" /></span>
								</div>
							<?php } elseif ( 'yes' == get_user_meta( $current_user_id, 'ced_cwsm_payement_status', true ) ) { ?>
								<p style="color:red !important;"><?php esc_attr_e( 'Thanks for choosing this package. Now you are our wholesaleuser', 'wholesale-market' ); ?></p>
								<p>Your selected package :<?php echo esc_attr( $package ); ?></p>
								<p>Package Duration :<?php echo esc_attr( $months ); ?>months and <?php echo esc_attr( $days ); ?>days</p>
								<p>Price :<?php echo esc_attr( get_woocommerce_currency_symbol() ) . esc_attr( $price ); ?></p>
								<?php
							}
							/*}*/
			}
		}
	}
				new Wholesale_user_register_addon();
}
