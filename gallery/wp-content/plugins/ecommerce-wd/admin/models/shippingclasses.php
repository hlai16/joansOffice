<?php
defined('ABSPATH') || die('Access Denied');

class EcommercewdModelShippingclasses extends EcommercewdModel {
  public function get_rows() {
    $rows = parent::get_rows();
    if ($rows) {
      $args = array(
        'post_type' => 'wde_products',
        'posts_per_page' => -1,
      );
      $posts = get_posts($args);
      $shipping_classes_count = array();
      $shipping_classes_count_posts = array();
      foreach ($posts as $post) {
        $shipping_class_ids = get_post_meta($post->ID, 'wde_shipping_class_ids', TRUE);
        $shipping_class_ids_arr = explode(',', $shipping_class_ids);
        foreach ($shipping_class_ids_arr as $shipping_class_id) {
          $shipping_classes_count_posts[$shipping_class_id][] = $post->ID;
          if (isset($shipping_classes_count[$shipping_class_id])) {
            ++$shipping_classes_count[$shipping_class_id];
          }
          else {
            $shipping_classes_count[$shipping_class_id] = 1;
          }
        }
      }
      foreach ($rows as $row) {
        $row->product_count = isset($shipping_classes_count[$row->id]) ? $shipping_classes_count[$row->id] : 0;
        $row->product_count_posts = isset($shipping_classes_count_posts[$row->id]) ? implode(',', $shipping_classes_count_posts[$row->id]) : 0;
        $slug = $row->slug ? $row->slug : $row->name;
        $row->slug = WDFHelper::get_unique_slug($slug, $row->id, 'ecommercewd_shippingclasses');
      }
    }
    return $rows;
  }

  public function get_shipping_class_defaults() {
    global $wpdb;
    $defaults = $wpdb->get_col('DESC `' . $wpdb->prefix . 'ecommercewd_shippingclasses`');
    if (!$defaults) {
      $defaults = array('id', 'name', 'slug', 'description');
    }
    $object = new stdClass;
    foreach ($defaults as $default) {
      $object->$default = '';
    }
    $object->id = 'default';
    return $object;
  }
}