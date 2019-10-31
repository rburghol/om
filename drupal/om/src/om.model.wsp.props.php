#!/user/bin/env drush
<?php
// Create WSP properties attached to Facility:riverseg model elements in dH
module_load_include('inc', 'om', 'src/om_translate_to_dh');

$args = array();
while ($arg = drush_shift()) {
  $args[] = $arg;
}
// input file cmd:
// model_pid, system_adminid, prop_varkey, propname
// example: 4726070, 178413, om_class_Equation, wsp2020_2020_mgy
// drush scr modules/om/src/om.model.wsp.props.php cmd 4726070 178413 om_class_Equation wsp2020_2020_mgy

// all batch element settings
$model_pid = FALSE;
$system_adminid = FALSE;
$prop_entity_type = 'dh_properties';
$prop_varkey = FALSE;
$propname = FALSE;

// Is single command line arg?
if ( (count($args) >= 4) or ($args[0] == 'file')) {
  // Do command line, single element settings
  // set these if doing a single -- will fail if both not set
  // $model_pid = 4726070; // set these if doing a single (model: VIRGINIA BEACH SERVICE AREA:North Landing River)
  // $system_adminid = 178413; // (system: Virginia Beach, City of)

  $query_type = $args[0];
  $model_pid = $args[1];
  $system_adminid = $args[2];
  $prop_varkey = $args[3];
  $propname = $args[4];
} else {
  // warn and quit
  error_log("Usage: om.model.wsp.props.php query_type=[cmd/file] model_pid system_adminid prop_varkey propname");
  die;
}

error_log("query_type = $query_type, featureid = $model_pid, propname = $propname, varkey = $prop_varkey");


// read csv of model_pid / system_adminid pairs
// find model -- report error if it does not exist
// name = propname
// iterate through properties

if ($query_type == 'file') {
  $filepath = $model_pid;
  $model_pid = FALSE;
  $system_adminid = FALSE;
  error_log("File requested: $filepath");
}

$om = 'http://deq2.bse.vt.edu/om/get_model.php';

// classes = array() empty mean all

if (!($model_pid and $system_adminid)) {
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
    'model_pid' => $model_pid, 
    'system_adminid' => $system_adminid,
    'propname' => $propname,
    'prop_varkey' => $prop_varkey,
  );
}

foreach ($data as $element) {
  $model_pid = $element['model_pid'];
  $linkage = str_replace('vahydrosw_wshed_', '', $element['coverage_hydrocode']);
  $coverage_name = $element['coverage_name'];
  $feature_name = $element['feature_name'];
  $prop_varkey = isset($element['prop_varkey']) ? $element['prop_varkey'] : FALSE;
  $propvalue = isset($element['propvalue']) ? $element['propvalue'] : FALSE;
  $propname = isset($element['propname']) ? $element['propname'] : FALSE;
  // add a default var class for files that come in without one.
  
  // Now, search for this linkage prop
  $dh_wsp_prop_pid = FALSE;
  $linkage_pid = om_get_search_model_subprops('dh_properties', $model_pid, 'link_wsp_wd_current_mgy');
  if ($linkage_pid) {
    //error_log("Found $linkage_pid");
    $linkage_prop = entity_load_single('dh_properties', $linkage_pid);
    $dh_wsp_prop_pid = $linkage_prop->featureid;
    //error_log("Found Matching model: $dh_wsp_prop_pid");
  }
  if (!$dh_wsp_prop_pid) {
    // add a new wsp property if one does not exist - propname match 
    // add an om_map_model_linkage to wsp property 
    // If requested, add another equation prop
    $values = array(
      'varkey' => 'om_class_Equation', 
      'propname' => $propname,
      'featureid' => $model_pid,
      'propvalue' => NULL,
      'propcode' => NULL, 
      'entity_type' => 'dh_properties',
    );
    error_log("Adding: " . $propname . " to " . $model_pid);
    if ($debug) error_log("Values: " . print_r($values,1));
    $dh_wsp_prop = om_model_getSetProperty($values, 'name', FALSE);
    $dh_wsp_prop->save();
    $dh_wsp_prop->linkage = $linkage;
    // now add the linkage prop
  } else {
    error_log("Updating: $dh_wsp_prop_pid " . $propname . " to " . $model_pid);
    $dh_wsp_prop = entity_load_single('dh_properties', $dh_wsp_prop_pid);
    $dh_wsp_prop->propname = $propname;
  }
  $dh_wsp_prop->save();

  if (!$prop_varkey and $propname) {
    $prop_varkey = 'om_map_model_linkage';
  }
  if ($prop_varkey) {
    $values = array(
      'varkey' => $prop_varkey, 
      'propname' => $propname,
      'featureid' => $dh_wsp_prop->pid,
      'entity_type' => 'dh_properties',
    );
    if ($debug) error_log("Adding $propname $prop_varkey - $propvalue " . print_r($values,1));
    $model_prop = om_model_getSetProperty($values, 'name', FALSE);
    $plugin = array_shift($model_prop->dh_variables_plugins);
    if (method_exists($plugin, 'applyEntityAttribute')) {
      $plugin->applyEntityAttribute($model_prop, $propvalue);
    } else {
      $model_prop->propvalue = $propvalue;
    }
    $model_prop->save();
    error_log("Set: " . $propname . ' = ' . $propvalue);
  }
}

?>
