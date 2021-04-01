<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Adds Product Meta fields and save them to database(for simple and variable products).
 *
 * @class    CED_CWSM_Add_Product_Meta_Fields
 * @version  2.0.8
 * @package  wholesale-market/adminSide
 * @package Class
 */
class CED_CWSM_Add_Product_Meta_Fields {

	// store the single instance
	private static $_instance;
	/*
	 * Get an instance of the database
	 * @return database
	 */
	public static function getInstance() {
		if ( ! self::$_instance instanceof self ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * This function creates meta-fields for simple products.
	 *
	 * @name cwsm_create_simple_product_meta_fields()

	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_create_simple_product_meta_fields() {
		global $post, $thepostid;

		if ( WC()->version < '3.0.0' ) {
			if ( get_product( $thepostid )->is_type( 'simple' ) ) {
				$attribute_description = 'Enter Wholesale-Price Of The Product Here.';
				echo '<div class="show_if_simple options_group ced_cwsm_fields_wrapper">';
				echo '<h3 align= "center" class="ced_cwsm_fields_wrapper_header">' . esc_attr( 'Wholesale Market', 'wholesale-market' ) . '</h3>';
				echo '<div class="show_if_simple options_group ced_cwsm_user_section_div">';
				echo '<h3>' . esc_attr( 'Wholesale-User Related Options', 'wholesale-market' ) . '</h3>';
				woocommerce_wp_text_input(
					array(
						'id'          => 'ced_cwsm_wholesale_price',
						'data_type'   => 'price',
						'label'       => esc_attr( 'Wholesale Price', 'wholesale-market' ) . ' (' . get_woocommerce_currency_symbol() . ')',
						'desc_tip'    => true,
						'description' => esc_attr( $attribute_description, 'wholesale-market' ),
					)
				);

				do_action( 'ced_cwsm_add_simple_product_meta_fields', $post, $thepostid );
				echo '</div>';
				do_action( 'ced_cwsm_add_simple_product_meta_fields_for_different_wholesale_role' );
				echo '</div>';
			}
		} else {
			if ( wc_get_product( $thepostid )->is_type( 'simple' ) ) {
				$attribute_description = 'Enter Wholesale-Price Of The Product Here.';
				echo '<div class="show_if_simple options_group ced_cwsm_fields_wrapper">';
				echo '<h3 align= "center" class="ced_cwsm_fields_wrapper_header">' . esc_attr( 'Wholesale Market', 'wholesale-market' ) . '</h3>';
				echo '<div class="show_if_simple options_group ced_cwsm_user_section_div">';
				echo '<h3>' . esc_attr( 'Wholesale-User Related Options', 'wholesale-market' ) . '</h3>';
				woocommerce_wp_text_input(
					array(
						'id'          => 'ced_cwsm_wholesale_price',
						'data_type'   => 'price',
						'label'       => esc_attr( 'Wholesale Price', 'wholesale-market' ) . ' (' . get_woocommerce_currency_symbol() . ')',
						'desc_tip'    => true,
						'description' => esc_attr( $attribute_description, 'wholesale-market' ),
					)
				);

				do_action( 'ced_cwsm_add_simple_product_meta_fields', $post, $thepostid );
				echo '</div>';
				do_action( 'ced_cwsm_add_simple_product_meta_fields_for_different_wholesale_role' );
				echo '</div>';
			}
		}
	}

	/**
	 * This function saves meta-fields for simple products.
	 *
	 * @name cwsm_save_simple_product_meta_fields()

	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_save_simple_product_meta_fields( $post_id ) {
		if ( ! isset( $_POST['woocommerce_meta_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ) ) ) {
			return;
		}
		/*
		echo "<pre>";
		print_r($_POST);
		die("hello");*/
		$cwsm_wholesale_price = isset( $_POST['ced_cwsm_wholesale_price'] ) ? sanitize_text_field( $_POST['ced_cwsm_wholesale_price'] ) : '';
		update_post_meta( $post_id, 'ced_cwsm_wholesale_price', wc_format_decimal( $cwsm_wholesale_price ) );

		do_action( 'ced_cwsm_save_added_simple_product_meta_fields', $post_id );
		do_action( 'ced_cwsm_save_added_simple_product_meta_fields_for_different_wholesale_role', $post_id );
	}

	/**
	 * This function creates meta-fields for variable products.
	 *
	 * @name cwsm_create_variation_product_meta_fields()

	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_create_variation_product_meta_fields( $loop, $variation_data, $variation ) {
		global $woocommerce, $post;

		$varitionPrice         = get_post_meta( $variation->ID, 'ced_cwsm_wholesale_price', true );
		$currencySymbol        = get_woocommerce_currency_symbol();
		$attribute_description = 'Enter Wholesale-Price Of The Product Here.';

		echo '<div class="options_group ced_cwsm_fields_wrapper">';
		echo '<h3 align= "center" class="ced_cwsm_fields_wrapper_header">' . esc_attr( 'Wholesale Market', 'wholesale-market' ) . '</h3>';

		?>
		<div class="variable_pricing ced_cwsm_variable_pricing wc-metabox ced_cwsm_user_section_div">
			<?php echo '<h3>' . esc_attr( 'Wholesale-User Related Options', 'wholesale-market' ) . '</h3>'; ?>
			<p class="form-field ced_cwsm_wholesale_price">
				<label><?php echo esc_attr( 'Wholesale Price', 'wholesale-market' ) . ' (' . esc_attr( $currencySymbol ) . '):'; ?> </label> 
				<input type="text" name="ced_cwsm_wholesale_price[<?php echo esc_attr( $variation->ID ); ?>]"
				value="
				<?php
				if ( isset( $varitionPrice ) ) {
					echo esc_attr( $varitionPrice );}
				?>
				"
				class="wc_input_decimal" />
				<?php echo wc_help_tip( esc_attr( $attribute_description, 'wholesale-market' ) ); ?>
			</p>
			<?php
			do_action( 'ced_cwsm_add_variation_product_meta_fields', $loop, $variation_data, $variation );
			?>
		</div>
		<?php
		do_action( 'ced_cwsm_add_variation_product_meta_fields_for_different_wholesale_role', $loop, $variation_data, $variation );
		echo '</div>';
	}

	/**
	 * This function saves meta-fields for variable products.
	 *
	 * @name cwsm_save_variation_product_meta_fields()

	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_save_variation_product_meta_fields( $post_id ) {
		if ( ! isset( $_POST['woocommerce_meta_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ) ) ) {
			return;
		}

		$all_prices = isset( $_POST['ced_cwsm_wholesale_price'] ) ? ( $_POST['ced_cwsm_wholesale_price'] ) : '';
		if ( is_array( $all_prices ) ) {
			foreach ( $all_prices as $variationId => $variationValue ) {
				$wholesale_price = isset( $_POST['ced_cwsm_wholesale_price'][ $variationId ] ) ? sanitize_text_field( $_POST['ced_cwsm_wholesale_price'][ $variationId ] ) : '';
				update_post_meta( $variationId, 'ced_cwsm_wholesale_price', ( '' === $wholesale_price ) ? '' : sanitize_text_field( wc_format_decimal( $wholesale_price ) ) );
				do_action( 'ced_cwsm_save_added_variation_product_meta_fields', $variationId );
			}
		}
		do_action( 'ced_cwsm_save_added_variation_product_meta_fields_for_different_wholesale_role', $post_id );
	}
}
?>
