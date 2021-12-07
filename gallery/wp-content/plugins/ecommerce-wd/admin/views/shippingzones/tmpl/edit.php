<?php
defined('ABSPATH') || die('Access Denied');

// css
wp_enqueue_style('wde_layout_' . $this->_layout);
wp_enqueue_style('wde_' . WDFInput::get_controller() . '_' . $this->_layout);

// js
wp_enqueue_script('wde_view');
wp_enqueue_script('wde_layout_' . $this->_layout);
wp_enqueue_script('wde_' . WDFInput::get_controller() . '_' . $this->_layout);

$row_default_currency = $this->row_default_currency;

$list_shipping_type = array();
$list_shipping_type[] = (object)array('value' => 'per_bundle', 'text' => __('Per bundle', 'wde'));
$list_shipping_type[] = (object)array('value' => 'per_unit', 'text' => __('Per unit', 'wde'));

$row = $this->row;
?>
<form name="adminForm" id="adminForm" action="" method="post">
  <?php echo $this->generate_message(); ?>
  <table class="adminlist table">
    <tbody>
      <tr>
        <td class="col_key">
          <label for="name"><?php _e('Name', 'wde'); ?>&nbsp;<span class="star">*</span></label>
        </td>
        <td class="col_value">
          <input type="text" name="name" id="name" value="<?php echo $row->name; ?>" class="required_field" onKeyPress="return disableEnterKey(event);" />
        </td>
      </tr>
      <?php
      if ($row->country_ids != -1) {
        ?>
      <tr>
        <td class="col_key">
          <label><?php _e('Countries', 'wde'); ?></label>
        </td>
        <td class="col_value">
          <textarea type="text" name="country_names" id="country_names" class="names_list" disabled="disabled"><?php echo $row->country_names; ?></textarea>
          <?php echo WDFHTML::jfbutton_inline('', WDFHTML::BUTTON_INLINE_TYPE_REMOVE, '', '', 'onclick="onBtnRemoveCountriesClick(event, this);"'); ?>
          <?php echo WDFHTML::jfbutton(__('Select', 'wde'), '', 'thickbox', 'onclick="onBtnSelectCountriesClick(event, this);"', WDFHTML::BUTTON_COLOR_GREEN, WDFHTML::BUTTON_SIZE_SMALL); ?>
          <?php echo WDFHTML::jfbutton(__('Manage', 'wde'), '', '', 'href="' . add_query_arg(array('taxonomy' => 'wde_countries', 'post_type' => 'wde_products'), admin_url('edit-tags.php')) . '" target="_blank"', WDFHTML::BUTTON_COLOR_WHITE, WDFHTML::BUTTON_SIZE_SMALL); ?>
          <input type="hidden" name="country_ids" id="country_ids" value="<?php echo $row->country_ids; ?>" />
          <p class="description"><?php _e('To set for all countries leave the field empty.', 'wde'); ?></p>
        </td>
      </tr>
      <tr>
        <td class="col_key">
          <label for="postcode"><?php _e('Postcode', 'wde'); ?></label>
        </td>
        <td class="col_value">
          <textarea type="text" name="postcode" id="postcode" class="names_list" placeholder="<?php _e('List 1 postcode per line', 'wde'); ?>"><?php echo $row->postcode; ?></textarea>
          <p class="description"><?php _e('Postcodes containing wildcards (e.g. CB23*) and fully numeric ranges (e.g. 90210...99000) are also supported.', 'wde'); ?></p>
        </td>
      </tr>
        <?php
      }
      ?>
      <tr>
        <td class="col_key">
          <label><?php _e('Free shipping', 'wde'); ?></label>
        </td>
        <td class="col_value">
          <?php
          $free_shipping = $row->free_shipping;
          $free_shipping_0_checked = $free_shipping != 1 ? 'checked="checked"' : '';
          $free_shipping_1_checked = $free_shipping == 1 ? 'checked="checked"' : '';
          $free_shipping_after_certain_price_checked = $free_shipping == 2 ? 'checked="checked"' : '';
          $price_container_class_hidden = $free_shipping == 1 ? 'hidden' : '';
          $free_shipping_start_price_container_class_hidden = $free_shipping != 2 ? 'hidden' : '';
          ?>
          <input type="radio" name="free_shipping" id="free_shipping_0" value="0"
                 <?php echo $free_shipping_0_checked; ?>
                 onchange="onFreeShippingChange(event, this);"/>
          <label for="free_shipping_0">
            <?php _e('No', 'wde'); ?>
          </label>
          <input type="radio"
                 name="free_shipping"
                 id="free_shipping_1"
                 value="1"
                 <?php echo $free_shipping_1_checked; ?>
                 onchange="onFreeShippingChange(event, this);"/>
          <label for="free_shipping_1">
            <?php _e('Yes', 'wde'); ?>
          </label>
          <p class="description"></p>
        </td>
      </tr>
      <tr class="price_container">
        <td class="col_key">
          <label for="price"><?php _e('Price', 'wde'); ?></label>
        </td>
        <td class="col_value">
          <input type="text" name="price" id="price" value="<?php echo $row->price; ?>" onKeyPress="return disableEnterKey(event);" />
          <?php echo $row_default_currency->code; ?>
          <br />
          (<label for="free_shipping_after_certain_price">
              <input type="checkbox"
                     name="free_shipping_after_certain_price"
                     id="free_shipping_after_certain_price"
                     value="1"
                     <?php echo $free_shipping_after_certain_price_checked; ?>
                     onchange="onFreeShippingAfterCertainPriceChange(event, this);"/>
              <?php _e('Free shipping for orders over a certain price', 'wde'); ?>
          </label>
          <span class="free_shipping_start_price_container <?php echo $free_shipping_start_price_container_class_hidden; ?>">
              <input type="text"
                     name="free_shipping_start_price"
                     value="<?php echo $row->free_shipping_start_price; ?>"/>
              <?php echo $row_default_currency->code; ?>
          </span>)
          <p class="description"></p>
        </td>
      </tr>
      <tr class="price_container">
        <td class="col_key">
          <label><?php _e('Shipping rate calculation', 'wde'); ?></label>
        </td>
        <td class="col_value">
          <?php echo WDFHTML::wd_radio_list('shipping_type', $list_shipping_type, 'value', 'text', $row->shipping_type); ?>
        </td>
      </tr>
    </tbody>
  </table>
  <span class="price_container">
    <h3 class="wde_inline"><?php _e('Shipping Class Costs', 'wde'); ?></h3>
    <?php echo WDFHTML::jfbutton(__('Manage', 'wde'), '', '', 'href="' . add_query_arg(array('page' => 'wde_shippingclasses'), admin_url('admin.php')) . '" target="_blank"', WDFHTML::BUTTON_COLOR_WHITE, WDFHTML::BUTTON_SIZE_SMALL); ?>
  </span>
  <?php
  if ($row->shipping_classes) {
    ?>
  <table class="adminlist table price_container">
    <tbody>
      <?php
      $shipping_classes_price = $row->shipping_classes_price;
      foreach ($row->shipping_classes as $shipping_class) {
        $id = $shipping_class->id;
        $price = isset($shipping_classes_price->$id) ? $shipping_classes_price->$id : '';
        ?>
      <tr>
        <td class="col_key">
          <label for="shipping_class<?php echo $id; ?>_price">
            <?php echo $shipping_class->id == 0 ? sprintf(__('%s price', 'wde'), $shipping_class->name) : sprintf(__('"%s" shipping class price', 'wde'), $shipping_class->name); ?>
          </label>
        </td>
        <td class="col_value">
          <input type="text" name="shipping_class<?php echo $id; ?>_price" id="shipping_class<?php echo $id; ?>_price" value="<?php echo $price; ?>" placeholder="<?php _e('N/A', 'wde'); ?>" />
          <p class="description"></p>
        </td>
      </tr>
        <?php
      }
      ?>
    </tbody>
  </table>
    <?php
  }
  ?>
  <input type="hidden" name="option" value="com_<?php echo WDFHelper::get_com_name(); ?>" />
  <input type="hidden" name="controller" value="<?php echo WDFInput::get_controller(); ?>" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="id" value="<?php echo $row->id; ?>" />
  <input type="hidden" name="redirect_task" value="edit" />
</form>