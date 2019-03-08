<?php

// load routines needed for tablefield handling
module_load_include('module', 'dh_wsp');

function om_get_property($values, $singularity = 'name') {
  $pids = dh_get_properties($values, $singularity);
  $om_model = FALSE;
  if (count($pids) > 0) {
    $pid = array_shift($pids);
    $om_model = entity_load_single('dh_properties', $pid->pid);
  }
  return $om_model;
}

function om_create_property($values, $singularity = 'name') {
  error_log("$cache[varkey] does not exist...creating");
  $om_model = dh_properties_enforce_singularity($values, $singularity);
  return $om_model;
}

function om_translate_to_dh($om_comp, &$dh_prop) {
  $translated = FALSE;
  // load object plugin if exists
  $plugin = count($dh_prop->dh_variables_plugins) > 0 ? $dh_prop->dh_variables_plugins[0] : FALSE;
  $default_subprops = $plugin->getDefaults($dh_prop);
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
      $translated = TRUE;
      break;
  
  }
  return $translated;
}

function om_get_dh_varkey($om_comp) {
  $q = "  select varkey from {dh_variabledefinition} ";
  $q .= " where varcode = :om_class and vocabulary = 'om_object_classes'";
  $result = db_query($q, array(':om_class' => $om_comp->object_class));
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
