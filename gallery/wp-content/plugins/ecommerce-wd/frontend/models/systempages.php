<?php
defined('ABSPATH') || die('Access Denied');

class EcommercewdModelSystempages extends EcommercewdModel {
  private $error_message_list = array(
                                  1 => 'Wrong request',
                                  2 => 'Error loading page',
                                  3 => 'Could not retrieve checkout information',
                                  4 => 'Could not find product',
                                  5 => 'Could not find products',
                                  6 => 'Could not retrieve product information',
                                  7 => 'Manufacturer not found',
                                  8 => 'Error loading options',
                                  9 => 'Error loading order',
                                  );

  public function get_error() {
    $error = new stdClass();
    
    $model_options = WDFHelper::get_model('options');
    $options = $model_options->get_options();
    $error->code = get_query_var($options->option_endpoint_systempages_errnum);

    if (isset($this->error_message_list[$error->code])) {
      $error->header = __('Error', 'wde');
      $error->msg = __($this->error_message_list[$error->code], 'wde');
    }
    else {
      $error->header = __('Error', 'wde');
      $error->msg = '';
    }

    return $error;
  }
}
