jQuery( document ).ready(
	function(){

		if ( jQuery( 'select#ced_cwsm_range_wise_pricing_type' ).val() == 'ced_cwsm_range_wise_pricing_type_discount' ) {
			jQuery( 'select#ced_cwsm_range_wise_pricing_type' ).closest( 'tr' ).next().show();
			jQuery( 'select#ced_cwsm_range_wise_pricing_type' ).closest( 'tr' ).next().next().show();
		} else {
			jQuery( 'select#ced_cwsm_range_wise_pricing_type' ).closest( 'tr' ).next().hide();
			jQuery( 'select#ced_cwsm_range_wise_pricing_type' ).closest( 'tr' ).next().next().hide();
		}

		jQuery( document.body ).on(
			'change',
			'select#ced_cwsm_range_wise_pricing_type',
			function(){
				if ( jQuery( this ).val() == 'ced_cwsm_range_wise_pricing_type_discount' ) {
					jQuery( this ).closest( 'tr' ).next().show();
					jQuery( this ).closest( 'tr' ).next().next().show();
				} else {
					jQuery( this ).closest( 'tr' ).next().hide();
					jQuery( this ).closest( 'tr' ).next().next().hide();
				}
			}
		);
	}
);
