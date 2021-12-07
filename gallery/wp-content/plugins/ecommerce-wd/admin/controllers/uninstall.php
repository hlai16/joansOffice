<?php
defined('ABSPATH') || die('Access Denied');

class EcommercewdControllerUninstall extends EcommercewdController {
  public function __construct() {
    global $wde_options;
    if (!class_exists("TenWebLibConfig")) {
      include_once(WD_E_DIR . "/wd/config.php");
    }
    $config = new TenWebLibConfig();
    $config->set_options($wde_options);
    $deactivate_reasons = new TenWebLibDeactivate($config);
    $deactivate_reasons->submit_and_deactivate();
  }
}