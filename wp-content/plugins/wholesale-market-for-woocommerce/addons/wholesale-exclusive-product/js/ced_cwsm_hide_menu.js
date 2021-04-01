jQuery( ' li a' ).each(
	function(){
		var data = jQuery( this ).html();

		if (data == ced_cwsm_hide_menu_js_ajax.wholesale_shop_page_title) {
			if ( ! ced_cwsm_hide_menu_js_ajax.wholesale_user) {
				jQuery( this ).parent().remove();
			}
		}

	}
)
