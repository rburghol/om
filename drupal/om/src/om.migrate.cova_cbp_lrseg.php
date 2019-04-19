#!/user/bin/env drush
<?php
// Migrate Land-River Segment runoff models from OM to vahydro 2.0
module_load_include('inc', 'om', 'src/om_translate_to_dh');


// read csv of elementid / hydrocode pairs
// find dh feature -- report error if it does not exist
// name = hydrocode + vah-1.0
// iterate through properties

$om = 'http://deq2.bse.vt.edu/om/get_model.php';
$filepath = '/var/www/html/files/vahydro/om_lrsegs.txt';
//$filepath = '/var/www/html/files/vahydro/om_lrsegs-short.txt';

// dH Settings
$bundle = 'landunit';
$ftype = 'cbp532_lrseg';

// single proc settings
$one_proc = '';

// all batch element settings
$elementid = FALSE;
$hydrocode = FALSE;
// single element settings
//$elementid = 256687; // set these if doing a single
//$hydrocode = 'A51121_OR2_8130_7900'; // set these if doing a single -- will fail if both not set
// classes = array() empty mean all
$classes = array('dataMatrix');

if (!($elementid and $hydrocode)) {
  $file = fopen($filepath, 'r');
  $header = fgetcsv($file, 0, "\t");
  while ($line = fgetcsv($file, 0, "\t")) {
    $data[] = array_combine($header,$line);
  }
} else {
  $data = array();
  $data[] = array('elementid' => $elementid, 'hydrocode' => $hydrocode);
}

foreach ($data as $element) {
  $elid = $element['elementid'];
  $hydrocode = $element['hydrocode'];
  $uri = $om . "?elementid=$elid";
  error_log("Opening $uri ");
  $json = file_get_contents ($uri);
  $object = json_decode($json);
  // try to load the drupal object with matching hydrocode
  $om_fid = dh_search_feature($hydrocode, $bundle, $ftype);
  if (!$om_fid) {
    error_log("Could not load dh feature with bundle=$bundle, ftype = $ftype and hydrocode = $hydrocode");
    watchdog('om', "Could not load dh feature with ftype = $ftype and hydrocode = $hydrocode");
    // skip to the next one
    continue;
  }
  $om_feature = entity_load_single('dh_feature', $om_fid);
  error_log("Found $om_feature->name ($om_feature->hydroid)");
  $om_model = FALSE;
  $values = array(
    'entity_type' => 'dh_feature', 
    'featureid' => $om_fid, 
    'propname' => $hydrocode . ' vah1.0', 
    'propcode'=>'vahydro-1.0', 
    'varkey'=>'om_model_element',
    'object_class' => $object->object_class,
  );
  $om_model = om_model_getSetProperty($values, 'propcode_singular');
  error_log("Model = " . print_r((array)$om_model,1));
  // see if the 
  if (is_object($om_model)) {
    error_log("Model with pid = $om_model->pid");
    // first, disable set_remote to prevent looping
    $om_model->set_remote = FALSE;
    // add the element link
    $om_link = array(
      'entity_type' => 'dh_properties', 
      'featureid' => $om_model->pid, 
      'propname' => 'OM Element Link', 
      'propvalue' => $elid, 
      'varkey'=>'om_element_connection'
    );
    om_model_getSetProperty($om_link, 'singular');
    $props = dh_get_dh_propnames('dh_properties', $om_model->pid);
    error_log("Prop names for this entity: " . print_r($props,1));
    // now add these
    $procs = $object->processors;
    foreach ($procs as $procname => $proc) {
      // just do one
      if (($one_proc <> '') and ($procname <> $one_proc)) {
        continue;
      }
      $object_class = $proc->object_class;
      error_log("Found $procname : $object_class");
      if (empty($classes) or in_array($object_class, $classes)) {
        $proc_data = array(
          'propname' => $procname,
          'entity_type' => 'dh_properties',
          'featureid' => $om_model->pid,
          'varkey' => om_get_dh_varkey($proc),
        );
        // establish the property
        $prop = om_model_getSetProperty($proc_data, 'name');
        dh_variables_getPlugins($prop);
        om_translate_to_dh($proc, $prop);
        $prop->set_remote = FALSE;
        //error_log("Translated: " . print_r($prop,1));
        $prop->save();
      } else {
        error_log("Skipping Classes - $object_class");
      }
    }
  }
  
}

?>