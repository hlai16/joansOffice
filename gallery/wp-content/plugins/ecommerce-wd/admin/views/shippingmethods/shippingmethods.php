<?php
// Create shippingmethods taxonomy to allow update from 1.1.8.
function wde_create_shippingmethod() {
  register_taxonomy('wde_shippingmethods', 'wde_products', array('public' => FALSE));
}
add_action('init', 'wde_create_shippingmethod');
