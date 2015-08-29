
function add_checkout_element(){
	var cc_current = jQuery('#dpsc_item_count').val();
	var newRow = jQuery('div.dpsc_append_row script[type="dpsc_checkout_row"]').clone();
	newRow.attr('id',cc_current);
	newRow = newRow.html().replace(/CURRENTCOUNT/g,cc_current);
	jQuery('tbody.sort-checkout').append(newRow);
	cc_current++;
	jQuery('#dpsc_item_count').val(cc_current);
}

function delete_checkout_element(elem){
	jQuery(elem).parent().parent().remove();
	
}