(function( $ ) {
	
	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	 $(document).on('click','.ced_smsa_send_data',function(){
	 	$('.show_loader_ajx').show();
	 	var ajaxUrl = smsa_ajax_request.ajax_url;
	 	var orderId = $('.ced_smsa_send_data').attr('data-order');
	 	$('#show_loader_ajx_'+orderId).show();
	 	$.post(
	 		ajaxUrl,
	 		{
	 			'action' : 'ced_smsa_send_data',
	 			'oID' : orderId 
	 		},
	 		function(response){
	 			$('#show_loader_ajx_'+orderId).hide();
	 			alert(response);
	 			$('.show_loader_ajx').hide();
					$('.show_loader_ajx').show();
					var ajaxUrl = smsa_ajax_request.ajax_url;
					var tracking_num = $('.ced_smsa_get_invoice').attr('data_order_get_pdf');
					var orderId = $('.ced_smsa_get_invoice').attr('data_order_id');
					$('#show_loader_ajx_'+orderId).show();
					$.post(
						ajaxUrl,
						{
							'action' : 'ced_smsa_get_pfd',
							'tracking_num' : tracking_num,
							'orderId' : orderId
						},
						function(response){
							$('#show_loader_ajx_'+orderId).hide();
							// window.location = response;
							$('.show_loader_ajx').hide();
							var ajaxUrl = smsa_ajax_request.ajax_url;
							var file_name = $('.ced_order_get_pdf').attr('data_order_get_pdf');
							var orderId = $('.ced_order_get_pdf').attr('data_order_id');
							$('#show_loader_ajx_'+orderId).show();
							$.post(
								ajaxUrl,
								{
									'action' : 'ced_smsa_get_pfd_download',
									'file_name' : file_name
								},
								function(response){
									$('.show_loader_ajx_'+orderId).hide();
									window.location = response;
									// window.location.reload();
									
								}
								);
							// window.location.reload();
		   
						}
						);
				
	 			// window.location.reload();
	 		}
	 		);
	 });

	 $(document).on('click','.ced_smsa_submit_class',function(){
	 	$( document ).find( '.ced_wTi_loader' ).show();
	 	var ajaxUrl = smsa_ajax_request.ajax_url;
	 	var passKey		=  $('.passKey').val();
	 	var ced_smsa_sName = $('.ced_smsa_sName').val();
	 	var ced_smsa_sContact = $('.ced_smsa_sContact').val();
	 	var ced_smsa_Addr1 = $('.ced_smsa_Addr1').val();
	 	var ced_smsa_sCity = $('.ced_smsa_sCity').val();
	 	var ced_smsa_sPhone = $('.ced_smsa_sContact').val();
	 	var ced_smsa_sCntry = $('.ced_smsa_sCntry').val();
	 	if(!ced_smsa_sName || !ced_smsa_sContact || !ced_smsa_Addr1 || !ced_smsa_sCity || !ced_smsa_sPhone || !ced_smsa_sCntry  ){
	 		alert('Please fill all details ');
	 		$( '.ced_wTi_loader' ).hide();
	 		return;
	 	}
	 	$.post(
	 		ajaxUrl,
	 		{
	 			'action' : 'ced_smsa_save_seller',
	 			'passKey' : passKey,
	 			'ced_smsa_sName' : ced_smsa_sName,
	 			'ced_smsa_sContact' : ced_smsa_sContact,
	 			'ced_smsa_Addr1' : ced_smsa_Addr1,
	 			'ced_smsa_sCity' : ced_smsa_sCity,
	 			'ced_smsa_sPhone' : ced_smsa_sPhone,
	 			'ced_smsa_sCntry' : ced_smsa_sCntry
	 		},
	 		function(response){
	 			response = $.parseJSON( response );
	 			// console.log( 'great' );
	 			if( response.status == "200" )
	 			{
	 				var html = '<div id="message" class="ced_wTi_notice updated notice is-dismissible"><p>'+response.message+'</p></div>';
	 			}
	 			else if( response.status == "201" )
	 			{
	 				var html = '<div id="message" class="ced_wTi_notice notice-error notice is-dismissible"><p>'+response.message+'</p></div>';
	 			}
	 			$( html ).insertAfter('.ced_smsa_lable');
	 			$( '.ced_wTi_loader' ).hide();
	 		}
	 		);
	 });

	//  $(document).on('click','.ced_smsa_get_invoice',function(){
	//  	$('.show_loader_ajx').show();
	//  	var ajaxUrl = smsa_ajax_request.ajax_url;
	//  	var tracking_num = $('.ced_smsa_get_invoice').attr('data_order_get_pdf');
	//  	var orderId = $('.ced_smsa_get_invoice').attr('data_order_id');
	//  	$('#show_loader_ajx_'+orderId).show();
	//  	$.post(
	//  		ajaxUrl,
	//  		{
	//  			'action' : 'ced_smsa_get_pfd',
	//  			'tracking_num' : tracking_num,
	//  			'orderId' : orderId
	//  		},
	//  		function(response){
	//  			$('#show_loader_ajx_'+orderId).hide();
	//  			// window.location = response;
	//  			$('.show_loader_ajx').hide();
	//  			window.location.reload();

	//  		}
	//  		);
	//  });

	//  $(document).on('click' , '.ced_order_get_pdf', function(){
	//  	var ajaxUrl = smsa_ajax_request.ajax_url;
	//  	var file_name = $('.ced_order_get_pdf').attr('data_order_get_pdf');
	//  	var orderId = $('.ced_order_get_pdf').attr('data_order_id');
	//  	$('#show_loader_ajx_'+orderId).show();
	//  	$.post(
	//  		ajaxUrl,
	//  		{
	//  			'action' : 'ced_smsa_get_pfd_download',
	//  			'file_name' : file_name
	//  		},
	//  		function(response){
	//  			$('.show_loader_ajx_'+orderId).hide();
	//  			window.location = response;
	//  			// window.location.reload();
	 			
	//  		}
	//  		);


	//  });

	 $(document).on('click','.ced_smsa_cancle_shipment',function(){

	 	var ajaxUrl = smsa_ajax_request.ajax_url;
	 	$('.show_loader_ajx').show();
	 	var tracking_num = $('.ced_smsa_cancle_shipment').attr('data_order_cancle');
	 	$.post(
	 		ajaxUrl,
	 		{
	 			'action' : 'ced_smsa_cancle_shipment',
	 			'tracking_num' : tracking_num 
	 		},
	 		function(response){
	 			$('.show_loader_ajx').hide();
	 			alert(response);
	 		}
	 		);
	 });

	})( jQuery );
