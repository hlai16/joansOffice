<?php
defined('ABSPATH') || die('Access Denied');

class EcommercewdViewShippingclasses extends EcommercewdView {
  public function display($tpl = null) {
    ?>
    <div class="wrap">
      <?php
      $this->create_toolbar();
      $model = $this->getModel();
      $task = WDFInput::get_task();
      switch ($task) {
        case 'explore':
          $this->_layout = 'explore';
          $this->filter_items = $model->get_rows_filter_items();
          $this->sort_data = $model->get_rows_sort_data();
          $this->pagination = $model->get_rows_pagination();
          $this->rows = $model->get_rows();
          break;
        default:
          $this->_layout = 'default';
          $this->rows = $model->get_rows();
          $this->shipping_class_defaults = $model->get_shipping_class_defaults();
          break;
      }
      parent::display($tpl);
      ?>
    </div>
    <?php
  }

  private function create_toolbar() {
    switch (WDFInput::get_task()) {
      case 'explore':
        break;
      default:
        ?>
        <div class="wde_link_cont">
          <a class="wde_link_inactive" href="<?php echo esc_url(admin_url('admin.php?page=wde_shippingzones')); ?>"><?php _e('Shipping zones', 'wde'); ?></a> | 
          <a class="wde_link_active" href="<?php echo esc_url(admin_url('admin.php?page=wde_shippingclasses')); ?>"><?php _e('Shipping classes', 'wde'); ?></a> 
        </div>
        <?php
        WDFToolbar::title(__('Shipping classes', 'wde'), 'spidershop_shippings');

        WDFToolbar::addButton('save', __('Save', 'wde'));
        WDFToolbar::addToolbar();
        break;
    }
  }
}