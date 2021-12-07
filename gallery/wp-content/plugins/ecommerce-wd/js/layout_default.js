jQuery(document).ready(function () {
  jQuery(".colspanchange").attr("colspan", jQuery("#adminForm table.adminlist>thead>tr>th:visible").length);
});

function onTabActivated(currentTabIndex) {
  adminFormSet("tab_index", currentTabIndex);
}