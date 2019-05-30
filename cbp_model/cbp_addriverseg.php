<?php

// this script adds riversegment location to the cbp_model_location table 
# set up db connection
#include('config.php');
$noajax = 1;
$projectid = 3;

include_once('config.php');
#include('qa_functions.php');
#include('ms_config.php');
/* also loads the following variables:
   $libpath - directory containing library files
   $indir - directory for files to be read
   $outdir - directory for files to be written
*/
error_reporting(E_ERROR);
$debug = 0;
$localdebug = 0;

$basin_outlet = '';
$scid = 3;
if (isset($_GET['basin_outlet'])) {
   $basin_outlet = $_GET['basin_outlet'];
}
if (isset($_GET['scid'])) {
   $scid = $_GET['scid'];
}
if (isset($argv[1])) {
   $scid = $argv[1];
}
if (isset($argv[2])) {
   $basin_outlet = $argv[2];
}

# initilize cbp data connection
$cbp_connstring = "host=$dbip dbname=cbp user=$dbuser password=$dbpass ";
//print($cbp_connstring);
$cbp_dbconn = pg_connect($cbp_connstring, PGSQL_CONNECT_FORCE_NEW);
$cbp_listobject = new pgsql_QueryObject;
$cbp_listobject->ogis_compliant = 1;
$cbp_listobject->connstring = $cbp_connstring;
$cbp_listobject->dbconn = $cbp_dbconn;
$cbp_listobject->adminsetuparray = $adminsetuparray;


# get list of files in uci directory
# iterate through list, using element 589 as the template, and simply setting the uciname to a new value and calling init()
switch ($scid) {
   case 3:
   $path = "/opt/model/p52icprb/StreamWDM/Current";
   break;

   case 4:
   $path = "/opt/model/p53/p532c-sova/tmp/wdm/river/p532sova/stream";
   break;

   case 2:
   $path = "/opt/model/p52/tmp/wdm/river/p52An/stream";
   break;
}
//$ext = 'uci'; // use if uci files are your base
$ext = 'wdm';

if ( strlen($basin_outlet) == 0 ) {
   $files = getFileArray($path,$ext);
} else {
   $cbp_listobject->querystring = " select linkage_table, linkage_column from cbp_scenario where scenarioid = $scid ";
   $cbp_listobject->performQuery();
   print("$cbp_listobject->querystring ; <br>\n");
   $linkage_table = $cbp_listobject->getRecordValue(1,'linkage_table');
   $linkage_column = $cbp_listobject->getRecordValue(1,'linkage_column');
   $data = getCBPSegList($cbp_listobject, $linkage_table, $linkage_column, $basin_outlet, $debug,-1);
   $segments = $data['segments'];
   $segnames = $data['segnames'];
   $files = array();
   foreach ($segnames as $thisseg) {
      array_push($files, $thisseg . ".uci");
   }
}
print_r($files);
//die;
$i = 0;

foreach ($files as $uciname) {
   $ucipath = $path . $uciname;
   $cbp_listobject->init();

   $riverseg = substr($ucipath,-17,13);
   print("River Segment: $riverseg<br>\n");
   $newprops = array('filepath' => $ucipath);
   
   $cbp_listobject->querystring = " select location_id from cbp_model_location ";
   $cbp_listobject->querystring .= " where scenarioid = $scid ";
   $cbp_listobject->querystring .= " and id1 = 'river' ";
   $cbp_listobject->querystring .= " and id2 = '$riverseg' ";
   print("$cbp_listobject->querystring ; <br>\n");
   $cbp_listobject->performQuery();
   if (count($cbp_listobject->queryrecords) > 0) {
      $locid = $cbp_listobject->getRecordValue(1,'location_id');
      $cbp_listobject->querystring = " update cbp_model_location ";
      $cbp_listobject->querystring .= " set last_updated = now() ";
      $cbp_listobject->querystring .= " where location_id = $locid ";
      print("$cbp_listobject->querystring ; <br>\n");
      $cbp_listobject->performQuery();
   } else {
      # need to insert this location
      $cbp_listobject->querystring = " insert into cbp_model_location (scenarioid, id1, id2 ) ";
      $cbp_listobject->querystring .= " values ($scid, 'river', '$riverseg' ) ";
      print("$cbp_listobject->querystring ; <br>\n");
      $cbp_listobject->performQuery();
      
      $cbp_listobject->querystring = " select location_id from cbp_model_location ";
      $cbp_listobject->querystring .= " where scenarioid = $scid ";
      $cbp_listobject->querystring .= " and id1 = 'river' ";
      $cbp_listobject->querystring .= " and id2 = '$riverseg' ";
      print("$cbp_listobject->querystring ; <br>\n");
      $cbp_listobject->performQuery();
      if (count($cbp_listobject->queryrecords) > 0) {
         $locid = $cbp_listobject->getRecordValue(1,'location_id');
      } else {
         print("Could not insert 'river' named $riverseg . <br>\n");
         break;
      }
   }

}

?>
