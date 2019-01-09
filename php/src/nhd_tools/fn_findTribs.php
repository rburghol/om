<?php

$noajax = 1;
include('./config.php');

if (count($argv) < 2) {
   print("Usage: php fn_findTribs.php comid\n");
   die;
}
$comid = $argv[1];

$tribs = findTribs($usgsdb, $comid, 1);

print("Tribs: \n" . print_r($tribs,1) . "\n");
?>
