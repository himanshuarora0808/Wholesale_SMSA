<?php
/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'Wholesale_User_Register_Notification' ) ) {
	/**
	 * This is class for add feature of register user as wholesale user .
	 *
	 * @name    Wholesale_User_Register_Notification
	 * @package Class
	 */

	class Wholesale_User_Register_Notification {

		/**
		 * This is construct of class
		 *
		 * @link http://www.cedcommerce.com/
		 */
		public function __construct() {
			add_filter( 'woocommerce_get_sections_ced_cwsm_plugin', array( $this, 'ced_wura_register_notification_section' ), 10, 1 );
			add_filter( 'woocommerce_get_settings_ced_cwsm_plugin', array( $this, 'ced_wura_register_notification_setting' ), 10, 2 );

			add_filter( 'ced_cwsm_append_basic_sections', array( $this, 'ced_wura_register_notification_section' ), 10, 1 );
			add_filter( 'ced_cwsm_append_basic_settings', array( $this, 'ced_wura_register_notification_setting' ), 10, 2 );

			add_action( 'wp_ajax_nopriv_ced_save_wholesale_notification', array( $this, 'ced_save_wholesale_notification_callback' ) );
			add_action( 'wp_ajax_ced_save_wholesale_notification', array( $this, 'ced_save_wholesale_notification_callback' ) );
			add_filter( 'manage_users_columns', array( $this, 'ced_wura_wholesale_requested_user_table' ) );
			add_filter( 'manage_users_custom_column', array( $this, 'ced_wura_wholesale_requested_user_table_row' ), 10, 3 );
			add_filter( 'manage_users_sortable_columns', array( $this, 'ced_wura_wholesale_requested_column_sortable' ) );
			add_filter( 'request', array( $this, 'ced_wura_wholesale_requested_column_orderby' ) );
		}



		/**
		 * This function is used to order by wholesale user requested columm
		 *
		 * @name ced_wura_wholesale_requested_column_orderby()
		 *
		 * @link  http://www.cedcommerce.com/
		 */

		public function ced_wura_wholesale_requested_column_orderby( $vars ) {
			if ( isset( $vars['orderby'] ) && 'ced_wura_wholesale_request' == $vars['orderby'] ) {
				$vars = array_merge(
					$vars,
					array(
						'meta_key' => 'ced_wholesale_request',
						'orderby'  => 'meta_value',
					)
				);
			}
			return $vars;
		}

		/**
		 * This function is used to make sortable requested user wholesale column
		 *
		 * @name ced_wura_wholesale_requested_column_sortable()
		 *
		 * @link  http://www.cedcommerce.com/
		 */

		public function ced_wura_wholesale_requested_column_sortable( $columns ) {
			$custom = array(
				'ced_wura_wholesale_request' => 'ced_wura_wholesale_request',
			);
			return wp_parse_args( $custom, $columns );
		}


		/**
		 * This function is used to append values in Table for requested role
		 *
		 * @name ced_wura_wholesale_requested_user_table_row()
		 *
		 * @link  http://www.cedcommerce.com/
		 */


		public function ced_wura_wholesale_requested_user_table_row( $val, $column_name, $user_id ) {
			switch ( $column_name ) {
				case 'ced_wura_wholesale_request':
					$request                          = get_user_meta( $user_id, 'ced_wholesale_request', true );
					 $request_direct                  = get_user_meta( $user_id, 'ced_direct_role_request', true );
					$roles                            = get_option( 'ced_cwsm_wholesaleRolesArray', array() );
					$roles['ced_cwsm_wholesale_user'] = array(
						'key'        => 'ced_cwsm_wholesale_user',
						'name'       => 'Wholesale Customer',
						'quick_note' => 'wholesale customer',
					);
					if ( array_key_exists( $request, $roles ) ) {
						$role = $roles[ $request ]['name'];
					} else {
						$role = '';
					}
					if ( isset( $request ) || isset( $request_direct ) && ! empty( $request ) || ! empty( $request_direct ) ) {
						if ( '' != $request && 'accept' != $request && 'cancel' != $request ) {
							return '<span>' . __( 'Requested Role: ', 'wholesale-market' ) . $role . '</span><div class="ced_wura_user_coloumn"><input type="button" value="Accept" class="button ced_wura_user_request_accept" data-id="' . $user_id . '"" role = "' . $request . '">&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value="Cancel" class="button ced_wura_user_request_cancel" data-id="' . $user_id . '" /><img src="' . CED_WURA_DIR_URL . '/assets/images/loading.gif" style="display:none;" width="30px" height="30px" id="ced_wura_ajax_loader"></div>';
						}
						if ( 'accept' == $request ) {
							return '<div class="ced_wura_user_coloumn"><img src="' . CED_WURA_DIR_URL . '/assets/images/accept.png"></div>';
						}
						if ( 'cancel' == $request ) {
							return '<div class="ced_wura_user_coloumn"><img src="' . CED_WURA_DIR_URL . '/assets/images/cancel.png"></div>';
						}
						if ( $request_direct ) {
							if ( in_array( 'wholesale-market-user-adon/wholesale-market-user-addon.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

								return '<div class="ced_wura_user_coloumn"><img src="' . CED_WURA_DIR_URL . '/assets/images/accept.png"></div>';
							}
						}
					} else {
						return '<div class="ced_wura_user_coloumn"><img src="' . CED_WURA_DIR_URL . '/assets/images/blank.png"></div>';
					}
					break;
				default:
			}
			return $val;
		}

		/**
		 * This function is used to add column of wholesale request
		 *
		 * @name ced_wura_wholesale_requested_user_table()
		 *
		 * @link  http://www.cedcommerce.com/
		 */

		public function ced_wura_wholesale_requested_user_table( $column ) {
			$column['ced_wura_wholesale_request'] = __( 'Wholesale Request', 'wholesale-market' );
			return $column;
		}


		/**
		 * This function is used to update notification values
		 *
		 * @name ced_save_wholesale_notification_callback()
		 *
		 * @link  http://www.cedcommerce.com/
		 */

		public function ced_save_wholesale_notification_callback() {
				$check_ajax = check_ajax_referer( 'ced-request-ajax-seurity-string', 'ajax_nonce' );
			if ( $check_ajax ) {
				if ( isset( $_POST['data'] ) ) {
					$notification_data = isset( $_POST['data'] ) ? sanitize_text_field( $_POST['data'] ) : '';
					update_option( 'ced_wura_notification', $notification_data );
				}
				die;
			}
		}

		/**
		 * This function is used to register notification section
		 *
		 * @name ced_wura_register_notification_section()
		 *
		 * @link  http://www.cedcommerce.com/
		 */

		public function ced_wura_register_notification_section( $sections ) {
			$sections['ced_wura_register_notification'] = __( 'Notification', 'wholesale-market' );
			return $sections;
		}

		/**
		 * This function is used to add notification setting section
		 *
		 * @name ced_wura_register_notification_setting()
		 *
		 * @link  http://www.cedcommerce.com/
		 */

		public function ced_wura_register_notification_setting( $settingReceived, $current_section ) {
			if ( 'ced_wura_register_notification' == $current_section ) {
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

				$accept_subject = '';
				if ( isset( $notification_setting['accept_subject'] ) ) {
					$accept_subject = $notification_setting['accept_subject'];
				}

				$accept_message = '';
				if ( isset( $notification_setting['accept_message'] ) ) {
					$accept_message = $notification_setting['accept_message'];
				}

				$cancel_subject = '';
				if ( isset( $notification_setting['cancel_subject'] ) ) {
					$cancel_subject = $notification_setting['cancel_subject'];
				}

				$cancel_message = '';
				if ( isset( $notification_setting['cancel_message'] ) ) {
					$cancel_message = $notification_setting['cancel_message'];
				}

				$GLOBALS['hide_save_button'] = true;
				?>
				<table class="form-table ced_wura_notification_section">
					<tbody>
						<tr valign="top">
							<th class="titledesc" scope="row">
								<label for="ced_wura_notification_from_name"><?php esc_attr_e( 'From Name', 'wholesale-market' ); ?></label>
							</th>
							<td class="forminp forminp-text">
								<input type="text" placeholder="" class="input-text" value="<?php echo esc_attr( $admin_name ); ?>" style="" id="ced_wura_notification_from_name" name="ced_wura_notification_from_name">
							</td>
						</tr>
						<tr valign="top">
							<th class="titledesc" scope="row">
								<label for="ced_wura_notification_from_mail"><?php esc_attr_e( 'From Email', 'wholesale-market' ); ?></label>
							</th>
							<td class="forminp forminp-email">
								<input type="email" placeholder="" class="input-text" value="<?php echo esc_attr( $admin_email ); ?>" style="" id="ced_wura_notification_from_mail" name="ced_wura_notification_from_mail">
							</td>
						</tr>
						
						<tr valign="top">
							<th class="titledesc" scope="row">
								<label for="ced_wura_notification_admin_rcv_subject"><?php esc_attr_e( 'New Request for Wholesale Role Subject', 'wholesale-market' ); ?></label>
							</th>
							<td class="forminp forminp-text">
								<input type="text" placeholder="" class="input-text" value="<?php echo esc_attr( $admin_subject ); ?>" style="" id="ced_wura_notification_admin_rcv_subject" name="ced_wura_notification_admin_rcv_subject">
							</td>
						</tr>
						<tr valign="top">
							<th class="titledesc" scope="row">
								<label for="ced_wura_notification_admin_rcv_msg"><?php esc_attr_e( 'New Request for Wholesale Role Message', 'wholesale-market' ); ?></label>
							</th>
							<td class="forminp forminp-textarea">
								<?php
								$content   = $admin_message;
								$editor_id = 'ced_wura_notification_admin_rcv_msg';
								$settings  = array(
									'media_buttons'    => false,
									'drag_drop_upload' => true,
									'dfw'              => true,
									'teeny'            => true,
									'editor_height'    => 200,
									'editor_class'     => '',
									'textarea_name'    => 'ced_wura_notification_admin_rcv_msg',
								);
								wp_editor( stripslashes( $content ), $editor_id, $settings );
								?>
							</td>
						</tr>
						
						<tr valign="top">
							<th class="titledesc" scope="row">
								<label for="ced_wura_notification_rcv_subject"><?php esc_attr_e( 'Wholesale Role Request Received Subject', 'wholesale-market' ); ?></label>
							</th>
							<td class="forminp forminp-text">
								<input type="text" placeholder="" class="input-text" value="<?php echo esc_attr( $user_subject ); ?>" style="" id="ced_wura_notification_rcv_subject" name="ced_wura_notification_rcv_subject">
							</td>
						</tr>
						<tr valign="top">
							<th class="titledesc" scope="row">
								<label for="ced_wura_notification_rcv_msg"><?php esc_attr_e( 'Wholesale Role Request Received Message', 'wholesale-market' ); ?></label>
							</th>
							<td class="forminp forminp-textarea">
								<?php
								$content   = $user_message;
								$editor_id = 'ced_wura_notification_rcv_msg';
								$settings  = array(
									'media_buttons'    => false,
									'drag_drop_upload' => true,
									'dfw'              => true,
									'teeny'            => true,
									'editor_height'    => 200,
									'editor_class'     => '',
									'textarea_name'    => 'ced_wura_notification_rcv_msg',
								);
								wp_editor( stripslashes( $content ), $editor_id, $settings );
								?>
							</td>
						</tr>
						
						<tr valign="top">
							<th class="titledesc" scope="row">
								<label for="ced_wura_notification_rcv_subject"><?php esc_attr_e( 'Wholesale Role Request Accept Subject', 'wholesale-market' ); ?></label>
							</th>
							<td class="forminp forminp-text">
								<input type="text" placeholder="" class="input-text" value="<?php echo esc_attr( $accept_subject ); ?>" style="" id="ced_wura_notification_accept_subject" name="ced_wura_notification_accept_subject">
							</td>
						</tr>
						<tr valign="top">
							<th class="titledesc" scope="row">
								<label for="ced_wura_notification_accept_msg"><?php esc_attr_e( 'Wholesale Role Request Accept Message', 'wholesale-market' ); ?></label>
							</th>
							<td class="forminp forminp-textarea">
								<?php
								$content   = $accept_message;
								$editor_id = 'ced_wura_notification_accept_msg';
								$settings  = array(
									'media_buttons'    => false,
									'drag_drop_upload' => true,
									'dfw'              => true,
									'teeny'            => true,
									'editor_height'    => 200,
									'editor_class'     => '',
									'textarea_name'    => 'ced_wura_notification_accept_msg',
								);
								wp_editor( stripslashes( $content ), $editor_id, $settings );
								?>
							</td>
						</tr>
						
						<tr valign="top">
							<th class="titledesc" scope="row">
								<label for="ced_wura_notification_rcv_subject"><?php esc_attr_e( 'Wholesale Role Request Cancel Subject', 'wholesale-market' ); ?></label>
							</th>
							<td class="forminp forminp-text">
								<input type="text" placeholder="" class="input-text" value="<?php echo esc_attr( $cancel_subject ); ?>" style="" id="ced_wura_notification_cancel_subject" name="ced_wura_notification_cancel_subject">
							</td>
						</tr>
						<tr valign="top">
							<th class="titledesc" scope="row">
								<label for="ced_wura_notification_cancel_msg"><?php esc_attr_e( 'Wholesale Role Request Cancel Message', 'wholesale-market' ); ?></label>
							</th>
							<td class="forminp forminp-textarea">
								<?php
								$content   = $cancel_message;
								$editor_id = 'ced_wura_notification_cancel_msg';
								$settings  = array(
									'media_buttons'    => false,
									'drag_drop_upload' => true,
									'dfw'              => true,
									'teeny'            => true,
									'editor_height'    => 200,
									'editor_class'     => '',
									'textarea_name'    => 'ced_wura_notification_cancel_msg',
								);
								wp_editor( stripslashes( $content ), $editor_id, $settings );
								?>
							</td>
						</tr>
						
						<tr valign="top">
							<td class="titledesc" scope="row">
								<input type="button" class="button-primary" id="ced_wura_save_notification_setting" value="<?php esc_attr_e( 'Save Setting', 'wholesale-market' ); ?>">
								<img height="20px" src="<?php echo esc_url( CED_WURA_DIR_URL ); ?>/assets/images/loading.gif" id="ced_wura_loading" style="display:none;">
							</td>
							
						</tr>
					</tbody>
				</table>
				<?php
				$settings = array();
				return $settings;
			}
			return $settingReceived;
		}
	}
	new Wholesale_User_Register_Notification();
}
