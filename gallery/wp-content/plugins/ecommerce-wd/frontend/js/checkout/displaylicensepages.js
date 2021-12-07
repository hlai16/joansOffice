jQuery(document).ready(function () {
    jQuery("#wd_shop_tab_license_pages a").click(function (event) {
        jQuery(this).tab("show");
    });
});

function onWDShop_pagerBtnClick(event, obj, forceAccept) {
    if (forceAccept == true) {
        var isAccepted = jQuery("form[name=wd_shop_form_license_pages] input[name=accept]").is(":checked");
        if (isAccepted == false) {
            var jq_alert = jQuery(".wd_shop_checkout_alert_licensing");
            if (jq_alert.is(":visible") == false) {
                jq_alert
                    .removeClass("hidden")
                    .slideUp(0)
                    .slideDown(250);
            } else {
                jq_alert
                    .fadeOut(100)
                    .fadeIn(100);
            }
            return;
        }
    }

    wdShop_mainForm_setAction(jQuery(obj).attr("href"));
    wdShop_mainForm_submit();
}