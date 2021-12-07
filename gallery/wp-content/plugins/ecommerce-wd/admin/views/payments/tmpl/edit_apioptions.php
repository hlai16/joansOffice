<?php
defined('ABSPATH') || die('Access Denied');

$lists = $this->lists;
$row = $this->row;
$row_fields = $row->fields;
$fields = $row->field_types;

if (WDFInput::get("type") == 'paypalexpress') {
  echo WDFHTML::non_commercial();
  return;
}
if (WDFInput::get("type") != 'without_online_payment') { ?>
	<div id="wd_paymnet_options">
		<table class="adminlist table">
			<tbody>
				<?php foreach ($fields as $key => $field) {
					if ($field['type'] == 'radio') {
						$checked = ($row_fields->$key === '') ? 1 : $row_fields->$key;
					?>
						<tr>
							<td class="col_key">
								<label for="<?php echo $key; ?>">
									<?php _e($field['text'], 'wde'); ?>:
								</label>
							</td>
							<td class="col_value">
								<?php echo WDFHTML::wd_radio_list($key, $lists['radio'][$key], 'value', 'text', $checked, $field['attributes']);
								if (isset($field['description']) && $field['description']) {
								  ?>
                  <div class="wde_description">
                    <?php echo $field['description']; ?>
                  </div>
                  <?php
                }
								?>
							</td>
						</tr>
					<?php
          }
          elseif($field['type'] == 'select') { ?>
						<tr>
							<td class="col_key">
								<label for="<?php echo $key; ?>">
									<?php _e($field['text'], 'wde'); ?>:
								</label>
							</td>
							<td class="col_value">
								<?php echo WDFHTML::wd_select($key, $field['options'], 'value', 'text', $row_fields->$key, $field['attributes']);
                if (isset($field['description']) && $field['description']) {
                  ?>
                  <div class="wde_description">
                    <?php echo $field['description']; ?>
                  </div>
                  <?php
                }
								?>
							</td>
						</tr>
					<?php 
          }
          elseif($field['type'] == 'thumb_box') { ?>
						<tr>
							<td class="col_key">
								<label for="<?php echo $key; ?>">
									<?php _e($field['text'], 'wde'); ?>:
								</label>
							</td>
							<td class="col_value">
								<?php echo WDFHTML::jf_thumb_box('thumb_box_' . $key, false, 'images', $key); ?>
                <input type="hidden" name="<?php echo $key ?>" id="<?php echo $key ?>" value="<?php echo $row_fields->$key; ?>" />
                <script>
                  var _imageUrls<?php echo $key ?> = JSON.parse("<?php echo $row_fields->$key ? '\"' . addslashes(stripslashes(wp_get_attachment_url($row_fields->$key))) . '\"' : '[]'; ?>");
                </script>
                <?php
                if (isset($field['description']) && $field['description']) {
                  ?>
                  <div class="wde_description">
                    <?php echo $field['description']; ?>
                  </div>
                  <?php
                }
                ?>
							</td>
						</tr>
					<?php
          }
          else { ?>
						<tr>
							<td class="col_key">
								<label for="<?php echo $key; ?>"><?php _e($field['text'], 'wde'); ?>:</label>
							</td>
							<td class="col_value">
								<input type="text" name="<?php echo $key; ?>" value="<?php echo $row_fields->$key; ?>" id="<?php echo $key; ?>" <?php echo $field['attributes'];?>/>
                <?php
                if (isset($field['description']) && $field['description']) {
                  ?>
                  <div class="wde_description">
                    <?php echo $field['description']; ?>
                  </div>
                  <?php
                }
                ?>
							</td>
						</tr>
					<?php 
          }
          ?>
				<?php 
        } 
        ?>
			</tbody>
		</table>
	</div>
<?php 
}
?>
	<table class="adminlist table">
		<tbody>
			<tr>
				<td class="col_key">
					<label><?php _e('Published', 'wde'); ?>:</label>
				</td>
				<td class="col_value">
					<?php echo WDFHTML::wd_radio('published', $row->published, __('Yes', 'wde'), __('No', 'wde')); ?>
				</td>
			</tr>
		</tbody>
	</table>