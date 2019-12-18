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
} else {
  error_log("Usage: php om_getpid.php entity_type featureid propname");
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
$pid = $rez->fetchColumn();
echo $pid;

?>