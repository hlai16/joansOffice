<?php

defined('ABSPATH') || die('Access Denied');

class EcommercewdViewCheckout extends EcommercewdView {
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
  public function display($params = null) {
    $model = $this->getModel();
    $options_model = WDFHelper::get_model('options');
    $this->options = $options_model->get_options();
    $options = $this->options;
    $this->checkout_data = $model->get_checkout_data();

    $model_usermanagement = WDFHelper::get_model('usermanagement');
    $this->row_user = $model_usermanagement->get_current_user_row();

    $task = WDFInput::get_task();
    $task = isset($task) ? $task : $params['layout'];
    $order_id = WDFInput::get('order_id', '');
    
    // $task = $params['layout'];
    switch ($task) {
      case 'displayshippingdata':
        $this->_layout = 'displayshippingdata';
        $this->pager_data = $model->get_pager_data();
        $this->form_fields = $model->get_data_form_fields();
        $final_checkout_data = $model->get_final_checkout_data(1);
        $this->final_checkout_data = $final_checkout_data;
        break;
      case 'displaylicensepages':
        $this->_layout = 'displaylicensepages';
        $this->pager_data = $model->get_pager_data();
        $pages = $model->get_license_pages();
        if ($pages === false) {
          WDFChecoutHelper::show_error($this->checkout_data['ses_id'], __('Error getting license pages', 'wde'));
        }
        $this->pages = $pages;
        break;
      case 'displayconfirmorder':
        $this->_layout = 'displayconfirmorder';
        $this->pager_data = $model->get_pager_data();
        $message = $model->is_final_checkout_data_valid();
        if ($message !== TRUE) {
          WDFChecoutHelper::show_error($this->checkout_data['ses_id'], $message);
        }
        $final_checkout_data = $model->get_final_checkout_data(1);
        if ($final_checkout_data === false) {
          WDFChecoutHelper::show_error($this->checkout_data['ses_id'], __('Final checkout data is invalid', 'wde'));
        }
        $this->row_default_currency = WDFDb::get_row('currencies', '`default`=1');
        $this->final_checkout_data = $final_checkout_data;
        $this->payment_buttons_data = $model->get_payment_buttons_data($final_checkout_data['total_price']);
        break;								
      case 'displaycheckoutfinishedsuccess':
        $this->_layout = 'displaycheckoutfinishedsuccess';
        $this->order_link = $order_id ? WDFPath::add_pretty_query_args(get_permalink($options->option_orders_page), $options->option_endpoint_orders_displayorder, $order_id, TRUE) : '';
        break;
      case 'displaycheckoutfinishedfailure':
        $this->_layout = 'displaycheckoutfinishedfailure';
        break;
      default:
        break;
    }
    parent::display($params);
  }
}