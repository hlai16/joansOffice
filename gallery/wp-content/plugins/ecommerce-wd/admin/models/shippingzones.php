<?php
defined('ABSPATH') || die('Access Denied');

class EcommercewdModelShippingzones extends EcommercewdModel {
  public function get_row($id = 0) {
    $row = parent::get_row($id);
    $country_ids = isset($row->country_ids) ? explode(',', $row->country_ids) : FALSE;
    if ($country_ids) {
      foreach ($country_ids as $country_id) {
        $term = get_term($country_id, 'wde_countries');
        if (!is_wp_error($term)) {
          $country_names[] = $term->name;
        }
      }
    }
    $row->price = isset($row->price) ? WDFText::wde_number_format($row->price, 2) : '0.00';
    $row->free_shipping_start_price = isset($row->free_shipping_start_price) ? WDFText::wde_number_format($row->free_shipping_start_price, 2) : '0.00';
    $row->shipping_type = isset($row->shipping_type) ? $row->shipping_type : 'per_bundle';
    $row->country_names = isset($country_names) ? implode('&#13;', $country_names) : '';
    $row->shipping_classes_price = WDFJson::decode($row->shipping_classes_price);
    $row->shipping_classes = WDFHelper::get_shipping_classes();

    return $row;
  }

  protected function add_rows_query_order() {
    $query = ' ORDER BY `ordering` DESC';
    return $query;
  }

  public function get_rows() {
    $rows = parent::get_rows();
    if ($rows) {
      foreach ($rows as $row) {
        $country_ids = isset($row->country_ids) ? explode(',', $row->country_ids) : FALSE;
        $country_names = array();
        if ($country_ids) {
          foreach ($country_ids as $country_id) {
            $term = get_term($country_id, 'wde_countries');
            if (!is_wp_error($term)) {
              $country_names[$country_id] = isset($term->name) ? $term->name : '';
            }
          }
        }
        $row->country_names = $country_names;
      }
    }
    return $rows;
  }

  protected function init_rows_filters() {
    $filter_items = array();
    // name
    $filter_item = new stdClass();
    $filter_item->type = 'string';
    $filter_item->name = 'name';
    $filter_item->default_value = null;
    $filter_item->operator = 'like';
    $filter_item->input_type = 'text';
    $filter_item->input_label = __('Name', 'wde');
    $filter_item->input_name = 'search_name';
    $filter_items[$filter_item->name] = $filter_item;

    $this->rows_filter_items = $filter_items;

    parent::init_rows_filters();
  }
}