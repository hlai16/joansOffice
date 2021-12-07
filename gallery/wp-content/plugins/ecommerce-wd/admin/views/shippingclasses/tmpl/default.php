<?php
defined('ABSPATH') || die('Access Denied');

// css
wp_enqueue_style('wde_layout_' . $this->_layout);
wp_enqueue_style('wde_' . WDFInput::get_controller() . '_' . $this->_layout);
// js
wp_enqueue_script('wde_view');
wp_enqueue_script('wde_layout_' . $this->_layout);
wp_enqueue_script('wde_' . WDFInput::get_controller() . '_' . $this->_layout);

$pager_number = 0;
$rows = $this->rows;
array_push($rows, $this->shipping_class_defaults);
?>
<form name="adminForm" id="adminForm" action="" method="post">
  <?php echo $this->generate_message(); ?>
  <table class="adminlist table table-striped wp-list-table widefat fixed pages wde_shipping_class wd_taxes">
    <thead>
      <tr>
        <th class="col_checked manage-column column-cb check-column"><input id="check_all" type="checkbox" style="margin:0;" /></th>
        <th class="col_name"><?php _e('Name', 'wde'); ?></th>
        <th class="col_slug"><?php _e('Slug', 'wde'); ?></th>
        <th class="col_description"><?php _e('Description', 'wde'); ?></th>
        <th class="col_product_count"><?php _e('Product count', 'wde'); ?></th>
      </tr>
    </thead>
    <tbody>	
    <?php
      foreach ($rows as $i => $row) {
        if ($row->id == 'default') {
          $class = 'wde_hide';
          $row->product_count = 0;
        }
        else {
          $class = 'row' . ($i % 2);
        }
        ?>
      <tr class="<?php echo $class; ?>">
        <td class="col_checked check-column">
          <?php
          if ($row->slug != 'default_shipping') {
            ?>
          <input type="checkbox" class="wde_check" id="cb<?php echo $row->id; ?>" name="cid[<?php echo $row->id; ?>]" value="<?php echo $row->id; ?>" />
            <?php
          }
          ?>
        </td>
        <td class="col_name">
          <input type="text" value="<?php echo $row->name ?>" name="names[<?php echo $row->id; ?>]" />
        </td>
        <td class="col_slug">
          <input type="text" value="<?php echo $row->slug ?>" name="slugs[<?php echo $row->id; ?>]" <?php echo $row->slug != 'default_shipping' ? '' : 'disabled="disabled"'; ?> />
        </td>
        <td class="col_description">
          <input type="text" value="<?php echo $row->description ?>" name="descriptions[<?php echo $row->id; ?>]" />
        </td>
        <td class="col_product_count">
          <?php
          if ($row->product_count) {
            ?>
          <a target="_blank" href="<?php echo add_query_arg(array('post_type' => 'wde_products', 'wde_shipping_class_posts' => $row->product_count_posts), admin_url('edit.php')); ?>">
            <?php
            echo $row->product_count;
            ?>
          </a>
            <?php
          }
          else {
            $row->product_count;
          }
          ?>
        </td>
      </tr>
      <?php
      }
    ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="5">
          <?php
          echo WDFHTML::jfbutton(__('Insert row', 'wde'), '', '', 'onclick="wde_add_shipping_class_row();"');
          echo WDFHTML::jfbutton(__('Remove selected row(s)', 'wde'), '', '', 'onclick="wde_remove_shipping_class_row();"');
          ?>
          <input type="hidden" name="removed" value="" />
        </td>
      </tr>
    </tfoot>
  </table>
  <input type="hidden" name="task" value="" />
</form>