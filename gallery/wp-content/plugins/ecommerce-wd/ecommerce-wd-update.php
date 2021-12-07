<?php

function wde_update($version) {
  global $wpdb;
  $wpdb->query("CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "ecommercewd_tax_rates` (
    `id`		 		INT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `country` 	 	INT(20) NOT NULL,
    `state`			VARCHAR(200) NOT NULL,
    `zipcode`	 		VARCHAR(200) NOT NULL,
    `city` 	 		VARCHAR(200) NOT NULL,
    `rate` 			DECIMAL(16,2) NOT NULL,
    `tax_name` 		VARCHAR(200) NOT NULL,
    `priority` 		INT(20) NOT NULL,
    `compound` 		INT(20) NOT NULL,
    `shipping_rate` 	DECIMAL(16,2) NOT NULL,
    `ordering` 		INT(20) NOT NULL,
    `tax_id` 			INT(20) NOT NULL,
    PRIMARY KEY (`id`)
    )
    ENGINE = MyISAM
    DEFAULT CHARSET = utf8
    AUTO_INCREMENT = 1;");
  if (version_compare($version, '1.1.0') == -1) {
    $wpdb->query('INSERT INTO `' . $wpdb->prefix . 'ecommercewd_options` (`id`, `name`, `value`, `default_value`) VALUES
      ("", "enable_tax", 1, 1),
      ("", "round_tax_at_subtotal", 0, 0),
      ("", "price_entered_with_tax", 0, 0),
      ("", "option_include_tax_in_checkout_price", 0, 0),
      ("", "tax_based_on", "shipping_address", "shipping_address"),
      ("", "price_display_suffix", "", ""),
      ("", "base_location", "", ""),
      ("", "tax_total_display", "single", "single")');
    $wpdb->query("ALTER TABLE " . $wpdb->prefix . "ecommercewd_orderproducts ADD `discount_rate` int(3) DEFAULT NULL");
    $wpdb->query("ALTER TABLE " . $wpdb->prefix . "ecommercewd_orderproducts ADD `discount` decimal(16,2) DEFAULT NULL");
    $wpdb->query("ALTER TABLE " . $wpdb->prefix . "ecommercewd_orderproducts ADD `shipping_method_type` varchar(10) DEFAULT NULL");
    $wpdb->query("ALTER TABLE " . $wpdb->prefix . "ecommercewd_orderproducts ADD `tax_info` longtext DEFAULT ''");
  }
  if (version_compare($version, '1.1.3') == -1) {
    $paypal_express_options = $wpdb->get_var("SELECT `options` FROM `" . $wpdb->prefix . "ecommercewd_payments` WHERE `base_name`='paypalexpress'");
    if ($paypal_express_options) {
      $paypal_express_options = WDFJson::decode($paypal_express_options);
      $paypal_express_options->skip_form = 0;
      $paypal_express_options->skip_overview = 0;
      $paypal_express_options->show_button = 0;
      $paypal_express_options->button_style = 'paypal';
      $paypal_express_options->button_text = 'Paypal express checkout';
      $paypal_express_options->button_image = '';
      $paypal_express_options = WDFJson::encode($paypal_express_options);
      $wpdb->query("UPDATE `" . $wpdb->prefix . "ecommercewd_payments` SET `options`='" . $paypal_express_options . "' WHERE `base_name`='paypalexpress'");
    }
  }
  if (version_compare($version, '1.1.4') == -1) {
    $widget_params = array(
      'widget_view_show_image' => 1,
      'widget_view_show_label' => 1,
      'widget_view_show_name' => 1,
      'widget_view_show_rating' => 1,
      'widget_view_show_category' => 1,
      'widget_view_show_manufacturer' => 1,
      'widget_view_show_model' => 1,
      'widget_view_show_price' => 1,
      'widget_view_show_market_price' => 1,
      'widget_view_show_button_compare' => 1,
      'widget_view_show_button_write_review' => 1,
      'widget_view_show_button_buy_now' => 1,
      'widget_view_show_button_add_to_cart' => 1,
      'widget_view_show_description' => 1,
      'widget_view_show_social_buttons' => 1,
      'widget_view_show_parameters' => 1,
      'widget_view_show_shipping_info' => 1,
      'widget_view_show_reviews' => 1,
      'widget_view_show_related_products' => 1,
      'widget_view_show_button_quick_view' => 1,
    );
    $themes = $wpdb->get_results( 'SELECT `id`,`data` FROM ' . $wpdb->prefix . 'ecommercewd_themes', ARRAY_A );
    foreach ( $themes as  $theme ) {
      $data = (array) json_decode($theme['data']);
      $id = $theme['id'];
      $data = array_merge($data, $widget_params);
      $wpdb->update($wpdb->prefix . 'ecommercewd_themes', array('data' => json_encode($data)), array('id' => $id));
    }
  }
  if (version_compare($version, '1.2.0') == -1) {
    $wpdb->query('INSERT INTO `' . $wpdb->prefix . 'ecommercewd_options` (`id`, `name`, `value`, `default_value`) VALUES
      ("", "shipping_calculator", 0, 0),
      ("", "shipping_in_cart", 0, 0),
      ("", "shipping_destination", "shipping", "shipping")');

    $wpdb->query("CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "ecommercewd_shippingzones` (
      `id`                          INT(16) UNSIGNED NOT NULL AUTO_INCREMENT,
      `name`                        VARCHAR(256) NOT NULL,
      `country_ids`                 VARCHAR(256) NOT NULL,
      `postcode`                    VARCHAR(256) NOT NULL,
      `price`                       VARCHAR(6) NOT NULL,
      `free_shipping`               TINYINT(1) NOT NULL DEFAULT 0,
      `free_shipping_start_price`   VARCHAR(6) NOT NULL,
      `shipping_type`               VARCHAR(256) NOT NULL DEFAULT 'per_bundle',
      `shipping_classes_price`      LONGTEXT NOT NULL DEFAULT '',
      `taxable`                     TINYINT(1) NOT NULL DEFAULT 0,
      `ordering`                    INT(6) NOT NULL,
      `enabled`                     TINYINT(1) NOT NULL DEFAULT 1,
      PRIMARY KEY (`id`)
    ) DEFAULT CHARSET=utf8;");

    $wpdb->query("CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "ecommercewd_shippingclasses` (
      `id`                          INT(16) UNSIGNED NOT NULL AUTO_INCREMENT,
      `name`                        VARCHAR(256) NOT NULL,
      `slug`                        VARCHAR(256) NOT NULL,
      `description`                 VARCHAR(256) NOT NULL,
      PRIMARY KEY (`id`)
    ) DEFAULT CHARSET=utf8;");

    $products = get_posts( array('posts_per_page' => -1, 'post_type' => 'wde_products') );
    $products_shippings = array();
    foreach ( $products as $product ) {
      $product_shipping_methods = get_the_terms( $product->ID, 'wde_shippingmethods' );
      if ( is_wp_error( $product_shipping_methods ) || empty( $product_shipping_methods ) ) {
        continue;
      }
      foreach ( $product_shipping_methods as $product_shipping_method ) {
        if ( $product_shipping_method && isset( $product_shipping_method->term_id ) ) {
          $name = isset( $product_shipping_method->name ) ? $product_shipping_method->name : '';
          $slug = isset( $product_shipping_method->slug ) ? $product_shipping_method->slug : $name;
          $slug = WDFHelper::get_unique_slug( $slug, 0, 'ecommercewd_shippingclasses' );
          $description = isset( $product_shipping_method->description ) ? $product_shipping_method->description : '';
          $shippingclasses_data = array(
            'id' => $product_shipping_method->term_id,
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
          );
          if (!in_array($product_shipping_method->term_id, $products_shippings)) {
            $saved = $wpdb->insert( $wpdb->prefix . 'ecommercewd_shippingclasses', $shippingclasses_data );
            if ( $saved ) {
              $class_id = $wpdb->insert_id;
              update_post_meta( $product->ID, 'wde_shipping_class_ids', $class_id );
              $products_shippings[$class_id] = $product_shipping_method->term_id;
            }
          }
        }
      }
    }
    $shippingmethods = get_terms( 'wde_shippingmethods' );
    if ( !is_wp_error( $shippingmethods ) && !empty( $shippingmethods ) ) {
      foreach ( $shippingmethods as $shippingmethod ) {
        if ( !isset( $shippingmethod->term_id ) ) {
          continue;
        }
        $term_meta = get_option( 'wde_shippingmethods_' . $shippingmethod->term_id );
        if ( !isset( $term_meta ) ) {
          continue;
        }
        $name = isset( $shippingmethod->name ) ? $shippingmethod->name : '';
        $country_ids = isset( $term_meta['country_ids'] ) ? $term_meta['country_ids'] : '';
        $postcode = '';
        $price = '';
        $free_shipping = isset( $term_meta['free_shipping'] ) ? $term_meta['free_shipping'] : 0;
        $free_shipping_start_price = isset($term_meta['free_shipping_start_price']) ? WDFText::wde_number_format( $term_meta['free_shipping_start_price'], 2 ) : '';
        $shipping_type = isset( $term_meta['shipping_type'] ) ? $term_meta['shipping_type'] : 'per_bundle';
        $class_price = isset( $term_meta['price'] ) ? WDFText::wde_number_format($term_meta['price'], 2) : '';
        $shipping_classes_price = '';
        if ( $class_price != WDFText::wde_number_format(0, 2) ) {
          $class_id = array_search( $shippingmethod->term_id, $products_shippings );
          if ( $class_id ) {
            $shipping_classes_price = WDFJson::encode( array( $class_id => $class_price ) );
          }
        }
        $taxable = ( isset( $term_meta['tax_id'] ) && $term_meta['tax_id'] ) ? 1 : 0;

        $shippingzones_data = array(
          'name' => sprintf(__('Shipping zone for %s', 'wde'), $name),
          'country_ids' => $country_ids,
          'postcode' => $postcode,
          'price' => $price,
          'free_shipping' => $free_shipping,
          'free_shipping_start_price' => $free_shipping_start_price,
          'shipping_type' => $shipping_type,
          'shipping_classes_price' => $shipping_classes_price,
          'taxable' => $taxable,
          'ordering' => 1,
          'enabled' => 1,
        );
        $wpdb->insert( $wpdb->prefix . 'ecommercewd_shippingzones', $shippingzones_data );
      }
    }

    $shippingzones_data = array(
      'name' =>  __('Rest of the World', 'wde'),
      'country_ids' => "-1",
      'postcode' => "-1",
      'price' => "",
      'free_shipping' => 0,
      'free_shipping_start_price' => "",
      'shipping_type' => "per_bundle",
      'shipping_classes_price' => "",
      'taxable' => 0,
      'ordering' => 0,
      'enabled' => 1,
    );
    $wpdb->insert( $wpdb->prefix . 'ecommercewd_shippingzones', $shippingzones_data );

    $shippingclasses_data = array(
      'name' => __('Default shipping', 'wde'),
      'slug' => 'default-shipping',
      'description' => '',
    );
    $wpdb->insert( $wpdb->prefix . 'ecommercewd_shippingclasses', $shippingclasses_data );
  }
  if (version_compare($version, '1.2.2') == -1) {
    $wpdb->query("ALTER TABLE  `" . $wpdb->prefix . "ecommercewd_shippingzones` CHANGE `price` `price` VARCHAR(256) NOT NULL");
    $wpdb->query("ALTER TABLE  `" . $wpdb->prefix . "ecommercewd_shippingzones` CHANGE `free_shipping_start_price` `free_shipping_start_price` VARCHAR(256) NOT NULL");
  }
  return;
}
