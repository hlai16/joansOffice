<?php

defined('ABSPATH') || die('Access Denied');

class EcommercewdControllerPaypalstandard extends EcommercewdController {
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

	public function finish_checkout() {
    WDFChecoutHelper::check_can_checkout();
    WDFChecoutHelper::check_checkout_data();

		$model = WDFHelper::get_model('checkout');
    $message = $model->is_final_checkout_data_valid();
    if ($message != TRUE) {
      WDFChecoutHelper::show_error(0, $message);
    }
    $final_checkout_data = $model->get_final_checkout_data();
    if ($final_checkout_data === false) {
      WDFChecoutHelper::show_error(0, __('Final checkout data is invalid', 'wde'));
    }
    $this->finish_checkout_with_paypal($final_checkout_data);
  }

	/* Paypal return and cancel handler functions.*/
  public function handle_paypal_checkout_return() {
    WDFChecoutHelper::check_can_checkout();

    $model_options = WDFHelper::get_model('options');
    $options = $model_options->get_options();

    // $model = WDFHelper::get_model('checkout');
    // $checkout_data = $model->store_checkout_data();
    $ses_id = WDFSession::get('ses_id');
    $order_id = WDFSession::get('order_id');
      
    $action_display_finished_success = WDFPath::add_pretty_query_args(get_permalink($options->option_checkout_page), $options->option_endpoint_checkout_finished_success, '1', FALSE);
    $action_display_finished_success = add_query_arg(array('ses_id' => $ses_id, 'order_id' => $order_id), $action_display_finished_success);
    wp_redirect($action_display_finished_success);
    exit;
  }

  public function handle_paypal_checkout_cancel() {
    WDFChecoutHelper::check_can_checkout();

    $ses_id = WDFSession::get('ses_id');
    $order_id = WDFSession::get('order_id');
    global $wpdb;
    $status_id = $wpdb->get_var('SELECT id FROM ' . $wpdb->prefix . 'ecommercewd_orderstatuses WHERE name="Cancelled"');
    $update = $wpdb->update($wpdb->prefix . 'ecommercewd_orders', array(
      'status_id' => $status_id,
      'status_name' => "Cancelled",
      'date_modified' => current_time('Y-m-d H:i:s')
    ), array('id' => $order_id));
    WDFChecoutHelper::show_error($ses_id, __('Checkout cancelled', 'wde'));
  }

  public function handle_paypal_checkout_notify() {
    $order_id = WDFSession::get('order_id');
    $model_checkout = WDFHelper::get_model('checkout');
    $checkout_api_options = $model_checkout->checkout_api_options('paypalstandard');
    $is_production = $checkout_api_options->mode == 1 ? true : false;
    WDFPaypalstandard::set_production_mode($is_production);

    // validate ipn
    $ipn_data = WDFPaypalstandard::validate_ipn();
    if (is_array($ipn_data) == false) {
      return false;
    }

    // store ipn in db
    $row_order = WDFDb::get_row_by_id('orders', $order_id);

    // insert new data in payment_data
    $payment_data = WDFJson::decode($row_order->payment_data);
    if ($payment_data == null) {
      $payment_data = array();
    }
    $payment_data[] = $ipn_data;
    $row_order->payment_data = WDFJson::encode($payment_data);

    // update payment data status
    $row_order->payment_data_status = $ipn_data['payment_status'];
    // mark as unread
    $row_order->read = 0;

    global $wpdb;
    $saved = $wpdb->replace($wpdb->prefix . 'ecommercewd_orders', (array) $row_order);
    if (!$saved) {
      $this->save_paypal_ipn_error_log($row_order->id, $ipn_data);
    }

    $model_orders = WDFHelper::get_model('orders');
    // $row_order = $model_orders->add_order_products_data($row_order, true);
    WDFChecoutHelper::send_checkout_finished_mail($row_order, 'notify');
  }

  ////////////////////////////////////////////////////////////////////////////////////////
  // Getters & Setters                                                                  //
  ////////////////////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////////////////////
  // Private Methods                                                                    //
  ////////////////////////////////////////////////////////////////////////////////////////	
	private function finish_checkout_with_paypal($final_checkout_data) {
    $model = WDFHelper::get_model('checkout');
    $checkout_data = $model->store_checkout_data();
    if ($checkout_data === false) {
      return false;
    }
    $order_id = $checkout_data['order_id'];
    
    $model_options = WDFHelper::get_model('options');
    $options = $model_options->get_options();
    $checkout_api_options = $model->checkout_api_options('paypalstandard');
    $is_production = $checkout_api_options->mode == 1 ? true : false;
    WDFPaypalstandard::set_production_mode($is_production);

    // credentials
    $credentials = array('business' => $checkout_api_options->paypal_email);
    WDFPaypalstandard::set_credentials($credentials);

    // callbacks
    $request_url = WDFPath::add_pretty_query_args(get_permalink($options->option_checkout_page), $options->option_endpoint_checkout_confirm_order, '1', FALSE);
    $data = array(
      'ses_id' => $final_checkout_data['ses_id'],
      'order_id' => $order_id,
      'controller' => 'paypalstandard',
    );
    $request_url = add_query_arg($data, $request_url);
    $request_params = array(
      'return' => add_query_arg('local_task', 'handle_paypal_checkout_return', $request_url),
      'cancel_return' => add_query_arg('local_task', 'handle_paypal_checkout_cancel', $request_url),
      'notify_url' => add_query_arg('local_task', 'handle_paypal_checkout_notify', $request_url),
    );

    $products_price_total = 0;
    $product_taxes_price_total = 0;
    $product_shipping_price_total = 0;
    // products data
    $products_data = $final_checkout_data['products_data'];
    $items_params = array();
    $i = 1;
    if (is_array($products_data)) {
      foreach ($products_data as $product_data) {
        $items_params['item_name_' . $i] = $product_data->name;
        $items_params['amount_' . $i] = $product_data->current_price;
        $items_params['tax_' . $i] = $product_data->tax_total;
        $items_params['quantity_' . $i] = $product_data->count;
        if ($final_checkout_data['shipping_method']) {
          $items_params['shipping_' . $i] = $final_checkout_data['shipping_method']->shipping_method_price;
          $final_checkout_data['shipping_method'] = false;
        }
        else {
          $items_params['shipping_' . $i] = $product_data->shipping_method_price;
        }
        $products_price_total += $product_data->count * $product_data->current_price;
        $product_taxes_price_total += $product_data->count * $product_data->tax_total;
        $product_shipping_price_total += $product_data->count * $product_data->shipping_method_price;

        $i++;
      }
    }

    // order data
    $order_params = array(
      // 'PAYMENTREQUEST_0_AMT' => $products_price_total + $product_taxes_price_total + $product_shipping_price_total,
      // 'PAYMENTREQUEST_0_ITEMAMT' => $products_price_total,
      // 'PAYMENTREQUEST_0_TAXAMT' => $product_taxes_price_total,
      // 'shipping' => $product_shipping_price_total,
      'currency_code' => $final_checkout_data['currency_code'],
      'cmd' => "_cart",
      'upload' => 1,
      'charset' => "UTF-8",
    );

    $row_order = WDFDb::get_row_by_id('orders', $order_id);
    $model_orders = WDFHelper::get_model('orders');
    // $row_order = $model_orders->add_order_products_data($row_order, true);
    WDFChecoutHelper::send_checkout_finished_mail($row_order, 'new');
    WDFPaypalstandard::set_standard_checkout($request_params, $order_params, $items_params);
  }

	private function save_paypal_ipn_error_log($order_id, $ipn_data) {
    $log_content = WDFJson::encode($ipn_data);
    file_put_contents($order_id . '_' . date("Y_m_d_H_i_s") . '.txt', $log_content);
  }

  ////////////////////////////////////////////////////////////////////////////////////////
  // Listeners                                                                          //
  ////////////////////////////////////////////////////////////////////////////////////////
}