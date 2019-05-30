<?php
# set up db connection
include('config.php');
$noajax = 1;
$projectid = 3;
$scid = 4;

//include_once('xajax_modeling.element.php');
//include_once('config.php');
#include('qa_functions.php');
#include('ms_config.php');5
/* also loads the following variables:
   $libpath - directory containing library files
   $indir - directory for files to be read
   $outdir - directory for files to be written
*/
error_reporting(E_ERROR);
$debug = 0;
$dt = 86400;
$starttime = '1984-01-01';
$endtime = '2005-12-31';
//$endtime = '1984-01-04';
//$scenarioname = 'p52An';
$scenarioname = 'p53cal';
$forceoverwrite = 0;

if (count($argv) < 3) {
   print("Usage: php cbpget_river_wdm3.php reachid(4-digit) dsn [scenarioname] [forceoverwritre=0] \n");
   die;
}

$basin_outlet = $argv[1];
$onedsn = $argv[2];
   
if (isset($argv[3])) {
   if (strlen($argv[3]) > 0) {
      $scenarioname = $argv[3];
   }
}
if (isset($argv[4])) {
   if (strlen($argv[3]) > 0) {
      $forceoverwrite = $argv[4];
      //error_log("Over-write?: $forceoverwrite \n");
   }
} 

$dsns = array($onedsn);


// items of interest:
// WDM4 - SURO - 111 - surface runoff
// WDM4 - IFWO - 211 - interflow
// WDM4 - AGWO - 411 - groundwater
$scinfo = getCBPScenarioInfo($scenarioname);
$scid = $scinfo['scid'];
$modelbase = $scinfo['modelbase'];
$metbase = $scinfo['metbase'];
$ucibase = $scinfo['ucipath'];
$landbase = $scinfo['landbase'];
$wdms = $scinfo['wdms'];
$dsn_names = $scinfo['reach_dsn_names'];
$linkage_table = $scinfo['linkage_table'];
$linkage_column = $scinfo['linkage_column'];

$data = getCBPSegList($cbp_listobject, $linkage_table, $linkage_column, $basin_outlet, 1, 0);
//print(print_r($data,1) . "\n");
//$data = getCBPSegList($cbp_listobject, 'sc_cbp5', 'catcode2', $basin_outlet, 1,-1);
$segments = $data['segments'];
$id1 = 'river';
$id2 = $data['segnames'][0];
$locinfo = getModelLocation($cbp_listobject, $scid, 'river', $id2, '','', 1, 0);

$modelbasedir = $scinfo['basedir'];
$path = $scinfo['reachpath'];
$filebase = $id2 . ".wdm";
$uciname = $id2 . ".uci";
$ucipath = $ucibase . "/$uciname";
$uciobject->ucidir = $ucibase;
$uciobject->uciname = $uciname;
$uciobject->init();
$wdm_rec = getExtSourceUCI($uciobject, $onedsn);
error_log("$ucipath,\n $wdmpath,\n DSNs: $onedsn,\n $scid\n  $id1, $id2, $id3 \n WDM From UCI:" . print_r($wdm_rec,1) . "\n");

$wdmpath = str_replace('../../..', $modelbase, $wdm_rec['filepath']);
error_log("Returning WDM Path $wdmpath \n");
echo $wdmpath;
?>
