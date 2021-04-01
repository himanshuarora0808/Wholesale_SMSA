// var ajaxNonce = Ced_import_export_action_handler.ajax_nonce;
jQuery( document ).ready(
	function(){

		jQuery( document.body ).on(
			'click',
			'h3#ced_cwsm_csv_module_instruction_heading',
			function() {
				jQuery( this ).toggleClass( "open" );
				jQuery( 'div#ced_cwsm_csv_module_instruction' ).slideToggle();
				if (jQuery( 'h3#ced_cwsm_csv_module_instruction_heading span' ).html() == '+') {
					jQuery( 'h3#ced_cwsm_csv_module_instruction_heading span' ).html( '-' );
				} else {
					jQuery( 'h3#ced_cwsm_csv_module_instruction_heading span' ).html( '+' );
				}
			}
		);

		jQuery( document.body ).on(
			'click',
			'button#ced_cwsm_close_csv_import_report',
			function(event) {
				event.stopPropagation(); // Stop stuff happening
				event.preventDefault(); // Totally stop stuff happening
				jQuery( "div#ced_cwsm_csv_processing_div" ).show().delay( 500 ).fadeOut(
					function(){
						jQuery( "div#ced_cwsm_csv_processing_div" ).html( '<img src="' + ced_cwsm_csv_script_js_ajax.loading_image + '">' );
						jQuery( "div#ced_cwsm_csv_processing_div" ).removeClass( "ced_cwsm_success_id" );
						jQuery( "button#ced_cwsm_csv_submit_button" ).attr( 'disabled',false );
					}
				);
			}
		);

		jQuery( document.body ).on(
			'click',
			'button#ced_cwsm_download_error_log',
			function(event) {
				event.stopPropagation(); // Stop stuff happening
				event.preventDefault(); // Totally stop stuff happening

				var fileName = jQuery( this ).attr( "data-link" );
				jQuery.ajax(
					{
						url : ced_cwsm_csv_script_js_ajax.ajax_url,
						type : 'post',
						data : {
							action : 'ced_cwsm_csv_import_export_module_download_error_log',
							fileName : fileName
						},
						success : function( reportHTML )
					{
							alert( reportHTML );
							jQuery( "div#ced_cwsm_report_module_processed_data" ).html( reportHTML );
						}
					}
				);
			}
		);

	}
);
 var ajaxNonce = Ced_Csvimport_action_handler.ajax_nonce;
jQuery(
	function($)
	{
		// Variable to store your files
		var files;

		// Add events
		$( 'input#ced_cwsm_csvToUpload[type=file]' ).on( 'change', ced_cwsm_prepareUpload );
		$( 'button#ced_cwsm_csv_submit_button' ).on( 'click', ced_cwsm_uploadFiles );

		// Grab the files and set them to our variable
		function ced_cwsm_prepareUpload(event)
		{
			$( "div#ced_cwsm_csv_processing_div" ).hide();
			$( "div#ced_cwsm_csv_processing_div" ).html( '<img src="' + ced_cwsm_csv_script_js_ajax.loading_image + '">' );
			$( "div#ced_cwsm_csv_processing_div" ).removeClass();
			$( "button#ced_cwsm_csv_submit_button" ).attr( 'disabled',false );

			files = event.target.files;

			var fileName    = files[0].name;
			var fileNameExt = fileName.substr( fileName.lastIndexOf( '.' ) + 1 );

			$( "label#ced_cwsm_csv_file_name" ).html( fileName );

			if (fileNameExt != "csv") {
				var htmlToRender = "<h3 id='ced_cwsm_csv_failure'>Please upload a .csv file only</h3>";
				$( "div#ced_cwsm_csv_processing_div" ).html( htmlToRender );
				$( "div#ced_cwsm_csv_processing_div" ).addClass( "ced_cwsm_failure_id" );
				$( "div#ced_cwsm_csv_processing_div" ).show().delay( 2000 ).fadeOut(
					function(){
						$( "div#ced_cwsm_csv_processing_div" ).html( '<img src="' + ced_cwsm_csv_script_js_ajax.loading_image + '">' );
						$( "#ced_cwsm_csvToUpload" ).val( "" );
						$( "label#ced_cwsm_csv_file_name" ).html( 'No File Selected' );
						$( "div#ced_cwsm_csv_processing_div" ).removeClass( "ced_cwsm_failure_id" );
					}
				);
			}

			return;
		}

		// Catch the form submit and upload the files
		function ced_cwsm_uploadFiles(event)
		{
			event.stopPropagation(); // Stop stuff happening
			event.preventDefault(); // Totally stop stuff happening

			if ($( "#ced_cwsm_csvToUpload" ).val() == "") {
				var htmlToRender = "<h3 id='ced_cwsm_csv_failure'>Please select a .csv file first</h3>";
				$( "div#ced_cwsm_csv_processing_div" ).html( htmlToRender );
				$( "div#ced_cwsm_csv_processing_div" ).addClass( "ced_cwsm_failure_id" );
				$( "div#ced_cwsm_csv_processing_div" ).show().delay( 2000 ).fadeOut(
					function(){
						$( "div#ced_cwsm_csv_processing_div" ).html( '<img src="' + ced_cwsm_csv_script_js_ajax.loading_image + '">' );
						$( "div#ced_cwsm_csv_processing_div" ).removeClass( "ced_cwsm_failure_id" );
					}
				);

				return;
			}

			var fileName    = files[0].name;
			var fileNameExt = fileName.substr( fileName.lastIndexOf( '.' ) + 1 );

			if (fileNameExt != "csv") {
				var htmlToRender = "<h3 id='ced_cwsm_csv_failure'>Please upload a .csv file only</h3>";
				$( "div#ced_cwsm_csv_processing_div" ).html( htmlToRender );
				$( "div#ced_cwsm_csv_processing_div" ).addClass( "ced_cwsm_failure_id" );
				$( "div#ced_cwsm_csv_processing_div" ).show().delay( 2000 ).fadeOut(
					function(){
						$( "div#ced_cwsm_csv_processing_div" ).html( '<img src="' + ced_cwsm_csv_script_js_ajax.loading_image + '">' );
						$( "#ced_cwsm_csvToUpload" ).val( "" );
						$( "label#ced_cwsm_csv_file_name" ).html( 'No File Selected' );
						$( "div#ced_cwsm_csv_processing_div" ).removeClass( "ced_cwsm_failure_id" );
					}
				);

				return;
			}

			// START A LOADING SPINNER HERE
			$( "div#ced_cwsm_csv_processing_div" ).show();

			// Create a formdata object and add the files
			var formData = new FormData();
			$.each(
				files,
				function(key, value)
				{
					formData.append( key, value );
				}
			);

			formData.append( 'action', 'ced_cwsm_csv_import_export_module_read_csv' );
			if ($( "input#ced_cwsm_update_woocommerce_price" ).prop( "checked" ) == true) {
				formData.append( 'modify_woocommerce_price', 'true' );
			} else {
				formData.append( 'modify_woocommerce_price', 'false' );
			}

			$.ajax(
				{
					ajax_nonce:ajaxNonce,
					url : 	ced_cwsm_csv_script_js_ajax.ajax_url,
					type : 'post',
					data : formData,
					cache: false,
					dataType: 'json',
					processData: false, // Don't process the files
					contentType: false, // Set content type to false as jQuery will tell the server its a query string request
					success : function( data )
				{
						if (data.status == false) {
							var htmlToRender = "<h3 id='ced_cwsm_csv_failure'>" + data.reason + "</h3>";
							$( "div#ced_cwsm_csv_processing_div" ).html( htmlToRender );
							$( "div#ced_cwsm_csv_processing_div" ).addClass( "ced_cwsm_failure_id" );

							$( "div#ced_cwsm_csv_processing_div" ).show().delay( 2000 ).fadeOut(
								function(){
									$( "div#ced_cwsm_csv_processing_div" ).html( '<img src="' + ced_cwsm_csv_script_js_ajax.loading_image + '">' );
									$( "div#ced_cwsm_csv_processing_div" ).removeClass( "ced_cwsm_failure_id" );
									$( "#ced_cwsm_csvToUpload" ).val( "" );
									$( "label#ced_cwsm_csv_file_name" ).html( 'No File Selected' );
									$( "input#ced_cwsm_update_woocommerce_price" ).prop( "checked", false );
								}
							);

						} else {
							var htmlToRender = "<h3 id='ced_cwsm_csv_success'>CSV successfully imported.</h3>";
							htmlToRender    += "<table id='ced_cwsm_csv_import_report'><tr><th>S.No</th><th>Product Meta Updated</th><th>Successful Updates</th><th>Failed Updates</th></tr>";
							htmlToRender    += "<tr><td>1</td><td>Wholesale Price</td><td>" + data.wholesale_price.successfulUpdate + "</td><td>" + data.wholesale_price.failedUpdate + "</td></tr>";
							htmlToRender    += "<tr><td>2</td><td>Min Qty To Buy</td><td>" + data.min_qty_to_buy.successfulUpdate + "</td><td>" + data.min_qty_to_buy.failedUpdate + "</td></tr>";

							if ($( "input#ced_cwsm_update_woocommerce_price" ).prop( "checked" ) == true) {
								htmlToRender += "<tr><td>3</td><td>Regular Price</td><td>" + data.regular_price.successfulUpdate + "</td><td>" + data.regular_price.failedUpdate + "</td></tr>";
								htmlToRender += "<tr><td>4</td><td>Sale Price</td><td>" + data.special_price.successfulUpdate + "</td><td>" + data.special_price.failedUpdate + "</td></tr>";
							}
							htmlToRender += "</table>";

							htmlToRender += "<button id='ced_cwsm_close_csv_import_report' class='button-primary'>Close</button>";

							htmlToRender += '<div class="error_reason"><p>* The reason behind failed update is either the data was not available or data was in incorrect format.</p>';
							htmlToRender += '<a class="error_log_button" href="admin.php?page=wc-settings&tab=ced_cwsm_plugin&section=ced_cwsm_csv_import_export_module&ced_cwsm_log_download=' + data.error_log_link + '" target="_blank">Download Error Log</a></div>';

							$( "div#ced_cwsm_csv_processing_div" ).html( htmlToRender );
							$( "div#ced_cwsm_csv_processing_div" ).addClass( "ced_cwsm_success_id" );

							$( "#ced_cwsm_csvToUpload" ).val( "" );
							$( "label#ced_cwsm_csv_file_name" ).html( 'No File Selected' );
							$( "input#ced_cwsm_update_woocommerce_price" ).prop( "checked", false );
							$( "button#ced_cwsm_csv_submit_button" ).attr( 'disabled',true );
						}
					}
				}
			);

		}

	}
);
