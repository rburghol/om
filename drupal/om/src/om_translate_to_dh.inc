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

function om_setTableField(&$entity, $luval_matrix_field, $csvtable) {
  // requires a table to be set in non-associative format (essentially a csv)
  $instance = field_info_instance($entity->entityType(), $luval_matrix_field, $entity->bundle);
  $field = field_info_field($luval_matrix_field);
  $default = field_get_default_value($entity->entityType(), $entity, $field, $instance);
  // set pointer to actual structure named $tablefield
  error_log("Default" . print_r((array)$default,1));
  if (isset($default[0]['tabledata'])) {
    // check for new kluge structure
    $tablefield = &$default[0]['tabledata'];
  } else {
    // use old structure
    $tablefield = &$default[0]['tablefield'];
  }
  //dpm($default,'default');
  list($imported_tablefield, $row_count, $max_col_count) = dh_tablefield_parse_array($csvtable);
  // set some default basics
  $tablefield['tabledata'] = $imported_tablefield;
  $tablefield['rebuild']['count_cols'] = $max_col_count;
  $tablefield['rebuild']['count_rows'] = $row_count;
  $tablefield['format'] = !isset($tablefield['format']) ? NULL : $tablefield['format'];
  // value is stored in same place regardless of module version
  if (function_exists('tablefield_serialize')) {
    $default[0]['value'] = tablefield_serialize($field, $tablefield);
  } else {
    $default[0]['value'] = serialize($tablefield);
  }
  $entity->{$luval_matrix_field} = array(
    'und' => $default
  );
}

function om_translate_to_dh($om_comp, &$dh_prop) {
  $translated = FALSE;
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
      //error_log("Before" . print_r((array)$dh_prop->field_dh_matrix,1));
      foreach ($dh_prop->dh_variables_plugins as $plugin) {
        if (method_exists($plugin, 'setCSVTableField')) {
          $plugin->setCSVTableField($dh_prop, $in);
        }
      }
      //list($out, $numrows, $numcols) = dh_tablefield_parse_array($in);
      //error_log("Input matrix: " . print_r($in,1));
      //error_log("Output matrix: " . print_r($out,1));
      //om_setTableField($dh_prop, 'field_dh_matrix', $out);
      //error_log("After" . print_r((array)$dh_prop->field_dh_matrix,1));
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
