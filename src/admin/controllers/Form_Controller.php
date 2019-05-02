<?php

class DPLR_Form_Controller
{
  
  private $doppler_service;

  function __construct($doppler_service)
  {
    $this->doppler_service = $doppler_service;
  }

  function comparator($object1, $object2) {
      return $object1->name > $object2->name;
  }

  function create( $form = null ) {

    if (isset($form) && count($form) > 0) {

      DPLR_Form_Model::insert(['name'=>$form['name'], 'title' => $form['title'], 'list_id' => $form['list_id']]);
      $form_id =  DPLR_Form_Model::insert_id();

      DPLR_Form_Model::setSettings($form_id, $form["settings"]);

      $field_position_counter = 1;

      $form['fields'] = isset($form['fields']) ? $form['fields'] : [];

      foreach ($form['fields'] as $key => $value) {

        $mod = ['name' => $key, 'type' => $value['type'], 'form_id' => $form_id, 'sort_order' => $field_position_counter++];
        DPLR_Field_Model::insert($mod);

        $field_id =  DPLR_Field_Model::insert_id();
        $field_settings = $value['settings'];

        DPLR_Field_Model::setSettings($field_id, $field_settings);

      }
      
      //TODO: create method redirect on controller
      echo "<script>location.href = 'admin.php?page=doppler_forms_submenu_forms&created=1';</script>";
    
    } else {
      $this->showCreateEditForm();
    }
  }

  function update($form_id, $form_to_update = NULL) {

    if (isset($form_to_update) && count($form_to_update) > 0) {

      DPLR_Form_Model::update($form_id, ['name'=>$form_to_update['name'], 'title' => $form_to_update['title'], 'list_id' => $form_to_update['list_id']]);

      DPLR_Form_Model::setSettings($form_id, $form_to_update["settings"]);

      $field_position_counter = 1;

      $form_to_update['fields'] = isset($form_to_update['fields']) ? $form_to_update['fields'] : [];

      DPLR_Field_Model::deleteWhere(['form_id' => $form_id]);

      foreach ($form_to_update['fields'] as $key => $value) {
        
        $mod = ['name' => $key, 'type' => $value['type'], 'form_id' => $form_id, 'sort_order' => $field_position_counter++];

        DPLR_Field_Model::insert($mod);

        $field_id =  DPLR_Field_Model::insert_id();

        $field_settings = $value['settings'];

        $res = DPLR_Field_Model::setSettings($field_id, $field_settings);

      }

      echo "<script>location.href = 'admin.php?page=doppler_forms_submenu_forms';</script>";
    
    } else {
      $this->showCreateEditForm($form_id);
    }
  }

  function getAll() {
    $forms = DPLR_Form_Model::getAll(false, array('id'));
		$create_form_url = admin_url( 'admin.php?page=doppler_forms_submenu_forms&action=create');
		$edit_form_url = admin_url( 'admin.php?page=doppler_forms_submenu_forms&action=edit&form_id=[FORM_ID]' );
    $delete_form_url = admin_url( 'admin.php?page=doppler_forms_submenu_forms&action=delete&form_id=[FORM_ID]' );
    
    $options = get_option('dplr_settings');
    $this->doppler_service->setCredentials(['api_key' => $options['dplr_option_apikey'], 'user_account' => $options['dplr_option_useraccount']]);
  
    $list_resource = $this->doppler_service->getResource('lists');
    $dplr_lists = $list_resource->getAllLists();
  
    if(is_array($dplr_lists)){

      foreach($dplr_lists as $k=>$v){
        if(is_array($v)):
          foreach($v as $i=>$j){
            $dplr_lists_aux[$j->listId] = trim($j->name);
          }
        endif;
      }

      $dplr_lists_arr = $dplr_lists_aux;

    }

    include plugin_dir_path( __FILE__ ) . "../partials/forms-list.php";
    
  }

  function delete($id) {
    $form = DPLR_Form_Model::delete($id);
    echo "<script>location.href = 'admin.php?page=doppler_forms_submenu_forms';</script>";
  }

  private function showCreateEditForm($form_id = NULL) {
    
    $options = get_option('dplr_settings');
    $this->doppler_service->setCredentials(['api_key' => $options['dplr_option_apikey'], 'user_account' => $options['dplr_option_useraccount']]);
    $list_resource = $this->doppler_service->getResource('lists');
    $fields_resource = $this->doppler_service->getResource('fields');

    $dplr_lists = $list_resource->getAllLists();
  
    foreach($dplr_lists as $k=>$v){
      foreach($v as $i=>$j){
        $dplr_lists_aux[] = $j;
      }
    }
    
    $dplr_lists = $dplr_lists_aux;
    
    $dplr_fields = $fields_resource->getAllFields();
    $dplr_fields = isset($dplr_fields->items) ? $dplr_fields->items : [];

    usort($dplr_fields, function($a, $b) {
      return strtolower($a->name) > strtolower($b->name);
    });

    if ($form_id != NULL) {
      $form = DPLR_Form_Model::get($form_id, true);
      $fields = DPLR_Field_Model::getBy(['form_id' => $form_id],['sort_order'], true);
      include plugin_dir_path( __FILE__ ) . "../partials/forms-edit.php";
    } else {
      include plugin_dir_path( __FILE__ ) . "../partials/forms-create.php";
    }

  }

}

?>