#!/user/bin/env drush
<?php
module_load_include('inc', 'om', 'src/om_translate_to_dh');
// go thru list of Local and remote elements without CBP6 Runoffs
// om_elementid, vahydro_pid, varkey, template_id
// Create a clone of an object in OM 
// create a shell on VAHydro 
// add om_element_connection with pull_once from OM to VAHydro 

// test: cmd 210453 4696374 om_model_element 340393 
// drush scr modules/om/src/om_edit_matrix_runit.php cmd 4696566
$scenario = 'CFBASE30Y20180615';
$basepath = '/media/NAS/omdata/p6/out/land';
$version = 'p6';
$args = array();
while ($arg = drush_shift()) {
  $args[] = $arg;
}

// Is single command line arg?
if (count($args) >= 2) {
  $query_type = $args[0];
  $vahydro_pid = $args[1];
} else {
  print("Usage: php om_edit_matrix_runit.php query_type vahydro_pid \n");
  die;
}

if ($query_type == 'file') {
  $filepath = $model_name;
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
    'vahydro_pid' => $vahydro_pid,
  );
}

foreach ($data as $element) {
  $vahydro_pid = $element['vahydro_pid']; 
  if (!$vahydro_pid) {
    error_log("Missing model ID cannot process");
    error_log(print_r($element,1));
    die;
  }
  $dh_prop = om_load_dh_model('pid', $vahydro_pid);
  $plugin = dh_variables_getPlugins($dh_prop);
  error_log("Props and Fields: " . print_r(array_keys((array)$dh_prop),1));
  $om_matrix = $plugin->tablefieldToOMMatrix($dh_prop->field_dh_matrix);
  error_log("Matrix: " . print_r($om_matrix,1));
  /*
  $csv = om_readDelimitedFile($lu_filepath);
  error_log("Opening " . $lu_filepath);
  if (is_object($plugin )) {
    error_log("Checking plugin " . get_class($plugin));
    if (method_exists($plugin, 'setCSVTableField')) {
      //error_log("Setting csv" . print_r($csv,1));
      $plugin->setCSVTableField($dh_prop, $csv);
    }
  }
  // we save the parent model element, which saves all attached properties, except the landuse matrix
  $dh_prop->save();
  */
}
?>