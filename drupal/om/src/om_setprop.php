#!/user/bin/env drush
<?php
module_load_include('inc', 'om', 'src/om_translate_to_dh');

$args = array();
while ($arg = drush_shift()) {
  $args[] = $arg;
}

if (count($args) < 1) {
  error_log("Usage: php om_setprop.php query_type entity_type featureid varkey propname propvalue propcode ");
  die;
}
error_log("Args:" . print_r($args,1));
$query_type = $args[0];
$data = array();
if ($query_type == 'cmd') {
  if (count($args) >= 6) {
    $vars = array();
    $vars['entity_type'] = $args[1];
    $vars['featureid'] = $args[2];
    $vars['varkey']= $args[3];
    $vars['propname'] = $args[4];
    $vars['propvalue'] = $args[5];
    $vars['propcode'] = $args[6];
    $data[] = $vars;
  } else {
    error_log("Usage: php om_setprop.php query_type entity_type featureid varkey propname propvalue propcode ");
    die;
  }
} else {
  $filepath = $args[1];
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

  error_log(print_r($element,1));
  $prop = om_model_getSetProperty($element);
  if (is_object($prop)) {
    error_log("Prop $prop->propname created with pid = $prop->pid");
  } else {
    error_log("Failed to create property from " . print_r($values,1));
  }
}



?>