<?php
defined('ABSPATH') || die('Access Denied');

// css
wp_enqueue_style('wde_layout_' . $this->_layout);
wp_enqueue_style('wde_' . WDFInput::get_controller() . '_' . $this->_layout);
// js
wp_enqueue_script('wde_view');
wp_enqueue_script('wde_layout_' . $this->_layout);

$filter_items = $this->filter_items;
$sort_data = $this->sort_data;
$sort_by = $sort_data['sort_by'];
$sort_order = $sort_data['sort_order'];
$pagination = $this->pagination;
$pager_number = 0;
$rows = $this->rows;
$class_name = 'icon-disable-drag';
if ($sort_by == 'ordering' && $sort_order == 'asc') {
  wp_enqueue_script('jquery-ui-sortable');
  wp_enqueue_script('wde_jquery-ordering');
	$class_name = 'icon-drag';
}
?>
<form name="adminForm" id="adminForm" action="" method="post">
  <?php echo $this->generate_message(); ?>
  <div class="tablenav top">
    <?php
    echo $this->generate_filters($filter_items);
    echo $this->generate_pager($pagination->_count, $pager_number++, WDFSession::get_pagination_start());
    ?>
  </div>
  <table class="wp-list-table widefat fixed pages adminlist">
    <thead>
    <th class="col_ordering sorted"></th>
      <th class="col_num">#</th>
      <th class="col_checked manage-column column-cb check-column"><input id="check_all" type="checkbox" style="margin:0;" /></th>
      <?php echo WDFHTML::wd_ordering('name', $sort_by, $sort_order, __('Name', 'wde')); ?>
      <th class=""><?php _e('Countries', 'wde'); ?></th>
      <?php echo WDFHTML::wd_ordering('enabled', $sort_by, $sort_order, __('Enabled', 'wde')); ?>
      <th class="col_edit"><?php _e('Edit', 'wde'); ?></th>
      <th class="col_delete"><?php _e('Delete', 'wde'); ?></th>
    </thead>
    <tbody class="wde_ordering_desc">
      <?php
      if ($rows) {
        for ($i = 0; $i < count($rows); $i++) {
          $row = $rows[$i];
          $alternate = (!isset($alternate) || $alternate == 'class="alternate"') ? '' : 'class="alternate"';
          ?>
          <tr id="tr_<?php echo $row->id; ?>" <?php echo $alternate; ?>>
            <td class="<?php echo $row->country_ids != -1 ? 'col_ordering' : ''; ?>">
              <?php
              if ($row->country_ids != -1) {
                ?>
              <?php echo $this->generate_order_cell_content('', $row->ordering, $class_name); ?>
              <input type="hidden" value="<?php echo $i; ?>" name="orders[<?php echo $row->id; ?>]" />
                <?php
              }
              ?>
            </td>
            <td class="col_num">
              <?php echo $pagination->_offset + $i; ?>
            </td>
            <td class="col_checked check-column">
              <input id="cb<?php echo $row->id; ?>" name="cid[]" value="<?php echo $row->id; ?>" type="checkbox" class="<?php echo $row->country_ids != -1 ? 'wde_check' : 'hidden'; ?>" />
            </td>
            <td class="col_name">
              <a href="<?php echo $row->edit_url; ?>" title="<?php _e('Edit', 'wde'); ?>"><?php echo $row->name; ?></a>
            </td>
            <td class="col_name">
              <?php
              if ($row->country_ids == -1) {
                _e('This zone is used for shipping addresses that aren‘t included in any other shipping zone. Adding shipping methods to this zone is optional.', 'wde');
              }
              elseif ($row->country_names) {
                ?>
              <div class="countries_cont">
                <?php
                foreach ($row->country_names as $country_id => $country_name) {
                  ?>
                <span class="country_cont" id="<?php echo $row->id; ?>_tag_<?php echo $country_id; ?>">
                  <span><?php echo $country_name; ?></span>
                </span>
                  <?php
                }
                ?>
              </div>
                <?php
              }
              ?>
            </td>
            <td class="col_enabled">
              <?php echo WDFHTML::icon_boolean_inactive($row->id, $row->enabled, 'enable', 'disable', FALSE); ?>
            </td>
            <td class="col_edit">
              <a href="<?php echo $row->edit_url; ?>" title="<?php _e('Edit', 'wde'); ?>"><?php _e('Edit', 'wde'); ?></a>
            </td>
            <td class="col_delete">
              <?php
              if ($row->country_ids != -1) {
                ?>
              <a onclick="wde_check_one('#cb<?php echo $row->id; ?>');submitform('remove_keep_default_and_basic');return false;" href=""><?php _e('Delete', 'wde'); ?></a>
                <?php
              }
              ?>
            </td>
          </tr>
          <?php
        }
      }
      else {
        echo WDFHTML::no_items(WDFToolbar::$item_name);
      }
      ?>
    </tbody>
  </table>
  <div class="tablenav top">
    <?php echo $this->generate_pager($pagination->_count, $pager_number++, WDFSession::get_pagination_start()); ?>
  </div>
    <input type="hidden" name="option" value="com_<?php echo WDFHelper::get_com_name(); ?>"/>
    <input type="hidden" name="controller" value="<?php echo WDFInput::get_controller(); ?>"/>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value=""/>
    <input type="hidden" name="sort_by" value="<?php echo $sort_by; ?>"/>
    <input type="hidden" name="sort_order" value="<?php echo $sort_order; ?>"/>
    <input type="hidden" name="tab_index" value="<?php echo WDFInput::get('tab_index'); ?>" />
</form>