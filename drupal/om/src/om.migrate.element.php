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
$model_varkey = 'om_model_element';
$model_entity_type = 'dh_feature';
// command line class override
$classes = array('dataMatrix', 'Equation', 'USGSGageSubComp');
//$classes = array('Equation');

// Is single command line arg?
if (count($args) >= 2) {
  // Do command line, single element settings
  // set these if doing a single -- will fail if both not set
  // $elementid = 340385; // set these if doing a single
  // $hydrocode = 'vahydrosw_wshed_JB0_7050_0000_yarmouth';
  $elementid = $args[0];
  $hydrocode = $args[1];
  if (isset($args[2])) {
    $one_proc = $args[2];
  }
  if (isset($args[3])) {
    $bundle = $args[3];
  }
  if (isset($args[4])) {
    $ftype = $args[4];
  }
  if (isset($args[5])) {
    $model_scenario = $args[5];
  }
  if (isset($args[6])) {
    $model_varkey = $args[6];
  }
  if (isset($args[7])) {
    $classes = explode(',',$args[7]);
  }
} else {
  // warn and quit
  error_log("Usage: om.migrate.element.php elementid hydrocode [procname=''(all)] [bundle=watershed] [ftype=vahydro] [model_scenario=vahydro-1.0] [model_varkey=om_model_element] [classes=" . implode(',', $classes) . "]");
  die;
}

error_log("elementid = $elementid, hydrocode = $hydrocode, procname = $one_proc, bundle=$bundle, ftype=$ftype");


// read csv of elementid / hydrocode pairs
// find dh feature -- report error if it does not exist
// name = hydrocode + vah-1.0
// iterate through properties

if ($elementid == 'file') {
  //$filepath = '/var/www/html/files/vahydro/om_lrsegs.txt';
  //$filepath = '/var/www/html/files/vahydro/om_lrsegs-short.txt';
  // 2nd param should be hydrocode
  // To do all model containers, use:
  //   /www/files/vahydro/vahydrosw_om_wshed_elements.tsv
  //  This includes subnodes as well as model nodes for scenario 37
  $filepath = $hydrocode;
  $elementid = FALSE;
  $hydrocode = FALSE;
  error_log("File requested: $filepath");
}

$om = 'http://deq2.bse.vt.edu/om/get_model.php';

// classes = array() empty mean all

if (!($elementid and $hydrocode)) {
  $data = array();
  $file = fopen($filepath, 'r');
  $header = fgetcsv($file, 0, "\t");
  while ($line = fgetcsv($file, 0, "\t")) {
    $data[] = array_combine($header,$line);
  }
  error_log("File opened with records: " . count($data));
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
  //error_log("json:" . $json);
  $object = json_decode($json);
  // try to load the drupal object with matching hydrocode
  $om_fid = dh_search_feature($hydrocode, $bundle, $ftype);
  if (!$om_fid) {
    error_log("Could not load dh feature with bundle=$bundle, ftype = $ftype and hydrocode = $hydrocode");
    watchdog('om', "Could not load dh feature with ftype = $ftype and hydrocode = $hydrocode");
    // skip to the next one
    continue;
  }
  if (is_object($object)) {
    $om_feature = entity_load_single($model_entity_type, $om_fid);
    error_log("Found $om_feature->name ($om_feature->hydroid)");
    $om_model = FALSE;
    $values = array(
      'entity_type' => $model_entity_type,
      'featureid' => $om_fid,
      'propcode'=>$model_scenario,
      'varkey' => $model_varkey,
      'propname' => $object->name,
    );
    error_log("Searching Model " . print_r($values,1));
    $om_model = om_model_getSetProperty($values, 'propcode_singular');
    error_log("Model = $om_model->propname - $om_model->propcode ");
    // see if the
    if (is_object($om_model)) {
      error_log("Model with pid = $om_model->pid");
      // first, disable set_remote to prevent looping
      // add the element link
      $link_props = array(
        'entity_type' => 'dh_properties',
        'featureid' => $om_model->pid,
        'propname' => 'OM Element Link',
        'propvalue' => $elid,
        'varkey'=>'om_element_connection'
      );
      // retrieve or create the link
      $om_link = om_model_getSetProperty($link_props, 'varid');
      // now, we stash the link set_remote property, since it needs to be disabled here
      //   to prevent saving and then resaving on om
      error_log("om_link PID for this entity: $om_link->pid");
      $link_set_remote = $om_link->propcode;
      $om_link->propcode = '0';
      $om_link->save();
      $props = dh_get_dh_propnames('dh_properties', $om_model->pid);
      error_log("Prop names for this entity: " . print_r($props,1));
      // now add these
      $procs = $object->processors;
      $procnames = array_keys($procs);
      error_log("Processor names for om model: " . print_r($procnames,1));
      foreach ($procs as $procname => $proc) {
        // just do one
        if (($one_proc <> 'all') and ($procname <> $one_proc)) {
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
          // load or establish the property (do not save until sure if we've handled it)
          error_log("Looking for: " . print_r($proc_data,1));
          $prop = om_model_getSetProperty($proc_data, 'name');
          if (is_object($prop)) {
            error_log("Prop loaded for $prop->propname ");
          }
          $translated = om_translate_to_dh($proc, $prop);
          $prop->set_remote = FALSE;
          error_log("Translated $object_class = $translated ");
          if ($translated) {
            error_log("Saving $procname .");
            $prop->save();
          } else {
            error_log("Translation failed.");
          }
        } else {
          error_log("Skipping Classes - $object_class");
        }
      }
      $om_link->propcode = $link_set_remote;
      $om_link->save();
    }
  } else {
    error_log("Could not find: elementid=$elid ");
  }

}

?>
