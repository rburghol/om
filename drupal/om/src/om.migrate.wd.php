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

// Is single command line arg?
if (count($args) >= 4) {
  // Do command line, single element settings
  // set these if doing a single -- will fail if both not set
  // $featureid = 340385; // set these if doing a single
  // $coverage_hydrocode = 'vahydrosw_wshed_JB0_7050_0000_yarmouth';
  $query_type = $args[0];
  $featureid = $args[1];
  $coverage_hydrocode = $args[2];
  $coverage_name = $args[3];
  if (isset($args[4])) {
    $varkey = $args[4];
  }
  if (isset($args[5])) {
    $propvalue = $args[5];
  }
} else {
  // warn and quit
  error_log("Usage: om.migrate.wd.php query_type=[feature/file] featureid coverage_hydrocode coverage_name [varkey=''(all)] [propvalue=] ");
  die;
}

error_log("query_type = $query_type, featureid = $featureid, coverage_hydrocode = $coverage_hydrocode, varkey = $varkey, propvalue=$propvalue");


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
  $data[] = array('featureid' => $featureid, 'coverage_hydrocode' => $coverage_hydrocode);
}

foreach ($data as $element) {
  $featureid = $element['featureid'];
  $coverage_hydrocode = $element['coverage_hydrocode'];
  $coverage_name = $element['coverage_name'];
  $varkey = isset($element['varkey']) ? $element['varkey'] : FALSE;
  $propvalue = isset($element['propvalue']) ? $element['propvalue'] : FALSE;
  // add a new model if one does not exist - propname match 
  // add a riverseg prop to model 
  // If requested, add another equation prop 
  $values = array(
    'varkey' => 'om_water_system_element', 
    'propname' => $name . ':' . $coverage_name,
    'propvalue' => NULL,
    'propcode' => 'vahydro-1.0', 
    'entity_type' => 'dh_feature',
  );
  $dh_model = om_model_getSetProperty($values, 'name', FALSE);
  $dh_model->riverseg = $coverage_hydrocode;
  $dh_model->save();
}

?>
