function wdShop_formSort_onSortByChange(event, obj) {
    var jq_formSort = jQuery("form[name=wd_shop_form_sort]");
    var sortBy = jq_formSort.find("select[name=sort_by] option:selected").val();
    wdShop_mainForm_set("sort_by", sortBy);
    wdShop_mainForm_set("sort_order", "asc");
    wdShop_mainForm_set('pagination_limit_start', 0);

    wdShop_mainForm_setAction(wdShop_urlDisplayProducts);
    wdShop_mainForm_submit();
}

function wdShop_formSort_onBtnSortOrderClick(event, obj, newOrder) {
    wdShop_mainForm_set("sort_order", newOrder);
    wdShop_mainForm_set('pagination_limit_start', 0);

    wdShop_mainForm_setAction(wdShop_urlDisplayProducts);
    wdShop_mainForm_submit();
}
