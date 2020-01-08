<?php

module_load_include('module', 'om');
$a = arg();
$pid = NULL;

if (isset($a[2])) {
  $pid = $a[2];
} else {
  echo "Usage: /[pid]<br>";
}
if ($pid <> NULL) {
  $prop = entity_load_single('dh_properties', $pid);
  $plugin = dh_variables_getPlugins($prop);
  $exp = $plugin->exportOpenMI($prop);
  $vars = array();
  $plugin->exportVarDefs($prop, $vars);

  dpm($exp,'Export');
  dpm($vars,'Vars');


  $exp_json = json_encode($exp, JSON_PRETTY_PRINT);
  $vars_json = json_encode($vars, JSON_PRETTY_PRINT);

  echo "<pre>$exp_json</pre>";
  echo "<pre>$vars_json</pre>";
}
?>