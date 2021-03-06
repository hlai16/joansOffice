<?php

defined('ABSPATH') || die('Access Denied');

add_filter('query_vars', 'wde_add_query_vars');
function wde_add_query_vars($vars) {
  require_once WD_E_DIR . DS . 'framework' . DS . 'WDFDb.php';
  $options = WDFDb::get_options();
  if ($options) {
    $vars[] = $options->option_endpoint_compare_products;
    $vars[] = $options->option_endpoint_edit_user_account;
    $vars[] = $options->option_endpoint_checkout_shipping_data;
    $vars[] = $options->option_endpoint_checkout_products_data;
    $vars[] = $options->option_endpoint_checkout_license_pages;
    $vars[] = $options->option_endpoint_checkout_confirm_order;
    $vars[] = $options->option_endpoint_checkout_finished_success;
    $vars[] = $options->option_endpoint_checkout_finished_failure;
    $vars[] = $options->option_endpoint_checkout_product;
    $vars[] = $options->option_endpoint_checkout_all_products;
    $vars[] = $options->option_endpoint_orders_displayorder;
    $vars[] = $options->option_endpoint_orders_printorder;
    $vars[] = $options->option_endpoint_systempages_errnum;
  }
  return $vars;
}

add_action('init', 'wde_rewrite_add_rewrites');
function wde_rewrite_add_rewrites() {
  require_once WD_E_DIR . DS . 'framework' . DS . 'WDFDb.php';
  $options = WDFDb::get_options();
  if ($options) {
    if (isset($options->option_endpoint_compare_products)) {
      add_rewrite_endpoint($options->option_endpoint_compare_products, EP_PERMALINK | EP_PAGES);
    }
    if (isset($options->option_endpoint_edit_user_account)) {
      add_rewrite_endpoint($options->option_endpoint_edit_user_account, EP_PERMALINK | EP_PAGES);
    }
    if (isset($options->option_endpoint_checkout_shipping_data)) {
      add_rewrite_endpoint($options->option_endpoint_checkout_shipping_data, EP_PERMALINK | EP_PAGES);
    }
    if (isset($options->option_endpoint_checkout_products_data)) {
      add_rewrite_endpoint($options->option_endpoint_checkout_products_data, EP_PERMALINK | EP_PAGES);
    }
    if (isset($options->option_endpoint_checkout_license_pages)) {
      add_rewrite_endpoint($options->option_endpoint_checkout_license_pages, EP_PERMALINK | EP_PAGES);
    }
    if (isset($options->option_endpoint_checkout_confirm_order)) {
      add_rewrite_endpoint($options->option_endpoint_checkout_confirm_order, EP_PERMALINK | EP_PAGES);
    }
    if (isset($options->option_endpoint_checkout_finished_success)) {
      add_rewrite_endpoint($options->option_endpoint_checkout_finished_success, EP_PERMALINK | EP_PAGES);
    }
    if (isset($options->option_endpoint_checkout_finished_failure)) {
      add_rewrite_endpoint($options->option_endpoint_checkout_finished_failure, EP_PERMALINK | EP_PAGES);
    }
    if (isset($options->option_endpoint_checkout_product)) {
      add_rewrite_endpoint($options->option_endpoint_checkout_product, EP_PERMALINK | EP_PAGES);
    }
    if (isset($options->option_endpoint_checkout_all_products)) {
      add_rewrite_endpoint($options->option_endpoint_checkout_all_products, EP_PERMALINK | EP_PAGES);
    }
    if (isset($options->option_endpoint_orders_displayorder)) {
      add_rewrite_endpoint($options->option_endpoint_orders_displayorder, EP_PERMALINK | EP_PAGES);
    }
    if (isset($options->option_endpoint_orders_printorder)) {
      add_rewrite_endpoint($options->option_endpoint_orders_printorder, EP_PERMALINK | EP_PAGES);
    }
    if (isset($options->option_endpoint_systempages_errnum)) {
      add_rewrite_endpoint($options->option_endpoint_systempages_errnum, EP_PERMALINK | EP_PAGES);
    }
  }
}

add_filter('request', 'wde_rewrite_filter_request');
function wde_rewrite_filter_request($vars) {
  require_once WD_E_DIR . DS . 'framework' . DS . 'WDFDb.php';
  $options = WDFDb::get_options();
  if ($options) {
    if (isset($vars[$options->option_endpoint_compare_products])) {
      $vars[$options->option_endpoint_compare_products] = true;
    }
    if (isset($vars[$options->option_endpoint_edit_user_account])) {
      $vars[$options->option_endpoint_edit_user_account] = true;
    }
    if (isset($vars[$options->option_endpoint_checkout_shipping_data])) {
      $vars[$options->option_endpoint_checkout_shipping_data] = true;
    }
    if (isset($vars[$options->option_endpoint_checkout_products_data])) {
      $vars[$options->option_endpoint_checkout_products_data] = true;
    }
    if (isset($vars[$options->option_endpoint_checkout_license_pages])) {
      $vars[$options->option_endpoint_checkout_license_pages] = true;
    }
    if (isset($vars[$options->option_endpoint_checkout_confirm_order])) {
      $vars[$options->option_endpoint_checkout_confirm_order] = true;
    }
    if (isset($vars[$options->option_endpoint_checkout_finished_success])) {
      $vars[$options->option_endpoint_checkout_finished_success] = true;
    }
    if (isset($vars[$options->option_endpoint_checkout_finished_failure])) {
      $vars[$options->option_endpoint_checkout_finished_failure] = true;
    }
    if (isset($vars[$options->option_endpoint_checkout_product])) {
      $vars[$options->option_endpoint_checkout_product] = true;
    }
    if (isset($vars[$options->option_endpoint_checkout_all_products])) {
      $vars[$options->option_endpoint_checkout_all_products] = true;
    }
  }
  return $vars;
}

// Change some taxonomies position in admin menu.
function wde_taxanomies_menu_correction($parent_file) {
	global $current_screen;
	$taxonomy = $current_screen->taxonomy;
	if ($taxonomy == 'wde_discounts'
    || $taxonomy == 'wde_taxes'
    || $taxonomy == 'wde_countries') {
		$parent_file = 'wde_orders';
  }
	return $parent_file;
}
add_action('parent_file', 'wde_taxanomies_menu_correction');

// Custom post types.
$wde_custom_datas = array(
  'wde_products',
  'wde_manufacturers',
  'wde_categories',
  'wde_tags',
  'wde_parameters',
  'wde_discounts',
  'wde_taxes',
  'wde_labels',
  'wde_shippingmethods',
  'wde_countries',
  'wde_users',
);
// Include files for custom post types.
foreach ($wde_custom_datas as $wde_custom_data) {
  $wde_custom_data = substr($wde_custom_data, 4);
  if (file_exists(WD_E_DIR . DS . 'admin' . DS . 'views' . DS . $wde_custom_data . DS . $wde_custom_data . '.php')) {
    require_once WD_E_DIR . DS . 'admin' . DS . 'views' . DS . $wde_custom_data . DS . $wde_custom_data . '.php';
  }
}

// Create custom pages templates.
function wde_custom_post_type_template($single_template) {
  global $post;
  if (isset($post) && isset($post->post_type)) {
    if ($post->post_type == 'wde_manufacturers' || $post->post_type == 'wde_products') {
      if ($post->post_type == 'wde_products' && isset($_GET['task']) && (esc_html($_GET['task']) == 'ajax_rate_product' || esc_html($_GET['task']) == 'ajax_getcompareproductrow')) {
        wde_front_end(array('product_id' => get_the_ID(), 'type' => "products", 'layout' => "displayproduct"), TRUE);
        die();
      }
      $wde_view = substr($post->post_type, 4);
      $single_template = wde_get_template($wde_view);
    }
  }
  return $single_template;
}
add_filter('single_template', 'wde_custom_post_type_template');

// Create custom taxonomy templates.
function wde_custom_taxonomy_template($template) {
  $cur_page = get_queried_object();
  if (isset($cur_page) && isset($cur_page->taxonomy) && $cur_page->taxonomy == 'wde_categories') {
    $wde_view = substr($cur_page->taxonomy, 4);
    $template = wde_get_template($wde_view);
  }
  return $template;
}
add_filter('taxonomy_template', 'wde_custom_taxonomy_template');

function wde_get_template($view = '') {
  $templates_external_folder = str_replace(array('/', '\\'), DS, WP_PLUGIN_DIR . DS . WD_E_PLUGIN_NAME . '-templates');
  $templates_internal_folder = str_replace(array('/', '\\'), DS, WD_E_DIR . '/frontend/views/templates');
  if (is_dir($templates_external_folder)) {
    $templates_folder = $templates_external_folder;
  }
  else {
    $templates_folder = $templates_internal_folder;
  }
  if (!$view) {
    return $templates_folder;
  }
  else {
    // Get current theme name.
    $theme = wp_get_theme();
    $theme_name = $theme->template;
    // To get special view file.
    $theme_view = DS . $view . '_' . $theme_name . '.php';
    $view = DS . $view . '.php';
    if (file_exists($templates_external_folder . $theme_view)) {
      $templates_folder = $templates_external_folder;
      $view = $theme_view;
    }
    elseif (file_exists($templates_internal_folder . $theme_view)) {
      $templates_folder = $templates_internal_folder;
      $view = $theme_view;
    }
    elseif (file_exists($templates_external_folder . $view)) {
      $templates_folder = $templates_external_folder;
    }
    elseif (file_exists($templates_internal_folder . $view)) {
      $templates_folder = $templates_internal_folder;
    }
  }
  return $templates_folder . $view;
}

// add_action('after_switch_theme', 'wde_copy_templates');

function wde_copy_templates() {
  require_once WD_E_DIR . DS . 'framework' . DS . 'WDFHelper.php';
  WDFHelper::init();
  require_once WD_E_DIR . DS . 'admin' . DS . 'controllers' . DS . 'templates.php';
  new EcommercewdControllerTemplates();
  EcommercewdControllerTemplates::copy_templates(TRUE);
}

// Get task depend on endpoint.
function wde_endpoint($data, $key = 0) {
  if (!array_key_exists($key, $data)) {
    return WDFInput::get_task();
  }
  $task = get_query_var($data[$key]['option']);
  if (!$task) {
    $task = wde_endpoint($data, $key + 1);
  }
  else {
    $task = $data[$key]['task'];
  }
  return $task;
}

// Add meta tags to specified pages.
function wde_add_meta_tags() {
  $cur_page = get_queried_object();
  $is_wde_manufacturer = isset($cur_page) && isset($cur_page->post_type) && $cur_page->post_type == 'wde_manufacturers';
  $is_wde_product = isset($cur_page) && isset($cur_page->post_type) && $cur_page->post_type == 'wde_products';
  $is_wde_category = isset($cur_page) && isset($cur_page->taxonomy) && $cur_page->taxonomy == 'wde_categories';
  $meta_information = NULL;
  $fields = array(
    'meta_title',
    'meta_description',
    'meta_keyword',
  );
  if ($is_wde_manufacturer || $is_wde_product) {
    $meta_information = new stdClass();
    foreach ($fields as $field) {
      $meta_information->$field = esc_attr(get_post_meta($cur_page->ID, 'wde_' . $field, TRUE));
    }
  }
  else if ($is_wde_category) {
    $meta_information = new stdClass();
    $row_temp = get_option("wde_categories_" . $cur_page->term_id);
    foreach ($fields as $field) {
      $meta_information->$field = isset($row_temp[$field]) ? $row_temp[$field] : '';
    }
  }
  
  if ($meta_information != NULL) {
    if ($meta_information->meta_title) {
      ?>
    <meta property="title" content="<?php echo $meta_information->meta_title; ?>" />
      <?php
    }
    if ($meta_information->meta_keyword) {
      ?>
    <meta property="keywords" content="<?php echo $meta_information->meta_keyword; ?>" />
      <?php
    }
    if ($meta_information->meta_description) {
      ?>
    <meta property="description" content="<?php echo $meta_information->meta_description; ?>" />
      <?php
    }
  }
}
add_action('wp_head', 'wde_add_meta_tags', 5);

// Register Ecommerce page custom post type.
function wde_register_custom_post_types() {
  $options = WDFDB::get_options();
  $labels = array(
		'name'               => _x( 'Ecommerce Page', 'post type general name', 'wde' ),
		'singular_name'      => _x( 'Ecommerce Page', 'post type singular name', 'wde' ),
		'menu_name'          => _x( 'Ecommerce Pages', 'admin menu', 'wde' ),
		'name_admin_bar'     => _x( 'Ecommerce Page', 'add new on admin bar', 'wde' ),
		'add_new'            => _x( 'Add New', 'book', 'wde' ),
		'add_new_item'       => __( 'Add New Ecommerce Page', 'wde' ),
		'new_item'           => __( 'New Ecommerce Page', 'wde' ),
		'edit_item'          => __( 'Edit Ecommerce Page', 'wde' ),
		'view_item'          => __( 'View Ecommerce Page', 'wde' ),
		'all_items'          => __( 'All Ecommerce Pages', 'wde' ),
		'search_items'       => __( 'Search Ecommerce Pages', 'wde' ),
		'parent_item_colon'  => __( 'Parent Ecommerce Pages:', 'wde' ),
		'not_found'          => __( 'No Ecommerce Pages found.', 'wde' ),
		'not_found_in_trash' => __( 'No Ecommerce Pages found in Trash.', 'wde' )
	);
	$args = array(
		'labels'             => $labels,
    // 'menu_icon'          => WD_E_URL . '/images/toolbar_icons/manufacturers_20.png',
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_nav_menus'  => true,
		'query_var'          => isset($options->option_ecommerce_base) ? $options->option_ecommerce_base : TRUE,
		'rewrite'            => isset($options->option_ecommerce_base) ? array('slug' => $options->option_ecommerce_base) : TRUE,
		'has_archive'        => false,
		'hierarchical'       => false,
    'capabilities' => array(
      'create_posts' => FALSE,
      'edit_post' => 'edit_posts',
      'read_post' => 'edit_posts',
      'delete_posts' => FALSE,
    )
  );
  register_post_type('wde_page', $args);
}
add_action('init', 'wde_register_custom_post_types');

// Remove view link from action lists of Checkout and System pages.
function wde_page_action_list($actions, $post) {
	if (current_user_can('edit_posts')) {
    global $typenow;
    if ($typenow == 'wde_page' && isset($actions['view'])) {
      if (strpos($actions['view'], 'checkout') !== FALSE || strpos($actions['view'], 'system-pages') !== FALSE) {
        unset($actions['view']);
      }
    }
	}
	return $actions;
}
add_filter('post_row_actions', 'wde_page_action_list', 10, 2);

require_once WD_E_DIR . DS . 'ecommerce-wd-wizard.php';

function wde_topic() {
  $screen = get_current_screen();
  if (strpos($screen->id, 'wde_') === false) {
    return '';
  }
  $page = str_replace(array('toplevel_page_wde_', 'edit-wde_', 'admin_page_wde_', 'ecommerce-wd_page_wde_', 'wde_'), '', $screen->id);
  $prefix = 'wde';
  $is_free = TRUE;
  switch ($page) {
    case 'categories': {
      $user_guide_link = 'https://help.10web.io/hc/en-us/articles/360018136511-Adding-A-Product';
      break;
    }
    case 'countries': {
      $user_guide_link = 'https://help.10web.io/hc/en-us/sections/360002515211?_ga=2.123028104.2002648838.1555915144-926870922.1519900477';
      break;
    }
    case 'currencies': {
      $user_guide_link = 'https://help.10web.io/hc/en-us/sections/360002515211?_ga=2.123028104.2002648838.1555915144-926870922.1519900477';
      break;
    }
    case 'discounts': {
      $user_guide_link = 'https://help.10web.io/hc/en-us/articles/360018138371-Discounts-Tax-and-Shipping';
      break;
    }
    case 'labels': {
      $user_guide_link = 'https://help.10web.io/hc/en-us/articles/360018136511-Adding-A-Product';
      break;
    }
    case 'manufacturers': {
      $user_guide_link = 'https://help.10web.io/hc/en-us/sections/360002515211?_ga=2.123028104.2002648838.1555915144-926870922.1519900477';
      break;
    }
    case 'options': {
      $user_guide_link = 'https://help.10web.io/hc/en-us/articles/360018136451-Setup-and-Options';
      break;
    }
    case 'orders': {
      $user_guide_link = 'https://help.10web.io/hc/en-us/sections/360002515211?_ga=2.123028104.2002648838.1555915144-926870922.1519900477';
      break;
    }
    case 'parameters': {
      $user_guide_link = 'https://help.10web.io/hc/en-us/sections/360002515211?_ga=2.123028104.2002648838.1555915144-926870922.1519900477';
      break;
    }
    case 'payments': {
      $user_guide_link = 'https://help.10web.io/hc/en-us/sections/360002515211?_ga=2.123028104.2002648838.1555915144-926870922.1519900477';
      break;
    }
    case 'products': {
      $user_guide_link = 'https://help.10web.io/hc/en-us/articles/360018136511-Adding-A-Product';
      break;
    }
    case 'ratings': {
      $user_guide_link = 'https://help.10web.io/hc/en-us/articles/360018138491-Ratings-and-Tracking';
      break;
    }
    case 'reports': {
      $user_guide_link = 'https://help.10web.io/hc/en-us/sections/360002515211?_ga=2.123028104.2002648838.1555915144-926870922.1519900477';
      break;
    }
    case 'shippingmethods': {
      $user_guide_link = 'https://help.10web.io/hc/en-us/articles/360018138371-Discounts-Tax-and-Shipping';
      break;
    }
    case 'tag': {
      $user_guide_link = 'https://help.10web.io/hc/en-us/sections/360002515211?_ga=2.123028104.2002648838.1555915144-926870922.1519900477';
      break;
    }
    case 'taxes': {
      $user_guide_link = 'https://help.10web.io/hc/en-us/articles/360018138371-Discounts-Tax-and-Shipping';
      break;
    }
    case 'themes': {
      $user_guide_link = 'https://help.10web.io/hc/en-us/articles/360018138411-Themes-and-Publishing';
      break;
    }
    default: {
      return '';
      break;
    }
  }
  $show_guide_link = TRUE;
  $support_forum_link = 'https://wordpress.org/support/plugin/ecommerce-wd';
  $premium_link = 'https://10web.io/plugins/wordpress-ecommerce/?utm_source=ecommerce&utm_medium=free_plugin';
  wp_enqueue_style($prefix . '_roboto');
  wp_enqueue_style($prefix . '_pricing');
  ob_start();
  ?>
  <div class="wrap">
    <h1 class="wd-head-notice">&nbsp;</h1>
    <div class="wd-topbar-container">
      <?php
      if ($is_free) {
        ?>
        <div class="wd-topbar wd-topbar-content">
          <div class="wd-topbar-content-container">
            <div class="wd-topbar-content-title">
              <?php _e('Ecommerce by 10Web Premium', $prefix); ?>
            </div>
            <div class="wd-topbar-content-body">
              <?php _e('Enable PayPal Express Checkout for your shop, showcase it with additional Premium layouts and much more.', $prefix); ?>
            </div>
          </div>
          <div class="wd-topbar-content-button-container">
            <a href="<?php echo $premium_link; ?>" target="_blank" class="wd-topbar-upgrade-button"><?php _e( 'Upgrade', $prefix ); ?></a>
          </div>
        </div>
        <?php
      }
      ?>
      <div class="wd-topbar wd-topbar-links">
        <div class="wd-topbar-links-container">
          <?php
          if ( $show_guide_link ) {
            ?>
            <a href="<?php echo $user_guide_link; ?>" target="_blank">
              <div class="wd-topbar-links-item">
                <?php _e('User guide', $prefix); ?>
              </div>
            </a>
            <?php
          }
          if ($is_free) {
            if ( $show_guide_link ) {
              ?>
              <span class="wd-topbar-separator"></span>
              <?php
            }
            ?>
            <a href="<?php echo $support_forum_link; ?>" target="_blank">
              <div class="wd-topbar-links-item">
                <?php _e('Support Forum', $prefix); ?>
              </div>
            </a>
            <?php
          }
          ?>
        </div>
      </div>
    </div>
  </div>
  <?php
  echo ob_get_clean();
}
add_action('admin_notices', 'wde_topic', 11);

/*
Whitelist Ecommerce query variables for Yoast SEO plugin.
*/
add_filter('wpseo_whitelist_permalink_vars', 'wde_add_query_vars_seo');
function wde_add_query_vars_seo($vars) {
  $vars[] = 'task';
  $vars[] = 'ses_id';
  $vars[] = 'local_task';
  $vars[] = 'controller';
  $vars[] = 'order_id';
  $vars[] = 'error_msg';
  require_once WD_E_DIR . DS . 'framework' . DS . 'WDFDb.php';
  $options = WDFDb::get_options();
  if ($options) {
    $vars[] = $options->option_endpoint_compare_products;
    $vars[] = $options->option_endpoint_edit_user_account;
    $vars[] = $options->option_endpoint_checkout_shipping_data;
    $vars[] = $options->option_endpoint_checkout_products_data;
    $vars[] = $options->option_endpoint_checkout_license_pages;
    $vars[] = $options->option_endpoint_checkout_confirm_order;
    $vars[] = $options->option_endpoint_checkout_finished_success;
    $vars[] = $options->option_endpoint_checkout_finished_failure;
    $vars[] = $options->option_endpoint_checkout_product;
    $vars[] = $options->option_endpoint_checkout_all_products;
    $vars[] = $options->option_endpoint_orders_displayorder;
    $vars[] = $options->option_endpoint_orders_printorder;
    $vars[] = $options->option_endpoint_systempages_errnum;
  }
  return $vars;
}
