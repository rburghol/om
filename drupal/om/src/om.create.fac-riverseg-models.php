#!/user/bin/env drush
<?php
// Migrate Land-River Segment runoff models from OM to vahydro 2.0
module_load_include('inc', 'om', 'src/om_translate_to_dh');

$args = array();
while ($arg = drush_shift()) {
  $args[] = $arg;
}

// Is single command line arg?
if (count($args) >= 1) {
  // Do command line, single element settings
  // set these if doing a single -- will fail if both not set
  // $elementid = 340385; // set these if doing a single
  // $hydrocode = 'vahydrosw_wshed_JB0_7050_0000_yarmouth';
  $file = $args[0];
} else {
  // warn and quit
  error_log("Usage: om.create.fac-riverseg-models.php filename");
  die;
}

error_log("filename = $file");


$data = array();
$file = fopen($filepath, 'r');
$header = fgetcsv($file, 0, "\t");
while ($line = fgetcsv($file, 0, "\t")) {
  $data[] = array_combine($header,$line);
}
error_log("File opened with records: " . count($data));

// Use drush tools to create models to attach to facilities
// after adding the model, set the riverseg property (TextField constant)
// this can be used to check for existing objects, to update, instead of add 
// find the feature, using hydroid
// set the vahydro_pid property on the OM element after saving/updating
foreach ($data as $element) {
  $hydroid = $element['facility_hydroid'];
  $coverage_hydrocode = $element['coverage_hydrocode'];
  $riverseg = substr($coverage_hydrocode, 17);
  $facility = entity_load_single('dh_feature', $hydroid);
  $model_info = array(
    'varkey' => 'om_model_element',
    'featureid' => $facility->hydroid,
    'entity_type' => 'dh_feature'
  );
  $result = dh_get_properties($model_info, 'name');
  if (isset($result['dh_properties'])) {
    $models = entity_load('dh_properties', array_keys($result['dh_properties']));
    foreach ($models as $model) {
      $criteria = array();
      $criteria[] = array(
        'name' => 'varid',
        'op' => 'IN',
        'value' => dh_varkey2varid('om_class_textField'),
      );
      $model->loadComponents($criteria);
      $model_riverseg = is_object($model->dh_properties['riverseg']) ? $dh_properties['riverseg']->propcode : FALSE;
      if (is_object($model_riverseg)) {
        $model_riverseg = ($model_riverseg->propcode == $riverseg) ? $model_riverseg = $model : FALSE;
      }
      if (!$model_riverseg) {
        // need to create a new riverseg:facility model 
      }
    }
  }
}

?>
