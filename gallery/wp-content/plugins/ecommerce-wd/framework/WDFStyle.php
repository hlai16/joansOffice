<?php
defined('ABSPATH') || die('Access Denied');

/**
 * WDFStyle class.
 *
 * @since 1.2.1
 */
class WDFStyle {
  private $prefix;
  private $theme;
  private $dir;

  function __construct() {
    $this->prefix = '';
    for ($i = 1; $i <= 12; $i++) {
      $this->prefix .= '#wd_shop_container_' . $i . ' ';
    }
    $this->prefix .= '#wd_shop_container';
    $this->theme = $this->get_theme_row();
    $this->dir = WD_E_DIR . '/frontend/css/theme.css';
  }

  /**
   * Get default theme.
   *
   * @return theme parameters.
   */
  private function get_theme_row() {
    $theme = WDFDb::get_row('themes', '`default`=1');
    $theme = (object) array_merge((array) $theme, (array) json_decode($theme->data));
    unset($theme->data);
    return $theme;
  }

  /**
   * Create css file if not exist.
   *
   */
  public function check() {
    if (!file_exists($this->dir)) {
      $this->create_file();
    }
  }

  /**
   * Create css file.
   *
   * @param    array $views css to add file.
   * 
   * @return TRUE on success otherwise FALSE.
   */
  public function create_file($views = FALSE) {
    $handle = @fopen($this->dir, 'w');
    if ($handle === FALSE) {
      return FALSE;
    }
    if (!$views) {
      $views = array('general', 'headings', 'input', 'buttons', 'divider', 'navbar', 'modal', 'paneluserdata', 'panelproduct', 'well', 'label', 'alert', 'breadcrumb', 'pills', 'tab', 'pagination', 'pager', 'single_item', 'multiple_items');
    }
    $css = '';
    foreach ($views as $view) {
      $css .= $this->$view();
    }
    if (@fwrite($handle, $css) === FALSE) {
      return FALSE;
    }
    @fclose($handle);
    global $wde_theme_version;
    update_option('wde_theme_version', date('YmdHsi'));
    return TRUE;
  }

  private function general() {
    $prefix = $this->prefix;
    $theme = $this->theme;

    $rounded_corners = $theme->rounded_corners;
    $content_main_color = $theme->content_main_color;
    $main_font_size = $theme->main_font_size ? 'font-size: ' . $theme->main_font_size . ';' : '';
    $main_font_family = $theme->main_font_family ? $theme->main_font_family : 'inherit';
    $main_font_weight = $theme->main_font_weight ? $theme->main_font_weight : 'inherit';

    ob_start();
    if ($rounded_corners == 0) {
      ?>
    <?php echo $prefix; ?> * {
      -moz-border-radius: 0 !important;
      -webkit-border-radius: 0 !important;
      -khtml-border-radius: 0 !important;
      border-radius: 0 !important;
    }
      <?php
    }
    ?>
    <?php echo $prefix; ?>,
    <?php echo $prefix; ?> h1,
    <?php echo $prefix; ?> h2,
    <?php echo $prefix; ?> h3,
    <?php echo $prefix; ?> h4,
    <?php echo $prefix; ?> h5,
    <?php echo $prefix; ?> h6 {
      <?php echo $main_font_size; ?>
      font-family: <?php echo $main_font_family; ?>;
      font-weight: <?php echo $main_font_weight; ?>;
      color: <?php echo $content_main_color; ?>;
    }
    <?php

    return ob_get_clean();
  }

  private function headings() {
    $prefix = $this->prefix;
    $theme = $this->theme;

    $header_font_size = $theme->header_font_size ? 'font-size: ' . $theme->header_font_size . ';' : '';
    $header_font_family = $theme->header_font_family ? $theme->header_font_family : 'inherit';
    $header_font_weight = $theme->header_font_weight ? $theme->header_font_weight : 'inherit';

    $header_content_color = $theme->header_content_color;
    $header_border_color = WDFColorUtils::color_to_rgba($theme->header_content_color);
    $header_border_color['a'] = 0.15;
    $header_border_color = WDFColorUtils::color_to_rgba($header_border_color, TRUE);

    $subtext_font_size = $theme->subtext_font_size ? 'font-size: ' . $theme->subtext_font_size . ';' : '';
    $subtext_font_family = $theme->subtext_font_family ? $theme->subtext_font_family : 'inherit';
    $subtext_font_weight = $theme->subtext_font_weight ? $theme->subtext_font_weight : 'inherit';
    $subtext_content_color = $theme->subtext_content_color;

    ob_start();
    ?>
    <?php echo $prefix; ?>
    .wd_shop_header {
      <?php echo $header_font_size; ?>
      font-family: <?php echo $header_font_family; ?>;
      font-weight: <?php echo $header_font_weight; ?>;
      color: <?php echo $header_content_color; ?>;
      border-bottom-color: <?php echo $header_border_color; ?>;
    }
    <?php echo $prefix; ?>
    .wd_shop_header_sm {
      font-family: <?php echo $header_font_family; ?>;
      font-weight: <?php echo $header_font_weight; ?>;
      color: <?php echo $header_content_color; ?>;
      border-bottom-color: <?php echo $header_border_color; ?>;
    }
    <?php echo $prefix; ?>
    small {
      <?php echo $subtext_font_size; ?>
      font-family: <?php echo $subtext_font_family; ?>;
      font-weight: <?php echo $subtext_font_weight; ?>;
      color: <?php echo $subtext_content_color; ?>;
    }
    <?php

    return ob_get_clean();
  }

  private function input() {
    $prefix = $this->prefix;
    $theme = $this->theme;

    $input_font_size = $theme->input_font_size ? 'font-size: ' . $theme->input_font_size . ';' : '';
    $input_font_family = $theme->input_font_family ? $theme->input_font_family : 'inherit';
    $input_font_weight = $theme->input_font_weight ? $theme->input_font_weight : 'inherit';
    $input_content_color = $theme->input_content_color;
    $input_placeholder_color = WDFColorUtils::adjust_brightness($input_content_color, 25);
    $input_bg_color = $theme->input_bg_color;
    $input_border_color = $theme->input_border_color;
    $input_focus_border_color = WDFColorUtils::adjust_brightness($theme->input_focus_border_color, 10);
    $input_focus_border_color_rgba = WDFColorUtils::color_to_rgba($input_focus_border_color);
    $input_focus_border_color_rgba['a'] = 0.6;
    $input_focus_border_color_rgba = WDFColorUtils::color_to_rgba($input_focus_border_color, true);

    $addon_content_color = WDFColorUtils::adjust_brightness($input_border_color, -20);
    $addon_bg_color = WDFColorUtils::adjust_brightness($input_border_color, 35);
    $addon_border_color = $input_border_color;

    $input_has_error_content_color = $theme->input_has_error_content_color;
    $input_has_error_border_color = $input_has_error_content_color;
    $input_has_error_focus_border_color = WDFColorUtils::color_to_rgba(WDFColorUtils::adjust_brightness($input_has_error_content_color, -15), true);
    $input_has_error_focus_shadow_color = WDFColorUtils::color_to_rgba(WDFColorUtils::adjust_saturation(WDFColorUtils::adjust_brightness($input_has_error_content_color, 10), -30), true);

    $has_error_addon_content_color = WDFColorUtils::adjust_brightness($input_has_error_border_color, -20);
    $has_error_addon_bg_color = WDFColorUtils::adjust_brightness($input_has_error_border_color, 35);
    $has_error_addon_border_color = $input_has_error_border_color;

    ob_start();
    ?>
    <?php echo $prefix; ?>
    .form-control::-webkit-input-placeholder {
      color: <?php echo $input_placeholder_color; ?>;
    }
    <?php echo $prefix; ?>
    .form-control {
      <?php echo $input_font_size; ?>
      font-family: <?php echo $input_font_family; ?>;
      font-weight: <?php echo $input_font_weight; ?>;
      color: <?php echo $input_content_color ?>;
      background-color: <?php echo $input_bg_color; ?>;
      border-color: <?php echo $input_border_color; ?>;
    }
    <?php echo $prefix; ?>
    .form-control:focus {
      border-color: <?php echo $input_focus_border_color; ?>;
      -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075), 0 0 8px <?php echo $input_focus_border_color_rgba; ?>;
      box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075), 0 0 8px <?php echo $input_focus_border_color_rgba; ?>;
    }
    <?php echo $prefix; ?>
    .input-group-addon {
      color: <?php echo $addon_content_color; ?>;
      background-color: <?php echo $addon_bg_color; ?>;
      border-color: <?php echo $addon_border_color; ?>;
    }
    /* has error */
    <?php echo $prefix; ?>
    .has-error .control-label {
      <?php echo $input_font_size; ?>
      font-family: <?php echo $input_font_family; ?>;
      font-weight: <?php echo $input_font_weight; ?>;
      color: <?php echo $input_has_error_content_color; ?>;
    }
    <?php echo $prefix; ?>
    .has-error .form-control {
      border-color: <?php echo $input_has_error_focus_border_color; ?>;
      -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
      box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
    }
    <?php echo $prefix; ?>
    .has-error .form-control:focus {
      <?php echo $input_font_size; ?>
      font-family: <?php echo $input_font_family; ?>;
      font-weight: <?php echo $input_font_weight; ?>;
      border-color: <?php echo $input_has_error_content_color; ?>;
      -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075), 0 0 6px <?php echo $input_has_error_focus_shadow_color; ?>;
      box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075), 0 0 6px <?php echo $input_has_error_focus_shadow_color; ?>;
    }
    <?php echo $prefix; ?>
    .has-error .input-group-addon {
      color: <?php echo $has_error_addon_content_color; ?>;
      background-color: <?php echo $has_error_addon_bg_color; ?>;
      border-color: <?php echo $has_error_addon_border_color; ?>;
    }
    <?php

    return ob_get_clean();
  }

  private function buttons() {
    $prefix = $this->prefix;
    $theme = $this->theme;

    // button info
    $button_font_size = $theme->button_font_size ? 'font-size: ' . $theme->button_font_size . ';' : '';
    $button_font_family = $theme->button_font_family ? $theme->button_font_family : 'inherit';
    $button_font_weight = $theme->button_font_weight ? $theme->button_font_weight : 'inherit';
    $button_default_content_color = $theme->button_default_content_color;
    $button_default_bg_color = $theme->button_default_bg_color;
    $button_default_border_color = $theme->button_default_border_color;
    $button_default_hover_content_color = $theme->button_default_content_color;
    $button_default_hover_bg_color = WDFColorUtils::adjust_brightness($theme->button_default_bg_color, -8);
    $button_default_hover_border_color = WDFColorUtils::adjust_brightness($theme->button_default_border_color, -12);
    $button_default_disabled_bg_color = $theme->button_default_bg_color;
    $button_default_disabled_border_color = $theme->button_default_border_color;

    // button primary
    $button_primary_content_color = $theme->button_primary_content_color;
    $button_primary_bg_color = $theme->button_primary_bg_color;
    $button_primary_border_color = $theme->button_primary_border_color;
    $button_primary_hover_content_color = $theme->button_primary_content_color;
    $button_primary_hover_bg_color = WDFColorUtils::adjust_brightness($theme->button_primary_bg_color, -8);
    $button_primary_hover_border_color = WDFColorUtils::adjust_brightness($theme->button_primary_border_color, -12);
    $button_primary_disabled_bg_color = $theme->button_primary_bg_color;
    $button_primary_disabled_border_color = $theme->button_primary_border_color;

    // button success
    $button_success_content_color = $theme->button_success_content_color;
    $button_success_bg_color = $theme->button_success_bg_color;
    $button_success_border_color = $theme->button_success_border_color;
    $button_success_hover_content_color = $theme->button_success_content_color;
    $button_success_hover_bg_color = WDFColorUtils::adjust_brightness($theme->button_success_bg_color, -8);
    $button_success_hover_border_color = WDFColorUtils::adjust_brightness($theme->button_success_border_color, -12);
    $button_success_disabled_bg_color = $theme->button_success_bg_color;
    $button_success_disabled_border_color = $theme->button_success_border_color;

    // button info
    $button_info_content_color = $theme->button_info_content_color;
    $button_info_bg_color = $theme->button_info_bg_color;
    $button_info_border_color = $theme->button_info_border_color;
    $button_info_hover_content_color = $theme->button_info_content_color;
    $button_info_hover_bg_color = WDFColorUtils::adjust_brightness($theme->button_info_bg_color, -8);
    $button_info_hover_border_color = WDFColorUtils::adjust_brightness($theme->button_info_border_color, -12);
    $button_info_disabled_bg_color = $theme->button_info_bg_color;
    $button_info_disabled_border_color = $theme->button_info_border_color;

    // button warning
    $button_warning_content_color = $theme->button_warning_content_color;
    $button_warning_bg_color = $theme->button_warning_bg_color;
    $button_warning_border_color = $theme->button_warning_border_color;
    $button_warning_hover_content_color = $theme->button_warning_content_color;
    $button_warning_hover_bg_color = WDFColorUtils::adjust_brightness($theme->button_warning_bg_color, -8);
    $button_warning_hover_border_color = WDFColorUtils::adjust_brightness($theme->button_warning_border_color, -12);
    $button_warning_disabled_bg_color = $theme->button_warning_bg_color;
    $button_warning_disabled_border_color = $theme->button_warning_border_color;

    // button danger
    $button_danger_content_color = $theme->button_danger_content_color;
    $button_danger_bg_color = $theme->button_danger_bg_color;
    $button_danger_border_color = $theme->button_danger_border_color;
    $button_danger_hover_content_color = $theme->button_danger_content_color;
    $button_danger_hover_bg_color = WDFColorUtils::adjust_brightness($theme->button_danger_bg_color, -8);
    $button_danger_hover_border_color = WDFColorUtils::adjust_brightness($theme->button_danger_border_color, -12);
    $button_danger_disabled_bg_color = $theme->button_danger_bg_color;
    $button_danger_disabled_border_color = $theme->button_danger_border_color;

    // button link
    $button_link_content_color = $theme->button_link_content_color;
    $button_link_hover_content_color = WDFColorUtils::adjust_brightness($theme->button_link_content_color, -15);

    ob_start();
    ?>
    <?php echo $prefix; ?>
    .btn-default {
      <?php echo $button_font_size; ?>
      font-family: <?php echo $button_font_family; ?>;
      font-weight: <?php echo $button_font_weight; ?>;
      border-color: <?php echo $button_default_border_color?>;
      background-color: <?php echo $button_default_bg_color?>;
      color: <?php echo $button_default_content_color ?>;
    }

    <?php echo $prefix; ?>
    .btn-default:hover,
    <?php echo $prefix; ?> .btn-default:focus,
    <?php echo $prefix; ?> .btn-default:active,
    <?php echo $prefix; ?> .btn-default.active,
    <?php echo $prefix; ?> .open .dropdown-toggle.btn-default {
      <?php echo $button_font_size; ?>
      font-family: <?php echo $button_font_family; ?>;
      font-weight: <?php echo $button_font_weight; ?>;
      border-color: <?php echo $button_default_hover_border_color ?>;
      background-color: <?php echo $button_default_hover_bg_color ?>;
      color: <?php echo $button_default_hover_content_color ?>;
    }

    <?php echo $prefix; ?>
    .btn-default.disabled,
    <?php echo $prefix; ?> .btn-default[disabled],
    <?php echo $prefix; ?> fieldset[disabled] .btn-default,
    <?php echo $prefix; ?> .btn-default.disabled:hover,
    <?php echo $prefix; ?> .btn-default[disabled]:hover,
    <?php echo $prefix; ?> fieldset[disabled] .btn-default:hover,
    <?php echo $prefix; ?> .btn-default.disabled:focus,
    <?php echo $prefix; ?> .btn-default[disabled]:focus,
    <?php echo $prefix; ?> fieldset[disabled] .btn-default:focus,
    <?php echo $prefix; ?> .btn-default.disabled:active,
    <?php echo $prefix; ?> .btn-default[disabled]:active,
    <?php echo $prefix; ?> fieldset[disabled] .btn-default:active,
    <?php echo $prefix; ?> .btn-default.disabled.active,
    <?php echo $prefix; ?> .btn-default[disabled].active,
    <?php echo $prefix; ?> fieldset[disabled] .btn-default.active {
      border-color: <?php echo $button_default_disabled_border_color; ?>;
      background-color: <?php echo $button_default_disabled_bg_color; ?>;
    }
    <?php echo $prefix; ?>
    .btn-primary {
      <?php echo $button_font_size; ?>
      font-family: <?php echo $button_font_family; ?>;
      font-weight: <?php echo $button_font_weight; ?>;
      border-color: <?php echo $button_primary_border_color?>;
      background-color: <?php echo $button_primary_bg_color?>;
      color: <?php echo $button_primary_content_color ?>;
    }
    <?php echo $prefix; ?>
    .btn .caret {
      <?php echo $button_font_size; ?>
      border-color: <?php echo $button_primary_border_color?>;
      background-color: <?php echo $button_primary_bg_color?>;
      border-top-color: <?php echo $button_primary_content_color ?>;
    }

    <?php echo $prefix; ?>
    .btn-primary:hover,
    <?php echo $prefix; ?> .btn-primary:focus,
    <?php echo $prefix; ?> .btn-primary:active,
    <?php echo $prefix; ?> .btn-primary.active,
    <?php echo $prefix; ?> .open .dropdown-toggle.btn-primary {
      <?php echo $button_font_size; ?>
      font-family: <?php echo $button_font_family; ?>;
      font-weight: <?php echo $button_font_weight; ?>;
      border-color: <?php echo $button_primary_hover_border_color ?>;
      background-color: <?php echo $button_primary_hover_bg_color ?>;
      color: <?php echo $button_primary_hover_content_color ?>;
    }
    <?php echo $prefix; ?>
    .btn-primary.disabled,
    <?php echo $prefix; ?> .btn-primary[disabled],
    <?php echo $prefix; ?> fieldset[disabled] .btn-primary,
    <?php echo $prefix; ?> .btn-primary.disabled:hover,
    <?php echo $prefix; ?> .btn-primary[disabled]:hover,
    <?php echo $prefix; ?> fieldset[disabled] .btn-primary:hover,
    <?php echo $prefix; ?> .btn-primary.disabled:focus,
    <?php echo $prefix; ?> .btn-primary[disabled]:focus,
    <?php echo $prefix; ?> fieldset[disabled] .btn-primary:focus,
    <?php echo $prefix; ?> .btn-primary.disabled:active,
    <?php echo $prefix; ?> .btn-primary[disabled]:active,
    <?php echo $prefix; ?> fieldset[disabled] .btn-primary:active,
    <?php echo $prefix; ?> .btn-primary.disabled.active,
    <?php echo $prefix; ?> .btn-primary[disabled].active,
    <?php echo $prefix; ?> fieldset[disabled] .btn-primary.active {
      border-color: <?php echo $button_primary_disabled_border_color; ?>;
      background-color: <?php echo $button_primary_disabled_bg_color; ?>;
    }
    <?php echo $prefix; ?>
    .btn-default .caret {
      <?php echo $button_font_size; ?>
      font-family: <?php echo $button_font_family; ?>;
      font-weight: <?php echo $button_font_weight; ?>;
      border-top-color: <?php echo $button_default_content_color ?>;
      border-bottom-color: <?php echo $button_default_content_color ?>;
    }
    <?php echo $prefix; ?>
    .btn-default:hover .caret,
    <?php echo $prefix; ?> .btn-default:focus .caret,
    <?php echo $prefix; ?> .btn-default:active .caret,
    <?php echo $prefix; ?> .btn-default.active .caret {
      border-top-color: <?php echo $button_default_hover_content_color ?>;
      border-bottom-color: <?php echo $button_default_hover_content_color ?>;
    }
    <?php echo $prefix; ?>
    .btn-success {
      <?php echo $button_font_size; ?>
      font-family: <?php echo $button_font_family; ?>;
      font-weight: <?php echo $button_font_weight; ?>;
      border-color: <?php echo $button_success_border_color?>;
      background-color: <?php echo $button_success_bg_color?>;
      color: <?php echo $button_success_content_color ?>;
    }
    <?php echo $prefix; ?>
    .btn-success:hover,
    <?php echo $prefix; ?> .btn-success:focus,
    <?php echo $prefix; ?> .btn-success:active,
    <?php echo $prefix; ?> .btn-success.active,
    <?php echo $prefix; ?> .open .dropdown-toggle.btn-success {
      <?php echo $button_font_size; ?>
      font-family: <?php echo $button_font_family; ?>;
      font-weight: <?php echo $button_font_weight; ?>;
      border-color: <?php echo $button_success_hover_border_color ?>;
      background-color: <?php echo $button_success_hover_bg_color ?>;
      color: <?php echo $button_success_hover_content_color ?>;
    }
    <?php echo $prefix; ?>
    .btn-success.disabled,
    <?php echo $prefix; ?> .btn-success[disabled],
    <?php echo $prefix; ?> fieldset[disabled] .btn-success,
    <?php echo $prefix; ?> .btn-success.disabled:hover,
    <?php echo $prefix; ?> .btn-success[disabled]:hover,
    <?php echo $prefix; ?> fieldset[disabled] .btn-success:hover,
    <?php echo $prefix; ?> .btn-success.disabled:focus,
    <?php echo $prefix; ?> .btn-success[disabled]:focus,
    <?php echo $prefix; ?> fieldset[disabled] .btn-success:focus,
    <?php echo $prefix; ?> .btn-success.disabled:active,
    <?php echo $prefix; ?> .btn-success[disabled]:active,
    <?php echo $prefix; ?> fieldset[disabled] .btn-success:active,
    <?php echo $prefix; ?> .btn-success.disabled.active,
    <?php echo $prefix; ?> .btn-success[disabled].active,
    <?php echo $prefix; ?> fieldset[disabled] .btn-success.active {
      border-color: <?php echo $button_success_disabled_border_color; ?>;
      background-color: <?php echo $button_success_disabled_bg_color; ?>;
    }
    <?php echo $prefix; ?>
    .btn-success .caret {
      <?php echo $button_font_size; ?>
      font-family: <?php echo $button_font_family; ?>;
      font-weight: <?php echo $button_font_weight; ?>;
      border-top-color: <?php echo $button_success_content_color ?>;
      border-bottom-color: <?php echo $button_success_content_color ?>;
    }
    <?php echo $prefix; ?>
    .btn-success:hover .caret,
    <?php echo $prefix; ?> .btn-success:focus .caret,
    <?php echo $prefix; ?> .btn-success:active .caret,
    <?php echo $prefix; ?> .btn-success.active .caret {
      <?php echo $button_font_size; ?>
      font-family: <?php echo $button_font_family; ?>;
      font-weight: <?php echo $button_font_weight; ?>;
      border-top-color: <?php echo $button_success_hover_content_color ?>;
      border-bottom-color: <?php echo $button_success_hover_content_color ?>;
    }
    <?php echo $prefix; ?>
    .btn-info {
      border-color: <?php echo $button_info_border_color?>;
      background-color: <?php echo $button_info_bg_color?>;
      color: <?php echo $button_info_content_color ?>;
    }
    <?php echo $prefix; ?>
    .btn-info:hover,
    <?php echo $prefix; ?> .btn-info:focus,
    <?php echo $prefix; ?> .btn-info:active,
    <?php echo $prefix; ?> .btn-info.active,
    <?php echo $prefix; ?> .open .dropdown-toggle.btn-info {
        border-color: <?php echo $button_info_hover_border_color ?>;
        background-color: <?php echo $button_info_hover_bg_color ?>;
        color: <?php echo $button_info_hover_content_color ?>;
    }
    <?php echo $prefix; ?>
    .btn-info.disabled,
    <?php echo $prefix; ?> .btn-info[disabled],
    <?php echo $prefix; ?> fieldset[disabled] .btn-info,
    <?php echo $prefix; ?> .btn-info.disabled:hover,
    <?php echo $prefix; ?> .btn-info[disabled]:hover,
    <?php echo $prefix; ?> fieldset[disabled] .btn-info:hover,
    <?php echo $prefix; ?> .btn-info.disabled:focus,
    <?php echo $prefix; ?> .btn-info[disabled]:focus,
    <?php echo $prefix; ?> fieldset[disabled] .btn-info:focus,
    <?php echo $prefix; ?> .btn-info.disabled:active,
    <?php echo $prefix; ?> .btn-info[disabled]:active,
    <?php echo $prefix; ?> fieldset[disabled] .btn-info:active,
    <?php echo $prefix; ?> .btn-info.disabled.active,
    <?php echo $prefix; ?> .btn-info[disabled].active,
    <?php echo $prefix; ?> fieldset[disabled] .btn-info.active {
        border-color: <?php echo $button_info_disabled_border_color; ?>;
        background-color: <?php echo $button_info_disabled_bg_color; ?>;
    }
    <?php echo $prefix; ?>
    .btn-info .caret {
        border-top-color: <?php echo $button_info_content_color ?>;
        border-bottom-color: <?php echo $button_info_content_color ?>;
    }
    <?php echo $prefix; ?>
    .btn-info:hover .caret,
    <?php echo $prefix; ?> .btn-info:focus .caret,
    <?php echo $prefix; ?> .btn-info:active .caret,
    <?php echo $prefix; ?> .btn-info.active .caret {
        border-top-color: <?php echo $button_info_hover_content_color ?>;
        border-bottom-color: <?php echo $button_info_hover_content_color ?>;
    }
    <?php echo $prefix; ?>
    .btn-warning {
        border-color: <?php echo $button_warning_border_color?>;
        background-color: <?php echo $button_warning_bg_color?>;
        color: <?php echo $button_warning_content_color ?>;
    }
    <?php echo $prefix; ?>
    .btn-warning:hover,
    <?php echo $prefix; ?> .btn-warning:focus,
    <?php echo $prefix; ?> .btn-warning:active,
    <?php echo $prefix; ?> .btn-warning.active,
    <?php echo $prefix; ?> .open .dropdown-toggle.btn-warning {
        border-color: <?php echo $button_warning_hover_border_color ?>;
        background-color: <?php echo $button_warning_hover_bg_color ?>;
        color: <?php echo $button_warning_hover_content_color ?>;
    }
    <?php echo $prefix; ?>
    .btn-warning.disabled,
    <?php echo $prefix; ?> .btn-warning[disabled],
    <?php echo $prefix; ?> fieldset[disabled] .btn-warning,
    <?php echo $prefix; ?> .btn-warning.disabled:hover,
    <?php echo $prefix; ?> .btn-warning[disabled]:hover,
    <?php echo $prefix; ?> fieldset[disabled] .btn-warning:hover,
    <?php echo $prefix; ?> .btn-warning.disabled:focus,
    <?php echo $prefix; ?> .btn-warning[disabled]:focus,
    <?php echo $prefix; ?> fieldset[disabled] .btn-warning:focus,
    <?php echo $prefix; ?> .btn-warning.disabled:active,
    <?php echo $prefix; ?> .btn-warning[disabled]:active,
    <?php echo $prefix; ?> fieldset[disabled] .btn-warning:active,
    <?php echo $prefix; ?> .btn-warning.disabled.active,
    <?php echo $prefix; ?> .btn-warning[disabled].active,
    <?php echo $prefix; ?> fieldset[disabled] .btn-warning.active {
        border-color: <?php echo $button_warning_disabled_border_color; ?>;
        background-color: <?php echo $button_warning_disabled_bg_color; ?>;
    }
    <?php echo $prefix; ?>
    .btn-warning .caret {
        border-top-color: <?php echo $button_warning_content_color ?>;
        border-bottom-color: <?php echo $button_warning_content_color ?>;
    }
    <?php echo $prefix; ?>
    .btn-warning:hover .caret,
    <?php echo $prefix; ?> .btn-warning:focus .caret,
    <?php echo $prefix; ?> .btn-warning:active .caret,
    <?php echo $prefix; ?> .btn-warning.active .caret {
        border-top-color: <?php echo $button_warning_hover_content_color ?>;
        border-bottom-color: <?php echo $button_warning_hover_content_color ?>;
    }
    <?php echo $prefix; ?>
    .btn-danger {
        border-color: <?php echo $button_danger_border_color?>;
        background-color: <?php echo $button_danger_bg_color?>;
        color: <?php echo $button_danger_content_color ?>;
    }
    <?php echo $prefix; ?>
    .btn-danger:hover,
    <?php echo $prefix; ?> .btn-danger:focus,
    <?php echo $prefix; ?> .btn-danger:active,
    <?php echo $prefix; ?> .btn-danger.active,
    <?php echo $prefix; ?> .open .dropdown-toggle.btn-danger {
        border-color: <?php echo $button_danger_hover_border_color ?>;
        background-color: <?php echo $button_danger_hover_bg_color ?>;
        color: <?php echo $button_danger_hover_content_color ?>;
    }
    <?php echo $prefix; ?>
    .btn-danger.disabled,
    <?php echo $prefix; ?> .btn-danger[disabled],
    <?php echo $prefix; ?> fieldset[disabled] .btn-danger,
    <?php echo $prefix; ?> .btn-danger.disabled:hover,
    <?php echo $prefix; ?> .btn-danger[disabled]:hover,
    <?php echo $prefix; ?> fieldset[disabled] .btn-danger:hover,
    <?php echo $prefix; ?> .btn-danger.disabled:focus,
    <?php echo $prefix; ?> .btn-danger[disabled]:focus,
    <?php echo $prefix; ?> fieldset[disabled] .btn-danger:focus,
    <?php echo $prefix; ?> .btn-danger.disabled:active,
    <?php echo $prefix; ?> .btn-danger[disabled]:active,
    <?php echo $prefix; ?> fieldset[disabled] .btn-danger:active,
    <?php echo $prefix; ?> .btn-danger.disabled.active,
    <?php echo $prefix; ?> .btn-danger[disabled].active,
    <?php echo $prefix; ?> fieldset[disabled] .btn-danger.active {
        border-color: <?php echo $button_danger_disabled_border_color; ?>;
        background-color: <?php echo $button_danger_disabled_bg_color; ?>;
    }
    <?php echo $prefix; ?>
    .btn-danger .caret {
        border-top-color: <?php echo $button_danger_content_color ?>;
        border-bottom-color: <?php echo $button_danger_content_color ?>;
    }
    <?php echo $prefix; ?>
    .btn-danger:hover .caret,
    <?php echo $prefix; ?> .btn-danger:focus .caret,
    <?php echo $prefix; ?> .btn-danger:active .caret,
    <?php echo $prefix; ?> .btn-danger.active .caret {
        border-top-color: <?php echo $button_danger_hover_content_color ?>;
        border-bottom-color: <?php echo $button_danger_hover_content_color ?>;
    }
    <?php echo $prefix; ?>
    .link,
    <?php echo $prefix; ?> .btn-link {
        color: <?php echo $button_link_content_color; ?>;
    }
    <?php echo $prefix; ?>
    .link:hover,
    <?php echo $prefix; ?> .btn-link:hover,
    <?php echo $prefix; ?> .link:focus,
    <?php echo $prefix; ?> .btn-link:focus {
        color: <?php echo $button_link_hover_content_color; ?>;
    }
    <?php echo $prefix; ?>
    .link.disabled,
    <?php echo $prefix; ?> .btn-link.disabled,
    <?php echo $prefix; ?> .link[disabled],
    <?php echo $prefix; ?> .btn-link[disabled],
    <?php echo $prefix; ?> .link.disabled:hover,
    <?php echo $prefix; ?> .btn-link.disabled:hover,
    <?php echo $prefix; ?> .link[disabled]:hover,
    <?php echo $prefix; ?> .btn-link[disabled]:hover,
    <?php echo $prefix; ?> .link.disabled:focus,
    <?php echo $prefix; ?> .btn-link.disabled:focus,
    <?php echo $prefix; ?> .link[disabled]:focus,
    <?php echo $prefix; ?> .btn-link[disabled]:focus {
        color: <?php echo $button_link_content_color; ?>;
        opacity: 0.5;
    }
    <?php

    return ob_get_clean();
  }

  private function divider() {
    $prefix = $this->prefix;
    $theme = $this->theme;

    $divider_color = $theme->divider_color;

    ob_start();
    ?>
    <?php echo $prefix; ?>
    .wd_divider {
      background-color: <?php echo $divider_color; ?>;
    }
    <?php

    return ob_get_clean();
  }

  private function navbar() {
    $prefix = $this->prefix;
    $theme = $this->theme;

    $navbar_bg_color = $theme->navbar_bg_color;
    $navbar_border_color = $theme->navbar_border_color;
    $navbar_link_content_color = $theme->navbar_link_content_color;
    $navbar_link_hover_content_color = $theme->navbar_link_hover_content_color;
    $navbar_link_open_content_color = $theme->navbar_link_open_content_color;
    $navbar_link_open_bg_color = $theme->navbar_link_open_bg_color;
    $navbar_badge_content_color = $theme->navbar_badge_content_color;
    $navbar_badge_font_size = $theme->navbar_badge_font_size ? 'font-size: ' . $theme->navbar_badge_font_size . ';' : '';
    $navbar_badge_font_family = $theme->navbar_badge_font_family ? $theme->navbar_badge_font_family : 'inherit';
    $navbar_badge_font_weight = $theme->navbar_badge_font_weight ? $theme->navbar_badge_font_weight : 'inherit';

    $navbar_badge_bg_color = $theme->navbar_badge_bg_color;
    $navbar_dropdown_link_content_color = $theme->navbar_dropdown_link_content_color;
    $navbar_dropdown_link_font_size = $theme->navbar_dropdown_link_font_size ? 'font-size: ' . $theme->navbar_dropdown_link_font_size . ';' : '';
    $navbar_dropdown_link_font_family = $theme->navbar_dropdown_link_font_family ? $theme->navbar_dropdown_link_font_family : 'inherit';
    $navbar_dropdown_link_font_weight = $theme->navbar_dropdown_link_font_weight ? $theme->navbar_dropdown_link_font_weight : 'inherit';

    $navbar_dropdown_link_hover_content_color = $theme->navbar_dropdown_link_hover_content_color;
    $navbar_dropdown_link_hover_background_content_color = $theme->navbar_dropdown_link_hover_background_content_color;
    $navbar_dropdown_divider_color = $theme->navbar_dropdown_divider_color;
    $navbar_dropdown_background_color = $theme->navbar_dropdown_background_color;
    $navbar_dropdown_border_color = $theme->navbar_dropdown_border_color;

    ob_start();
    ?>
    <?php echo $prefix; ?>
    .navbar-default {
      background-color: <?php echo $navbar_bg_color; ?>;
      border-color: <?php echo $navbar_border_color; ?>;
    }
    <?php echo $prefix; ?>
    .navbar-default .navbar-nav > li > a,
    <?php echo $prefix; ?>
    .bs_dropdown-toggle {
      color: <?php echo $navbar_link_content_color; ?>;
      <?php echo $navbar_badge_font_size; ?>
      font-family: <?php echo $navbar_badge_font_family; ?>;
      font-weight: <?php echo $navbar_badge_font_weight; ?>;
    }
    <?php echo $prefix; ?>
    .navbar-default .navbar-nav > li > a .caret {
      border-top-color: <?php echo $navbar_link_content_color; ?>;
      border-bottom-color: <?php echo $navbar_link_content_color; ?>;
    }
    <?php echo $prefix; ?>
    .navbar-default .navbar-nav > li > a:hover,
    <?php echo $prefix; ?> .navbar-default .navbar-nav > li > a:focus {
      color: <?php echo $navbar_link_hover_content_color; ?>;
    }
    <?php echo $prefix; ?>
    .navbar-default .navbar-nav > li > a:hover .caret,
    <?php echo $prefix; ?> .navbar-default .navbar-nav > li > a:focus .caret {
      border-top-color: <?php echo $navbar_link_hover_content_color; ?>;
      border-bottom-color: <?php echo $navbar_link_hover_content_color; ?>;
    }
    <?php echo $prefix; ?>
    .navbar-default .navbar-nav > .open > a,
    <?php echo $prefix; ?> .navbar-default .navbar-nav > .open > a:hover,
    <?php echo $prefix; ?> .navbar-default .navbar-nav > .open > a:focus {
      color: <?php echo $navbar_link_open_content_color; ?>;
      background-color: <?php echo $navbar_link_open_bg_color; ?>;
    }
    <?php echo $prefix; ?>
    .navbar-default .navbar-nav > .open > a .caret,
    <?php echo $prefix; ?> .navbar-default .navbar-nav > .open > a:hover .caret,
    <?php echo $prefix; ?> .navbar-default .navbar-nav > .open > a:focus .caret {
      border-top-color: <?php echo $navbar_link_open_content_color; ?>;
      border-bottom-color: <?php echo $navbar_link_open_content_color; ?>;
    }
    <?php echo $prefix; ?>
    .badge {
      <?php echo $navbar_badge_font_size; ?>
      font-family: <?php echo $navbar_badge_font_family; ?>;
      font-weight: <?php echo $navbar_badge_font_weight; ?>;
      color: <?php echo $navbar_badge_content_color; ?>;
      background-color: <?php echo $navbar_badge_bg_color; ?>;
    }
    <?php echo $prefix; ?>
    .navbar-default .navbar-nav .open .bs_dropdown-menu {
      background-color: <?php echo $navbar_dropdown_background_color; ?>;
      border-color: <?php echo $navbar_dropdown_border_color; ?>;
    }
    <?php echo $prefix; ?>
    .navbar-default .navbar-nav .open .bs_dropdown-menu > li.divider {
      background-color: <?php echo $navbar_dropdown_divider_color; ?>;
    }
    <?php echo $prefix; ?>
    .navbar-default .navbar-nav .open .bs_dropdown-menu > li > a {
      color: <?php echo $navbar_dropdown_link_content_color; ?>;
      <?php echo $navbar_dropdown_link_font_size; ?>
      font-family: <?php echo $navbar_dropdown_link_font_family; ?>;
      font-weight: <?php echo $navbar_dropdown_link_font_weight; ?>;
    }
    <?php echo $prefix; ?>
    .navbar-default .navbar-nav .open .bs_dropdown-menu > li > a:hover,
    <?php echo $prefix; ?> .navbar-default .navbar-nav .open .bs_dropdown-menu > li > a:focus {
      color: <?php echo $navbar_dropdown_link_hover_content_color; ?>;
      background-color: <?php echo $navbar_dropdown_link_hover_background_content_color; ?>;
    }
    <?php

    return ob_get_clean();
  }

  private function modal() {
    $prefix = $this->prefix;
    $theme = $this->theme;

    $modal_backdrop_color = $theme->modal_backdrop_color;
    $modal_bg_color = $theme->modal_bg_color;
    $modal_border_color = $theme->modal_border_color;
    $modal_dividers_color = $theme->modal_dividers_color;

    $modal_ctrl_color = $theme->modal_bg_color;
    $modal_ctrl_hover_color = WDFColorUtils::adjust_brightness($modal_ctrl_color, -10);
    $modal_ctrl_mdown_color = WDFColorUtils::adjust_brightness($modal_ctrl_color, -20);

    ob_start();
    ?>
    <?php echo $prefix; ?>
    .modal-content {
      background-color: <?php echo $modal_bg_color; ?>;
      border-color: <?php echo $modal_border_color; ?>;
    }
    <?php echo $prefix; ?>
    .modal-backdrop {
      background-color: <?php echo $modal_backdrop_color; ?>;
    }
    <?php echo $prefix; ?>
    .modal-header {
      border-bottom-color: <?php echo $modal_dividers_color; ?>;
    }
    <?php echo $prefix; ?>
    .modal-footer {
      border-top-color: <?php echo $modal_dividers_color; ?>;
    }
    <?php echo $prefix; ?>
    .wd-modal-ctrl.btn-link {
      color: <?php echo $modal_ctrl_color; ?>;
    }
    <?php echo $prefix; ?>
    .wd-modal-ctrl.btn-link:hover,
    <?php echo $prefix; ?> .wd-modal-ctrl.btn-link:focus {
      color: <?php echo $modal_ctrl_hover_color; ?>;
    }
    <?php echo $prefix; ?>
    .wd-modal-ctrl.btn-link:active {
      color: <?php echo $modal_ctrl_mdown_color; ?>;
    }
    <?php echo $prefix; ?>
    .wd-modal-ctrl.btn-link.disabled,
    <?php echo $prefix; ?> .wd-modal-ctrl.btn-link[disabled],
    <?php echo $prefix; ?> .wd-modal-ctrl.btn-link.disabled:hover,
    <?php echo $prefix; ?> .wd-modal-ctrl.btn-link[disabled]:hover,
    <?php echo $prefix; ?> .wd-modal-ctrl.btn-link.disabled:focus,
    <?php echo $prefix; ?> .wd-modal-ctrl.btn-link[disabled]:focus,
    <?php echo $prefix; ?> .wd-modal-ctrl.btn-link.disabled:active,
    <?php echo $prefix; ?> .wd-modal-ctrl.btn-link[disabled]:active {
      color: <?php echo $modal_ctrl_color; ?> !important;
    }
    <?php

    return ob_get_clean();
  }

  private function paneluserdata() {
    $prefix = $this->prefix;
    $theme = $this->theme;

    $panel_user_data_bg_color = $theme->panel_user_data_bg_color;
    $panel_user_data_border_color = $theme->panel_user_data_border_color;
    $panel_user_data_footer_bg_color = $theme->panel_user_data_footer_bg_color;

    ob_start();
    ?>
    <?php echo $prefix; ?>
    .panel.wd_shop_panel_user_data {
      background-color: <?php echo $panel_user_data_bg_color; ?>;
      border-color: <?php echo $panel_user_data_border_color; ?>;
    }
    <?php echo $prefix; ?>
    .wd_shop_panel_user_data .panel-footer {
      background-color: <?php echo $panel_user_data_footer_bg_color; ?>;
      border-top-color: <?php echo $panel_user_data_border_color; ?>;
    }
    <?php

    return ob_get_clean();
  }

  private function panelproduct() {
    $prefix = $this->prefix;
    $theme = $this->theme;

    $panel_product_bg_color = $theme->panel_product_bg_color;
    $panel_product_border_color = $theme->panel_product_border_color;
    $panel_product_footer_bg_color = $theme->panel_product_footer_bg_color;

    $items_slider_hover_border_color_rgba = WDFColorUtils::color_to_rgba($panel_product_border_color);
    $items_slider_hover_border_color_rgba['a'] = 0.45;
    $items_slider_hover_border_color_rgba = WDFColorUtils::color_to_rgba($items_slider_hover_border_color_rgba, true);
    $items_slider_active_border_color_rgba = WDFColorUtils::color_to_rgba($panel_product_border_color);
    $items_slider_active_border_color_rgba['a'] = 0.85;
    $items_slider_active_border_color_rgba = WDFColorUtils::color_to_rgba($items_slider_active_border_color_rgba, true);

    ob_start();
    ?>
    <?php echo $prefix; ?>
    .panel.wd_shop_panel_product {
      background-color: <?php echo $panel_product_bg_color; ?>;
      border-color: <?php echo $panel_product_border_color; ?>;
    }
    <?php echo $prefix; ?>
    .wd_shop_panel_product .panel-footer {
      background-color: <?php echo $panel_product_footer_bg_color; ?>;
      border-top-color: <?php echo $panel_product_border_color; ?>;
    }
    <?php echo $prefix; ?>
    .wd_shop_product_image_container,
    <?php echo $prefix; ?> .wd_shop_category_image_container,
    <?php echo $prefix; ?> .wd_shop_manufacturer_logo_container {
      border-color: <?php echo $panel_product_border_color; ?>;
    }
    <?php echo $prefix; ?>
    .wd_shop_product_images_slider ul.wd_items_slider_items_list li,
    <?php echo $prefix; ?> .wd_shop_products_slider ul.wd_items_slider_items_list li {
      border-color: transparent;
    }
    <?php echo $prefix; ?>
    .wd_shop_product_images_slider ul.wd_items_slider_items_list li:hover,
    <?php echo $prefix; ?> .wd_shop_products_slider ul.wd_items_slider_items_list li:hover {
      border-color: <?php echo $items_slider_hover_border_color_rgba; ?>;
    }
    <?php echo $prefix; ?>
    .wd_shop_product_images_slider ul.wd_items_slider_items_list li.active,
    <?php echo $prefix; ?> .wd_shop_products_slider ul.wd_items_slider_items_list li li.active,
    <?php echo $prefix; ?> .wd_shop_product_images_slider ul.wd_items_slider_items_list li.active:hover,
    <?php echo $prefix; ?> .wd_shop_products_slider ul.wd_items_slider_items_list li li.active:hover {
      border-color: <?php echo $items_slider_active_border_color_rgba; ?>;
    }
    <?php echo $prefix; ?>
    .wd_shop_table_product_data,
    <?php echo $prefix; ?> .wd_shop_table_product_data tbody,
    <?php echo $prefix; ?> .wd_shop_table_product_data tbody tr,
    <?php echo $prefix; ?> .wd_shop_table_product_data tbody tr td {
      border-color: <?php echo $panel_product_border_color; ?>;
      border-width: 1px;
      border-style: solid;
    }
    <?php

    return ob_get_clean();
  }

  private function well() {
    $prefix = $this->prefix;
    $theme = $this->theme;

    $well_bg_color = $theme->well_bg_color;
    $well_border_color = $theme->well_border_color;

    ob_start();
    ?>
    <?php echo $prefix; ?>
    .well {
      background-color: <?php echo $well_bg_color; ?>;
      border-color: <?php echo $well_border_color; ?>;
    }
    <?php

    return ob_get_clean();
  }

  private function label() {
    $prefix = $this->prefix;
    $theme = $this->theme;

    $label_content_color = $theme->label_content_color;
    $label_font_size = $theme->label_font_size ? 'font-size: ' . $theme->label_font_size . ';' : '';
    $label_font_family = $theme->label_font_family ? $theme->label_font_family : 'inherit';
    $label_font_weight = $theme->label_font_weight ? $theme->label_font_weight : 'inherit';

    $label_bg_color = $theme->label_bg_color;
    $label_bg_hover_color = WDFColorUtils::adjust_brightness($label_bg_color, -10);

    ob_start();
    ?>
    <?php echo $prefix; ?>
    .label-default {
      <?php echo $label_font_size; ?>
      font-family: <?php echo $label_font_family; ?>;
      font-weight: <?php echo $label_font_weight; ?>;
      color: <?php echo $label_content_color; ?>;
      background-color: <?php echo $label_bg_color; ?>;
    }
    <?php echo $prefix; ?>
    .label-default[href]:hover,
    <?php echo $prefix; ?> .label-default[href]:focus {
      background-color: <?php echo $label_bg_hover_color; ?>;
    }
    <?php

    return ob_get_clean();
  }

  private function alert() {
    $prefix = $this->prefix;
    $theme = $this->theme;

    $alert_info_content_color = $theme->alert_info_content_color;
    $alert_font_size = $theme->alert_font_size ? 'font-size: ' . $theme->alert_font_size . ';' : '';
    $alert_font_family = $theme->alert_font_family ? $theme->alert_font_family : 'inherit';
    $alert_font_weight = $theme->alert_font_weight ? $theme->alert_font_weight : 'inherit';

    $alert_info_bg_color = $theme->alert_info_bg_color;
    $alert_info_border_color = $theme->alert_info_border_color;
    $alert_info_link_color = WDFColorUtils::adjust_brightness($theme->alert_info_border_color, -15);

    $alert_danger_content_color = $theme->alert_danger_content_color;
    $alert_danger_bg_color = $theme->alert_danger_bg_color;
    $alert_danger_border_color = $theme->alert_danger_border_color;
    $alert_danger_link_color = WDFColorUtils::adjust_brightness($theme->alert_danger_border_color, -15);

    ob_start();
    ?>
    <?php echo $prefix; ?>
    .alert-info {
      <?php echo $alert_font_size; ?>
      font-family: <?php echo $alert_font_family; ?>;
      font-weight: <?php echo $alert_font_weight; ?>;
      color: <?php echo $alert_info_content_color; ?>;
      background-color: <?php echo $alert_info_bg_color; ?>;
      border-color: <?php echo $alert_info_border_color; ?>;
    }
    <?php echo $prefix; ?>
    .alert-info * {
      <?php echo $alert_font_size; ?>
      font-family: <?php echo $alert_font_family; ?>;
      font-weight: <?php echo $alert_font_weight; ?>;
      color: <?php echo $alert_info_content_color; ?>;
      border-color: <?php echo $alert_info_border_color; ?>;
    }
    <?php echo $prefix; ?>
    .alert-info .alert-link {
      color: <?php echo $alert_info_link_color; ?>;
    }
    <?php echo $prefix; ?>
    .alert-danger {
      <?php echo $alert_font_size; ?>
      font-family: <?php echo $alert_font_family; ?>;
      font-weight: <?php echo $alert_font_weight; ?>;
      color: <?php echo $alert_danger_content_color; ?>;
      background-color: <?php echo $alert_danger_bg_color; ?>;
      border-color: <?php echo $alert_danger_border_color; ?>;
    }
    <?php echo $prefix; ?>
    .alert-danger * {
      <?php echo $alert_font_size; ?>
      font-family: <?php echo $alert_font_family; ?>;
      font-weight: <?php echo $alert_font_weight; ?>;
      color: <?php echo $alert_danger_content_color; ?>;
      border-color: <?php echo $alert_danger_border_color; ?>;
    }
    <?php echo $prefix; ?>
    .alert-danger .alert-link {
      color: <?php echo $alert_danger_link_color; ?>;
    }
    <?php

    return ob_get_clean();
  }

  private function breadcrumb() {
    $prefix = $this->prefix;
    $theme = $this->theme;

    $breadcrumb_content_color = $theme->breadcrumb_content_color;
    $breadcrumb_font_size = $theme->breadcrumb_font_size ? 'font-size: ' . $theme->breadcrumb_font_size . ';' : '';
    $breadcrumb_font_family = $theme->breadcrumb_font_family ? $theme->breadcrumb_font_family : 'inherit';
    $breadcrumb_font_weight = $theme->breadcrumb_font_weight ? $theme->breadcrumb_font_weight : 'inherit';

    $breadcrumb_bg_color = $theme->breadcrumb_bg_color;
    $breadcrumb_active_content_color = WDFColorUtils::adjust_brightness($breadcrumb_bg_color, 8);

    ob_start();
    ?>
    <?php echo $prefix; ?>
    .breadcrumb {
      background-color: <?php echo $breadcrumb_bg_color; ?>;
    }
    <?php echo $prefix; ?> .breadcrumb > li + li:before,
    <?php echo $prefix; ?> .breadcrumb > li,
    <?php echo $prefix; ?> .breadcrumb > li a {
      color: <?php echo $breadcrumb_content_color; ?>;
      <?php echo $breadcrumb_font_size; ?>
      font-family: <?php echo $breadcrumb_font_family; ?>;
      font-weight: <?php echo $breadcrumb_font_weight; ?>;
    }
    <?php

    return ob_get_clean();
  }

  private function pills() {
    $prefix = $this->prefix;
    $theme = $this->theme;

    $pill_link_content_color = $theme->pill_link_content_color;
    $pill_link_font_size = $theme->pill_link_font_size ? 'font-size: ' . $theme->pill_link_font_size . ';' : '';
    $pill_link_font_family = $theme->pill_link_font_family ? $theme->pill_link_font_family : 'inherit';
    $pill_link_font_weight = $theme->pill_link_font_weight ? $theme->pill_link_font_weight : 'inherit';

    $pill_link_hover_content_color = $theme->pill_link_hover_content_color;
    $pill_link_hover_bg_color = $theme->pill_link_hover_bg_color;

    ob_start();
    ?>
    <?php echo $prefix; ?> .nav-pills > li > a {
      color: <?php echo $pill_link_content_color; ?>;
      <?php echo $pill_link_font_size; ?>
      font-family: <?php echo $pill_link_font_family; ?>;
      font-weight: <?php echo $pill_link_font_weight; ?>;
    }
    <?php echo $prefix; ?> .nav-pills > li > a:hover,
    <?php echo $prefix; ?> .nav-pills > li > a:focus {
      color: <?php echo $pill_link_hover_content_color; ?>;
      background-color: <?php echo $pill_link_hover_bg_color; ?>;
    }
    <?php

    return ob_get_clean();
  }

  private function tab() {
    $prefix = $this->prefix;
    $theme = $this->theme;

    $tab_link_content_color = $theme->tab_link_content_color;
    $tab_link_font_size = $theme->tab_link_font_size ? 'font-size: ' . $theme->tab_link_font_size . ';' : '';
    $tab_link_font_family = $theme->tab_link_font_family ? $theme->tab_link_font_family : 'inherit';
    $tab_link_font_weight = $theme->tab_link_font_weight ? $theme->tab_link_font_weight : 'inherit';

    $tab_link_hover_content_color = $theme->tab_link_hover_content_color;
    $tab_link_hover_bg_color = $theme->tab_link_hover_bg_color;
    $tab_link_active_content_color = $theme->tab_link_active_content_color;
    $tab_link_active_bg_color = $theme->tab_link_active_bg_color;
    $tab_border_color = $theme->tab_border_color;

    ob_start();
    ?>
    <?php echo $prefix; ?>
    .nav-tabs {
      border-bottom-color: <?php echo $tab_border_color; ?>;
    }
    <?php echo $prefix; ?>
    .nav-tabs > li > a {
      color: <?php echo $tab_link_content_color; ?>;
      <?php echo $tab_link_font_size; ?>
      font-family: <?php echo $tab_link_font_family; ?>;
      font-weight: <?php echo $tab_link_font_weight; ?>;
    }
    <?php echo $prefix; ?>
    .nav-tabs > li > a:hover {
      <?php echo $tab_link_font_size; ?>
      font-family: <?php echo $tab_link_font_family; ?>;
      font-weight: <?php echo $tab_link_font_weight; ?>;
      color: <?php echo $tab_link_hover_content_color; ?>;
      background-color: <?php echo $tab_link_hover_bg_color; ?>;
      border-color: <?php echo $tab_link_hover_bg_color . ' ' . $tab_link_hover_bg_color . ' ' . $tab_border_color; ?>;
    }
    <?php echo $prefix; ?>
    .nav-tabs > li.active > a,
    <?php echo $prefix; ?> .nav-tabs > li.active > a:hover,
    <?php echo $prefix; ?> .nav-tabs > li.active > a:focus {
      <?php echo $tab_link_font_size; ?>
      font-family: <?php echo $tab_link_font_family; ?>;
      font-weight: <?php echo $tab_link_font_weight; ?>;
      color: <?php echo $tab_link_active_content_color; ?>;
      background-color: <?php echo $tab_link_active_bg_color; ?>;
      border-color: <?php echo $tab_border_color; ?>;
      border-bottom-color: transparent;
    }
    <?php

    return ob_get_clean();
  }

  private function pagination() {
    $prefix = $this->prefix;
    $theme = $this->theme;

    $pagination_content_color = $theme->pagination_content_color;
    $pagination_font_size = $theme->pagination_font_size ? 'font-size: ' . $theme->pagination_font_size . ';' : '';
    $pagination_font_family = $theme->pagination_font_family ? $theme->pagination_font_family : 'inherit';
    $pagination_font_weight = $theme->pagination_font_weight ? $theme->pagination_font_weight : 'inherit';

    $pagination_bg_color = $theme->pagination_bg_color;
    $pagination_hover_content_color = $theme->pagination_hover_content_color;
    $pagination_hover_bg_color = $theme->pagination_hover_bg_color;
    $pagination_active_content_color = $theme->pagination_active_content_color;
    $pagination_active_bg_color = $theme->pagination_active_bg_color;
    $pagination_border_color = $theme->pagination_border_color;

    ob_start();
    ?>
    <?php echo $prefix; ?>
    .pagination > li > a,
    <?php echo $prefix; ?> .pagination > li > span {
      <?php echo $pagination_font_size; ?>
      font-family: <?php echo $pagination_font_family; ?>;
      font-weight: <?php echo $pagination_font_weight; ?>;
      color: <?php echo $pagination_content_color; ?>;
      background-color: <?php echo $pagination_bg_color; ?>;
      border-color: <?php echo $pagination_border_color; ?>;
    }
    <?php echo $prefix; ?>
    .pagination > li > a:hover,
    <?php echo $prefix; ?> .pagination > li > span:hover,
    <?php echo $prefix; ?> .pagination > li > a:focus,
    <?php echo $prefix; ?> .pagination > li > span:focus {
      <?php echo $pagination_font_size; ?>
      font-family: <?php echo $pagination_font_family; ?>;
      font-weight: <?php echo $pagination_font_weight; ?>;
      color: <?php echo $pagination_hover_content_color; ?>;
      background-color: <?php echo $pagination_hover_bg_color; ?>;
    }
    <?php echo $prefix; ?>
    .pagination > .active > a,
    <?php echo $prefix; ?> .pagination > .active > span,
    <?php echo $prefix; ?> .pagination > .active > a:hover,
    <?php echo $prefix; ?> .pagination > .active > span:hover,
    <?php echo $prefix; ?> .pagination > .active > a:focus,
    <?php echo $prefix; ?> .pagination > .active > span:focus {
      color: <?php echo $pagination_active_content_color; ?>;
      background-color: <?php echo $pagination_active_bg_color; ?>;
      border-top: 1px solid <?php echo $pagination_active_bg_color; ?>;
      border-right: 1px solid <?php echo $pagination_active_bg_color; ?>;
      border-bottom: 1px solid <?php echo $pagination_active_bg_color; ?>;
      border-left: 1px solid <?php echo $pagination_active_bg_color; ?>;
    }
    <?php echo $prefix; ?>
    .pagination > .disabled > span,
    <?php echo $prefix; ?> .pagination > .disabled > a,
    <?php echo $prefix; ?> .pagination > .disabled > a:hover,
    <?php echo $prefix; ?> .pagination > .disabled > a:focus {
      <?php echo $pagination_font_size; ?>
      font-family: <?php echo $pagination_font_family; ?>;
      font-weight: <?php echo $pagination_font_weight; ?>;
      color: <?php echo $pagination_content_color; ?>;
      background-color: <?php echo $pagination_bg_color; ?>;
      border-color: <?php echo $pagination_border_color; ?>;
    }
    <?php

    return ob_get_clean();
  }

  private function pager() {
    $prefix = $this->prefix;
    $theme = $this->theme;

    $pager_content_color = $theme->pager_content_color;
    $pager_font_size = $theme->pager_font_size ? 'font-size: ' . $theme->pager_font_size . ';' : '';
    $pager_font_family = $theme->pager_font_family ? $theme->pager_font_family : 'inherit';
    $pager_font_weight = $theme->pager_font_weight ? $theme->pager_font_weight : 'inherit';

    $pager_bg_color = $theme->pager_bg_color;
    $pager_border_color = $theme->pager_border_color;
    $pager_hover_content_color = WDFColorUtils::adjust_brightness($pager_content_color, -15);
    $pager_hover_bg_color = WDFColorUtils::adjust_brightness($pager_bg_color, -8);

    ob_start();
    ?>
    <?php echo $prefix; ?>
    .pager li > a,
    <?php echo $prefix; ?> .pager li > span {
      <?php echo $pager_font_size; ?>
      font-family: <?php echo $pager_font_family; ?>;
      font-weight: <?php echo $pager_font_weight; ?>;
      color: <?php echo $pager_content_color; ?>;
      background-color: <?php echo $pager_bg_color; ?>;
      border-color: <?php echo $pager_border_color; ?>;
    }
    <?php echo $prefix; ?>
    .pager li > a:hover,
    <?php echo $prefix; ?> .pager li > a:focus {
      color: <?php echo $pager_hover_content_color; ?>;
      background-color: <?php echo $pager_hover_bg_color; ?>;
    }
    <?php echo $prefix; ?>
    .pager .disabled > a,
    <?php echo $prefix; ?> .pager .disabled > a:hover,
    <?php echo $prefix; ?> .pager .disabled > a:focus,
    <?php echo $prefix; ?> .pager .disabled > span {
      background-color: <?php echo $pager_bg_color; ?>;
    }
    <?php

    return ob_get_clean();
  }

  private function single_item() {
    $prefix = $this->prefix;
    $theme = $this->theme;

    $product_name_color = $theme->product_name_color;
    $product_name_font_size = $theme->product_name_font_size ? 'font-size: ' . $theme->product_name_font_size . ';' : '';
    $product_name_font_family = $theme->product_name_font_family ? $theme->product_name_font_family : 'inherit';
    $product_name_font_weight = $theme->product_name_font_weight ? $theme->product_name_font_weight : 'inherit';

    $product_category_color = $theme->product_category_color;
    $product_manufacturer_color = $theme->product_manufacturer_color;
    $product_model_color = $theme->product_model_color;
    $product_procreator_font_size = $theme->product_procreator_font_size ? 'font-size: ' . $theme->product_procreator_font_size . ';' : '';
    $product_procreator_font_family = $theme->product_procreator_font_family ? $theme->product_procreator_font_family : 'inherit';
    $product_procreator_font_weight = $theme->product_procreator_font_weight ? $theme->product_procreator_font_weight : 'inherit';

    $product_price_color = $theme->product_price_color;
    $product_price_font_size = $theme->product_price_font_size ? 'font-size: ' . $theme->product_price_font_size . ';' : '';
    $product_price_font_family = $theme->product_price_font_family ? $theme->product_price_font_family : 'inherit';
    $product_price_font_weight = $theme->product_price_font_weight ? $theme->product_price_font_weight : 'inherit';

    $product_market_price_color = $theme->product_market_price_color;
    $product_market_price_font_size = $theme->product_market_price_font_size ? 'font-size: ' . $theme->product_market_price_font_size . ';' : '';
    $product_market_price_font_family = $theme->product_market_price_font_family ? $theme->product_market_price_font_family : 'inherit';
    $product_market_price_font_weight = $theme->product_market_price_font_weight ? $theme->product_market_price_font_weight : 'inherit';

    $product_codes_color = $theme->product_code_color;
    $product_code_font_size = $theme->product_code_font_size ? 'font-size: ' . $theme->product_code_font_size . ';' : '';
    $product_code_font_family = $theme->product_code_font_family ? $theme->product_code_font_family : 'inherit';
    $product_code_font_weight = $theme->product_code_font_weight ? $theme->product_code_font_weight : 'inherit';

    $product_description_color = $theme->product_description_color;
    $product_description_font_size = $theme->product_description_font_size ? 'font-size: ' . $theme->product_description_font_size . ';' : '';
    $product_description_font_family = $theme->product_description_font_family ? $theme->product_description_font_family : 'inherit';
    $product_description_font_weight = $theme->product_description_font_weight ? $theme->product_description_font_weight : 'inherit';

    ob_start();
    ?>
    <?php echo $prefix; ?> .wd_shop_product_name,
    <?php echo $prefix; ?> .wd_shop_product_name:hover,
    <?php echo $prefix; ?> .wd_shop_product_name:focus,
    <?php echo $prefix; ?> .wd_shop_product_name.disabled,
    <?php echo $prefix; ?> .wd_shop_product_name[disabled],
    <?php echo $prefix; ?> .wd_shop_product_name.disabled:hover,
    <?php echo $prefix; ?> .wd_shop_product_name[disabled]:hover,
    <?php echo $prefix; ?> .wd_shop_product_name.disabled:focus,
    <?php echo $prefix; ?> .wd_shop_product_name[disabled]:focus {
      color: <?php echo $product_name_color; ?>;
      <?php echo $product_name_font_size; ?>
      font-family: <?php echo $product_name_font_family; ?>;
      font-weight: <?php echo $product_name_font_weight; ?>;
    }
    <?php echo $prefix; ?> .wd_shop_product_category,
    <?php echo $prefix; ?> .wd_shop_product_category:hover,
    <?php echo $prefix; ?> .wd_shop_product_category:focus,
    <?php echo $prefix; ?> .wd_shop_product_category.disabled,
    <?php echo $prefix; ?> .wd_shop_product_category[disabled],
    <?php echo $prefix; ?> .wd_shop_product_category.disabled:hover,
    <?php echo $prefix; ?> .wd_shop_product_category[disabled]:hover,
    <?php echo $prefix; ?> .wd_shop_product_category.disabled:focus,
    <?php echo $prefix; ?> .wd_shop_product_category[disabled]:focus,
    <?php echo $prefix; ?> .wd_shop_product_category_name,
    <?php echo $prefix; ?> .wd_shop_product_category_name:hover,
    <?php echo $prefix; ?> .wd_shop_product_category_name:focus,
    <?php echo $prefix; ?> .wd_shop_product_category_name.disabled,
    <?php echo $prefix; ?> .wd_shop_product_category_name[disabled],
    <?php echo $prefix; ?> .wd_shop_product_category_name.disabled:hover,
    <?php echo $prefix; ?> .wd_shop_product_category_name[disabled]:hover,
    <?php echo $prefix; ?> .wd_shop_product_category_name.disabled:focus,
    <?php echo $prefix; ?> .wd_shop_product_category_name[disabled]:focus {
      color: <?php echo $product_category_color; ?>;
      <?php echo $product_procreator_font_size; ?>
      font-family: <?php echo $product_procreator_font_family; ?>;
      font-weight: <?php echo $product_procreator_font_weight; ?>;
    }
    <?php echo $prefix; ?> .wd_shop_product_manufacturer,
    <?php echo $prefix; ?> .wd_shop_product_manufacturer:hover,
    <?php echo $prefix; ?> .wd_shop_product_manufacturer:focus,
    <?php echo $prefix; ?> .wd_shop_product_manufacturer.disabled,
    <?php echo $prefix; ?> .wd_shop_product_manufacturer[disabled],
    <?php echo $prefix; ?> .wd_shop_product_manufacturer.disabled:hover,
    <?php echo $prefix; ?> .wd_shop_product_manufacturer[disabled]:hover,
    <?php echo $prefix; ?> .wd_shop_product_manufacturer.disabled:focus,
    <?php echo $prefix; ?> .wd_shop_product_manufacturer[disabled]:focus,
    <?php echo $prefix; ?> .wd_shop_product_manufacturer_name,
    <?php echo $prefix; ?> .wd_shop_product_manufacturer_name:hover,
    <?php echo $prefix; ?> .wd_shop_product_manufacturer_name:focus,
    <?php echo $prefix; ?> .wd_shop_product_manufacturer_name.disabled,
    <?php echo $prefix; ?> .wd_shop_product_manufacturer_name[disabled],
    <?php echo $prefix; ?> .wd_shop_product_manufacturer_name.disabled:hover,
    <?php echo $prefix; ?> .wd_shop_product_manufacturer_name[disabled]:hover,
    <?php echo $prefix; ?> .wd_shop_product_manufacturer_name.disabled:focus,
    <?php echo $prefix; ?> .wd_shop_product_manufacturer_name[disabled]:focus {
      color: <?php echo $product_manufacturer_color; ?>;
      <?php echo $product_procreator_font_size; ?>
      font-family: <?php echo $product_procreator_font_family; ?>;
      font-weight: <?php echo $product_procreator_font_weight; ?>;
    }
    <?php echo $prefix; ?> .wd_shop_product_price,
    <?php echo $prefix; ?> .wd_shop_product_price:hover,
    <?php echo $prefix; ?> .wd_shop_product_price:focus,
    <?php echo $prefix; ?> .wd_shop_product_price.disabled,
    <?php echo $prefix; ?> .wd_shop_product_price[disabled],
    <?php echo $prefix; ?> .wd_shop_product_price.disabled:hover,
    <?php echo $prefix; ?> .wd_shop_product_price[disabled]:hover,
    <?php echo $prefix; ?> .wd_shop_product_price.disabled:focus,
    <?php echo $prefix; ?> .wd_shop_product_price[disabled]:focus {
      color: <?php echo $product_price_color; ?>;
      <?php echo $product_price_font_size; ?>
      font-family: <?php echo $product_price_font_family; ?>;
      font-weight: <?php echo $product_price_font_weight; ?>;
    }
    <?php echo $prefix; ?> .wd_shop_product_price_small,
    <?php echo $prefix; ?> .wd_shop_product_price_small:hover,
    <?php echo $prefix; ?> .wd_shop_product_price_small:focus,
    <?php echo $prefix; ?> .wd_shop_product_price_small.disabled,
    <?php echo $prefix; ?> .wd_shop_product_price_small[disabled],
    <?php echo $prefix; ?> .wd_shop_product_price_small.disabled:hover,
    <?php echo $prefix; ?> .wd_shop_product_price_small[disabled]:hover,
    <?php echo $prefix; ?> .wd_shop_product_price_small.disabled:focus,
    <?php echo $prefix; ?> .wd_shop_product_price_small[disabled]:focus {
      color: <?php echo $product_price_color; ?>;
      font-family: <?php echo $product_price_font_family; ?>;
      font-weight: <?php echo $product_price_font_weight; ?>;
    }
    <?php echo $prefix; ?> .wd_shop_product_market_price,
    <?php echo $prefix; ?> .wd_shop_product_market_price:hover,
    <?php echo $prefix; ?> .wd_shop_product_market_price:focus,
    <?php echo $prefix; ?> .wd_shop_product_market_price.disabled,
    <?php echo $prefix; ?> .wd_shop_product_market_price[disabled],
    <?php echo $prefix; ?> .wd_shop_product_market_price.disabled:hover,
    <?php echo $prefix; ?> .wd_shop_product_market_price[disabled]:hover,
    <?php echo $prefix; ?> .wd_shop_product_market_price.disabled:focus,
    <?php echo $prefix; ?> .wd_shop_product_market_price[disabled]:focus {
      color: <?php echo $product_market_price_color; ?>;
      <?php echo $product_market_price_font_size; ?>
      font-family: <?php echo $product_market_price_font_family; ?>;
      font-weight: <?php echo $product_market_price_font_weight; ?>;
    }
    <?php echo $prefix; ?> .wd_shop_product_model,
    <?php echo $prefix; ?> .wd_shop_product_model:hover,
    <?php echo $prefix; ?> .wd_shop_product_model:focus,
    <?php echo $prefix; ?> .wd_shop_product_model.disabled,
    <?php echo $prefix; ?> .wd_shop_product_model[disabled],
    <?php echo $prefix; ?> .wd_shop_product_model.disabled:hover,
    <?php echo $prefix; ?> .wd_shop_product_model[disabled]:hover,
    <?php echo $prefix; ?> .wd_shop_product_model.disabled:focus,
    <?php echo $prefix; ?> .wd_shop_product_model[disabled]:focus,
    <?php echo $prefix; ?> .wd_shop_product_model_name,
    <?php echo $prefix; ?> .wd_shop_product_model_name:hover,
    <?php echo $prefix; ?> .wd_shop_product_model_name:focus,
    <?php echo $prefix; ?> .wd_shop_product_model_name.disabled,
    <?php echo $prefix; ?> .wd_shop_product_model_name[disabled],
    <?php echo $prefix; ?> .wd_shop_product_model_name.disabled:hover,
    <?php echo $prefix; ?> .wd_shop_product_model_name[disabled]:hover,
    <?php echo $prefix; ?> .wd_shop_product_model_name.disabled:focus,
    <?php echo $prefix; ?> .wd_shop_product_model_name[disabled]:focus {
      color: <?php echo $product_model_color; ?>;
      <?php echo $product_procreator_font_size; ?>
      font-family: <?php echo $product_procreator_font_family; ?>;
      font-weight: <?php echo $product_procreator_font_weight; ?>;
    }
    <?php echo $prefix; ?> .wd_shop_product_codes,
    <?php echo $prefix; ?> .wd_shop_product_codes:hover,
    <?php echo $prefix; ?> .wd_shop_product_codes:focus,
    <?php echo $prefix; ?> .wd_shop_product_codes.disabled,
    <?php echo $prefix; ?> .wd_shop_product_codes[disabled],
    <?php echo $prefix; ?> .wd_shop_product_codes.disabled:hover,
    <?php echo $prefix; ?> .wd_shop_product_codes[disabled]:hover,
    <?php echo $prefix; ?> .wd_shop_product_codes.disabled:focus,
    <?php echo $prefix; ?> .wd_shop_product_codes[disabled]:focus,
    <?php echo $prefix; ?> .wd_shop_product_codes_name,
    <?php echo $prefix; ?> .wd_shop_product_codes_name:hover,
    <?php echo $prefix; ?> .wd_shop_product_codes_name:focus,
    <?php echo $prefix; ?> .wd_shop_product_codes_name.disabled,
    <?php echo $prefix; ?> .wd_shop_product_codes_name[disabled],
    <?php echo $prefix; ?> .wd_shop_product_codes_name.disabled:hover,
    <?php echo $prefix; ?> .wd_shop_product_codes_name[disabled]:hover,
    <?php echo $prefix; ?> .wd_shop_product_codes_name.disabled:focus,
    <?php echo $prefix; ?> .wd_shop_product_codes_name[disabled]:focus {
      color: <?php echo $product_codes_color; ?>;
      <?php echo $product_code_font_size; ?>
      font-family: <?php echo $product_code_font_family; ?>;
      font-weight: <?php echo $product_code_font_weight; ?>;
    }
    <?php echo $prefix; ?> .wd_shop_product_description,
    <?php echo $prefix; ?> .wd_shop_product_description:hover,
    <?php echo $prefix; ?> .wd_shop_product_description:focus,
    <?php echo $prefix; ?> .wd_shop_product_description.disabled,
    <?php echo $prefix; ?> .wd_shop_product_description[disabled],
    <?php echo $prefix; ?> .wd_shop_product_description.disabled:hover,
    <?php echo $prefix; ?> .wd_shop_product_description[disabled]:hover,
    <?php echo $prefix; ?> .wd_shop_product_description.disabled:focus,
    <?php echo $prefix; ?> .wd_shop_product_description[disabled]:focus {
      color: <?php echo $product_description_color; ?>;
      <?php echo $product_description_font_size; ?>
      font-family: <?php echo $product_description_font_family; ?>;
      font-weight: <?php echo $product_description_font_weight; ?>;
    }
    <?php echo $prefix; ?> .wd_shop_category_name,
    <?php echo $prefix; ?> .wd_shop_category_name:hover,
    <?php echo $prefix; ?> .wd_shop_category_name:focus,
    <?php echo $prefix; ?> .wd_shop_category_name.disabled,
    <?php echo $prefix; ?> .wd_shop_category_name[disabled],
    <?php echo $prefix; ?> .wd_shop_category_name.disabled:hover,
    <?php echo $prefix; ?> .wd_shop_category_name[disabled]:hover,
    <?php echo $prefix; ?> .wd_shop_category_name.disabled:focus,
    <?php echo $prefix; ?> .wd_shop_category_name[disabled]:focus {
      color: <?php echo $product_name_color; ?>;
      <?php echo $product_name_font_size; ?>
      font-family: <?php echo $product_name_font_family; ?>;
      font-weight: <?php echo $product_name_font_weight; ?>;
    }
    <?php echo $prefix; ?> .wd_shop_category_description,
    <?php echo $prefix; ?> .wd_shop_category_description:hover,
    <?php echo $prefix; ?> .wd_shop_category_description:focus,
    <?php echo $prefix; ?> .wd_shop_category_description.disabled,
    <?php echo $prefix; ?> .wd_shop_category_description[disabled],
    <?php echo $prefix; ?> .wd_shop_category_description.disabled:hover,
    <?php echo $prefix; ?> .wd_shop_category_description[disabled]:hover,
    <?php echo $prefix; ?> .wd_shop_category_description.disabled:focus,
    <?php echo $prefix; ?> .wd_shop_category_description[disabled]:focus {
      color: <?php echo $product_description_color; ?>;
      <?php echo $product_description_font_size; ?>
      font-family: <?php echo $product_description_font_family; ?>;
      font-weight: <?php echo $product_description_font_weight; ?>;
    }
    <?php echo $prefix; ?> .wd_shop_manufacturer_name,
    <?php echo $prefix; ?> .wd_shop_manufacturer_name:hover,
    <?php echo $prefix; ?> .wd_shop_manufacturer_name:focus,
    <?php echo $prefix; ?> .wd_shop_manufacturer_name.disabled,
    <?php echo $prefix; ?> .wd_shop_manufacturer_name[disabled],
    <?php echo $prefix; ?> .wd_shop_manufacturer_name.disabled:hover,
    <?php echo $prefix; ?> .wd_shop_manufacturer_name[disabled]:hover,
    <?php echo $prefix; ?> .wd_shop_manufacturer_name.disabled:focus,
    <?php echo $prefix; ?> .wd_shop_manufacturer_name[disabled]:focus {
      color: <?php echo $product_name_color; ?>;
      <?php echo $product_name_font_size; ?>
      font-family: <?php echo $product_name_font_family; ?>;
      font-weight: <?php echo $product_name_font_weight; ?>;
    }
    <?php echo $prefix; ?> .wd_shop_manufacturer_description,
    <?php echo $prefix; ?> .wd_shop_manufacturer_description:hover,
    <?php echo $prefix; ?> .wd_shop_manufacturer_description:focus,
    <?php echo $prefix; ?> .wd_shop_manufacturer_description.disabled,
    <?php echo $prefix; ?> .wd_shop_manufacturer_description[disabled],
    <?php echo $prefix; ?> .wd_shop_manufacturer_description.disabled:hover,
    <?php echo $prefix; ?> .wd_shop_manufacturer_description[disabled]:hover,
    <?php echo $prefix; ?> .wd_shop_manufacturer_description.disabled:focus,
    <?php echo $prefix; ?> .wd_shop_manufacturer_description[disabled]:focus {
      color: <?php echo $product_description_color; ?>;
      <?php echo $product_description_font_size; ?>
      font-family: <?php echo $product_description_font_family; ?>;
      font-weight: <?php echo $product_description_font_weight; ?>;
    }
    <?php

    return ob_get_clean();
  }

  private function multiple_items() {
    $prefix = $this->prefix;
    $theme = $this->theme;

    $product_name_color = $theme->multiple_product_name_color;
    $product_name_font_size = $theme->multiple_product_name_font_size ? 'font-size: ' . $theme->multiple_product_name_font_size . ';' : '';
    $product_name_font_family = $theme->multiple_product_name_font_family ? $theme->multiple_product_name_font_family : 'inherit';
    $product_name_font_weight = $theme->multiple_product_name_font_weight ? $theme->multiple_product_name_font_weight : 'inherit';

    $product_price_color = $theme->multiple_product_price_color;
    $product_price_font_size = $theme->multiple_product_price_font_size ? 'font-size: ' . $theme->multiple_product_price_font_size . ';' : '';
    $product_price_font_family = $theme->multiple_product_price_font_family ? $theme->multiple_product_price_font_family : 'inherit';
    $product_price_font_weight = $theme->multiple_product_price_font_weight ? $theme->multiple_product_price_font_weight : 'inherit';

    $product_market_price_color = $theme->multiple_product_market_price_color;
    $product_market_price_font_size = $theme->multiple_product_market_price_font_size ? 'font-size: ' . $theme->multiple_product_market_price_font_size . ';' : '';
    $product_market_price_font_family = $theme->multiple_product_market_price_font_family ? $theme->multiple_product_market_price_font_family : 'inherit';
    $product_market_price_font_weight = $theme->multiple_product_market_price_font_weight ? $theme->multiple_product_market_price_font_weight : 'inherit';

    $product_description_color = $theme->multiple_product_description_color;
    $product_description_font_size = $theme->multiple_product_description_font_size ? 'font-size: ' . $theme->multiple_product_description_font_size . ';' : '';
    $product_description_font_family = $theme->multiple_product_description_font_family ? $theme->multiple_product_description_font_family : 'inherit';
    $product_description_font_weight = $theme->multiple_product_description_font_weight ? $theme->multiple_product_description_font_weight : 'inherit';

    ob_start();
    ?>
    <?php echo $prefix; ?> .wd_shop_product_name_all,
    <?php echo $prefix; ?> .wd_shop_product_name_all:hover,
    <?php echo $prefix; ?> .wd_shop_product_name_all:focus,
    <?php echo $prefix; ?> .wd_shop_product_name_all.disabled,
    <?php echo $prefix; ?> .wd_shop_product_name_all[disabled],
    <?php echo $prefix; ?> .wd_shop_product_name_all.disabled:hover,
    <?php echo $prefix; ?> .wd_shop_product_name_all[disabled]:hover,
    <?php echo $prefix; ?> .wd_shop_product_name_all.disabled:focus,
    <?php echo $prefix; ?> .wd_shop_product_name_all[disabled]:focus {
      color: <?php echo $product_name_color; ?>;
      <?php echo $product_name_font_size; ?>
      font-family: <?php echo $product_name_font_family; ?>;
      font-weight: <?php echo $product_name_font_weight; ?>;
    }
    <?php echo $prefix; ?> .wd_shop_product_price_all,
    <?php echo $prefix; ?> .wd_shop_product_price_all:hover,
    <?php echo $prefix; ?> .wd_shop_product_price_all:focus,
    <?php echo $prefix; ?> .wd_shop_product_price_all.disabled,
    <?php echo $prefix; ?> .wd_shop_product_price_all[disabled],
    <?php echo $prefix; ?> .wd_shop_product_price_all.disabled:hover,
    <?php echo $prefix; ?> .wd_shop_product_price_all[disabled]:hover,
    <?php echo $prefix; ?> .wd_shop_product_price_all.disabled:focus,
    <?php echo $prefix; ?> .wd_shop_product_price_all[disabled]:focus {
      color: <?php echo $product_price_color; ?>;
      <?php echo $product_price_font_size; ?>
      font-family: <?php echo $product_price_font_family; ?>;
      font-weight: <?php echo $product_price_font_weight; ?>;
    }
    <?php echo $prefix; ?> .wd_shop_product_price_all_small,
    <?php echo $prefix; ?> .wd_shop_product_price_all_small:hover,
    <?php echo $prefix; ?> .wd_shop_product_price_all_small:focus,
    <?php echo $prefix; ?> .wd_shop_product_price_all_small.disabled,
    <?php echo $prefix; ?> .wd_shop_product_price_all_small[disabled],
    <?php echo $prefix; ?> .wd_shop_product_price_all_small.disabled:hover,
    <?php echo $prefix; ?> .wd_shop_product_price_all_small[disabled]:hover,
    <?php echo $prefix; ?> .wd_shop_product_price_all_small.disabled:focus,
    <?php echo $prefix; ?> .wd_shop_product_price_all_small[disabled]:focus {
      color: <?php echo $product_price_color; ?>;
      font-family: <?php echo $product_price_font_family; ?>;
      font-weight: <?php echo $product_price_font_weight; ?>;
    }
    <?php echo $prefix; ?> .wd_shop_product_market_price_all,
    <?php echo $prefix; ?> .wd_shop_product_market_price_all:hover,
    <?php echo $prefix; ?> .wd_shop_product_market_price_all:focus,
    <?php echo $prefix; ?> .wd_shop_product_market_price_all.disabled,
    <?php echo $prefix; ?> .wd_shop_product_market_price_all[disabled],
    <?php echo $prefix; ?> .wd_shop_product_market_price_all.disabled:hover,
    <?php echo $prefix; ?> .wd_shop_product_market_price_all[disabled]:hover,
    <?php echo $prefix; ?> .wd_shop_product_market_price_all.disabled:focus,
    <?php echo $prefix; ?> .wd_shop_product_market_price_all[disabled]:focus {
      color: <?php echo $product_market_price_color; ?>;
      <?php echo $product_market_price_font_size; ?>
      font-family: <?php echo $product_market_price_font_family; ?>;
      font-weight: <?php echo $product_market_price_font_weight; ?>;
    }
    <?php echo $prefix; ?> .wd_shop_product_description_all,
    <?php echo $prefix; ?> .wd_shop_product_description_all:hover,
    <?php echo $prefix; ?> .wd_shop_product_description_all:focus,
    <?php echo $prefix; ?> .wd_shop_product_description_all.disabled,
    <?php echo $prefix; ?> .wd_shop_product_description_all[disabled],
    <?php echo $prefix; ?> .wd_shop_product_description_all.disabled:hover,
    <?php echo $prefix; ?> .wd_shop_product_description_all[disabled]:hover,
    <?php echo $prefix; ?> .wd_shop_product_description_all.disabled:focus,
    <?php echo $prefix; ?> .wd_shop_product_description_all[disabled]:focus {
      color: <?php echo $product_description_color; ?>;
      <?php echo $product_description_font_size; ?>
      font-family: <?php echo $product_description_font_family; ?>;
      font-weight: <?php echo $product_description_font_weight; ?>;
    }
    <?php echo $prefix; ?> .wd_shop_category_name_all,
    <?php echo $prefix; ?> .wd_shop_category_name_all:hover,
    <?php echo $prefix; ?> .wd_shop_category_name_all:focus,
    <?php echo $prefix; ?> .wd_shop_category_name_all.disabled,
    <?php echo $prefix; ?> .wd_shop_category_name_all[disabled],
    <?php echo $prefix; ?> .wd_shop_category_name_all.disabled:hover,
    <?php echo $prefix; ?> .wd_shop_category_name_all[disabled]:hover,
    <?php echo $prefix; ?> .wd_shop_category_name_all.disabled:focus,
    <?php echo $prefix; ?> .wd_shop_category_name_all[disabled]:focus {
      color: <?php echo $product_name_color; ?>;
      <?php echo $product_name_font_size; ?>
      font-family: <?php echo $product_name_font_family; ?>;
      font-weight: <?php echo $product_name_font_weight; ?>;
    }
    <?php echo $prefix; ?> .wd_shop_category_description_all,
    <?php echo $prefix; ?> .wd_shop_category_description_all:hover,
    <?php echo $prefix; ?> .wd_shop_category_description_all:focus,
    <?php echo $prefix; ?> .wd_shop_category_description_all.disabled,
    <?php echo $prefix; ?> .wd_shop_category_description_all[disabled],
    <?php echo $prefix; ?> .wd_shop_category_description_all.disabled:hover,
    <?php echo $prefix; ?> .wd_shop_category_description_all[disabled]:hover,
    <?php echo $prefix; ?> .wd_shop_category_description_all.disabled:focus,
    <?php echo $prefix; ?> .wd_shop_category_description_all[disabled]:focus {
      color: <?php echo $product_description_color; ?>;
      <?php echo $product_description_font_size; ?>
      font-family: <?php echo $product_description_font_family; ?>;
      font-weight: <?php echo $product_description_font_weight; ?>;
    }
    <?php echo $prefix; ?> .wd_shop_manufacturer_name_all,
    <?php echo $prefix; ?> .wd_shop_manufacturer_name_all:hover,
    <?php echo $prefix; ?> .wd_shop_manufacturer_name_all:focus,
    <?php echo $prefix; ?> .wd_shop_manufacturer_name_all.disabled,
    <?php echo $prefix; ?> .wd_shop_manufacturer_name_all[disabled],
    <?php echo $prefix; ?> .wd_shop_manufacturer_name_all.disabled:hover,
    <?php echo $prefix; ?> .wd_shop_manufacturer_name_all[disabled]:hover,
    <?php echo $prefix; ?> .wd_shop_manufacturer_name_all.disabled:focus,
    <?php echo $prefix; ?> .wd_shop_manufacturer_name_all[disabled]:focus {
      color: <?php echo $product_name_color; ?>;
      <?php echo $product_name_font_size; ?>
      font-family: <?php echo $product_name_font_family; ?>;
      font-weight: <?php echo $product_name_font_weight; ?>;
    }
    <?php echo $prefix; ?> .wd_shop_manufacturer_description_all,
    <?php echo $prefix; ?> .wd_shop_manufacturer_description_all:hover,
    <?php echo $prefix; ?> .wd_shop_manufacturer_description_all:focus,
    <?php echo $prefix; ?> .wd_shop_manufacturer_description_all.disabled,
    <?php echo $prefix; ?> .wd_shop_manufacturer_description_all[disabled],
    <?php echo $prefix; ?> .wd_shop_manufacturer_description_all.disabled:hover,
    <?php echo $prefix; ?> .wd_shop_manufacturer_description_all[disabled]:hover,
    <?php echo $prefix; ?> .wd_shop_manufacturer_description_all.disabled:focus,
    <?php echo $prefix; ?> .wd_shop_manufacturer_description_all[disabled]:focus {
      color: <?php echo $product_description_color; ?>;
      <?php echo $product_description_font_size; ?>
      font-family: <?php echo $product_description_font_family; ?>;
      font-weight: <?php echo $product_description_font_weight; ?>;
    }
    <?php

    return ob_get_clean();
  }
}
