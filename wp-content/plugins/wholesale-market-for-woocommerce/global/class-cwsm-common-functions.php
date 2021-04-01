<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Adds a global instance to call some core functionality on-the-fly.
 *
 * @class    CED_CWSM_Global_Functions
 * @version  2.0.8
 * @package  wholesale-market/global
 */
class CED_CWSM_Global_Functions {
	private static $_instance;

	public static function getInstance() {
		if ( ! self::$_instance instanceof self ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * This function fetches common fields for variations.
	 *
	 * @name getCommonFieldsForAllVariations()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function getCommonFieldsForAllVariations() {
		$commonFields = array();
		$commonFields = apply_filters( 'ced_cwsm_alter_common_fields_for_all_variations', $commonFields );
		return $commonFields;
	}

	/**
	 * This function check is have to render meta-field or not.
	 *
	 * @name isHaveToRenderMetaField()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function isHaveToRenderMetaField( $metaFieldKey ) {
		$isHaveToRender = true;
		$commonFields   = $this->getCommonFieldsForAllVariations();
		foreach ( $commonFields as $commonField ) {
			if ( get_option( $metaFieldKey . '_enable', false ) == 'yes' ) {
				$isHaveToRender = false;
				break;
			}
		}
		$isHaveToRender = apply_filters( 'ced_cwsm_alter_is_have_to_render_meta_field', $isHaveToRender );
		return $isHaveToRender;
	}

	/**
	 * This function fetches wholesale price of a product.
	 *
	 * @name getWholesalePrice()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function getWholesalePrice( $productId ) {
		$wholesalePrice = get_post_meta( $productId, 'ced_cwsm_wholesale_price', true );
		$wholesalePrice = apply_filters( 'ced_cwsm_alter_wholesale_price', $wholesalePrice, $productId );
		return $wholesalePrice;
	}

	/**
	 * This function fetches minimum quanity to buy of a product.
	 *
	 * @name getMinQtyToBuy()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function getMinQtyToBuy( $productId ) {
		$min_qty_condition_enabled = get_option( 'ced_cwsm_enable_minQty' );
		if ( empty( $min_qty_condition_enabled ) || 'no' == $min_qty_condition_enabled ) {
			return false;
		}

		if ( $min_qty_condition_enabled ) {
			$min_qty_picker_type = get_option( 'ced_cwsm_radio_minQty_picker' );
			if ( 'ced_cwsm_radio_common_minQty' == $min_qty_picker_type ) {
				$min_qty = (int) get_option( 'ced_cwsm_central_min_qty' );
			} elseif ( 'ced_cwsm_radio_individual_minQty' == $min_qty_picker_type ) {
				$min_qty = (int) get_post_meta( $productId, 'ced_cwsm_min_qty_to_buy', true );
				$min_qty = apply_filters( 'ced_cwsm_alter_min_qty_to_buy', $min_qty, $productId );
			}
		}
		$min_qty = apply_filters( 'ced_cwsm_alter_min_qty_to_buy_final', $min_qty, $productId );
		return (int) $min_qty;
	}

	/**
	 * This function handles whether to show wholesale price or not to current user.
	 *
	 * @name isActiveUserCanSeeWholesalePrice()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function isActiveUserCanSeeWholesalePrice() {
		if ( get_option( 'ced_cwsm_radio_whoCanSee' ) == '_cwsm_radio_showWSuser' ) {
			$wholesaleRolesArray   = $this->getWholesaleRoleKeys();
			$wholesaleRolesArray[] = 'ced_cwsm_wholesale_user';

			$current_user    = wp_get_current_user();
			$isWholesaleUser = false;
			foreach ( $current_user->roles as $userRole ) {
				if ( in_array( $userRole, $wholesaleRolesArray ) ) {
					$isWholesaleUser = true;
					break;
				}
			}
			return $isWholesaleUser;
		}
		return true;
	}

	/**
	 * This function fetches wholesale-role keys.
	 *
	 * @name getWholesaleRoleKeys()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function getWholesaleRoleKeys() {
		$wholesaleRolesArray = array();
		$previousRoles       = get_option( 'ced_cwsm_wholesaleRolesArray', false );
		if ( is_array( $previousRoles ) && ! empty( $previousRoles ) ) {
			foreach ( $previousRoles as $previousRole ) {
				$wholesaleRolesArray[] = $previousRole['key'];
			}
		}
		return $wholesaleRolesArray;
	}

	/**
	 * This function checks is plugin is activated or not using the plugins settings provided.
	 *
	 * @name is_CWSM_plugin_active()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function is_CWSM_plugin_active() {
		if ( get_option( 'ced_cwsm_enable_wholesale_market' ) == 'no' ) {
			return false;
		}
		return true;
	}

	/**
	 * This function identifies whether current user is wholesale user or not.
	 *
	 * @name isCurrentUserIsWholesaleUser()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function isCurrentUserIsWholesaleUser() {
		$wholesaleRolesArray   = $this->getWholesaleRoleKeys();
		$wholesaleRolesArray[] = 'ced_cwsm_wholesale_user';

		$current_user    = wp_get_current_user();
		$isWholesaleUser = false;
		foreach ( $current_user->roles as $userRole ) {
			if ( in_array( $userRole, $wholesaleRolesArray ) ) {
				$isWholesaleUser = true;
				break;
			}
		}
		return $isWholesaleUser;
	}

	/**
	 * This function get current wholesale-user's role.
	 *
	 * @name getCurrentWholesaleUserRole()
	 *
	 * @link  http://www.cedcommerce.com/
	 */
	public function getCurrentWholesaleUserRole() {
		$wholesaleRolesArray   = $this->getWholesaleRoleKeys();
		$wholesaleRolesArray[] = 'ced_cwsm_wholesale_user';
		$current_user          = wp_get_current_user();
		$wholesaleUserRole     = 'ced_cwsm_wholesale_user';
		foreach ( $current_user->roles as $userRole ) {
			if ( in_array( $userRole, $wholesaleRolesArray ) ) {
				$wholesaleUserRole = $userRole;
				break;
			}
		}
		$wholesaleUserRole = apply_filters( 'ced_cwsm_alter_wholesaleUserRole', $wholesaleUserRole );
		return $wholesaleUserRole;
	}
}

/**
 * Create object instance for this class.
 */
global $globalCWSM;
$globalCWSM = CED_CWSM_Global_Functions::getInstance();

