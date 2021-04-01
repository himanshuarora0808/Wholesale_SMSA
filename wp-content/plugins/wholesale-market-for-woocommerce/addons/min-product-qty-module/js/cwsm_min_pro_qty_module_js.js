if (jQuery( 'input[type=radio][name=ced_cwsm_radio_minQty_picker]:checked' ).val() == 'ced_cwsm_radio_common_minQty') {
	jQuery( '#ced_cwsm_central_min_qty' ).closest( 'tr' ).show();
} else if (jQuery( 'input[type=radio][name=ced_cwsm_radio_minQty_picker]:checked' ).val() == 'ced_cwsm_radio_individual_minQty') {
	jQuery( '#ced_cwsm_central_min_qty' ).closest( 'tr' ).hide();
}

jQuery( 'input[type=radio][name=ced_cwsm_radio_minQty_picker]' ).change(
	function() {
		if (this.value == 'ced_cwsm_radio_individual_minQty') {
			jQuery( '#ced_cwsm_central_min_qty' ).closest( 'tr' ).hide();
		} else if (this.value == 'ced_cwsm_radio_common_minQty') {
			jQuery( '#ced_cwsm_central_min_qty' ).closest( 'tr' ).show();
		}
	}
);


/** update from :: version 1.0.8 **/
jQuery( 'input[type=checkbox][name="ced_cwsm_consider_variations_as_one_common"]' ).change(
	function() {
		if (jQuery( this ).is( ':checked' )) {
			jQuery( 'input[type=checkbox][name="ced_cwsm_consider_variations_as_one_individual"]' ).attr( 'checked',false );
		}
	}
);
jQuery( 'input[type=checkbox][name="ced_cwsm_consider_variations_as_one_individual"]' ).change(
	function() {
		if (jQuery( this ).is( ':checked' )) {
			jQuery( 'input[type=checkbox][name="ced_cwsm_consider_variations_as_one_common"]' ).attr( 'checked',false );
		}
	}
);
/** update from :: version 1.0.8 **/
