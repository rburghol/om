#!/user/bin/env drush
<?php
module_load_include('inc', 'om', 'src/om_translate_to_dh');

$args = array();
while ($arg = drush_shift()) {
  $args[] = $arg;
}

// Is single command line arg?
$vars = array();
$query_type = $args[0];
if (count($args) >= 4) {
  $vars['entity_type'] = $args[1];
  $vars['featureid'] = $args[2];
  $vars['propname'] = $args[3];
} elseif ($query_type == 'pid') {
  $vars['pid'] = $args[1];
} else {
  error_log("Usage: php om_deleteprop.php query_type entity_type featureid propname");
  error_log("Note: 'file' is not yet enabled");
  error_log("Note: Use featureid = -1 for all ");
  die;
}

if (!in_array($query_type, array('cmd', 'pid') )) {
  error_log("Only cmd & pid mode enabled");
  die;
}

$q = "select pid from {dh_properties} ";
switch ($query_type) {
  case 'cmd':
  $q .= " where propname = :propname ";
  $q .= " and entity_type = :entity_type ";
  if ($vars['featureid'] <> 'all') {
    $q .= " and featureid = :featureid ";
  } else {
    unset($vars['featureid']);
  }
  break;
  
  case 'pid':
  $q .= " where pid = :pid ";
  break;
}
error_log($q . "vars " . print_r($vars,1));

$rez = db_query($q, $vars);
while ($pid = $rez->fetchColumn()) {
  $prop = entity_load_single('dh_properties', $pid);
  error_log("Deleting $prop->propname ($prop->pid)");
  entity_delete('dh_properties', $prop->pid);
}


?>