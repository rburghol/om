<?php

// load routines needed for tablefield handling
module_load_include('module', 'dh_wsp');


function om_get_property($values, $singularity = 'name') {
  $pids = dh_get_properties($values, $singularity);
  error_log("Pids:" . print_r((array)$pids, 1)); 
  $om_model = FALSE;
  if (count($pids) > 0) {
    $pid = array_shift($pids['dh_properties']);
    $om_model = entity_load_single('dh_properties', $pid->pid);
  }
  return $om_model;
}

function om_create_property($values, $singularity = 'name') {
  error_log("Object does not exist...creating " . print_r($values,1));
  $om_model = dh_properties_enforce_singularity($values, $singularity);
  return $om_model;
}

function om_translate_to_dh($om_comp, &$dh_prop) {
  $translated = FALSE;
  error_log("om_translate_to_dh() for $om_comp->object_class ");
  // load object plugin if exists
  $plugin = dh_variables_getPlugins($dh_prop);
  if (is_object($plugin )) {
    error_log("Calling getDefaults on " . get_class($plugin ));
    $default_subprops = $plugin->getDefaults($dh_prop);
    error_log("Obtained defaults: " . print_r(array_keys($default_subprops),1));
  }
  
  switch ($om_comp->object_class) {
    case 'dataMatrix':
      $dh_prop->rowkey = $om_comp->keycol1;
      $dh_prop->colkey = $om_comp->keycol2;
      //$dh_prop->field_mytablefield['und'][0]['value'] = serialize($yourtable);
      $in = $om_comp->matrix_rowcol;
      array_walk(
        $in, 
        function(&$item, $key) {
          $item = (array)$item;
        }
      );
      if (method_exists($plugin, 'setCSVTableField')) {
        $plugin->setCSVTableField($dh_prop, $in);
      }
      $translated = TRUE;
    break;
    case 'Equation':
      $dh_prop->propcode = $om_comp->equation;
      $dh_prop->defaultval = $om_comp->defaultval;
      if (length($dh_prop->propcode) > 255) {
        // set to proptext
        // @todo: set to proptext of a subcomp-subcomp and handle in plugin
      }
      $translated = TRUE;
    break;
    case 'USGSGageSubComp':
    case 'USGSGageObject':
      $dh_prop->propcode = $om_comp->staid;
      $dh_prop->propvalue = $om_comp->sitetype; #sitetype = 1 - stream gage, 2 - groundwater, 3 - reservoir level;
      $translated = TRUE;
    break;
  
  }
  if ($translated) {
    error_log("Translated $om_comp->object_class to $dh_prop->propname");
  } else {
    error_log("Could not translate");
  }
  return $translated;
}

function om_get_dh_varkey($om_comp) {
  $duplicates = array(
    'USGSGageSubComp' => 'USGSGageObject'
  );
  $object_class = 
    isset($duplicates[$om_comp->object_class]) 
    ? $duplicates[$om_comp->object_class]
    : $om_comp->object_class
  ;
  //error_log(" This object_class = $object_class");
  
  $q = "  select varkey from {dh_variabledefinition} ";
  $q .= " where varcode = :om_class and vocabulary = 'om_object_classes'";
  $result = db_query($q, array(':om_class' => $object_class));
  $varkey = $result->fetchField();
  return $varkey;
}

function dh_get_dh_propnames($entity_type, $entity_id) {
  $q = "  select propname from {dh_properties} ";
  $q .= " where featureid = :entity_id and entity_type = :entity_type";
  $result = db_query($q, array(':entity_id' => $entity_id, ':entity_type' => $entity_type));
  $props = $result->fetchCol();
  return $props;
}
