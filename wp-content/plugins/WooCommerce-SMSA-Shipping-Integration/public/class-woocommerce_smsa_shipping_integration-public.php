<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://cedcommerce.com/
 * @since      1.0.0
 *
 * @package    woocommerce_smsa_shipping_integration
 * @subpackage woocommerce_smsa_shipping_integration/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    woocommerce_smsa_shipping_integration
 * @subpackage woocommerce_smsa_shipping_integration/public
 * @author     cedcommerce <webmaster@cedcommerce.com>
 */
class woocommerce_smsa_shipping_integration_Public {

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
	 * @param      string    $woocommerce_smsa_shipping_integration       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $woocommerce_smsa_shipping_integration, $version ) {

		$this->woocommerce_smsa_shipping_integration = $woocommerce_smsa_shipping_integration;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->woocommerce_smsa_shipping_integration, plugin_dir_url( __FILE__ ) . 'css/woocommerce_smsa_shipping_integration-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->woocommerce_smsa_shipping_integration, plugin_dir_url( __FILE__ ) . 'js/woocommerce_smsa_shipping_integration-public.js', array( 'jquery' ), $this->version, false );

	}

	public function my_tracking_smsa($columns){
		if(is_array($columns) && !empty($columns)){
			$columns = array_slice($columns, 0, 4, true) +
			array("smsa_tracking_num" => __('Tracking Number' , 'ced_smsa')) +
			array_slice($columns, 4, count($columns) - 1, true) ;
		}
		return $columns;
	}

	public function smsa_tracking_num($order){
		if(isset($order)){
			$orderid = $order->get_id();
			if(isset($orderid) && $orderid != null){
				$ced_smsa_tracking_no = get_post_meta($orderid,'ced_smsa_awno',true);
				if(isset($ced_smsa_tracking_no) && $ced_smsa_tracking_no != ""){
					echo '<a href="http://www.smsaexpress.com/Track.aspx?tracknumbers='.$ced_smsa_tracking_no.'" target="_blank">'.$ced_smsa_tracking_no.'</a>'; 
					// echo $ced_smsa_tracking_no;
				}
				else{
				echo "Shipment Pending !";
				}
			}
		}
	}


	public function smsa_tracking_num_order_detail_page($order){ 

		if(isset($order)){
			$orderid = $order->get_id();
			if(isset($orderid) && $orderid != null){
				$ced_smsa_tracking_no = get_post_meta($orderid,'ced_smsa_awno',true); ?>

		<div class="ced_tracking_heading">
		<h2 class="ced_tracking_title"> Tracking Information </h2>
		<table class="woocommerce-table ced_tracking_table">
			<tr>
				<td> Tracking Number </td>
				<td> <?php if(isset($ced_smsa_tracking_no) && $ced_smsa_tracking_no != ""){ echo '<a href="http://www.smsaexpress.com/Track.aspx?tracknumbers='.$ced_smsa_tracking_no.'" target="_blank">'.$ced_smsa_tracking_no.'</a>';  } else{ echo "Shipment Pending";} ?> </td>
				</tr>
			</table>
		</div>
		<?php
				}
			}
		 }
	}
