<?php


// checks for files/runs fidelity - clears them if they fail vertain tests

// choose the elements to run, these must be root monitoring sites, as indicated by the suffix 'A01' -- 'A12'
$noajax = 1;
$projectid = 3;
include_once("./xajax_modeling.element.php");
include_once("./lib_verify.php");
include_once("./lib_batchmodel.php");

if (isset($argv[1])) {
   $elementid = $argv[1];
} else { 
   print("Usage: php fn_checkTreeRunDate.php elementid runid [startdate] [enddate] [cachedate] [debug=0]\n");
   die;
}
if (isset($argv[2])) {
   $runid = $argv[2];
} else {
   print("Usage: php fn_clearRun.php elementid runid \n");
   die;
}
$startdate = isset($argv[3]) ? $argv[3] : '1984-01-01';
$enddate = isset($argv[4]) ? $argv[4] : '2005-12-31';
$cache_date = isset($argv[5]) ? $argv[5] : date('Y-m-d');
$debug = isset($argv[6]) ? $argv[6] : 0;
error_reporting(E_ALL);
print("Clearing elementid $elementid, Run - $runid \n");
$status = checkTreeRunDate($listobject, $elementid, $runid, $startdate, $enddate, $cache_date, $debug);
print("Status: $status.\n");

$is_run = verifyRunStatus($listobject, $elementid, $runid, $serverip);
error_log("Is Run?: " . print_r($is_run,1));
?>
