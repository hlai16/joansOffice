<?php

defined('ABSPATH') || die('Access Denied');

class EcommercewdControllerShoppingcart extends EcommercewdController {
  ////////////////////////////////////////////////////////////////////////////////////////
  // Events                                                                             //
  ////////////////////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////////////////////
  // Constants                                                                          //
  ////////////////////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////////////////////
  // Variables                                                                          //
  ////////////////////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////////////////////
  // Constructor & Destructor                                                           //
  ////////////////////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////////////////////
  // Public Methods                                                                     //
  ////////////////////////////////////////////////////////////////////////////////////////
  public function add() {
    WDFInput::set('tmpl', 'component');

    $options = WDFDb::get_options();
  
    $j_user = wp_get_current_user();

    $model = WDFHelper::get_model('shoppingcart', true);

    $failed = false;
    $insert = true;
    $msg = '';
    $order_product_id = 0;

    // Get product data.
    $product_data = array();
    $product_data['id'] = WDFInput::get('product_id', 0, 'int');
    $product_data['count'] = max(1, WDFInput::get('product_count', 1, 'int'));
    $product_parameters = array();
    $input_product_parameters = WDFInput::get_parsed_json('product_parameters_json', array());
    if (is_array($input_product_parameters) || is_object($input_product_parameters)) {
      foreach ($input_product_parameters as $parameter_id => $parameter_value) {
        $product_parameters[intval($parameter_id)] = $parameter_value;
      }
    }
    $product_data['parameters'] = $product_parameters;

    $row_products = WDFProduct::get_product_rows($product_data['id'], FALSE, null, false, null, true); //WDFDb::get_row_by_id('products', $product_data['id']);
    $row_product = empty($row_products) == false ? $row_products[0] : null;
    if (!$row_product) {
      $failed = true;
      $msg = __('Failed to add to cart', 'wde');			
    }

    // add product name
    if ($failed == false) {
        $product_data['id'] = $row_product->id;
        $product_data['name'] = $row_product->name;
    }

    //check if user already has this product in cart
    if ($failed == false) {
      $row_product_id_count = new stdClass();
      $row_product_id_count->id = 0;
      $row_product_id_count->product_count = 0;
      $row_product_id_count->product_parameters = '';

      global $wpdb;
      $query = 'SELECT id, product_count, product_parameters';
      $query .= ' FROM ' . $wpdb->prefix . 'ecommercewd_orderproducts';
      $query .= ' WHERE order_id = 0';

      if (is_user_logged_in()) {
        $query .= ' AND j_user_id = ' . $j_user->ID;
      }
      else {
        $user_order_product_rand_ids = WDFInput::cookie_get_array('order_product_rand_ids');
        if (empty($user_order_product_rand_ids) == false) {
          $query .= ' AND j_user_id = 0';
          $query .= ' AND rand_id IN (' . implode(',', $user_order_product_rand_ids) . ')';
        }
        else {
          $query .= ' AND 0';
        }
      }
      $query .= ' AND product_id = ' . $product_data['id'];
      $rows = $wpdb->get_results($query);

      if ($wpdb->last_error) {
          $failed = true;
          $msg = __('Failed to add to cart', 'wde');
      }
      elseif (count($rows) != 0) {
        // if($options->checkout_redirect_to_cart_after_adding_an_item == 2){
          // $failed = true;
          // $msg = __('Product already added to cart', 'wde');
        // }
        // else {            
          foreach($rows as $row) {
            // $insert = false;
            $id = $row->id;
            $product_count = $row->product_count;
            $product_parameters_json = $row->product_parameters;
            $product_parameters_array = json_decode($product_parameters_json);

            $new_keys = array();
            if (is_array($product_data['parameters'])) {
              foreach ($product_data['parameters'] as $parameter_key => $product_parameter) {
                $new_keys[] = $parameter_key . '_' . $id;
              }
            }

            $product_data_parameters = (!empty($new_keys) && !empty($product_data['parameters'])) ? array_combine($new_keys, array_values($product_data['parameters'])) : array();

            foreach ($product_parameters_array as $param_key => $param_value) {
              if (isset($product_data_parameters[$param_key]) && $product_data_parameters[$param_key] != $param_value) {
                $insert = true;
                break;
              }
              else {
                $insert = false;
              }
            }

            if ($insert == false) {
              // $product_data['count'] += $product_count;
              // $order_product_id = $id;
              break;
            }
          }
          if ($insert == false) {
            $product_data['count'] += $product_count;
            $order_product_id = $id;
          }
        // }
      }
    }

    if ($failed == false) {
      //insert product data into cart table
      if ($insert == true) {
        $rand_id = WDFHelper::get_order_product_rand_id();
        if ($rand_id === false) {
          $failed = true;
          $msg = __('Failed to add to cart', 'wde');
        } else {
          $query_values = array();
          $query_values['rand_id'] = $rand_id;
          $query_values['j_user_id'] = is_user_logged_in() ? $j_user->ID : 0;
          $query_values['user_ip_address'] = WDFUtils::get_client_ip_address();
          $query_values['product_id'] = $product_data['id'];
          $query_values['product_name'] = $product_data['name'];
          $query_values['product_parameters'] = WDFJson::encode($product_data['parameters']);
          $query_values['product_count'] = $product_data['count'];
          $wpdb->insert($wpdb->prefix . 'ecommercewd_orderproducts', $query_values);

          $order_product_id = $wpdb->insert_id;
          $new_keys = array();
          if (is_array($product_data['parameters'])) {
            foreach ($product_data['parameters'] as $parameter_key => $product_parameter) {
              $new_keys[] = $parameter_key . '_' . $order_product_id;
            }
          }
          
          $product_data['parameters'] = (!empty($new_keys) && !empty($product_data['parameters'])) ? array_combine($new_keys, array_values($product_data['parameters'])) : array();
          
          $query_values = array();
          $query_values['product_parameters'] = WDFJson::encode($product_data['parameters']);
          $wpdb->update($wpdb->prefix . 'ecommercewd_orderproducts', $query_values, array('id' => $order_product_id));

          
          if ($wpdb->last_error) {
            $msg = __('Failed to update item', 'wde');
          }
          if ($wpdb->last_error) {
            $failed = true;
            $msg = __('Failed to add to cart', 'wde');
          } elseif (!is_user_logged_in()) {
            // if user is not logged in, add order product rand_id to cookies
            $user_order_product_rand_ids = WDFInput::cookie_get_array('order_product_rand_ids', array());
            $user_order_product_rand_ids[] = $rand_id;
            WDFInput::cookie_set_array('order_product_rand_ids', $user_order_product_rand_ids);
          }
        }
      } //update product data count
      else {
        $query = 'UPDATE ' . $wpdb->prefix . 'ecommercewd_orderproducts SET product_count = ' . $product_data['count'];
        $query .= ' WHERE order_id = 0';

        if (is_user_logged_in()) {
          $query .= ' AND j_user_id = ' . $j_user->ID;
        } else {
          $user_order_product_rand_ids = WDFInput::cookie_get_array('order_product_rand_ids', array());
          if (empty($user_order_product_rand_ids) == false) {
            $query .= ' AND j_user_id = 0';
            $query .= ' AND rand_id IN (' . implode(',', $user_order_product_rand_ids) . ')';
          } else {
            $query .= ' AND 0';
          }
        }
        $query .= ' AND product_id = ' . $product_data['id'];
        $query .= ' AND id = ' . $order_product_id;
        $wpdb->query($query);
        
        if ($wpdb->last_error) {
          $failed = true;
          $msg = __('Failed to update item', 'wde');
        }
      }
    }

    // get cart's products count
    $query = 'SELECT COUNT(*)';
    $query .= ' FROM ' . $wpdb->prefix . 'ecommercewd_orderproducts';
    $query .= ' WHERE order_id = 0';

    if (is_user_logged_in()) {
      $query .= ' AND j_user_id = ' . $j_user->ID;
    }
    else {
      $user_order_product_rand_ids = WDFInput::cookie_get_array('order_product_rand_ids', array());
      if (empty($user_order_product_rand_ids) == false) {
        $query .= ' AND j_user_id = 0';
        $query .= ' AND rand_id IN (' . implode(',', $user_order_product_rand_ids) . ')';
      } else {
        $query .= ' AND 0';
      }
    }
    $products_in_cart = $wpdb->get_var($query);
    if ($wpdb->last_error) {
      $products_in_cart = -1;
    }
    $product_added = $failed == true ? false : true;
    if ($product_added == true) {
      $msg = __('Successfully added to cart', 'wde') . '<br /><a href="' . get_permalink($options->option_shopping_cart_page) . '">' . __('View cart.', 'wde') . '</a>';
    }

    $data = array();
    $data['product_added'] = $product_added;
    $data['msg'] = $msg;
    $data['products_in_cart'] = $products_in_cart;
    echo WDFJson::encode($data);
    die();
  }

  public function ajax_remove_order_product() {
    global $wpdb;
    
    $j_user = wp_get_current_user();

    // $model_shopping_cart = WDFHelper::get_model('shoppingcart', true);

    $failed = false;
    $msg = '';

    $order_product_id = WDFInput::get('order_product_id', 0, 'int');
    
    //remove order product
    $query = 'DELETE FROM ' . $wpdb->prefix . 'ecommercewd_orderproducts';
    $query .= ' WHERE order_id = 0';
    if (is_user_logged_in()) {
      $query .= ' AND j_user_id = ' . $j_user->ID;
    } else {
      $order_product_rand_ids = WDFInput::cookie_get_array('order_product_rand_ids', array());
      if (empty($order_product_rand_ids) == false) {
        $query .= ' AND j_user_id = 0';
        $query .= ' AND rand_id IN (' . implode(',', $order_product_rand_ids) . ')';
      } else {
        $query .= ' AND 0';
      }
    }
    $query .= ' AND id = ' . $order_product_id;
    $wpdb->query($query);
    // WDFInput::set('task', 'displayshoppingcart');
    // wde_front_end(array('type' => 'shoppingcart', 'layout' => 'displayshoppingcart'), TRUE);
    if ($wpdb->last_error) {
      $failed = true;
      $msg = __('Failed to update item', 'wde');
    }

    // $order_products = $model_shopping_cart->get_order_product_rows();
    // $order_products_left = count($order_products);

    $total_text = WDFHelper::get_order_products_total_price_text();

    $data = array();
    $data['failed'] = $failed;
    $data['msg'] = $msg;
    $data['order_products_left'] = 0;//$order_products_left;
    $data['total_text'] = $total_text;

    echo WDFJson::encode($data);
    die();
  }

  public function ajax_remove_all_order_products() {
    global $wpdb;
    $j_user = wp_get_current_user();

    $model_shopping_cart = WDFHelper::get_model('shoppingcart', true);

    $failed = false;
    $msg = '';

    //remove order product
    $query = 'DELETE FROM ' . $wpdb->prefix . 'ecommercewd_orderproducts';
    $query .= ' WHERE order_id = 0';
    if (is_user_logged_in()) {
      $query .= ' AND j_user_id = ' . $j_user->ID;
    } else {
      $order_product_rand_ids = WDFInput::cookie_get_array('order_product_rand_ids', array());
      if (empty($order_product_rand_ids) == false) {
        $query .= ' AND j_user_id = 0';
        $query .= ' AND rand_id IN (' . implode(',', $order_product_rand_ids) . ')';
      } else {
        $query .= ' AND 0';
      }
    }
    $wpdb->query($query);

    if ($wpdb->last_error) {
      $failed = true;
      $msg = __('Failed to remove items.', 'wde');
    }

    $total_text = WDFHelper::get_order_products_total_price_text();

    $data = array();
    $data['failed'] = $failed;
    $data['msg'] = $msg;
    $data['total_text'] = $total_text;

    echo WDFJson::encode($data);
    die();
  }

  public function displayshoppingcart($params) {
    WDFHelper::add_guest_user_products();
    WDFHelper::check_cart();
    WDFHelper::remove_unavailable_products();
    parent::display($params);
  }
}