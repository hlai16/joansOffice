jQuery(document).ready(function () {
  for (var i = 0; i < _selectedIds.length; i++) {
    var selectedId = _selectedIds[i];
    if (selectedId != "") {
      jQuery("form[name=adminForm] input[type=checkbox][name^=cid][value=" + selectedId + "]").prop("checked", true);
    }
  }
});

function submitCheckedItems() {
  var shippings = [];
  jQuery("form[name=adminForm] input[type=checkbox][name^=cid]:checked").each(function () {
    var jq_thisTr = jQuery(this).closest("tr");
    var shipping = {};
    shipping.id = jq_thisTr.attr("itemId");
    shipping.name = jq_thisTr.attr("itemName");
    shippings.push(shipping);
  });
  window.parent[_callback](shippings);
  window.parent.tb_remove();
}

function onBtnSelectClick(event, obj) {
  submitCheckedItems();
}