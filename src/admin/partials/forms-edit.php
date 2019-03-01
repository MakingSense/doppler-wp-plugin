<?php echo "<script>console.log(".json_encode($form).");</script>"; ?>

<div class="dplr">
  <form class="" method="post">
    <input type="hidden" name="create" value="true">
      <div class="grid">
        <div class="col-4-5 panel nopd">
          <div class="panel-header">
            <h2><?php _e('Form basic information', 'doppler-form')?></h2>
          </div>
          <div class="panel-body">
            <div class="dplr_input_section">
              <label for="name"><?php _e('Form name', 'doppler-form')?></label>
              <input type="text" name="name" placeholder="" value="<?php echo $form->name; ?>" required/>
            </div>
            <div class="dplr_input_section">
            <label for="title"><?php _e('Form title', 'doppler-form')?></label>
            <input type="text" name="title" placeholder="" value="<?php echo $form->title; ?>"/>
          </div>
            <div class="dplr_input_section">
              <label for="list_id"><?php _e('Doppler list', 'doppler-form')?></label>
              <select class="" name="list_id" id="list-id" required>
                <option value=""></option>
                <?php for ($i=0; $i < count($dplr_lists); $i++) { ?>
                  <option <?php echo $form->list_id == $dplr_lists[$i]->listId ? 'selected="selected"' : ''; ?> value="<?php echo $dplr_lists[$i]->listId; ?>">
                    <?php echo $dplr_lists[$i]->name; ?>
                  </option>
                <?php } ?>
                </select>
            </div>
          </div>
        </div>
      </div>
      <div class="grid">
          <div class="col-4-5 panel nopd">
            <div class="panel-header">
              <h2><?php _e('Form fields', 'doppler-form')?></h2>
            </div>
            <div class="panel-body grid">
              <div class="col-1-2 dplr_input_section">
                <label for="list_id"><?php _e('Select the fields to include', 'doppler-form')?></label>
                <select id="fieldList" class="" name="">
                  <option value="" ><?php _e('Select the fields you want to add to your form', 'doppler-form')?></option>
                </select>
              </div>
              <div class="col-1-2">
                <ul class="sortable accordion" id="formFields">

                </ul>
              </div>
            </div>
          </div>
      </div>
      <div class="grid">
        <div class="col-4-5 panel nopd">
          <div class="panel-header">
            <h2><?php _e('Form settings', 'doppler-form')?></h2>
          </div>
          <div class="panel-body grid">
            <div class="dplr_input_section">
              <label for="submit_text"><?php _e('Button text', 'doppler-form')?></label>
              <input type="text" name="settings[button_text]" value="<?php echo $form->settings["button_text"] ?>" placeholder="<?php _e('Submit', 'doppler-form')?>"/>
            </div>
            <div class="dplr_input_section">
              <label for="submit_text"><?php _e('Confirmation message', 'doppler-form')?></label>
              <input type="text" name="settings[message_success]" value="<?=$form->settings["message_success"] ?>"  placeholder="<?php _e('Thanks for subscribing', 'doppler-form')?>" />
            </div>
            <div class="dplr_input_section">
              <label for="settings[button_position]"><?php _e('Button position', 'doppler-form')?></label>
              <?php $button_position = $form->settings["button_position"]; ?>
              <select class="" name="settings[button_position]">
                <option <?php if($button_position == 'left') echo 'selected="selected"';?> value="left"><?php _e('Left', 'doppler-form')?></option>
                <option <?php if($button_position == 'center') echo 'selected="selected"';?> value="center"><?php _e('Center', 'doppler-form')?></option>
                <option <?php if($button_position == 'right') echo 'selected="selected"';?> value="right"><?php _e('Right', 'doppler-form')?></option>
                <option <?php if($button_position == 'fill') echo 'selected="selected"';?> value="fill"><?php _e('Fill', 'doppler-form')?></option>
              </select>
            </div>
            <div class="dplr_input_section">
              <label for="settings[change_button_bg]"><?php _e('Button background color', 'doppler-form')?></label>
              <?php _e('Use my theme\'s default color', 'doppler-form')?>
                <input type="radio" name="settings[change_button_bg]" class="dplr-toggle-selector" value="no" <?php if(!isset($form->settings['change_button_bg']) || $form->settings['change_button_bg']==='no') echo 'checked'?>>&nbsp; 
              <?php _e('Choose another color', 'doppler-form')?>
                <input type="radio" name="settings[change_button_bg]" class="dplr-toggle-selector" value="yes" <?php if($form->settings['change_button_bg']==='yes') echo 'checked'?>> 
              <input class="color-selector" type="hidden" name="settings[button_color]" value="<?php echo $form->settings["button_color"]; ?>">   
            </div>
            <div class="dplr_input_section">
              <label for="settings[use_consent_field]"><?php _e('¿Use consent field?', 'doppler-form')?></label>
              <?php _e('Yes', 'doppler-form')?>
                <input type="radio" name="settings[use_consent_field]" class="dplr-toggle-consent" value="yes" <?php if($form->settings['use_consent_field']==='yes') echo 'checked'?>>&nbsp; 
              <?php _e('No', 'doppler-form')?>
                <input type="radio" name="settings[use_consent_field]" class="dplr-toggle-consent" value="no" <?php if($form->settings['use_consent_field']!=='yes') echo 'checked'?>> 
            </div>
          </div>
        </div>
      </div>
      <div class="grid" id="dplr_consent_section" <?= ($form->settings['use_consent_field']==='yes')? 'style="display:block"' : 'style="display:none"'; ?>>
        <div class="col-4-5 panel nopd">
          <div class="panel-header">
            <h2><?php _e('Consent field settings', 'doppler-form')?></h2>
          </div>
          <div class="panel-body grid">
              <div class="dplr_input_section">
                <label for="settings[consent_field_text]"><?php _e('Checkbox label', 'doppler-form')?></label>
                <input type="text" name="settings[consent_field_text]" value="<?=$form->settings["consent_field_text"] ?>" placeholder="<?php _e("I've read and accept the privace policy", "doppler-form")?>"/>
              </div>
              <div class="dplr_input_section">
              <label for="settings[consent_field_url]">
                <?php _e('Enter the URL of your privacy policy. Do you want to know more? Press ', 'doppler-form'); ?> 
                <?= '<a href="'.esc_url('https://help.fromdoppler.com/es/reglamento-general-de-proteccion-de-datos').'" target="blank">'.__('HELP','doppler-form').'</a>'?>
              </label>
              <input type="url" name="settings[consent_field_url]" pattern="https?://.+" value="<?=$form->settings["consent_field_url"] ?>" placeholder="<?php esc_html_e("https://www.mysite.com", "doppler-form")?>"/>
            </div>
          </div>
        </div>
      </div>
    <input type="submit" value="<?php _e('Save', 'doppler-form')?>" class="button button-primary"> <a href="<?php echo admin_url('admin.php?page=doppler_forms_submenu_forms')?>"  class="button button-primary"><?php _e('Cancel', 'doppler-form')?></a>
  </form>
</div>
<script type="text/javascript">
var all_fields = <?php echo json_encode($dplr_fields); ?>;
all_fields = jQuery.grep(all_fields, function(el, idx) {return el.type == "consent"}, true)
var form_fields = <?php echo json_encode($fields); ?>;
var fieldsView = new FormFieldsView(all_fields, form_fields, jQuery("#fieldList"), jQuery("#formFields"));
jQuery(".color-selector").colorpicker({color: "<?php echo $form->settings["button_color"]; ?>"});
</script>