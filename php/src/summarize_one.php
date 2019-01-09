<?php

$noajax = 1;
$projectid = 3;
$scid = 28;
$root = 1;

switch ($root) {
   case 0:
   include('/var/www/html/wooomm/xajax_modeling.element.php');
   print("Using stable version of model - library = $libpath \n");
   break;

   case 1:
   include('/var/www/html/wooommdev/xajax_modeling.element.php');
   print("Using development version of model\n");
   break;

   default:
   include('/var/www/html/wooommdev/xajax_modeling.element.php');
   break;
}
//include("./lib_verify.php");

error_reporting(E_ALL);

// patch ICPRB watershed linkages and names
// two modes: 
   // 1) check for dropped linkages (upstream), missing to_node (downstream) - check for unintended links to new records
   // 2) process changes

// types of changes that may take place:
// watershed object name
// downstream linkages
// upstream linkages
// land use update/insert
if ( ($argv[1] == '--help') or (count($argv) < 5)) {
   print("Usage: php summarize_one.php elementid runid rundate startdate enddate \n");
   die;
}
$been_run = checkRunDate($listobject, $argv[1], $argv[2], $argv[3], $argv[4], $argv[5], 1);
summarizeRun($listobject, $recid, $run_id, $startdate, $enddate, 0, $strict);
print("Has Been Run: $been_run \n");
?>
