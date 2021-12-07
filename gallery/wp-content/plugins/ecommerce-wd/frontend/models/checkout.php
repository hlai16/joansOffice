<?php
defined('ABSPATH') || die('Access Denied');

class EcommercewdModelCheckout extends EcommercewdModel {
  private static $MAX_PAYPAL_DESCRIPTION_LENGTH = 127;

  private $checkout_data;
  private $license_pages;

  public function init_checkout() {
    $row_default_currency = WDFDb::get_row('currencies', '`default`= 1');

    $checkout_data = array();
    $checkout_data['ses_id'] = time();
    $checkout_data['checkout_type'] = WDFInput::get_task() == 'quick_checkout' ? 'quick_checkout' : 'normal_checkout';
    $checkout_data['payment_method'] = '';
    $checkout_data['currency_id'] = $row_default_currency->id;
    $checkout_data['currency_code'] = $row_default_currency->code;
    $checkout_data['currency_sign'] = $row_default_currency->sign;
    $checkout_data['currency_sign_position'] = $row_default_currency->sign_position;
    $checkout_data['country_id'] = WDFInput::get('country_id', -1, 'int');
    $checkout_data['zipcode'] = WDFInput::get('zipcode', -1, 'string');
    $this->add_checkout_initial_data($checkout_data);
    $this->add_checkout_initial_products_data($checkout_data);
    $checkout_data['shipping_method'] = new stdClass();
    $_SESSION['checkout_data_' . $checkout_data['ses_id']] = serialize($checkout_data);

    return $checkout_data;
  }

  public function get_checkout_data() {
    if ($this->checkout_data == null) {
      $ses_id = WDFInput::get('ses_id', 0, 'float');
      if ($ses_id == 0) {
        WDFChecoutHelper::show_error(0, __('Invalid session', 'wde'));
      }
      $checkout_data = isset($_SESSION['checkout_data_' . $ses_id]) ? unserialize($_SESSION['checkout_data_' . $ses_id]) : '';
      $this->update_checkout_data($checkout_data);
      $_SESSION['checkout_data_' . $ses_id] = serialize($checkout_data);
      $this->checkout_data = $checkout_data;
    }
    return $this->checkout_data;
  }

  public function is_final_checkout_data_valid($check_payment_method = true) {
    $options_model = WDFHelper::get_model('options');
    $options = $options_model->get_options();
    $row_default_currency = WDFDb::get_row('currencies', '`default`=1');

    $checkout_data = $this->get_checkout_data();

    // validate checkout data
    $is_data_invalid = FALSE;
    if ($checkout_data == null) {
      $is_data_invalid = true;
      return __('Final checkout data is not valid.', 'wde');
    }
    // validate checkout type
    if ($is_data_invalid == false) {
      switch ($checkout_data['checkout_type']) {
        case 'normal_checkout':
        case 'quick_checkout':
          break;
        default:
          $is_data_invalid = true;
          return __('Checkout type is not valid.', 'wde');
          break;
      }
    }
    // validate currency
    if ($is_data_invalid == false) {
      if (($row_default_currency->id != $checkout_data['currency_id']) || ($row_default_currency->code != $checkout_data['currency_code'])) {
        $is_data_invalid = true;
        return __('The currency is not valid.', 'wde');
      }
    }
    // validate required fields
    if ($is_data_invalid == false) {
      if (($checkout_data['wde_billing_first_name'] == '')
        || (($options->user_data_middle_name == 2) && ($checkout_data['wde_billing_middle_name'] == ''))
        || (($options->user_data_last_name == 2) && ($checkout_data['wde_billing_last_name'] == ''))
        || (is_email($checkout_data['wde_billing_email']) == false)
        || (($options->user_data_company == 2) && ($checkout_data['wde_billing_company'] == ''))
        || (($options->user_data_country == 2) && (!intval($checkout_data['wde_billing_country_id'])))
        || (($options->user_data_state == 2) && ($checkout_data['wde_billing_state'] == ''))
        || (($options->user_data_city == 2) && ($checkout_data['wde_billing_city'] == ''))
        || (($options->user_data_address == 2) && ($checkout_data['wde_billing_address'] == ''))
        || (($options->user_data_mobile == 2) && ($checkout_data['wde_billing_mobile'] == ''))
        || (($options->user_data_phone == 2) && ($checkout_data['wde_billing_phone'] == ''))
        || (($options->user_data_fax == 2) && ($checkout_data['wde_billing_fax'] == ''))
        || (($options->user_data_zip_code == 2) && ($checkout_data['wde_billing_zip_code'] == ''))
        || (($options->user_data_middle_name == 2) && ($checkout_data['has_shipping']) && ($checkout_data['wde_shipping_middle_name'] == ''))
        || (($options->user_data_last_name == 2) && ($checkout_data['has_shipping']) && ($checkout_data['wde_shipping_last_name'] == ''))
        || (($options->user_data_company == 2) && ($checkout_data['has_shipping']) && ($checkout_data['wde_shipping_company'] == ''))
        || (($options->user_data_country == 2) && ($checkout_data['has_shipping']) && (!intval($checkout_data['wde_shipping_country_id'])))
        || (($options->user_data_state == 2) && ($checkout_data['has_shipping']) && ($checkout_data['wde_shipping_state'] == ''))
        || (($options->user_data_city == 2) && ($checkout_data['has_shipping']) && ($checkout_data['wde_shipping_city'] == ''))
        || (($options->user_data_address == 2) && ($checkout_data['has_shipping']) && ($checkout_data['wde_shipping_address'] == ''))
        || (($options->user_data_zip_code == 2) && ($checkout_data['has_shipping']) && ($checkout_data['wde_shipping_zip_code'] == ''))) {
        $is_data_invalid = true;
        return __('Fill in required fields.', 'wde');
      }
    }
    // validate products
    if ($is_data_invalid == false) {
      $products_data = $checkout_data['products_data'];
      if (empty($products_data) == false) {
        foreach ($products_data as $product_data) {
          // check product_exists
          $query = new WP_Query(array(
            'post_type' => 'wde_products',
            'post__in' => array($product_data->id),
            'post_status' => array('publish')
          ));
          if (!$query->have_posts()) {
            $is_data_invalid = true;
            return __('The product is not valid.', 'wde');
          }

          $row_product = new stdClass();
          $row_product->id = $product_data->id;
          // check product in shopping cart (for normal checkout)
          if ($checkout_data['checkout_type'] == 'normal_checkout') {
            $user_order_product_ids = $this->get_user_order_product_ids();
            $row_order_product = WDFDb::get_row_by_id('orderproducts', $product_data->order_product_id);
            if (($row_order_product->id == 0) || ($row_order_product->product_id != $row_product->id) || (in_array($row_order_product->id, $user_order_product_ids) == false)) {
              $is_data_invalid = true;
              return __('Final checkout data is not valid.', 'wde');
            }
          }
          $fields = array('unlimited', 'amount_in_stock', 'parameters');
          foreach ($fields as $field) {
            $row_product->$field = esc_attr(get_post_meta($product_data->id, 'wde_' . $field, TRUE));
          }

          // Check availability.
          if (($row_product->unlimited == 0) && ($row_product->amount_in_stock < $product_data->count)) {
            $is_data_invalid = true;
            return __('The product is out of stock.', 'wde');
          }
          $parameters = WDFJson::decode($row_product->parameters);

          // check parameters
          if (is_array($product_data->parameters)) {
            foreach ($product_data->parameters as $parameter_id => $parameter_value) {
              if (is_array($parameters)) {
                foreach ($parameters as $parameter) {
                  if ($parameter->id == $parameter_id) {
                    $type_id = $parameter->type_id;
                  }
                }
              }
              if ($type_id != 1) {
                if (!is_array($parameter_value)) {
                  $parameter_value = array($parameter_value);
                }
                foreach ($parameter_value as $value) {
                  if ($value != "" && $value != '0') {
                    $parameter_term_meta = get_term_by('id', $parameter_id, 'wde_parameters');
                    $par_parameter_terms = get_terms('wde_par_' . $parameter_term_meta->slug, array('hide_empty' => 0));
                    $parameter_values_exists = FALSE;
                    if (is_array($par_parameter_terms)) {
                      foreach ($par_parameter_terms as $par_parameter_term) {
                        if (esc_html($par_parameter_term->name) == esc_html($value)) {
                          $parameter_values_exists = TRUE;
                          break;
                        }
                      }
                    }
                    if ($parameter_values_exists == false) {
                      $is_data_invalid = true;
                      return __('The parameter has no values.', 'wde');
                    }
                  }
                }
              }
              else {
                $is_data_invalid = false;
              }
            }
          }
          // Check if address is valid.
          $is_address_valid = WDFProduct::get_product_shippings($product_data, $checkout_data);
          if ($is_address_valid === FALSE) {
            $is_data_invalid = true;
            return __('This item cannot be shipped to your country.', 'wde');
          }
        }
      }
    }
    return $is_data_invalid == false ? true : __('Final checkout data is not valid.', 'wde');
  }

  public function get_final_checkout_data($init = '') {
    $options_model = WDFHelper::get_model('options');
    $options = $options_model->get_options();
    $row_default_currency = WDFDb::get_row('currencies', '`default`=1');
    $option_include_tax_in_checkout_price = isset($options->option_include_tax_in_checkout_price) ? $options->option_include_tax_in_checkout_price : 0;

    $decimals_supported = in_array($row_default_currency->code, WDFPaypalstandard::$currencies_without_decimal_support) == true ? false : true;
    $decimals_to_show = (($options->option_show_decimals == 1) && ($decimals_supported == true)) ? 2 : 0;

    $ses_id = WDFInput::get('ses_id', 0, 'float');

    $final_checkout_data = unserialize($_SESSION['checkout_data_' . $ses_id]);
    if ($final_checkout_data['wde_shipping_country_id']) {
      $row_shipping_country = get_term_by('id', $final_checkout_data['wde_shipping_country_id'], 'wde_countries');
      $final_checkout_data['wde_shipping_country'] = $row_shipping_country->name;
    }
    else {
      $final_checkout_data['wde_shipping_country'] = '';
    }
    if ($final_checkout_data['wde_billing_country_id']) {
      $row_billing_country = get_term_by('id', $final_checkout_data['wde_billing_country_id'], 'wde_countries');
      $final_checkout_data['wde_billing_country'] = $row_billing_country->name;
    }
    else {
      $final_checkout_data['wde_billing_country'] = '';
    }

    if (empty($final_checkout_data['products_data']) == true) {
      $options_model->enqueue_message(__('No products to checkout', 'wde'), 'danger');
      if ($final_checkout_data['checkout_type'] == 'quick_checkout') {
        wp_redirect(get_permalink($options->option_all_products_page));
        exit;
      }
      else {
        wp_redirect(get_permalink($options->option_shopping_cart_page));
        exit;
      }
    }

    $products_data =& $final_checkout_data['products_data'];
    $total_price = 0;
    $total_shipping_price = 0;
    $total_items_count = 0;
    $per_order_shipping_methods = array();

    $shipping_method_products_map = array();

    if (is_array($products_data)) {
      foreach ($products_data as $product_data) {
        $product_data->name = $product_data->post_title;
        $url = '';
        if (!has_post_thumbnail($product_data->id)) {
          $image_ids_string = get_post_meta($product_data->id, 'wde_images', TRUE);
          $image_ids = explode(',', $image_ids_string);
          if (isset($image_ids[0]) && is_numeric($image_ids[0]) && $image_ids[0] != 0) {
            $image_id = (int) $image_ids[0];
            $url = wp_get_attachment_url($image_id);
          }
        }
        else {
          $url = wp_get_attachment_url(get_post_thumbnail_id($product_data->id));
        }
        $product_data->image = $url;

        $product_data->price = esc_attr(get_post_meta($product_data->id, 'wde_price', TRUE));
        $product_data->price = WDFText::float_val($product_data->price, $decimals_to_show);
        $current_price = $product_data->price;

        // parameters
        $parameters_obj = $this->get_product_parameters_string($product_data);
        $parameters = $parameters_obj['str'];
        if ($parameters === false) {
          WDFChecoutHelper::show_error($final_checkout_data['ses_id'], __('Error getting product parameters', 'wde'));
        }
        $product_data->parameters_string = $parameters;
        $product_data->parameters_price = WDFText::float_val($parameters_obj['price'], $decimals_to_show);
        $product_data->parameters_price_text = WDFHelper::price_text($product_data->parameters_price, $decimals_to_show, $row_default_currency);
        $current_price += $product_data->parameters_price;

        $product_data->current_price_text = WDFHelper::price_text($current_price, $decimals_to_show, $row_default_currency);

        $product_data->discount_rate = '';
        $product_data->discount = 0;
        $product_data->discount_text = '';
        $discounts = wp_get_object_terms($product_data->id, 'wde_discounts');
        if ($discounts) {
          $discount = $discounts[0];
          if (isset($discount->term_id)) {
            $discount = get_option('wde_discounts_' . $discount->term_id);
            if (isset($discount['rate']) && $discount['rate']) {
              $discount_rate = $discount['rate'];
              $product_data->discount_rate = $discount_rate . '%';
              $product_data->discount = WDFText::float_val(($current_price * $discount_rate / 100), $decimals_to_show);
              $product_data->discount_text = WDFHelper::price_text($product_data->discount, $decimals_to_show, $row_default_currency);
            }
          }
        }
        $current_price -= $product_data->discount;
        $product_data->current_price = $current_price;

        $calculated_tax_rates = WDFHelper::calculate_tax_rates($current_price, $product_data->id);

        $product_data->tax_rate = 0;
        $product_data->tax_info = '';
        $product_data->tax_name = '';
        $product_data->tax_total = 0;
        $product_data->tax_total_text = '';
        // $product_data->price = $current_price;
        $product_data->shipping_tax = FALSE;
        if ($calculated_tax_rates) {
          $product_data->tax_rate = $calculated_tax_rates['tax_total'];
          $product_data->tax_info = $calculated_tax_rates['tax_info'];
          $product_data->tax_name = $calculated_tax_rates['tax_name'];
          $product_data->tax_total = $calculated_tax_rates['tax_total'];
          $product_data->tax_total_text = $calculated_tax_rates['tax_total_text'];
          $product_data->shipping_tax = $calculated_tax_rates['shipping_tax'];
          // $product_data->price = $calculated_tax_rates['price'];
          $current_price = $option_include_tax_in_checkout_price ? $calculated_tax_rates['tax_price'] : $calculated_tax_rates['price'];
        }

        $product_data->price_text = WDFHelper::price_text($current_price, $decimals_to_show, $row_default_currency);
        ob_start();
        if ($product_data->current_price_text) {
          ?>
          <span><?php echo __('Price', 'wde') . ':&nbsp;' . $product_data->current_price_text; ?></span>
          <br />
          <?php
        }
        if ($product_data->discount_rate) {
          ?>
          <span><?php echo __('Discount', 'wde') . ':&nbsp;' . $product_data->discount_rate; ?></span>
          <br />
          <?php
        }
        if ($option_include_tax_in_checkout_price && $product_data->tax_info) {
          if ($options->tax_total_display == 'itemized') {
            foreach ($product_data->tax_info as $tax_info) {
              ?>
            <span>
              <?php echo ($tax_info['name'] != '' ? $tax_info['name'] : __('Tax', 'wde')) . ': '; ?>
            </span>
            <span><?php echo $tax_info['tax_text']; ?></span><br />
              <?php
            }
          }
          elseif ($product_data->tax_total_text) {
            ?>
            <span><?php _e('Tax', 'wde'); ?>:</span>
            <span><?php echo $product_data->tax_total_text; ?></span>
            <?php
          }
        }
        $product_data->price_info = WDFTextUtils::remove_html_spaces(ob_get_clean());

        $product_data->subtotal_price = $product_data->count * ($current_price + $product_data->tax_total);

        $total_price += $product_data->subtotal_price;
      }
      $has_shipping = false;
      foreach ($products_data as $product_data) {
        $product_data->shipping_method_row = '';
        $product_data->shipping_price_text = '';
        $product_data->decimals_to_show = $decimals_to_show;
        $product_data->row_default_currency = $row_default_currency;
        $shipping_method_row = WDFProduct::get_product_shippings($product_data, $final_checkout_data);
        // Check the product has no shipping or has shipping but "Enable shipping" is set to "No".
        if ($shipping_method_row) {
          $has_checked_shipping_method = false;
          foreach ($shipping_method_row->shipping_classes as $shipping_class) {
            if ($shipping_class) {
              // Checked.
              if ((isset($_POST['product_shipping_method_id_' . $product_data->id . '_' . $product_data->order_product_id]) && $shipping_class->id == $_POST['product_shipping_method_id_' . $product_data->id . '_' . $product_data->order_product_id])) {
                $shipping_class->checked = true;
                $current_shipping = $shipping_class;
                $has_checked_shipping_method = true;
                $product_data->shipping_method_id = $shipping_class->id;
              }
              elseif ($shipping_class->id == $product_data->shipping_method_id) {
                $shipping_class->checked = true;
                $current_shipping = $shipping_class;
                $has_checked_shipping_method = true;
              }
              else {
                $shipping_class->checked = false;
              }
            }
          }
          // Check first shipping method if there is no checked shipping methods.
          if ($has_checked_shipping_method == false) {
            foreach ($shipping_method_row->shipping_classes as $shipping_class) {
              $shipping_class->checked = true;
              $current_shipping = $shipping_class;
              break;
            }
          }
          if (!isset($current_shipping)) {
            $current_shipping = new stdClass();
          }
          if (!isset($current_shipping->id)) {
            $current_shipping->id = '';
          }
          if (!isset($current_shipping->price)) {
            $current_shipping->price = '';
          }
          $product_data->shipping_method_id = $current_shipping->id;
          $product_data->shipping_method_row = $current_shipping;

          if ($shipping_method_row->free_shipping == 1 || ($shipping_method_row->free_shipping == 2 && $total_price >= $shipping_method_row->free_shipping_start_price)) {
            $shipping_price = 0;
            $product_data->shipping_price = 0;
          }
          else {
            $shipping_count = $shipping_method_row->shipping_type == 'per_unit' ? $product_data->count : 1;
            $shipping_price = $shipping_count * $current_shipping->price;
            $product_data->shipping_price = $current_shipping->price;
          }

          $product_data->shipping_price_text = $options->option_order_shipping_type == 'per_order' ? '' : WDFHelper::price_text($product_data->shipping_price, $decimals_to_show, $row_default_currency);

          if ($options->option_order_shipping_type == 'per_item') {
            $product_data->subtotal_price += $shipping_price;
            $total_shipping_price += $shipping_price;
          }
          else {
            if ( $shipping_method_row->free_shipping == 1 || ($shipping_method_row->free_shipping == 2 && $total_price >= $shipping_method_row->free_shipping_start_price) ) {
              // Free shipping over certain price when shipping is per order.
              $total_shipping_price = 0;
            }
            else {
              // Collect products with same shipping method when shipping rate calculation type is per order.
              if ( !isset($per_order_shipping_methods[$current_shipping->id]) ) {
                $per_order_shipping_methods[$current_shipping->id] = $shipping_price;
                $total_shipping_price += $shipping_price;
              }
              elseif ( $shipping_method_row->shipping_type == 'per_unit' ) {
                $total_shipping_price += $shipping_price;
              }
            }
          }
        }
        $product_data->shipping_method_rows = $shipping_method_row;
        $has_shipping = $has_shipping || !empty($shipping_method_row);
        $product_data->subtotal_price_text = WDFHelper::price_text($product_data->subtotal_price, $decimals_to_show, $row_default_currency);
      }
    }
    $final_checkout_data['has_shipping'] = $has_shipping || !empty($total_shipping_price);
    $final_checkout_data['shipping_method']->shipping_type = $options->option_order_shipping_type;
    $final_checkout_data['shipping_method']->shipping_method_price = $total_shipping_price;
    $final_checkout_data['shipping_method']->shipping_method_price_text = WDFHelper::price_text($total_shipping_price, $decimals_to_show, $row_default_currency);
    $final_checkout_data['total_price_without_shipping'] = $total_price;
    $total_price += $total_shipping_price; // If shipping rate calculation type is per order.
    $final_checkout_data['total_price'] = $total_price;
    $checkout_data = unserialize($_SESSION['checkout_data_' . $ses_id]);
    $final_checkout_data['total_price_changed'] = isset($checkout_data['total_price']) ? ($total_price != $checkout_data['total_price']) : true;
    $checkout_data['total_price'] = $total_price;
    $checkout_data['products_data'] = $products_data;
    $checkout_data['has_shipping'] = $final_checkout_data['has_shipping'];
    $_SESSION['checkout_data_' . $checkout_data['ses_id']] = serialize($checkout_data);
    $final_checkout_data['total_price_text'] = WDFHelper::price_text($total_price, $decimals_to_show, $row_default_currency);

    return $final_checkout_data;
  }

  public function store_checkout_data() {
    // check for 0 products checkout
    // is data valid
    if ($this->is_final_checkout_data_valid() != TRUE) {
      return false;
    }

    $final_checkout_data = $this->get_final_checkout_data(1);
    if ($final_checkout_data === false) {
      return false;
    }

    // clear order session
    WDFSession::clear('checkout_data_' . $final_checkout_data['ses_id'], true, false);

    if ($final_checkout_data === false) {
      return false;
    }
    $row_default_order_status = WDFDb::get_row('orderstatuses', '`default`=1');

    $cur_date = current_time('Y-m-d H:i:s');

    $model_orders = WDFHelper::get_model('orders');
    $rand_id = $model_orders->get_order_rand_id();
    if ($rand_id === false) {
      return false;
    }

    $row_order = WDFDb::get_row_by_id('orders');
    $row_order->rand_id = $rand_id;
    $row_order->checkout_type = $final_checkout_data['checkout_type'];
    $row_order->checkout_date = $cur_date;
    $row_order->date_modified = $cur_date;
    $row_order->j_user_id = $final_checkout_data['j_user_id'];
    $row_order->user_ip_address = $final_checkout_data['user_ip_address'];
    $row_order->status_id = $row_default_order_status->id;
    $row_order->status_name = $row_default_order_status->name;
    $row_order->payment_method = $final_checkout_data['payment_method'];
    $row_order->payment_data = WDFJson::encode(array());
    $row_order->payment_data_status = '';
    $row_order->billing_data_first_name = $final_checkout_data['wde_billing_first_name'];
    $row_order->billing_data_middle_name = $final_checkout_data['wde_billing_middle_name'];
    $row_order->billing_data_last_name = $final_checkout_data['wde_billing_last_name'];
    $row_order->billing_data_email = $final_checkout_data['wde_billing_email'];
    $row_order->billing_data_company = $final_checkout_data['wde_billing_company'];
    $row_order->billing_data_country_id = $final_checkout_data['wde_billing_country_id'];
    $row_order->billing_data_country = $final_checkout_data['wde_billing_country'] ? $final_checkout_data['wde_billing_country'] : '';
    $row_order->billing_data_state = $final_checkout_data['wde_billing_state'];
    $row_order->billing_data_city = $final_checkout_data['wde_billing_city'];
    $row_order->billing_data_address = $final_checkout_data['wde_billing_address'];
    $row_order->billing_data_mobile = $final_checkout_data['wde_billing_mobile'];
    $row_order->billing_data_phone = $final_checkout_data['wde_billing_phone'];
    $row_order->billing_data_fax = $final_checkout_data['wde_billing_fax'];
    $row_order->billing_data_zip_code = $final_checkout_data['wde_billing_zip_code'];		
    $row_order->shipping_data_first_name = $final_checkout_data['wde_shipping_first_name'];
    $row_order->shipping_data_middle_name = $final_checkout_data['wde_shipping_middle_name'];
    $row_order->shipping_data_last_name = $final_checkout_data['wde_shipping_last_name'];
    $row_order->shipping_data_company = $final_checkout_data['wde_shipping_company'];
    $row_order->shipping_data_country_id = $final_checkout_data['wde_shipping_country_id'];
    $row_order->shipping_data_country = $final_checkout_data['wde_shipping_country'] ? $final_checkout_data['wde_shipping_country'] : '';
    $row_order->shipping_data_state = $final_checkout_data['wde_shipping_state'];
    $row_order->shipping_data_city = $final_checkout_data['wde_shipping_city'];
    $row_order->shipping_data_address = $final_checkout_data['wde_shipping_address'];
    $row_order->shipping_data_zip_code = $final_checkout_data['wde_shipping_zip_code'];
    $shipping_method = $final_checkout_data['shipping_method'];
    $row_order->shipping_type = $shipping_method->shipping_type;
    $row_order->shipping_method_price = $shipping_method->shipping_method_price;
    $row_order->currency_id = $final_checkout_data['currency_id'];
    $row_order->currency_code = $final_checkout_data['currency_code'];
    $row_order->read = 0;

    global $wpdb;
    $wpdb->replace($wpdb->prefix . 'ecommercewd_orders', (array) $row_order);
    $row_order = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'ecommercewd_orders WHERE id="%d"', $wpdb->insert_id));
    if ($wpdb->last_error) {
      return false;
    }
    $final_checkout_data['order_id'] = $wpdb->insert_id;
    $final_checkout_data['order_rand_id'] = $row_order->rand_id;
    
    $products_data =& $final_checkout_data['products_data'];

    // decrease products count
    if (is_array($products_data)) {
      foreach ($products_data as $product_data) {
        $unlimited = esc_attr(get_post_meta($product_data->id, 'wde_unlimited', TRUE));
        $amount_in_stock = esc_attr(get_post_meta($product_data->id, 'wde_amount_in_stock', TRUE));
        if ($unlimited == 0) {
          update_post_meta($product_data->id, 'wde_amount_in_stock', max(0, $amount_in_stock - $product_data->count));
        }
      }
    }

    // create order product rows if quick checkout, modify rows if not
    $updated_order_product_ids = array();
    if (is_array($products_data)) {
      foreach ($products_data as $product_id => $product_data) {
        $product_data =& $products_data[$product_id];
        $order_product_id = $final_checkout_data['checkout_type'] == 'quick_checkout' ? null : $product_data->order_product_id;
        $row_order_product = WDFDb::get_row_by_id('orderproducts', $order_product_id);
        $row_order_product->order_id = $final_checkout_data['order_id'];          
        $row_order_product->j_user_id = $final_checkout_data['j_user_id'];
        $row_order_product->user_ip_address = $final_checkout_data['user_ip_address'];
        $row_order_product->product_id = $product_data->id;
        $row_order_product->product_name = $product_data->name;
        $row_order_product->product_image = $product_data->image;
        $row_order_product->product_parameters = $product_data->parameters_string;
        $row_order_product->product_parameters_price = $product_data->parameters_price;
        $row_order_product->product_price = $product_data->price;
        $row_order_product->tax_id = isset($product_data->tax_id) ? $product_data->tax_id : 0;
        $row_order_product->tax_name = isset($product_data->tax_name) ? $product_data->tax_name : '';
        $row_order_product->tax_price = $product_data->tax_total;
        $row_order_product->tax_info = json_encode($product_data->tax_info);
        $row_order_product->discount_rate = $product_data->discount_rate;
        $row_order_product->discount = $product_data->discount;
        // $row_order_product->subtotal_price = $product_data->subtotal_price;
        $shipping_method = $product_data->shipping_method_row;
        $row_order_product->shipping_method_type = isset($shipping_method->shipping_type) ? $shipping_method->shipping_type : '';
        $row_order_product->shipping_method_id = isset($shipping_method->id) ? $shipping_method->id : '';
        $row_order_product->shipping_method_name = isset($shipping_method->name) ? $shipping_method->name : '';
        $row_order_product->shipping_method_price = isset($shipping_method->price) ? $shipping_method->price : '';
        $row_order_product->product_count = $product_data->count;
        $row_order_product->currency_id = $final_checkout_data['currency_id'];
        $row_order_product->currency_code = $final_checkout_data['currency_code'];
        $saved = $wpdb->replace($wpdb->prefix . 'ecommercewd_orderproducts', (array) $row_order_product);

        if (!$saved) {
          // remove order row
          WDFDb::remove_rows('orders', $final_checkout_data['order_id']);

          // delete / restore order product rows
          if ($final_checkout_data['checkout_type'] == 'quick_checkout') {
            WDFDb::remove_rows('orderproducts', $updated_order_product_ids);
          }
          else {
            if (is_array($updated_order_product_ids)) {
              foreach ($updated_order_product_ids as $order_product_id) {
                $wpdb->update($wpdb->prefix . 'ecommercewd_orderproducts', array('order_id' => 0, 'product_parameters' => WDFJson::encode(array())), array('id' => $order_product_id));
              }
            }
          }
          return false;
        }
        $product_data->order_product_id = $row_order_product->id;
        $updated_order_product_ids[] = $row_order_product->id;
      }
    }

    // if checkout is anonymouse store order ids in cookies
    if ($final_checkout_data['j_user_id'] == 0) {
      $order_rand_ids = WDFInput::cookie_get_array('order_rand_ids', array());
      $order_rand_ids[] = stripslashes($final_checkout_data['order_rand_id']);
      WDFInput::cookie_set_array('order_rand_ids', $order_rand_ids);
    }
    
    return $final_checkout_data;
  }
  
  public function get_data_form_fields() {
    $j_user = wp_get_current_user();
    $checkout_data = $this->get_checkout_data();
    $form_fields = WDFDb::get_user_meta_fields_list($j_user->ID, false, true);
    foreach ($form_fields["billing_fields_list"] as $i => $form_field) {
      $form_fields["billing_fields_list"][$i]['value'] = $checkout_data[$form_fields["billing_fields_list"][$i]['id']];
    }
    foreach ($form_fields["shipping_fields_list"] as $i => $form_field) {
      $form_fields["shipping_fields_list"][$i]['value'] = $checkout_data[$form_fields["shipping_fields_list"][$i]['id']];
    }
    return $form_fields;
  }
  
  public function get_license_pages() {
    if ($this->license_pages == null) {
      $checkout_data = $this->get_checkout_data();
      $products_data = $checkout_data['products_data'];

      if (is_array($products_data)) {
        foreach ($products_data as $product_data) {
          $page_ids_string = get_post_meta($product_data->id, 'wde_page_ids', TRUE);
        }
      }
      
      $page_ids = explode(',', $page_ids_string);
      $pages = array();
      foreach ($page_ids as $page_id) {
        if ($page_id) {
          $page = array();
          $post = get_post($page_id);
          $page['title'] = $post->post_title;
          $page['text'] = do_shortcode($post->post_content);
          $pages[$page_id] = $page;
        }
      }
      $this->license_pages = $pages;
      $this->license_pages = array_unique($this->license_pages, SORT_REGULAR);
      $this->license_pages = array_values($this->license_pages);
    }

    return $this->license_pages;
  }

  public function get_payment_buttons_data($total_price) {
    $options_model = WDFHelper::get_model('options');
    $options = $options_model->get_options();
    $checkout_options = $this->checkout_options();
    $checkout_data = $this->get_checkout_data();
    $action_finish_checkout = WDFPath::add_pretty_query_args(get_permalink($options->option_checkout_page), $options->option_endpoint_checkout_confirm_order, '1', FALSE);
    $data = array(
      'ses_id' => $checkout_data['ses_id'],
      'data' => 'confirm_data',
      'local_task' => 'finish_checkout',
    );
    $action_finish_checkout = add_query_arg($data, $action_finish_checkout);
    $action_show_form = '';
    $payments_data_array = array();
    if (isset($checkout_options['without_online_payment']) == true && $checkout_options['without_online_payment']->published) {
      $payment_button_data_without_online_payment = new stdClass();
      $payment_button_data_without_online_payment->text = __('Without online payment', 'wde');
      $data = array(
        'payment_method' => 'without_online_payment',
        'controller' => 'withoutonline',
      );
      $payment_button_data_without_online_payment->action = add_query_arg($data, $action_finish_checkout);
      $payments_data_array["without_online_payment"] = $payment_button_data_without_online_payment;
    }

    if (isset($checkout_options['paypalstandard']) == true && $checkout_options['paypalstandard']->published) {
      $payment_button_data_paypal_standard = new stdClass();
      $payment_button_data_paypal_standard->text = __('Pay with paypal', 'wde');
      $data = array(
        'payment_method' => 'paypalstandard',
        'controller' => 'paypalstandard',
      );
      $payment_button_data_paypal_standard->action = add_query_arg($data, $action_finish_checkout);
      $payments_data_array["paypalstandard"] = $payment_button_data_paypal_standard;
    }

    if (isset($checkout_options['authorizenetaim']) == true && $checkout_options['authorizenetaim']->published) {
      $payment_button_data_authorizenet_aim = new stdClass();
      $payment_button_data_authorizenet_aim->text = __('Pay with authorizenet AIM', 'wde');
      $payment_button_data_authorizenet_aim->action = $action_show_form . '&payment_method=authorizenetaim&controller=authorizenetaim';
      $payments_data_array["authorizenetaim"] = $payment_button_data_authorizenet_aim;
    }

    if (isset($checkout_options['authorizenetsim']) == true && $checkout_options['authorizenetsim']->published) {
      $checkout_api_options = $this->checkout_api_options('authorizenetsim');
      $payment_button_data_authorizenet_sim = new stdClass();
      $payment_button_data_authorizenet_sim->text = __('Pay with authorizenet SIM', 'wde');         
      $sim_form_data = WDFHelper::get_model('authorizenetsim')->get_checkout_form_data();			
      $payment_button_data_authorizenet_sim->action = $sim_form_data['form_action'];
      $payments_data_array["authorizenetsim"] = $payment_button_data_authorizenet_sim;
    }

    if (isset($checkout_options['authorizenetdpm']) == true && $checkout_options['authorizenetdpm']->published) {
      $payment_button_data_authorizenet_dpm = new stdClass();
      $payment_button_data_authorizenet_dpm->text = __('Pay with authorizenet DPM', 'wde');
      $payment_button_data_authorizenet_dpm->action = $action_show_form . '&payment_method=authorizenetdpm&controller=authorizenetdpm';
      $payments_data_array["authorizenetdpm"] = $payment_button_data_authorizenet_dpm;
    }

    /*
      * Add Payment Buttons.
      * Since version 2.1.3
    */
    $payments_data_array = apply_filters('wde_payment_buttons', $payments_data_array, $checkout_data['ses_id']);

    // get available payment methods, check if count is more than 1
    $payment_buttons_data = array();
    if (is_array($payments_data_array)) {
      foreach ($payments_data_array as $key => $value){
        if ($total_price != 0 || $key == 'without_online_payment') {
          $payment_buttons_data[$key] = $payments_data_array[$key];
        }
      }
    }
    return $payment_buttons_data;
  }

  public function get_pager_data() {
    $options_model = WDFHelper::get_model('options');
    $options = $options_model->get_options();
    $checkout_data = $this->get_checkout_data();
    $license_pages = $this->get_license_pages();
    if ($license_pages === false) {
      WDFChecoutHelper::show_error($checkout_data['ses_id'], __('Error getting license pages', 'wde'));
    }
    $has_license_pages = empty($license_pages) == false ? true : false;

    $pager_data = array();

    $btn_cancel_checkout_data = array();
    $btn_cancel_checkout_data['url'] = get_permalink($options->option_shopping_cart_page);
    $pager_data['btn_cancel_checkout_data'] = $btn_cancel_checkout_data;

    $action_display_shipping_data = WDFPath::add_pretty_query_args(get_permalink($options->option_checkout_page), $options->option_endpoint_checkout_shipping_data, '1', FALSE);
    $action_display_shipping_data = add_query_arg('ses_id' , $checkout_data['ses_id'], $action_display_shipping_data);
    $action_display_license_pages = WDFPath::add_pretty_query_args(get_permalink($options->option_checkout_page), $options->option_endpoint_checkout_license_pages, '1', FALSE);
    $action_display_license_pages = add_query_arg('ses_id' , $checkout_data['ses_id'], $action_display_license_pages);
    $action_display_confirm_order = WDFPath::add_pretty_query_args(get_permalink($options->option_checkout_page), $options->option_endpoint_checkout_confirm_order, '1', FALSE);
    $action_display_confirm_order = add_query_arg('ses_id' , $checkout_data['ses_id'], $action_display_confirm_order);
    switch (WDFInput::get_task()) {
      case 'displayshippingdata':
        $btn_next_page_data = array();
        if ($has_license_pages == true) {
          $btn_next_page_data['text'] = __('Licensing', 'wde');
          $btn_next_page_data['action'] = $action_display_license_pages;
        }
        else {
          $btn_next_page_data['text'] = __('Confirm order', 'wde');
          $btn_next_page_data['action'] = $action_display_confirm_order;
        }
        $pager_data['btn_next_page_data'] = $btn_next_page_data;
        break;
      case 'displaylicensepages':
        $btn_prev_page_data = array();
        $btn_prev_page_data['text'] = __('Shipping data', 'wde');
        $btn_prev_page_data['action'] = $action_display_shipping_data;
        $pager_data['btn_prev_page_data'] = $btn_prev_page_data;

        $btn_next_page_data = array();
        $btn_next_page_data['text'] = __('Confirm order', 'wde');

        $btn_next_page_data['action'] = $action_display_confirm_order;
        $pager_data['btn_next_page_data'] = $btn_next_page_data;
        break;
      case 'displayconfirmorder':
        $btn_prev_page_data = array();
        if ($has_license_pages == true) {
          $btn_prev_page_data['text'] = __('Licensing', 'wde');
          $btn_prev_page_data['action'] = $action_display_license_pages;
        }
        else {
          $btn_prev_page_data['text'] = __('Shipping data', 'wde');
          $btn_prev_page_data['action'] = $action_display_shipping_data;
        }
        $pager_data['btn_prev_page_data'] = $btn_prev_page_data;
        break;
    }

    return $pager_data;
  }

  public function checkout_options() {
    global $wpdb;
    $query = 'SELECT short_name, name, base_name, options, published FROM ' . $wpdb->prefix . 'ecommercewd_payments ORDER BY ordering';
    $payments = $wpdb->get_results($query, OBJECT_K);
    if ($wpdb->last_error) {
      return false;
    }
    foreach ($payments as $i => $row) {
      switch ($row->base_name) {
        case 'paypalstandard' : {
          $row->options = WDFJson::decode($row->options);
          if (!$row->options->paypal_email) {
            unset($payments[$i]);
          }
          break;
        }
        case 'paypalexpress' : {
          $row->options = WDFJson::decode($row->options);
          if (!$row->options->paypal_user || !$row->options->paypal_password || !$row->options->paypal_signature) {
            unset($payments[$i]);
          }
          break;
        }
        default : {
          break;
        }
      }
    }
    return $payments;
  }

  public function checkout_api_options($short_name){
    global $wpdb;
    $query = 'SELECT options FROM ' . $wpdb->prefix . 'ecommercewd_payments WHERE short_name="' . $short_name .'"';
    $options = $wpdb->get_var($query);
    if ($wpdb->last_error) {
      return false;
    }
    $options = WDFJson::decode($options);
    return 	$options;
  }

  private function add_checkout_initial_data(&$checkout_data) {
    $options_model = WDFHelper::get_model('options');
    $options = $options_model->get_options();
    $j_user = wp_get_current_user();
    $user_row = WDFDb::get_user_meta_fields_list($j_user->ID, false, true);
    $country_id = $checkout_data['country_id'];
    $zipcode = $checkout_data['zipcode'];
    $shipping_destination = isset($options->shipping_destination) ? $options->shipping_destination : 'shipping';

    foreach ($user_row["billing_fields_list"] as $i => $form_field) {
      if ($country_id != -1 && $shipping_destination != 'shipping' && $form_field['id'] == 'wde_billing_country_id') {
        $value = $country_id;
      }
      elseif ($zipcode != -1 && $shipping_destination != 'shipping' && $form_field['id'] == 'wde_shipping_zip_code') {
        $value = $zipcode;
      }
      else {
        $value = $form_field['value'];
      }
      $id = $form_field['id'];
      $checkout_data[$id] = $value;
    }
    foreach ($user_row["shipping_fields_list"] as $i => $form_field) {
      if ($shipping_destination != 'shipping') {
        foreach ($user_row["billing_fields_list"] as $billing_field) {
          if ($billing_field['id'] == str_replace('_shipping', '_billing', $form_field['id'])) {
            $value = $billing_field['value'];
            break;
          }
        }
      }
      else {
        $value = $form_field['value'];
      }
      if ($country_id != -1 && $form_field['id'] == 'wde_shipping_country_id') {
        $value = $country_id;
      }
      if ($zipcode != -1 && $form_field['id'] == 'wde_shipping_zip_code') {
        $value = $zipcode;
      }
      $id = $form_field['id'];
      $checkout_data[$id] = $value;
    }
  }

  private function add_checkout_initial_products_data(&$checkout_data) {
    $options_model = WDFHelper::get_model('options');
    $options = $options_model->get_options();
    $products_data = array();

    // if products data not inited
    $task = WDFInput::get_task();

    switch ($task) {
      case 'checkout_product':
        // ckeck if user has this product in shopping cart
        $order_product_id = WDFInput::get('order_product_id', 0, 'int');
        $order_product_ids = $this->get_user_order_product_ids();
        if (($order_product_ids === false) || (empty($order_product_ids) == true) || (in_array($order_product_id, $order_product_ids) == false)) {
          WDFHelper::show_error(4);
        }
        // add product data
        $product_data = $this->get_product_data_by_order_product_id($order_product_id);
        if ($product_data === false) {
          WDFChecoutHelper::show_error($checkout_data['ses_id'], __('Error getting product data', 'wde'));
        }
        $products_data[$product_data->id] = $product_data;
        break;
      case 'checkout_all_products':
        // get shopping cart products
        $order_product_ids = $this->get_user_order_product_ids();
        if (($order_product_ids === false) || (empty($order_product_ids) == true)) {
          WDFHelper::show_error(5);
        }
        // add products data
        for ($i = 0; $i < count($order_product_ids); $i++) {
          $order_product_id = $order_product_ids[$i];
          $product_data = $this->get_product_data_by_order_product_id($order_product_id);
          if ($product_data === false) {
            WDFChecoutHelper::show_error($checkout_data['ses_id'], __('Error getting product data', 'wde'));
          }
          $products_data[$product_data->order_product_id] = $product_data;
        }
        break;
      case 'quick_checkout':
        $product_data = $this->get_product_data_from_input();
        if ($product_data === false) {
          WDFHelper::show_error(6);
        }
        $products_data[$product_data->id] = $product_data;
        break;
    }

    // check products availability
    $this->check_products_availability($products_data);
    // remove invalid products and check for 0 products checkout
    if (is_array($products_data)) {
      foreach ($products_data as $index => $product_data) {
        if ($product_data->id == 0) {
          unset($products_data[$index]);
        }
      }
    }
    if (empty($products_data) == true) {
      if ($checkout_data['checkout_type'] == 'quick_checkout') {
        wp_redirect(get_permalink($options->option_all_products_page));
        exit;
      }
      else {
        wp_redirect(get_permalink($options->option_shopping_cart_page));
        exit;
      }
    }
    $checkout_data['products_data'] = $products_data;
  }

  private function get_user_order_product_ids() {
    $j_user = wp_get_current_user();
    global $wpdb;
    $query = 'SELECT id FROM ' . $wpdb->prefix . 'ecommercewd_orderproducts WHERE order_id=0';
    if (is_user_logged_in()) {
      $query .= ' AND j_user_id = ' . $j_user->ID;
    }
    else {
      $order_product_rand_ids = WDFInput::cookie_get_array('order_product_rand_ids', array());
      if (empty($order_product_rand_ids) == false) {
        $query .= ' AND j_user_id=0 AND rand_id IN (' . implode(',', $order_product_rand_ids) . ')';
      }
    }
    $order_product_ids = WDFArrayUtils::to_integer($wpdb->get_col($query));
    if ($wpdb->last_error) {
      return false;
    }
    return $order_product_ids;
  }

  private function get_product_data_by_order_product_id($order_product_id) {
    global $wpdb;
    $query = 'SELECT product_id AS id, id AS order_product_id, product_count AS count, product_parameters AS parameters, shipping_method_id FROM ' . $wpdb->prefix . 'ecommercewd_orderproducts WHERE id = ' . $order_product_id;
    $product_data = $wpdb->get_row($query);
    if (($wpdb->last_error) || (!$product_data->id)) {
      return false;
    }
    $query = new WP_Query(array(
      'post_type' => 'wde_products',
      'post__in' => array($product_data->id),
      'post_status' => array('publish')
    ));
    if (!$query->have_posts()) {
      return false;
    }
    $posts = $query->posts;
    $product_data = (object) array_merge((array) $product_data, (array) $posts[0]);
    $parameters = esc_attr(get_post_meta($product_data->id, 'wde_parameters', TRUE));
    // parameters
    $product_parameters = WDFJson::decode($product_data->parameters);
    $product_parameters = WDFArrayUtils::keys_to_integer($product_parameters, array());
    $product_selectable_parameters = $this->get_product_initial_selectable_parameters($product_data->id, $product_parameters);
    if ($product_selectable_parameters === false) {
      return false;
    }

    // validate
    $product_data->id = (int) $product_data->id;
    $product_data->order_product_id = (int) $product_data->order_product_id;
    $product_data->count = (int) $product_data->count;
    $product_data->parameters = $product_selectable_parameters;

    return $product_data;
  }

  private function get_product_data_from_input() {
    // get product data
    $product_id = WDFInput::get('product_id', 0, 'int');

    $product_count = max(1, WDFInput::get('product_count', 1, 'int'));
    $input_product_parameters = (array) WDFInput::get_parsed_json('product_parameters_json', array());
    $input_product_parameters = WDFArrayUtils::keys_to_integer($input_product_parameters, array());
    $product_selectable_parameters = $this->get_product_initial_selectable_parameters($product_id, $input_product_parameters);
    if ($product_selectable_parameters === false) {
      return false;
    }

    $query = new WP_Query(array(
      'post_type' => 'wde_products',
      'post__in' => array($product_id),
      'post_status' => array('publish')
    ));
    if (!$query->have_posts()) {
      return false;
    }
    
    $posts = $query->posts;
    $product_data = $posts[0];

    // additional data
    $product_data->id = (int) $product_data->id;
    $product_data->order_product_id = 0;
    $product_data->count = $product_count;
    $product_data->parameters = $product_selectable_parameters;
    $product_data->shipping_method_id = WDFInput::get('product_shipping_method_id_' . $product_data->order_product_id , 0, 'int');
    $product_data->id = $product_data->ID;

    return $product_data;
  }

  private function get_product_initial_selectable_parameters($product_id, $product_parameters) {
    $parameters = esc_attr(get_post_meta($product_id, 'wde_parameters', TRUE));
    $selectable_parameter_rows = WDFJson::decode($parameters);
    // override with order product parameters
    $product_override_parameters = array();
    if (empty($selectable_parameter_rows) == false) {
      foreach ($selectable_parameter_rows as $selectable_parameter_row) {
        if ((empty($product_parameters) == false) && (isset($product_parameters[intval($selectable_parameter_row->id)]))) {
          $product_override_parameters[intval($selectable_parameter_row->id)] = $product_parameters[intval($selectable_parameter_row->id)];
        }
        else {
          $product_override_parameters[intval($selectable_parameter_row->id)] = '';
        }
      }
    }
    return $product_override_parameters;
  }

  private function check_products_availability(&$products_data) {
    $unavailable_products_ids = array();
    if (is_array($products_data)) {
      foreach ($products_data as $id => $product_data) {
        $product_id = $product_data->id;
        $amount_in_stock = esc_attr(get_post_meta($product_id, 'wde_amount_in_stock', TRUE));
        $unlimited = esc_attr(get_post_meta($product_id, 'wde_unlimited', TRUE));

        if (($unlimited == 0) && ($amount_in_stock <= 0) ) {
          $unavailable_products_ids[] = $id;
          continue;
        }
      }
    }
    if (empty($unavailable_products_ids) == false) {
      foreach ($unavailable_products_ids as $id) {
        unset($products_data[$id]);
      }
    }
  }

  private function update_checkout_data(&$checkout_data) {
    $data = WDFInput::get('data');
    switch ($data) {
      case 'shipping_data':
        $form_fields = WDFDb::get_user_meta_fields_list();    
        foreach ($form_fields["billing_fields_list"] as $i => $form_field) {
          $checkout_data[$form_fields["billing_fields_list"][$i]['id']] = WDFInput::get($form_fields["billing_fields_list"][$i]['id'], $checkout_data[$form_fields["billing_fields_list"][$i]['id']]);
        }
        foreach ($form_fields["shipping_fields_list"] as $i => $form_field) {
          $checkout_data[$form_fields["shipping_fields_list"][$i]['id']] = WDFInput::get($form_fields["shipping_fields_list"][$i]['id'], $checkout_data[$form_fields["shipping_fields_list"][$i]['id']]);
        }
        break;
      case 'confirm_data':
        $j_user = wp_get_current_user();
        $checkout_data['payment_method'] = WDFInput::get('payment_method');
        $checkout_data['j_user_id'] = $j_user->ID;
        $checkout_data['user_ip_address'] = WDFUtils::get_client_ip_address();
        break;
    }
  }

  private function get_product_parameters_string($product_data) {
    $model_options = WDFHelper::get_model('options');
    $options = $model_options->get_options();
    $decimals = $options->option_show_decimals == 1 ? 2 : 0;

    $parameters = esc_attr(get_post_meta($product_data->id, 'wde_parameters', TRUE));
    $product_parameters = WDFJson::decode($parameters);

    $row_default_currency = WDFDb::get_row('currencies', '`default`= 1');
    $price_sum = 0;
    $str_parameters = array();
    $override_parameters = $product_data->parameters;
    if (is_array($product_parameters)) {
      foreach ($product_parameters as $key => $product_parameter) {
        $product_parameter = (array) $product_parameter;
        $parameter_id = (int) $product_parameter['id'];
        $term = get_term($parameter_id, 'wde_parameters');
        $product_parameter['name'] = isset($term->name) ? $term->name : '';
        $type_id = $product_parameter['type_id'];
        $insert = true;
        if (isset($override_parameters[$parameter_id]) == false) {
          continue;
        }
        $product_parameter['value'] = $override_parameters[$parameter_id];
        $product_parameter['text'] = array();

        if ($type_id != 1) {
          if (!is_array($product_parameter['value'])) {
            $product_parameter_array = array($product_parameter['value']);
          }
          else {
            if (sizeof($product_parameter['value']) != 0) {
              $product_parameter_array = $product_parameter['value'];
            }
            else {
              $product_parameter_array = array();
              $insert = false;
            }
          }
          foreach ($product_parameter_array as $value) {
            if ($value != '' && $value != '0' && sizeof($value) != 0) {
              //get the price
              if (is_array($product_parameter['values'])) {
                foreach ($product_parameter['values'] as $product_parameter_value) {
                  $product_parameter_value_arr = WDFJson::decode($product_parameter_value);
                  if ($product_parameter_value_arr->value == $value) {
                    $price = $product_parameter_value_arr->price;
                    break;
                  }
                }
              }
              $price_sign = substr($price, 0, 1);
              $price  = $price_sign . WDFText::wde_number_format(substr($price, 1), $decimals);
              $param_data['value'] = $value;
              $param_data['price'] = $price;
              array_push($product_parameter['text'], $param_data);
            }
            else {
              $insert = false;
            }
          }
        }
        else {
          if ($product_parameter['value'] != '') {
            $param_data['value'] = $product_parameter['value'];
            $param_data['price'] = '';
            array_push($product_parameter['text'], $param_data);
          }
          else {
            $insert = false;
          }
        }
        $str_param_values_array = array();
        if (is_array($product_parameter['text'])) {
          foreach ($product_parameter['text'] as $text) {
            $param_price = '';
            if ($text['price'] != '' && $text['price'] != '+') {
              $price_sign = substr($text['price'], 0, 1);
              $price = substr($text['price'], 1);
              if (WDFText::wde_number_format($price, $decimals) != WDFText::wde_number_format(0, $decimals)) {
                $price_with_currency_sign = $row_default_currency->sign_position == 0 ? $row_default_currency->sign . $price : $price . $row_default_currency->sign;
                $param_price = ' (' . $price_sign . $price_with_currency_sign . ')';
                $price = WDFText::float_val(substr($text['price'], 1, strlen($text['price'])), $decimals);
                if ($price_sign == '+') {
                  $price_sum = sprintf("%.2f", $price_sum) + sprintf("%.2f", $price);
                }
                else {
                  $price_sum = sprintf("%.2f", $price_sum) - sprintf("%.2f", $price);
                }
              }
            }
            $str_param_values_array[] = $text['value'] . $param_price;
          }
        }
        if ($insert) {
          $str_param_value = implode(', ', $str_param_values_array);
          $str_parameters[] = $product_parameter['name'] . ': ' . $str_param_value;
        }
      }
    }
    $params['str'] = addslashes(implode('%br%', $str_parameters));
    $params['price'] = sprintf("%.2f", $price_sum);
    return $params;
  }
}