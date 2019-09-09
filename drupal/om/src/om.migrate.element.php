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
  $elementid = $args[1];
  $hydrocode = $args[2];
  if (isset($args[3])) {
    $one_proc = $args[3];
  }
  if (isset($args[4])) {
    $bundle = $args[4];
  }
  if (isset($args[5])) {
    $ftype = $args[5];
  }
  if (isset($args[6])) {
    $model_scenario = $args[6];
  }
  if (isset($args[7])) {
    $model_varkey = $args[7];
  }
  if (isset($args[8])) {
    $classes = explode(',',$args[8]);
  }
} else {
  // warn and quit
  error_log("Usage: om.migrate.element.php query_type=[feature]|pid,prop_feature elementid hydrocode [procname=''(all)] [bundle=watershed] [ftype=vahydro] [model_scenario=vahydro-1.0] [model_varkey=varcode (queries for varcode matching OM class)] [classes=" . implode(',', $classes) . "]");
  error_log("If query_type = feature and hydrocode is integer, will assume a hydroid of the parent of the model element has been submitted ");
  error_log("If query_type = pid and hydrocode is integer, will assume a pid for the model element has been submitted ");
  error_log("If query_type = prop_feature and hydrocode is integer, will assume a pid for the model element that is the parent of the model element has been submitted");
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
  // data could have elementid on one side, pid on another. or parent_pid to migrate an element to a specific
  // parent model
  // all model elements should have propcode = vahydro-1.0 or some other relevant model scenario indicator
  // propname, propcode, and varkey/varid can all be used for matching elements 
  // similarly, 
  // could pass in parent_elid and custom1 or name of child in order to link parents and children.
  // if (isset($element['parent_elid']) and isset($element['custom1']) ) {
    // $children = getNestedContainersCriteria ($listobject, $elementid, $types, $custom1, $custom2, $ignore);
    // $child = array_shift($children);
    // $elid = $child['elementid'];
  //}
  $elid = $element['elementid'];
  $hydrocode = $element['hydrocode'];
  // if hydrocode is numeric, we are passing a pid for the target model element in
  if (!isset($element['om_fid']) and is_numeric($hydrocode)) {
    $element['om_fid'] = $hydrocode;
  }
  $uri = $om . "?elementid=$elid";
  $model_entity_type = isset($element['model_entity_type']) ? $element['model_entity_type'] : $model_entity_type;
  error_log("Opening $uri ");
  $json = file_get_contents ($uri);
  //error_log("json:" . $json);
  $object = json_decode($json);
  // Check to see if we have passed in a drupal prop featureid as om_fid
  // otherwise, try to load the drupal object with matching hydrocode
  $om_fid = isset($element['om_fid']) ? $element['om_fid'] : dh_search_feature($hydrocode, $bundle, $ftype);
  if (!$om_fid) {
    error_log("Could not load dh feature with bundle=$bundle, ftype = $ftype and hydrocode = $hydrocode");
    watchdog('om', "Could not load dh feature with ftype = $ftype and hydrocode = $hydrocode");
    // skip to the next one
    continue;
  }
  if (is_object($object)) {
    // check the model_varkey 
    // - varcode = search the database for a variable whose varcode matches the OM objectclass of this object 
    // - all others expect the varkey to use 
    if ($model_varkey == 'varcode') {
      // 
      $model_varkey = dh_varcode2varid($object->object_class, TRUE);
      $model_varkey = !$model_varkey ? 'om_model_element' : $model_varkey;
      error_log("Object class: " . $object->object_class . " Using variable key from Varcode query: $model_varkey ");
    }
    $om_model = FALSE;
    switch($query_type) {
      case 'pid':
      break;
      
      case 'prop_feature':
      $search_mode = 'name';
      $model_entity_type = 'dh_properties';
      error_log("Using query_mode PROP_FEATURE to load model element");
      break;
      
      case 'feature':
      default:
      $search_mode = 'propcode_singular';
      error_log("Using query_mode FEATURE to load model element");
      $om_feature = entity_load_single($model_entity_type, $om_fid);
      error_log("Found $om_feature->name ($om_feature->hydroid)");
      break;
    }
    if ($query_type == 'pid') {
      // this is a reference to a direct model pid, no need to query
      $om_model = entity_load_single('dh_properties', $om_fid);
      error_log("Using query_mode PID to load model element directly.");
    } else {
      error_log("Searching Model " . print_r($values,1));
      $values = array(
        'entity_type' => $model_entity_type,
        'featureid' => $om_fid,
        'propcode'=>$model_scenario,
        'varkey' => $model_varkey,
        'propname' => $object->name,
      );
      // If the model prop does not exist yet, this will create AND save it.
      $om_model = om_model_getSetProperty($values, $search_mode);
    }
    error_log("Searched mode $search_mode, found Model = $om_model->propname - $om_model->propcode ");
    // see if the
    if (is_object($om_model)) {
      // we now have a saved dH object, with defaults if specified by the class plugin.
      // Now we:
      // 1. disable the element link back save so we can handle everything first.
      // 2. update all properties with their OM object values 
      // 3. Save the dH model element afterwards
      // set the object class value ??
      // Currently this is not used.  The object_class is a function of the plugin, which is set by the 
      // varkey.  We need to either have a lookup or 
      //$om_model->object_class;
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
      // now add these components.
      $procs = $object->processors;
      $procnames = array_keys($procs);
      error_log(count($object->processors) . " Processor names for om model: " . print_r($procnames,1));
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
      // Now, save the model element
      // wait: does this already get done when we save the remote link?
      //$om_model->set_remote = 0;
      // handle object class settings if specified 
      om_translate_to_dh($object, $om_model);
      error_log("A: $om_model->area, DA: $om_model->drainage_area ");
      $om_model->save();
      // finally, restore the link setting to enable saves from dH to OM if requested.
      $om_link->propcode = $link_set_remote;
      $om_link->save();
    }
  } else {
    error_log("Could not find: elementid=$elid ");
  }

}

?>
