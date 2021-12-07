<?php
defined('ABSPATH') || die('Access Denied');

class EcommercewdControllerShippingzones extends EcommercewdController {
  public function __construct() {
    parent::__construct();
  }

  protected function store_input_in_row() {
    $shipping_classes = WDFHelper::get_shipping_classes();
    if ($shipping_classes) {
      $shippings_arr = array();
      foreach ($shipping_classes as $shipping_class) {
        $shipping_class_price = WDFInput::get('shipping_class' . $shipping_class->id . '_price', '', 'string');
        if ($shipping_class_price !== '') {
          // Add only classes which are available.
          $shippings_arr[$shipping_class->id] = $shipping_class_price;
        }
      }
      WDFInput::set('shipping_classes_price', empty($shippings_arr) ? '' : WDFJson::encode($shippings_arr));
    }
    $free_shipping = WDFInput::get('free_shipping', 0, 'int');
    $free_shipping_after_certain_price = WDFInput::get('free_shipping_after_certain_price', 0, 'int');
    if (!$free_shipping && $free_shipping_after_certain_price) {
      WDFInput::set('free_shipping', 2);
    }
    $id = WDFInput::get('id', 0, 'int');
    if (!$id) {
      global $wpdb;
      $query = 'SELECT MAX(`ordering`) FROM ' . $wpdb->prefix . 'ecommercewd_shippingzones WHERE `country_ids`<>"-1"';
      $max_ordering = intval($wpdb->get_var($query));
      WDFInput::set('ordering', ++$max_ordering);
    }
    $row = WDFDb::store_input_in_row();
    return $row;
  }

  public function remove_keep_rest() {
    $message_id = '';
    $ids = WDFInput::get_checked_ids();
    // prevent from removing default item
    $contain_default_items = false;
    $default_item_rows =  WDFDb::get_rows('', '`country_ids` = "-1"');
    foreach ($default_item_rows as $default_item_row) {
      $index_of_default_item_id = array_search($default_item_row->id, $ids);
      if ($index_of_default_item_id !== false) {
        $contain_default_items = true;
        unset($ids[$index_of_default_item_id]);
        $ids = array_values($ids);
      }
    }
    if ($contain_default_items == true) {
      $message_id = 28;
    }

    // remove items
    if ((empty($ids) == false) && (WDFDb::remove_rows('', $ids) == false)) {
      $message_id = 4;
    }

    WDFHelper::redirect('', '', '', '', $message_id);
  }
}
