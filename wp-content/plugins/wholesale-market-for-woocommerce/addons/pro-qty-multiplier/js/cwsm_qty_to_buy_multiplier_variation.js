jQuery( document ).ready(
	function(){
		jQuery( document.body ).on(
			'change',
			'.variations select',
			function() {

				var variation_id = jQuery( 'input:hidden[name="variation_id"]' ).val();
				// alert(variation_id);
				var ced_cwsm_pro_qty_multiplier = "1";
				if ( variation_id != "" ) {
					var variationInfo           = JSON.parse( ced_cwsm_qty_to_buy_multiplier_variation_AJAX.variationInfo );
					ced_cwsm_pro_qty_multiplier = variationInfo[variation_id].ced_cwsm_qty_to_buy_multiplier;
				}
				var qtySelector = jQuery( 'div.quantity input[name="quantity"]' );
				jQuery( qtySelector ).attr( 'step', ced_cwsm_pro_qty_multiplier );
				jQuery( qtySelector ).attr( 'min', ced_cwsm_pro_qty_multiplier );
				jQuery( qtySelector ).attr( 'value', ced_cwsm_pro_qty_multiplier );
			}
		);

		/* the below code was causing an issue for
		( variable product ) price was not shown and quantity
		multiplier was not working.*/

		// jQuery( '.variations_form' ).on( 'woocommerce_update_variation_values', function( event, variation ) {
		// variation.min_qty = 16;
		// });
	}
);
