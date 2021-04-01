<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://cedcommerce.com/
 * @since      1.0.0
 *
 * @package    woocommerce_smsa_shipping_integration
 * @subpackage woocommerce_smsa_shipping_integration/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    woocommerce_smsa_shipping_integration
 * @subpackage woocommerce_smsa_shipping_integration/admin
 * @author     cedcommerce <webmaster@cedcommerce.com>
 */
class woocommerce_smsa_shipping_integration_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $woocommerce_smsa_shipping_integration    The ID of this plugin.
	 */
	private $woocommerce_smsa_shipping_integration;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $woocommerce_smsa_shipping_integration       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $woocommerce_smsa_shipping_integration, $version ) {
		// error_reporting(~0);
		// 	ini_set('display_errors', 1);

		$this->woocommerce_smsa_shipping_integration = $woocommerce_smsa_shipping_integration;
		$this->version = $version;
		
		if(!defined('CED_ADMIN_PATH')){
			define('CED_ADMIN_PATH', plugin_dir_path( __FILE__ ));
		}
		add_action('wp_ajax_ced_smsa_send_data',array($this,'ced_smsa_send_data'));
		add_action('wp_ajax_ced_smsa_save_seller',array($this,'ced_smsa_save_seller'));
		add_action('wp_ajax_ced_smsa_get_pfd',array($this,'ced_smsa_get_pfd'));
		add_action('wp_ajax_ced_smsa_cancle_shipment',array($this,'ced_smsa_cancle_shipment'));
		add_action('wp_ajax_ced_smsa_get_pfd_download',array($this,'ced_smsa_get_pfd_download'));
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in woocommerce_smsa_shipping_integration_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The woocommerce_smsa_shipping_integration_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style( $this->woocommerce_smsa_shipping_integration, plugin_dir_url( __FILE__ ) . 'css/woocommerce_smsa_shipping_integration-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in woocommerce_smsa_shipping_integration_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The woocommerce_smsa_shipping_integration_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		
		wp_enqueue_script( $this->woocommerce_smsa_shipping_integration, plugin_dir_url( __FILE__ ) . 'js/woocommerce_smsa_shipping_integration-admin.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->woocommerce_smsa_shipping_integration,'smsa_ajax_request', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

	}
	
	/**
	 * Register the menu and submenus for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function admin_menus(){
		require_once 'settings/class-woocommerce_smsa_shipping_integration-settings.php';
	}

	public function add_meta_boxes(){

		global $post;
		
		$post_type = get_post_type($post);
		$order_types = wc_get_order_types();
		
		if(in_array($post_type, $order_types)){
			
			add_meta_box( 'smsa-order-manager', __( 'Send Data', 'ced-umb-smsa' ) . wc_help_tip( __( 'Send product data to SMSA server for tracking number.', 'ced-umb-smsa' ) ), array($this,'order_manager_box') );
		}

	}

	public function order_manager_box(){

		global $post;
		$order_id = isset($post->ID) ? intval($post->ID) : '';
		if(!is_null($order_id)){
			$order = wc_get_order($order_id);
			if(is_wp_error($order)){
				
			}else if($order==''){
				
			}else{
				// echo"<pre>";
				// print_r($order);die;
				$tracking_number = get_post_meta($order_id , 'ced_smsa_awno' , true);
				if(!empty($tracking_number)){
					echo"<span>Tracking Number : $tracking_number</span>";
					echo"<button type='button' data_order_cancle=".$tracking_number." class='button-primary ced_smsa_cancle_shipment' >Cancle Shipment</button>";
					$smsa_file_name = get_post_meta($order_id , 'smsa_shipping_pfd' , true);
					if (!empty($smsa_file_name)) {
						echo"<button type='button' data_order_get_pdf=".$smsa_file_name." class='button-primary ced_order_get_pdf' data_order_id=".$order_id." >Get Pdf</button>";
					}else{
						echo"<button type='button' data_order_get_pdf=".$tracking_number." class='button-primary ced_smsa_get_invoice' data_order_id=".$order_id." >Get Invoice</button>";
					}
				}else{
					echo"<button type='button' data-order=".$order_id." class='button-primary ced_smsa_send_data' >Send Details for Invoice</button>";
				}
				echo '<span class="show_loader_ajx"><img src='.home_url().'/wp-admin/images/spinner-2x.gif alt="" width="" height=""></span>';
			}
		}



	}

	public function ced_smsa_send_data($id =''){
		if(isset($id) && !empty($id)){
			$woo_order_id = $id;
		}else{
			$woo_order_id = $_POST['oID'];
		}
		if(!empty($woo_order_id)){
			$order = wc_get_order($woo_order_id);
			$order_detail = $order->get_data();
			if($order_detail['payment_method'] == 'cod'){
				$ced_cod = $order_detail['total'];
			}else{
				$ced_cod = 0;
			}
			$order_first_name = $order->get_shipping_first_name();
			if(empty($order_first_name)){
				$order_first_name = $order->get_billing_first_name();

			}

			$order_last_name = $order->get_shipping_last_name();
			if(empty($order_last_name)){
				$order_last_name = $order->get_billing_last_name();
			}

			$shipping_address_1 = $order->get_shipping_address_1();
			if(empty($shipping_address_1)){
				$shipping_address_1 = $order->get_billing_address_1();
			}

			$shipping_address_2 = $order->get_shipping_address_2();
			if(empty($shipping_address_2)){
				$shipping_address_2 = $order->get_billing_address_2();
			}

			$shipping_city = $order->get_shipping_city();
			if(empty($shipping_city)){
				$shipping_city = $order->get_billing_city();
			}

			$shipping_postcode = $order->get_shipping_postcode();
			if(empty($shipping_postcode)){
				$shipping_postcode = $order->get_billing_postcode();
			}

			$shipping_country = $order->get_shipping_country();

			if(empty($shipping_country))
				$shipping_country = 'KSA';
			$shipping_email = $order->get_billing_email();
			$shipping_mobile = $order->get_billing_phone();
			$seller_detal = get_option('seller_smsa_details');
			$seller_detal = json_decode($seller_detal);
			$passKey = isset($seller_detal->passKey) ? $seller_detal->passKey : '';

			$ced_smsa_sName = isset($seller_detal->ced_smsa_sName) ? $seller_detal->ced_smsa_sName : '';
			$ced_smsa_sContact = isset($seller_detal->ced_smsa_sContact) ? $seller_detal->ced_smsa_sContact : '';
			$ced_smsa_Addr1 = isset($seller_detal->ced_smsa_Addr1) ? $seller_detal->ced_smsa_Addr1 : '';
			$ced_smsa_sCity = isset($seller_detal->ced_smsa_sCity) ? $seller_detal->ced_smsa_sCity : '';
			$ced_smsa_sPhone = isset($seller_detal->ced_smsa_sPhone) ? $seller_detal->ced_smsa_sPhone : '';
			$ced_smsa_sCntry = isset($seller_detal->ced_smsa_sCntry) ? $seller_detal->ced_smsa_sCntry : '';
			$params = array();
            		//shipper parameters
			$params["passKey"] = $passKey;
			$params["refNo"] = $woo_order_id;
			$params["sentDate"] = date("d/m/Y");
			$params["idNo"] = '';
			$params["cName"] = $order_first_name.' '.$order_last_name ;
			$params["cntry"] = $shipping_country;
			$params["cCity"] = $shipping_city;
			$params["cZip"] = $shipping_postcode;
			$params["cPOBox"] = '';
			$params["cMobile"] = $shipping_mobile;
			$params["cTel1"] = $shipping_mobile;
			$params["cTel2"] = '';
			$params["cAddr1"] =$shipping_address_1;
			$params["cAddr2"] = $shipping_address_2;
			$params["shipType"] = 'DLV';
			$params["PCs"] = 1;
			$params["cEmail"] = $shipping_email;
			$params["carrValue"] = 0;
			$params["carrCurr"] = '';
			$params["codAmt"] = $ced_cod;
			$params["weight"] = 1;
			$params["custVal"] = '';
			$params["custCurr"] = '';
			$params["insrAmt"] = 0;
			$params["insrCurr"] = '';
			$params["itemDesc"] = '';
			$params["sName"] = $ced_smsa_sName;
			$params["sContact"] = $ced_smsa_sContact;
			$params["sAddr1"] = $ced_smsa_Addr1;
			$params["sAddr2"] = '';
			$params["sCity"] = $ced_smsa_sCity;
			$params["sPhone"] = $ced_smsa_sContact;
			$params["sCntry"] = $ced_smsa_sCntry;/*SA*/
			$params["prefDelvDate"] = '';
			$params["gpsPoints"] = '';
			/*test*/
			// $params["cName"] = 'John';
			// $params["cntry"] = 'SA';
			// $params["cCity"] = 'Riyadh';
			// $params["cZip"] = 14972;
			// $params["cPOBox"] = 3574;
			// $params["cMobile"] = 0533173400;
			// $params["cTel1"] = 011271692;
			// $params["cTel2"] = /*$customer_billing_address->getTelephone();*/
			// $params["cAddr1"] = 'Dirab';
			// $params["cAddr2"] = 'Al Ashab Street';
			// $params["shipType"] = 'DLV';
			// $params["PCs"] = 1;
			// $params["cEmail"] = 'amer@shamil.com';
			// $params["carrValue"] = 0;
			// $params["carrCurr"] = '';
			// $params["codAmt"] = 0;
			// $params["weight"] = 1;
			// $params["custVal"] = 43;
			// $params["custCurr"] = '';
			// $params["insrAmt"] = 0;
			// $params["insrCurr"] = '';
			// $params["itemDesc"] = 'trtrt';
			// $params["sName"] = 'Amer';
			// $params["sContact"] = 011271692;
			// $params["sAddr1"] = '';
			// $params["sAddr2"] = '';
			// $params["sCity"] = 'Riyadh';
			// $params["sPhone"] = 011271692;
			// $params["sCntry"] = 'SA';
			// $params["prefDelvDate"] = '';
			// $params["gpsPoints"] = '';
			// print_r($params);
			// die;
			try {
				$client = new SoapClient('http://track.smsaexpress.com/SECOM/SMSAwebService.asmx?wsdl');
				$result = $client->addShipment($params);
				$awbno = $result->addShipmentResult;
				if(!empty($awbno) && is_numeric($awbno)){
					update_post_meta($woo_order_id , 'ced_smsa_awno' , $awbno);
					$order->update_status('completed');
					if(isset($id) && !empty($id)){
						return $id;
					}else{

						echo"Success";
					}
				}else{
					if(isset($id) && !empty($id)){
						return $awbno;
					}else{

						echo $awbno;
					}
				}
				wp_die();
			}
			catch(Exception $e)
			{
				print_r($e->getMessage());die;
			}
		}
		return;
	}


	public function ced_smsa_get_pfd(){
		if(!empty($_POST['tracking_num']) || $_GET['action'] == "ced_smsa_get_pfd"){
			$wpuploadDir	=	wp_upload_dir();
			$baseDir		=	$wpuploadDir['basedir'];
			$uploadDir		=	$baseDir . '/smsa/invoice';

			if (! is_dir($uploadDir))
			{
				mkdir( $uploadDir, 0777 ,true);
			}
			$seller_detal = get_option('seller_smsa_details');
			$seller_detal = json_decode($seller_detal);
			$passKey = isset($seller_detal->passKey) ? $seller_detal->passKey : '';
			$client = new SoapClient('http://track.smsaexpress.com/SECOM/SMSAwebService.asmx?wsdl');
			$trackng_num = '';
			$orderId = '';
			if ($_GET['action'] == "ced_smsa_get_pfd") {
				$trackng_num = $_GET['tracking_number'];
				$orderId = $_GET['oid'];
			}else{
				$trackng_num = $_POST['tracking_num'];
				$orderId = $_POST['orderId'];
			}
			$params['awbNo'] = $trackng_num;
			$params['passKey'] = $passKey;
			$result = $client->getpdf($params);
			$file_path 			= 	$uploadDir . '/Shipment-'.$orderId.'.pdf';
			$file_name			=	'Shipment-'.$orderId.'.pdf';
			update_post_meta($orderId , 'smsa_shipping_pfd' , $file_name);
			$myfile 			= 	fopen($file_path, "w") ;
			fwrite($myfile, $result->getPDFResult);
			fclose($myfile);
			if($_GET['action'] == 'ced_smsa_get_pfd'){
				wp_redirect(admin_url("edit.php?post_type=shop_order"));
				die;
			}
			wp_die();
		}
	}

	public function ced_smsa_get_pfd_download(){
		if (!empty($_POST['file_name'])) {
			$file_name = $_POST['file_name'];
			$return_data = home_url().'/wp-content/uploads/smsa/invoice/'.$file_name;
			echo $return_data;
			wp_die();
		}else{
			$pdfName = $_GET['filename'];
			$file = wp_upload_dir()['basedir'].'/smsa/invoice/'.$pdfName;
				header('Content-Description: File Transfer');
			   	header('Content-Type: application/octet-stream');
		  	 	header('Content-Disposition: attachment; filename="'.basename($file).'"');
			   	header('Expires: 0');
			   	header('Cache-Control: must-revalidate');
			   	header('Pragma: public');
			   	header('Content-Length: ' . filesize($file));
			   	readfile($file);
				exit;
		}

	}

	public function ced_smsa_cancle_shipment(){
		$seller_detal = get_option('seller_smsa_details');
		$seller_detal = json_decode($seller_detal);
		$passKey = isset($seller_detal->passKey) ? $seller_detal->passKey : '';
		try {
			$params = array();
			$params['awbNo'] = $_POST['tracking_num'];
			$params['passkey'] = $passKey;
			$params['reas'] = 'cancel';
			$client = new SoapClient('http://track.smsaexpress.com/SECOM/SMSAwebService.asmx?wsdl');
			$result = $client->cancelShipment($params);
			echo $result->cancelShipmentResult;
			wp_die();
		}catch(Exception $e)
		{
			print_r($e->getMessage());die;
		}

	}

	public function ced_smsa_save_seller(){
		$seller_smsa_details = json_encode($_POST);
		update_option('seller_smsa_details' , $seller_smsa_details);
		echo json_encode(array( 'status' => '200','message'=>'Setting details saved successfully' ));
        wp_die();
	}

	public function mwb_smsa_new_order_column($columns){
		$columns['ced_sma_invoice'] = __('SMSA Invoice','ced_smsa');
		return $columns;
	}

	public function mwb_smsa_wc_add_data_invoice_column_content($column){
		global $post,$the_order;

		if ( empty( $the_order ) || $the_order->get_id() !== $post->ID ) {
			$the_order = wc_get_order( $post->ID );
		}
		$orderId = $the_order->get_id();
		switch ( $column ) 
		{
			case "ced_sma_invoice" : 
			$tracking_number = get_post_meta($orderId , 'ced_smsa_awno' , true);
			if(!empty($tracking_number)){
				// echo"<span>Tracking Number : $tracking_number</span>";
				// echo"<button type='button' data_order_cancle=".$tracking_number." class='button-primary ced_smsa_cancle_shipment' >Cancle Shipment</button>";
				$smsa_file_name = get_post_meta($orderId , 'smsa_shipping_pfd' , true);
				if (!empty($smsa_file_name)) {
					echo"<a href='admin-ajax.php?action=ced_smsa_get_pfd_download&filename=$smsa_file_name&oid=$orderId' class='button-primary' data_order_id=".$orderId." >Download Pdf</a>";
				}else{
					echo"<a href='admin-ajax.php?action=ced_smsa_get_pfd&tracking_number=$tracking_number&oid=$orderId' class='button-primary' data_order_id=".$orderId." >Get Invoice</a>";
				}
			}else{
				$error_smsa_shipping = get_post_meta($orderId , 'error_smsa_shipping' , true);
				if(isset($error_smsa_shipping) &&!empty($error_smsa_shipping)){
					echo $error_smsa_shipping;
				}else{

					echo "Shipment Pending";
				}
				// echo"<button type='button' data-order=".$orderId." class='button-primary ced_smsa_send_data' >Send Details for Invoice</button>";
			}
			echo '<span id="show_loader_ajx_'.$orderId.'" class="show_loader_ajx"><img src='.home_url().'/wp-admin/images/spinner-2x.gif alt="" width="" height=""></span>';
			break;
		}
	}

	public function mwb_smsa_change_order_bulk_actions($array){
		$array['ced_generate_shipment'] = __('Add Shipment SMSA','ced_smsa');
		return $array;
	}

	public function mwb_smsa_edit_order_bulk_actions($redirect_to, $action, $ids){
		if(isset($action) && $action == 'ced_generate_shipment'){
			foreach($ids as $woo_id){
				$response = $this->ced_smsa_send_data($woo_id);
				if(!is_numeric($response)){
					update_post_meta($woo_id , 'error_smsa_shipping' , $response);
				}
			}
		}
		// print_r($response);die;
		return esc_url_raw( $redirect_to );	
	}


  
	function ced_send_tracking_id_mail( $order, $sent_to_admin, $plain_text, $email ) {



		if(!empty($order)) {
			$order_id = $order->get_id();
			$order_detail = wc_get_order( $order_id );
			$order_status  = $order_detail->get_status(); 
			$smsa_tracking_number = !empty(get_post_meta($order_id, 'ced_smsa_awno', true)) ? get_post_meta($order_id, 'ced_smsa_awno', true) :'';
			if($order_status == 'completed' && $smsa_tracking_number !== '') {
				echo '<h2 class="email-upsell-title">Tracking Number : <a href="https://www.smsaexpress.com/trackingdetails?tracknumbers='.$smsa_tracking_number.'">'.$smsa_tracking_number.'</a></h2><p class="email-upsell-p">Tracking number help you to track your order status after shipping.</p>';
			}
		}
			
		
	}
}
