function submitCheckedItems(id) {
  jQuery("form[name=adminForm] input[type=checkbox][name^=cid]:checked").each(function () {
    var jq_thisTr = jQuery(this).closest("tr");
    var category = {};
    category.id = jq_thisTr.attr("itemId");
    category.name = jq_thisTr.attr("itemName");
    category.level = jq_thisTr.attr("itemLevel");	
    category.tree = jq_thisTr.attr("itemTree");
    window.parent[_callback](category, id);
  });

  window.parent.tb_remove();
}

function onItemClick(event, obj, id) {
  jQuery("form[name=adminForm] input[type=checkbox][name=checkall-toggle]").prop("checked", false);
  jQuery("form[name=adminForm] input[type=checkbox][name^=cid]").prop("checked", false);
  jQuery(obj).closest("tr").find("input[type=checkbox][name^=cid]").prop("checked", true);
  submitCheckedItems(id);
}

function onBtnInsertClick(event, obj, id) {
  submitCheckedItems(id);
}