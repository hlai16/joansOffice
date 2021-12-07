jQuery(document).ready(function () {
    // products slider
    new WDItemsSlider(jQuery(".wd_shop_products_slider"), {loop: true, slideWidth: "slideSizePage"})
});

function wdShop_onAllProductsClick(event, obj) {
  var manufacturer_id = jQuery(obj).attr("data-manufacturer-id");
  var jq_form_all_products = jQuery("form[name=wd_shop_form_all_products]");
  jq_form_all_products.find("input[name=filter_manufacturer_ids]").val(manufacturer_id);
  jq_form_all_products.submit();
}