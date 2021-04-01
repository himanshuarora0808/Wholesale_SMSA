<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ced_CWSM_Product_Addon_Licence_Settings {

	private static $_instance;

	public static function getInstance() {
		self::$_instance = new self();
		if ( ! self::$_instance instanceof self ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	* Licence callback
	*/
	/*
	public function ced_cwsm_product_addon_validate_licensce_callback() {

		$return_response               = array();
		$license_arg                   = array();
		$license_arg['domain_name']    = isset( $_POST['domain_name'] ) ? sanitize_text_field( $_POST['domain_name'] ) : '';
		$license_arg['module_name']    = isset( $_POST['module_name'] ) ? sanitize_text_field( $_POST['module_name'] ) : '';
		$license_arg['version']        =  isset( $_POST['frame_version'] ) ? sanitize_text_field( $_POST['frame_version'] ) : '';
		$license_arg['php_version']    = isset( $_POST['php_version'] ) ? sanitize_text_field( $_POST['php_version'] ) : '';
		$license_arg['framework']      = isset( $_POST['frame_name'] ) ? sanitize_text_field( $_POST['frame_name'] ) : '';
		$license_arg['admin_name']     = isset( $_POST['admin_name'] ) ? sanitize_text_field( $_POST['admin_name'] ) : '';
		$license_arg['admin_email']    = isset( $_POST['admin_email'] ) ? sanitize_text_field( $_POST['admin_email'] ) : '';
		$license_arg['module_license'] = isset( $_POST['license_key'] ) ? sanitize_text_field( $_POST['license_key'] ) : '';
		$license_arg['edition']        = '';

		/*$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => 'http://cedcommerce.com/licensing/validate',
			CURLOPT_USERAGENT => 'Cedcommerce',
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $license_arg
		));

		$res = curl_exec($curl);
		curl_close($curl);*/

		/*
		$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://cedcommerce.com/licensing/validate');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_POST, 1);
			// Edit: prior variable $postFields should be $postfields;
			curl_setopt($ch, CURLOPT_POSTFIELDS, $license_arg);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only!
			$res = curl_exec($ch);

			curl_close($ch);

			//////////////////////

		$response = json_decode($res, true);

		$cedcommerce_hash = '';
		if (isset($response['hash']) && isset($response['level'])) {
			$cedcommerce_hash  = $response['hash'];
			$cedcommerce_level = $response['level'];
			$i                 =1;
			for ($i=1;$i<=$cedcommerce_level;$i++) {
				$cedcommerce_hash = base64_decode($cedcommerce_hash);
			}
		}

		$cedcommerce_response = json_decode($cedcommerce_hash, true);

		if ($cedcommerce_response['domain'] == $license_arg['domain_name'] && $cedcommerce_response['license'] == $license_arg['module_license'] && $cedcommerce_response['module_name'] == $license_arg['module_name']) {
			update_option('ced_cwsm_product_addon_license', $res);
			update_option('ced_cwsm_product_addon_license_key', $cedcommerce_response['license']);
			update_option('ced_cwsm_product_addon_license_module', $cedcommerce_response['module_name']);
			$return_response['response'] = 'success';
		} else {
			$return_response['response'] = 'failure';
		}
		echo json_encode($return_response);
		wp_die();
	}*/

}

