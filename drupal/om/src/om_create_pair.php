#!/user/bin/env drush
<?php
module_load_include('inc', 'om', 'src/om_translate_to_dh');
// go thru list of Local and remote elements without CBP6 Runoffs
// om_elementid, vahydro_pid, varkey, template_id
// Create a clone of an object in OM 
// create a shell on VAHydro 
// add om_element_connection with pull_once from OM to VAHydro 

// test: cmd 210453 4696374 om_model_element 340393 
$scenarioid = 37;
$args = array();
while ($arg = drush_shift()) {
  $args[] = $arg;
}

// Is single command line arg?
if (count($args) >= 2) {
  $query_type = $args[0];
  $template_id = $args[1];
  $om_parentid = $args[2];
  $vahydro_parentid = $args[3];
  $varkey = $args[4];
  $template_id = $args[5];
  $scenarioid = $scenarioid[6];
} else {
  print("Usage: php om_create_pair.php query_type om_parentid vahydro_parentid varkey template_id scenarioid [37] \n");
  die;
}

if ($query_type == 'file') {
  $filepath = $om_parentid;
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
    'om_parentid' => $feature_hydroid, 
    'vahydro_parentid' => $coverage_hydroid,
    'varkey' => $varkey,
    'template_id' => $template_id,
    'scenarioid' => $scenarioid,
  );
}

foreach ($data as $element) {
  $om_elementid = $element['om_elementid'];
  $vahydro_parentid = $element['vahydro_parentid'];
  $varkey = $element['varkey'];
  $template_id = $element['template_id'];
  if (!$template_id) {
    error_log("Missing template_id cannot process");
    error_log(print_r($element,1));
    die;
  }
  $om_parent = om_get_om_model($om_parentid);
  $vahydro_parent = om_load_dh_model('pid', $vahydro_parentid);
  om_copy_element($scenarioid, $template_id, $parentid);
  
}



?>