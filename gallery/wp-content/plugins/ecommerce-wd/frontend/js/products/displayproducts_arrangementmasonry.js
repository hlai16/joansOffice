jQuery(document).ready(function () {
	Change_productpanelWidth();
  var wd_shop_product_width = parseInt(jQuery(".wd_shop_product").outerWidth() - 1);

  // Change image container height.
  jQuery(".wd_shop_product").each(function () {
    jQuery(this).css("height", jQuery(this).height());
    jQuery(this).css("width", wd_shop_product_width);
  });

  // Set maximum width for products container.
  jQuery(".wd_shop_products_container").css("max-width", jQuery(".wd_shop_products_container").outerWidth());

  jQuery(".wd_shop_products_container").masonry({
    percentPosition: true,
    itemSelector: ".wd_shop_product",
    columnWidth: "#wd_shop_for_width"
  });
});

jQuery(window).resize(function() {
  Change_productpanelWidth();
});

function Change_productpanelWidth() {
	if (jQuery("#wd_shop_for_width").outerWidth(true) < 242) {
		jQuery('.wd_shop_bottom_checkout_buttons').each(function () {
			jQuery(this).removeClass( "pull-right").addClass("clear_buttons_float");
		});
	}
	else {
		jQuery('.wd_shop_bottom_checkout_buttons').each(function () {
			jQuery(this).removeClass( "clear_buttons_float").addClass("pull-right");
		});
	}
}