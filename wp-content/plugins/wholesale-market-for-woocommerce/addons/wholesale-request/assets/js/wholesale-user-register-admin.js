var ajaxNonce = Ced_requestaction_handler.ajax_nonce;
function wp_editor_getContent(editor_id) {
	if ( typeof editor_id == 'undefined' ) {
		editor_id = wpActiveEditor;
	}
	if ( jQuery( '#wp-' + editor_id + '-wrap' ).hasClass( 'tmce-active' ) && tinyMCE.get( editor_id ) ) {
		return tinyMCE.get( editor_id ).getContent();
	} else {
		return jQuery( '#' + editor_id ).val();
	}
}
jQuery( document ).ready(
	function($){
		$( "#ced_wura_save_notification_setting" ).click(
			function(){
				$( "#ced_wura_loading" ).show();
				var notification               = {};
				notification['name']           = $( "#ced_wura_notification_from_name" ).val();
				notification['mail']           = $( "#ced_wura_notification_from_mail" ).val();
				notification['admin_subject']  = $( "#ced_wura_notification_admin_rcv_subject" ).val();
				notification['admin_message']  = wp_editor_getContent( 'ced_wura_notification_admin_rcv_msg' );
				notification['user_subject']   = $( "#ced_wura_notification_rcv_subject" ).val();
				notification['user_message']   = wp_editor_getContent( 'ced_wura_notification_rcv_msg' );
				notification['accept_subject'] = $( "#ced_wura_notification_accept_subject" ).val();
				notification['accept_message'] = wp_editor_getContent( 'ced_wura_notification_accept_msg' );
				notification['cancel_subject'] = $( "#ced_wura_notification_cancel_subject" ).val();
				notification['cancel_message'] = wp_editor_getContent( 'ced_wura_notification_cancel_msg' );

				var data = {
					'action':'ced_save_wholesale_notification',
					'data':notification
				};

				$.ajax(
					{
						ajax_nonce:ajaxNonce,
						url: ced_wura_admin.ajax_url,
						type: "POST",
						data: data,
						dataType :'json',
						success: function(response)
					{
							$( "#ced_wura_loading" ).hide();
							location.reload();
						}
					}
				);
			}
		);

		$( ".ced_wura_user_request_accept" ).click(
			function(e){
				$( '#ced_wura_ajax_loader' ).show();
				e.preventDefault();
				var current  = $( this );
				var user_id  = $( this ).data( "id" );
				var req_role = $( this ).attr( "role" );

				var data = {
					'action':'ced_wholesale_process_request',
					'data':'accept',
					'id':user_id,
					'req_role': req_role
				};

				$.ajax(
					{
						ajax_nonce:ajaxNonce,
						url: ced_wura_admin.ajax_url,
						type: "POST",
						data: data,
						dataType :'json',
						success: function(response)
					{
							$( '#ced_wura_ajax_loader' ).hide();
							current.parent().html( '<img src="' + ced_wura_admin.plugin_url + '/assets/images/accept.png">' );
						}
					}
				);
			}
		);
		$( ".ced_wura_user_request_cancel" ).click(
			function(e){
				e.preventDefault();
				var current = $( this );
				var user_id = $( this ).data( "id" );
				var data    = {
					'action':'ced_wholesale_process_request',
					'data':'cancel',
					'id':user_id
				};

				$.ajax(
					{
						url: ced_wura_admin.ajax_url,
						type: "POST",
						data: data,
						dataType :'json',
						success: function(response)
					{
							current.parent().html( '<img src="' + ced_wura_admin.plugin_url + '/assets/images/cancel.png">' );
						}
					}
				);
			}
		);
	}
);

jQuery( document ).ready(
	function(){
		jQuery( '#ced_cwsm_request_role_addon_functionality' ).on(
			'change',
			function() {
				if ( jQuery( '#ced_cwsm_request_role_addon_functionality' ).is( ":checked" ) ) {
					jQuery( '#ced_cwsm_request_role_addon_directly' ).closest( 'tr' ).show();
					jQuery( '#ced_cwsm_request_role_myaccount_page' ).closest( 'tr' ).show();
				} else {
					jQuery( '#ced_cwsm_request_role_addon_directly' ).closest( 'tr' ).hide();
					jQuery( '#ced_cwsm_request_role_myaccount_page' ).closest( 'tr' ).hide();
				}
			}
		);
		if ( jQuery( '#ced_cwsm_request_role_addon_functionality' ).is( ":checked" ) ) {
			jQuery( '#ced_cwsm_request_role_myaccount_page' ).closest( 'tr' ).show();
			jQuery( '#ced_cwsm_request_role_addon_directly' ).closest( 'tr' ).show();
		} else {
			jQuery( '#ced_cwsm_request_role_addon_directly' ).closest( 'tr' ).hide();
			jQuery( '#ced_cwsm_request_role_myaccount_page' ).closest( 'tr' ).hide();
		}
	}
);
