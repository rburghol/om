<?php
# set up db connection
#include('config.php');
$noajax = 1;
$projectid = 3;
include_once('xajax_modeling.element.php');
#error_reporting(E_ALL);
print("Un-serializing Model Object <br>");
$debug = 0;

$elementid = 30665;
if (isset($_GET['elementid'])) {
   $elementid = $_GET['elementid'];
}
if (isset($argv[1])) {
   $elementid = $argv[1];
}
   
// create a shell object
// create a timer
// add a matrix 

$thisobresult = unSerializeModelObject($elementid);
$thisobject = $thisobresult['object'];
$thisname = $thisobject->name;
$thisobject->debug = 1;
$thisobject->outdir = $outdir;
$thisobject->outurl = $outurl;

$thisobject->wake();
error_log("Element $elementid wake() Returned from calling routine.");
$thisobject->init();
error_log("Element $elementid init() Returned from calling routine.");
$debugstring = '';

error_log("Column Defs:" . print_r($thisobject->column_defs,1));

$thisobject->initTimer();
$thisobject->step();
$phub_class = get_class($thisobject->processors['broadcast_hydro']);
error_log("Phub class: $phub_class");
$phubhub_class = get_class($phub->parentHub);
error_log("Phubhub class: $phubhub_class");
$phub = $thisobject->processors['broadcast_hydro'];
error_log("PhubPhub ardata = " . print_r($phub->parentHub->arData,1));
error_log("Phub ardata = " . print_r($phub->arData,1));

?>