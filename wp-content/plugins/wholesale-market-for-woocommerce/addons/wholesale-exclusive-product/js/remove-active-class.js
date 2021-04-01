jQuery( document ).ready(
	function() {
		jQuery( "a" ).each(
			function() {
				if (jQuery( this ).attr( 'href' ) == ced_cwsm_remove_active_class_js_ajax.shop_page_url) {
					jQuery( this ).parent().removeClass( 'current-menu-item' );
					jQuery( this ).parent().removeClass( 'current_page_item' );
					jQuery( this ).removeClass( 'active' );
				}
			}
		);
	}
);
