#!/user/bin/env drush
<?php
// Create Facility:riverseg model element in dH
// or create MP:riverseg model element in dH
module_load_include('inc', 'om', 'src/om_translate_to_dh');

// om_find_parent_feature()
$pid = 4696570;
$prop = entity_load_single('dh_properties', $pid);

$parent = om_find_parent_feature($prop);
error_log("found " . $parent->name . " - " . $parent->hydroid);

// om_get_search_model_subprops()
/*
$pid = om_get_search_model_subprops('dh_feature', 67292, 'riverseg', TRUE, 'YM3_6430_6620', 'vahydro-1.0');
error_log("Found $pid");
// now that pid parent is the model in question
$riverseg_prop = entity_load_single('dh_properties', $pid);
$model = $riverseg_prop->featureid;
*/

?>