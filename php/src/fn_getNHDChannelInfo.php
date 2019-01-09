<?php

$noajax = 1;
$projectid = 3;
$userid = 1;
include_once('xajax_modeling.element.php');

if (isset($_GET['latdd'])) {
   if (isset($_GET['latdd'])) $latdd = $_GET['latdd'];
   if (isset($_GET['londd'])) $londd = $_GET['londd'];
} else {
   if (count($argv) < 3) {
      print("USAGE: php fn_getNHDChannelInfo.php lat lon [debug=0]\n");
      die;
   }
   $latdd = $argv[1];
   $londd = $argv[2];
}

if (!class_exists('nhdPlusDataSource')) {
   print("Can not locate NHD+ Library");
   die;
}
print("Initializing nhdPlusDataSource <br>");
$nhd = new nhdPlusDataSource;
$nhd->units = 'mi';
$nhd->init();
if ( is_numeric($latdd) and is_numeric($londd)) {
   $nhd->debug = $debug;
   $nhd->getPointInfo($latdd, $londd);
   print("Searching for coords: $latdd, $londd <br>");
   print("NLCD Land Use: " . print_r($nhd->nlcd_landuse,1) . "<br>");
   //error_log("NHD+ Reaches: " . print_r($nhd->nhd_segments,1));
   print("Channel Slope: " . $nhd->channel_slope . "<br>");
   print("Channel Length(mi): " . $nhd->channel_length . "<br>");
   print("Channel Drainage Area (Sq. Mi.): " . $nhd->drainage_area . "<br>");
} else {
   print("Lat: $latdd and Lon: $londd must be numeric - abandoning <br>");
}
?>
