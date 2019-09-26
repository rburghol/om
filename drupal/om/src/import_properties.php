#!/user/bin/env drush
<?php
// Migrate Land-River Segment runoff models from OM to vahydro 2.0
module_load_include('inc', 'om', 'src/om_translate_to_dh');

$args = array();
while ($arg = drush_shift()) {
  $args[] = $arg;
}

// Defaults
// dH Settings
$bundle = 'watershed';
$ftype = 'vahydro';
// single proc settings
$one_proc = 'all';
// all batch element settings
$elementid = FALSE;
$hydrocode = FALSE;
$model_scenario = 'vahydro-1.0';
$model_varkey = 'varcode';
$model_entity_type = 'dh_feature';
// command line class override
$classes = array('dataMatrix', 'Equation', 'USGSGageSubComp', 'textField');
//$classes = array('Equation');

// Is single command line arg?
if (count($args) >= 2) {
  // Do command line, single element settings
  // set these if doing a single -- will fail if both not set
  // $elementid = 340385; // set these if doing a single
  // $hydrocode = 'vahydrosw_wshed_JB0_7050_0000_yarmouth';
  $query_type = $args[0];
  $entity_type = $args[1];
  if (isset($args[2])) {
    $featureid = $args[2];
  }
  if (isset($args[3])) {
    $varkey = $args[3];
  }
  if (isset($args[4])) {
    $propname = $args[4];
  } 
  if (isset($args[5])) {
    $propvalue = $args[5];
  }
  if (isset($args[6])) {
    $propcode = $args[6];
  }

} else {
  // warn and quit
  error_log("Usage: import_properties.php query_type=[cmd/file] entity_type/filepath [featureid] [varkey] [propname] [propvalue] [propcode]");
  die;
}

error_log("query_type = $query_type, entity_type = $entity_type, featureid = $featureid, varkey=$varkey, propname=$propname, propvalue=$propvalue, propcode=$propcode");


// read csv of elementid / hydrocode pairs
// find dh feature -- report error if it does not exist
// name = hydrocode + vah-1.0
// iterate through properties

if ($query_type == 'file') {
  //$filepath = '/var/www/html/files/vahydro/om_lrsegs.txt';
  //$filepath = '/var/www/html/files/vahydro/om_lrsegs-short.txt';
  // 2nd param should be hydrocode
  // To do all model containers, use:
  //   /www/files/vahydro/vahydrosw_om_wshed_elements.tsv
  //  This includes subnodes as well as model nodes for scenario 37
  $filepath = $entity_type;
  $featureid = FALSE;
  error_log("File requested: $filepath");

  $data = array();
  $file = fopen($filepath, 'r');
  $header = fgetcsv($file, 0, "\t");
  while ($line = fgetcsv($file, 0, "\t")) {
    $data[] = array_combine($header,$line);
  }
  error_log("File opened with records: " . count($data));
} else {
  $data = array();
  $data[] = array('query_type' => $query_type, 'entity_type' =>$entity_type, 
  'featureid' =>$featureid, 'varkey'=>$varkey, 'propname'=>$propname, 
  'propvalue'=>$propvalue, 'propcode'=>$propcode);
  
}

foreach ($data as $element) {
  $values = array(
      'entity_type' => $element['entity_type'],
	  'featureid' => $element['featureid'],
      'varkey' => $element['varkey'], 
      'propname' => $element['propname'],    
      'propvalue' => $element['propvalue'],
      'propcode' => $element['propcode'] 
    );
  $dh_property = om_model_getSetProperty($values, 'name', FALSE);
  error_log("saving $dh_property->propname, $dh_property->propvalue");
  $dh_property->save();
}

?>
