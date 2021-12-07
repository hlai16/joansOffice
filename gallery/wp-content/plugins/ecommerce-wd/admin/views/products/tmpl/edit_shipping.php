<?php
defined('ABSPATH') || die('Access Denied');

$lists = $this->lists;
$list_shipping_data_field = $lists['list_shipping_data_field'];
$row = $this->row;
$model_options = WDFHelper::get_model('options');
$options = $model_options->get_options();
$initial_values = $options['initial_values'];
?>
<table class="adminlist table">
  <tbody>
    <tr id="shipping_classes" class="shipping_info">
      <td class="col_key">
        <label><?php _e('Shipping classes', 'wde') ?>:</label>
      </td>
      <td class="col_value">
        <textarea type="text" name="shipping_class_names" id="shipping_class_names" class="names_list" disabled="disabled" data-tab-index="shipping"><?php echo $row->shipping_class_names; ?></textarea>
        <?php echo WDFHTML::jfbutton_inline('', WDFHTML::BUTTON_INLINE_TYPE_REMOVE, '', '', 'onclick="onBtnRemoveShippingClick(event, this);"'); ?>
        <?php echo WDFHTML::jfbutton(__('Select', 'wde'), '', 'thickbox', 'onclick="onBtnSelectShippingClick(event, this);"', WDFHTML::BUTTON_COLOR_GREEN, WDFHTML::BUTTON_SIZE_SMALL); ?>
        <?php echo WDFHTML::jfbutton(__('Manage', 'wde'), '', '', 'href="' . add_query_arg(array('page' => 'wde_shippingclasses'), admin_url('admin.php')) . '" target="_blank"', WDFHTML::BUTTON_COLOR_WHITE, WDFHTML::BUTTON_SIZE_SMALL); ?>
        <input type="hidden" name="shipping_class_ids" id="shipping_class_ids" value="<?php echo $row->shipping_class_ids; ?>" />
        <p class="description"><?php _e('Leave empty if there is no shipping for this product.', 'wde') ?></p>
      </td>
    </tr>
    <tr class="shipping_info">
      <td class="col_key">
        <label for="weight"><?php _e('Weight', 'wde'); ?>:</label>
      </td>
    <td class="col_value">
      <input type="text" name="weight" id="weight" value="<?php echo $row->weight; ?>" onKeyPress="return disableEnterKey(event);" />
         <span><?php echo ' (' . $initial_values['weight_unit'] . ')'; ?></span>
      </td>	
    </tr>
    <tr class="shipping_info">
      <td class="col_key">
        <label for="dimensions_length"><?php _e('Dimensions (LxWxH)', 'wde') ?>:</label>
      </td>
      <td class="col_value">
        <input type="text" name="dimensions_length" id="dimensions_length" value="<?php echo $row->dimensions_length; ?>" onKeyPress="return disableEnterKey(event);" />
        <input type="text" name="dimensions_width" id="dimensions_width" value="<?php echo $row->dimensions_width; ?>" onKeyPress="return disableEnterKey(event);" />  
        <input type="text" name="dimensions_height" id="dimensions_height" value="<?php echo $row->dimensions_height; ?>" onKeyPress="return disableEnterKey(event);" /> 
        <span><?php echo ' (' . $initial_values['dimensions_unit'] . ')';?></span> 
        <input type="hidden" name="dimensions" value="<?php echo $row->dimensions; ?>" />
      </td>	
    </tr>
  </tbody>
</table>
