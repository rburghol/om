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
  $src_entity = om_load_dh_model('object', $fid, $model_name);
  $om_link = om_load_dh_model('object', $src_entity, 'om_element_connection');
  $dest_entity = om_load_dh_model('object', $fid, $model_name);
  $om_link = om_load_dh_model('object', $src_entity, 'om_element_connection');
  // cache and disable object synch if it exists
  if (!($om_link->pid === NULL)) {
    $cc = $om_link->propcode;
    $om_link->propcode = '0';
    $om_link->save();
  }
  
  $result = om_copy_subcomp($src_entity, $dest_entity, $propname);
  //$copy->save();
  error_log("Property $copy->propname created with pid = $copy->pid");
  // restore original object synch if it exists
  if (!($om_link->pid === NULL)) {
    $om_link->propcode = $cc;
    $om_link->save();
  }
}



?>