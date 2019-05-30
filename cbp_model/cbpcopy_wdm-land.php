<?php


# set up db connection
#include('config.php');
$noajax = 1;
$projectid = 3;
$scid = 4;

//include_once('xajax_modeling.element.php');
include_once('config.php');
error_reporting(E_ERROR);
error_log("Un-serializing Model Object <br>\n");
$debug = 0;

$dt = 86400;
$starttime = '1984-01-01';
$endtime = '2005-12-31';
$scenarioname = 'p52An';

if (isset($_GET['scenarioname'])) {
   $scenarioname = $_GET['scenarioname'];
}
if (isset($argv[1])) {
   $scenarioname = $argv[1];
}
if (isset($argv[2])) {
   $onelu = $argv[2];
   error_log("Single landuse requested: $onelu \n");
} else {
   $onelu = '';
}
if (isset($argv[3])) {
   $onelseg = trim($argv[3]);
   error_log("Single land segment requested: $onelseg \n");
} else {
   $onelseg = '';
}
if (isset($argv[4])) {
   $onedsn = $argv[4];
   error_log("Single DSN requested: $onedsn \n");
} else {
   $onedsn = '';
}
if (isset($argv[5])) {
   $forceoverwrite = $argv[5];
   error_log("Over-write?: $forceoverwrite \n");
} else {
   $forceoverwrite = 0;
}
//print_r($argv);

// obtain standard scenario info
$scinfo = getCBPScenarioInfo($scenarioname);
$scid = $scinfo['scid'];
$modelbase = $scinfo['modelbase'];
$metbase = $scinfo['metbase'];
$landbase = $scinfo['landbase'];
$wdms = $scinfo['wdms'];
$dsn_names = $scinfo['dsn_names'];

$forceoverwrite = 0; # whether or not to force a rerun of all UCI's, or just to do ones that are not in the db
// items of interest:
// WDM4 - SURO - 111 - surface runoff
// WDM4 - IFWO - 211 - interflow
// WDM4 - AGWO - 411 - groundwater

// this syntax will call a single Land segment, but with ALL land uses:
// php ~/www-html/wooommdev/test_cbpdump_wdm-land.php p52An '' A24043


$perlnds = array('afo','for','hvf','hyo','lwm','nhi','nhy','npa','puh','trp','alf','ext','hom','hwm','hyw','nal','nho','nlo','pas','pul','urs');
$implnds = array( 'bar','imh','iml');

if (strlen($onedsn) > 0) {
   $dsns = array($onedsn);
} else {
   $dsns = array('211');
}
//$dsns = array('111','211','411');
// right now, for various reasons, only one DSN may be read at a time.  This is a problem from a time 
// stand point, but not that big a deal in the grand scheme of things
// since we can only handle one DSN at a time, we can retrieve the custom path for the WDM
$wdm_no = $dsn_names[$dsns[0]]['wdm'];
error_log("WDM Number $wdm_no identified.\n");
$path = $wdms[$wdm_no]['path'];


# get list of files in uci directory
# iterate through list, using element 589 as the template, and simply setting the uciname to a new value and calling init()

if ( ($onelu == '') ) {
   $lunames = getFileArray($path,'');
   error_log("Loading lu-names from $path <br>\n");
} else {
   $lunames = array($onelu);
}
$i = 0;
foreach ($lunames as $luname) {
   switch ($wdm_no) {
      case 3:
      $dirpath = $path . "/$luname/$scenarioname";
      break;
      
      case 2:
      $dirpath = $path;
      error_log("Selected path $dirpath <br>\n");
      break;
      
      case 1:
      $dirpath = $path;
      error_log("Selected path $dirpath <br>\n");
      break;
      
      default:
      $dirpath = $path . "/$luname/$scenarioname";
      break;
   }
   
   $files = getFileArray($dirpath,'wdm');
   error_log("Land Segment WDM's found in $dirpath : found " . count($files) . " wdm files <br>\n");
   error_log("Handling $luname <br>\n");
   $desiredfiles = array();
   error_log("Searching for filename pattern %$onelseg% <br>\n");
   foreach ($files as $filename) {
      if (strlen($onelseg) > 0) {
         //print("Searching for $onelseg in $filename - position: " . strpos($onelseg,$filename) . "\n");
         if (strpos($filename, $onelseg)) {
            $desiredfiles[] = $filename;
         }
      } else {
         $desiredfiles[] = $filename;
      }
   }
   error_log("Land Segment WDM's selected for import : <br>\n" . print_r($desiredfiles,1) . " <br>\n");
   foreach ($desiredfiles as $filename) {
      
      error_log("Handling $filename <br>\n");
      switch ($wdm_no) {
         case 3:
         $wdmpath = $path . "/$luname/$scenarioname/$filename";
         $landseg = substr($filename, 3,6);
         break;

         case 2:
         $wdmpath = $path . "/$filename";
         $landseg = substr($filename, 5,6);
         break;

         case 1:
         // meteorological input file
         $wdmpath = $path . "/$filename";
         $landseg = substr($filename, 4,6);
         break;

         default:
         $wdmpath = $path . "/$luname/$scenarioname/$filename";
         $landseg = substr($filename, 3,6);
         break;
      }
      
      $filename = importWDMData2($cbp_listobject, $dsn_names, $wdmpath, $dsns, $scid, $landseg, $luname, $starttime, $endtime);

   }
}
shell_exec('rm /tmp/error*');
echo $filename;
?>