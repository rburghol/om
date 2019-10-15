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
  $values = array(
    'entity_type' => $element['entity_type'],
    'propname' => $element['propname'],
    'featureid' => $element['src_id']
  );
  $src_prop = om_get_property($values, 'name');
  //error_log("prop:" . print_r((array)$src_prop));
  $copy_values = array();
  $info = $src_prop->entityInfo();
  $copyable = array('varid') + $info['property info'];
  error_log("Info:" . print_r($copyable,1));
  $fields = field_info_instances('dh_properies', $src_prop->bundle);
  error_log("fields: on $src_prop->bundle" . print_r($fields,1));
  die;
  foreach ($copyable as $pname) {
    if (isset($src_prop->{$pname})) {
      $copy_values[$pname] = $src_prop->{$pname};
    }
  }
  // add or replace new property with copy values 
}



?>