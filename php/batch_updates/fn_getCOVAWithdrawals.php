<?php

$noajax = 1;
$projectid = 3;
$userid = 1;

include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
#include_once("./lib_batchmodel.php");

if (count($argv) < 2) {
   print("Usage: fn_getCOVADischarges.php scenarioid riversegment \n");
   die;
}

$scenarioid = $argv[1];
$riverseg = $argv[2];

$debug = FALSE;
$elid = getCOVACBPContainer($listobject, $scenarioid, $riverseg);
$container = getChildComponentCustom1($listobject, $elid, 'cova_pswd', -1);
error_log("Withdrawal & PS Main Container" . print_r($container,1));
$wd = getCOVAWithdrawals($listobject, $elid, array(), $debug);
print("getCOVAWithdrawals(listobject, $elid) \n");
error_log(print_r($wd,1));
foreach ($wd as $thiswd) {
   //print($thiswd['vpdes_permit_no'] . "\n");
}

?>