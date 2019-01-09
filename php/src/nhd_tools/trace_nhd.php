<?php

$noajax = 1;
include('./config.php');

if (count($argv) < 2) {
   print("Usage: php trace_nhd.php comid [debug]\n");
   die;
}
$comid = $argv[1];
if (isset($argv[2])) {
   $debug = $argv[2];
}

$result = findTribs($usgsdb, $comid, $debug = 0);
print("Direct Tributaries to this object: " . print_r($result['tribs'],1) . "\n");
print("Segments in this tree: " . print_r($result['segment_list'],1) . "\n");

?>
