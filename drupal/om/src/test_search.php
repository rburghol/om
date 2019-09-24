#!/user/bin/env drush
<?php
// Create Facility:riverseg model element in dH
// or create MP:riverseg model element in dH
module_load_include('inc', 'om', 'src/om_translate_to_dh');

$pid = om_get_search_model_subprops('dh_feature', 67292, 'riverseg', TRUE, 'YM3_6430_6620', 'vahydro-1.0');
error_log("Found $pid");
// now that pid parent is the model in question
?>