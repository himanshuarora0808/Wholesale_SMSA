jQuery( document ).ready(
	function(){
		jQuery( "#ced_cwsm_product_addon_save_licenses" ).click(
			function(event){

				event.preventDefault();
				event.stopPropagation();

				jQuery( ".license_loading_image" ).show();
				jQuery( ".licennse_notification" ).hide();

				var domain_name   = jQuery( "#ced_cwsm_product_addon_domain_name" ).val();
				var module_name   = jQuery( "#ced_cwsm_product_addon_module_name" ).val();
				var frame_version = jQuery( "#ced_cwsm_product_addon_framework_version" ).val();
				var php_version   = jQuery( "#ced_cwsm_product_addon_phpversion" ).val();
				var frame_name    = jQuery( "#ced_cwsm_product_addon_framework" ).val();
				var admin_name    = jQuery( "#ced_cwsm_product_addon_admin_name" ).val();
				var admin_email   = jQuery( "#ced_cwsm_product_addon_admin_email" ).val();
				var license_key   = jQuery( "#ced_cwsm_product_addon_license_key" ).val();

				if (license_key == '' || license_key == null) {
					jQuery( "#ced_cwsm_product_addon_license_key" ).attr( 'style','border:1px solid red' );
					jQuery( ".license_loading_image" ).hide();
					return false;
				} else {
					jQuery( "#ced_cwsm_product_addon_license_key" ).removeAttr( 'style' );
				}

				var data = {	'action':'ced_cwsm_product_addon_validate_licensce',
					'domain_name':domain_name,
					'module_name':module_name,
					'frame_version':frame_version,
					'php_version':php_version,
					'frame_name':frame_name,
					'admin_name':admin_name,
					'admin_email':admin_email,
					'license_key':license_key,
				};

				jQuery.post(
					ajaxurl,
					data,
					function(data){

						jQuery( ".license_loading_image" ).hide();

						if (data.hasOwnProperty( 'response' )) {
							if (data['response'] == 'success') {
								jQuery( '.licennse_notification' ).text( 'Validated' );
								jQuery( '.licennse_notification' ).attr( 'style','color:green' );
								location.reload();

							} else {
								jQuery( '.licennse_notification' ).text( 'Invalid License' );
								jQuery( '.licennse_notification' ).attr( 'style','color:red' );
							}
						}
					},
					'json'
				);
			}
		);
	}
);
