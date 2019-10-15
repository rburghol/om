#!/user/bin/env drush
<?php
module_load_include('inc', 'om', 'src/om_translate_to_dh');

$args = array();
while ($arg = drush_shift()) {
  $args[] = $arg;
}

// Is single command line arg?
if (count($args) >= 3) {
  $query_type = $args[0];
  $entity_type = $args[1];
  $src_id = $args[2];
  $dest_id = $args[3];
  $propname = $args[4];
} else {
  error_log("Usage: php copy_subcomps.php query_type entity_type src_id dest_id [all/propname[|newname],sub2,...] [cascade=0/1]");
  error_log("Note: 'all' is not yet enabled");
  die;
}

function om_get_copyable($src_prop) {
  // standard fields
/*
  $copyable = array(
    'propname' = array('required', 
    'propvalue', 
    'startdate', 
    'enddate', 
    'propcode', 
    'varid'
  );
  */
  // load field info
  
  
  return $copyable;
}

if ($query_type == 'file') {
  $filepath = $src_id;
  error_log("File requested: $filepath");
  $data = array();
  $file = fopen($filepath, 'r');
  $header = fgetcsv($file, 0, "\t");
  if (count($header) == 0) {
    $header = fgetcsv($file, 0, "\t");
  }
  while ($line = fgetcsv($file, 0, "\t")) {
    
    $data[] = array_combine($header,$line);
  }
  error_log("File opened with records: " . count($data));
  error_log("Header: " . print_r($header,1));
  error_log("Record 1: " . print_r($data[0],1));
} else {
  $data = array();
  $data[] = array(
    'entity_type' => $entity_type, 
    'src_id' => $src_id, 
    'dest_id' => $dest_id,
    'propname' => $propname,
  );
}

foreach ($data as $element) {
  if (
    empty($element['entity_type'])
    or empty($element['propname'])
    or empty($element['src_id'])
    or empty($element['dest_id'])
  ) {
    error_log("Could not process " . print_r($element,1));
    continue;
  }
  $values = array(
    'entity_type' => $element['entity_type'],
    'propname' => $element['propname'],
    'featureid' => $element['src_id']
  );
  $src_prop = om_get_property($values, 'name');
  $values['featureid'] = $dest_id;
  //error_log("prop:" . print_r((array)$src_prop));
  $copy_values = array();
  $info = $src_prop->entityInfo();
  $fields = field_info_instances($src_prop->entityType(), $src_prop->bundle);
  $copyable = array_unique(array_merge(array('varid', 'bundle'), array_values($info['property info'])));
  error_log("array_keys(fields):" . print_r(array_keys($fields),1));
  error_log("copyable:" . print_r($copyable,1));
  foreach ($copyable as $pname) {
    if (isset($src_prop->{$pname})) {
      $values[$pname] = $src_prop->{$pname};
    }
  }
  error_log("To copy:" . print_r($values,1));
  // add or replace new property with copy values 
  $copy = om_model_getSetProperty($values, 'name', FALSE);
  
  $plugin = dh_variables_getPlugins($src_prop);
  if (is_object($plugin )) {
    error_log("Calling getDefaults on " . get_class($plugin ));
    $default_subprops = $plugin->getDefaults($src_prop);
    error_log("Obtained defaults: " . print_r(array_keys($default_subprops),1));
  }
  foreach ($default_subprops as $thisprop) {
    if (property_exists($src_prop, $thisprop['propname'])) {
      error_log("Setting $thisprop[propname] to " . $src_prop->{$thisprop['propname']});
      $copy->{$thisprop['propname']} = $src_prop->{$thisprop['propname']};
    }
  }
  foreach ($fields as $fieldname) {
    if (isset($src_prop->{$fieldname})) {
      $copy->{$fieldname} = $src_prop->{$pname};
    }
  }
  error_log("Made copy:" . print_r($copy,1));
  $copy->save();
  //$copy->save();
  error_log("Property $copy->propname created with pid = $copy->pid");
}



?>