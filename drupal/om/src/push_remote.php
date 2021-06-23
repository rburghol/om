#!/user/bin/env drush
<?php
module_load_include('inc', 'om', 'src/om_translate_to_dh');

$args = array();
while ($arg = drush_shift()) {
  $args[] = $arg;
}
$pid = $args[0];

error_log( "pid:" . $pid);
$obj = entity_load_single('dh_properties', $pid);
$remote = om_load_dh_property($obj, 'om_element_connection');
if (is_object($remote)) {
  $plugin = dh_variables_getPlugins($remote);
  error_log("Trying to pushAllToRemote on plugin of type " . get_class($plugin));
  $plugin->pushAllToRemote($remote);
} else {
  error_log("No remtoe comnection found");
}
?>