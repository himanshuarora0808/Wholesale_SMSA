<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://cedcommerce.com/
 * @since      1.0.0
 *
 * @package    woocommerce_smsa_shipping_integration
 * @subpackage woocommerce_smsa_shipping_integration/admin/partials
 */
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
  <title>ced basic plugin</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.min.css">
  <!-- Bootstrap -->
  <!-- <link rel="stylesheet" href="fancybox/source/jquery.fancybox.css?v=2.1.5" type="text/css" media="screen" /> -->
  <!--   <link href="assets/font/specimen_stylesheet.css" rel="stylesheet">  -->
  <!-- <link href="../css/ie10-viewport-bug-workaround.css" rel="stylesheet"> -->
  <link rel="stylesheet" type="text/css" href="assets/style.css">
  <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
<style type="text/css">
 .pickupdataTable{
  border-collapse: collapse;
 }

 .pickupdataTable,.pickupdataTable td,.pickupdataTable th{
  border: 1px solid black;
 }
 .pickupdataTable th, .pickupdataTable td {
  padding: 7px;
  text-align: left;
}

</style>
</head>
<body>
  <section class="section ced_woocommerce_smsa_shipping_integration_topnav_wrap">
    <?php $seller_detal = get_option('seller_smsa_details');   
    $seller_detal = json_decode($seller_detal);
    $passKey = isset($seller_detal->passKey) ? $seller_detal->passKey : '';

    $ced_smsa_sName = isset($seller_detal->ced_smsa_sName) ? $seller_detal->ced_smsa_sName : '';
    $ced_smsa_sContact = isset($seller_detal->ced_smsa_sContact) ? $seller_detal->ced_smsa_sContact : '';
    $ced_smsa_Addr1 = isset($seller_detal->ced_smsa_Addr1) ? $seller_detal->ced_smsa_Addr1 : '';
    $ced_smsa_sCity = isset($seller_detal->ced_smsa_sCity) ? $seller_detal->ced_smsa_sCity : '';
    $ced_smsa_sPhone = isset($seller_detal->ced_smsa_sPhone) ? $seller_detal->ced_smsa_sPhone : '';
    $ced_smsa_sCntry = isset($seller_detal->ced_smsa_sCntry) ? $seller_detal->ced_smsa_sCntry : '';

    ?>
  <div class="ced_wTi_loader ced_wTi_hidden_div">
  <img src=<?php echo './images/loading.gif'; ?> width="50" height="50">
   </div>
    <div class="">
      <div class="ced_woocommerce_smsa_shipper_details">
        <label class="ced_smsa_lable"><?php _e('Shipper Details', 'ced_smsa') ?></label>
        <div>
          <div class="ced_smsa_shopper_details">
            <span class="ced_span_lable"> <?php _e('Passkey ', 'ced_smsa') ?><span style="color:red">*</span> : </span>
            <input type="text" class="passKey" placeholder="Testing" value=<?php echo $passKey; ?>></input>
          </div>
          <div class="ced_smsa_shopper_details">
            <span class="ced_span_lable"> <?php _e('Name ', 'ced_smsa') ?><span style="color:red">*</span> : </span>
            <input type="text" class="ced_smsa_sName" placeholder="CedCommerce" value=<?php echo $ced_smsa_sName; ?>></input>
          </div>
          <div class="ced_smsa_shopper_details">
            <span class="ced_span_lable"> <?php _e('Contact ', 'ced_smsa') ?><span style="color:red">*</span> : </span>
            <input type="text" class="ced_smsa_sContact" placeholder="1234567890" value=<?php echo $ced_smsa_sContact; ?>></input>
          </div>
          <div class="ced_smsa_shopper_details">
            <span class="ced_span_lable"> <?php _e('Shipping Address ', 'ced_smsa') ?><span style="color:red">*</span> : </span>
            <input type="text" class="ced_smsa_Addr1" placeholder="Saudi Arabia" value=<?php echo $ced_smsa_Addr1; ?>></input>
          </div>
          <div class="ced_smsa_shopper_details">
            <span class="ced_span_lable"> <?php _e('Shipping City ', 'ced_smsa') ?><span style="color:red">*</span> :  </span>
            <input type="text" class="ced_smsa_sCity" placeholder="Riyadh" value=<?php echo $ced_smsa_sCity; ?>></input>
          </div>
          <!-- <div class="ced_smsa_shopper_details">
            <span class="ced_span_lable"> <?php //_e('Phone :', 'ced_smsa') ?></span>
            <input type="text" class="ced_smsa_sPhone" value=<?php //echo $ced_smsa_sPhone; ?>></input>
          </div> -->
          <div class="ced_smsa_shopper_details">
            <span class="ced_span_lable"> <?php _e('Shipping Country ', 'ced_smsa')?><span style="color:red">*</span> : </span>
            <input type="text" class="ced_smsa_sCntry" placeholder="Saudi Arabia" value=<?php echo $ced_smsa_sCntry; ?>></input>
          </div>
          <span class="ced_span_button"><input type="button" class="ced_smsa_submit_class" value="Save"></input></span>
        </div>
      </div>
    </div>

  </section><!-- top nav end -->

  <section class="section ced_woocommerce_smsa_shipping_integration_topnav_wrap">      
    <div class="ced_wTi_loader ced_wTi_hidden_div">
      <img src=<?php echo './images/loading.gif'; ?> width="50" height="50">
    </div>
    <div class="">
      <div class="ced_woocommerce_smsa_shipper_details">
        <label class="ced_smsa_lable"><?php _e('Pickup Details', 'ced_smsa') ?></label>
        <div>
          <div class="ced_smsa_shopper_details">
            <span class="ced_span_lable"> <?php _e('Pickup Address ', 'ced_smsa') ?><span style="color:red">*</span> : </span>
            <input type="text" class="ced_pickup_add" placeholder="Pickup Address"></input>
          </div>    
          <div class="ced_smsa_shopper_details">
            <span class="ced_span_lable"> <?php _e('Pickup City ', 'ced_smsa') ?><span style="color:red">*</span> : </span>
            <input type="text" class="ced_pickup_city" placeholder="Pickup City"></input>
          </div>    
          <div class="ced_smsa_shopper_details">
            <span class="ced_span_lable"> <?php _e('Pickup Province ', 'ced_smsa') ?><span style="color:red">*</span> : </span>
            <input type="text" class="ced_pickup_province" placeholder="Pickup Province"></input>
          </div>        
          <span class="ced_span_button"><input type="button" class="ced_smsa_submit_add_pickup" value="Add"></input></span>
        </div>
      </div>
    </div>

  </section>
  <section class="section ced_woocommerce_smsa_shipping_integration_topnav_wrap ced_pickup_add_section">      
    <?php $pickup_add = unserialize(get_option('seller_pickup_add'));  
       if(isset($pickup_add) && !empty($pickup_add)){?>
      <table class="pickupdataTable"><tr><th>Pickup Address</th><th>Pickup City</th><th>Pickup Province</th><th colspan="2">Action</th></tr>
        <?php     
        foreach ($pickup_add as $key => $pickup_address) {
          $pickupAddVal = json_decode($pickup_address, true);?>
          <tr>
            <td>
              <?php echo $pickupAddVal['pickupAdd']; ?>
            </td>
            <td>
              <?php echo $pickupAddVal['pickupCity']; ?>
            </td>
            <td>
              <?php echo $pickupAddVal['pickupProvince']; ?>
            </td>
            <td><button class="delete_pickupaddress btn btn-primary" data-id="<?php echo $key ?>">Delete</button></td>    
        </tr>
        <?php } } ?>
      </section> 
</body>
</html>