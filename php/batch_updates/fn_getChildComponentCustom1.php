<?php

$noajax = 1;
$projectid = 3;
$userid = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ALL);
##include_once("./lib_batchmodel.php");

if (count($argv) < 3) {
   print("Usage: fn_getChildComponentCustom1.php elementid custom1 \n");
   die;
}

$elementid = $argv[1];
$custom1 = $argv[2];
$debug = 1;

print("Requested for Element $elementid: \n");
$tree = getChildComponentCustom1($listobject, $elementid, $custom1, 1);
print_r($tree);

print("\n");
$elist = array();
foreach ($tree as $thisbranch) {
   $elist[] = $thisbranch['elementid'];
}
print("Element Ids: " . implode(",", $elist) . "\n");


?>