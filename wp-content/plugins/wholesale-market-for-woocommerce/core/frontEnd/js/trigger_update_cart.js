jQuery( document ).ready(
	function(){
		jQuery( document.body ).trigger( 'updated_wc_div' );
	}
);

jQuery( document.body ).on(
	'click',
	'div.cwsm_cross_div',
	function(){
		jQuery( 'div.custom-msg-wrapper' ).hide();
	}
);
jQuery( document ).on(
	'click',
	'.woocommerce-cart-form__cart-item .product-remove .remove',
	function(){
		jQuery( document ).ajaxStop(
			function() {
				window.location.replace( window.location.href );
			}
		);
	}
);
jQuery( document ).on(
	'click',
	'input[name="update_cart"]',
	function(){
		jQuery( document ).ajaxStop(
			function() {
				window.location.replace( window.location.href );
			}
		);
	}
);
jQuery( document ).on(
	'click',
	'button[name="update_cart"]',
	function(){
		jQuery( document ).ajaxStop(
			function() {
				window.location.replace( window.location.href );
			}
		);
	}
);
/*jQuery(document).ready(function($) {
	var upd_cart_btn = jQuery(".woocommerce-cart input[name='update_cart']");
	jQuery(".cart_item").find(".qty").on("change", function() {
		upd_cart_btn.trigger("click"); upd_cart_btn.val("...updating");
		jQuery(".cart_item").fadeTo( "slow" , 0.5);
	});
});
*/
/*jQuery(document).ready(function() {
var upd_cart_btn = jQuery(".woocommerce-cart input[name='update_cart']");
upd_cart_btn.hide();
jQuery(".cart_item").find(".qty").on("change", function() {
	upd_cart_btn.trigger("click");
	});
});*/
