<?php

$noajax = 1;
include('./config.php');
if (count($argv) < 3) {
   print("USAGE: php get_nhd_basins.php pointname lat lon [debug=0]\n");
   die;
}
list($pointname, $latdd, $londd, $debug) = array($argv[1], $argv[2], $argv[3], $argv[4]);
$outlet_info = findNHDSegment($usgsdb, $latdd, $londd);
$outlet = $outlet_info['comid'];
$area = $outlet_info['areasqkm'];
$carea = $outlet_info['cumdrainag'];
print("Outlet COMID : $outlet \n");
print("Outlet Cumulative Area : $carea \n");
print("Outlet Local Area : $area \n");
print("Finding Tribs $outlet \n");
$result = findTribs($usgsdb,$outlet, $debug);
print("Individual Tribs : \n");
print_r($result['segment_list']);
$result = findMergedTribs($usgsdb,$outlet, $debug);
print("\\nMerged Tribs : \n");
print_r($result['merged_segments']);
print("\n");
$wktgeom = getMergedNHDShape($usgsdb, array($comid), array(), 1);
storeNHDMergedShape($usgsdb, $comid, $wktgeom, 0, 1);
?>
