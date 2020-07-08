<?php
$noajax = 1;
$projectid = 3;
$userid = 1;
include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
#include_once("./lib_batchmodel.php");
$sumdir = '/var/www/html/d.dh/'; // default for post-processing

if (count($argv) < 2) {
   print("Usage: php fn_summarize_model.php elementid runid \n");
   die;
}

$elementid = $argv[1];
$runid = $argv[2];

$manifest = $outdir . "/manifest.$runid" . "." . $elementid . ".log";
error_log("Looking for manifest : $manifest");
$elements = file($manifest);
foreach ($elements as $elid) {
  $cmd_output = array();
  $cmd = "cd $sumdir \n";
  $elid = intval(trim($elid));
  $cmd .= "/opt/model/om/drupal/om/sh/summarize_element.sh $elid $runid";
  error_log("Executing Summary : $cmd");
  $forkout = exec( $cmd, $cmd_output );
}

?>
