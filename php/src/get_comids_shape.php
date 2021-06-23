<?php

$noajax = 1;
include_once('xajax_modeling.element.php');
if (count($argv) < 2) {
  error_log("Return a st_UNION WKT given a list of comids");
  error_log("USAGE: php get_comids_shape.php name comids (csv) [debug=0]\n");
  die;
}
$name = $argv[1];
$comids = $argv[2];
$debug = $argv[3];
$wktgeom = getMergedNHDShape($usgsdb, $comids, array(), $debug);
echo "WKT for $name ($comids) = \n" . $wkt_geom;
?>
