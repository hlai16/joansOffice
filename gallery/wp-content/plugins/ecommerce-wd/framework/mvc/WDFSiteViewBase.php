<?php
defined('ABSPATH') || die('Access Denied');

class WDFSiteViewBase {
  public function __construct($model) {
    $this->model = $model;
  }

  public function display($params) {
    if (isset($_GET['filter'])) {
      $filter = esc_html($_GET['filter']);
      $view_path = apply_filters($filter, 'view');
      require_once $view_path;
    }
    else {
      $controller = $params['type'];
      if ($controller != 'theme') {
        $task = WDFInput::get_task();
      }
      $task = isset($task) ? $task : $params['layout'];
      require WD_E_DIR . '/frontend/views/' . $controller . '/tmpl/' . $task . '.php';
    }
  }

  public function getModel() {
    return $this->model;
  }
}