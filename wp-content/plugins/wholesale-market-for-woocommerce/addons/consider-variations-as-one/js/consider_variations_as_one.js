jQuery( document ).ready(
	function(){

		var auto_check_fields = JSON.parse( ced_cwsm_consider_variations_as_one_js_ajax.auto_check_fields );

		var previousStateOfFields = {};
		for (fieldIndex in auto_check_fields) {
			previousStateOfFields[auto_check_fields[fieldIndex]] = (jQuery( "#" + auto_check_fields[fieldIndex] ).attr( "checked" ) == "checked") ? true : false;
		}

		jQuery( document.body ).on(
			'change',
			'input:checkbox',
			function(){
				if ( jQuery( '#ced_cwsm_enable_consider_variations_as_one' ).attr( "checked" ) == "checked" ) {
					var currentID = jQuery( this ).attr( 'id' );
					if ( auto_check_fields.indexOf( currentID ) != -1 ) {
						jQuery( '#' + currentID ).attr( 'checked', true );
					}
				}
			}
		);

		jQuery( document.body ).on(
			'change',
			'#ced_cwsm_enable_consider_variations_as_one',
			function(){
				if ( jQuery( this ).attr( "checked" ) == "checked" ) {
					for (fieldIndex in auto_check_fields) {
						jQuery( '#' + auto_check_fields[fieldIndex] ).attr( 'checked', true );
					}
				} else {
					for (fieldIndex in auto_check_fields) {
						jQuery( '#' + auto_check_fields[fieldIndex] ).attr( 'checked',previousStateOfFields[auto_check_fields[fieldIndex]] );
					}
				}
			}
		);

	}
);
