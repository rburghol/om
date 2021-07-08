<?php
module_load_include('inc', 'dh', 'plugins/dh.display');
module_load_include('module', 'dh');


class dHVariablePluginVarGroup extends dHVariablePluginDefault {
  // propcode = varkey of item to group 
  var $feature = FALSE;
  public function hiddenFields() {
    // this hides all field except name and code by default 
    // contained objects will dictate the display and edit visibility
    return array('pid', 'startdate', 'enddate', 'varid', 'featureid', 'entity_type', 'bundle', 'dh_link_admin_pr_condition', 'varname', 'propvalue');
  }

  public function formRowEdit(&$form, $entity) {
    parent::formRowEdit($form, $entity);
    // @todo: implement a method to show editable entries for linked
    //        props of varkey = propcode 
    //        use linked props edit form 
    //        var $attach_method = 'contained';
  }
  
  public function save(&$entity) {
    //error_log("Saving group controller");
    parent::save();
  }
  
  public function dh_getValue($entity, $ts = FALSE) {
    // Get and Render Related Variables
    $propcode_list = $this->getCodeList($entity);
    return $propcode_list;
  }
  
  public function loadGroup($entity) {
    if (!is_object($this->feature)) {
      $this->feature = $this->getParentEntity($entity);
    }
    // Get and Render Related Variables
    $criteria = array(
      'varid' => dh_varkey2varid($entity->propcode)
    );
    $this->feature->loadComponents($criteria);
  }
  
  public function getCodeList($entity) {
    $this->loadGroup($entity);
    $propcodes = array();
    // Get and Render Related Variables
    foreach (array_keys($this->feature->prop_varkey_map[$entity->propcode]) as $pname) {
      //error_log("Checking $pname " . $this->feature->dh_properties[$pname]->pid);
      $propcodes[] = $this->feature->dh_properties[$pname]->propcode;
    }
    //$propcodes = array_filter($propcodes);
    $propcode_list = implode(', ', array_unique($propcodes));
    return $propcode_list;
  }
  
  public function buildContent(&$content, &$entity, $view_mode) {
    // @todo: handle teaser mode and full mode with plugin support
    //        this won't happen till we enable at module level however, now it only 
    //        is shown when selecting "plugin" in the view mode in views
    $content['body']['#markup'] = "Grouped Property Container";
    $now = dh_handletimestamp(date('Y-m-d'));
    $args = arg();
    $content['body'] = array(
      '#type' => 'item',
    );
    // load associated props
    $propcode_list = $this->getCodeList($entity);
    
    $content['#view_mode'] = $view_mode;
    $hidden = $this->hiddenFields();
    // also hide these, since they will be replaced, 
    // but we don't include in hiddenFields since we need them for editing
    $hidden[] = 'propcode';
    $hidden[] = 'propname';
    foreach ($hidden as $col) {
      $content[$col]['#type'] = 'hidden';
    }
    // we do NOT use loadReplicant(&$entity, $varkey, $exclude_cached = FALSE, $repl_bundle = FALSE)
    // because the plumbing there expects only one replicant, and we want to get all
    // instead we use loadComponents
    if (!empty($entity->propcode)) {
      // *****************************
      // Get and Render Chems & Rates
      switch ($view_mode) {
        case 'teaser':
        case 'ical_summary':
        case 'full':
        case 'plugin':
        default:   
          $content['body'] = array(
            '#type' => 'item',
          );
          $content['body']['#markup'] = $propcode_list;
        break;
      }
    }
    //dpm($feature,'final feature');
    //dpm($content,'final content');
    //dsm("Finished buildContent");
  }
  
}

class dHVarAnnotation extends dHVariablePluginDefault {
  // propcode = varkey of item to group 
  public function hiddenFields() {
    // this hides all field except name and code by default 
    // contained objects will dictate the display and edit visibility
    return array('pid', 'startdate', 'enddate', 'varid', 'featureid', 'entity_type', 'bundle', 'dh_link_admin_pr_condition', 'varname', 'propvalue', 'propcode');
  }
  public function formRowEdit(&$form, $entity) {
    parent::formRowEdit($form, $entity);
    // @todo: implement a method to show editable entries for linked
    //        props of varkey = propcode 
    //        use linked props edit form 
    //        var $attach_method = 'contained';
    $form['propname']['#weight'] = 1;
    $form['proptext']['#weight'] = 2;
  }
}
?>
