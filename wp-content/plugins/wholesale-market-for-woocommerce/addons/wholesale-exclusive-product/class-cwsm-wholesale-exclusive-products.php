<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Adds "Feature of assigning a product as exclusively(only) a wholesale product" condition to core plugin using hooks and filters of that.
 *
 * @class    CED_CWSM_Wholesale_Exclusive_Products
 * @version  1.0.2
 * @package  wholesale-market-product-addon/addons/wholesale-exclusive-product
 * @package Class
 */

class CED_CWSM_Wholesale_Exclusive_Products {

	public function __construct() {

		global $ced_cwsm_product_addon_license; // it must be dynamic for each extension
		$license_key = get_option( 'ced_cwsm_product_addon_license_key', false );
		$module_name = get_option( 'ced_cwsm_product_addon_license_module', false );
		if ( is_array( $ced_cwsm_product_addon_license ) ) {
			$server_host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( $_SERVER['HTTP_HOST'] ) : '';
			if ( $ced_cwsm_product_addon_license['license'] == $license_key && $ced_cwsm_product_addon_license['module_name'] == $module_name && $ced_cwsm_product_addon_license['domain'] == $server_host ) {
				$this->add_hooks_and_filters();
			}
		}
		$this->add_hooks_and_filters();

	}

	/**
	 * This function hooks into all filters and actions available in core plugin.
	 *
	 * @name add_hooks_and_filters()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function add_hooks_and_filters() {

		add_filter( 'ced_cwsm_append_product_addon_sections', array( $this, 'ced_cwsm_append_product_addon_sections' ), 10.1, 1 );
		add_filter( 'ced_cwsm_append_product_addon_settings', array( $this, 'ced_cwsm_append_product_addon_settings' ), 10, 2 );

		$ced_cwsm_enable_wholesale_exclusive_products = get_option( 'ced_cwsm_enable_wholesale_exclusive_products', false );
		if ( empty( $ced_cwsm_enable_wholesale_exclusive_products ) || 'no' == $ced_cwsm_enable_wholesale_exclusive_products ) {
			return;
		}

		add_action( 'ced_cwsm_add_simple_product_meta_fields', array( $this, 'ced_cwsm_add_simple_product_meta_fields' ), 10, 3 );
		add_action( 'ced_cwsm_save_added_simple_product_meta_fields', array( $this, 'ced_cwsm_save_added_simple_product_meta_fields' ), 10, 2 );

		 // consider variations as one condition
		add_filter( 'ced_cwsm_alter_common_fields_for_all_variations', array( $this, 'ced_cwsm_alter_common_fields_for_all_variations' ), 11, 1 );

		// the callback function checks for is_page
		// which doesn't work in this hook
		// /* assign template to wholesale-shop page like shop page */
		// add_filter( 'woocommerce_get_shop_page_id', array( $this, 'woocommerce_alter_shop_page_id' ) );

		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		add_filter( 'woocommerce_get_breadcrumb', array( $this, 'woocommerce_get_breadcrumb' ), 30, 2 );
		add_filter( 'woocommerce_page_title', array( $this, 'woocommerce_page_title' ) );
		add_action( 'woocommerce_product_query', array( $this, 'ced_cwsm_wholesale_exclusive_products_alter_query' ), 10, 1 );
		add_action( 'woocommerce_product_query', array( $this, 'hide_from_shop_page' ), 10 );
		add_action( 'save_post', array( $this, 'ced_cwsm_exclusive_product_save_variationPro_meta_field' ), 10, 1 );

	}

	public function ced_cwsm_exclusive_product_save_variationPro_meta_field() {
		global $post;
		// $product = wc_get_product($post->ID);
		// $role_identifier = $globalCWSM->getCurrentWholesaleUserRole();
		// ($role_identifier == 'ced_cwsm_wholesale_user') ? $role_identifier = '' : $role_identifier = '_'.$role_identifier;
		// print_r($role_identifier);die;
		// print_r($product->get_available_variations());die;

		/*
		print_r($_POST);
		die;*/

		if ( ! isset( $_POST['woocommerce_meta_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ) ) ) {
			return;
		}

		$valueToSave = ( isset( $_POST['ced_cwsm_enable_as_wholesale_exclusive'] ) ) ? 'yes' : '';
		if ( isset( $valueToSave ) && ! empty( $valueToSave ) ) {
			update_post_meta( $post->ID, 'ced_cwsm_enable_as_wholesale_exclusive', $valueToSave );

		}
	}

	public function hide_from_shop_page( $q ) {

		?><!-- <style type="text/css">
			#menu-item-240{
				display:none;
			}
		</style> -->
		<?php
		$meta_query = $q->get( 'meta_query' );
		global $globalCWSM;
		if ( $globalCWSM->isCurrentUserIsWholesaleUser() ) {
			if ( get_option( 'ced_cwsm_enable_wholesale_exclusive_products' ) == 'yes' ) {
				$wholesale_shop_page_slug = get_option( 'ced_cwsm_wholesale_shop_page_slug', false ) ? get_option( 'ced_cwsm_wholesale_shop_page_slug', false ) : 'wholesale-shop';
				$role_identifier          = $globalCWSM->getCurrentWholesaleUserRole();
				( 'ced_cwsm_wholesale_user' == $role_identifier ) ? $role_identifier = '' : $role_identifier = '_' . $role_identifier;
				$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( $_SERVER['REQUEST_URI'] ) : '';
				if ( strpos( $request_uri, $wholesale_shop_page_slug ) == false ) {
					$meta_query[] = array(
						'relation' => 'OR',
						array(
							'key'     => 'ced_cwsm_hide_wholesale_exclusive_from_shop' . $role_identifier,
							'compare' => 'NOT EXISTS',
						),
						array(
							'key'     => 'ced_cwsm_hide_wholesale_exclusive_from_shop' . $role_identifier,
							'value'   => '',
							'compare' => 'EQUAl',

						),
					);
				}
			}
		}
		$q->set( 'meta_query', $meta_query );
	}


	public function pre_get_posts( $q ) {

		global $globalCWSM;
		if ( $globalCWSM->isCurrentUserIsWholesaleUser() ) :
			$wholesale_shop_page_slug = get_option( 'ced_cwsm_wholesale_shop_page_slug', false ) ? get_option( 'ced_cwsm_wholesale_shop_page_slug', false ) : 'wholesale-shop';

			if ( get_queried_object() && ! empty( get_queried_object()->post_name ) && get_queried_object()->post_name == $wholesale_shop_page_slug && is_page( $wholesale_shop_page_slug ) ) {
				$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( $_SERVER['REQUEST_URI'] ) : '';
				if ( strpos( $request_uri, $wholesale_shop_page_slug ) !== false ) {
					if ( ! is_admin() ) {
						$q->query_vars['pagename']  = '';
						$q->query_vars['post_type'] = 'product';
						$q->is_page                 = '';
						$q->is_archive              = '1';
						$q->is_post_type_archive    = '1';
						$q->is_singular             = '';
						remove_action( 'pre_get_posts', array( $this, 'pre_get_posts' ), 10 );
					}
				}
			}
		endif;
	}

	/**
	 * This function modifies the query to only display wholesale exclusive products on wholesale market page.
	 *
	 * @name ced_cwsm_wholesale_exclusive_products_alter_query()
	 *
	 * @link  http://www.cedcommerce.com/EXISTS
	 */
	public function ced_cwsm_wholesale_exclusive_products_alter_query( $q ) {
		global $globalCWSM;

		$role_identifier = $globalCWSM->getCurrentWholesaleUserRole();
		( 'ced_cwsm_wholesale_user' == $role_identifier ) ? $role_identifier = '' : $role_identifier = '_' . $role_identifier;

		if ( is_shop() ) {
			$wholesale_shop_page_slug = get_option( 'ced_cwsm_wholesale_shop_page_slug', false ) ? get_option( 'ced_cwsm_wholesale_shop_page_slug', false ) : 'wholesale-shop';
			$request_uri              = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( $_SERVER['REQUEST_URI'] ) : '';
			if ( strpos( $request_uri, $wholesale_shop_page_slug ) !== false ) {
				$meta_query = $q->get( 'meta_query' );
				if ( get_option( 'ced_cwsm_enable_wholesale_exclusive_products' ) == 'yes' ) {
					$meta_query[] = array(
						'key'     => 'ced_cwsm_enable_as_wholesale_exclusive' . $role_identifier,
						'value'   => 'yes',
						'compare' => 'EQUALS',
					);
				}
				$q->set( 'meta_query', $meta_query );
			}
		}
	}


	// /**
	// * This function remove woocommerce price on shop page.
	// * @name woocommerce_alter_shop_page_id()
	// *
	// * @link  http://www.cedcommerce.com/
	// */
	// function woocommerce_alter_shop_page_id( $id_received ) {
	// $wholesale_shop_page_slug = get_option( 'ced_cwsm_wholesale_shop_page_slug', false ) ? get_option( 'ced_cwsm_wholesale_shop_page_slug', false ) : 'wholesale-shop';
	// if( is_page( $wholesale_shop_page_slug ) ) {
	// $pageInfo = get_page_by_path( $wholesale_shop_page_slug );
	// return $pageInfo->id;
	// }
	// return $id_received;
	// }

	public function wp_enqueue_scripts() {
		global $globalCWSM;
		$whoelsale_user           = $globalCWSM->isCurrentUserIsWholesaleUser();
		$wholesale_shop_page_slug = get_option( 'ced_cwsm_wholesale_shop_page_slug', false ) ? get_option( 'ced_cwsm_wholesale_shop_page_slug', false ) : 'wholesale-shop';

		$pageInfo = get_page_by_path( $wholesale_shop_page_slug );
		$title    = ! empty( $pageInfo ) ? get_the_title( $pageInfo->ID ) : '';

		wp_enqueue_script( 'ced_cwsm_hide_menu_js', plugins_url( 'js/ced_cwsm_hide_menu.js', __FILE__ ), array( 'jquery' ), '1.0', true );
		wp_localize_script(
			'ced_cwsm_hide_menu_js',
			'ced_cwsm_hide_menu_js_ajax',
			array(
				'ajax_url'                  => admin_url( 'admin-ajax.php' ),
				'wholesale_user'            => $whoelsale_user,
				'wholesale_shop_page_title' => $title,
			)
		);
		if ( $whoelsale_user ) :
			if ( is_page( $wholesale_shop_page_slug ) ) {
				$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( $_SERVER['REQUEST_URI'] ) : '';
				if ( strpos( $request_uri, $wholesale_shop_page_slug ) !== false ) {
					$wholesale_shop_page_url = get_permalink( $pageInfo->id );

					$shop_page     = get_post( wc_get_page_id( 'shop' ) );
					$shop_page_url = get_permalink( $shop_page->id );

					wp_enqueue_script( 'ced_cwsm_remove_active_class_js', plugins_url( 'js/remove-active-class.js', __FILE__ ), array( 'jquery' ), '1.0', true );
					wp_localize_script(
						'ced_cwsm_remove_active_class_js',
						'ced_cwsm_remove_active_class_js_ajax',
						array(
							'ajax_url'                => admin_url( 'admin-ajax.php' ),
							'shop_page_url'           => $shop_page_url,
							'wholesale_shop_page_url' => $wholesale_shop_page_url,
						)
					);
				}
			}
		endif;
	}

	public function woocommerce_get_breadcrumb( $crumbs, $thisRef ) {
		$wholesale_shop_page_slug = get_option( 'ced_cwsm_wholesale_shop_page_slug', false ) ? get_option( 'ced_cwsm_wholesale_shop_page_slug', false ) : 'wholesale-shop';
		if ( is_page( $wholesale_shop_page_slug ) ) {
			$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( $_SERVER['REQUEST_URI'] ) : '';
			if ( strpos( $request_uri, $wholesale_shop_page_slug ) !== false ) {
				$pageInfo   = get_page_by_path( $wholesale_shop_page_slug );
				$arrayToUse = array( $pageInfo->post_title, get_permalink( $pageInfo->id ) );
				$crumbs[1]  = $arrayToUse;
			}
		}
		return $crumbs;
	}

	public function woocommerce_page_title( $page_title ) {

		if ( is_shop() ) {
			$wholesale_shop_page_slug = get_option( 'ced_cwsm_wholesale_shop_page_slug', false ) ? get_option( 'ced_cwsm_wholesale_shop_page_slug', false ) : 'wholesale-shop';
			$req_uri                  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( $_SERVER['REQUEST_URI'] ) : '';
			if ( strpos( $req_uri, false !== $wholesale_shop_page_slug ) ) {
				$pageInfo = get_page_by_path( $wholesale_shop_page_slug );
				return $pageInfo->post_title;
			}
		}
		return $page_title;
	}


	/*** For Simple Product ***/

	/**
	 * This function creates meta-fields for simple products.
	 *
	 * @name ced_cwsm_add_simple_product_meta_fields()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_add_simple_product_meta_fields( $post, $thepostid, $role_identifier = '' ) {
		global $post, $thepostid;

		if ( wc_get_product( $thepostid )->is_type( 'simple' ) && get_option( 'ced_cwsm_enable_wholesale_exclusive_products' ) == 'yes' ) {
			$attribute_description = 'Enable for making product as Exclusively Wholesale.';
			woocommerce_wp_checkbox(
				array(
					'id'          => 'ced_cwsm_enable_as_wholesale_exclusive' . $role_identifier,
					'label'       => __( 'Mark As Wholesale Exclusive Product', 'wholesale-market' ),
					'desc_tip'    => true,
					'description' => __( $attribute_description, 'wholesale-market' ),
				)
			);
			woocommerce_wp_checkbox(
				array(
					'id'          => 'ced_cwsm_hide_wholesale_exclusive_from_shop' . $role_identifier,
					'label'       => __( 'Hide Wholesale Exclusive Product From Shop', 'wholesale-market' ),
					'desc_tip'    => true,
					'description' => __( $attribute_description, 'wholesale-market' ),
				)
			);
		}
	}

	/**
	 * This function saves whoelsale exclusive product meta field.
	 *
	 * @name ced_cwsm_save_added_simple_product_meta_fields()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_save_added_simple_product_meta_fields( $post_id, $role_identifier = '' ) {
		if ( ! isset( $_POST['woocommerce_meta_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ) ) ) {
			return;
		}
		$valueToSave  = ( isset( $_POST[ 'ced_cwsm_enable_as_wholesale_exclusive' . $role_identifier ] ) ) ? 'yes' : '';
		$valueforsave = ( isset( $_POST[ 'ced_cwsm_hide_wholesale_exclusive_from_shop' . $role_identifier ] ) ) ? 'yes' : '';

		update_post_meta( $post_id, 'ced_cwsm_enable_as_wholesale_exclusive' . $role_identifier, $valueToSave );
		update_post_meta( $post_id, 'ced_cwsm_hide_wholesale_exclusive_from_shop' . $role_identifier, $valueforsave );
	}


	/**
	 * This function adds fields for variabkle product as per consider variations as one condition.
	 *
	 * @name ced_cwsm_alter_common_fields_for_all_variations()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_alter_common_fields_for_all_variations( $commonFields ) {
		$req_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( $_SERVER['REQUEST_URI'] ) : '';
		if ( strpos( $req_uri, 'page=wholesale_market&tab=ced_cwsm_product_addon&section=ced_cwsm_consider_variations_as_one_module' ) ) {
			return $commonFields;
		}

		update_option( 'ced_cwsm_enable_as_wholesale_exclusive_enable', 'yes' );
		update_option( 'ced_cwsm_hide_wholesale_exclusive_from_shop_enable', 'yes' );
		$attribute_description = 'Enable for making product as Exclusively Wholesale.';
		$commonFields[]        = array(
			'id'          => 'ced_cwsm_enable_as_wholesale_exclusive',
			'label'       => __( 'Mark As Wholesale Exclusive Product', 'wholesale-market' ),
			'desc_tip'    => true,
			'description' => __( $attribute_description, 'wholesale-market' ),
			'type'        => 'checkbox',
			'value'       => '',
		);
		$commonFields[]        = array(
			'id'          => 'ced_cwsm_hide_wholesale_exclusive_from_shop',
			'label'       => __( 'Hide Product for normal Users', 'wholesale-market' ),
			'desc_tip'    => true,
			'description' => __( 'Enable this for hiding the product for non wholesale users', 'wholesale-market' ),
			'type'        => 'checkbox',
			'value'       => '',
		);
		// print_r($commonFields);

		return $commonFields;
	}

	/**
	 * This function adds section on wholesale market tab.
	 *
	 * @name ced_cwsm_append_product_addon_sections()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_append_product_addon_sections( $sections ) {
		$sections['ced_cwsm_wholesale_exclusive_product'] = __( 'Wholesale Exclusive Product', 'wholesale-market' );
		return $sections;
	}

	/**
	 * This function adds settings on wholesale market tab.
	 *
	 * @name ced_cwsm_append_product_addon_settings()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function ced_cwsm_append_product_addon_settings( $settingReceived, $current_section ) {
		if ( 'ced_cwsm_wholesale_exclusive_product' == $current_section ) {
			$settings = apply_filters(
				'ced_cwsm_wholesale_exclusive_products_setting',
				array(
					'section_title-5'                   => array(
						'name' => __( 'Wholesale Exclusive Products', 'wholesale-market' ),
						'type' => 'title',
						'desc' => __( 'Settings regarding Wholesale Exclusive Products are listed below.<br/>Utilizing this option you can set a product wholesale exclusive, and that products will be list automatically in "Wholesale-Shop" page created by admin.', 'wholesale-market' ),
						'id'   => 'wc_cwsm_setting_tab_section_title-5',
					),
					'ced_cwsm_enable_wholesale_exclusive_products' => array(
						'title'   => __( 'Enable Wholesale Exclusive Product Condition', 'wholesale-market' ),
						'desc'    => __( 'Enable To Activate Make Products Wholesale Exclusive Condition', 'wholesale-market' ),
						'id'      => 'ced_cwsm_enable_wholesale_exclusive_products',
						'type'    => 'checkbox',
						'default' => 'no',
					),
					// 'ced_cwsm_show_wholesale_exclusive_products_for_customer'	=>array(
					// 'title'   => __( 'Show Wholesale Exclusive Products For Non Wholesale Users', 'wholesale-market' ),
					// 'desc'    => __( 'Enable To Activate Show Products Wholesale Exclusive For Non Wholesale Users', 'wholesale-market' ),
					// 'id'      => 'ced_cwsm_show_wholesale_exclusive_products_for_customer',
					// 'type'    => 'checkbox',
					// 'default' => 'no',
					// ),
					'ced_cwsm_wholesale_shop_page_slug' => array(
						'title'    => __( 'Enter Wholesale Shop Page Slug', 'wholesale-market' ),
						'desc'     => __( 'This is to set the slug of Wholesale-Shop page created', 'wholesale-market' ),
						'id'       => 'ced_cwsm_wholesale_shop_page_slug',
						'class'    => 'input-text',
						'type'     => 'text',
						'desc_tip' => true,
					),
					'section_end-5'                     => array(
						'type' => 'sectionend',
						'id'   => 'wc_ced_ws_setting_tab_section_end-5',
					),
				)
			);
			return $settings;
		}
		return $settingReceived;
	}

}
new CED_CWSM_Wholesale_Exclusive_Products();
?>
