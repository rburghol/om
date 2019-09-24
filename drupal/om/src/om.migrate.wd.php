#!/user/bin/env drush
<?php
// Create Facility:riverseg model element in dH
// or create MP:riverseg model element in dH
module_load_include('inc', 'om', 'src/om_translate_to_dh');

$args = array();
while ($arg = drush_shift()) {
  $args[] = $arg;
}
// input file cmd:
// mp_hydroid, mp_name, riverseg, model_scenario 

// all batch element settings
$featureid = FALSE;
$coverage_hydrocode = FALSE;
$model_scenario = 'vahydro-1.0';
$model_varkey = 'varcode';
$model_entity_type = 'dh_feature';
$varkey = FALSE;
$propvalue = FALSE;

// Is single command line arg?
if (count($args) >= 4) {
  // Do command line, single element settings
  // set these if doing a single -- will fail if both not set
  // $featureid = 340385; // set these if doing a single
  // $coverage_hydrocode = 'vahydrosw_wshed_JB0_7050_0000_yarmouth';
  $query_type = $args[0];
  $featureid = $args[1];
  $feature_name = $args[2];
  $coverage_hydrocode = $args[3];
  $coverage_name = $args[4];
  if (isset($args[5])) {
    $varkey = $args[5];
  }
  if (isset($args[6])) {
    $propvalue = $args[6];
  }
} else {
  // warn and quit
  error_log("Usage: om.migrate.wd.php query_type=[cmd/file] featureid feature_name coverage_hydrocode coverage_name [varkey=''(all)] [propvalue=] ");
  die;
}

error_log("query_type = $query_type, featureid = $featureid, feature_name = $feature_name, coverage_hydrocode = $coverage_hydrocode, varkey = $varkey, propvalue=$propvalue");


// read csv of featureid / coverage_hydrocode pairs
// find dh feature -- report error if it does not exist
// name = coverage_hydrocode + vah-1.0
// iterate through properties

if ($query_type == 'file') {
  $filepath = $featureid;
  $featureid = FALSE;
  $coverage_hydrocode = FALSE;
  error_log("File requested: $filepath");
}

$om = 'http://deq2.bse.vt.edu/om/get_model.php';

// classes = array() empty mean all

if (!($featureid and $coverage_hydrocode)) {
  $data = array();
  $file = fopen($filepath, 'r');
  $header = fgetcsv($file, 0, "\t");
  while ($line = fgetcsv($file, 0, "\t")) {
    $data[] = array_combine($header,$line);
  }
  error_log("File opened with records: " . count($data));
} else {
  $data = array();
  $data[] = array(
    'featureid' => $featureid, 
    'coverage_hydrocode' => $coverage_hydrocode,
    'featureid' => $featureid, 
    'feature_name' => $feature_name,
    'coverage_hydrocode' => $coverage_hydrocode,
    'coverage_name' => $coverage_name,
    'varkey' => $varkey,
    'propvalue' => $propvalue,
  );
}

foreach ($data as $element) {
  $featureid = $element['featureid'];
  $riverseg = substr($element['coverage_hydrocode'], -13);
  $coverage_name = $element['coverage_name'];
  $feature_name = $element['feature_name'];
  $varkey = isset($element['varkey']) ? $element['varkey'] : FALSE;
  $propvalue = isset($element['propvalue']) ? $element['propvalue'] : FALSE;
  // add a new model if one does not exist - propname match 
  // add a riverseg prop to model 
  // If requested, add another equation prop 
  $values = array(
    'varkey' => 'om_water_system_element', 
    'propname' => $feature_name . ':' . $coverage_name,
    'propvalue' => NULL,
    'propcode' => 'vahydro-1.0', 
    'entity_type' => 'dh_feature',
  );
  error_log("Values: " . print_r($values,1));
  
  $dh_model = om_model_getSetProperty($values, 'name', FALSE);
  $dh_model->riverseg = $riverseg;
  $dh_model->save();
  
  if ($varkey) {
    
  }
}

?>
