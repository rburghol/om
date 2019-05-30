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
if (count($argv) < 3) {
   error_log("Usage: php get_locationid.php scenario_name id1 id2 id3 \n");
   die;
}
$scenarioname = $argv[1];
$id1 = $argv[2];
$id2 = $argv[3];
$id3 = $argv[4];
if ($id1 == 'river') {
   error_log("Getting full river segment name from $id2 ");
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
   //error_log(print_r($scinfo,1));
   $data = getCBPSegList($cbp_listobject, $linkage_table, $linkage_column, $id2, 1, 0);
   //print(print_r($data,1) . "\n");
   //$data = getCBPSegList($cbp_listobject, 'sc_cbp5', 'catcode2', $basin_outlet, 1,-1);
   $segments = $data['segments'];
   $id2 = $data['segnames'][0];
}
//print_r($argv);

// obtain standard scenario info
$scinfo = getCBPScenarioInfo($scenarioname);
$scid = $scinfo['scid'];

$locid = getCBPLocationID($cbp_listobject, $scid, $id1, $id2, $id3);

echo $locid;
?>