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
$feature_hydroid = FALSE;
$coverage_hydrocode = FALSE;
$model_scenario = 'vahydro-1.0';
$model_prop_varkey = 'varcode';
$model_entity_type = 'dh_feature';
$prop_varkey = FALSE;
$propname = FALSE;
$propvalue = FALSE;

// Is single command line arg?
if ( (count($args) >= 4) or ($args[0] == 'file')) {
  // Do command line, single element settings
  // set these if doing a single -- will fail if both not set
  // $feature_hydroid = 340385; // set these if doing a single
  // $coverage_hydrocode = 'vahydrosw_wshed_JB0_7050_0000_yarmouth';
  $query_type = $args[0];
  $feature_hydroid = $args[1];
  $feature_name = $args[2];
  $coverage_hydrocode = $args[3];
  $coverage_name = $args[4];
  if (isset($args[5])) {
    $propname = $args[5];
  }
  if (isset($args[6])) {
    $prop_varkey = $args[6];
  }
  if (isset($args[7])) {
    $propvalue = $args[7];
  }
} else {
  // warn and quit
  error_log("Usage: om.migrate.wd.php query_type=[cmd/file] featureid feature_name coverage_hydrocode coverage_name [propname=prop_varkey] [prop_varkey='om_class_Equation'(all)] [propvalue=]");
  die;
}

error_log("query_type = $query_type, featureid = $feature_hydroid, feature_name = $feature_name, coverage_hydrocode = $coverage_hydrocode, prop_varkey = $prop_varkey, propvalue=$propvalue");


// read csv of featureid / coverage_hydrocode pairs
// find dh feature -- report error if it does not exist
// name = coverage_hydrocode + vah-1.0
// iterate through properties

if ($query_type == 'file') {
  $filepath = $feature_hydroid;
  $feature_hydroid = FALSE;
  $coverage_hydrocode = FALSE;
  error_log("File requested: $filepath");
}

$om = 'http://deq2.bse.vt.edu/om/get_model.php';

// classes = array() empty mean all

if (!($feature_hydroid and $coverage_hydrocode)) {
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
    'feature_hydroid' => $feature_hydroid, 
    'coverage_hydroid' => $coverage_hydroid,
    'coverage_hydrocode' => $coverage_hydrocode,
    'feature_name' => $feature_name,
    'feature_hydrocode' => $feature_hydrocode,
    'coverage_name' => $coverage_name,
    'propname' => $propname,
    'prop_varkey' => $prop_varkey,
    'propvalue' => $propvalue,
  );
}

foreach ($data as $element) {
  $feature_hydroid = $element['feature_hydroid'];
  $riverseg = substr($element['coverage_hydrocode'], -13);
  $coverage_name = $element['coverage_name'];
  $feature_name = $element['feature_name'];
  $prop_varkey = isset($element['prop_varkey']) ? $element['prop_varkey'] : FALSE;
  $propvalue = isset($element['propvalue']) ? $element['propvalue'] : FALSE;
  $propname = isset($element['propname']) ? $element['propname'] : FALSE;
  // add a default var class for files that come in without one.
  if (!$prop_varkey and $propname) {
    $prop_varkey = 'om_class_Equation';
  }
  // add a new model if one does not exist - propname match 
  // add a riverseg prop to model 
  // If requested, add another equation prop 
  $values = array(
    'varkey' => 'om_water_system_element', 
    'propname' => $feature_name . ':' . $coverage_name,
    'featureid' => $feature_hydroid,
    'propvalue' => NULL,
    'propcode' => 'vahydro-1.0', 
    'entity_type' => 'dh_feature',
  );
  error_log("Values: " . print_r($values,1));
  
  $dh_model = om_model_getSetProperty($values, 'name', FALSE);
  $dh_model->riverseg = $riverseg;
  $dh_model->save();
  
  if ($prop_varkey) {
    $values = array(
      'varkey' => $prop_varkey, 
      'propname' => $propname,
      'featureid' => $dh_model->pid,
      'entity_type' => 'dh_properties',
    );
    error_log("Adding $propname $prop_varkey" . print_r($values,1));
    $model_prop = om_model_getSetProperty($values, 'name', FALSE);
    $plugin = array_shift($model_prop->dh_variables_plugins);
    if (method_exists($plugin, 'applyEntityAttribute')) {
      $plugin->applyEntityAttribute($model_prop, $propvalue);
    } else {
      $model_prop->propvalue = $propvalue;
    }
    $model_prop->save();
  }
}

?>
