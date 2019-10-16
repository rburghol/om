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
$new_matrix = array();
$new_matrix[] = array(1, 'Qbaseline_unit');
$new_matrix[] = array(2, 'Qsynth_unit');
$new_matrix[] = array(3, 'Qvahydro_unit');
$new_matrix[] = array(4, 'Qcbp6_unit');
$new_matrix[] = array(5, 'Qcbp6_unit');
$new_matrix[] = array(6, 'Qcbp6_unit');
error_log("New Matrix: " . print_r($new_matrix,1));

foreach ($data as $element) {
  $vahydro_pid = $element['vahydro_pid']; 
  if (!$vahydro_pid) {
    error_log("Missing model ID cannot process");
    error_log(print_r($element,1));
    die;
  }
  $dh_prop = om_load_dh_model('pid', $vahydro_pid);
  $plugin = dh_variables_getPlugins($dh_prop);
  $om_matrix = $plugin->tablefieldToOMMatrix($dh_prop->field_dh_matrix);
  $plugin->load($dh_prop);
  error_log("Updating: $pid : $dh_prop->propname keycol1: " . get_class($dh_prop->keycol1));
  $plugin->setCSVTableField($dh_prop, $new_matrix);
  // we save the matrix
  $dh_prop->save();
}
?>