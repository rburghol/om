<?php

$noajax = 1;
include('./config.php');

if (count($argv) < 2) {
   print("Usage: php delete_nhd.php comid [debug]\n");
   die;
}
$comid = $argv[1];
if (isset($argv[2])) {
   $debug = $argv[2];
}

deleteNHDMergedShape($usgsdb, $comid, $debug);

?>
