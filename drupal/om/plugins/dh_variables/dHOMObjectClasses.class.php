<?php
module_load_include('inc', 'dh', 'plugins/dh.display');
module_load_include('module', 'dh');

class dHOMObjectClass extends dHVariablePluginDefault {
  var $object_class = 'BlankShell';
  var $path = "/opt/model/apache/batch_updates/";
  public function hiddenFields() {
    $hidden = array(
      'pid', 
      'featureid', 
      'entity_type', 
      'bundle', 
      'dh_link_admin_pr_condition', 
      'field_prop_upload'
    );
    return $hidden;
  }
  
  public function classOptions() {
    // get options from the vocab
    $row->propvalue = number_format($row->propvalue, 3);
  }
  
  public function formRowRender(&$rowvalues, &$row) {
    // special render handlers when displaying in a grouped property block
    // show select list for varid
    // update form via ajax when changed
  }
  
  public function setUp(&$entity) {
    //dpm($entity, 'setUp()');
  }
  
  public function load(&$entity) {
    // get field default basics
  }
  
  public function saveObjectClass(&$entity) {
    // get field default basics
    if ($this->object_class) {
      $values = array(
        'entity_type' => $entity->entityType(),
        'propcode' => $this->object_class,
        'propname' => 'object_class',
        'featureid' => $entity->identifier(),
        'varid' => dh_varkey2varid('om_object_class', TRUE),
      );
      dh_update_properties($values, 'singular');
    }
  }
  
  public function insert(&$entity) {
    //$entity->propname = 'blankShell';
    $this->saveObjectClass($entity);
    parent::insert();
  }
  
  public function update(&$entity) {
    //$entity->propname = 'blankShell';
    $this->saveObjectClass($entity);
    parent::update();
  }
  
  public function save(&$entity) {
    //$entity->propname = 'blankShell';
    parent::save();
    // now, find out if we are suppose to sync to a remote server
    // 1. $elid = findRemoteOMElement($entity, $depth) ; this returns $elid and increments $depth
    // 2. if $elid = 0 then no remote sync
    // 3. Determine how to save
    $depth = 0; // we init here, since save() shouldn't be called in this chain on any upstream objects
    $elid = $this->findRemoteOMElement($entity, $depth);
    // $depth will be modified by the methods
    if ($elid > 0) {
      $this->setRemoteProp($entity, $elid, $depth);
    }
  }
  
  public function setRemoteProp($entity, $elid, $depth) {
    // this will vary depending upon the variable being set, and also the depth
    // thought most often edit_subcomp_props will be 90% of transcations??
    // all subclasses should handle this in
    // 
    switch ($depth) {
      case 1:
        // this is a property of the element itself
        drupal_set_message("Handling a property on the element ($elid) depth = $depth");
        drupal_set_message("Usage: batch_setprop.php scenarioid \"$entity->propname=value\" $elid [elemname] [custom1] [custom2] [objectclass] \n");
      break;
      case 2:
        // this is a property on a subcomp of the element
        drupal_set_message("Handling a property on a subcomp of the element ($elid) depth = $depth");
        drupal_set_message("Usage: edit_subcomp_props.php $elid parentpropname \"$entity->propname=value\" \n");
      break;
      default:
        drupal_set_message("Can not handle remote update of properties where depth = $depth");
      break;
    }
  }
    
  public function findRemoteOMElement($entity, &$depth) {
    //dpm($entity, "findRemoteOMElement @ depth = $depth");
    $elid = 0;
    $depth++;
    // check for a property with varkey om_element_connection on this entity
    $elvar_info = array(
      'featureid' => $entity->pid,
      'entity_type' => 'dh_properties',
      'bundle' => 'dh_properties',
      'varid' => dh_varkey2varid('om_element_connection', TRUE),
    );
    // *************************************************
    // Current MGY
    // *************************************************
    dpm($elvar_info, " dh_get_properties(elvar_info, 'singular')");
    $elvar = dh_properties_enforce_singularity($elvar_info, 'singular');
    dpm($elvar, " elvar");
    if ($elvar) {
      $elid = $elvar->propvalue;
    } else {
      // get parent
      $parent = $this->getParentEntity($entity);
      dpm($parent, "trying to call findRemoteOMElement on parent");
      if (isset($parent->dh_variables_plugins) and is_array($parent->dh_variables_plugins)) {
        foreach ($parent->dh_variables_plugins as $plugin) {
          if (is_object($plugin) and method_exists($plugin, 'findRemoteOMElement')) {
            $elid = $plugin->findRemoteOMElement($parent, $depth);
          }
        }
      }
    }
    // if not, load parent, check for findRemoteOMElement() method, if present, call it, if not, return
    return $elid;
  }
  
  public function create(&$entity) {
    // set up defaults?
    $entity->propname = !empty($this->object_class) ? $this->object_class : 'un-named';
    if ($this->default_bundle) {
      $entity->bundle = $this->default_bundle;
    }
  }
  
  public function formRowEdit(&$rowform, $entity) {
    // special render handlers when displaying in a grouped property block
    //dpm($entity, 'entity');
    $this->hideFormRowEditFields($rowform);
    $opts = dh_vardef_varselect_options(array("vocabulary = 'om_object_classes'"));
    $rowform['varid']['#options'] = $opts;
    $rowform['varid']['#type'] = 'select';
    //$rowform['varid']['#default_value'] = 'om_class_BlankShell';
    $rowform['propname']['#default_value'] = empty($entity->propname) ? $this->object_class : $entity->propname;
    $rowform['propname']['#title_display'] = 'before';
    $rowform['propname']['#title'] = 'Name';
    $rowform['propname']['#type'] = 'textfield';
    $rowform['proptext']['#weight'] = 10;
    //$rowform['propname']['#markup'] = 'object_class';
  }
}

class dHOMElementConnect extends dHOMObjectClass {
  var $object_class = FALSE;
  
  public function findRemoteOMElement($entity, &$depth) {
    // since this connector is the final model container, we know the elid is by definition the propvalue
    $elid = $entity->propvalue;
    // don't increment here since this is a property on another object that already increments
    //$depth++;
    return $elid;
  }
}

class dHOMDataMatrix extends dHOMObjectClass {
  var $object_class = 'DataMatrix';
  var $default_bundle = 'om_data_matrix';
}

class dHOMConstant extends dHOMObjectClass {
  var $object_class = 'Constant';
}

class dHOMNumericConstant extends dHOMObjectClass {
  var $object_class = FALSE;
}

class dHOMAlphanumericConstant extends dHOMObjectClass {
  var $object_class = FALSE;
}

class dHOMModelContainer extends dHOMObjectClass {
  var $object_class = 'modelContainer';
  
  public function hiddenFields() {
    $hidden = array_merge(array('propvalue', 'startdate', 'enddate'), parent::hiddenFields());
    return $hidden;
  }
  
  public function formRowEdit(&$rowform, $entity) {
    parent::formRowEdit($rowform, $entity);
    $rowform['propname']['#default_value'] = empty($entity->propname) ? $this->object_class : $entity->propname;
    $rowform['propname']['#title_display'] = 'before';
    $rowform['propname']['#title'] = 'Name';
    $rowform['propname']['#type'] = 'textfield';
    $rowform['propcode']['#title'] = 'Model Version';
    $rowform['proptext']['und']['#title'] = 'Model Description';
    $rowform['proptext']['und'][0]['value']['#title'] = 'Model Description';
    $rowform['proptext']['#weight'] = 10;
  }
}

class dHOMModelElement extends dHOMObjectClass {
  var $object_class = 'modelObject';
  
  public function hiddenFields() {
    $hidden = parent::hiddenFields();
    return $hidden;
  }
  
}

class dHOMEquation extends dHOMObjectClass {
  var $object_class = 'Equation';
  
  public function formRowEdit(&$rowform, $entity) {
    parent::formRowEdit($rowform, $entity);
    $rowform['propcode']['#title'] = '';
    $rowform['propcode']['#prefix'] = ' = ';
  }
  
  public function setRemoteProp($entity, $elid, $depth) {
    // this will vary depending upon the variable being set, and also the depth
    // thought most often edit_subcomp_props will be 90% of transcations??
    // all subclasses should handle this in
    // 
    $cmd = FALSE;
    switch ($depth) {
      case 1:
        // this is a property of the element itself
        drupal_set_message("Handling a property on the element ($elid) depth = $depth");
        drupal_set_message("Usage: batch_setprop.php scenarioid \"$entity->propname=value\" $elid [elemname] [custom1] [custom2] [objectclass] \n");
      break;
      case 2:
        // this is a property on a subcomp of the element
        drupal_set_message("Handling a property on a subcomp of the element ($elid) depth = $depth");
        drupal_set_message("Usage: edit_subcomp_props.php $elid $entity->propname \"equation=$entity->propcode\" \n");
        $cmd = "cd $this->path \n";
        $cmd .= "php edit_subcomp_props.php $elid $entity->propname \"equation=$entity->propcode\" ";
      break;
      default:
        drupal_set_message("Can not handle remote update of properties where depth = $depth");
      break;
    }
    if ($cmd) {
      dpm( $cmd, "Executing ");
      shell_exec($cmd);
    }
  }
}

?>