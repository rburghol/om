<?php

$noajax = 1;
include('./config.php');

if (count($argv) < 2) {
   print("Usage: php singlept_drainage.php comid\n");
   die;
}
$comid = $argv[1];

$wktgeom = getMergedNHDShape($usgsdb, array($comid), array(), 1);
storeNHDMergedShape($usgsdb, $comid, $wktgeom, 0, 1);

?>
