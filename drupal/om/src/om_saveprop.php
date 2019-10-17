#!/user/bin/env drush
<?php
module_load_include('inc', 'om', 'src/om_translate_to_dh');

$args = array();
while ($arg = drush_shift()) {
  $args[] = $arg;
}

// Is single command line arg?
$vars = array();
if (count($args) >= 4) {
  $query_type = $args[0];
  $vars['entity_type'] = $args[1];
  $vars['featureid'] = $args[2];
  $vars['propname'] = $args[3];
} else {
  error_log("Usage: php om_saveprop.php query_type entity_type featureid propname");
  error_log("Note: 'file' is not yet enabled");
  error_log("Note: Use featureid = -1 for all ");
  die;
}

if ($query_type <> 'cmd') {
  error_log("Only cmd mode enabled");
  die;
}

$q = "select pid from {dh_properties} ";
$q .= " where propname = :propname ";
$q .= " and entity_type = :entity_type ";
if ($vars['featureid'] <> 'all') {
  $q .= " and featureid = :featureid ";
} else {
  unset($vars['featureid']);
}
error_log($q);

$rez = db_query($q, $vars);
while ($pid = $rez->fetchColumn()) {
  $prop = entity_load_single('dh_properties', $pid);
  error_log("saving $prop->propname ($prop->pid)");
  $prop->save();
}


?>