var ajaxNonce = Ced_market_action_handler.ajax_nonce;
jQuery( document.body ).on(
	'click',
	'button#ced_cwsm_send_mail',
	function(event){
		event.stopPropagation(); // Stop stuff happening
		event.preventDefault(); // Totally stop stuff happening

		jQuery( '#ced_cwsm_send_loading' ).show();

		var suggestionTitle  = jQuery( '#ced_cwsm_suggestion_title' ).val().trim();
		var suggestionDetail = jQuery( '#ced_cwsm_suggestion_detail' ).val().trim();

		if (suggestionTitle == "" || suggestionDetail == "") {
			jQuery( '#ced_cwsm_send_loading' ).hide();
			jQuery( "div#ced_cwsm_mail_empty" ).show().delay( 2000 ).fadeOut(
				function(){
				}
			);
			return;
		}

		jQuery.ajax(
			{
				url : ced_cwsm_send_mail_js_ajax.ajax_url,
				type : 'post',
				data : {
					ajax_nonce:ajaxNonce,
					action : 'ced_cwsm_admin_suggestions_module_send_mail',
					suggestionTitle : suggestionTitle,
					suggestionDetail : suggestionDetail
				},
				success : function( response )
			{
					jQuery( '#ced_cwsm_send_loading' ).hide();

					if (response == "success") {
						jQuery( "div#ced_cwsm_mail_success" ).show().delay( 2000 ).fadeOut(
							function(){
								jQuery( '#ced_cwsm_suggestion_title' ).val( '' );
								jQuery( '#ced_cwsm_suggestion_detail' ).val( '' );
							}
						);
					} else {
						jQuery( "div#ced_cwsm_mail_failure" ).show().delay( 2000 ).fadeOut(
							function(){
							}
						);
					}
				}
			}
		);
	}
);
