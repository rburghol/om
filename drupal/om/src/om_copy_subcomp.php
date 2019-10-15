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
  $src_id = $args[1];
  $dest_id = $args[2];
} else {
  error_log("Usage: php copy_subcomps.php query_type src_id dest_id [all/propname[|newname],sub2,...] [cascade=0/1]");
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
    'src_id' => $src_id, 
    'dest_id' => $dest_id,
    'propname' => $propname,
  );
}

foreach ($data as $element) {
  $src_prop = om_get_property($values, 'name');
  $copy_values = array();
  $copyable = $src_prop->entityInfo();
  error_log("Info:" . print_r($copyable);
  die;
  foreach ($copyable as $pname) {
    if (isset($src_prop->{$pname})) {
      $copy_values[$pname] = $src_prop->{$pname};
    }
  }
  // add or replace new property with copy values 
}



?>