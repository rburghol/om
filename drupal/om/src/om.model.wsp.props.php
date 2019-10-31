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
$src_name = FALSE;

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
  $src_name = $args[5];
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

$data = array();
if (!($model_pid and $system_adminid)) {
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
  $data[] = array(
    'model_pid' => $model_pid, 
    'system_adminid' => $system_adminid,
    'propname' => $propname,
    'prop_varkey' => $prop_varkey,
  );
}

foreach ($data as $element) {
	// 1) load model property
	// 2) create or load om_class_Equation attached to model (i.e. wsp2020_2020_mgy)
	//   2.1) save the equation if its a create
	// 3) create or load om_map_model_linkage property attached to om_class_Equation
	// 4) update linkage attributtes 
	//   4.1) save the linkage
  $model_pid = $element['model_pid'];
  $system_adminid = $element['system_adminid'];
  $propname = isset($element['propname']) ? $element['propname'] : FALSE; //if not set, default to FALSE
  $prop_varkey = isset($element['prop_varkey']) ? $element['prop_varkey'] : FALSE;

	//load model property
  $model = entity_load_single('dh_properties', $model_pid);
  
	//create or load om_class_Equation
	$values = array(
      'varkey' => 'om_class_Equation', 
      'propname' => $propname,
      'featureid' => $model->pid,
      'propvalue' => NULL, //best practice to set them as NULL explicitly
      'propcode' => NULL, 
      'entity_type' => 'dh_properties',
    );
  $equation = om_model_getSetProperty($values); //this functions defualt is to save newly created, or returns object if it exists
  
  	//create or load om_map_model_linkage
	$values = array(
      'varkey' => 'om_map_model_linkage', 
      'propname' => 'linked_property',
      'featureid' => $equation->pid,
      'propvalue' => $system_adminid, 
      'propcode' => 'dh_adminreg_feature', 
      'entity_type' => 'dh_properties',
    );
  $link = om_model_getSetProperty($values,'name',FALSE);
  
  $link->src_prop = $src_name;
  $link->dest_prop = 'propcode';
  $link->link_type = 2;
  $link->update_setting = 'update';
  $link->save();
}
?>
