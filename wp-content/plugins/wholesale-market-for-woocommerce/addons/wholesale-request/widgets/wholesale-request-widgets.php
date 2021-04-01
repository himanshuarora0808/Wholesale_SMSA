<?php
/**
 * Adds Wholesale Request widget.
 */
class Wholesale_Request_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'wholesale_request', // Base ID
			__( 'Wholesale Request', 'wholesale-market' ), // Name
			array( 'description' => __( 'Send request to become a wholeseller', 'wholesale-market' ) ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 * @return array $args
	 * @return array $instance
	 */
	public function widget( $args, $instance ) {
		$current_user_id = get_current_user_id();
		if ( ! empty( $current_user_id ) ) {
			$current_user_info = get_userdata( $current_user_id );
			if ( ! empty( $current_user_info ) && ! empty( $current_user_info->roles ) ) {
				$current_user_role = $current_user_info->roles;
			}

			if ( get_option( 'ced_cwsm_request_role_addon_functionality' ) == 'yes' ) {
				global $globalCWSM;
				if ( ! $globalCWSM->isCurrentUserIsWholesaleUser() ) {
					if ( ! get_user_meta( $current_user_id, 'ced_wholesale_request', true ) || get_user_meta( $current_user_id, 'ced_wholesale_request', true ) == 'cancel' ) {

						$check = true;
						$check = apply_filters( 'ced_cwsm_render_widget', $check );
						if ( $check ) {
							echo esc_attr( $args['before_widget'] );
							do_action( 'ced_cwsm_before_request_widget' );
							if ( ! empty( $instance['title'] ) ) {
								echo esc_attr( $args['before_title'] ) . esc_attr( apply_filters( 'widget_title', $instance['title'] ) ) . esc_attr( $args['after_title'] );
							}
							$user_roles = get_option( 'ced_cwsm_wholesaleRolesArray' );
							?>
							<div id="ced_widget_success_msg" style="display:none;">
								<span style="color:red !important;"><?php esc_attr_e( 'Role Successfully Requested', 'wholesale-market' ); ?></span>
							</div>
							<?php
							$payment_addon_enable = get_option( 'ced_cwsm_basic_settings_payment_enable' );
							if ( 'on' == $payment_addon_enable ) {
								?>
							 <form action="http://192.168.0.166/web/wordpress/my-account/" method="POST">
							 <?php } ?>
							<div class="ced_cwsm_request_role">								
								<?php echo esc_attr_e( 'Want to Modify Role ', 'wholesale-market' ); ?>
								<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide ced_cwsm_request_role" style="padding-bottom:10px;">
									<select name="ced_cwsm_user_role" id="reg_role">
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
								<?php
								do_action( 'add_extra_field_on_registering_role' );
								?>
								<?php
								$payment_addon_enable = get_option( 'ced_cwsm_basic_settings_payment_enable' );
								if ( 'on' == $payment_addon_enable ) {
									?>
										<input type="submit" class="Button button" name="paynow_request_widget" value="<?php esc_attr_e( 'Pay Now', 'wholesale-market' ); ?>" />
								</form>
								<?php } else { ?>
								<a class="ced_wm_wholesale_request button" style="padding:10px;" id="ced_wm_wholesale_request" data-id="'.$current_user_id.'" href="javascript:void(0);"><?php esc_attr_e( 'Click Here', 'wholesale-market' ); ?></a>
								<?php } ?>
								<img src="<?php echo esc_url( CED_CWSM_PLUGIN_DIR_URL ) . 'assets/images/ajax-loader.gif'; ?>" style="display:none;" width="30px" height="30px" id="ced_cwsm_ajax_loader">
							</div>
							<?php
							do_action( 'ced_cwsm_after_request_widget' );
							echo esc_attr( $args['after_widget'] );
						}
					}
				} else {
					do_action( 'ced_cwsm_modify_widget', $current_user_id );
				}
			}
		}
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Request a Role', 'wholesale-market' );
		?>
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( esc_attr( 'Title:', 'wholesale-market' ) ); ?></label> 
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}
} // class Wholesale_Request_Widget

// register wholesale_request_widget Widget
function wholesale_request_widget() {
	register_widget( 'Wholesale_Request_Widget' );
}
add_action( 'widgets_init', 'wholesale_request_widget' );
