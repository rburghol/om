#!/user/bin/env drush
<?php
module_load_include('inc', 'om', 'src/om_translate_to_dh');

$args = array();
while ($arg = drush_shift()) {
  $args[] = $arg;
}

// Is single command line arg?
$vars = array();
if (count($args) >= 1) {
  $vars['varkey'] = $args[0];
} else {
  error_log("Usage: php om_getvardef.php varkey");
  die;
}

$q = "select hydroid from {dh_variabledefinition} ";
$q .= " where varkey = :varkey ";
error_log($q . "vars " . print_r($vars,1));

$rez = db_query($q, $vars);
$varid = $rez->fetchColumn();
error_log("varid:" . $varid);
echo $varid;

?>