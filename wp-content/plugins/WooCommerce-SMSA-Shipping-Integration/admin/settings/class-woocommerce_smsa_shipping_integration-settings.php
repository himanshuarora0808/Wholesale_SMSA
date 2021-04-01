<?php
if(!class_exists('cedBasicframeworkAdminSettings')){
	 class cedBasicframeworkAdminSettings{
	 	
	 	protected $loader;
	 	
	 	public function __construct(){
	 		
	 		self::loadDependencies();
	 	}
	 	
	 	public function loadDependencies(){
	 		add_menu_page('ced_woocommerce_smsa_shipping_integration', 'Woocommerce SMSA Shipping', 'manage_woocommerce', 'ced_woocommerce_smsa_shipping_integration',array($this,'ced_plugi_name_settings'));
	 	}
	 	
	 	public function ced_plugi_name_settings(){
	 		require_once CED_ADMIN_PATH.'partials/woocommerce_smsa_shipping_integration-admin-display.php';
	 	}
	 	
	 }
}
new cedBasicframeworkAdminSettings;