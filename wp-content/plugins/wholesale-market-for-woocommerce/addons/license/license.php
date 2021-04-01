<?php

global $wp_version;
global $current_user;
?>

<style>
.license_loading_image {
	height: 28px;
	vertical-align: middle;
	display:none;
}
</style>
<!--script src="<?php // echo esc_url( CED_CWMAD_PRODUCT_ADDON_PLUGIN_DIR_URL . 'addons/license/cedc_cwsm_license_admin_license.js'); ?>"></script-->
<?php
wp_register_script( 'custom-script', CED_CWMAD_PRODUCT_ADDON_PLUGIN_DIR_URL . 'addons/license/cedc_cwsm_license_admin_license.js', array(), '1.0.0', true );
wp_enqueue_script( 'custom-script' );

?>
<hr/>
<table class="form-table">
	<tbody>
		<tr valign="top">
			<th class="titledesc" scope="row">
				<label for=""><?php esc_html_e( 'Enter License Key', 'wholesale-market' ); ?></label>
			</th>
			<td class="forminp">
				<fieldset>
					<?php $http_host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( $_SERVER['HTTP_HOST'] ) : ''; ?>
					<input type="hidden" id="ced_cwsm_product_addon_domain_name" value="<?php echo esc_attr( $http_host ); ?>">
					<input type="hidden" id="ced_cwsm_product_addon_module_name" value="wholesale-market-product-addon">
					<input type="hidden" id="ced_cwsm_product_addon_framework_version" value="<?php echo esc_attr( $wp_version ); ?>">
					<input type="hidden" id="ced_cwsm_product_addon_phpversion" value="<?php echo esc_attr( phpversion() ); ?>">
					<input type="hidden" id="ced_cwsm_product_addon_framework" value="wordpress">
					<input type="hidden" id="ced_cwsm_product_addon_admin_name" value="<?php echo esc_attr( $current_user->user_login ); ?>">
					<input type="hidden" id="ced_cwsm_product_addon_admin_email" value="<?php echo esc_attr( $current_user->user_email ); ?>">
					<input type="text" id="ced_cwsm_product_addon_license_key" class="input-text regular-input">
					<input type="button" value="Validate" class="button-primary" id="ced_cwsm_product_addon_save_license">
					<img class="license_loading_image" src="<?php echo esc_url( CED_CWMAD_PRODUCT_ADDON_PLUGIN_DIR_URL . 'addons/license/loading.gif' ); ?>">
					<b class="licennse_notification"></b>
				</fieldset>
			</td>
		</tr>
	</tbody>
</table>


