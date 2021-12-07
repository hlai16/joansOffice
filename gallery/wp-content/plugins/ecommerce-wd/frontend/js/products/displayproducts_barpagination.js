function wdShop_formPagination_onPageClick(event, obj, limitStart) {
    var jq_btn = jQuery(obj);
    if ((jq_btn.parent().hasClass("disabled")) || (jq_btn.parent().hasClass("active"))) {
        return false;
    }

    wdShop_mainForm_set('pagination_limit_start', limitStart);

    wdShop_mainForm_setAction(wdShop_urlDisplayProducts);
    wdShop_mainForm_submit();
}

function wdShop_formPagination_onItemsPerPageChange(event, obj) {
    wdShop_mainForm_set('pagination_limit_start', 0);
    var limit = jQuery("form[name=wd_shop_form_pagination] select[name=items_per_page] option:selected").val();
    wdShop_mainForm_set('pagination_limit', limit);

    wdShop_mainForm_setAction(wdShop_urlDisplayProducts);
    wdShop_mainForm_submit();
}
