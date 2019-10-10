<?php
module_load_include('inc', 'dh', 'plugins/dh.display');
module_load_include('module', 'dh');

class dHVariablePluginDefaultOM extends dHVariablePluginDefault {
  
  // @todo: should we have a method for adding these defaults, to insure proper formation?
  // **** BEGIN - Experimental un-used Component Adding Methods
  //        the property $component_defaults and method add_component_default() are not currently used
  var $component_defaults = FALSE; // will be initialized in getDefaults or other place.
  public function add_component_default($config) {
    if ($this->component_defaults === FALSE) {
      $this->component_defaults = array();
    }
    if (!isset($config['form_machine_name'])) {
      $config['form_machine_name'] = $this->handleFormPropname($config['propname']);
    }
    if (!$this->validate_component_default($config)) {
      return FALSE;
    }
    $this->component_defaults[$config['form_machine_name']] = $config;
    return TRUE;
  }
  public function validate_component_default($config) {
    if (!isset($config['propname'])) {
      return FALSE;
    }
    if (!$this->validate_alphanumeric_underscore($config['form_machine_name'])) {
      return FALSE;
    }
    return TRUE;
  }
  public function validate_alphanumeric_underscore($str) {
    return preg_match('/^[A-Za-z][A-Za-z0-9]*(?:_[A-Za-z0-9]+)*$/',$str);
  }
  // **** END - Experimental un-used Component Adding Methods
  
  public function hiddenFields() {
    return array(
      'pid',
      'featureid',
      'startdate',
      'enddate',
      'bundle',
      'entity_type',
      'dh_link_admin_pr_condition', 
      'field_prop_upload',
    );
  }
  
  public function formRowEdit(&$rowform, $row) {
    parent::formRowEdit($rowform, $row); // does hiding etc.
    $varinfo = $row->varid ? dh_vardef_info($row->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    $this->loadProperties($row);
    // apply custom settings here
    $this->addAttachedProperties($rowform, $row);
  }
  
  public function insert(&$entity) {
    //$entity->propname = 'blankShell';
    // check for transition from ts to prop
    $this->convert_attributes_to_dh_props($entity);
    $this->updateProperties($entity);
    parent::insert($entity);
  }
  
  public function update(&$entity) {
    //dpm($entity,'update()');
    // check for transition from for value to prop
    $this->convert_attributes_to_dh_props($entity);
    $this->updateProperties($entity);
    parent::update($entity);
  }
  
  public function save(&$entity) {
    parent::save($entity);
  }
  
  public function loadProperties(&$entity, $overwrite = FALSE, $propname = FALSE) {
    $props = $this->getDefaults($entity);
    if (!($propname === FALSE)) {
      // a single prop has been requested
      if (!array_key_exists($propname, $props)) {
        watchdog('dh', 'loadProperties(entity, propname) called on dH Variable plugin object but propname = ' . strval($propname) . ' not found');
        return FALSE;
      }
      $props = array($propname => $props[$propname]);
    }
    foreach ($props as $thisvar) {
	    // propname is arbitrary by definition
      // also, propname can be non-compliant with form API, which requires underscores in place of spaces.
      // user can also rename properties, but that shouldn't be allowed with these kinds of defined by DefaultSettings
      // or at least, if the user renames the property then this plugin should create a new one.
      // name should alternatively be read-only in these forms.
      // if we create the name as form compliant, and create a field called "form_name", can we eliminate any guesswork?
      // we still have to deal with user-named properties, which is definitely something available to users.
      //   - actually, user defined would be handled in a separate fashion.  We need to handle this well, since the 
      //     modeling framework will enable many user-defined props, and we WILL want to be able to edit them in a multi-form
      //     type scenario. 
      $pn = $this->handleFormPropname($propname);
      if (!isset($thisvar['embed']) or ($thisvar['embed'] === TRUE)) {
        if ($overwrite 
		    or !property_exists($entity, $propname) 
        or (property_exists($entity, $propname) 
          and !is_object($entity->{$propname})
        ) 
		  ) {
          $thisvar['featureid'] = $entity->{$this->row_map['id']};
          $prop = $this->insureProperty($entity, $thisvar);
          //dpm($thisvar, "Insuring ");
          if (!$prop) {
            watchdog('om', 'Could not Add Properties in plugin loadProperties');
            return FALSE;
          }
          //dpm($prop,'prop');
          // apply over-rides if given
          $prop->vardesc = isset($thisvar['vardesc']) ? $thisvar['vardesc'] : $prop->vardesc;
          $prop->varname = isset($thisvar['varname']) ? $thisvar['varname'] : $prop->varname;
          $prop->datatype = isset($thisvar['datatype']) ? $thisvar['datatype'] : $prop->datatype;
          $entity->{$prop->propname} = $prop;
        }
      }
    }
  }
  
  public function insureProperty($entity, $thisvar) {
    // make sure all standard props are here
    $thisvar['featureid'] = $entity->{$this->row_map['id']};
    //dpm($thisvar, "Checking for property default");
    $thisvar = $thisvar + array('singularity' => 'name');
    $prop = om_model_getSetProperty($thisvar, $thisvar['singularity']);
    return $prop;
  }
  
  public function updateProperties(&$entity) {
    // @todo: move this to the base plugin class 
    $props = $this->getDefaults($entity);
    //dpm($entity, "Calling updateProperties");
    //dpm($props, "Iterating over attached properties");
    foreach ($props as $thisvar) {
      if (!isset($thisvar['embed']) or ($thisvar['embed'] === TRUE)) {
        //error_log("Saving " . $thisvar['propname']);
        // load the property 
        // if a property with propname is set on $entity, send its value to the plugin 
        //   * plugin should be stored on the property object already
        // if prop on entity is an object already, handle directly, otherwise, load it
        //   the object method is advantageous because we can make things persist
        if (property_exists($entity, $thisvar['propname'])) {
          if (!is_object($entity->{$thisvar['propname']})) {
            // this has been set by the form API as a value 
            // so we need to load/create a property then set the value
            //dpm($thisvar, "Creating object before saving ");
            $thisvar['featureid'] = $entity->{$this->row_map['id']};
            //@todo: this needs to use the plugin handler for this instead of assuming propvalue instead of propcode
            //       why isn't this already an object after convert_attributes_to_dh_props is called?
            //     Location (the featureid loader property) is already loaded, but Location Sharing is NOT -- why????
            $prop = om_model_getSetProperty($thisvar, 'name');
            //dpm($prop, "object after creation");
            // now, apply the stashed value to the property
            foreach ($prop->dh_variables_plugins as $plugin) {
              // the default method will guess location based on the value unless overridden by the plugin
              $plugin->applyEntityAttribute($prop, $entity->{$thisvar['propname']});
            }
            //dpm($prop, "object after plugins");
            //dsm("Saving Newly loaded object " . $thisvar['propname']);
            entity_save('dh_properties', $prop);
          } else {
            $prop = $entity->{$thisvar['propname']};
            // already a loaded form object, so just let it rip.
            //dpm($prop, "object from parent");
            //dsm("Saving preloaded object " . $thisvar['propname']);
            entity_save('dh_properties', $prop);
          }
        }
      }
    }
  }
  
  public function addAttachedProperties(&$form, &$entity) {
    $dopples = $this->getDefaults($entity);
    foreach ($dopples as $thisvar) {
      if (!isset($thisvar['embed']) or ($thisvar['embed'] === TRUE)) {
        $pn = $this->handleFormPropname($thisvar['propname']);
        $dopple = $entity->{$thisvar['propname']};
        // @todo: if this is a code variable, we should get propcode?
        switch ($this->attach_method) {
          case 'contained':
          $plugin = dh_variables_getPlugins($dopple);
          if ($plugin) {
            if (method_exists($plugin, 'attachNamedForm')) {
              //dsm("Using attachNamedForm()");
              $plugin->attachNamedForm($form, $dopple);
            } else {
              $plugin->formRowEdit($dopple_form, $dopple);
              $form[$pn] = $dopple_form['propvalue'];
            }
          }
          break;
          default:
          $dopple_form = array();
          dh_variables_formRowPlugins($dopple_form, $dopple);
          $form[$pn] = $dopple_form['propvalue'];
          break;
        }
      }
      if (isset($thisvar['#weight'])) {
        $form[$pn]['#weight'] = $thisvar['#weight'];
      }
    }
  }
  
  public function convert_attributes_to_dh_props($entity) {
    // this will be called after a form submittal, the added form fields from attached props will be/
    // added as plain fields on the entity, we then grab them by name and handle their contents.
    $props = $this->getDefaults($entity);
    //dpm($props,'props from getDefaults');
    //error_log("Handling properties on $entity->propname " . print_r($props,1));
    foreach ($props as $thisvar) {
      $convert_value = FALSE; // flag to see if we need to convert (in case we are called multiple times)
      $load_property = FALSE;
      $propvalue = NULL;
      $propname = $thisvar['propname'];
      // form property name will be converted to a machine name by attachNamedForm() methods.
      // so now we just get this name here so that we can keep things straight but allow users descriptive names
      $pn = $this->handleFormPropname($propname);
      // check for conversion from value to property
      // this could need to change as fully loaded objects could be stored as array  that are then loaded as object or handled more completely
      // in Form API *I think*
      // but for now, this handles the case where a property value is stashed on the object
      // cases:
      // - property exists, and IS object: check for form API munged name and copy over, otherwise, do nothing
      // - property exists and is NOT object: stash the value, load the prop object, and setValue to stashed
      // - property does not exist: load property and return
      if (property_exists($entity, $propname) and !is_object($entity->{$propname})) {
        // if the prop is not an object, stash the value and load property, 
        $convert_value = TRUE;
        $propvalue = $entity->{$thisvar['propname']};
        $load_property = TRUE;
      }
      if ( ($pn <> $propname) and property_exists($entity, $pn) ) {
        // handle case where prop name had spaces and was munged by form API
        // we assume that this is not going to be an object sine form API will return just a value
        $propvalue = $entity->{$pn};
        $convert_value = TRUE;
      }
      if (!property_exists($entity, $propname) ) {
        $load_property = TRUE;
      }
      if ($load_property) {
        //dsm("Loading property $pn");
        $this->loadProperties($entity, FALSE, $propname);
      }
      // now, apply the stashed value to the property
      if ($convert_value and is_object($entity->{$propname})) {
        $prop = $entity->{$thisvar['propname']};
        foreach ($prop->dh_variables_plugins as $plugin) {
          // the default method will guess location based on the value unless overridden by the plugin
          $plugin->applyEntityAttribute($prop, $propvalue);
        }
        // insure this featureid.  There is probably a better way to do this earlier in the process.
        // we need to insure a valid parent entity first, save it, then load attached properties and update.  
        $prop->featureid = $entity->identifier();
      }
    }
    //dpm($entity,'entity post conversion to props');
  }
  
  public function formRowSave(&$rowvalues, &$row) {
    // special form save handlers
    //dpm($rowvalues,'vals');
    //dpm($row,'entity');
    parent::formRowSave($rowvalues, $row);
  }
  
  public function buildContent(&$content, &$entity, $view_mode) {
    // @todo: handle teaser mode and full mode with plugin support
    foreach ($this->hiddenFields() as $hide) {
      unset($content[$hide]);
    }
    switch ($view_mode) {
      case 'plugin':
      case 'teaser':
      default:
        $content['propname'] = array(
          '#type' => 'item',
          '#markup' => "<b>Name:</b> $entity->propname<sub>($entity->varname)</sub>"
        );
        if (isset($content['propvalue'])) {
          $content['propvalue'] = array(
            '#type' => 'item',
            '#markup' => "<b>Value:</b> " . $entity->propvalue,
          ); 
        }
        if (isset($content['propcode'])) {
          $content['propcode'] = array(
            '#type' => 'item',
            '#markup' => "<b>Code:</b> " . $entity->propcode,
          ); 
        }
      break;
    }
  }
}

// @todo: evaluate dHVariablePluginCodeAttribute and dHVariablePluginNumericAttribute
//        for migration to base dh class
class dHVariablePluginCodeAttribute extends dHVariablePluginDefault {
  var $default_code = '';
  
  public function hiddenFields() {
    return array('tstime','featureid','tsendtime','entity_type','tsvalue');
  }
  public function formRowEdit(&$rowform, $row) {
    $varinfo = $row->varid ? dh_vardef_info($row->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    $rowform['propcode'] = array(
      '#title' => t($varinfo->varname),
      '#type' => 'textfield',
      '#description' => $varinfo->vardesc,
      '#default_value' => !empty($row->propcode) ? $row->propcode : "0.0",
    );
  }
  
  // @todo: move this into dh module once we are satisifed that it is robust
  public function attachNamedForm(&$rowform, $row) {
    $varinfo = $row->varid ? dh_vardef_info($row->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    $mname = $this->handleFormPropname($row->propname);
    $rowform[$mname] = array(
      '#title' => t($varinfo->varname),
      '#type' => 'textfield',
      '#description' => $varinfo->vardesc,
      '#default_value' => !empty($row->propcode) ? $row->propcode : "0.0",
    );
  }
  public function dh_getValue($entity, $ts = FALSE, $propname = FALSE, $config = array()) {
    // @todo: implement om routines
    return $this->getPropertyAttribute($entity);
  }
  
  public function applyEntityAttribute($property, $value) {
    $property->propcode = $value;
  }
  
  public function getPropertyAttribute($property) {
    return $property->propcode;
  }
}

class dHVariablePluginNumericAttribute extends dHVariablePluginDefault {
  var $default_value = 0;
  var $default_code = '';
  var $pct_range = array('<5', 10, 25, 50, 75, 90, 100);
  var $pct_default = NULL;
  
  public function hiddenFields() {
    return array('startdate','featureid','enddate','entity_type','propcode');
  }
  public function formRowEdit(&$rowform, $row) {
    $varinfo = $row->varid ? dh_vardef_info($row->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    switch ($varinfo->datatype) {
      // @todo: datatype does not actually get copied from vardef to props by the base class, it only grabs varid, varunits and varname, so we need to add datatype to make this work.
      case 'percent':
      $opts = $this->pct_list($this->pct_range);
      $rowform['propvalue'] = array(
        '#title' => t($varinfo->varname),
        '#type' => 'select',
        '#options' => $opts,
        '#empty_option' => 'n/a',
        '#description' => $varinfo->vardesc,
        '#default_value' => !empty($row->propvalue) ? $row->propvalue : $this->default_value,
      );
      break;
      case 'boolean':
      $opts = array(0 => 'False', 1 => 'True');
      $rowform['propvalue'] = array(
        '#title' => t($varinfo->varname),
        '#type' => 'select',
        '#options' => $opts,
        '#description' => $varinfo->vardesc,
        '#default_value' => !empty($row->propvalue) ? $row->propvalue : "$this->pct_default",
      );
      break;
      
      default:
      $rowform['propvalue'] = array(
        '#title' => t($varinfo->varname),
        '#type' => 'textfield',
        '#description' => $varinfo->vardesc,
        '#default_value' => !empty($row->propvalue) ? $row->propvalue : NULL,
      );
      break;
    }
  }
    
  public function pct_list($inc = 10) {
    $pcts = array();
    if (is_array($inc)) {
      // we already have our list of percents, just work it out
      foreach ($inc as $i) {
        $dec = floatval(preg_replace('/\D/', '', $i)) / 100.0;
        $pcts["$dec"] = $i . " %";
      }
    } else {
      $i = $inc;
      while ($i <= 100) {
        $dec = floatval($i) / 100.0;
        $pcts["$dec"] = $i . " %";
        $i += $inc;
      }
    }
    return $pcts;
  }
  
  // @todo: move this into dh module once we are satisifed that it is robust
  public function attachNamedForm(&$rowform, $entity) {
    $varinfo = $entity->varid ? dh_vardef_info($entity->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    $formshell = array();
    // use standard formatting to enable choices.
    $this->formRowEdit($formshell, $entity);
    $mname = $this->handleFormPropname($entity->propname);
    $rowform[$mname] = $formshell['propvalue'];
    $rowform[$mname]['#title'] = t($entity->varname);
    $rowform[$mname]['#description'] = t($entity->vardesc);
  }
  
  public function applyEntityAttribute($property, $value) {
    $property->propvalue = $value;
  }
  
  public function getPropertyAttribute($property) {
    return $property->propvalue;
  }
  
  public function dh_getValue($entity, $ts = FALSE, $propname = FALSE, $config = array()) {
    // @todo: implement om routines getPropertyAttribute() in base class 
    return $this->getPropertyAttribute($entity);
  }
}

class dHOMBaseObjectClass extends dHVariablePluginDefaultOM {
  // @todo: inherit dHVariablePluginDefaultOM, which will handle auto-adding of subprops in EditForm
  var $object_class = FALSE; // use to be BlankShell, but BlankShell will all be saved as modelElement 
  var $path = "/var/www/html/om/";
  var $state = array();
  var $setvarnames = array();
  var $attach_method = 'contained';
  
  public function hiddenFields() {
    $hidden = array(
      'pid', 
      'featureid', 
      'entity_type', 
      'bundle', 
      'varid', 
      'dh_link_admin_pr_condition', 
      'field_prop_upload',
      'object_class',
      'startdate',
      'enddate',
      'varname'
    );
    return $hidden;
  }

  function getPublicVars($entity, &$publix = array()) {
    //dpm($this,"called getPublicVars()");
    // gets all viewable variables
    $publix += array_keys($this->state); 
    $publix += $this->setvarnames; 
    $publix += $this->getPublicProps($entity); 
    $publix += $this->getPublicProcs($entity); 
    $publix += $this->getPublicInputs($entity); 
    $publix = array_unique($publix);
    sort($publix);
    return $publix;
  }

  function getLocalVars() {
    // gets all viewable variables
    $publix = array_unique(array_merge(array_keys($this->state), $this->getPublicProps($entity), $this->getPublicProcs($entity), $this->getPublicInputs($entity)));

    return $publix;
  }

  public function getPublicProps($entity) {
    // gets only properties that are visible (must be manually defined for now, could allow this to be set later)
    // taken directly from om library -- will revisit after full porting
    // children will subclass this and add their own like:
    // $publix = parent::getPublicProps($entity)
    $publix = array('name','objectname','description','componentid', 'startdate', 'enddate', 'dt', 'month', 'day', 'year', 'thisdate', 'the_geom', 'weekday', 'modays', 'week', 'hour', 'flow_mode', 'run_mode', 'timestamp');
    return $publix;
  }

  function getDataSources() {
    // taken directly from om library -- will revisit after full porting
    return array();
    return $this->datasources;
  }
  function getPublicProcs($entity) {
    // taken directly from om library -- will revisit after full porting
    // @todo: retrieve parent props, and local props.
    return array();
    // gets all viewable processors
    $retarr = array();
    if (is_array($this->procnames)) {
       #$this->logDebug("Procs for $this->name: " . print_r($this->procnames,1));
       //error_log("Procs for $this->name: " . print_r($this->procnames,1));
       foreach ($this->procnames as $pn) {
          $retarr[] = $pn;
          // check for vars on proc, if set add names to the array to return
          if (isset($this->processors[$pn])) {
             if (is_object($this->processors[$pn])) {
                if (isset($this->processors[$pn]->vars)) {
                   if (is_array($this->processors[$pn]->vars)) {
                      foreach ($this->processors[$pn]->vars as $procvar) {
                         if (!in_array($procvar, $retarr)) {
                            $retarr[] = $procvar;
                         }
                      }
                   }
                }
             }
          }
       }
    }
    return $retarr;
  }
  function getPublicInputs($entity) {
    // taken directly from om library -- will revisit after full porting
    return array();
    // gets all viewable variables
    if (is_array($this->inputnames)) {
       return $this->inputnames;
    } else {
       return array();
    }
  }
  function getPublicComponents($entity) {
    // taken directly from om library -- will revisit after full porting
    return array();
    // gets all viewable variables
    if (is_array($this->compnames)) {
       return $this->compnames;
    } else {
       return array();
    }
  }
  function getPrivateProps($entity) {
    // taken directly from om library -- will revisit after full porting
    return array();
    // gets all viewable variables in the local context only
    $privitz = array();

    return $privitz;
  }

  public function formRowRender(&$rowvalues, &$row) {
    // special render handlers when displaying in a grouped property block
    // show select list for varid
    // update form via ajax when changed
    parent::formRowRender($rowvalues, $row);
  }
  
  public function setUp(&$entity) {
    //dpm($entity, 'setUp()');
  }
  
  public function load(&$entity) {
    // get field default basics
  }
  public function saveObjectClass(&$entity) {
    // get field default basics
    // @todo: this should be done in getDefaults() function 
    if ($this->object_class) {
      $values = array(
        'entity_type' => $entity->entityType(),
        'propcode' => $this->object_class,
        'propname' => 'object_class',
        'featureid' => $entity->identifier(),
        'varid' => dh_varkey2varid('om_object_class', TRUE),
      );
      dh_update_properties($values, 'singular');
      //error_log("Saving object_class: " . print_r($values,1));
    }
  }
  public function getDefaults($entity, &$defaults = array()) {
    // Example:
    /*
    $defaults += array(
      'berry_weight_g' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 0.0,
        'propname' => 'Berry Weight',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'berry_weight_g',
        'varid' => dh_varkey2varid('berry_weight_g', TRUE),
        'embed' => TRUE, // defaults to TRUE, set this to FALSE to prevent embedding
      ),
    );
    */
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
      //dpm($thisone,'adding default');
      dh_update_properties($thisone, $thisone['singularity']);
    }
  }
  
  public function insert(&$entity) {
    //$entity->propname = 'blankShell';
    $this->saveObjectClass($entity);
    $this->addProperties($entity);
    parent::insert($entity);
  }
  
  public function update(&$entity) {
    //$entity->propname = 'blankShell';
    $this->saveObjectClass($entity);
    parent::update($entity);
    // should we do this here?
    //$this->synchronize($entity);
  }
  
  public function save(&$entity) {
    //$entity->propname = 'blankShell';
    parent::save($entity);
    // now, find out if we are suppose to sync to a remote server
    // 1. $elid = findRemoteOMElement($entity, $path) ; this returns $elid and increments $path
    // 2. if $elid = 0 then no remote sync
    // 3. Determine how to save
    $path = array(); // we init here, since save() shouldn't be called in this chain on any upstream objects
    $this->synchronize($entity);
  }
  
  public function synchronize(&$entity) {
    dsm("New synchronize method used");
    $elid = $this->findRemoteOMElement($entity, $path);
    // take the last parent out since that is just the name of the model element
    // and we don't need that, since we have the elementid 
    // if this was a form API use case we could keep the parent name
    array_pop($path);
    // $path will be modified by the methods
    // the property set_remote allows us to disable this functionality, for example
    // if we are doing an insert from an import, we wouldn't want to do this.
    if ($elid > 0) {
      $this->setAllRemoteProperties($entity, $elid, $path);
      if (count($path) == 0) {
        // if path is zero length it means that this is an exact match, so set the vahydro_hydroid prop 
        // on the OM element 
        $this->setRemoteProp($entity, $elid, array('value', 'vahydro_hydroid'), $entity->pid, 'textField');
      }
    }
  }
  
  public function setAllRemoteProperties($entity, $elid, $path) {
    // this is to be done on save.  The base class saves nothing
    // subclasses can save other things
  }
  
  public function setRemoteProp($entity, $elid, $path, $propvalue, $object_class = FALSE, $mode = '') {
    if ( ($this->set_remote === '0') or ($entity->set_remote === 0)) {
      //error_log("set_remote = FALSE - returning without setting $entity->propname");
      return;
    } else {
      //$db = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
      //error_log("Debug:" . print_r($db,1));
      //error_log("set_remote = $this->set_remote - Setting remote for $entity->propname");
    }
    // object_class ONLY refers to the base component being added to a model element
    // if a nested property is being set, like a matrix on a hydroImpSmall, the object STILL
    // refers to hydroImpSmall, and the parent component has to be able to handle the prop by name
    // this is due to the limitation of the OM data model, this won't be a limit when we go to full json objects
    // but then this will be obsolete.
    if ( ($object_class == FALSE) and (count($path) > 1)) {
      $object_class = $this->object_class;
      //watchdog('om', "Missing objectclass called for a nested OM property $propname on $parentname");
    }
    // all subclasses should handle this in
    // 
    $setstr = FALSE;
    $test_only = FALSE;
    // handle when the propvalue is an object, if it has a plugin
    if (is_object($propvalue)) {
      $plugin = dh_variables_getPlugins($propvalue);
      if (method_exists($plugin, 'getPropertyAttribute')) {
        $propvalue = $plugin->getPropertyAttribute($propvalue);
      } else {
        $propvalue = $propvalue->propvalue;
      }
    }
    // @todo
    // constants should have 1 layer lower, 
    // Ex: a max_storage property on an impoundment comes in as 3 parents, 
    //     but really should be handled by the 2 parent code
    // Ex: run_mode on the parent model object should resolve 2 1 parent, but comes in as 2
    // Equations ARE handled correctly however, since the 
    //dpm($path,'path');
    //dpm($entity,'entity');
    //dsm("Handling a property on a subcomp of the element ($elid) depth = " . count($path));
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
        // setprop_mode = 'json' for matrices, '' is default
        $setstr = "php set_subprop.php $elid $parentname \"$propname=$propvalue\" $object_class 0 ";
        // @todo: change syntax from elid propname "subpropname=value" parent_object_class overwrite
        //        to:
        //        elid propname subpropname subpropvalue parent_object_class setprop_mode overwrite 
        $setstr = "php set_subprop.php $elid $parentname \"$object_class\" $propname \"$propvalue\" \"$mode\" 0 ";
      break;
      case 3:
      // @todo: this would be a sub-comp of a sub-comp,
      //        Ex: a matrix on a subcomp, like the land use matrix of a CBP object or the stage-storage of lake
      //        Or, like an equation on a subcomp like channelObject
      //        at this time I don't think we should have any Equations sub-subs, but just in case 
        list($propname, $parentname, $grandparentname) = $path;
        // this is a property on a subcomp of the element
        //$setstr = "php set_subprop.php $elid $parentname $object_class $propname \"$propvalue\" \"$mode\" 0 ";
        $setstr = "php set_subprop.php $elid $grandparentname $object_class $parentname:$propname \"$propvalue\" \"$mode\" 0 ";
        // @todo: change syntax from elid propname "subpropname=value" parent_object_class overwrite
        //        to:
        //        elid propname subpropname subpropvalue parent_object_class setprop_mode overwrite 
        //dpm( $setstr, "3 level subcomp not yet handled -- will not execute ");
        $test_only = FALSE;
      break;
      default:
        drupal_set_message("Can not handle remote update of properties with depth = " . count($path));
      break;
    }
    if ($setstr and !$test_only) {
      $cmd = "cd $this->path \n";
      $cmd .= $setstr;
      //dpm( $path, "Exec Path ");
      //dpm( $cmd, "Executing ");
      shell_exec($cmd);
    }
    if ($test_only) {
      $cmd = "Testing Only. \n";
      $cmd = "cd $this->path \n";
      $cmd .= $setstr;
      //dpm( $path, "Testing Path ");
      //dpm( $cmd, "Testing to execute ");
    }
  }
  
  public function getRemoteProp($entity, $elid, $path, $object_class = FALSE) {
    // this element connection does not currently use this, but its children props might
    // @todo: make this work - for now just return
    if ( ($object_class == FALSE) and (count($path) > 1)) {
      $object_class = $this->object_class;
      //watchdog('om', "Missing objectclass called for a nested OM property $propname on $parentname");
    }
    // @todo:
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
    //dpm($path,'path');
    //dsm("Handling a property on a subcomp of the element ($elid) depth = " . count($path));
    switch (count($path)) {
      case 1:
        list($propname) = $path;
        // this is a property of the element itself
        // @todo: set a regular attribute using batch_setprop.php
        $setstr = "php getprop.php $elid \"$propname=$propvalue\" ";
      break;
      case 2:
        list($propname, $parentname) = $path;
        // this is a property on a subcomp of the element
        $setstr = "php get_subprop.php $elid $parentname \"$propname=$propvalue\" $object_class 0 ";
      break;
      case 3:
      // @todo: this would be a sub-comp of a sub-comp, like an equation on a subcomp like channelObject
      //        at this time I don't think we should have any of these, but just in case 
        list($propname, $parentname, $grandparentname) = $path;
        // this is a property on a subcomp of the element
        $setstr = "php get_subprop.php $elid $parentname \"$propname=$propvalue\" $object_class 0 ";
        //dpm( $setstr, "3 level subcomp not yet handled -- will not execute ");
        $setstr = FALSE;
      break;
      default:
        drupal_set_message("Can not handle remote update of properties with depth = " . count($path));
      break;
    }
    if ($setstr and !$test_only) {
      $cmd = "cd $this->path \n";
      $cmd .= $setstr;
      //dpm( $cmd, "Executing ");
      shell_exec($cmd);
    }
  }
    
  public function findRemoteOMElement($entity, &$path) {
    //dpm($entity, "findRemoteOMElement @ depth = $path");
    $elid = 0;
    $path[] = $entity->propname;
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
      $this->set_remote = $elvar->propcode;
    } else {
      // get parent
      $parent = $this->getParentEntity($entity);
      //dpm($parent,"Looking at object parent for remote element link.");
      if (isset($parent->dh_variables_plugins) and is_array($parent->dh_variables_plugins)) {
        foreach ($parent->dh_variables_plugins as $plugin) {
          if (is_object($plugin) and method_exists($plugin, 'findRemoteOMElement')) {
            $elid = $plugin->findRemoteOMElement($parent, $path);
          }
          $this->set_remote = property_exists($plugin, 'set_remote') ? $plugin->set_remote : 0;
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
  
  public function formRowEdit(&$form, $entity) {
    // special render handlers when displaying in a grouped property block
    //dpm($entity, 'entity');
    parent::formRowEdit($form, $entity);
    $this->hideFormRowEditFields($form);
    if ($this->object_class) {
      //$form['varid']['#default_value'] = 'om_class_BlankShell';
      $opts = dh_vardef_varselect_options(array("vocabulary = 'om_object_classes'"));
      $form['varid']['#options'] = $opts;
      $form['varid']['#title'] = 'Model Object Type';
      $form['varid']['#description'] = 'Select object class here. ';
      $form['varid']['#type'] = 'select';
    } else {
      $form['varid']['#type'] = 'hidden';
    }
    $form['propname']['#default_value'] = empty($entity->propname) ? $this->object_class : $entity->propname;
    $form['propname']['#title_display'] = 'before';
    $form['propname']['#title'] = 'Name';
    $form['propname']['#type'] = 'textfield';
    $form['proptext']['#weight'] = 10;
    //$form['propname']['#markup'] = 'object_class';
    // if this is an existing object (has pid) 
    // check to see if it has any missing default properties,
    // if so, offer to add them automatically on save.
    // make this weight 20 so it's last thing before save button
    $defprops = $this->getDefaults($entity);
  }
  
  public function applyEntityAttribute($property, $value) {
    $property->propvalue = $value;
  }
  
  public function getPropertyAttribute($property) {
    return $property->propvalue;
  }
  
  public function dh_getValue($entity, $ts = FALSE, $propname = FALSE, $config = array()) {
    // @todo: implement om routines getPropertyAttribute() in base class 
    return $this->getPropertyAttribute($entity);
  }
}

class dHOMElementConnect extends dHOMBaseObjectClass {
  var $object_class = FALSE;
  var $can_embed = FALSE; // om_element_connection can never be embedded.
  
  public function findRemoteOMElement($entity, &$path) {
    // since this connector is the final model container, we know the elid is by definition the propvalue
    $elid = $entity->propvalue;
    // don't increment here since this is a property on another object that already increments
    //$path[] = $entity->propname;
    return $elid;
  }
  
  public function setRemoteProp($entity, $elid, $path, $propvalue, $object_class = FALSE) {
    // this element connection does not currently use this, but its children props might
  }
  public function formRowEdit(&$form, $entity) {
    $varinfo = $entity->varid ? dh_vardef_info($entity->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    $form['propcode'] = array(
      '#title' => t('Automatically Push Changes to Remote?'),
      '#type' => 'select',
      '#options' => array('0'=>'False', '1'=>'True', 'pull_once' => 'One-Time Pull Remote Properties on Save()'),
      '#description' => '',
      '#default_value' => !empty($entity->propcode) ? $entity->propcode : "",
    );
    
  }
  public function save(&$entity) {
    parent::save($entity);
    if ($entity->propcode == 'pull_once') {
      // pull from remote, then set this back to previous entity value 
      $this->pullFromRemote($entity);
      // @todo: because the entity is already updatred by the time we get here, we can't retrieve the previous synch setting, so we assume that it is OK to push remote changes after this save and poull is complete.  Why?  Can't we intercept before entity is updated?
      $entity->propcode = '1';
    }
  }
  
  public function pullFromRemote($entity) {
    global $base_url;
    $cmd = "cd " . DRUPAL_ROOT . '/' . drupal_get_path('module', 'om') . "/src/ \n";
    $cmd .= "drush om.migrate.element.php pid $entity->propvalue $entity->featureid ";
    dpm( $cmd, "Executing ");
    shell_exec($cmd);
  }
}

class dHOMModelElement extends dHOMBaseObjectClass {
  // All objects of this class and inherited by this class
  // should assume that propcode is used to describe the primary 
  // model version/scenario.  However, a model object may belong to 
  // multiple scenarios which can be defined by the om_model_scenario subcomp
  var $object_class = 'modelObject';
  
  public function hiddenFields() {
    $hidden = parent::hiddenFields();
    return $hidden;
  }
  
  public function setAllRemoteProperties($entity, $elid, $path) {
    parent::setAllRemoteProperties($entity, $elid, $path);
    // this is to be done on save.  The base class only saves name and description, 
    // subclasses can save other things
    //dpm($path, 'original path to setAllRemoteProperties()');
    array_unshift($path, 'name');
    $this->setRemoteProp($entity, $elid, $path, $entity->propname, $this->object_class);
    // removes the name 
    array_shift($path);
    array_unshift($path, 'description');
    $this->setRemoteProp($entity, $elid, $path, $entity->proptext['und'][0]['value'], $this->object_class);
    //$this->setRemoteProp($entity, $elid, $path, 'description', $this->proptext);
  }
  
  public function delete($entity) {
    // @todo: ask if we want to delete the corresponding remote
    // @todo: enable to delete the corresponding remote
    //dpm($entity,'plugin delete() method called');
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
    //dpm($path, 'original path to setAllRemoteProperties()');
    //dpm($entity, 'subcomp entity to setAllRemoteProperties()');
    if (property_exists($entity, 'proptext')) {
      array_unshift($path, 'description');
      $this->setRemoteProp($entity, $elid, $path, $entity->proptext['und'][0]['value'], $this->object_class);
      //$this->setRemoteProp($entity, $elid, $path, 'description', $this->proptext);
    }
  }
  
  public function delete($entity) {
    dpm($entity,'plugin delete() method called');
    $comp_path = array(); // initialize the path var. 
                     // We will than use it later to determine if we should 
                     // Delete the remote element 
    $propname = $entity->propname;
    $elid = $this->findRemoteOMElement($entity, $comp_path);
    dpm($comp_path,'Delete subcomp path');
    dpm($elid,'Delete subcomp elid');
    // findRemoteElement
    // Check path depth - if this is a 1st level sub-comp delete, if not, return 
    switch (count($comp_path)) {
      case 2:
        list($propname, $parentname) = $path;
        // this is a property on a subcomp of the element
        $setstr = "php delete_subcomp.php $elid $propname ";
      break;
    }
    if ($setstr and !$test_only) {
      $cmd = "cd $this->path \n";
      $cmd .= $setstr;
      dpm( $cmd, "Executing ");
      shell_exec($cmd);
    }
  }
}

class dHOMEquation extends dHOMSubComp {
  var $object_class = 'Equation';
  
  public function getDefaults($entity, &$defaults = array()) {
    $defaults = parent::getDefaults($entity, $defaults);
    $defaults += array(
      'defaultval' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propname' => 'defaultval',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'vardesc' => 'Initial value.',
        'varname' => 'Initial Value',
        'varid' => dh_varkey2varid('om_class_AlphanumericConstant', TRUE),
      ),
    );
    return $defaults;
  }
  
  public function formRowEdit(&$rowform, $entity) {
    parent::formRowEdit($rowform, $entity);
    $rowform['propcode']['#title'] = '';
    $rowform['propcode']['#prefix'] = ' = ';
  }
  
  public function setAllRemoteProperties($entity, $elid, $path) {
    parent::setAllRemoteProperties($entity, $elid, $path);
    array_unshift($path, 'equation');
    $this->setRemoteProp($entity, $elid, $path, $entity->propcode, $this->object_class);
  }
  
  public function applyEntityAttribute($property, $value) {
    $property->propcode = $value;
  }
}

//class dHOMAlphanumericConstant extends dHVariablePluginDefault {
class dHOMAlphanumericConstant extends dHOMBaseObjectClass {
  var $object_class = 'textField';
  
  public function hiddenFields() {
    return array('varname', 'startdate', 'enddate','featureid','entity_type', 'propname','propvalue','dh_link_admin_pr_condition');
  }
  public function formRowEdit(&$form, $entity) {
    parent::formRowEdit($form, $entity);
    if (!$entity->varid) {
      return FALSE;
    }
    $form['propcode'] = array(
      '#title' => t($entity->varname),
      '#type' => 'textfield',
      '#description' => $entity->vardesc,
      '#default_value' => !empty($entity->propcode) ? $entity->propcode : "",
    );
  }
  public function attachNamedForm(&$form, $entity) {
    $varinfo = $entity->varid ? dh_vardef_info($entity->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    // create a blank to house the original form info
    $pform = array();
    $this->formRowEdit($pform, $entity);
    // harvest pieces I want to keep
    $mname = $this->handleFormPropname($entity->propname);
    $form[$mname] = $pform['propcode'];
  }
  
  public function applyEntityAttribute($property, $value) {
    $property->propcode = $value;
  }
  
  public function getPropertyAttribute($property) {
    return $property->propcode;
  }
  
  public function buildContent(&$content, &$entity, $view_mode) {
    // @todo: handle teaser mode and full mode with plugin support
    foreach ($this->hiddenFields() as $hide) {
      unset($content[$hide]);
    }
    switch ($view_mode) {
      case 'plugin':
      case 'teaser':
      default:
        $content['propname'] = array(
          '#type' => 'item',
          '#markup' => "<b>Name:</b> $entity->propname<sub>($entity->varname)</sub>"
        );
        $content['propcode'] = array(
          '#type' => 'item',
          '#markup' => "<b>Code:</b> " . $entity->propcode,
        );
      break;
    }
  }
  
  //public function setAllRemoteProperties($entity, $elid, $path) {
  //  parent::setAllRemoteProperties($entity, $elid, $path);
  //  // this sets only the variable on the base object
  //  //array_shift($path);
  //  $this->setRemoteProp($entity, $elid, $path, $entity->propcode, $this->object_class);
  //}
}


class dHOMConstant extends dHOMBaseObjectClass {
  // changed inheritance to support remote OM editing.
//class dHOMConstant extends dHVariablePluginNumericAttribute {
  // numeric constant 
  // this can be a stand-alone property, with it's own save() method unlike
  //   unlike the alphanumeric constants that are just embedded in the object edit form and 
  //   do not have their own save methods.
  //   This will be seldom used, as virtually all setting fields will be attached to something (like run_mode)
  // But WILL be used for object class attributes in OM (like area, slope, etc.)
  var $object_class = FALSE;
  var $default_value = 0;
  var $pct_range = array('<5', 10, 25, 50, 75, 90, 100); // @todo - allow default can be over-ridden in getDefaults() code
  
  public function hiddenFields() {
    $hidden = array_merge(array('propcode', 'startdate', 'enddate'), parent::hiddenFields());
    return $hidden;
  }
  
  public function setAllRemoteProperties($entity, $elid, $path) {
    parent::setAllRemoteProperties($entity, $elid, $path);
    // this sets only the variable on the base object
    //array_shift($path);
    $this->setRemoteProp($entity, $elid, $path, $entity->propvalue, $this->object_class);
  }
  
  public function formRowEdit(&$form, $entity) {
    parent::formRowEdit($form, $entity);
    if (!$entity->varid) {
      return FALSE;
    }
    $form['propvalue'] = array(
      '#title' => t($entity->varname),
      '#type' => 'textfield',
      '#description' => $entity->vardesc,
      '#default_value' => $entity->propvalue,
    );
    if (property_exists($varinfo, 'datatype')) {
      switch ($varinfo->datatype) {
        case 'percent':
        $opts = $this->pct_list($this->pct_range);
        $rowform['propvalue']['#type'] = 'select';
        $rowform['propvalue']['#options'] = $opts;
        $rowform['propvalue']['#empty_option'] = 'n/a';
        break;
        case 'boolean':
        $opts = array(0 => 'False', 1 => 'True');
        $rowform['propvalue']['#type'] = 'select';
        $rowform['propvalue']['#options'] = $opts;
        $rowform['propvalue']['#default_value'] = !empty($entity->propvalue) ? $entity->propvalue : "$this->pct_default";
        break;
      }
    }
  }
  
  public function pct_list($inc = 10) {
    $pcts = array();
    if (is_array($inc)) {
      // we already have our list of percents, just work it out
      foreach ($inc as $i) {
        $dec = floatval(preg_replace('/\D/', '', $i)) / 100.0;
        $pcts["$dec"] = $i . " %";
      }
    } else {
      $i = $inc;
      while ($i <= 100) {
        $dec = floatval($i) / 100.0;
        $pcts["$dec"] = $i . " %";
        $i += $inc;
      }
    }
    return $pcts;
  }
  
  public function applyEntityAttribute($property, $value) {
    $property->propvalue = $value;
  }
  
  public function getPropertyAttribute($property) {
    return $property->propvalue;
  }
  
  public function attachNamedForm(&$form, $entity) {
    $varinfo = $entity->varid ? dh_vardef_info($entity->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    // create a blank to house the original form info
    $pform = array();
    $this->formRowEdit($pform, $entity);
    // harvest pieces I want to keep
    $mname = $this->handleFormPropname($entity->propname);
    $form[$mname] = $pform['propvalue'];
  }
}

class dHOMtextField extends dHOMSubComp {
  // special subcomp for alpha info
  var $object_class = 'textField';
  public function hiddenFields() {
    return array('varname', 'startdate', 'enddate','featureid','entity_type', 'propvalue','dh_link_admin_pr_condition');
  }
  
  public function getDefaults($entity, &$defaults = array()) {
    // getDefaults is required to be compatible with om.migrate.element.php
    // could add a check for that to not call getDefaults, but for now, just put it here
    return $defaults;
  }
  public function setAllRemoteProperties($entity, $elid, $path) {
    parent::setAllRemoteProperties($entity, $elid, $path);
    //dsm("setAllRemoteProperties from dHOMtextField");
    array_unshift($path, 'value');
    $this->setRemoteProp($entity, $elid, $path, $entity->propcode, $this->object_class);
  }
  public function formRowEdit(&$form, $entity) {
    parent::formRowEdit($form, $entity);
    if (!$entity->varid) {
      return FALSE;
    }
    $form['propname'] = array(
      '#title' => t('Name'),
      '#type' => 'textfield',
      '#description' => $entity->vardesc,
      '#default_value' => !empty($entity->propname) ? $entity->propname : "",
    );
    $form['propcode'] = array(
      '#title' => t($entity->varname),
      '#type' => 'textfield',
      '#description' => 'Value for this text variable',
      '#default_value' => !empty($entity->propcode) ? $entity->propcode : "",
    );
  }
  public function attachNamedForm(&$form, $entity) {
    $varinfo = $entity->varid ? dh_vardef_info($entity->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    // create a blank to house the original form info
    $pform = array();
    $this->formRowEdit($pform, $entity);
    // harvest pieces I want to keep
    $mname = $this->handleFormPropname($entity->propname);
    $form[$mname] = $pform['propcode'];
  }
  
  public function applyEntityAttribute($property, $value) {
    $property->propcode = $value;
  }
  
  public function getPropertyAttribute($property) {
    return $property->propcode;
  }
}

class dHOMObjectClass extends dHVariablePluginCodeAttribute {
  
}

class dHOMPublicVars extends dHOMAlphanumericConstant {
  var $object_class = 'textField';
  
  public function formRowEdit(&$form, $entity) {
    parent::formRowEdit($form, $entity);
    if (!$entity->varid) {
      return FALSE;
    }
    $public_vars = $this->getPublicVars($entity);
    //dpm($public_vars,'public vars');
    $form['propcode'] = array(
      '#title' => t($entity->varname),
      '#type' => 'select',
      '#empty_option' => t('- Select -'),
      '#options' => array_combine($public_vars, $public_vars),
      '#description' => $entity->vardesc,
      '#default_value' => !empty($entity->propcode) ? $entity->propcode : "",
    );
  }
  
  public function getPublicVars($entity, &$publix = array()) {
    $parent = $this->getParentEntity($entity);
    $plugin = dh_variables_getPlugins($parent);
    if ($plugin) {
    //dpm($plugin,'plugin');
      if (method_exists($plugin, 'getPublicVars')) {
        $plugin->getPublicVars($entity, $publix);
      }
    }
    return $publix;
  }
}

class dHOM_ModelScenario extends dHVariablePluginDefault {
  var $object_class = FALSE;
}

class dHOMDataMatrix extends dHOMSubComp {
  var $object_class = 'DataMatrix';
  var $default_bundle = 'om_data_matrix';
  var $matrix_field = 'field_dh_matrix';
  
  public function hiddenFields() {
    return array('pid', 'propcode', 'startdate', 'enddate', 'varid', 'featureid', 'entity_type', 'bundle','dh_link_admin_pr_condition');
  }
  
  public function entityDefaults(&$entity) {
    //dpm($entity,'entity');
    // special render handlers when displaying in a grouped property block
    $entity->bundle = $this->default_bundle;
    $datatable = $this->tableDefault($entity);
    $this->setCSVTableField($entity, $datatable);
    //dpm($entity, 'entityDefaults');
  }
  
  public function getDefaults($entity, &$defaults = array()) {
    $defaults = parent::getDefaults($entity, $defaults);
    $defaults += array(
      'rowkey' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propname' => 'rowkey',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'vardesc' => 'Variable to use for row lookup if 1-d or 2-d lookup type.',
        'varname' => 'Row Key',
        //'varid' => dh_varkey2varid('om_class_AlphanumericConstant', TRUE),
        'varid' => dh_varkey2varid('om_class_PublicVars', TRUE),
      ),
      'colkey' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propname' => 'colkey',
        'vardesc' => 'Variable to use for column lookup if 2-d lookup type.',
        'varname' => 'Column Key',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        //'varid' => dh_varkey2varid('om_class_AlphanumericConstant', TRUE),
        'varid' => dh_varkey2varid('om_class_PublicVars', TRUE),
      ),
      'lutype1' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propname' => 'lutype1',
        'vardesc' => 'Row Lookup Type.',
        'varname' => 'Row Lookup Type',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varid' => dh_varkey2varid('om_class_Constant', TRUE),
      ),
    );
    return $defaults;
  }
  
  public function load(&$entity) {
    // get field default basics
    //dpm($entity, 'load()');
    parent::load($entity);
    if ($entity->is_new or $entity->reset_defaults) {
      $datatable = $this->tableDefault($entity);
      $this->setCSVTableField($entity, $datatable);
    }
  }
  
  // this class has a name, and a description, an exec_hierarchy and other atributes
  // @todo: add basic handling of things other than descriptions
  public function setAllRemoteProperties($entity, $elid, $path) {
    parent::setAllRemoteProperties($entity, $elid, $path);
    // @todo: move this to the base class if it checks out as OK
    //dpm($entity, 'before loadProp()');
    //$this->loadProperties($entity, FALSE);
    //dpm($path, 'original path to setAllRemoteProperties()');
    //dpm($entity, 'subcomp entity to setAllRemoteProperties()');
    
    // create Separate handlers for this if it is a component or sub-subcomponent
    // If sub-component omit the object_class since they should fail if they do not exist, rather than adding
    if (property_exists($entity, 'field_dh_matrix')) {
      $cols = $entity->field_dh_matrix['und'][0]['tablefield']['rebuild']['count_cols'];
      $om_matrix = $this->tablefieldToOMMatrix($entity->field_dh_matrix);
      $rows = $om_matrix['rows'];
      $cols = $om_matrix['cols'];
      // set rows
      $spath = $path;
      array_unshift($spath, 'numrows');
      $this->setRemoteProp($entity, $elid, $spath, $rows, $this->object_class, '');
      // set value type
      $spath = $path;
      array_unshift($spath, 'valuetype');
      $valuetype = ($cols > 2) ? 2 : 1; // 0 - array (normal), 1 - 1-col lookup, 2 - 2-col lookup
      $this->setRemoteProp($entity, $elid, $spath, $valuetype, $this->object_class, '');
      // set rowkey - i.e. keycol1 
      $spath = $path;
      array_unshift($spath, 'keycol1');
      $rowkey = $entity->rowkey; // 0 - array (normal), 1 - 1-col lookup, 2 - 2-col lookup
      $this->setRemoteProp($entity, $elid, $spath, $rowkey, $this->object_class, '');
      // set table matrix data
      $spath = $path;
      array_unshift($spath, 'matrix');
      $formatted = $om_matrix['array-1d'];
      $scsv = addslashes(json_encode($formatted));
      $this->setRemoteProp($entity, $elid, $spath, $scsv, $this->object_class, 'json-1d');
      $debug_json = json_decode(stripslashes($scsv), TRUE);
      //$this->setRemoteProp($entity, $elid, $path, 'description', $this->proptext);
      
      // set lutypes 
      $spath = $path;
      array_unshift($spath, 'lutype1');
      $rowkey = $entity->lutype1; // 0 - array (normal), 1 - 1-col lookup, 2 - 2-col lookup
      $this->setRemoteProp($entity, $elid, $spath, $rowkey, $this->object_class, '');
    }
  }
  
  public function tableDefault($entity) {
    // Returns associative array keyed table (like is used in OM)
    // This format is not used by Drupal however, so a translation 
    //   with tablefield_parse_assoc() is usually in order (such as is done in load)
    $table = array();
    $table[] = array('col1', 'col2', 'col3');
    $table[] = array('','','');
    return $table;
  }
  
  public function tablefieldToOMMatrix($field) {
    // translate tablefield to 1-D array, with values from rows left to right, top to bottom as 
    // comes from an HTML form submission in OM
    $ttrans = array('rows' => 0, 'cols' => $cols, 'array-1d' => array());
    // This relies upon finding the column and row counts in this location
    //  This is ONLY true after a tablefield is saved, which is nutso, so we insert a layer of code to handle this.
    $tablefield = om_tablefield_tablefield($field);
    $ttrans['cols'] = $tablefield['rebuild']['count_cols'];
    $ttrans['rows'] = $tablefield['rebuild']['count_rows'];
    $trat = $tablefield['tabledata'];
    //dpm($trat,'data');
    $rowkey = 0;
    foreach ($trat as $rowix => $rowvals) {
      $c = 0;
      foreach ($rowvals as $ix => $val) {
        $ttrans['array-1d'][] = $val;
        $c++;
        if ($c >= $ttrans['cols']) {
          break;
        }
      }
      $rowkey++;
    }
    return $ttrans;
  }
  
  function setCSVTableField(&$entity, $csvtable) {
    // requires a table to be set in non-associative format (essentially a csv)
    $instance = field_info_instance($entity->entityType(), $this->matrix_field, $entity->bundle);
    $field = field_info_field($this->matrix_field);
    $default = field_get_default_value($entity->entityType(), $entity, $field, $instance);
    //dpm($default,'default');
    list($imported_tablefield, $row_count, $max_col_count) = dh_tablefield_parse_array($csvtable);
    // set some default basics
    $default[0]['tablefield']['tabledata'] = $imported_tablefield;
    $default[0]['tablefield']['rebuild']['count_cols'] = $max_col_count;
    $default[0]['tablefield']['rebuild']['count_rows'] = $row_count;
    if (function_exists('tablefield_serialize')) {
      $default[0]['value'] = tablefield_serialize($field, $default[0]['tablefield']);
    } else {
      $default[0]['value'] = serialize($default[0]['tablefield']);
    }
    $default[0]['format'] = !isset($default[0]['format']) ? NULL : $default[0]['format'];
    $entity->{$this->matrix_field} = array(
      'und' => $default
    );
  }
  
  public function formRowEdit(&$form, $entity) {
    parent::formRowEdit($form, $entity);
    dpm($form,'form');
    // now, format the lookup type fields 
    $form['lutype1']['#type'] = 'select';
    $form['lutype1']['#options'] = array(
      0 => "Exact Match",
      1 => "Interpolated",
      2 => "Stair Step",
      3 => "Key Interpolate"
    );
    $form['lutype1']['#size'] = 1;
    $form['lutype1']["#empty_value"] = "";
    $form['lutype1']["#empty_option"] = "Not Set";
    $form['lutype1']["#description"] = "How to handle matching.  If this is 'Not Set' unexpected behavior may occur.";
  }
 
}

class dHOM_USGSGageObject extends dHOMModelElement {
  var $object_class = 'USGSGageObject';
}

class dHOMLinkage extends dHOMBaseObjectClass {
  var $object_class = FALSE;
  var $attach_method = 'contained';
  
  public function getDefaults($entity, &$defaults = array()) {
    $defaults = parent::getDefaults($entity, $defaults);
    $defaults += array(
      'link_type' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propname' => 'link_type',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'vardesc' => '1: parent-child link, 2: local property link, 3: remote object property link (not direct parent or child).',
        'varname' => 'Link Type',
        '#weight' => 1,
        'varid' => dh_varkey2varid('om_class_AlphanumericConstant', TRUE),
      ),
      'src_prop' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propname' => 'src_prop',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'vardesc' => 'Source Entity Property Name.',
        'varname' => 'Source Prop',
        '#weight' => 4,
        'varid' => dh_varkey2varid('om_class_AlphanumericConstant', TRUE),
      ),
      'dest_entity_type' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propname' => 'dest_entity_type',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'vardesc' => 'Destination Entity Type.',
        'varname' => 'Destination Entity Type',
        'varid' => dh_varkey2varid('om_class_AlphanumericConstant', TRUE),
        '#weight' => 5,
      ),
      'dest_entity_id' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propname' => 'dest_entity_id',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'vardesc' => 'Destination Entity Unique Identifier.',
        'varname' => 'Destination Entity ID',
        'varid' => dh_varkey2varid('om_class_AlphanumericConstant', TRUE),
        '#weight' => 6,
      ),
      'dest_prop' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propname' => 'dest_prop',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'vardesc' => 'Destination Entity Property Name.',
        'varname' => 'Destination Prop',
        'varid' => dh_varkey2varid('om_class_AlphanumericConstant', TRUE),
        '#weight' => 7,
      ),
      'src_location' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propname' => 'src_location',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'vardesc' => 'How to obtain link: null/localhost is default handled by local system. Other values: JSONAPI, RESTapi, and OMapi.',
        'varname' => 'Source Location',
        'varid' => dh_varkey2varid('om_class_AlphanumericConstant', TRUE),
        '#weight' => 8,
      ),
      'src_uri' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propname' => 'src_uri',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'vardesc' => 'Null/localhost is for local, rest, json or OM path to getProp.php and setProp.php.',
        'varname' => 'Source URI',
        'varid' => dh_varkey2varid('om_class_AlphanumericConstant', TRUE),
        '#weight' => 9,
      ),
      'update_setting' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propname' => 'update_setting',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'vardesc' => 'Valid settings: create (first time only), update, step, all. Only step objects are executed during model simulations.',
        'varname' => 'Update Setting',
        'varid' => dh_varkey2varid('om_class_AlphanumericConstant', TRUE),
        '#weight' => 10,
      ),
    );
    return $defaults;
  }
  
  public function formRowEdit(&$rowform, $entity) {
    parent::formRowEdit($rowform, $entity);
    // @todo:
    // - Link Type Select List
    // - Entity + Property Browser w/
    //   - a select list of local parent & child entities directly connected.
    //   - Visible if link type is local property (parent or child) or parent:child container link
    // - Remote entity search
    //   - user enters entity_type, name, location (default localhost), and/or elementid 
    //   - uses local search facility or REST/JSONAPI if remote 
    $rowform['propcode']['#title'] = 'Source Entity Type';
    $rowform['propcode']['#weight'] = 2;
    $rowform['propvalue']['#title'] = 'Source Entity ID';
    $rowform['propvalue']['#weight'] = 3;
  }
  
  public function updateProperties(&$entity) {
    parent::updateProperties($entity);
    // looks at link info,
    // if this is a remote or local property link
    // and if update_setting == 'update' or 'all' 
    // retrieve the linked data.
    //dpm($entity,'save model linkage');
    $src_location = empty($entity->src_location->propcode) ? 'localhost' : $entity->src_location->propcode;
    $update_setting = empty($entity->update_setting->propcode) ? 'none' : $entity->update_setting->propcode;
    switch ($update_setting) {
      case 'all':
      case 'update':
      // type 1 is a parent child, so no updates to make.
      if (in_array($entity->link_type->propcode, array(2,3))) {
        switch ($src_location) {
          case 'localhost':
            $linked_value = $this->getLocalhostLinkedValue($entity);
            //dsm("Found $linked_value ");
            $this->setLocalhostLinkedValue($entity, $linked_value);
          break;
          // @todo: handle other types besides localhost
        }
      }
      break;
    }
  }
  
  function setLocalhostLinkedValue(&$entity, $linked_value) {
    $dest_entity_type = $entity->dest_entity_type->propcode;
    $dest_entity_id = $entity->dest_entity_id->propcode;
    $dest_prop = $entity->dest_prop->propcode;
    // @todo: we don't yet use the dest_entity_type, or dest_entity_id since 
    //        we assumed this is attached to a parent property to set value 
    //        but we may later allow this
    $dest_entity = $this->getParentEntity($entity);
    if (is_object($dest_entity)) {
      if (property_exists($dest_entity, $dest_prop)) {
        $dest_entity->{$dest_prop} = $linked_value;
        $dest_entity->save();
        //dpm($dest_entity, "Saved dest entity");
      }
    }
  }
  
  function getLinkedEntity(&$entity) {
    $entity->src_entity_type = $entity->propcode;
    $entity->src_entity_id = $entity->propvalue;
    $entity->src_entity = entity_load_single($entity->src_entity_type, $entity->src_entity_id);
    return $entity->src_entity;
  }
  
  function getLocalhostLinkedValue(&$entity) {
    //dpm($entity,'getLocalhostLinkedValue entity');
    //dpm($entity->src_entity,'getLocalhostLinkedValue src_entity');
    if (!$entity->src_entity) {
      $this->getLinkedEntity($entity);
    }
    if (is_object($entity->src_entity)) {
      // check if prop already exists, if so, just grab it,
      // otherwise, try to load a dh_property with the target name 
      if (!empty($entity->src_prop->propcode)) {
        $src_prop = $entity->src_prop->propcode;
        if (property_exists($entity->src_entity, $src_prop)) {
          $linked_value = $entity->src_entity->{$src_prop};
        } else {
          $conds = array();
          $conds[] = array(
            'name' => 'propname',
            'value' => $src_prop
          );
          $loaded = $entity->src_entity->loadComponents($conds);
          //dpm($entity->src_entity,'source entity');
          if (count($loaded) > 0) {
            $loname = strtolower($src_prop);
            $src_object = $entity->src_entity->dh_properties[$loname];
            // @todo: support linking propcode or other values on dh_properties
            $linked_value = $src_object->propvalue;
          } else {
            watchdog('om', "OMLinkage could not find src_prop " . $src_prop);
          }
        }
      } else {
        watchdog('om', "Missing src_prop on OMLinkage config.");
      }
    }
    return $linked_value;
  }
  
  public function setAllRemoteProperties($entity, $elid, $path) {
    // @todo: this is a special entity that lives in its own table in 
    //        the om 1.0 system.  This should simply utilize the 
    //        w_linkElements script plumbing 
    // For now, we just return.
    return;
    // Copied from Equation class - to be modified.
    //parent::setAllRemoteProperties($entity, $elid, $path);
    //array_unshift($path, 'equation');
    //$this->setRemoteProp($entity, $elid, $path, $entity->propcode, $this->object_class);
  }
  public function findRemoteOMElement($entity, &$path) {
    // do not pass to sub-props as this does not propagate. (yet!)
    return 0;
  }
  
  public function buildContent(&$content, &$entity, $view_mode) {
    // @todo: handle teaser mode and full mode with plugin support
    parent::buildContent($content, $entity, $view_mode);
    $this->getLinkedEntity($entity);
    //dpm($entity->src_entity,'ent to content');
    switch ($view_mode) {
      case 'plugin':
      case 'teaser':
      default:
        if (is_object($entity->src_entity) and method_exists($entity->src_entity, 'entityType')) {
          // this is a local drupal entity, we can handle it 
          $content['remote'] = array(
            '#type' => 'link',
            '#title' => "From: " . $entity->src_entity->label(),
            '#href' => "admin/content/" . $entity->src_entity->entityType() . "/manage/" . $entity->src_entity->identifier() . "/view",
          );
        }
      break;
    }
  }  
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

?>