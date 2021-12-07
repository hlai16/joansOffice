<?php
defined('ABSPATH') || die('Access Denied');

class EcommercewdTableShippingzones {
  public $id = 0;
  public $name = '';
  public $country_ids = '';
  public $postcode = '';
  public $price = 0;
  public $free_shipping = 0;
  public $free_shipping_start_price = 0;
  public $shipping_type = 'per_bundle';
  public $shipping_classes_price = '';
  public $taxable = 0;
  public $ordering = 1;
  public $enabled = 1;
}
