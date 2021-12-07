<?php
defined('ABSPATH') || die('Access Denied');

class EcommercewdControllerShippingclasses extends EcommercewdController {
  public function __construct() {
    parent::__construct();
  }

  public function save() {
    $message = $this->store_input_in_row();
    WDFHelper::redirect('', '', '', '', $message);
  }

  protected function store_input_in_row() {
    $names = WDFInput::get('names', array(), 'array');
    $slugs = WDFInput::get('slugs', array(), 'array');
    $descriptions = WDFInput::get('descriptions', array(), 'array');
    $removed = WDFInput::get('removed', array(), 'array');
    global $wpdb;
    if ($removed) {
      $wpdb->query('DELETE FROM `' . $wpdb->prefix . 'ecommercewd_shippingclasses` WHERE `id` IN (' . $removed . ')');
    }
    foreach ($names as $key => $name) {
      if ($key != 'default' && $name) {
        $data = array(
          'name' => isset($names[$key]) ? $names[$key] : '',
          'slug' => isset($slugs[$key]) ? $slugs[$key] : '',
          'description' => isset($descriptions[$key]) ? $descriptions[$key] : '',
        );
        if (strpos($key, 'default') === FALSE) {
          $wpdb->update($wpdb->prefix . 'ecommercewd_shippingclasses', $data, array('id' => $key));
        }
        else {
          $wpdb->insert($wpdb->prefix . 'ecommercewd_shippingclasses', $data);
        }
      }
    }
    if ($wpdb->last_error) {
      return 32;
    }
    else {
      return 1;
    }
  }
}
