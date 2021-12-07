function wdShop_formArrangement_onArrangementChange(event, obj) {
  jQuery(obj).prop("checked", true);
  jQuery(obj).closest("label").removeClass("btn-success");

  var jq_formArrangement = jQuery("form[name=wd_shop_form_arrangement]");
  var arrangement = jq_formArrangement.find("input[name=arrangement]:checked").val();
  wdShop_mainForm_set("arrangement", arrangement);

  wdShop_mainForm_setAction(wdShop_urlDisplayProducts);
  wdShop_mainForm_submit();
}