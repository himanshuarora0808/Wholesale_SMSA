var ajaxNonce = Ced_register_action_handler.ajax_nonce;
jQuery( document ).ready(
	function($){

		$( 'input#createaccount' ).change(
			function(){
				if ($( this ).is( ":checked" )) {
					$( "#ced_wholesale_user_request_checkout_wrapper" ).show();
				} else {
					$( "#ced_wholesale_user_request_checkout_wrapper" ).hide();
				}
			}
		);

		$( ".ced_wm_wholesale_request" ).on(
			'click',
			function(){

				var user_id       = $( this ).data( 'id' );
				var role_required = $( this ).attr( 'role' );

				var select = $( this ).parent().find( 'select' );
				var val    = {};
				var count  = 1;
				$( select ).each(
					function(){

						val[this.name] = this.value;
						if (this.value == "") {
							count++;
						}

					}
				);
				val['user_id'] = user_id;
				if ( count == 1 ) {

					var data = {
						'action':'ced_wholesale_request_send',
						'user_id':user_id,
						'role_required': role_required,
						'wholesale_request' : true,
						'subscription_data':val
					}
					$( '#ced_cwsm_ajax_loader' ).show();
					$.ajax(
						{
							ajax_nonce:ajaxNonce,
							url: ced_wura_var.ajax_url,
							type: "POST",
							data: data,
							dataType :'json',
							success: function(response)
						{
								$( '#ced_cwsm_ajax_loader' ).hide();
								$( '#ced_widget_success_msg' ).show();
								$( '.ced_cwsm_request_role' ).hide();
								setTimeout( function(){ jQuery( '.ced_widget_success_msg' ).hide(); }, 3000 );
							}
						}
					);
				} else {
					alert( ced_wura_var.select_all );
				}

			}
		);

		$( ".ced_wm_wholesale_request1" ).on(
			'click',
			function(){

				var user_id       = $( this ).data( 'id' );
				var role_required = $( this ).attr( 'role' );

				var select = $( this ).parent().find( 'select' );
				var val    = {};
				var count  = 1;
				$( select ).each(
					function(){

						val[this.name] = this.value;

						if (this.value == "") {
							count++;
						}

					}
				);
				val['user_id'] = user_id;
				if ( count == 1 ) {
					var data = {
						'action':'ced_wholesale_request_send',
						'user_id':user_id,
						'role_required': role_required,
						'wholesale_request' : true,
						'subscription_data':val
					};
					$( '#ced_cwsm_ajax_loader1' ).show();
					$.ajax(
						{
							url: ced_wura_var.ajax_url,
							type: "POST",
							data: data,
							dataType :'json',
							success: function(response)
						{
								$( '#ced_cwsm_ajax_loader1' ).hide();
								$( '#ced_dashboard_success_msg' ).show();
								$( '.ced_cwsm_request_role' ).hide();
								setTimeout( function(){ jQuery( '.ced_dashboard_success_msg' ).hide(); }, 3000 );
							}
						}
					);
				} else {

					alert( ced_wura_var.select_all );
				}

			}
		);

		$( '#reg_role' ).on(
			'change' ,
			function(){
				$( '#ced_wm_wholesale_request' ).attr( 'role' , $( '#reg_role' ).val() );
			}
		);

		$( '#reg_role1' ).on(
			'change' ,
			function(){
				$( '#ced_wm_wholesale_request1' ).attr( 'role' , $( '#reg_role1' ).val() );
			}
		);
	}
);
