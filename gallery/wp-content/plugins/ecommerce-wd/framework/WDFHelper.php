<?php

class WDFHelper {
  ////////////////////////////////////////////////////////////////////////////////////////
  // Events                                                                             //
  ////////////////////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////////////////////
  // Constants                                                                          //
  ////////////////////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////////////////////
  // Variables                                                                          //
  ////////////////////////////////////////////////////////////////////////////////////////
  /**
   * Component name.
   *
   * @var    string
   */
  private static $com_name;

  private static $controller;

  /**
   * Map of generated models.
   *
   * @var    Array
   */
  private static $models_map;

  ////////////////////////////////////////////////////////////////////////////////////////
  // Constructor & Destructor                                                           //
  ////////////////////////////////////////////////////////////////////////////////////////
  /**
   * Init variables and require all files in framework
   */
  public static function init($com_name = '') {
    self::$com_name = $com_name != '' ? $com_name : (isset($_GET['page']) ? esc_html($_GET['page']) : '');
    self::require_dir(WD_E_DIR . DS . 'framework');
    self::require_dir(WD_E_DIR . DS . 'tables');
    if (!is_admin()) {
      if (WDFInput::get_controller() == null) {
        WDFInput::set('controller', WDFInput::get('view'));
      }
      if (WDFInput::get_task() == null) {
        WDFInput::set('task', WDFInput::get('layout'));
      }
    }
    self::com_require('controllers' . DS . 'controller.php');
    self::com_require('models' . DS . 'model.php');
    self::com_require('views' . DS . 'view.php');
    /*
      * Require Files From Addons.
      * Since version 2.1.3
    */
    do_action('wde_require');
  }

  ////////////////////////////////////////////////////////////////////////////////////////
  // Public Methods                                                                     //
  ////////////////////////////////////////////////////////////////////////////////////////
  /**
   * Get component name
   *
   * @return    string    name of the component
   */
  public static function get_com_name() {
    return WD_E_NAME;
  }

  /**
   * Require class starting from component directory
   *
   * @param    string $class_path path of the class to require
   */
  public static function com_require($class_path) {
    require_once WDFPath::get_com_path('admin') . DS . $class_path;
  }

  public static function get_controller() {
    if (self::$controller == NULL) {
      $input_controller = WDFInput::get_controller();
      if ($input_controller) {
        $controller_path = WDFPath::get_com_path('admin') . '/controllers/' . $input_controller . '.php';
        if (file_exists($controller_path)) {
          require_once $controller_path;
        }
      }
      $controller_class = ucfirst(self::get_com_name()) . 'Controller' . ucfirst($input_controller);
      self::$controller = new $controller_class();
    }
    return self::$controller;
  }

  public static function get_model($type = '', $is_frontend_ajax = false) {
    if (self::$models_map == NULL) {
      self::$models_map = array();
    }
    if ($type == '') {
      $input_controller = WDFInput::get_controller();
      $type = $input_controller;
    }
    if (isset(self::$models_map[$type]) == FALSE) {
      require_once WDFPath::get_com_path('admin', $is_frontend_ajax) . DS . 'models' . DS . $type . '.php';
      $model_class = ucfirst(self::get_com_name()) . 'Model' . ucfirst($type);
      self::$models_map[$type] = new $model_class;
    }
    return self::$models_map[$type];
  }

  public static function redirect($controller = '', $task = '', $cid = '', $params = '', $msg = '', $msgType = 'message', $ajax = FALSE) {
    if ($controller == '') {
      $controller = WDFInput::get_controller();
    }
    $url_parts = array();
    if ($controller != '') {
      $url_parts[] = 'page=wde_' . $controller;
    }
    if ($task != '') {
      $url_parts[] = 'task=' . $task;
    }
    if ($cid != '') {
      $url_parts[] = 'cid[]=' . $cid;
    }
    if ($params != '') {
      $url_parts[] = $params;
    }
    if ($msg != '') {
      $url_parts[] = 'msg=' . $msg;
    }
    /*if ($msgType != '') {
      $url_parts[] = 'msgType=' . $msgType;
    }*/

    $action = $ajax ? 'admin-ajax.php?action=wde_ajax&' : 'admin.php?';

    if (is_admin()) {
      /* For cid[]*/
      header("Location: " . $action . implode('&', $url_parts));
      exit;
    }
  }

  public static function get_image_original_url($image) {
    return str_replace("/thumb", "", $image);
  }
  
  public static function show_error($code) {
    $options = WDFHelper::get_model('options', true)->get_options();
    wp_redirect(WDFPath::add_pretty_query_args(get_permalink($options->option_systempages_page), $options->option_endpoint_systempages_errnum, $code, TRUE));
    exit;
  }

  ////////////////////////////////////////////////////////////////////////////////////////
  // Getters & Setters                                                                  //
  ////////////////////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////////////////////
  // Private Methods                                                                    //
  ////////////////////////////////////////////////////////////////////////////////////////
  /**
   * Require framework class
   *
   * @param    string $path name of the class to require
   */
  private static function framework_require($path) {
    require_once WD_E_DIR . DS . 'framework' . DS . $path;
  }

  /**
   * Require php files in specified folder
   *
   * @param    string $dir_path name of the dir of files
   * @param    boolean $include_subdirs include subdirectories
   */
  private static function require_dir($dir_path, $include_subdirs = TRUE) {
    $files = scandir($dir_path);
    foreach ($files as $file) {
      if (($file == '.') || ($file == '..')) {
        continue;
      }
      $file = $dir_path . DS . $file;

      if (is_dir($file) == TRUE) {
        self::require_dir($file);
      }
      else {
        if ((is_file($file) == TRUE)
          && (pathinfo($file, PATHINFO_EXTENSION) == 'php')) {
          require_once $file;
        }
      }
    }
  }

  /**
	 * Get tax for given product.
	 *
	 * @since 1.1.0
	 *
	 * @param int     $product_id    Product id.
	 * @return Tax rate(s) and tax class taxanomy id, name for given product.
	 */
  public static function get_product_tax($product_id) {
    $tax_rates = array();
    $tax_id = 0;
    $tax_name = '';
    $product_id = (int) $product_id;
    if ($product_id) {
      $taxes = wp_get_object_terms($product_id, 'wde_taxes');
      if (isset($taxes[0])) {
        $tax = $taxes[0];
        if (isset($tax->term_id) && $tax->term_id) {
          $tax_id = $tax->term_id;
          $tax_name = isset($tax->name) ? $tax->name : '';
          $tax_rates = WDFDb::get_tax_rates_by('id', $tax_id, FALSE);
          if (!$tax_rates) {
            $tax = get_option("wde_taxes_" . $tax_id);
            if (isset($tax['rate'])) {
              $tax_rate = new stdClass;
              $tax_rate->id = 0;
              $tax_rate->country = 0;
              $tax_rate->state = '';
              $tax_rate->zipcode = '';
              $tax_rate->city = '';
              $tax_rate->rate = $tax['rate'];
              $tax_rate->tax_name = '';
              $tax_rate->priority = 1;
              $tax_rate->compound = 0;
              $tax_rate->shipping_rate = 0;
              $tax_rate->ordering = 1;
              $tax_rate->tax_id = $tax_id;
              $tax_rates = array($tax_rate);
            }
          }
        }
      }
    }
    return array('id' => $tax_id, 'name' => $tax_name, 'tax_rates' => $tax_rates);
  }

  /**
	 * Get current customer checkout data.
	 *
	 * @since 1.1.0
	 *
	 * @return Current customer checkout data as array.
	 */
  public static function get_checkout_data() {
    $checkout_data = FALSE;
    $ses_id = WDFInput::get('ses_id', 0, 'float');
    if ($ses_id) {
      $checkout_data = isset($_SESSION['checkout_data_' . $ses_id]) ? unserialize($_SESSION['checkout_data_' . $ses_id]) : FALSE;
    }
    return $checkout_data;
  }

  /**
	 * Get current customer checkout billing/shipping data if it is or current user billing/shipping data otherwise.
	 *
	 * @since 1.1.0
	 *
	 * @return Current customer data as array.
	 */
  public static function get_user_data() {
    $billing_data = array(
      'first_name' => '',
      'middle_name' => '',
      'last_name' => '',
      'user_email' => '',
      'email' => '',
      'company' => '',
      'country_id' => '',
      'state' => '',
      'city' => '',
      'address' => '',
      'mobile' => '',
      'phone' => '',
      'fax' => '',
      'zip_code' => '',
    );
    $shipping_data = array(
      'first_name' => '',
      'middle_name' => '',
      'last_name' => '',
      'company' => '',
      'country_id' => '',
      'state' => '',
      'city' => '',
      'address' => '',
      'zip_code' => '',
    );

    $checkout_data = WDFHelper::get_checkout_data();
    if ($checkout_data) {
      foreach ($checkout_data as $key => $checkout_data) {
        if (strpos($key, 'wde_billing_') === 0) {
          $billing_data[str_replace('wde_billing_', '', $key)] = $checkout_data;
        }
        elseif (strpos($key, 'wde_shipping_') === 0) {
          $shipping_data[str_replace('wde_shipping_', '', $key)] = $checkout_data;
        }
      }
    }
    else {
      $user = wp_get_current_user();
      $id = $user->ID;
      if ($id) {
        $email = $user->user_email;
        $user_metas = get_user_meta($id);
        foreach ($user_metas as $key => $user_meta) {
          if (strpos($key, 'wde_billing_') === 0) {
            $billing_data[str_replace('wde_billing_', '', $key)] = isset($user_meta[0]) ? $user_meta[0] : '';
          }
          elseif (strpos($key, 'wde_shipping_') === 0) {
            $shipping_data[str_replace('wde_shipping_', '', $key)] = isset($user_meta[0]) ? $user_meta[0] : '';
          }
        }
        $billing_data['user_email'] = $email;
      }
    }
    $user_data = array(
      "billing_data" => $billing_data,
      "shipping_data" => $shipping_data,
    );
    return $user_data;
  }

  public static function wde_calc_tax($options, $p, $t) {
    $round_tax_at_subtotal = isset($options->round_tax_at_subtotal) ? $options->round_tax_at_subtotal : 0;
    $price_entered_with_tax = isset($options->price_entered_with_tax) ? $options->price_entered_with_tax : 0;
    if ($price_entered_with_tax) {
      $calc_tax = (100 * $p / (100 + $t)) * $t / 100;
    }
    else {
      $calc_tax = $p * $t / 100;
    }
    if (!$round_tax_at_subtotal) {
      $decimals = $options->option_show_decimals == 1 ? 2 : 0;
      $calc_tax = WDFText::wde_number_format($calc_tax, $decimals);
    }
    return $calc_tax;
  }

  /**
   * @param $value
   * @return bool
   */
  public static function check_empty($value) {
    return $value !== '';
  }

  /**
	 * Calculate the product tax.
	 *
	 * @since 1.1.0
	 *
	 * @return The the product tax information for the given price.
	 */
  public static function calculate_tax_rates($price, $product_id) {
    $model_options = WDFHelper::get_model('options');
		$options = $model_options->get_options();
    $enable_tax = isset($options->enable_tax) ? $options->enable_tax : 0;
    if (!$enable_tax) {
      return FALSE;
    }
    $round_tax_at_subtotal = isset($options->round_tax_at_subtotal) ? $options->round_tax_at_subtotal : 0;
    $price_entered_with_tax = isset($options->price_entered_with_tax) ? $options->price_entered_with_tax : 0;
    $tax_based_on = isset($options->tax_based_on) ? $options->tax_based_on : 'shipping_address';
    $base_location = isset($options->base_location) ? $options->base_location : '';
		$decimals = $options->option_show_decimals == 1 ? 2 : 0;
		$row_default_currency = WDFDb::get_row('currencies', '`default`=1');

    $product_tax = WDFHelper::get_product_tax($product_id);
    $tax_rates = $product_tax['tax_rates'];
    if (!$tax_rates) {
      return FALSE;
    }
    $tax_name = $product_tax['name'];

    $user_data = WDFHelper::get_user_data();
    $shipping_data = $user_data['shipping_data'];
    $billing_data = $user_data['billing_data'];

		$tax_total = 0;
		$shipping_tax = 0;
    $tax_price = $price;
		$tax_info = array();

    $priority = array();
    foreach ($tax_rates as $key => $tax_rate) {
			if (!$tax_rate->compound) {
        $condition = $tax_rate->country == 0
          || ((($tax_based_on == 'base_address')
              && ($tax_rate->country == $base_location)
            )
            || (($tax_based_on == 'shipping_address')
              && ($tax_rate->country == $shipping_data['country_id'])
              && ($tax_rate->state == '' || in_array($shipping_data['state'], array_filter(array_map('trim', explode(';', $tax_rate->state)), 'self::check_empty')))
              && ($tax_rate->zipcode == '' || in_array($shipping_data['zip_code'], array_filter(array_map('trim', explode(';', $tax_rate->zipcode)), 'self::check_empty')))
              && ($tax_rate->city == '' || in_array($shipping_data['city'], array_filter(array_map('trim', explode(';', $tax_rate->city)), 'self::check_empty')))
            )
            || (($tax_based_on == 'billing_address')
              && ($tax_rate->country == $billing_data['country_id'])
              && ($tax_rate->state == '' || in_array($shipping_data['state'], array_filter(array_map('trim', explode(';', $tax_rate->state)), 'self::check_empty')))
              && ($tax_rate->zipcode == '' || in_array($shipping_data['zip_code'], array_filter(array_map('trim', explode(';', $tax_rate->zipcode)), 'self::check_empty')))
              && ($tax_rate->city == '' || in_array($shipping_data['city'], array_filter(array_map('trim', explode(';', $tax_rate->city)), 'self::check_empty')))
            )
          );
        if ($condition) {
          if (in_array($tax_rate->priority, $priority)) {
            continue;
          }
          $priority[] = $tax_rate->priority;
          $tax = self::wde_calc_tax($options, $price, $tax_rate->rate);
          $tax_total += $tax;
          /* if ($price_entered_with_tax) {
            $tax_price -= $tax;
          }
          else {
            $tax_price += $tax;
          } */
          $shipping_tax += $tax_rate->shipping_rate;
          if (WDFText::wde_number_format($tax, $decimals) != WDFText::wde_number_format(0, $decimals)) {
            $tax = WDFText::wde_number_format($tax, $decimals);
            $tax_text = $row_default_currency->sign_position == 0 ? $row_default_currency->sign . $tax : $tax . $row_default_currency->sign;
            $tax_info[$tax_rate->id] = array('name' => $tax_rate->tax_name, 'tax' => $tax, 'tax_text' => $tax_text, 'shipping_rate' => $tax_rate->shipping_rate);
          }
        }
      }
		}

    $priority = array();
    foreach ($tax_rates as $key => $tax_rate) {
			if ($tax_rate->compound) {
        $condition = $tax_rate->country == 0
          || ((($tax_based_on == 'base_address')
              && ($tax_rate->country == $base_location)
            )
            || (($tax_based_on == 'shipping_address')
              && ($tax_rate->country == $shipping_data['country_id'])
              && ($tax_rate->state == '' || in_array($shipping_data['state'], array_filter(array_map('trim', explode(';', $tax_rate->state)), 'self::check_empty')))
              && ($tax_rate->zipcode == '' || in_array($shipping_data['zip_code'], array_filter(array_map('trim', explode(';', $tax_rate->zipcode)), 'self::check_empty')))
              && ($tax_rate->city == '' || in_array($shipping_data['city'], array_filter(array_map('trim', explode(';', $tax_rate->city)), 'self::check_empty')))
            )
            || (($tax_based_on == 'billing_address')
              && ($tax_rate->country == $billing_data['country_id'])
              && ($tax_rate->state == '' || in_array($shipping_data['state'], array_filter(array_map('trim', explode(';', $tax_rate->state)), 'self::check_empty')))
              && ($tax_rate->zipcode == '' || in_array($shipping_data['zip_code'], array_filter(array_map('trim', explode(';', $tax_rate->zipcode)), 'self::check_empty')))
              && ($tax_rate->city == '' || in_array($shipping_data['city'], array_filter(array_map('trim', explode(';', $tax_rate->city)), 'self::check_empty')))
            )
          );
        if ($condition) {
          if (in_array($tax_rate->priority, $priority)) {
            continue;
          }
          $priority[] = $tax_rate->priority;
          
          $tax = self::wde_calc_tax($options, $tax_price, $tax_rate->rate);
          
          $tax_total += $tax;
          $shipping_tax += $tax_rate->shipping_rate;
          if (WDFText::wde_number_format($tax, $decimals) != WDFText::wde_number_format(0, $decimals)) {
            $tax = WDFText::wde_number_format($tax, $decimals);
            $tax_text = $row_default_currency->sign_position == 0 ? $row_default_currency->sign . $tax : $tax . $row_default_currency->sign;
            $tax_info[$tax_rate->id] = array('name' => $tax_rate->tax_name, 'tax' => $tax, 'tax_text' => $tax_text, 'shipping_rate' => $tax_rate->shipping_rate);
          }
        }
      }
		}

    if (empty($tax_info)) {
      return FALSE;
    }
    if ($price_entered_with_tax) {
      $tax_price -= $tax_total;
    }
    else {
      $tax_price += $tax_total;
    }
    $tax_total = WDFText::wde_number_format($tax_total, $decimals);
    $tax_total_text = WDFHelper::price_text($tax_total, $decimals, $row_default_currency);

    $tax_price = WDFText::wde_number_format($price_entered_with_tax ? $price : $tax_price, $decimals);
    $tax_price_text = WDFHelper::price_text($tax_price, $decimals, $row_default_currency);

    $price_without_tax = WDFText::wde_number_format($price_entered_with_tax ? $price - $tax_total : $price, $decimals);
    $price_without_tax_text = WDFHelper::price_text($price_without_tax, $decimals, $row_default_currency);

    // $tax_total = $price_entered_with_tax ? 0 : $tax_total;

    return array(
      'price' => $price_without_tax,
      'price_text' => $price_without_tax_text,
      'tax_price' => $tax_price,
      'tax_price_text' => $tax_price_text,
      'tax_name' => $tax_name,
      'tax_total' => $tax_total,
      'tax_total_text' => $tax_total_text,
      'shipping_tax' => $shipping_tax,
      'tax_info' => $tax_info);
  }

  /**
	 * Return the price with currency sign.
	 *
	 * @since 1.1.0
	 *
	 * @return The price with currency sign.
	 */
  public static function price_text($price, $decimals = '', $currency = '') {
    $price_text = '';

    if ($decimals === '') {
      $options_model = WDFHelper::get_model('options');
      $options = $options_model->get_options();
      $decimals = ($options->option_show_decimals == 1) ? 2 : 0;
    }

    if (WDFText::wde_number_format($price, $decimals) != WDFText::wde_number_format(0, $decimals)) {
      if ($currency === '') {
        $currency = WDFDb::get_row('currencies', '`default`=1');
      }
      $currency_sign = $currency->sign;
      $currency_position = $currency->sign_position;
      $price_text = WDFText::wde_number_format($price, $decimals);
      $price_text = $currency_position == 0 ? $currency_sign . $price_text : $price_text . $currency_sign;
    }

    return $price_text;
  }

  /**
	 * Get order data by id.
	 *
	 * @since 1.1.0
	 *
	 * @return The order and order products data.
	 */
  public static function get_order($order_id = 0) {
    if (!$order_id) {
      return FALSE;
    }
    $order = WDFDb::get_row_by_id('orders', $order_id);
    if (!$order) {
      return FALSE;
    }
    global $wpdb;
    $order->product_rows = $wpdb->get_results($wpdb->prepare('SELECT * FROM `' . $wpdb->prefix . 'ecommercewd_orderproducts` WHERE order_id=%d', $order_id));
    if ($wpdb->last_error || !$order->product_rows) {
      return FALSE;
    }

    $options = WDFDb::get_options();
    $decimals = $options->option_show_decimals == 1 ? 2 : 0;
    $currency_id = $order->currency_id;
    $currency = WDFDb::get_row('currencies', '`id`=' . $currency_id);
    if ($currency) {
      $currency = WDFDb::get_row('currencies', '`default`=1');
    }
    $total_shipping_type = $order->shipping_type;
    $shipping_total_price = $order->shipping_method_price;
    
    $total_price = 0;
    if ($total_shipping_type == 'per_order') {
      $total_price += $shipping_total_price;
    }
    else {
      $order->shipping_method_price = 0;
    }
    $order->discount = false;
    if ($order->product_rows) {
      $order->product_names = array();
      $order->product_images = array();
      $order->price = 0;
      $order->tax_price = 0;
      $order->shipping_price = 0;
      foreach ($order->product_rows as $product) {
        $order->product_names[] = $product->product_name;
        $order->product_images[] = $product->product_image;
        if (!isset($product->discount) || $product->discount === NULL) {
          // For old orders which discounts and parameters price are included in price.
          $product->discount = 0;
          $product->product_parameters_price = 0;
        }
        $subtotal_price = ($product->product_price + $product->product_parameters_price + $product->tax_price - $product->discount) * $product->product_count;
        if ($total_shipping_type != 'per_order') {
          if (!isset($product->shipping_method_type) || $product->shipping_method_type === NULL) {
            // For old orders which shippings methods types were not saved in db.
            $shipping_method = get_option("wde_shippingmethods_" . $product->shipping_method_id);
            $product->shipping_method_type = isset($shipping_method['shipping_type']) ? $shipping_method['shipping_type'] : 'per_bundle';
          }
          $shipping = ($product->shipping_method_type == 'per_unit' ? $product->product_count : 1) * $product->shipping_method_price;
          $subtotal_price += $shipping;
        }
        else {
          $product->shipping_method_price = 0;
        }
        $total_price += $subtotal_price;

        $product->price_text = WDFHelper::price_text($product->product_price, $decimals, $currency);
        $product->tax_price_text = WDFHelper::price_text($product->tax_price, $decimals, $currency);
        ob_start();
        if ($product->tax_info != '' && $options->tax_total_display == 'itemized') {
          $tax_info_decoded = json_decode($product->tax_info);
          if (is_array($tax_info_decoded)) {
            foreach ($tax_info_decoded as $tax_info) {
              ?>
            <span>
              <?php echo ((isset($tax_info->name) && $tax_info->name != '') ? $tax_info->name : __('Tax', 'wde')); ?>: 
            </span>
            <span><?php echo (isset($tax_info->tax_text) ? $tax_info->tax_text : ''); ?></span><br />
              <?php
            }
          }
        }
        elseif ($product->tax_price_text) {
          ?>
          <span><?php _e('Tax', 'wde'); ?>: </span>
          <span><?php echo $product->tax_price_text; ?></span>
          <?php
        }
        $product->tax_price_text = ob_get_clean();

        $product->shipping_method_price_text = WDFHelper::price_text($product->shipping_method_price, $decimals, $currency);
        $product->discount_rate = $product->discount_rate ? $product->discount_rate . '%' : '';
        if ($product->discount_rate) {
          $order->discount = true;
        }
        $product->subtotal_text = WDFHelper::price_text($subtotal_price, $decimals, $currency);

        $order->price += ($product->product_price + $product->product_parameters_price - $product->discount) * $product->product_count;
        $order->tax_price += $product->tax_price;
        $order->shipping_price += $product->shipping_method_price;
      }
      $order->product_names = implode(', ', $order->product_names);
    }
    $order->total_price = $total_price;

    $order->total_price_text = WDFHelper::price_text($order->total_price, $decimals, $currency);
    $order->total_shipping_price_text = WDFHelper::price_text($order->shipping_method_price, $decimals, $currency);
    if ($order->shipping_method_price) {
      $order->shipping_price = $order->shipping_method_price;
    }
    $order->price_text = WDFHelper::price_text($order->price, $decimals, $currency);
    $order->tax_price_text = WDFHelper::price_text($order->tax_price, $decimals, $currency);
    $order->shipping_price_text = WDFHelper::price_text($order->shipping_price, $decimals, $currency);
    return $order;
  }

  public static function get_order_for_shopping_cart() {
    global $wpdb;
    $order_product_id = WDFInput::get('order_product_id', 0, 'int');
    $j_user = wp_get_current_user();
    $model_options = WDFHelper::get_model('options', true);
    $options = $model_options->get_options();
    $option_include_tax_in_checkout_price = isset($options->option_include_tax_in_checkout_price) ? $options->option_include_tax_in_checkout_price : 0;
    $decimals = $options->option_show_decimals == 1 ? 2 : 0;
    $row_default_currency = WDFDb::get_row('currencies', '`default` = 1');
    $address = WDFHelper::get_address($options);
    $country_id = isset($_POST['country_id']) ? esc_html($_POST['country_id']) : $address['country_id'];
    $zipcode = isset($_POST['zipcode']) ? esc_html($_POST['zipcode']) : $address['zipcode'];
    if ($order_product_id) {
      $query_values = array();
      // On change product parameters or product count.
      $order_product_count = WDFInput::get('order_product_count', 1, 'int');
      $order_product_parameters = WDFJson::encode(WDFInput::get_parsed_json('order_product_parameters_json', array()));
      $order_product_parameters_price = WDFInput::get('order_product_parameters_price', 0, 'string');
      $product_shipping_method_id = WDFInput::get('product_shipping_method_id_' . $order_product_id, 0, 'int');
      if ($product_shipping_method_id) {
        $query_values['shipping_method_id'] = $product_shipping_method_id;
        $product = new stdClass;
        $product->id = WDFInput::get('product_id', 0, 'int');
        $product->decimals_to_show = $decimals;
        $product->row_default_currency = $row_default_currency;
        $shipping_method_row = WDFProduct::get_product_shippings($product, array('wde_shipping_country_id' => $country_id, 'wde_shipping_zip_code' => $zipcode));
        $shipping_classes = $shipping_method_row->shipping_classes;
        if (isset($shipping_classes[$product_shipping_method_id])) {
          $shipping_method = $shipping_classes[$product_shipping_method_id];
          $query_values['shipping_method_name'] = isset($shipping_method->name) ? $shipping_method->name : '';
          $query_values['shipping_method_price'] = isset($shipping_method->price) ? $shipping_method->price : 0;
        }
      }
      if ($order_product_count < 1) {
        $order_product_count = 1;
      }
      $query_values['product_count'] = $order_product_count;
      $query_values['product_parameters'] = $order_product_parameters;
      $query_values['product_parameters_price'] = $order_product_parameters_price;

      $wpdb->update($wpdb->prefix . 'ecommercewd_orderproducts', $query_values, array('id' => $order_product_id));
      if ($wpdb->last_error) {
        echo $wpdb->last_error;
        return false;
      }
    }

    $query = 'SELECT T_ORDER_PRODUCTS.id';
    $query .= ', T_ORDER_PRODUCTS.product_id';
    $query .= ', T_ORDER_PRODUCTS.product_parameters';
    $query .= ', T_ORDER_PRODUCTS.product_parameters_price';
    $query .= ', T_ORDER_PRODUCTS.product_count';
    $query .= ', T_ORDER_PRODUCTS.product_name';
    $query .= ', T_ORDER_PRODUCTS.product_parameters AS product_parameters_json';
    $query .= ' FROM ' . $wpdb->prefix . 'ecommercewd_orderproducts AS T_ORDER_PRODUCTS';
    $query .= ' WHERE T_ORDER_PRODUCTS.order_id = 0';
    if (is_user_logged_in()) {
      $query .= ' AND T_ORDER_PRODUCTS.j_user_id = ' . $j_user->ID;
    }
    else {
      $order_product_rand_ids = WDFInput::cookie_get_array('order_product_rand_ids');
      if (empty($order_product_rand_ids) == false) {
        $query .= ' AND T_ORDER_PRODUCTS.j_user_id = 0';
        $query .= ' AND T_ORDER_PRODUCTS.rand_id IN (' . implode(',', $order_product_rand_ids) . ')';
      }
      else {
        $query .= ' AND 0';
      }
    }
    $query .= ' ORDER BY T_ORDER_PRODUCTS.id ASC';
    $order_product_rows = $wpdb->get_results($query);

    if ($wpdb->last_error) {
      echo $wpdb->last_error;
      return false;
    }

    $total_shipping_price = 0;
    $per_order_shipping_methods = array();
    $total_price = 0;
    if (is_array($order_product_rows)) {
      foreach ($order_product_rows as $order_product_row) {
        $order_product_row->product_price = esc_attr(get_post_meta($order_product_row->product_id, 'wde_price', TRUE));
        $order_product_row->product_amount_in_stock = esc_attr(get_post_meta($order_product_row->product_id, 'wde_amount_in_stock', TRUE));
        $order_product_row->product_unlimited = esc_attr(get_post_meta($order_product_row->product_id, 'wde_unlimited', TRUE));
        $url = '';
        if (!has_post_thumbnail($order_product_row->product_id)) {
          $image_ids_string = get_post_meta($order_product_row->product_id, 'wde_images', TRUE);
          $image_ids = explode(',', $image_ids_string);
          if (isset($image_ids[0]) && is_numeric($image_ids[0]) && $image_ids[0] != 0) {
            $image_id = (int) $image_ids[0];
            $url = wp_get_attachment_url($image_id);
          }
        }
        else {
          $url = wp_get_attachment_url(get_post_thumbnail_id($order_product_row->product_id));
        }
        $order_product_row->product_image = $url;
        $order_product_row->product_url = get_permalink($order_product_row->product_id);

        $current_price = $order_product_row->product_price;
        $current_price += $order_product_row->product_parameters_price;
        $order_product_row->current_price = $current_price;
        $order_product_row->current_price_text = WDFHelper::price_text($current_price, $decimals, $row_default_currency);

        $order_product_row->product_discount_rate = '';
        $order_product_row->discount = 0;
        $order_product_row->discount_text = '';
        $discounts = wp_get_object_terms($order_product_row->product_id, 'wde_discounts');
        if ($discounts) {
          $discount = $discounts[0];
          if (isset($discount->term_id)) {
            $discount = get_option('wde_discounts_' . $discount->term_id);
            if (isset($discount['rate']) && $discount['rate']) {
              $discount_rate = $discount['rate'];
              $order_product_row->product_discount_rate = $discount_rate . '%';
              $order_product_row->discount = WDFText::float_val(($current_price * $discount_rate / 100), $decimals);
              $order_product_row->discount_text = WDFHelper::price_text($order_product_row->discount, $decimals, $row_default_currency);
            }
          }
        }
        $current_price -= $order_product_row->discount;

        $calculated_tax_rates = WDFHelper::calculate_tax_rates($current_price, $order_product_row->product_id);
        $order_product_row->price = $current_price;
        $order_product_row->tax_info = '';
        $order_product_row->product_tax_rate = 0;
        $order_product_row->product_tax_rate_text = '';
        $order_product_row->shipping_tax = FALSE;
        if ($calculated_tax_rates) {
          $order_product_row->tax_info = $calculated_tax_rates['tax_info'];
          $order_product_row->product_tax_rate = $calculated_tax_rates['tax_total'];
          $order_product_row->product_tax_rate_text = $calculated_tax_rates['tax_total_text'];
          $order_product_row->shipping_tax = $calculated_tax_rates['shipping_tax'];
          $order_product_row->price = $calculated_tax_rates['price'];
          $current_price = $option_include_tax_in_checkout_price ? $calculated_tax_rates['tax_price'] : $calculated_tax_rates['price'];
        }

        // amount in stock
        if ($order_product_row->product_unlimited == 1) {
          $order_product_row->product_available = true;
          $order_product_row->product_availability_msg = __('In stock', 'wde');
          $order_product_row->stock_class = 'wd_in_stock';
        }
        elseif ($order_product_row->product_amount_in_stock > 0) {
          $order_product_row->product_available = true;
          $order_product_row->product_availability_msg = __('In stock', 'wde') . ': ' . $order_product_row->product_amount_in_stock;
          $order_product_row->stock_class = 'wd_in_stock';
        }
        else {
          $order_product_row->product_available = false;
          $order_product_row->product_availability_msg = __('Out of stock', 'wde');
          $order_product_row->stock_class = 'wd_out_of_stock';
          $order_product_row->product_count = 0;
        }

        $order_product_row->subtotal = $order_product_row->product_count * ($order_product_row->price + $order_product_row->product_tax_rate);

        $order_product_row->product_final_price = WDFText::float_val($current_price, $decimals);
        $order_product_row->product_final_price_text = WDFHelper::price_text($order_product_row->product_final_price, $decimals, $row_default_currency);
        $order_product_row->price_text = WDFHelper::price_text($order_product_row->current_price, $decimals, $row_default_currency);

        ob_start();
        if ($order_product_row->price_text) {
          ?>
          <span><?php echo __('Price', 'wde') . ':&nbsp;' . $order_product_row->price_text; ?></span>
          <br />
          <?php
        }
        if ($order_product_row->product_discount_rate) {
          ?>
          <span><?php echo __('Discount', 'wde') . ':&nbsp;' . $order_product_row->product_discount_rate; ?></span>
          <br />
          <?php
        }
        if ($option_include_tax_in_checkout_price && $order_product_row->product_tax_rate_text) {
          if ($options->tax_total_display == 'itemized') {
            foreach ($order_product_row->tax_info as $tax_info) {
              ?>
            <span>
              <?php echo ($tax_info['name'] != '' ? $tax_info['name'] : __('Tax', 'wde')) . ': '; ?>
            </span>
            <span><?php echo $tax_info['tax_text']; ?></span>
            <br />
              <?php
            }
          }
          elseif ($order_product_row->product_tax_rate_text) {
            ?>
            <span><?php _e('Tax', 'wde'); ?>:</span>
            <span><?php echo $order_product_row->product_tax_rate_text; ?></span>
            <?php
          }
        }
        $order_product_row->product_final_price_info = WDFTextUtils::remove_html_spaces(ob_get_clean());

        // parameter datas
        $product_parameter_datas = WDFHelper::get_product_parameter_datas($order_product_row->product_id, $order_product_row->id, $order_product_row->product_parameters_json);
        $order_product_row->product_parameter_datas = $product_parameter_datas;

        $order_product_row->shipping_method_row = '';
        $order_product_row->shipping_price_text = '';

        $total_price += $order_product_row->subtotal;
      }

      // Calculate shipping in subtotal and total depend on shipping calculation type.
      foreach ( $order_product_rows as $order_product_row ) {
        $product = new stdClass;
        $product->id = $order_product_row->product_id;
        $product->subtotal_price = $order_product_row->subtotal;
        $product->shipping_tax = $order_product_row->shipping_tax;
        $product->decimals_to_show = $decimals;
        $product->row_default_currency = $row_default_currency;
        $shipping_method_row = WDFProduct::get_product_shippings($product, array('wde_shipping_country_id' => $country_id, 'wde_shipping_zip_code' => $zipcode));
        if ($shipping_method_row) {
          $has_checked_shipping_method = false;
          foreach ($shipping_method_row->shipping_classes as $shipping_class) {
            if ($shipping_class) {
              // Checked.
              if (isset($_POST['product_shipping_method_id_' . $order_product_row->id]) && $shipping_class->id == $_POST['product_shipping_method_id_' . $order_product_row->id]) {
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
          $order_product_row->shipping_method_row = $current_shipping;

          if ($shipping_method_row->free_shipping == 1 || ($shipping_method_row->free_shipping == 2 && $total_price >= $shipping_method_row->free_shipping_start_price)) {
            $shipping_price = 0;
            $order_product_row->shipping_price = 0;
          }
          else {
            $shipping_count = $shipping_method_row->shipping_type == 'per_unit' ? $order_product_row->product_count : 1;
            $shipping_price = $shipping_count * $current_shipping->price;
            $order_product_row->shipping_price = $current_shipping->price;
          }

          $order_product_row->shipping_price_text = $options->option_order_shipping_type == 'per_order' ? '' : WDFHelper::price_text($order_product_row->shipping_price, $decimals, $row_default_currency);

          if ($options->option_order_shipping_type == 'per_item') {
            $order_product_row->subtotal += $shipping_price;
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
        $order_product_row->shipping_method_rows = $shipping_method_row;

        $order_product_row->subtotal_text = WDFHelper::price_text($order_product_row->subtotal, $decimals, $row_default_currency);
      }
    }
    $shipping_method = new stdClass;
    $shipping_method->shipping_type = $options->option_order_shipping_type;
    $shipping_method->shipping_method_price = $total_shipping_price;
    $shipping_method->shipping_method_price_text = WDFHelper::price_text($total_shipping_price, $decimals, $row_default_currency);
    $total_price_without_shipping = $total_price;
    $total_price += $total_shipping_price; // If shipping rate calculation type is per order.
    $total_price_text = WDFHelper::price_text($total_price, $decimals, $row_default_currency);
    return array(
      'order_product_rows' => $order_product_rows,
      'country_id' => $country_id,
      'zipcode' => $zipcode,
      'total_price_without_shipping' => $total_price_without_shipping,
      'total_price' => $total_price_text,
      'shipping_method' => $shipping_method,
    );
  }

  public static function get_order_products_total_price_text() {
    $model_options = WDFHelper::get_model('options', true);
    $options = $model_options->get_options();

    $row_default_currency = WDFDb::get_row('currencies', '`default` = 1');

    // $order_product_rows = $this->get_order_product_rows();

    $total = 0;
    // if (is_array($order_product_rows)) {
      // foreach ($order_product_rows as $order_product_row) {
        // $total += $order_product_row->subtotal;
      // }
    // }
    $decimals = $options->option_show_decimals == 1 ? 2 : 0;
    
    $total_text = '';
    if (WDFText::wde_number_format($total, $decimals) != WDFText::wde_number_format(0, $decimals)) {
      $total_text = WDFText::wde_number_format($total, $decimals);
      $total_text = $row_default_currency->sign_position == 0 ? $row_default_currency->sign . $total_text : $total_text . $row_default_currency->sign;
    }
    return $total_text;
  }

  public static function get_order_product_rand_id() {
    global $wpdb;
    $query = 'SELECT rand_id';
    $query .= ' FROM ' . $wpdb->prefix . 'ecommercewd_orderproducts';
    $existing_rand_ids = $wpdb->get_col();
    if ($wpdb->last_error) {
      return false;
    }

    do {
      $rand_id = rand(10000000, 99999999);
    } while (in_array($rand_id, $existing_rand_ids) == true);

    return $rand_id;
  }

  private static function get_product_parameter_datas($product_id, $product_row_id, $order_product_parameters_json) {
    $model_options = WDFHelper::get_model('options', true);
    $options = $model_options->get_options();
    $decimals = $options->option_show_decimals == 1 ? 2 : 0;
    // get product parameters
    $parameter_rows = array();
    $parameters = WDFJson::decode(esc_attr(get_post_meta($product_id, 'wde_parameters', TRUE)));

    if (is_array($parameters)) {
      foreach ($parameters as $param) {
        $term = get_term($param->id, "wde_parameters");
        $row_temp = get_option("wde_parameters_" . $param->id);
        if (!is_wp_error($term)) {
          $param->name = $term->name;
        }
        if (empty($param->values) == true) {
          $param_to_add = new stdClass();
          $param_to_add->id = $param->id;
          $param_to_add->name = $param->name;
          $param_to_add->type_id = $param->type_id;
          $param_to_add->required = isset($row_temp['required']) ? $row_temp['required'] : false;
          $param_to_add->value = '';
          $param_to_add->value_price = '';
          $parameter_rows[] = $param_to_add;
        }
        else {
          foreach ($param->values as $val) {
            $values = WDFJson::decode($val);
            $param_to_add = new stdClass();
            $param_to_add->id = $param->id;
            $param_to_add->name = $param->name;
            $param_to_add->type_id = $param->type_id;
            $param_to_add->required = isset($row_temp['required']) ? $row_temp['required'] : false;
            $param_to_add->value = $values->value;
            $param_to_add->value_price = $values->price;
            $parameter_rows[] = $param_to_add;
          }
        }
      }
    }

    // order product parameter values map
    $order_product_parameters = WDFJson::decode($order_product_parameters_json);
    $order_product_prameter_values = array();
    if (is_array($order_product_parameters) || is_object($order_product_parameters)) {
      foreach ($order_product_parameters as $parameter_id_product_row_id => $parameter_value) {
        $order_product_prameter_values[$parameter_id_product_row_id] = $parameter_value;
      }
    }
    $row_default_currency = WDFDb::get_row('currencies', '`default` = 1');

    // parameter datas map
    $parameter_datas_map = array();
    foreach ($parameter_rows as $parameter_row) {
      $parameter_id = $parameter_row->id;
      $price_sign = substr($parameter_row->value_price,0,1);	
      $parameter_row->value_price = $price_sign.WDFText::wde_number_format(substr($parameter_row->value_price,1), $decimals);	
      if (isset($parameter_datas_map[$parameter_id])) {
        $parameter_datas_map[$parameter_id]->values[] = array('value' => $parameter_row->value, 'price' => $parameter_row->value_price);
      } else {
        $parameter_data = new stdClass();
        $parameter_data->id = $parameter_row->id;
        $parameter_data->name = $parameter_row->name;
        $parameter_data->type_id = $parameter_row->type_id;
        $parameter_data->required = $parameter_row->required;
        $parameter_data->values[] = array('value' => $parameter_row->value, 'price' => $parameter_row->value_price);
        if (isset($order_product_prameter_values[$parameter_data->id . '_' . $product_row_id])) {
          $parameter_data->value = $order_product_prameter_values[$parameter_data->id . '_' . $product_row_id];
        }
        elseif (count($parameter_data->values) > 0) {
          $parameter_data->value = $parameter_data->values[0]['value'];
        }
        else {
          $parameter_data->value = '';
        }
        $parameter_datas_map[$parameter_id] = $parameter_data;          
      }
    }

    $parameter_datas = array();
    if (is_array($parameter_datas_map)) {
      foreach ($parameter_datas_map as $parameter_data) {
        // if (count($parameter_data->values) <= 1 && $parameter_data->type_id != 1 && $parameter_data->type_id != 3)  {
          // continue;
        // }
        for ($i = 0; $i < count($parameter_data->values); $i++) {
          $parameter_data->values[$i]['text'] = $parameter_data->values[$i]['value'];
          if ($parameter_data->values[$i]['price'] != '+' && $parameter_data->values[$i]['price'] != '') {
            $price_sign = substr($parameter_data->values[$i]['price'], 0, 1);
            $price = substr($parameter_data->values[$i]['price'],1);
            if (WDFText::wde_number_format($price, $decimals) != WDFText::wde_number_format(0, $decimals)) {
              if ($row_default_currency->sign_position == 1) {
                $parameter_data->values[$i]['text'] = $parameter_data->values[$i]['value'] . ' (' . $price_sign . $price . $row_default_currency->sign . ')';
              }
              else {
                $parameter_data->values[$i]['text'] = $parameter_data->values[$i]['value'] . ' (' . $price_sign . $row_default_currency->sign . $price .  ')';
              }
            }
          }
        }
        $parameter_datas[] = $parameter_data;
      }
    }
    return $parameter_datas;
  }

  /**
	 * Get country id until an address is entered.
	 *
	 * @since 1.2.0
	 *
	 * @return The country id depend on current user address if customer is logged in otherwise 0.
	 */
  public static function get_address($options = FALSE) {
    if (!$options) {
      $options_model = WDFHelper::get_model('options');
      $options = $options_model->get_options();
    }
    // Get current user.
    $user = wp_get_current_user();
    if ($user->ID === 0) {
      // If user is not logged in.
      return 0;
    }
    // Get user meta information (shipping/billing country).
    $user_data = get_user_meta($user->ID);
    $shipping_destination = isset($options->shipping_destination) ? $options->shipping_destination : 'shipping';
    $prefix = ($shipping_destination == 'shipping' || $shipping_destination == 'merged') ? 'shipping' : 'billing';
    $country_id = isset($user_data['wde_' . $prefix . '_country_id']) ? (int) $user_data['wde_' . $prefix . '_country_id'][0] : 0;
    $zipcode = isset($user_data['wde_' . $prefix . '_zip_code']) ? $user_data['wde_' . $prefix . '_zip_code'][0] : '';

    return array('country_id' => $country_id, 'zipcode' => $zipcode);
  }

  /**
	 * Get shipping classes.
	 *
	 * @since 1.2.0
	 *
	 * @return The shipping classes.
	 */
  public static function get_shipping_classes() {
    global $wpdb;
    $shipping_classes = $wpdb->get_results('SELECT * FROM `' . $wpdb->prefix . 'ecommercewd_shippingclasses` ORDER BY `name` ASC', OBJECT_K);
    if ($wpdb->last_error) {
      return FALSE;
    }
    else {
      return $shipping_classes;
    }
  }

  /**
	 * Get slug for given row.
	 *
	 * @since 1.2.0
	 *
	 * @return The unique slug.
	 */
  public static function get_unique_slug($slug, $id, $table) {
    $slug = sanitize_title($slug);
    global $wpdb;
    $query = $wpdb->prepare( "SELECT `slug` FROM `" . $wpdb->prefix . $table . "` WHERE `slug`='%s' AND `id`!=%d", $slug, $id );
    if ( $wpdb->get_var( $query ) ) {
      $num = 2;
      do {
        $alt_slug = $slug . "-$num";
        $num++;
        $slug_check = $wpdb->get_var( $wpdb->prepare( "SELECT `slug` FROM `" . $wpdb->prefix . $table . "` WHERE `slug`='%s'", $alt_slug ) );
      } while ( $slug_check );
      $slug = $alt_slug;
    }
    return $slug;
  }

  public static function wde_get_par($k) {
    $k = explode('_', $k);
    return $k[0];
  }

  /**
	 * Check shopping cart.
	 *
	 * @since 1.2.0
	 *
	 */
  public static function check_cart() {
    global $wpdb;
    $j_user = wp_get_current_user();
    $selected_order_products = array();
    if ($j_user->ID != 0) {
      // get user product ids
      $query = 'SELECT id, product_id, product_count, product_name, product_parameters';
      $query .= ' FROM ' . $wpdb->prefix . 'ecommercewd_orderproducts';
      $query .= ' WHERE j_user_id = ' . $j_user->ID;
      $query .= ' AND order_id = 0';
      $user_order_products = $wpdb->get_results($query);

      if ($wpdb->last_error) {
        return false;
      }			
      
      for( $i=0; $i < count($user_order_products); $i++ ) {
        $user_order_product = $user_order_products[$i];
        for($j = $i + 1; $j < count($user_order_products); $j++ ) {
          $_user_order_product = $user_order_products[$j];
          if($user_order_product->product_id == $_user_order_product->product_id) {
            $parameters = WDFHelperFunctions::object_to_array(WDFJson::decode($user_order_product->product_parameters));
            $_parameters = WDFHelperFunctions::object_to_array(WDFJson::decode($_user_order_product->product_parameters));
            if( count($parameters) != count($_parameters)) {
              continue;
            }
            else {
              $array_map_parameters =	!empty($parameters) ? array_map(array('self', 'wde_get_par'), array_keys($parameters)) : array();
              if (!empty($array_map_parameters) && !empty($parameters)) {
                $parameters = array_combine($array_map_parameters , $parameters);
              }
              if (!empty($array_map_parameters) && !empty( $_parameters)) {
                $_parameters = array_combine(array_map(array('self', 'wde_get_par'), array_keys($_parameters)), $_parameters);
              }
          
              $parameters_keys = (!empty($parameters)) ? array_keys($parameters) : array();
              $_parameters_keys = (!empty($_parameters)) ? array_keys($_parameters) : array();
              if (WDFHelperFunctions::multidimensional_array_diff($parameters, $_parameters) == array() && array_diff($parameters_keys, $_parameters_keys) == array()) {
                $selected_order_products[$user_order_product->product_id] = array( 'product_count' => ($_user_order_product->product_count + $user_order_product->product_count), 'parameters' => $parameters, '_row_parameters' =>$_user_order_product->product_parameters, 'row_parameters' =>$user_order_product->product_parameters,'product_name'=>$user_order_product->product_name);
              }
            }
          }
        }
      }
    }
    if (is_array($selected_order_products)) {
      foreach ($selected_order_products as $product_id => $order_product_data) {
        $query = 'DELETE FROM ' . $wpdb->prefix . 'ecommercewd_orderproducts';
        $query .= ' WHERE product_id = ' . $product_id;
        $query .= ' AND order_id = 0';
        $query .= " AND (product_parameters = '" . $order_product_data['_row_parameters'] ."' OR product_parameters = '". $order_product_data['row_parameters'] ."')";
        $wpdb->query($query);
        
        if (!$wpdb->last_error) {
          // insert new order product row
          $query_values = array();
          $query_values['j_user_id'] = is_user_logged_in() ? $j_user->ID : 0;
          $query_values['user_ip_address'] = WDFUtils::get_client_ip_address();
          $query_values['product_id'] = $product_id;
          $query_values['product_name'] = $order_product_data['product_name'];
          $query_values['product_count'] = $order_product_data['product_count'];
          $wpdb->insert($wpdb->prefix . 'ecommercewd_orderproducts', $query_values);
          
          $order_product_id = $wpdb->insert_id;
          
          // insert parameters
          $new_parameters = $order_product_data['parameters'];
          $new_keys = array();
          $new_values = array();
          if(is_array($new_parameters)) {
            foreach ($new_parameters as $parameter_key => $product_parameter) {
              $new_keys[] = $parameter_key . '_' . $order_product_id;
              $new_values[] = $product_parameter;
            }
            $new_parameters = (empty($new_keys) === false && empty($new_values) === false) ? array_combine($new_keys, $new_values) : array();
          }

          $query_values = array();
          $query_values['product_parameters'] = WDFJson::encode($new_parameters);
          $wpdb->update($wpdb->prefix . 'ecommercewd_orderproducts', $query_values, array('id' => $order_product_id));
        }          
      }
    }
  }

  public static function add_guest_user_products() {
    global $wpdb;
    $model = WDFHelper::get_model('shoppingcart', true);
    $j_user = wp_get_current_user();

    // get user product ids
    $query = 'SELECT product_id';
    $query .= ' FROM ' . $wpdb->prefix . 'ecommercewd_orderproducts';
    $query .= ' WHERE j_user_id = ' . $j_user->ID;
    $query .= ' AND order_id = 0';
    $user_product_ids = $wpdb->get_col($query);

    if ($wpdb->last_error) {
      $model->enqueue_message(__('Failed to add your products to the cart', 'wde'), 'danger');
      return false;
    }

    // get order product ids with new rows
    $order_product_rand_ids = WDFInput::cookie_get_array('order_product_rand_ids');

    $query = 'SELECT id';
    $query .= ' FROM ' . $wpdb->prefix . 'ecommercewd_orderproducts';
    $query .= ' WHERE order_id = 0';
    $query .= ' AND j_user_id = 0';
    if (empty($order_product_rand_ids) == false) {
      $query .= ' AND rand_id IN (' . implode(',', $order_product_rand_ids) . ')';
    } else {
      $query .= ' AND 0';
    }
    if (empty($user_product_ids) == false) {
      $query .= ' AND product_id NOT IN (' . implode(',', $user_product_ids) . ')';
    }
    $new_order_product_ids = $wpdb->get_col($query);

    if ($wpdb->last_error) {
      $model->enqueue_message(__('Failed to add your products to the cart', 'wde'), 'danger');
      return false;
    }

    // add new products to users shopping cart
    $query = 'UPDATE ' . $wpdb->prefix . 'ecommercewd_orderproducts';
    $query .= ' SET j_user_id = ' . $j_user->ID;
    if (empty($new_order_product_ids) == false) {
      $query .= ' WHERE id IN (' . implode(',', $new_order_product_ids) . ')';
    } else {
      $query .= ' WHERE 0';
    }
    $wpdb->query($query);

    if ($wpdb->last_error) {
      $model->enqueue_message(__('Failed to add product(s) to your cart', 'wde'), 'danger');
      return false;
    }

    if (empty($new_order_product_ids) == false) {
      $model->enqueue_message(__('Guest products added', 'wde'), 'success');
    }

    // get merged products rand ids
    if (empty($new_order_product_ids) == false) {
      $query = 'SELECT rand_id';
      $query .= ' FROM ' . $wpdb->prefix . 'ecommercewd_orderproducts';
      $query .= ' WHERE id IN (' . implode(',', $new_order_product_ids) . ')';
      $merged_order_product_rand_ids = $wpdb->get_col($query);

      if ($wpdb->last_error) {
        // TODO:
      }
    } else {
      $merged_order_product_rand_ids = array();
    }

    // remove merged order products ids from cookies
    $oreder_product_rand_ids_left = array_diff($order_product_rand_ids, $merged_order_product_rand_ids);
    $oreder_product_rand_ids_left = array_values($oreder_product_rand_ids_left);
    WDFInput::cookie_set_array('order_product_rand_ids', $oreder_product_rand_ids_left);

    return true;
  }

  public static function remove_unavailable_products() {
    global $wpdb;
    $j_user = wp_get_current_user();
    $guest_order_product_rand_ids = WDFInput::cookie_get_array('order_product_rand_ids');

    // get user unavailable order products
    $query = 'SELECT T_ORDER_PRODUCTS.id, T_ORDER_PRODUCTS.rand_id, T_ORDER_PRODUCTS.product_name';
    $query .= ' FROM ' . $wpdb->prefix . 'ecommercewd_orderproducts AS T_ORDER_PRODUCTS';
    $query .= ' LEFT JOIN ' . $wpdb->prefix . 'posts AS T_PRODUCTS ON T_ORDER_PRODUCTS.product_id = T_PRODUCTS.ID';
    $query .= ' WHERE T_ORDER_PRODUCTS.order_id = 0 AND T_PRODUCTS.post_type = \'wde_products\'';
    $query .= ' AND (T_PRODUCTS.ID IS NULL OR T_PRODUCTS.post_status <> \'publish\')';
    if (is_user_logged_in()) {
      $query .= ' AND T_ORDER_PRODUCTS.j_user_id = ' . $j_user->ID;
    } else {
      if (empty($guest_order_product_ids) == false) {
        $query .= ' AND T_ORDER_PRODUCTS.j_user_id = 0';
        $query .= ' AND T_ORDER_PRODUCTS.rand_id IN (' . implode(',', $guest_order_product_rand_ids) . ')';
      } else {
        $query .= ' AND 0';
      }
    }
    $unavailable_products_rows = $wpdb->get_results($query);

    if ($wpdb->last_error) {
      WDFHelper::show_error(2);
    }

    $unavailable_ids = array();
    $unavailable_rand_ids = array();
    $unavailable_products_names = array();
    if (is_array($unavailable_products_rows)) {
      foreach ($unavailable_products_rows as $row_unavailable_product) {
        $unavailable_ids[] = $row_unavailable_product->id;
        $unavailable_rand_ids[] = $row_unavailable_product->rand_id;
        $unavailable_products_names[] = $row_unavailable_product->product_name;
      }
    }

    //remove unavailable order product
    if (empty($unavailable_ids) == false) {
      $query = 'DELETE FROM ' . $wpdb->prefix . 'ecommercewd_orderproducts';
      $query .= ' WHERE id IN (' . implode(',', $unavailable_ids) . ')';
      $wpdb->query($query);

      if ($wpdb->last_error) {
        WDFHelper::show_error(2);
      }

      if (!is_user_logged_in()) {
        $guest_order_product_rand_ids = array_diff($guest_order_product_rand_ids, $unavailable_rand_ids);
        $guest_order_product_rand_ids = array_values($guest_order_product_rand_ids);
        WDFInput::cookie_set_array('order_product_rand_ids', $guest_order_product_rand_ids);
      }
      $model = WDFHelper::get_model('shoppingcart', true);
      $model->enqueue_message(__('Products no longer available', 'wde') . ': ' . implode(', ', $unavailable_products_names), 'info');
    }
  }
}