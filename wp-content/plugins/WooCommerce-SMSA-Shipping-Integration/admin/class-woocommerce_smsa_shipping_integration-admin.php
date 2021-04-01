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
// ini_set('display_errors', 1);

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

add_action('wp_ajax_ced_smsa_save_add_pickup',array($this,'ced_smsa_save_add_pickup')); // ajax to save pickup add
add_action('wp_ajax_ced_smsa_delete_add_pickup',array($this,'ced_smsa_delete_add_pickup')); // ajax to delete pickup add
wp_schedule_event( time(), 'every_twelve_hours', array($this,'symbiocards_hourly_event'));
add_action( 'wp', array($this, 'symbiocards_activation' ));
//add_action( 'symbiocards_hourly_event', array($this, 'ced_customer_total_order'));
//add_filter( 'cron_schedules', array($this,'myprefix_custom_cron_schedule') );
add_action( 'wp_footer', array($this, 'ced_customer_total_order'));
add_action( 'save_post', array($this,'ced_save_selected_pickup_address'), 10, 1 );	
}

function myprefix_custom_cron_schedule( $schedules ) {
    $schedules['every_twelve_hours'] = array(
        'interval' => 43200, // Every 6 hours
        'display'  => __( 'Every 12 hours' ),
    );
    return $schedules;
}


function symbiocards_activation() {
	if ( !wp_next_scheduled( 'symbiocards_hourly_event' ) ) {
		wp_schedule_event( time(), 'every_twelve_hours', 'symbiocards_hourly_event' );
	}
}



public function update_symbiocards() {
	wp_mail( 'jacobwilliam@cedcommerce.com', '[symbiostock_network_update] Network Symbiocards Updated', 'Network Symbiocards Updated - ' . current_time( 'mysql' ) );
// echo "Hello";
// die("--*--");
}


public function ced_customer_total_order() {	  
	$seller_detal = get_option('seller_smsa_details');
	$seller_detal = json_decode($seller_detal);
	$passKey = isset($seller_detal->passKey) ? $seller_detal->passKey : 'ff';

	$customer_orders = get_posts( array(
		'numberposts' => - 1,
// 'meta_key'    => '_customer_user',
// 'meta_value'  => get_current_user_id(),
		'post_type'   => array( 'shop_order' ),
		'post_status' => array( 'wc-processing', 'processing' ),
		'date_query' => array(
			'after' => date('Y-m-d', strtotime('-10 days')),
			'before' => date('Y-m-d', strtotime('today')) 
		)
	) );
	
    // print_r($customer_orders);
	foreach ($customer_orders as $key => $value) {
		
        // $value->ID;
		$trak_nbr = get_post_meta($value->ID , 'ced_smsa_awno' , true);

		

		//API CALL 
		// if($trak_nbr){	
		 $soapClient = new SoapClient('http://track.smsaexpress.com/SECOM/SMSAwebService.asmx?wsdl',array('soap_version' => SOAP_1_1));			
		 try {

			$params = array();
			$params["awbNo"] = '290110727067'; //$trak_nbr; // '290110727067';
			$params["passkey"] = 'aLsh85@c';//$passKey; // 'aLsh85@c';
			$auth_call = $soapClient->getTracking($params);	
			print_r($auth_call->getTrackingResult);
			//  change status of order delivered
			
			if(preg_match('(PROOF OF DELIVERY CAPTURED)', $auth_call->getTrackingResult->any)){  
				$order = wc_get_order( $value->ID );
				$order->update_status( 'completed', '', true );				
			} 	
			
		} catch (SoapFault $fault) {
			//print_r($fault);
			die();
		}
	  //}	 
    
	}
	
}
public function ced_track_smsa_order(){
	?>
	<form method="POST">
	<input type='submit' name='track' value='Track Order' class='button-primary'><br>
	</form>
	<?php
	$trak_nbr = get_post_meta($value->ID , 'ced_smsa_awno' , true);
	// if($trak_nbr){	
	 $soapClient = new SoapClient('http://track.smsaexpress.com/SECOM/SMSAwebService.asmx?wsdl',array('soap_version' => SOAP_1_1));			
	 try {

		$params = array();
		$params["awbNo"] = '290110727067'; //$trak_nbr; // '290110727067';
		$params["passkey"] = 'aLsh85@c';//$passKey; // 'aLsh85@c';
		$auth_call = $soapClient->getTracking($params);	
		print_r($auth_call->getTrackingResult);	
	} catch (SoapFault $fault) {
		//print_r($fault);
		die();
	}
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

		add_meta_box( 'smsa-order-province-box', __( 'Pickup Province', 'ced-umb-smsa' ) , array($this,'order_manager_pickup_province_box') );
		add_meta_box( 'smsa-order-province-box', __( 'Track Shipment', 'ced-umb-smsa' ) , array($this,'ced_track_smsa_order') );		
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
			$tracking_number = get_post_meta($order_id , 'ced_smsa_awno' , true);
			if(!empty($tracking_number)){
				echo"<span>Tracking Number : $tracking_number</span>";
				echo"<button type='button' data-orders=".$order_id." data_order_cancle=".$tracking_number." class='button-primary ced_smsa_cancle_shipment' >Cancle Shipment</button>";
				$smsa_file_name = get_post_meta($order_id , 'smsa_shipping_pfd' , true);
				if (!empty($smsa_file_name)) {
					echo"<button type='button' data_order_get_pdf=".$smsa_file_name." class='button-primary ced_order_get_pdf' data_order_id=".$order_id." >Get Pdf</button>";
				}else{
					echo"<button type='button' data_order_get_pdf=".$tracking_number." class='button-primary ced_smsa_get_invoice' data_order_id=".$order_id." >Get Invoice</button>";
				}
			}else{			

				$sel_pickup_address = get_post_meta( $post->ID, 'ced_smsa_pickup_address_data', true ) ? get_post_meta( $post->ID, 'ced_smsa_pickup_address_data', true ) : '';				

				$pickup_add = unserialize(get_option('seller_pickup_add'));?>

				<input type="hidden" name="ced_pickup_add_nonce" value="<?php echo wp_create_nonce() ?>">
				<p>
					<label for="pickup_add_meta_box_type">Select Pickup Address: </label>
					<select name='ced_smsa_pickup_address_data' id='ced_smsa_pickup_address_data'>
						<?php 
						if(isset($pickup_add) && !empty($pickup_add)){ 
							foreach ($pickup_add as $pickupAdds):
								$pickupAddVal = json_decode($pickupAdds, true);
								$complete_pickup_address = $pickupAddVal['pickupAdd'].'-'.$pickupAddVal['pickupCity'].'-'.$pickupAddVal['pickupProvince'];
								?>
								<option value="<?php echo esc_attr($complete_pickup_address); ?>" <?php selected( $sel_pickup_address, esc_attr($complete_pickup_address)); ?>><?php echo esc_html($complete_pickup_address); ?></option>
							<?php endforeach; ?>
						</select>
					</p>				
					<?php
				}

				echo"<button type='button' data-order=".$order_id." class='button-primary ced_smsa_send_data' >Send Details for Invoice</button>";			
			}
				$ship_status = get_post_meta($order_id , 'ced_smsa_ship_note' , true);
				if(!empty($ship_status)){
					echo $ship_status;
				}
				echo '<span class="show_loader_ajx"><img src='.home_url().'/wp-admin/images/spinner-2x.gif alt="" width="" height=""></span>';
			}
		}

	}

	public function order_manager_pickup_province_box(){
		global $post;
		$sel_pickup_province = get_post_meta( $post->ID, 'ced_smsa_pickup_province', true ) ? get_post_meta( $post->ID, 'ced_smsa_pickup_province', true ) : '';
		$pickup_add = unserialize(get_option('seller_pickup_add'));?>
		<p>
			<label for="pickup_add_pickup_box_type">Select Pickup Province: </label>
			<select name='ced_smsa_pickup_province' id='ced_smsa_pickup_province'>
				<?php 
				foreach ($pickup_add as $pickupProvince) {
					$pickupAddProvinceVal = json_decode($pickupProvince, true);
					$complete_pickup_province = $pickupAddProvinceVal['pickupProvince']; ?>
					<option value="<?php echo esc_attr($complete_pickup_province); ?>" <?php selected( $sel_pickup_province, esc_attr($complete_pickup_province)); ?>><?php echo esc_html($complete_pickup_province); ?></option>
				<?php }?>      
			</select>
		</p>

	<?php }

	function ced_save_selected_pickup_address( $post_id ) {

		if ( ! isset( $_POST[ 'ced_pickup_add_nonce' ] ) ) {
			return $post_id;
		}
		$nonce = $_REQUEST[ 'ced_pickup_add_nonce' ];

//Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce ) ) {
			return $post_id;
		}


		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

// Check the user's permissions.
		if ( 'shop_order' == $_POST[ 'post_type' ] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}
		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
		}       

// Sanitize user input  and update the meta field in the database.
		update_post_meta( $post_id, 'ced_smsa_pickup_address_data', $_POST[ 'ced_smsa_pickup_address_data' ] );
		update_post_meta( $post_id, 'ced_smsa_pickup_province', $_POST[ 'ced_smsa_pickup_province' ] );

	}

	public function ced_smsa_send_data($id =''){
		
		if(isset($id) && !empty($id)){
			$woo_order_id = $id;
		}else{
			$woo_order_id = $_POST['oID'];
		}
		if(!empty($woo_order_id)){
	        $pack_nbr = $POST['pack_nbr']; 
	        $pickupAdd = $_POST['pickupAdd'];
			$pickupCity = $_POST['pickupCity'];
			$pickupProvince = $_POST['pickupProvince'];            

			/*$complete_address = get_post_meta($woo_order_id,'ced_smsa_pickup_address_data',true);
			$complete_address = explode("-",$complete_address);*/

			update_post_meta( $woo_order_id, 'ced_smsa_pickup_address_data', $pickupAdd.'-'.$pickupCity.'-'.$pickupProvince );
		
			$order = wc_get_order($woo_order_id);
			$order_detail = $order->get_data();
			$order_items = $order->get_items();
			$wt_total = '';
			$od_quan = '';
			$pack_nbr = $_POST['pack_nbr'];
			$wt_total = (float) $wt_total;
			$od_quan = (int) $od_quan;
			foreach ( $order_items as $item ) {
				$product_id = $item->get_product_id();
				$var_id = $item->get_variation_id();

// print_r($product_id);
// echo "---";
// print_r($var_id);
				if(!isset($var_id) || $var_id == "0"){
					$var_id = $product_id;
				}

				$product = wc_get_product( $var_id );
				$item_quantity = $item->get_quantity(); 
				$p_wt = $product->get_weight();

// echo "wt val";
// print_r($p_wt);
// print_r($product);

				$od_quan += $item_quantity;
				if(isset($p_wt) && !empty($p_wt)){
					$wt_total += $p_wt * $item_quantity;
				}
			}
// die("====");

			if($wt_total == '0'){
				$wt_total = '1';
			}
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

			$ced_smsa_Addr1 = $pickupAdd; // address
			$ced_smsa_sCity = $pickupCity; // city
			
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
			$params["PCs"] = '';
			$params["cEmail"] = $shipping_email;
			$params["carrValue"] = 0;
			$params["carrCurr"] = '';
			$params["codAmt"] = $ced_cod;
			$params["weight"] = $wt_total;
			$params["custVal"] = '';
			$params["custCurr"] = '';
			$params["insrAmt"] = 0;
			$params["insrCurr"] = '';
			$params["itemDesc"] = '';
			$params["sName"] = $ced_smsa_sName;
			$params["sContact"] = $ced_smsa_sContact;
			$params["sAddr1"] = $ced_smsa_Addr1; //address
			$params["sAddr2"] = $pickupProvince;  //province
			$params["sCity"] = $ced_smsa_sCity; //city
			$params["sPhone"] = $ced_smsa_sContact;
			$params["sCntry"] = $ced_smsa_sCntry;/*SA*/
			$params["prefDelvDate"] = '';
			$params["gpsPoints"] = '';			
			
			try {
				$client = new SoapClient('http://track.smsaexpress.com/SECOM/SMSAwebService.asmx?wsdl');
				$result = $client->addShipment($params);
				$awbno = $result->addShipmentResult;

//sample code



// $client = new SoapClient('http://track.smsaexpress.com/SECOM/SMSAwebService.asmx');
// $result = $client->getTracking('290103186863','Testing1');
// // $awbno = $result->addShipmentResult;

// print_r($result);
// die("----");


				if(!empty($awbno) && is_numeric($awbno)){
					update_post_meta($woo_order_id , 'ced_smsa_awno' , $awbno);
// $order->update_status('completed');
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
			$woo_order_id = $_POST['order_can_id'];
			update_post_meta($woo_order_id , 'ced_smsa_ship_note' , 'Shipment Cancelled');
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

// call back to save pickup add
	public function ced_smsa_save_add_pickup(){

		$seller_pickup_add_list = json_encode($_POST);
		$seller_pickup_add = unserialize(get_option('seller_pickup_add'));
		if(is_array($seller_pickup_add)){
			$seller_pickup_add[] = $seller_pickup_add_list;
		} else{
			$seller_pickup_add = array();
			$seller_pickup_add[] = $seller_pickup_add_list;
		}


		update_option('seller_pickup_add', serialize($seller_pickup_add));        
		echo json_encode(array( 'status' => '200','message'=>'Setting details saved successfully' ));
		wp_die();
	}

// call back to delete pick up add
	public function ced_smsa_delete_add_pickup(){

		$seller_pickup_add_to_delete = $_POST['to_delete_pickAdd'];	

		$seller_pickup_add_list = unserialize(get_option('seller_pickup_add'));

		unset($seller_pickup_add_list[$seller_pickup_add_to_delete]);

		$seller_pickup_add_list = array_values($seller_pickup_add_list);

		update_option('seller_pickup_add', serialize($seller_pickup_add_list));
		echo json_encode(array( 'status' => '200','message'=>'Address Deleted Successfully' ));
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
		return esc_url_raw( $redirect_to );	
	}
}
