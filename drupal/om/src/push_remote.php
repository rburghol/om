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
$plugin = $plugin = dh_variables_getPlugins($obj);
error_log("Trying to pushAllToRemote on plugin of type " . get_class($plugin));
$plugin->pushAllToRemote($obj);

?>