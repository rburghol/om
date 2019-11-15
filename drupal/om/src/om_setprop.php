#!/user/bin/env drush
<?php
module_load_include('inc', 'om', 'src/om_translate_to_dh');

$args = array();
while ($arg = drush_shift()) {
  $args[] = $arg;
}

// Is single command line arg?
$vars = array();


if (count($args) < 1) {
  error_log("Usage: php om_setprop.php query_type entity_type featureid pid propname propvalue propcode ");
  error_log("Note: 'all' is not yet enabled");
  die;
}
$query_type = $args[0];
$data = array();
if ($query_type == 'cmd') {
  if (count($args) >= 6) {
    $vars = array();
    $vars['entity_type'] = $args[1];
    $vars['featureid'] = $args[2];
    $vars['pid']= $args[3];
    $vars['propname'] = $args[4];
    $vars['propvalue'] = $args[5];
    $vars['propcode'] = $args[6];
    $data[] = $vars;
  } else {
    error_log("Usage: php om_setprop.php query_type entity_type featureid pid propname propvalue propcode ");
    error_log("Note: 'all' is not yet enabled");
    die;
  }
} else {
  $filepath = $dest_id;
  $dest_id = FALSE;
  $src_id = FALSE;
  error_log("File requested: $filepath");
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
}



foreach ($data as $element) {



}



?>