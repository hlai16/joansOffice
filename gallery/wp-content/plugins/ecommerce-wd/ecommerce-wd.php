<?php
/**
 * Plugin Name: Ecommerce by 10Web
 * Plugin URI: https://10web.io/plugins/wordpress-ecommerce/
 * Description: Ecommerce by 10Web is a highly-functional, user friendly eCommerce plugin, which is perfect for developing online stores for any level of complexity.
 * Version: 1.2.15
 * Author: 10Web
 * Author URI: https://10web.io/plugins/
 * License: GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
if (defined('DS') === FALSE) {
  define('DS', DIRECTORY_SEPARATOR);
}
define('WD_E_PLUGIN_NAME', plugin_basename(dirname(__FILE__)));
define('WD_E_DIR', plugin_dir_path(__FILE__));
define('WD_E_URL', plugins_url(WD_E_PLUGIN_NAME));
define('WD_E_FRONT_URL', str_replace(site_url(), home_url(), WD_E_URL));
define('WD_E_NAME', 'ecommercewd');
define('WD_E_VERSION', '1.2.15');
define('WD_E_DB_VERSION', '1.2.15');
define('DEV_MODE', FALSE);

if (DEV_MODE) {
  error_reporting(-1);
  ini_set('display_errors', 'On');
}

// Plugin menu.
function wde_options_panel() {
  // Prepare framework.
  require_once WD_E_DIR . DS . 'framework' . DS . 'WDFText.php';
  $menu_position = '25,99';
  $menu_position_index = 25;
  foreach($GLOBALS['menu'] as $pos => $menu) {
    if (isset($menu[5]) && $menu[5] == 'menu-posts-wde_products') {
      $menu_position = ($pos - 1) . ',99';
      $menu_position_index = $pos;
      break;
    }
  }
  foreach($GLOBALS['menu'] as $pos => $menu) {
    if (isset($menu[5]) && $menu[5] == 'menu-posts-wde_page') {
      $GLOBALS['menu'][$menu_position_index . ',01'] = $menu;
      unset($GLOBALS['menu'][$pos]);
      break;
    }
  }
  add_menu_page(__('Ecommerce Add-ons', 'wde'), __('Ecommerce Add-ons', 'wde'), 'manage_options', 'addons_wde', 'wde_addons', WD_E_URL . '/addons/images/add-ons-icon.png', $menu_position_index . ',2');

  $parent_slug = null;
  if ( get_option( "wde_subscribe_done" ) == 1 ) {
    $parent_slug = "wde_orders";
    $orders_page = add_menu_page(__('Ecommerce', 'wde'), __('Ecommerce', 'wde'), 'manage_options', 'wde_orders', 'wd_ecommerce', WD_E_URL . '/images/wd_ecommerce.png', $menu_position);
    add_action('admin_print_styles-' . $orders_page, 'wde_styles');
    add_action('admin_print_scripts-' . $orders_page, 'wde_scripts');
  }
  add_submenu_page($parent_slug, __('Orders', 'wde'), __('Orders', 'wde'), 'manage_options', 'wde_orders', 'wd_ecommerce');
  
  add_submenu_page($parent_slug, __('Ratings', 'wde'), __('Ratings', 'wde'), 'manage_options', 'wde_ratings', 'wd_ecommerce');

  add_submenu_page($parent_slug, __('Discounts', 'wde'), __('Discounts', 'wde'), 'manage_options', 'edit-tags.php?taxonomy=wde_discounts');
  add_submenu_page($parent_slug, __('Tax classes', 'wde'), __('Tax classes', 'wde'), 'manage_options', 'edit-tags.php?taxonomy=wde_taxes');
  add_submenu_page($parent_slug, __('Shippings', 'wde'), __('Shippings', 'wde'), 'manage_options', 'wde_shippingzones', 'wd_ecommerce');
  add_submenu_page('', __('Shipping classes', 'wde'), __('Shipping classes', 'wde'), 'manage_options', 'wde_shippingclasses', 'wd_ecommerce');

  add_submenu_page('', __('Order statuses', 'wde'), __('Order statuses', 'wde'), 'manage_options', 'wde_orderstatuses', 'wd_ecommerce');
  add_submenu_page($parent_slug, __('Reports', 'wde'), __('Reports', 'wde'), 'manage_options', 'wde_reports', 'wd_ecommerce');

  $currencies_page = add_submenu_page('', __('Currencies', 'wde'), __('Currencies', 'wde'), 'manage_options', 'wde_currencies', 'wd_ecommerce');
  add_action('admin_print_styles-' . $currencies_page, 'wde_styles');
  add_action('admin_print_scripts-' . $currencies_page, 'wde_scripts');
  add_submenu_page('', __('Payment systems', 'wde'), __('Payment systems', 'wde'), 'manage_options', 'wde_payments', 'wd_ecommerce');
  add_submenu_page('', __('Templates', 'wde'), __('Templates', 'wde'), 'manage_options', 'wde_templates', 'wd_ecommerce');
  add_submenu_page($parent_slug, __('Themes', 'wde'), __('Themes', 'wde'), 'manage_options', 'wde_themes', 'wd_ecommerce');
  add_submenu_page($parent_slug, __('Options', 'wde'), __('Options', 'wde'), 'manage_options', 'wde_options', 'wd_ecommerce');
  $uninstall_page = add_submenu_page($parent_slug, __('Uninstall', 'wde'), __('Uninstall', 'wde'), 'manage_options', 'wde_uninstall', 'wd_ecommerce');
  add_action('admin_print_styles-' . $uninstall_page, 'wde_styles');
  add_action('admin_print_scripts-' . $uninstall_page, 'wde_scripts');
}
add_action('admin_menu', 'wde_options_panel');

require_once(WD_E_DIR . '/addons/addons.php');

// Plugin styles.
function wde_styles() {
  wp_admin_css('thickbox');
  wp_enqueue_style('wde_deactivate-css',  WD_E_URL . '/wd/assets/css/deactivate_popup.css', array(), WD_E_VERSION);
}

// Plugin scripts.
function wde_scripts() {
  wp_enqueue_script('thickbox');

  wp_enqueue_script('wde-deactivate-popup', WD_E_URL.'/wd/assets/js/deactivate_popup.js', array(), WD_E_VERSION, true );
  $admin_data = wp_get_current_user();

  wp_localize_script( 'wde-deactivate-popup', 'wdeWDDeactivateVars', array(
    "prefix" => "wde" ,
    "deactivate_class" =>  'wde_deactivate_link',
    "email" => $admin_data->data->user_email,
    "plugin_wd_url" => "https://10web.io/plugins/wordpress-ecommerce/",
  ));
}

function wd_ecommerce($attrs = NULL) {
  // Fix for json functions for older versions of php. Function is decleared in WDFJson.php.
  set_error_handler('wdf_json_error_handler');
  $controller = WDFHelper::get_controller();
  $task = WDFInput::get_task();
  $controller->execute($task, $attrs);
}

function wde_output_buffer() {
  require_once WD_E_DIR . DS . 'framework' . DS . 'WDFHelper.php';
  WDFHelper::init();
}
add_action('admin_init', 'wde_output_buffer');

function wde_output_buffer_init() {
  ob_start();
}
add_action('init', 'wde_output_buffer_init');


add_action('wp_ajax_wde_ajax', 'wde_ajax');

function wde_ajax() {
  if (function_exists('current_user_can')) {
    if (!current_user_can('manage_options')) {
      die('Access Denied');
    }
  }
  else {
    die('Access Denied');
  }

  // Fix for json functions for older versions of php. Function is decleared in WDFJson.php.
  set_error_handler('wdf_json_error_handler');

  // Prepare framework.
  require_once WD_E_DIR . DS . 'framework' . DS . 'WDFHelper.php';
  WDFHelper::init();
  require_once WD_E_DIR . DS . 'framework' . DS . 'WDFToolbar.php';

  // Print order.
  require_once WD_E_DIR . DS . 'helpers' . DS . 'order.php';

  $controller = WDFHelper::get_controller();
  $task = WDFInput::get_task();
  $controller->execute($task);
  die();
}

function wde_shortcode($params) {
  ob_start();
  wde_front_end($params);
  return str_replace(array("\r\n", "\n", "\r"), '', preg_replace('/<!--(.|s)*?-->/', '', ob_get_clean()));
  // return str_replace(array("\r\n", "\n", "\r"), '', ob_get_clean());
  // return ob_get_clean();
}
add_shortcode('wde', 'wde_shortcode');

function wde_front_ajax() {
  if (function_exists('switch_to_locale') && function_exists('get_locale')) {
    switch_to_locale(get_locale());
  }
  $params = array();
  ob_start();
  wde_front_end($params, true);
  // return str_replace(array("\r\n", "\n", "\r"), '', ob_get_clean());
  return ob_get_clean();
}

add_action('wp_ajax_WDEQuickView', 'wde_front_ajax');
add_action('wp_ajax_nopriv_WDEQuickView', 'wde_front_ajax');

add_action('wp_ajax_wde_AddToShoppingCart', 'wde_front_ajax');
add_action('wp_ajax_nopriv_wde_AddToShoppingCart', 'wde_front_ajax');
add_action('wp_ajax_wde_UpdateOrderProduct', 'wde_front_ajax');
add_action('wp_ajax_nopriv_wde_UpdateOrderProduct', 'wde_front_ajax');
add_action('wp_ajax_wde_RemoveOrderProduct', 'wde_front_ajax');
add_action('wp_ajax_nopriv_wde_RemoveOrderProduct', 'wde_front_ajax');

$wde = 0;
$wde_controller = 0;
$wde_product_search_inputs_created = array();
function wde_front_end($params, $is_frontend_ajax = false) {
  global $wde;
  global $wde_controller;

  // Prepare framework.
  require_once WD_E_DIR . DS . 'framework' . DS . 'WDFHelper.php';
  WDFHelper::init();
  require_once WD_E_DIR . DS . 'framework' . DS . 'WDFToolbar.php';
  require_once WD_E_DIR . DS . 'helpers' . DS . 'order.php';

  $type = WDFInput::get('type');
  $input_controller = isset($type) ? $type : $params['type'];
  
  if ($wde && !$is_frontend_ajax && ($input_controller != $wde_controller)) {
    WDFHTML::wd_bs_container_start();
    echo '<div class="alert alert-danger">' . __('Multiple Ecommerce shortcodes are not supported.', 'wde') . '</div>';
    WDFHTML::wd_bs_container_end();
    return;
  }
  $wde_controller = $input_controller;
  $options = WDFDB::get_options();
  $data_arr = array(
    'usermanagement' => array(
      0 => array(
        'option' => $options->option_endpoint_edit_user_account,
        'task' => 'displayupdateuserdata',
      ),
    ),
    'checkout' => array(
      0 => array(
        'option' => $options->option_endpoint_checkout_shipping_data,
        'task' => 'displayshippingdata',
      ),
      1 => array(
        'option' => $options->option_endpoint_checkout_finished_failure,
        'task' => 'displaycheckoutfinishedfailure',
      ),
      2 => array(
        'option' => $options->option_endpoint_checkout_finished_success,
        'task' => 'displaycheckoutfinishedsuccess',
      ),
      3 => array(
        'option' => $options->option_endpoint_checkout_confirm_order,
        'task' => 'displayconfirmorder',
      ),
      4 => array(
        'option' => $options->option_endpoint_checkout_license_pages,
        'task' => 'displaylicensepages',
      ),
      5 => array(
        'option' => $options->option_endpoint_checkout_products_data,
        'task' => 'displayproductsdata',
      ),
      6 => array(
        'option' => $options->option_endpoint_checkout_product,
        'task' => 'checkout_product',
      ),
      7 => array(
        'option' => $options->option_endpoint_checkout_all_products,
        'task' => 'checkout_all_products',
      ),
    ),
    'orders' => array(
      0 => array(
        'option' => $options->option_endpoint_orders_printorder,
        'task' => 'printorder',
      ),
      1 => array(
        'option' => $options->option_endpoint_orders_displayorder,
        'task' => 'displayorder',
      ),
    ),
    'systempages' => array(
      0 => array(
        'option' => $options->option_endpoint_systempages_errnum,
        'task' => 'displayerror',
      ),
    ),
    'products' => array(
      0 => array(
        'option' => $options->option_endpoint_compare_products,
        'task' => 'displaycompareproducts',
      ),
    ),
  );
  $task = array_key_exists($input_controller, $data_arr) ? wde_endpoint($data_arr[$input_controller]) : WDFInput::get_task();
  $task = isset($task) ? $task : ((isset($params['layout']) && $params['layout']) ? $params['layout'] : 'display' . $params['type']);
  $params['layout'] = $task;

  WDFInput::set('task', $task);
 
  $controller_path = WDFPath::get_com_path('', $is_frontend_ajax) . '/controllers/' . $input_controller . '.php';
  if (file_exists($controller_path)) {
    require_once $controller_path;
  }
  else {
    return false;
  }
  $controller_class = ucfirst(WDFHelper::get_com_name()) . 'Controller' . ucfirst($input_controller);
  $controller = new $controller_class();
  $controller->$task($params);

  $wde++;
}

// Register shortcode edit button.
function wde_register($plugin_array) {
  $url = WD_E_URL . '/js/wde_editor_button.js';
  $plugin_array["wde_mce"] = $url;
  return $plugin_array;
}

function wde_admin_ajax() {
  ?>
  <script>
    var wde_admin_ajax = '<?php echo add_query_arg(array('action' => 'WDEShortcode'), admin_url('admin-ajax.php')); ?>';
    var wde_plugin_url = '<?php echo WD_E_URL; ?>';
  </script>
  <?php
}
add_action('admin_head', 'wde_admin_ajax');
add_action('wp_ajax_WDEShortcode', 'wde_shortcode_ajax');
add_filter('mce_external_plugins', 'wde_register');

function wde_add_button($buttons) {
  array_push($buttons, "wde_mce");
  return $buttons;
}
add_filter('mce_buttons', 'wde_add_button', 0);

function wde_shortcode_ajax() {
  if (function_exists('current_user_can')) {
    if (!current_user_can('manage_options')) {
      die('Access Denied');
    }
  }
  else {
    die('Access Denied');
  }
  // Prepare framework.
  require_once WD_E_DIR . DS . 'framework' . DS . 'WDFHelper.php';
  WDFHelper::init();
  // require_once WD_E_DIR . DS . 'framework' . DS . 'WDFToolbar.php';
  require_once(WD_E_DIR . DS . 'admin' . DS . 'views' . DS . 'menu_form_fields' . DS . 'menu_form_fields.php');
}

function wde_install() {
  $version = get_option("wde_version");
  // require_once WD_E_DIR . DS . 'framework' . DS . 'WDFHelper.php';
  // WDFHelper::init();
  if (!isset($_GET['action']) || $_GET['action'] != 'deactivate') {
    if ($version && version_compare($version, WD_E_DB_VERSION, '<')) {
      // Update theme css.
      $theme = new WDFStyle();
      $theme->create_file();
      require_once WD_E_DIR . "/ecommerce-wd-update.php";
      wde_update($version);
      update_option("wde_version", WD_E_DB_VERSION);
    }
    elseif (!$version && (!isset($_GET['page']) || $_GET['page'] != 'wde_uninstall')) {
      require_once WD_E_DIR . DS . "ecommerce-wd-insert.php";
      wde_insert();
      add_option("wde_version", WD_E_DB_VERSION, '', 'no');
      wde_register_custom_post_types();
      wde_create_product_posttype();
      wde_create_manufacturers_posttype();
      flush_rewrite_rules();
      wp_safe_redirect(admin_url('index.php?page=wde_setup'));
      exit;
    }
  }
}
add_action('admin_init', 'wde_install');

// Languages localization.
function wde_language_load() {
  load_plugin_textdomain('wde', FALSE, basename(dirname(__FILE__)) . '/languages');
}
add_action('init', 'wde_language_load');

function wde_register_frontend_scripts() {
  $prefix = 'wde_';
  $frontend_path = WD_E_FRONT_URL . '/frontend/';

  // Theme css.
  require_once WD_E_DIR . DS . 'framework' . DS . 'WDFHelper.php';
  WDFHelper::init();
  $theme = new WDFStyle();
  $theme->check();
  wp_register_style($prefix . 'theme', WD_E_FRONT_URL . '/frontend/css/theme.css', FALSE, get_option('wde_theme_version'));

  // Framework.
  wp_register_script($prefix . 'items_slider', WD_E_FRONT_URL . '/js/framework/items_slider.js', array('jquery'), WD_E_VERSION);
  wp_register_script($prefix . 'utils', WD_E_FRONT_URL . '/js/framework/utils.js', array('jquery'), WD_E_VERSION);
  wp_register_script($prefix . 'jquery.zoom', WD_E_FRONT_URL . '/js/framework/jquery.zoom.js', array('jquery'), WD_E_VERSION);
  wp_register_style($prefix . 'star_rater', WD_E_FRONT_URL . '/css/framework/star_rater.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'star_rater', WD_E_FRONT_URL . '/js/framework/star_rater.js', array('jquery'), WD_E_VERSION);

  // Bootstrap.
  wp_register_style($prefix . 'bootstrap', WD_E_FRONT_URL . '/bootstrap/css/bootstrap.css', FALSE, WD_E_VERSION);
  wp_register_style($prefix . 'base_bootstrap', WD_E_FRONT_URL . '/bootstrap/css/base_bootstrap.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'bootstrap', WD_E_FRONT_URL . '/bootstrap/js/bootstrap.js', array('jquery'), WD_E_VERSION);
  wp_register_script($prefix . 'bootstraphtml5shiv', WD_E_FRONT_URL . '/bootstrap/ie8/html5shiv.js', array('jquery'), WD_E_VERSION);
  wp_register_script($prefix . 'bootstraprespond', WD_E_FRONT_URL . '/bootstrap/ie8/respond.min.js', array('jquery'), WD_E_VERSION);

  // Main.
  wp_register_style($prefix . 'main', $frontend_path . 'css/wd_main.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'main', $frontend_path . 'js/wd_main.js', array('jquery'), WD_E_VERSION);

  // Manufacturer.
  wp_register_style($prefix . 'displaymanufacturer', $frontend_path . 'css/manufacturers/displaymanufacturer.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'displaymanufacturer', $frontend_path . 'js/manufacturers/displaymanufacturer.js', array('jquery'), WD_E_VERSION);

  // Manufacturers.
  wp_register_style($prefix . 'displaymanufacturers', $frontend_path . 'css/manufacturers/displaymanufacturers.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'displaymanufacturers', $frontend_path . 'js/manufacturers/displaymanufacturers.js', array('jquery'), WD_E_VERSION);

  // Category.
  wp_register_style($prefix . 'displaycategory', $frontend_path . 'css/categories/displaycategory.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'displaycategory', $frontend_path . 'js/categories/displaycategory.js', array('jquery'), WD_E_VERSION);

  // Categories.
  wp_register_style($prefix . 'displaycategories', $frontend_path . 'css/categories/displaycategories.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'displaycategories', $frontend_path . 'js/categories/displaycategories.js', array('jquery'), WD_E_VERSION);

  // Usermanagement.
  wp_register_script($prefix . 'displaylogin', $frontend_path . 'js/usermanagement/displaylogin.js', array('jquery'), WD_E_VERSION);
  wp_register_script($prefix . 'displayupdateuserdata', $frontend_path . 'js/usermanagement/displayupdateuserdata.js', array('jquery'), WD_E_VERSION);

  // Shopping cart.
  wp_register_style($prefix . 'displayshoppingcart', $frontend_path . 'css/shoppingcart/displayshoppingcart.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'displayshoppingcart', $frontend_path . 'js/shoppingcart/displayshoppingcart.js', array('jquery'), WD_E_VERSION);

  // Orders.
  wp_register_style($prefix . 'displayorders', $frontend_path . 'css/orders/displayorders.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'displayorders', $frontend_path . 'js/orders/displayorders.js', array('jquery'), WD_E_VERSION);

  // Order.
  wp_register_style($prefix . 'displayorder', $frontend_path . 'css/orders/displayorder.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'displayorder', $frontend_path . 'js/orders/displayorder.js', array('jquery'), WD_E_VERSION);

  // Print order.
  wp_register_script($prefix . 'printorder', $frontend_path . 'js/orders/printorder.js', array('jquery'), WD_E_VERSION);

  // Checkout.
  wp_register_script($prefix . 'displayshippingdata', $frontend_path . 'js/checkout/displayshippingdata.js', array('jquery'), WD_E_VERSION);

  wp_register_script($prefix . 'displaylicensepages', $frontend_path . 'js/checkout/displaylicensepages.js', array('jquery'), WD_E_VERSION);

  wp_register_style($prefix . 'displayconfirmorder', $frontend_path . 'css/checkout/displayconfirmorder.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'displayconfirmorder', $frontend_path . 'js/checkout/displayconfirmorder.js', array('jquery'), WD_E_VERSION);

  wp_register_script($prefix . 'displaycheckoutfinishedfailure', $frontend_path . 'js/checkout/displaycheckoutfinishedfailure.js', array('jquery'), WD_E_VERSION);

  // Product.
  wp_register_style($prefix . 'displayproduct', $frontend_path . 'css/products/displayproduct.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'displayproduct', $frontend_path . 'js/products/displayproduct.js', array('jquery'), WD_E_VERSION);

  // Products.
  wp_register_style($prefix . 'displayproducts', $frontend_path . 'css/products/displayproducts.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'displayproducts', $frontend_path . 'js/products/displayproducts.js', array('jquery'), WD_E_VERSION);

  wp_register_style($prefix . 'displayproducts_arrangementblog_style', $frontend_path . 'css/products/displayproducts_arrangementblog_style.css', FALSE, WD_E_VERSION);

  wp_register_style($prefix . 'displayproducts_arrangementcheese', $frontend_path . 'css/products/displayproducts_arrangementcheese.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'displayproducts_arrangementcheese', $frontend_path . 'js/products/displayproducts_arrangementcheese.js', array('jquery'), WD_E_VERSION);

  wp_register_style($prefix . 'displayproducts_arrangementlist', $frontend_path . 'css/products/displayproducts_arrangementlist.css', FALSE, WD_E_VERSION);

  wp_register_style($prefix . 'displayproducts_arrangementmasonry', $frontend_path . 'css/products/displayproducts_arrangementmasonry.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'displayproducts_arrangementmasonry', $frontend_path . 'js/products/displayproducts_arrangementmasonry.js', array('jquery'), WD_E_VERSION);
  wp_register_script($prefix . 'displayproducts_arrangement.masonry.pkgd.min', $frontend_path . 'js/products/displayproducts_arrangement.masonry.pkgd.min.js', array('jquery'), WD_E_VERSION);

  wp_register_style($prefix . 'displayproducts_arrangementthumbs', $frontend_path . 'css/products/displayproducts_arrangementthumbs.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'displayproducts_arrangementthumbs', $frontend_path . 'js/products/displayproducts_arrangementthumbs.js', array('jquery'), WD_E_VERSION);

  wp_register_style($prefix . 'displayproduct_bartop', $frontend_path . 'css/products/displayproducts_bartop.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'displayproduct_bartop', $frontend_path . 'js/products/displayproducts_bartop.js', array('jquery'), WD_E_VERSION);
  wp_register_style($prefix . 'displayproducts_barsearch', $frontend_path . 'css/products/displayproducts_barsearch.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'displayproducts_barsearch', $frontend_path . 'js/products/displayproducts_barsearch.js', array('jquery'), WD_E_VERSION);
  wp_register_script($prefix . 'displayproducts_bararrangement', $frontend_path . 'js/products/displayproducts_bararrangement.js', array('jquery'), WD_E_VERSION);
  wp_register_script($prefix . 'displayproducts_barsort', $frontend_path . 'js/products/displayproducts_barsort.js', array('jquery'), WD_E_VERSION);
  wp_register_style($prefix . 'displayproducts_barpagination', $frontend_path . 'css/products/displayproducts_barpagination.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'displayproducts_barpagination', $frontend_path . 'js/products/displayproducts_barpagination.js', array('jquery'), WD_E_VERSION);
  wp_register_style($prefix . 'displayproducts_barfilters', $frontend_path . 'css/products/displayproducts_barfilters.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'displayproducts_barfilters', $frontend_path . 'js/products/displayproducts_barfilters.js', array('jquery'), WD_E_VERSION);

  wp_register_style($prefix . 'displayproducts_quickview', $frontend_path . 'css/products/displayproducts_quickview.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'displayproducts_quickview', $frontend_path . 'js/products/displayproducts_quickview.js', array('jquery'), WD_E_VERSION);
  wp_register_style($prefix . 'displayproduct_imagesviewer', $frontend_path . 'css/products/displayproduct_imagesviewer.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'displayproduct_imagesviewer', $frontend_path . 'js/products/displayproduct_imagesviewer.js', array('jquery'), WD_E_VERSION);
  wp_register_style($prefix . 'displayproduct_imagesviewermodal', $frontend_path . 'css/products/displayproduct_imagesviewermodal.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'displayproduct_imagesviewermodal', $frontend_path . 'js/products/displayproduct_imagesviewermodal.js', array('jquery'), WD_E_VERSION);
  wp_register_style($prefix . 'displaycompareproducts', $frontend_path . 'css/products/displaycompareproducts.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'displaycompareproducts', $frontend_path . 'js/products/displaycompareproducts.js', array('jquery'), WD_E_VERSION);

  $fonts_css = "@font-face {
    font-family: 'Glyphicons Halflings';
    src: url('" . WD_E_FRONT_URL . "/bootstrap/fonts/glyphicons-halflings-regular.eot');
    src: url('" . WD_E_FRONT_URL . "/bootstrap/fonts/glyphicons-halflings-regular.eot?#iefix') format('embedded-opentype'), url('" . WD_E_FRONT_URL . "/bootstrap/fonts/glyphicons-halflings-regular.woff') format('woff'), url('" . WD_E_FRONT_URL . "/bootstrap/fonts/glyphicons-halflings-regular.ttf') format('truetype'), url('" . WD_E_FRONT_URL . "/bootstrap/fonts/glyphicons-halflings-regular.svg#glyphicons-halflingsregular') format('svg');
  }";
  wp_add_inline_style($prefix . 'bootstrap', $fonts_css);
}
add_action('wp_enqueue_scripts', 'wde_register_frontend_scripts');

function wde_register_admin_scripts() {
  $prefix = 'wde_';
  $admin_path = WD_E_URL . '/admin/';

  // Scripts for custom taxanomies edit pages.
  $screen = get_current_screen();
  if ($screen->id == 'edit-wde_categories' || $screen->id == 'edit-wde_labels' || $screen->id == 'admin_page_wde_payments') {
    wp_enqueue_media();
  }
  if ($screen->id == 'edit-wde_categories' || $screen->id == 'ecommerce-wd_page_wde_shippingzones') {
    add_thickbox();
  }

  // Bootstrap.
  wp_register_style($prefix . 'bootstrap', WD_E_URL . '/bootstrap/css/bootstrap.css', FALSE, WD_E_VERSION);
  wp_register_style($prefix . 'base_bootstrap', WD_E_URL . '/bootstrap/css/base_bootstrap.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'bootstrap', WD_E_URL . '/bootstrap/js/bootstrap.js', array('jquery'), WD_E_VERSION);
  wp_register_script($prefix . 'bootstraphtml5shiv', WD_E_URL . '/bootstrap/ie8/html5shiv.js', array('jquery'), WD_E_VERSION);
  wp_register_script($prefix . 'bootstraprespond', WD_E_URL . '/bootstrap/ie8/respond.min.js', array('jquery'), WD_E_VERSION);

  // Framework.
  wp_register_script($prefix . 'jquery-ordering', WD_E_URL . '/js/jquery-ordering.js', array('jquery', 'jquery-ui-sortable'), WD_E_VERSION);
  wp_register_script($prefix . 'jquery.flot', WD_E_URL . '/js/reports/src/jquery.flot.min.js', array('jquery'), WD_E_VERSION);
  wp_register_script($prefix . 'jquery.flot.pie', WD_E_URL . '/js/reports/src/jquery.flot.pie.min.js', array('jquery'), WD_E_VERSION);
  wp_register_script($prefix . 'jquery.flot.resize', WD_E_URL . '/js/reports/src/jquery.flot.resize.min.js', array('jquery'), WD_E_VERSION);
  wp_register_script($prefix . 'jquery.flot.stack', WD_E_URL . '/js/reports/src/jquery.flot.stack.min.js', array('jquery'), WD_E_VERSION);
  wp_register_script($prefix . 'jquery.flot.time', WD_E_URL . '/js/reports/src/jquery.flot.time.min.js', array('jquery'), WD_E_VERSION);
  wp_register_script($prefix . 'color_utils', WD_E_URL . '/js/framework/color_utils.js', array('jquery'), WD_E_VERSION);
  wp_register_script($prefix . 'color_picker', WD_E_URL . '/js/framework/color_picker.js', array('jquery'), WD_E_VERSION);
  wp_register_script($prefix . 'thumb_box', WD_E_URL . '/js/framework/thumb_box.js', array('jquery'), WD_E_VERSION);
  wp_register_script($prefix . 'tag_box', WD_E_URL . '/js/framework/tag_box.js', array('jquery'), WD_E_VERSION);
  wp_register_script($prefix . 'star_rater', WD_E_URL . '/js/framework/star_rater.js', array('jquery'), WD_E_VERSION);
  wp_register_script($prefix . 'tree_generator', WD_E_URL . '/js/framework/tree_generator.js', array('jquery'), WD_E_VERSION);
  wp_register_script($prefix . 'module_box', WD_E_URL . '/js/framework/module_box.js', array('jquery'), WD_E_VERSION);
  wp_register_script($prefix . 'calendar', WD_E_URL . '/js/calendar/calendar.js', array('jquery'), WD_E_VERSION);
  wp_register_script($prefix . 'calendar_function', WD_E_URL . '/js/calendar/calendar_function.js', array('jquery'), WD_E_VERSION);
  wp_register_style($prefix . 'buttons', WD_E_URL . '/css/framework/buttons.css', FALSE, WD_E_VERSION);
  wp_register_style($prefix . 'color_picker', WD_E_URL . '/css/framework/color_picker.css', FALSE, WD_E_VERSION);
  wp_register_style($prefix . 'thumb_box', WD_E_URL . '/css/framework/thumb_box.css', FALSE, WD_E_VERSION);
  wp_register_style($prefix . 'tag_box', WD_E_URL . '/css/framework/tag_box.css', FALSE, WD_E_VERSION);
  wp_register_style($prefix . 'star_rater', WD_E_URL . '/css/framework/star_rater.css', FALSE, WD_E_VERSION);
  wp_register_style($prefix . 'tree_generator', WD_E_URL . '/css/framework/tree_generator.css', FALSE, WD_E_VERSION);
  wp_register_style($prefix . 'module_box', WD_E_URL . '/css/framework/module_box.css', FALSE, WD_E_VERSION);
  wp_register_style($prefix . 'calendar-jos', WD_E_URL . '/css/calendar-jos.css', FALSE, WD_E_VERSION);

  wp_register_style($prefix . 'toolbar_icons', WD_E_URL . '/css/toolbar_icons.css', FALSE, WD_E_VERSION);
  wp_register_style($prefix . 'admin_main', WD_E_URL . '/css/wd_main.css', FALSE, WD_E_VERSION);

  wp_register_script($prefix . 'view', WD_E_URL . '/js/view.js', array('jquery'), WD_E_VERSION);
  wp_localize_script($prefix . 'view', 'wde_localize', array( 
    'wde_admin_url' => admin_url(),
    'select_all_message' => sprintf(__( 'Selected %s item(s).', 'wde' ), 'xx'),
    'popup_open_message' => __( 'You must select at least one item.', 'wde' ),
    'fill_required_fields' => __( 'Please fill in all required fields!', 'wde' ),
  ));
  wp_register_style($prefix . 'layout_default', WD_E_URL . '/css/layout_default.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'layout_default', WD_E_URL . '/js/layout_default.js', array('jquery'), WD_E_VERSION);
  wp_register_style($prefix . 'layout_edit', WD_E_URL . '/css/layout_edit.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'layout_edit', WD_E_URL . '/js/layout_edit.js', array('jquery'), WD_E_VERSION);

  // Categories.
  wp_register_style($prefix . 'categories_edit', WD_E_URL . '/css/categories/edit.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'categories_edit', WD_E_URL . '/js/categories/edit.js', array('jquery'), WD_E_VERSION);

  // Countries.
  wp_register_style($prefix . 'countries_edit', WD_E_URL . '/css/countries/edit.css', FALSE, WD_E_VERSION);

  // Currencies.
  wp_register_style($prefix . 'currencies_default', WD_E_URL . '/css/currencies/default.css', FALSE, WD_E_VERSION);
  wp_register_style($prefix . 'currencies_edit', WD_E_URL . '/css/currencies/edit.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'currencies_edit', WD_E_URL . '/js/currencies/edit.js', array('jquery'), WD_E_VERSION);

  // Discounts.
  wp_register_style($prefix . 'discounts_edit', WD_E_URL . '/css/discounts/edit.css', FALSE, WD_E_VERSION);

  // Labels.
  wp_register_style($prefix . 'labels_edit', WD_E_URL . '/css/labels/edit.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'labels_edit', WD_E_URL . '/js/labels/edit.js', array('jquery'), WD_E_VERSION);

  // Manufacturers.
  wp_register_style($prefix . 'manufacturers_edit', WD_E_URL . '/css/manufacturers/edit.css', FALSE, WD_E_VERSION);

  // Options.
  wp_register_style($prefix . 'options_default', WD_E_URL . '/css/options/default.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'options_default', WD_E_URL . '/js/options/default.js', array('jquery'), WD_E_VERSION);

  // Orders.
  wp_register_style($prefix . 'orders_default', WD_E_URL . '/css/orders/default.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'orders_default', WD_E_URL . '/js/orders/default.js', array('jquery'), WD_E_VERSION);
  wp_register_style($prefix . 'orders_edit', WD_E_URL . '/css/orders/edit.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'orders_edit', WD_E_URL . '/js/orders/edit.js', array('jquery'), WD_E_VERSION);
  wp_register_style($prefix . 'orderproducts', WD_E_URL . '/css/orders/orderproducts.css', FALSE, WD_E_VERSION);
  wp_register_style($prefix . 'paymentdata', WD_E_URL . '/css/orders/paymentdata.css', FALSE, WD_E_VERSION);
  wp_register_style($prefix . 'orders_view', WD_E_URL . '/css/orders/view.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'orders_order_view', WD_E_URL . '/js/orders/order_view.js', array('jquery'), WD_E_VERSION);

  // Parameters.
  wp_register_style($prefix . 'parameters_edit', WD_E_URL . '/css/parameters/edit.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'parameters_edit', WD_E_URL . '/js/parameters/edit.js', array('jquery'), WD_E_VERSION);

  // Parameters.
  wp_register_style($prefix . 'payments_edit', WD_E_URL . '/css/payments/edit.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'payments_edit', WD_E_URL . '/js/payments/edit.js', array('jquery'), WD_E_VERSION);

  // Products.
  wp_register_style($prefix . 'products_edit', WD_E_URL . '/css/products/edit.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'products_edit', WD_E_URL . '/js/products/edit.js', array('jquery'), WD_E_VERSION);

  // Ratings.
  wp_register_style($prefix . 'ratings_default', WD_E_URL . '/css/ratings/default.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'ratings_default', WD_E_URL . '/js/ratings/default.js', array('jquery'), WD_E_VERSION);

  // Reports.
  wp_register_style($prefix . 'reports_default', WD_E_URL . '/css/reports/default.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'reports_default', WD_E_URL . '/js/reports/default.js', array('jquery'), WD_E_VERSION);

  // Tags.
  wp_register_style($prefix . 'tags_edit', WD_E_URL . '/css/tags/edit.css', FALSE, WD_E_VERSION);

  // Taxes.
  wp_register_style($prefix . 'taxes_edit', WD_E_URL . '/css/taxes/edit.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'taxes_edit', WD_E_URL . '/js/taxes/edit.js', array('jquery'), WD_E_VERSION);
  wp_localize_script($prefix . 'taxes_edit', 'wde_localize', array(
    'select_one_item'  => __('You must select at least one item.', 'wde'),
    'country_id'  => __('Country id', 'wde'),
  ));

  // Themes.
  wp_register_style($prefix . 'themes_default', WD_E_URL . '/css/themes/default.css', FALSE, WD_E_VERSION);
  wp_register_style($prefix . 'themes_edit', WD_E_URL . '/css/themes/edit.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'themes_edit', WD_E_URL . '/js/themes/edit.js', array('jquery'), WD_E_VERSION);

  // Users.
  wp_register_style($prefix . 'users_default', WD_E_URL . '/css/users/default.css', FALSE, WD_E_VERSION);

  $fonts_css = "@font-face {
    font-family: 'Glyphicons Halflings';
    src: url('" . WD_E_URL . "/bootstrap/fonts/glyphicons-halflings-regular.eot');
    src: url('" . WD_E_URL . "/bootstrap/fonts/glyphicons-halflings-regular.eot?#iefix') format('embedded-opentype'), url('" . WD_E_URL . "/bootstrap/fonts/glyphicons-halflings-regular.woff') format('woff'), url('" . WD_E_URL . "/bootstrap/fonts/glyphicons-halflings-regular.ttf') format('truetype'), url('" . WD_E_URL . "/bootstrap/fonts/glyphicons-halflings-regular.svg#glyphicons-halflingsregular') format('svg');
  }";
  wp_add_inline_style($prefix . 'bootstrap', $fonts_css);

  // Shipping zones.
  wp_register_script($prefix . 'shippingzones_edit', WD_E_URL . '/js/shippingzones/edit.js', array('jquery'), WD_E_VERSION);
  wp_register_style($prefix . 'shippingzones_edit', WD_E_URL . '/css/shippingzones/edit.css', FALSE, WD_E_VERSION);
  wp_register_style($prefix . 'shippingzones_default', WD_E_URL . '/css/shippingzones/default.css', FALSE, WD_E_VERSION);

  // Shipping classes.
  wp_register_script($prefix . 'shippingclasses_default', WD_E_URL . '/js/shippingclasses/default.js', array('jquery'), WD_E_VERSION);
  wp_register_style($prefix . 'shippingclasses_default', WD_E_URL . '/css/shippingclasses/default.css', FALSE, WD_E_VERSION);

  // Roboto font for top bar.
  wp_register_style($prefix . 'roboto', 'https://fonts.googleapis.com/css?family=Roboto:300,400,500,700');
  wp_register_style($prefix . 'pricing', WD_E_URL . '/css/pricing.css', array(), WD_E_VERSION);
}
add_action('admin_enqueue_scripts', 'wde_register_admin_scripts');

function wde_register_ajax_scripts($controller = '') {
  $prefix = 'wde_';
  wp_register_style($prefix . 'bootstrap', WD_E_URL . '/bootstrap/css/bootstrap.css', FALSE, WD_E_VERSION);
  wp_register_style($prefix . 'base_bootstrap', WD_E_URL . '/bootstrap/css/base_bootstrap.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'bootstrap', WD_E_URL . '/bootstrap/js/bootstrap.js', array('jquery'), WD_E_VERSION);
  wp_register_script($prefix . 'bootstraphtml5shiv', WD_E_URL . '/bootstrap/ie8/html5shiv.js', array('jquery'), WD_E_VERSION);
  wp_register_script($prefix . 'bootstraprespond', WD_E_URL . '/bootstrap/ie8/respond.min.js', array('jquery'), WD_E_VERSION);

  wp_register_script($prefix . 'tiny_mce_popup', site_url() . '/wp-includes/js/tinymce/tiny_mce_popup.js');
  wp_register_style($prefix . 'menu_form_fields', WD_E_URL . '/css/menu_form_fields/menu_form_fields.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'menu_form_fields', WD_E_URL . '/js/menu_form_fields/menu_form_fields.js', array('jquery'), WD_E_VERSION);
  wp_register_script($prefix . 'thumb_box', WD_E_URL . '/js/framework/thumb_box.js', array('jquery'), WD_E_VERSION);
  wp_register_script($prefix . 'tag_box', WD_E_URL . '/js/framework/tag_box.js', array('jquery'), WD_E_VERSION);
  wp_register_script($prefix . 'star_rater', WD_E_URL . '/js/framework/star_rater.js', array('jquery'), WD_E_VERSION);
  wp_register_script($prefix . 'tree_generator', WD_E_URL . '/js/framework/tree_generator.js', array('jquery'), WD_E_VERSION);
  wp_register_script($prefix . 'module_box', WD_E_URL . '/js/framework/module_box.js', array('jquery'), WD_E_VERSION);
  wp_register_script($prefix . 'calendar', WD_E_URL . '/js/calendar/calendar.js', array('jquery'), WD_E_VERSION);
  wp_register_script($prefix . 'calendar_function', WD_E_URL . '/js/calendar/calendar_function.js', array('jquery'), WD_E_VERSION);
  wp_register_style($prefix . 'buttons', WD_E_URL . '/css/framework/buttons.css', FALSE, WD_E_VERSION);
  wp_register_style($prefix . 'color_picker', WD_E_URL . '/css/framework/color_picker.css', FALSE, WD_E_VERSION);
  wp_register_style($prefix . 'thumb_box', WD_E_URL . '/css/framework/thumb_box.css', FALSE, WD_E_VERSION);
  wp_register_style($prefix . 'tag_box', WD_E_URL . '/css/framework/tag_box.css', FALSE, WD_E_VERSION);
  wp_register_style($prefix . 'star_rater', WD_E_URL . '/css/framework/star_rater.css', FALSE, WD_E_VERSION);
  wp_register_style($prefix . 'tree_generator', WD_E_URL . '/css/framework/tree_generator.css', FALSE, WD_E_VERSION);
  wp_register_style($prefix . 'module_box', WD_E_URL . '/css/framework/module_box.css', FALSE, WD_E_VERSION);
  wp_register_style($prefix . 'calendar-jos', WD_E_URL . '/css/calendar-jos.css', FALSE, WD_E_VERSION);

  wp_register_script($prefix . 'view', WD_E_URL . '/js/view.js', array('jquery'), WD_E_VERSION);
  wp_register_style($prefix . 'layout_explore', WD_E_URL . '/css/layout_explore.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'layout_explore', WD_E_URL . '/js/layout_explore.js', array('jquery'), WD_E_VERSION);

  wp_register_style($prefix . 'categories_explore', WD_E_URL . '/css/categories/explore.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'categories_explore', WD_E_URL . '/js/categories/explore.js', array('jquery'), WD_E_VERSION);
  wp_register_script($prefix . 'categories_show_tree', WD_E_URL . '/js/categories/show_tree.js', array('jquery'), WD_E_VERSION);

  wp_register_style($prefix . 'countries_explore', WD_E_URL . '/css/countries/explore.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'countries_explore', WD_E_URL . '/js/countries/explore.js', array('jquery'), WD_E_VERSION);

  wp_register_style($prefix . 'currencies_explore_currencies', WD_E_URL . '/css/currencies/explore_currencies.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'currencies_explore_currencies', WD_E_URL . '/js/currencies/explore_currencies.js', array('jquery'), WD_E_VERSION);
  wp_register_style($prefix . 'currencies_explore_paypal_currencies', WD_E_URL . '/css/currencies/explore_paypal_currencies.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'currencies_explore_paypal_currencies', WD_E_URL . '/js/currencies/explore_paypal_currencies.js', array('jquery'), WD_E_VERSION);

  wp_register_script($prefix . 'discounts_explore', WD_E_URL . '/js/discounts/explore.js', array('jquery'), WD_E_VERSION);

  wp_register_style($prefix . 'labels_explore', WD_E_URL . '/css/labels/explore.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'labels_explore', WD_E_URL . '/js/labels/explore.js', array('jquery'), WD_E_VERSION);

  wp_register_style($prefix . 'manufacturers_explore', WD_E_URL . '/css/manufacturers/explore.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'manufacturers_explore', WD_E_URL . '/js/manufacturers/explore.js', array('jquery'), WD_E_VERSION);

  wp_register_script($prefix . 'orders_printorder', WD_E_URL . '/js/orders/printorder.js', array('jquery'), WD_E_VERSION);
  wp_register_style($prefix . 'orderproducts_explore', WD_E_URL . '/css/orderproducts/explore.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'orderproducts_explore', WD_E_URL . '/js/orderproducts/explore.js', array('jquery'), WD_E_VERSION);

  wp_register_style($prefix . 'pages_explore', WD_E_URL . '/css/pages/explore.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'pages_explore', WD_E_URL . '/js/pages/explore.js', array('jquery'), WD_E_VERSION);

  wp_register_script($prefix . 'parameters_explore', WD_E_URL . '/js/parameters/explore.js', array('jquery'), WD_E_VERSION);

  wp_register_script($prefix . 'shippingclasses_explore', WD_E_URL . '/js/shippingclasses/explore.js', array('jquery'), WD_E_VERSION);

  wp_register_script($prefix . 'tags_explore', WD_E_URL . '/js/tags/explore.js', array('jquery'), WD_E_VERSION);

  wp_register_script($prefix . 'taxes_explore', WD_E_URL . '/js/taxes/explore.js', array('jquery'), WD_E_VERSION);

  wp_register_style($prefix . 'users_explore', WD_E_URL . '/css/users/explore.css', FALSE, WD_E_VERSION);
  wp_register_script($prefix . 'users_explore', WD_E_URL . '/js/users/explore.js', array('jquery'), WD_E_VERSION);
}

require_once WD_E_DIR . DS . 'ecommerce-wd-includes.php';

/*
  * Register addon widgets.
  * Since version 2.0.13
*/
function wde_register_widgets() {
  if (class_exists('WP_Widget')) {
    $widgets = apply_filters('wde_widgets_list', array());
    if (is_array($widgets)) {
      foreach ($widgets as $widget) {
        if (isset($widget['class_name']) && isset($widget['version_required']) && !version_compare($widget['version_required'], WD_E_DB_VERSION, '>')) {
          register_widget($widget['class_name']);
        }
      }
    }
  }
}
add_action('widgets_init', 'wde_register_widgets');

function wde_overview() {
  if (is_admin() && !isset($_REQUEST['ajax'])) {
    if (!class_exists("TenWebLib")) {
      $plugin_dir = apply_filters('tenweb_free_users_lib_path', array('version' => '1.1.1', 'path' => WD_E_DIR));
      require_once($plugin_dir['path'] . '/wd/start.php');
    }
    global $wde_options;
    $wde_options = array(
      "prefix" => "wde",
      "wd_plugin_id" => 157,
      "plugin_id" => 103,
      "plugin_title" => "Ecommerce by 10Web",
      "plugin_wordpress_slug" => "ecommerce-wd",
      "plugin_dir" => WD_E_DIR,
      "plugin_main_file" => __FILE__,
      "description" => __('Ecommerce by 10Web is a highly-functional, user friendly eCommerce plugin, which is perfect for developing online stores for any level of complexity.', 'wde'),
      "plugin_features" => array(
        0 => array(
          "title" => __("Easy Set-up", "wde"),
          "description" => __("Upon activation you'll be redirected to a setup wizard to configure basic options. After you can instantly proceed with creation of your first products.", "wde"),
        ),
        1 => array(
          "title" => __("Product Parameters", "wde"),
          "description" => __("The plugin allows to create products and assign them multiple parameters. Depending on parameter value product prices can vary.", "wde"),
        ),
        2 => array(
          "title" => __("PayPal Standard/Express Integration", "wde"),
          "description" => __("Ecommerce by 10Web supports PayPal payment gateway. You can choose between PayPal Payments Standard or PayPal Express.", "wde"),
        ),
        3 => array(
          "title" => __("Manufacturers and Labels", "wde"),
          "description" => __("Any product manufacturer can be set up and presented in a designated pages. Labels will be added on product images as an overlay.", "wde"),
        ),
        4 => array(
          "title" => __("5 Built-in Themes", "wde"),
          "description" => __("You can change styles and colors for buttons, header, navigation bar,user and product panels, pagination and more without any coding skill. You can also enable/disable certain content elements for product views. The plugin allows you to configure themes and see results in live preview in backend.", "wde"),
        )
      ),
      "video_youtube_id" => "",
      "plugin_wd_url" => "https://10web.io/plugins/wordpress-ecommerce/?utm_source=ecommerce&utm_medium=free_plugin",
      "plugin_wd_demo_link" => "https://demo.10web.io/ecommerce/",
      "plugin_wd_addons_link" => "https://10web.io/plugins/wordpress-ecommerce/#plugin_extensions",
      "after_subscribe" => admin_url('admin.php?page=wde_orders'),
      "plugin_wizard_link" => admin_url('admin.php?page=wde_setup'),
      "plugin_menu_title" => "Ecommerce by 10Web",
      "plugin_menu_icon" => WD_E_URL . '/images/wd_ecommerce.png',
      "deactivate" => true,
      "subscribe" => true,
      "custom_post" => 'wde_orders',
      "menu_position" => '25,99',
      "display_overview" => false,
    );

    ten_web_lib_init($wde_options);
  }
}
add_action('init', 'wde_overview');

function wde_add_plugin_meta_links($meta_fields, $file) {
  if ( plugin_basename(__FILE__) == $file ) {
    $plugin_url = "https://wordpress.org/support/plugin/ecommerce-wd";
    $prefix = 'wde';
    $meta_fields[] = "<a href='" . $plugin_url . "' target='_blank'>" . __('Support Forum', $prefix) . "</a>";
    $meta_fields[] = "<a href='" . $plugin_url . "/reviews#new-post' target='_blank' title='" . __('Rate', $prefix) . "'>
            <i class='wdi-rate-stars'>"
      . "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
      . "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
      . "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
      . "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
      . "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
      . "</i></a>";

    $stars_color = "#ffb900";

    echo "<style>"
      . ".wdi-rate-stars{display:inline-block;color:" . $stars_color . ";position:relative;top:3px;}"
      . ".wdi-rate-stars svg{fill:" . $stars_color . ";}"
      . ".wdi-rate-stars svg:hover{fill:" . $stars_color . "}"
      . ".wdi-rate-stars svg:hover ~ svg{fill:none;}"
      . "</style>";
  }

  return $meta_fields;
}
add_filter("plugin_row_meta", 'wde_add_plugin_meta_links', 10, 2);


add_filter('tw_get_plugin_blocks', 'wde_register_plugin_block');
function wde_register_plugin_block($blocks) {
  $plugin_name = 'Ecommerce-WD';
  $blocks['tw/ecommerce-wd'] = array(
    'title' => "Ecommerce",
    'titleSelect' => sprintf(__('Select %s', 'wde'), $plugin_name),
    'iconUrl' => WD_E_URL . '/images/tw-gb/icon.svg',
    'iconSvg' => array('width' => 20, 'height' => 20, 'src' => WD_E_URL . '/images/tw-gb/icon_grey.svg'),
    'isPopup' => true,
    'containerClass' => 'tw-container-wrap-800-540',
    'data' => array('shortcodeUrl' => add_query_arg(array('action' => 'WDEShortcode'), admin_url('admin-ajax.php'))),
  );
  return $blocks;
}
// Enqueue block editor assets for Gutenberg.
function wde_register_block_editor_assets($assets) {
  $version = '2.0.3';
  $js_path = WD_E_URL . '/js/tw-gb/block.js';
  $css_path = WD_E_URL . '/css/tw-gb/block.css';
  if (!isset($assets['version']) || version_compare($assets['version'], $version) === -1) {
    $assets['version'] = $version;
    $assets['js_path'] = $js_path;
    $assets['css_path'] = $css_path;
  }
  return $assets;
}
add_filter('tw_get_block_editor_assets', 'wde_register_block_editor_assets');

function wde_enqueue_block_editor_assets() {
  // Remove previously registered or enqueued versions
  $wp_scripts = wp_scripts();
  foreach ( $wp_scripts->registered as $key => $value ) {
    // Check for an older versions with prefix.
    if (strpos($key, 'tw-gb-block') > 0) {
      wp_deregister_script( $key );
      wp_deregister_style( $key );
    }
  }
  // Get plugin blocks from all 10Web plugins.
  $blocks = apply_filters('tw_get_plugin_blocks', array());
  // Get the last version from all 10Web plugins.
  $assets = apply_filters('tw_get_block_editor_assets', array());
  // Not performing unregister or unenqueue as in old versions all are with prefixes.
  wp_enqueue_script('tw-gb-block', $assets['js_path'], array( 'wp-blocks', 'wp-element' ), $assets['version']);
  wp_localize_script('tw-gb-block', 'tw_obj_translate', array(
    'nothing_selected' => __('Nothing selected.', 'wde'),
    'empty_item' => __('- Select -', 'wde'),
    'blocks' => json_encode($blocks)
  ));
  wp_enqueue_style('tw-gb-block', $assets['css_path'], array( 'wp-edit-blocks' ), $assets['version']);
}
add_action('enqueue_block_editor_assets', 'wde_enqueue_block_editor_assets');
