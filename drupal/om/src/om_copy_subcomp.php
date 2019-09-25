#!/user/bin/env drush
<?php
module_load_include('inc', 'om', 'src/om_translate_to_dh');

$args = array();
while ($arg = drush_shift()) {
  $args[] = $arg;
}

// Is single command line arg?
if (count($args) >= 3) {
  $query_type = $args[0];
  $src_id = $args[1];
  $dest_id = $args[2];
} else {
  print("Usage: php copy_subcomps.php query_type src_id dest_id [all/sub1[|newname],sub2,...] [cascade=0/1] \n");
  die;
}

if ($query_type == 'file') {
  $filepath = $src_id;
  error_log("File requested: $filepath");
}


$src_prop = om_load_dh_model($query_type, $src_id, $model_name);
$dest_prop = om_load_dh_model($query_type, $dest_id, $model_name);

?>