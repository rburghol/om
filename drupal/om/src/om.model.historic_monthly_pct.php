#!/user/bin/env drush
<?php
// Create historic_monthly_pct property attached to Facility:riverseg model elements in dH
module_load_include('inc', 'om', 'src/om_translate_to_dh');

$args = array();
while ($arg = drush_shift()) {
  $args[] = $arg;
}

// all batch element settings
$dest_id = FALSE; // the equation or other entity to receive the linked data 
$src_id = FALSE; // the entity that contains the property to be linked 
$prop_varkey = FALSE; // the type of 
$dest_prop = FALSE; // the name of the linked value on the dest_id object 
$src_prop = FALSE; // the property whose value is to be linked 
$prop_entity_type = 'dh_properties';


if ( (count($args) >= 4) or ($args[0] == 'file')) {
  
  $query_type = $args[0];
  $dest_id = $args[1];
  $src_id = $args[2];
  $prop_varkey = $args[3];
  $dest_prop = $args[4];
  $src_prop = $args[5];
  $src_entity_type = $args[6];
} else {
  // warn and quit
  error_log("Usage: om.model.wsp.props.php query_type=[cmd/file] dest_id src_id prop_varkey dest_prop src_prop src_entity_type");
  die;
}

error_log("query_type = $query_type, featureid = $dest_id, dest_prop = $dest_prop, varkey = $prop_varkey");


// read csv of dest_id / src_id pairs
// find model -- report error if it does not exist
// name = dest_prop
// iterate through properties

if ($query_type == 'file') {
  $filepath = $dest_id;
  $dest_id = FALSE;
  $src_id = FALSE;
  error_log("File requested: $filepath");
}

$om = 'http://deq2.bse.vt.edu/om/get_model.php';

// classes = array() empty mean all

$data = array();
if (!($dest_id and $src_id)) {
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
    'dest_id' => $dest_id, 
    'src_id' => $src_id,
    'dest_prop' => $dest_prop,
    'prop_varkey' => $prop_varkey,
    'src_prop' => $src_prop,
    'src_entity_type' => $src_entity_type,
  );
}

foreach ($data as $element) {
	// 1) load model property
	// 2) create or load om_class_Equation attached to model (i.e. wsp2020_2020_mgy)
	//   2.1) save the equation if its a create
	// 3) create or load om_map_model_linkage property attached to om_class_Equation
	// 4) update linkage attributtes 
	//   4.1) save the linkage
  $dest_id = $element['dest_id'];
  $src_id = $element['src_id'];
  $dest_prop = isset($element['dest_prop']) ? $element['dest_prop'] : FALSE; //if not set, default to FALSE
  $prop_varkey = isset($element['prop_varkey']) ? $element['prop_varkey'] : FALSE;
  $src_prop = isset($element['src_prop']) ? $element['src_prop'] : FALSE;
  $src_entity_type = isset($element['src_entity_type']) ? $element['src_entity_type'] : 'dh_adminreg_feature';

	//load model property
  $model = entity_load_single('dh_properties', $dest_id);
  
	//create or load om_class_DataMatrix -- WAS om_class_Equation
	$values = array(
      'varkey' => 'om_class_DataMatrix', 
      'propname' => $dest_prop,
      'featureid' => $model->pid,
      'propvalue' => NULL, //best practice to set them as NULL explicitly
      'propcode' => '0', 
      'entity_type' => 'dh_properties',
    );
  $equation = om_model_getSetProperty($values); //this functions defualt is to save newly created, or returns object if it exists
  
  if (!empty($src_id)){
  
  	//create or load om_map_model_linkage
	  $values = array(
        'varkey' => 'om_map_model_linkage', 
        'propname' => 'linked_property',
        'featureid' => $equation->pid,
        'propvalue' => $src_id, 
        'propcode' => $src_entity_type, 
        'entity_type' => 'dh_properties',
      );
	  $link = om_model_getSetProperty($values,'name',FALSE);
  
	  $link->src_prop = $src_prop;
	  $link->dest_prop = 'propcode';
	  $link->link_type = 2;
	  $link->update_setting = 'update';
    error_log("Linking from $src_entity_type:$src_id:$src_prop to $model->pid:$dest_prop($equation->pid):$link->dest_prop");
	  $link->save();
  }
}
?>
