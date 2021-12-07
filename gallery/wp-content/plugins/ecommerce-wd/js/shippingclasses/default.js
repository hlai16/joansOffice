function wde_add_shipping_class_row() {
  var index_number = jQuery(".wde_shipping_class>tbody>tr").length;
  var index = "default" + jQuery(".wde_shipping_class>tbody>tr").length;

  // Clone shipping class row from template.
  var row = jQuery(".wde_hide").clone().removeClass("wde_hide").addClass("row" + index);
  row.html(function(i, oldHTML) {
    return oldHTML.replace(/default/gi, index);
  });

  // Insert new shipping class row to the end of taxes table.
  var inserted_obj = jQuery(row).insertBefore(".wde_hide");
  return;
}

function wde_remove_shipping_class_row() {
	var col_checked = jQuery('.wde_shipping_class').find('td.col_checked input[type="checkbox"]');
  var removed = new Array();
	col_checked.each(function() {
		if (jQuery(this).prop('checked')) {
      if (jQuery(this).val().indexOf("default") === -1) {
        // Add only shipping classes saved in DB.
        removed.push(jQuery(this).val());
      }
			jQuery(this).parent().parent().remove();
		}	
	});
  jQuery('input[name="removed"]').val(removed);
	return false;
}