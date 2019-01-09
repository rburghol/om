<?php

$noajax = 1;
$projectid = 3;
$userid = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
$scenid = 37;
$londd = -79.0;
$latdd = 36.5;
if (isset($_GET['latdd'])) {
   if (isset($_GET['scenarioid'])) $scenid = $_GET['scenarioid'];
   if (isset($_GET['latdd'])) $latdd = $_GET['latdd'];
   if (isset($_GET['londd'])) $londd = $_GET['londd'];
} else {
   if (count($argv) < 3) {
      print("Usage: fn_findCOVALocationPossibilities.php scenarioid latdd londd \n");
      print("Browser: fn_findCOVALocationPossibilities.php?latdd=xx.y&londd=zz.w\n");
      die;
   }
   $scenid = $argv[1];
   $latdd = $argv[2];
   $londd = $argv[3];
}
$debug = 1;
print("Finding Model Container for Lat: $latdd, Lon: $londd <br>\n");
$options = findCOVALocationPossibilities($listobject, $scenid, $latdd, $londd);
foreach ($options as $key => $val) {
   $val['the_geom'] = substr($val['the_geom'], 0, 32) . " ... (truncated) ";
   $options[$key] = $val;
}
if (isset($_GET['latdd'])) print("<pre>");
print("Options: " . print_r($options,1) . " <br>\n");
if (isset($_GET['latdd'])) print("</pre>");


?>
