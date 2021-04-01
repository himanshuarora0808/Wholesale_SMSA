jQuery( document ).ready(
	function(){

		if (jQuery( 'input[type=radio][name=ced_cwsm_radio_qty_multiplier_picker]:checked' ).val() == 'ced_cwsm_qty_multiplier_pick_from_common_field') {
			jQuery( '#ced_cwsm_qty_to_buy_multiplier_common' ).closest( 'tr' ).show();
		} else if (jQuery( 'input[type=radio][name=ced_cwsm_radio_qty_multiplier_picker]:checked' ).val() == 'ced_cwsm_qty_multiplier_pick_from_product_panel') {
			jQuery( '#ced_cwsm_qty_to_buy_multiplier_common' ).closest( 'tr' ).hide();
		}

		jQuery( 'input[type=radio][name=ced_cwsm_radio_qty_multiplier_picker]' ).change(
			function() {
				if (this.value == 'ced_cwsm_qty_multiplier_pick_from_product_panel') {
					jQuery( '#ced_cwsm_qty_to_buy_multiplier_common' ).closest( 'tr' ).hide();
				} else if (this.value == 'ced_cwsm_qty_multiplier_pick_from_common_field') {
					jQuery( '#ced_cwsm_qty_to_buy_multiplier_common' ).closest( 'tr' ).show();
				}
			}
		);

	}
);
