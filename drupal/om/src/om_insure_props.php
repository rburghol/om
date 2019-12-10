#!/user/bin/env drush
<?php
module_load_include('inc', 'om', 'src/om_translate_to_dh');

$args = array();
while ($arg = drush_shift()) {
  $args[] = $arg;
}
error_log('args:' . print_r($args,1));
// Is single command line arg?
$vars = array();
$query_type = $args[0];
if (count($args) >= 2) {
  $pid = $args[1];
  $propname = isset($args[2]) ? $args[2] : 'all';
} else {
  error_log("Usage: php om_insure_props.php query_type pid [propname=all]");
  error_log("Note: 'file' is not yet enabled");
  error_log("Note: Use featureid = -1 for all ");
  die;
}

if (!in_array($query_type, array('cmd') )) {
  error_log("Only cmd mode enabled");
  die;
}
error_log("Loading pid = $pid ");
$prop = entity_load_single('dh_properties', $pid);
$plugin = dh_variables_getPlugins($prop); 

if ($propname == 'all') {
  $propname = FALSE;
}
error_log("Plugin: " . get_class($plugin));
$plugin->loadProperties($prop, FALSE, $propname, TRUE);

?>