jQuery( document.body ).on(
	'click',
	'h4.ced_cwsm_range_wise_pricing_heading',
	function(){
		jQuery( this ).next().slideToggle();
		if ( jQuery( this ).find( 'span' ).html() == '+') {
			jQuery( this ).find( 'span' ).html( '-' );
		} else {
			jQuery( this ).find( 'span' ).html( '+' );
		}
	}
);

jQuery( document.body ).on(
	'click',
	'span.ced_cwsm_add_range_row',
	function(){
		var tableRef    = jQuery( this ).parent().next();
		var tableRowRef = jQuery( 'tr',jQuery( tableRef ) ).eq( 1 );

		var minQtyName = jQuery( 'td',jQuery( tableRowRef ) ).eq( 0 ).find( 'input' ).attr( 'name' );
		var maxQtyName = jQuery( 'td',jQuery( tableRowRef ) ).eq( 1 ).find( 'input' ).attr( 'name' );
		var valueToUse = jQuery( 'td',jQuery( tableRowRef ) ).eq( 2 ).find( 'input' ).attr( 'name' );

		var htmlToAppend = '<tr>';
		htmlToAppend    += '<td><input type="number" name="' + minQtyName + '"></td>';
		htmlToAppend    += '<td><input type="number" name="' + maxQtyName + '"></td>';
		htmlToAppend    += '<td><input type="text" class="wc_input_price" name="' + valueToUse + '"></td>';
		htmlToAppend    += '<td><span class="button button-primary ced_cwsm_delete_range_row">Delete</span></td>';
		htmlToAppend    += '</tr>';
		jQuery( tableRef ).append( htmlToAppend );

	}
);


jQuery( document.body ).on(
	'click',
	'span.ced_cwsm_delete_range_row',
	function(){
		jQuery( this ).parent().parent().remove();
	}
);
