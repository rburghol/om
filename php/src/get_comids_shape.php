<?php

$noajax = 1;
include('./config.php');
if (count($argv) < 1) {
  error_log("Return a st_UNION WKT given a list of comids");
  error_log("USAGE: php get_comids_shape.php name comids (csv) [debug=0]\n");
  die;
}
$name = $argv[1];
$comids = $argv[2];
$debug = $argv[3];
$wktgeom = getMergedNHDShape($usgsdb, $seglist, array(), $debug);
echo $wkt_geom;
?>
