#!/user/bin/env drush
<?php
module_load_include('inc', 'om', 'src/om_translate_to_dh');
// go thru list of Local and remote elements without CBP6 Runoffs
// om_elementid, vahydro_pid, varkey, template_id
// Create a clone of an object in OM 
// create a shell on VAHydro 
// add om_element_connection with pull_once from OM to VAHydro 

// test: cmd 210453 4696374 om_model_element 340393 
$scenarioid = 37;
$args = array();
while ($arg = drush_shift()) {
  $args[] = $arg;
}

// Is single command line arg?
if (count($args) >= 2) {
  $query_type = $args[0];
  $template_id = $args[1];
  $om_parentid = $args[2];
  $vahydro_parentid = $args[3];
  $varkey = $args[4];
  $model_name = $args[5];
  $vahydro_search_type = $args[5] ? $args[6] : 'prop_feature';
  $object_class = isset($args[7]) ? $args[7] : 'modelObject';
  $scenarioid = isset($args[8]) ? $args[8] : $scenarioid;
} else {
  print("Usage: php om_create_pair.php query_type template_id om_parentid vahydro_parentid varkey model_name vahydro_search_type [object_class=modelObject] [scenarioid=37] \n");
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
    'om_parentid' => $om_parentid, 
    'vahydro_parentid' => $vahydro_parentid,
    'varkey' => $varkey,
    'template_id' => $template_id,
    'model_name' => $model_name,
    'vahydro_search_type' => $vahydro_search_type,
    'object_class' => $object_class,
    'scenarioid' => $scenarioid,
  );
}

foreach ($data as $element) {
  $om_parentid = $element['om_parentid'];
  $vahydro_parentid = $element['vahydro_parentid'];
  $varkey = $element['varkey'];
  $model_name = $element['model_name'];
  $vahydro_search_type = $element['vahydro_search_type'];
  $template_id = $element['template_id'];
  $object_class = $element['object_class'];
  $elid = $element['om_elementid'];
  $vahydro_pid = $element['vahydro_pid'];
  if (!$template_id) {
    error_log("Missing template_id cannot process");
    error_log(print_r($element,1));
    die;
  }
  $om_parent = om_get_om_model($om_parentid);
  error_log(print_r($element,1));
  foreach($om_parent->components as $key => $prop) {
    if ($prop['elemname'] == $model_name) {
      error_log("prop:" . print_r(array_keys((array)$prop),1));
      $elid = $prop['elementid'];
    }
  }
  $vahydro_parent = om_load_dh_model($vahydro_search_type, $vahydro_parentid);
  if (!$elid) {
    // need to create in OM
    $elid = om_copy_element($scenarioid, $template_id, $om_parentid, $model_name, -1);
    error_log("om_copy_element() Returned $elid ");
  }
  if (isset($element['custom2'])) {
    om_setprop($elid, 'custom2', $element['custom2']);
  }
  // add the VAHydro model or retrieve if it does not exist
  $vahydro_child = om_load_dh_model($vahydro_search_type, $vahydro_parentid, $model_name, $varkey, $object_class);
  $link_obj = om_link2dh($elid, $vahydro_child);
  $link_obj->propcode = 'pull_once';
  error_log("Saving Link/pull_once from: $link_obj->propvalue to $link_obj->featureid");
  $link_obj->save();
}
?>