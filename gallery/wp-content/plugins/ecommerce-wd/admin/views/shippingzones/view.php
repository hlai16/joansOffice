<?php
defined('ABSPATH') || die('Access Denied');

class EcommercewdViewShippingzones extends EcommercewdView {
  public function display($tpl = null) {
    ?>
    <div class="wrap">
      <?php
      $this->create_toolbar();

      $model = $this->getModel();

      $task = WDFInput::get_task();
      switch ($task) {
        case 'add':
        case 'edit':
          $this->_layout = 'edit';
          $this->row = $model->get_row();
          break;
        default:
          $this->_layout = 'default';
          $this->filter_items = $model->get_rows_filter_items();
          $this->sort_data = $model->get_rows_sort_data();
          $this->pagination = $model->get_rows_pagination();
          $this->rows = $model->get_rows();
          break;
      }
      parent::display($tpl);
      ?>
    </div>
    <?php
  }

  private function create_toolbar() {
    switch (WDFInput::get_task()) {
      case 'add':
        WDFToolbar::title(__('Add shipping zone', 'wde'), 'spidershop_shippings');

        WDFToolbar::addButton('save', __('Save', 'wde'));
        WDFToolbar::addButton('apply', __('Apply', 'wde'));
        WDFToolbar::addButton('cancel', __('Cancel', 'wde'));
        WDFToolbar::addToolbar();
        break;
      case 'edit':
        WDFToolbar::title(__('Edit shipping zone', 'wde'), 'spidershop_shippings');

        WDFToolbar::addButton('save', __('Save', 'wde'));
        WDFToolbar::addButton('apply', __('Apply', 'wde'));
        WDFToolbar::addButton('cancel', __('Cancel', 'wde'));
        WDFToolbar::addToolbar();
        break;
      default:
        ?>
        <div class="wde_link_cont">
          <a class="wde_link_active" href="<?php echo esc_url(admin_url('admin.php?page=wde_shippingzones')); ?>"><?php _e('Shipping zones', 'wde'); ?></a> | 
          <a class="wde_link_inactive" href="<?php echo esc_url(admin_url('admin.php?page=wde_shippingclasses')); ?>"><?php _e('Shipping classes', 'wde'); ?></a> 
        </div>
        <?php
        WDFToolbar::title(__('Shipping zones', 'wde'), 'spidershop_shippings');

        WDFToolbar::addNew();
        WDFToolbar::addButton('enable', __('Enable', 'wde'));
        WDFToolbar::addButton('disable', __('Disable', 'wde'));
        WDFToolbar::addButton('remove_keep_rest', __('Delete', 'wde'));
        WDFToolbar::addToolbar();
        break;
    }
  }
}