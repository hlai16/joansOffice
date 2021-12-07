<?php
defined('ABSPATH') || die('Access Denied');

class EcommercewdView extends WDFSiteViewBase {
  public function display($params) {
    $model = $this->getModel();
    $messages = $model->get_messages();

    // Theme css.
    wp_enqueue_style('wde_theme');

    // Bootstrap.
    wp_enqueue_script('wde_bootstrap');
    wp_enqueue_style('wde_bootstrap');
    // For IE8 and below bootstrap responsive view.
    wp_enqueue_script('wde_bootstraphtml5shiv');
    wp_enqueue_script('wde_bootstraprespond');

    wp_enqueue_script('wde_main');
    wp_enqueue_style('wde_main');
    ?>
    <script>
      var COM_NAME = "<?php echo WDFHelper::get_com_name(); ?>";
      var CONTROLLER = "<?php echo WDFInput::get_controller(); ?>";
      var TASK = "<?php echo WDFInput::get_task(); ?>";
    </script>
    <?php
    global $is_IE;
    if (!$is_IE) {
      ?>
    <style>
      #wd_shop_container {
        display: none;
      }
    </style>
      <?php
    }
    for ($i = 1; $i < 13; $i++) {
      ?>
    <div id="wd_shop_container_<?php echo $i; ?>">
      <?php
    }
    ?>
    <div id="wd_shop_container">
      <?php
      if (is_array($messages)) {
        foreach ($messages as $msg => $type) {
          ?>
          <div class="alert alert-<?php echo $type; ?>">
            <p>
              <?php echo $msg; ?>
            </p>
          </div>
          <?php
        }
      }
      parent::display($params);
      ?>
    </div>
      <?php
    for ($i = 1; $i < 13; $i++) {
      ?>
    </div>
      <?php
    }
  }
}