function wdShop_getInvalidFields() {
    var jq_formLogin = jQuery("form[name=loginform]");

    // required fields
    var invalidFields = [];
    jq_formLogin.find(".wd_shop_required_field").each(function () {
        var jq_field = jQuery(this);
        var field_type = jq_field.prop("tagName").toLowerCase();
        switch (field_type) {
            case "select":
                if (jq_field.find("option:selected").val() == "") {
                    invalidFields.push(jq_field);
                }
                break;
            default:
                if (jq_field.val() == "") {
                    invalidFields.push(jq_field);
                }
                break;
        }
    });

    return invalidFields;
}

function wdShop_onBtnLoginClick(event, obj) {
    jQuery("form[name=loginform] .form-group").removeClass("has-error");

    var invalidFields = wdShop_getInvalidFields();
    if (invalidFields.length > 0) {
        for (var i = 0; i < invalidFields.length; i++) {
            var invalidField = invalidFields[i];
            jQuery(invalidField).closest(".form-group").addClass("has-error");
        }

        var jq_alert = jQuery(".wd_shop_alert_incorrect_data");
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

    var jq_formLogin = jQuery("form[name=loginform]");
    jq_formLogin.submit();
}

function wd_shop_submit_form(e){
	if (!e){
		var e = event || window.event;
	}
    if(e.keyCode=='13'){
       document.getElementById('loginform').submit();
    }
}

