<?php
defined('ABSPATH') || die('Access Denied');

wp_enqueue_script('wde_utils');
wp_enqueue_script('wde_' . $this->_layout);

$options = $this->options;
$shipping_destination = isset($options->shipping_destination) ? $options->shipping_destination : 'shipping';
$has_shipping = $this->final_checkout_data['has_shipping'];
$billing_form_fields = $this->form_fields["billing_fields_list"];
$shipping_form_fields = $this->form_fields["shipping_fields_list"];
$pager_data = $this->pager_data;
?>
<div class="container">
  <div class="row">
    <div class="col-sm-12">
      <div class="wd_shop_panel_user_data panel panel-default">
        <div class="panel-body">
            <div class="row">
              <div class="col-sm-10 col-sm-offset-1">
                <form name="wd_shop_main_form" class="form-horizontal" role="form" action="" method="POST">
                  <div id="wde_billing_info">
                    <h3 class="wd_shop_header"><?php echo $shipping_destination == 'merged' ? __('Billing and Shipping data', 'wde') : __('Billing data', 'wde'); ?></h3>
                    <?php
                    if (is_array($billing_form_fields)) {
                      foreach ($billing_form_fields as $form_field_name => $form_field) {
                        if ($form_field['required'] > 0) {
                          ?>
                    <div class="form-group">
                      <label for="<?php echo $form_field['id']; ?>" class="col-sm-4 control-label">
                        <?php echo $form_field['label']; ?>:<?php if ($form_field['required'] == 2) { ?><span class="wd_star">*</span><?php } ?>
                      </label>
                      <div class="col-sm-8">
                          <?php
                          $class_required = $form_field['required'] == 2 ? 'wd_shop_required_field' : '';
                          switch ($form_field['type']) {
                            case 'select':
                              echo WDFHtml::wd_select($form_field['id'], $form_field['options'], 'id', 'name', $form_field['value'], 'class="form-control ' . $class_required . '"');
                              break;
                            default:
                              ?>
                        <input type="<?php echo $form_field['type']; ?>"
                               name="<?php echo $form_field['id']; ?>"
                               value="<?php echo $form_field['value']; ?>"
                               id="<?php echo $form_field['id']; ?>"
                               class="form-control <?php echo $class_required; ?>"
                               placeholder="<?php echo $form_field['label']; ?>" />
                              <?php
                              break;
                          }
                          ?>
                      </div>
                    </div>
                          <?php
                        }
                      }
                    }
                    ?>
                  </div>
                  <div class="<?php echo $has_shipping ? '' : 'wd_hidden'; ?>">
                  <?php
                    if ( $shipping_destination == 'shipping' ) {
                      ?>
                      <h3 class="wd_shop_header">
                        <?php _e( 'Shipping data', 'wde' ); ?>
                      </h3>
                      <?php
                    } elseif ( $shipping_destination == 'billing' ) {
                      ?>
                      <b>
                        <label class="wd_shop_header_sm"
                               for="wde_ship_to"><?php _e( 'Ship to a different address?', 'wde' ); ?>
                          <input type="checkbox" value="1" onclick="wde_ship_to_diff(this, 'wde_shipping_info');"
                                 id="wde_ship_to"/>
                        </label>
                      </b>
                      <?php
                    }
                    ?>
                    <div id="wde_shipping_info"
                         class="<?php echo $shipping_destination != 'shipping' ? 'wd_hidden' : ''; ?>">
                      <input type="checkbox" value="1" onclick="wd_ShopCopyBillingInformation(event, this);"
                             id="wd_shop_copy_billing_info"/>
                      <label for="wd_shop_copy_billing_info"><?php _e( 'Copy billing info', 'wde' ); ?></label>
                      <?php
                      if ( is_array( $shipping_form_fields ) ) {
                        foreach ( $shipping_form_fields as $form_field_name => $form_field ) {
                          if ( $form_field[ 'required' ] > 0 ) {
                            ?>
                            <div class="form-group">
                              <label for="<?php echo $form_field[ 'id' ]; ?>" class="col-sm-4 control-label">
                                <?php echo $form_field[ 'label' ]; ?>:<?php if ( $form_field[ 'required' ] == 2 ) { ?>
                                  <span class="wd_star">*</span><?php } ?>
                              </label>
                              <div class="col-sm-8">
                                <?php
                                $class_required = $form_field[ 'required' ] == 2 ? 'wd_shop_required_field' : '';
                                switch ( $form_field[ 'type' ] ) {
                                  case 'select':
                                    echo WDFHtml::wd_select( $form_field[ 'id' ], $form_field[ 'options' ], 'id', 'name', $form_field[ 'value' ], 'class="form-control ' . $class_required . '"' );
                                    break;
                                  default:
                                    ?>
                                    <input type="<?php echo $form_field[ 'type' ]; ?>"
                                           name="<?php echo $form_field[ 'id' ]; ?>"
                                           value="<?php echo $form_field[ 'value' ]; ?>"
                                           id="<?php echo $form_field[ 'id' ]; ?>"
                                           class="form-control <?php echo $class_required; ?>"
                                           placeholder="<?php echo $form_field[ 'label' ]; ?>"/>
                                    <?php
                                    break;
                                }
                                ?>
                              </div>
                            </div>
                            <?php
                          }
                        }
                      }
                      ?>
                      <input type="hidden" name="data" value="shipping_data"/>
                    </div>
                  </div>
                </form>
              </div>
            </div>
            <div class="wd_shop_checkout_alert_incorrect_data alert alert-danger hidden">
              <p><?php _e('This field is required', 'wde'); ?></p>
            </div>
        </div>
      </div>
      <div>
        <ul class="pager">
          <?php
          $btn_cancel_checkout_data = $pager_data['btn_cancel_checkout_data'];
          ?>
          <li class="previous">
            <a href="<?php echo $btn_cancel_checkout_data['url']; ?>">
              <span><?php _e('Cancel checkout', 'wde'); ?></span>
            </a>
          </li>
          <?php
          if (isset($pager_data['btn_prev_page_data'])) {
            $btn_prev_page_data = $pager_data['btn_prev_page_data'];
            ?>
            <li class="previous">
              <a href="<?php echo $btn_prev_page_data['action']; ?>"
                 onclick="onWDShop_pagerBtnClick(event, this); return false;">
                <span class="glyphicon glyphicon-arrow-left"></span>&nbsp;
                <span><?php echo $btn_prev_page_data['text']; ?></span>
              </a>
            </li>
            <?php
          }
          if (isset($pager_data['btn_next_page_data'])) {
            $btn_next_page_data = $pager_data['btn_next_page_data'];
            ?>
            <li class="next">
              <a href="<?php echo $btn_next_page_data['action']; ?>"
                 onclick="onWDShop_pagerBtnClick(event, this); return false;">
                <span><?php echo $btn_next_page_data['text']; ?></span>&nbsp;
                <span class="glyphicon glyphicon-arrow-right"></span>
              </a>
            </li>
            <?php
          }
          ?>
        </ul>
      </div>
    </div>
  </div>
</div>
