function checkForm() {
  var invalidField;
  jQuery(".required_field:not(.template .required_field)").each(function () {
    if (invalidField != null) {
      return false;
    }
    var value = jQuery(this).val();
    if (value.trim() == "") {
      invalidField = this;
      return false;
    }
  });

  if (invalidField != null) {
    alert(wde_localize.fill_required_fields);
    jQuery(invalidField).focus();
    jQuery("html, body").animate({
      scrollTop: jQuery(invalidField).offset().top - 200
    }, "fast");
    return false;
  }

  fillInputOption();

  return true;
}

function fillInputOption(){
	var valueOption
	if (_fields) {
		elements = {};
		var fields = JSON.parse(_fields);
		var cc_fields = JSON.parse(_cc_fields);
		for (var field in fields) {
			if (field == 'options') {
				var options = {};
				for (i = 0; i < cc_fields.length; i++) {
					options[cc_fields[i]] = jQuery("[name=" + cc_fields[i] + "]:checked").val();
				}
				elements[field] = options;
			}
			else {
				var value;
				if (jQuery("[name="+field+"]").prop("tagName") == 'select') {
					value = jQuery("[name="+field+"]:selected").val();
        }
				else if (jQuery("[name="+field+"]").attr("type") == 'radio' || jQuery("[name="+field+"]").attr("type") == 'checkbox') {
					value = jQuery("[name="+field+"]:checked").val();
        }
				else {
					value = jQuery("[name="+field+"]").val();
        }
				elements[field] = value;
			}			
		}	
		valueOption = JSON.stringify(elements);
	}
	else {
		valueOption = '';
  }
  adminFormSet("options", valueOption);
}

function fillInputImages() {
  jQuery("input[name=button_image]").val("");
}

function onTabActivated(currentTabIndex) {
  adminFormSet("tab_index", currentTabIndex);
}
