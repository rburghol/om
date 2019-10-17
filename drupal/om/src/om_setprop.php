#!/user/bin/env drush
<?php
module_load_include('inc', 'om', 'src/om_translate_to_dh');

$args = array();
while ($arg = drush_shift()) {
  $args[] = $arg;
}

// Is single command line arg?
if (count($args) >= 2) {
  $query_type = $args[0];
  $entity_type = $args[1];
  $featureid = $args[2];
  $dest_entity_type = $args[3];
  $dest_id = $args[4];
  $propname = $args[5];
} else {
  error_log("Usage: php copy_subcomps.php query_type src_entity_type src_id dest_entity_type dest_id [all/propname[|newname],sub2,...] [cascade=0/1]");
  error_log("Note: 'all' is not yet enabled");
  die;
}


?>