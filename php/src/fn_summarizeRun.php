<?php


// checks for files/runs fidelity - clears them if they fail vertain tests

// choose the elements to run, these must be root monitoring sites, as indicated by the suffix 'A01' -- 'A12'
$noajax = 1;
$projectid = 3;
include_once("./xajax_modeling.element.php");
include_once("./lib_verify.php");
include_once("./lib_batchmodel.php");

if (isset($_GET['elementid'])) {
  $args = $_GET;
} else {
  error_log(print_r($argv,1));
  $args = array();
  $names = array('elementid', 'runid', 'startdate', 'enddate', 'cachedate', 'debug');
  foreach ($names as $ix => $name) {
    if (isset($argv[$ix + 1])) {
      $args[$name] = $argv[$ix + 1];
    }
  }
}
  error_log(print_r($args,1));
$defaults = array(
  'elementid' => 0, 
  'runid' => 0 , 
  'startdate' => '1984-01-01', 
  'enddate' => '2005-12-31', 
  'cachedate' => date('Y-m-d'), 
  'debug' => 0,
  'force'=> 1,
  'strict' => 1
);
$args = $args + $defaults;
  error_log(print_r($args,1));
if ($args['elementid'] > 0) {
   //
} else { 
   print("Usage: php fn_checkTreeRunDate.php elementid runid [startdate] [enddate] [cachedate] [debug=0] [force=0] [strict=1]\n");
   die;
}
$elementid = $args['elementid'];
$runid = $args['runid'];
$startdate = $args['startdate'];
$enddate = $args['enddate'];
$cachedate = $args['cachedate'];
$debug = $args['debug'];
$force = $args['force'];
$strict = $args['strict'];
error_reporting(E_ALL);
print("Clearing elementid $elementid, Run - $runid \n");
$status = checkTreeRunDate($listobject, $elementid, $runid, $startdate, $enddate, $cachedate, $debug);
print("Status: $status.\n");
$output = summarizeRun($listobject, $elementid, $runid, $startdate, $enddate, $force, $strict);

echo print_r($output,1);
?>
