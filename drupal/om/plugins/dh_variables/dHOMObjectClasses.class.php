<?php
module_load_include('inc', 'dh', 'plugins/dh.display');
module_load_include('module', 'dh');

class dHOMBaseObjectClass extends dHVariablePluginDefault {
  var $object_class = 'BlankShell';
  var $path = "/var/www/html/om/";
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
  public function getDefaults($entity, &$defaults = array()) {
    return $defaults;
  }
  
  public function addProperties(&$entity) {
    // add properties that reside on this entity.
    // this can add any number of properties that should reside on this entity.
    // sub-class this and call repeatedly
    // Example:
    //$values = array(
    //  'entity_type' => $entity->entityType(),
    //  'propcode' => $this->object_class,
    //  'propname' => 'object_class',
    //  'featureid' => $entity->identifier(),
    //  'varid' => dh_varkey2varid('om_object_class', TRUE),
    //);
    //dh_update_properties($values, 'singular');
    $defaults = $this->getDefaults($entity);
    foreach ($defaults as $thisone) {
      dpm($thisone,'adding default');
      dh_update_properties($thisone, $thisone['singularity']);
    }
  }
  
  public function insert(&$entity) {
    //$entity->propname = 'blankShell';
    $this->saveObjectClass($entity);
    $this->addProperties($entity);
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
    // 1. $elid = findRemoteOMElement($entity, $parents) ; this returns $elid and increments $parents
    // 2. if $elid = 0 then no remote sync
    // 3. Determine how to save
    $path = array(); // we init here, since save() shouldn't be called in this chain on any upstream objects
    $elid = $this->findRemoteOMElement($entity, $path);
    // take the last parent out since that is just the name of the model element
    // and we don't need that, since we have the elementid 
    // if this was a form API use case we could keep the parent name
    array_pop($path);
    // $parents will be modified by the methods
    // the property set_remote allows us to disable this functionality, for example
    // if we are doing an insert from an import, we wouldn't want to do this.
    if ($elid > 0 and !property_exists($entity, 'set_remote') or $entity->set_remote) {
      $this->setAllRemoteProperties($entity, $elid, $path);
    }
  }
  
  public function setAllRemoteProperties($entity, $elid, $path) {
    // this is to be done on save.  The base class saves nothing
    // subclasses can save other things
  }
  
  public function setRemoteProp($entity, $elid, $path, $propvalue, $object_class = FALSE) {
    // @todo: make this work - for now just return
    if ( ($object_class == FALSE) and (count($path) > 1)) {
      $object_class = $this->object_class;
      //watchdog('om', "Missing objectclass called for a nested OM property $propname on $parentname");
    }
    // change $parents to $path
    //   - eliminate propname from function args since it is on the $path stack
    //   - 
    //   - decide if we need to handle the last argument, which is the name of the final containing element
    //     I think that this is superfluous information, or redundant, since we have the elementid
    //     But if we think in terms of the Drupal Form API, this information becomes very relevant.
    //     So for now we will include it, but pop it off BEFORE sending to setRemoteProp
    
    // this will vary depending upon the variable being set, and also the depth
    // thought most often edit_subcomp_props will be 90% of transactions??
    // all subclasses should handle this in
    // 
    $setstr = FALSE;
    $test_only = FALSE;
    // @todo
    // constants should have 1 layer lower, 
    // Ex: a max_storage property on an impoundment comes in as 3 parents, 
    //     but really should be handled by the 2 parent code
    // Ex: run_mode on the parent model object should resolve 2 1 parent, but comes in as 2
    // Equations ARE handled correctly however, since the 
    dpm($path,'path');
    dsm("Handling a property on a subcomp of the element ($elid) depth = " . count($path));
    switch (count($path)) {
      case 1:
        list($propname) = $path;
        // this is a property of the element itself
        // @todo: set a regular attribute using batch_setprop.php
        $setstr = "php setprop.php $elid \"$propname=$propvalue\" ";
      break;
      case 2:
        list($propname, $parentname) = $path;
        // this is a property on a subcomp of the element
        $setstr = "php set_subprop.php $elid $parentname \"$propname=$propvalue\" $object_class 0 ";
      break;
      case 3:
      // @todo: this would be a sub-comp of a sub-comp, like an equation on a subcomp like channelObject
      //        at this time I don't think we should have any of these, but just in case 
        list($propname, $parentname, $grandparentname) = $path;
        // this is a property on a subcomp of the element
        $setstr = "php set_subprop.php $elid $parentname \"$propname=$propvalue\" $object_class 0 ";
        //dpm( $setstr, "3 level subcomp not yet handled -- will not execute ");
        $setstr = FALSE;
      break;
      default:
        drupal_set_message("Can not handle remote update of properties with depth = " . count($parents));
      break;
    }
    if ($setstr and !$test_only) {
      $cmd = "cd $this->path \n";
      $cmd .= $setstr;
      dpm( $cmd, "Executing ");
      shell_exec($cmd);
    }
  }
  
  public function getRemoteProp($entity, $elid, $path, $object_class = FALSE) {
    // this element connection does not currently use this, but its children props might
    // @todo: make this work - for now just return
    if ( ($object_class == FALSE) and (count($path) > 1)) {
      $object_class = $this->object_class;
      //watchdog('om', "Missing objectclass called for a nested OM property $propname on $parentname");
    }
    // change $parents to $path
    //   - eliminate propname from function args since it is on the $path stack
    //   - 
    //   - decide if we need to handle the last argument, which is the name of the final containing element
    //     I think that this is superfluous information, or redundant, since we have the elementid
    //     But if we think in terms of the Drupal Form API, this information becomes very relevant.
    //     So for now we will include it, but pop it off BEFORE sending to setRemoteProp
    
    // this will vary depending upon the variable being set, and also the depth
    // thought most often edit_subcomp_props will be 90% of transactions??
    // all subclasses should handle this in
    // 
    $setstr = FALSE;
    $test_only = FALSE;
    // @todo
    // constants should have 1 layer lower, 
    // Ex: a max_storage property on an impoundment comes in as 3 parents, 
    //     but really should be handled by the 2 parent code
    // Ex: run_mode on the parent model object should resolve 2 1 parent, but comes in as 2
    // Equations ARE handled correctly however, since the 
    dpm($path,'path');
    dsm("Handling a property on a subcomp of the element ($elid) depth = " . count($path));
    switch (count($path)) {
      case 1:
        list($propname) = $path;
        // this is a property of the element itself
        // @todo: set a regular attribute using batch_setprop.php
        $setstr = "php setprop.php $elid \"$propname=$propvalue\" ";
      break;
      case 2:
        list($propname, $parentname) = $path;
        // this is a property on a subcomp of the element
        $setstr = "php set_subprop.php $elid $parentname \"$propname=$propvalue\" $object_class 0 ";
      break;
      case 3:
      // @todo: this would be a sub-comp of a sub-comp, like an equation on a subcomp like channelObject
      //        at this time I don't think we should have any of these, but just in case 
        list($propname, $parentname, $grandparentname) = $path;
        // this is a property on a subcomp of the element
        $setstr = "php set_subprop.php $elid $parentname \"$propname=$propvalue\" $object_class 0 ";
        //dpm( $setstr, "3 level subcomp not yet handled -- will not execute ");
        $setstr = FALSE;
      break;
      default:
        drupal_set_message("Can not handle remote update of properties with depth = " . count($parents));
      break;
    }
    if ($setstr and !$test_only) {
      $cmd = "cd $this->path \n";
      $cmd .= $setstr;
      dpm( $cmd, "Executing ");
      shell_exec($cmd);
    }
  }
    
  public function findRemoteOMElement($entity, &$parents) {
    //dpm($entity, "findRemoteOMElement @ depth = $parents");
    $elid = 0;
    $parents[] = $entity->propname;
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
    //dpm($elvar_info, " dh_get_properties(elvar_info, 'singular')");
    $elvar = dh_properties_enforce_singularity($elvar_info, 'singular');
    //dpm($elvar, " elvar");
    if ($elvar) {
      $elid = $elvar->propvalue;
    } else {
      // get parent
      $parent = $this->getParentEntity($entity);
      //dpm($parent, "trying to call findRemoteOMElement on parent");
      if (isset($parent->dh_variables_plugins) and is_array($parent->dh_variables_plugins)) {
        foreach ($parent->dh_variables_plugins as $plugin) {
          if (is_object($plugin) and method_exists($plugin, 'findRemoteOMElement')) {
            $elid = $plugin->findRemoteOMElement($parent, $parents);
          }
        }
      }
    }
    // if not, load parent, check for findRemoteOMElement() method, if present, call it, if not, return
    return $elid;
  }
  
  public function create(&$entity) {
    // set up defaults?
    $entity->propname = !empty($entity->propname) ? $entity->propname : (!empty($this->object_class) ? $this->object_class : 'un-named');
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

class dHOMObjectClass extends dHOMBaseObjectClass {
  // controls the objectclass property -- 
  // currently this does nothing but we *might* allow it to make this change?
  // seems dangerous
}

class dHOMElementConnect extends dHOMBaseObjectClass {
  var $object_class = FALSE;
  
  public function findRemoteOMElement($entity, &$parents) {
    // since this connector is the final model container, we know the elid is by definition the propvalue
    $elid = $entity->propvalue;
    // don't increment here since this is a property on another object that already increments
    //$parents[] = $entity->propname;
    return $elid;
  }
  
  public function setRemoteProp($entity, $elid, $path, $propvalue, $object_class = FALSE) {
    // this element connection does not currently use this, but its children props might
  }
}

class dHOMConstant extends dHOMBaseObjectClass {
  // numeric constant
  var $object_class = FALSE;
  
  public function hiddenFields() {
    $hidden = array_merge(array('propcode', 'startdate', 'enddate'), parent::hiddenFields());
    return $hidden;
  }
  
  public function formRowEdit(&$rowform, $entity) {
    parent::formRowEdit($rowform, $entity);
    $rowform['propvalue']['#title'] = 'Value';
    $rowform['propvalue']['#description'] = 'Numerical constant.';
  }
  
  public function setAllRemoteProperties($entity, $elid, $path) {
    parent::setAllRemoteProperties($entity, $elid, $path);
    // this is to be done on save.  The base class does nothing except format 
    $this->setRemoteProp($entity, $elid, $path, $entity->propvalue, $this->object_class);
  }
  
}

class dHOMModelElement extends dHOMBaseObjectClass {
  var $object_class = 'modelObject';
  
  public function hiddenFields() {
    $hidden = parent::hiddenFields();
    return $hidden;
  }
  
  public function setAllRemoteProperties($entity, $elid, $path) {
    parent::setAllRemoteProperties($entity, $elid, $path);
    // this is to be done on save.  The base class only saves name and description, 
    // subclasses can save other things
    array_unshift($path, 'name');
    $this->setRemoteProp($entity, $elid, $path, $entity->propname, $this->object_class);
    array_shift($path);
    array_unshift($path, 'description');
    $this->setRemoteProp($entity, $elid, $path, $entity->proptext['und'][0]['value'], $this->object_class);
    //$this->setRemoteProp($entity, $elid, $path, 'description', $this->proptext);
  }
}

class dHOMModelContainer extends dHOMModelElement {
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

class dHOMSubComp extends dHOMBaseObjectClass {
  // this class has a name, and a description, an exec_hierarchy and other atributes
  // @todo: add basic handling of things other than descriptions
  public function setAllRemoteProperties($entity, $elid, $path) {
    parent::setAllRemoteProperties($entity, $elid, $path);
    array_unshift($path, 'description');
    $this->setRemoteProp($entity, $elid, $path, $entity->proptext['und'][0]['value'], $this->object_class);
    //$this->setRemoteProp($entity, $elid, $path, 'description', $this->proptext);
  }
}

class dHOMEquation extends dHOMSubComp {
  var $object_class = 'Equation';
  
  public function formRowEdit(&$rowform, $entity) {
    parent::formRowEdit($rowform, $entity);
    $rowform['propcode']['#title'] = '';
    $rowform['propcode']['#prefix'] = ' = ';
  }
  
  public function setAllRemoteProperties($entity, $elid, $path) {
    parent::setAllRemoteProperties($entity, $elid, $path);
    dsm("setAllRemoteProperties from dHOMEquation");
    array_unshift($path, 'equation');
    $this->setRemoteProp($entity, $elid, $path, $entity->propcode, $this->object_class);
  }
}

class dHOMAlphanumericConstant extends dHOMBaseObjectClass {
  var $object_class = 'textField';
}

class dHOMDataMatrix extends dHOMSubComp {
  var $object_class = 'DataMatrix';
  var $default_bundle = 'om_data_matrix';
}

// Remaining Objects from who_xmlobject.php
// ***************************
// full objects only
// ***************************
/* 
   model=# select objectclass, count(*) from scen_model_element where scenarioid = 37 group by objectclass;
        objectclass      | count
  -----------------------+-------
   blankShell            |     1
   dataConnectionObject  |   476
   CBPDataConnection     |    68
   CBPLandDataConnection |  1474
   flowDurationGraph     |   646
   graphObject           |   675
   hydroImpoundment      |   655
   modelContainer        |  8093
   timeSeriesFile        |    21
   USGSChannelGeomObject |   654
   USGSGageObject        |  1313
   USGSSyntheticRecord   |   651
   waterSupplyElement    |    24
   waterSupplyModelNode  |   658
   wsp_vpdesvwuds        |  5938
   wsp_waterUser         |     2
  (16 rows)
  
-- VWP elements
  select objectclass, count(*) from scen_model_element where scenarioid = 13 group by objectclass;
           objectclass        | count
  --------------------------+-------
                            |     7
   blankShell               |    35
   CBPLandDataConnection    |     9
   channelObject            |     2
   dataConnectionObject     |     4
   flowDurationGraph        |     7
   giniGraph                |     7
   graphObject              |    89
   HabitatSuitabilityObject |     2
   hydroContainer           |     1
   hydroImpoundment         |     9
   modelContainer           |    90
   noaaGriddedPrecip        |     1
   reportObject             |     5
   storageObject            |    31
   timeSeriesFile           |    17
   USGSChannelGeomObject    |     9
   USGSGageObject           |    54
   USGSSyntheticRecord      |     7
   waterSupplyModelNode     |     5
   withdrawalRuleObject     |     1

*/ 

class dHOM_USGSGageObject extends dHOMModelElement {
  var $object_class = 'USGSGageObject';
}

?>