var ced_cwsm_allset = false;

jQuery( document.body ).on(
	'click',
	'input:submit[name=save]',
	function(event){

		if ( ! ced_cwsm_allset) {
			event.stopPropagation(); // Stop stuff happening
			event.preventDefault(); // Totally stop stuff happening

			var valueToCheck = jQuery( '#ced_cwsm_default_wm_price_txt' ).val();

			if (valueToCheck.indexOf( '{*wm_price}' ) > -1) {
				ced_cwsm_allset = true;
				jQuery( 'input:submit[name=save]' ).trigger( 'click' );
			} else {
				jQuery( 'span.cwsm_sorry_msg' ).remove();
				jQuery( '#ced_cwsm_default_wm_price_txt' ).addClass( 'cwsm_sorry_msg_red' );
				jQuery( '#ced_cwsm_default_wm_price_txt' ).after( '<span class="cwsm_sorry_msg">Sorry! you can\'t remove {*wm_price} from this textarea. </span>' );
				jQuery( 'html, body' ).animate(
					{
						scrollTop: jQuery( '#ced_cwsm_default_wm_price_txt' ).offset().top - 140
					},
					1000
				);
			}
		}

	}
);


jQuery( '#wpppdf_img_send_email' ).click(
	function(e) {
			e.preventDefault();
			jQuery( ".wpppdf_img_email_image p" ).removeClass( "ced_wpppdf_img_email_image_error" );
			jQuery( ".wpppdf_img_email_image p" ).removeClass( "ced_wpppdf_img_email_image_success" );

			jQuery( ".wpppdf_img_email_image p" ).html( "" );
			var email = jQuery( '.wpppdf_img_email_field' ).val();
			jQuery( "#wpppdf_loader" ).removeClass( "hide" );
			jQuery( "#wpppdf_loader" ).addClass( "dislay" );
			// alert(ajax_url);
			$.ajax(
				{
					type:'POST',
					url :ajax_url,
					data:{action:'wpppdf_send_mail',flag:true,emailid:email},
					success:function(data)
				{
						var new_data = JSON.parse( data );
						jQuery( "#wpppdf_loader" ).removeClass( "dislay" );
						jQuery( "#wpppdf_loader" ).addClass( "hide" );
						if (new_data['status'] == true) {
							jQuery( ".wpppdf_img_email_image p" ).addClass( "ced_wpppdf_img_email_image_success" );
							jQuery( ".wpppdf_img_email_image p" ).html( new_data['msg'] );
						} else {
							jQuery( ".wpppdf_img_email_image p" ).addClass( "ced_wpppdf_img_email_image_error" );
							jQuery( ".wpppdf_img_email_image p" ).html( new_data['msg'] );
						}
					}
				}
			);
	}
);
