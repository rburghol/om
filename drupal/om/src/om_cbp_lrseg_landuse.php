#!/user/bin/env drush
<?php
module_load_include('inc', 'om', 'src/om_translate_to_dh');
// go thru list of Local and remote elements without CBP6 Runoffs
// om_elementid, vahydro_pid, varkey, template_id
// Create a clone of an object in OM 
// create a shell on VAHydro 
// add om_element_connection with pull_once from OM to VAHydro 

// test: cmd 210453 4696374 om_model_element 340393 
$scenario = 'CFBASE30Y20180615';
$basepath = '/media/NAS/omdata/p6/out/land/';
$args = array();
while ($arg = drush_shift()) {
  $args[] = $arg;
}

// Is single command line arg?
if (count($args) >= 2) {
  $query_type = $args[0];
  $model_name = $args[1];
  $vahydro_pid = $args[2];
} else {
  print("Usage: php om_create_pair.php query_type model_name/file vahydro_pid \n");
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
    'model_name' => $model_name,
    'vahydro_pid' => $vahydro_pid,
  );
}

foreach ($data as $element) {
  $model_name = $element['model_name'];
  $vahydro_pid = $element['vahydro_pid']; 
  $landseg = substr($model_name, 1, 6);
  $riverseg = substr($model_name, 8, 13);
  if (!$vahydro_pid) {
    error_log("Missing model ID cannot process");
    error_log(print_r($element,1));
    die;
  }
  $vahydro_model = om_load_dh_model('pid', $vahydro_pid, $model_name);
  $vahydro_lu = om_load_dh_model('prop_feature', $vahydro_pid, 'landuse');
  
  $vahydro_lu->rowkey = '';
  $vahydro_lu->colkey = 'luyear';
  $vahydro_lu->scenario = $scenario;
  $vahydro_lu->landseg = $landseg;
  $vahydro_lu->riverseg = $riverseg;
  $vahydro_lu->filepath = implode('/', array($basepath, $scenario, 'eos', $landseg, '_0111-0211-0411.csv'));
  // e.g.: /media/NAS/omdata/p6/out/land/CFBASE30Y20180615/eos/N51121_0111-0211-0411.csv
  $plugin = dh_variables_getPlugins($vahydro_lu);
  $csv = file_get_contents($vahydro_lu->filepath);
  if (is_object($plugin )) {
    if (method_exists($plugin, 'setCSVTableField')) {
      $plugin->setCSVTableField($vahydro_lu, $csv);
    }
  }
  $vahydro_lu->save();
}
?>