jQuery( document ).ready(
	function(){

		jQuery( document.body ).on(
			'click',
			'div.wc-proceed-to-checkout a.checkout-button',
			function(event){
				var isQualifyMinCheckoutPriceCondition = jQuery( 'input#isQualifyMinCheckoutPriceCondition' ).val();
				if ( isQualifyMinCheckoutPriceCondition == "false" ) {
					var htmlToReplace = jQuery( 'div#cwsm_min_checkout_price_error div' );
					jQuery( this ).replaceWith( htmlToReplace );
					event.stopPropagation();
					event.preventDefault();
				}
			}
		);

	}
);
