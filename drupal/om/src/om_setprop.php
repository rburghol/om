#!/user/bin/env drush
<?php
module_load_include('inc', 'om', 'src/om_translate_to_dh');

$args = array();
while ($arg = drush_shift()) {
  $args[] = $arg;
}

// Is single command line arg?
$vars = array();
if (count($args) >= 6) {
  $query_type = $args[0];
  $vars['entity_type'] = $args[1];
  $vars['featureid'] = $args[2];
  $vars['pid']= $args[3];
  $vars['propname'] = $args[4];
  $vars['propvalue'] = $args[5];
  $vars['propcode'] = $args[6];
} else {
  error_log("Usage: php om_setprop.php query_type entity_type featureid pid propname propvalue propcode ");
  error_log("Note: 'all' is not yet enabled");
  die;
}

if ($query_type <> 'cmd') {
  error_log("Only cmd mode enabled");
  die;
}

$q = "select pid from {dh_properties}  ";
$q .= " where propname = :propname "
$q .= " and entity_type = :entity_type "
if ($vars['pid'] <> 'all') {
  $q .= " and entity_type = :pid ";
  $vars['pid'] = $pid;
} 








?>