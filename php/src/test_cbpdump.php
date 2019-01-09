<html>
<body>
<h3>Test Model Run</h3>

<?php


# set up db connection
#include('config.php');
$noajax = 1;
$projectid = 3;
$scid = 2;

include_once('xajax_modeling.element.php');
//include_once('config.php');
#include('qa_functions.php');
#include('ms_config.php');
/* also loads the following variables:
   $libpath - directory containing library files
   $indir - directory for files to be read
   $outdir - directory for files to be written
*/
error_reporting(E_ERROR);
print("Un-serializing Model Object <br>\n");
$debug = 0;
$localdebug = 0;

$elementid = 589; # the template object to use for data retrieval
$dt = 86400;
$starttime = '1984-01-01';
$endtime = '2005-12-31';
$basin_outlet = '';
$forceoverwrite = 1; # whether or not to force a rerun of all UCI's, or just to do ones that are not in the db

if (isset($_GET['basin_outlet'])) {
   $basin_outlet = $_GET['basin_outlet'];
}
if (isset($_GET['scid'])) {
   $scid = $_GET['scid'];
}
if (isset($argv[1])) {
   $basin_outlet = $argv[1];
}
if (isset($argv[2])) {
   $scid = $argv[2];
}
if (isset($argv[3])) {
   $forceoverwrite = $argv[3];
}
if (isset($argv[4])) {
   $localdebug = $argv[4];
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
$path = "/var/www/html/wooomm/dirs/proj3/components/cbp/";

if ( strlen($basin_outlet) == 0 ) {
   $files = getFileArray($path,'uci');
} else {
   $data = getCBPSegList($listobject, 'sc_cbp5', 'catcode2', $basin_outlet, $debug,-1);
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
   $thisobresult = unSerializeModelObject($elementid, $newprops, $cbp_listobject);
   //$thisobresult = unSerializeModelObject($elementid, $newprops);
   print("Object retrieved. Setting timer.<br>\n");
   $thisobject = $thisobresult['object'];
   $thisobject->listobject = $cbp_listobject;
   $newtimer = new simTimer;
   $newtimer->setStep($dt);
   $newtimer->setTime($starttime, $endtime);
   $thisobject->setSimTimer( $newtimer);
   
   $thisobject->max_memory_values = 1;
   
   $cbp_listobject->querystring = " select location_id from cbp_model_location ";
   $cbp_listobject->querystring .= " where scenarioid = $scid ";
   $cbp_listobject->querystring .= " and id1 = 'river' ";
   $cbp_listobject->querystring .= " and id2 = '$riverseg' ";
   //print("$cbp_listobject->querystring ; <br>\n");
   $cbp_listobject->performQuery();
   if (count($cbp_listobject->queryrecords) > 0) {
      $locid = $cbp_listobject->getRecordValue(1,'location_id');
      $cbp_listobject->querystring = " update cbp_model_location ";
      $cbp_listobject->querystring .= " set last_updated = now() ";
      $cbp_listobject->querystring .= " where location_id = $locid ";
      //print("$cbp_listobject->querystring ; <br>\n");
      $cbp_listobject->performQuery();
   } else {
      # need to insert this location
      $cbp_listobject->querystring = " insert into cbp_model_location (scenarioid, id1, id2 ) ";
      $cbp_listobject->querystring .= " values ($scid, 'river', '$riverseg' ) ";
      //print("$cbp_listobject->querystring ; <br>\n");
      $cbp_listobject->performQuery();
      
      $cbp_listobject->querystring = " select location_id from cbp_model_location ";
      $cbp_listobject->querystring .= " where scenarioid = $scid ";
      $cbp_listobject->querystring .= " and id1 = 'river' ";
      $cbp_listobject->querystring .= " and id2 = '$riverseg' ";
      //print("$cbp_listobject->querystring ; <br>\n");
      $cbp_listobject->performQuery();
      if (count($cbp_listobject->queryrecords) > 0) {
         $locid = $cbp_listobject->getRecordValue(1,'location_id');
      } else {
         print("Could not insert 'river' named $riverseg . <br>\n");
         break;
      }
   }
   
   # now, check to see if we want to overwrite, or ONLY add sites that have noe data
   # without checking for the period length, we are making aq big assumption that ALL data 
   # for a matching site is there, but that is OK for now.
   $cbp_listobject->querystring = " select count(*) as numrecs from cbp_scenario_output where location_id = $locid";
   $cbp_listobject->performQuery();
   if (count($cbp_listobject->queryrecords) > 0) {
      $numrecs = $cbp_listobject->getRecordValue(1,'numrecs');
   } else {
      $numrecs = 0;
   }
   print("$numrecs records already in scenario data table for $locid <br>\n");
   if ( ($numrecs == 0) or $forceoverwrite) {
   
      # now, intialize the object and stash the data
      $thisobject->init();
      
      //print($thisobject->errorstring . "<br>\n");

      $rovol = 'OUT - WDM4 111 WATR RCHRES 1 OFLOW';
      $ivol = 'IN - WDM4 11 WATR RCHRES 1 INFLOW';

      //print("DSN names:<br>\n" . print_r(array_keys($thisobject->wdm_dsns), 1) . "<br>\n");
      $ivolid = $thisobject->wdm_dsns[$ivol]['id'];
      $ivoldsn = $thisobject->wdm_dsns[$ivol]['dsn'];
      print(" IVOL object id = $ivolid<br>\n");
      $wdmobject = $thisobject->wdm_files[$ivolid]['object'];
      print("WDM Base File: " . $wdmobject->filepath . "<br>\n");
      $ivolobject = $wdmobject->processors[$ivoldsn];
      $pb = 'RCHRES';
      $pg = 'INFLOW';
      $pn = 'IVOL';
      if (is_object($ivolobject)) {
         $cbp_listobject->querystring = " delete from cbp_scenario_output ";
         $cbp_listobject->querystring .= " where scenarioid = $scid ";
         $cbp_listobject->querystring .= " and thisdate >= '$starttime' ";
         $cbp_listobject->querystring .= " and thisdate <= '$endtime' ";
         $cbp_listobject->querystring .= " and location_id = $locid ";
         $cbp_listobject->querystring .= " and param_group = '$pg' ";
         $cbp_listobject->querystring .= " and param_name = '$pn' ";
         //print("$cbp_listobject->querystring ; <br>\n");
         $cbp_listobject->performQuery();
         $intable = $ivolobject->db_cache_name;

         $invals = count($ivolobject->tsvalues);
         if ($localdebug) {
            $cbp_listobject->querystring = " select * from $intable limit 3";
            print("<br>\n$cbp_listobject->querystring ; <br>\n");
            $cbp_listobject->performQuery();
            print_r($cbp_listobject->queryrecords);
            print("<br>\n");
         }
         print("table with IVOL data = $intable ($invals values)<br>\n");
         $cbp_listobject->querystring = " insert into cbp_scenario_output (scenarioid, thisdate, location_id, ";
         $cbp_listobject->querystring .= " param_block, param_group, param_name, thisvalue ) ";
         $cbp_listobject->querystring .= " select $scid, thistime, $locid, '$pb', '$pg', '$pn', thisvalue from $intable ";
         print("$cbp_listobject->querystring ; <br>\n");
         $cbp_listobject->performQuery();
         $cbp_listobject->querystring = " select count(*) as numrecs from cbp_scenario_output  ";
         $cbp_listobject->querystring .= " where scenarioid = $scid ";
         $cbp_listobject->querystring .= " and thisdate >= '$starttime' ";
         $cbp_listobject->querystring .= " and thisdate <= '$endtime' ";
         $cbp_listobject->querystring .= " and location_id = $locid ";
         $cbp_listobject->querystring .= " and param_group = '$pg' ";
         $cbp_listobject->querystring .= " and param_name = '$pn' ";
         print("$cbp_listobject->querystring ; <br>\n");
         $cbp_listobject->performQuery();
         $j = $cbp_listobject->getRecordValue(1,'numrecs');
         print_r($cbp_listobject->queryrecords);

         print("Inserted $j values for $pn on $riverseg <br>\n");


      } else {
         print("DSN $ivoldsn is not an object.");
      }


      $rovolid = $thisobject->wdm_dsns[$rovol]['id'];
      $rovoldsn = $thisobject->wdm_dsns[$rovol]['dsn'];
      print(" rovol object id = $rovolid<br>\n");
      $wdmobject = $thisobject->wdm_files[$rovolid]['object'];
      $rovolobject = $wdmobject->processors[$rovoldsn];
      $pb = 'RCHRES';
      $pg = 'OUTFLOW';
      $pn = 'ROVOL';
      if (is_object($rovolobject)) {
         $cbp_listobject->querystring = " delete from cbp_scenario_output ";
         $cbp_listobject->querystring .= " where scenarioid = $scid ";
         $cbp_listobject->querystring .= " and thisdate >= '$starttime' ";
         $cbp_listobject->querystring .= " and thisdate <= '$endtime' ";
         $cbp_listobject->querystring .= " and location_id = $locid ";
         $cbp_listobject->querystring .= " and param_group = '$pg' ";
         $cbp_listobject->querystring .= " and param_name = '$pn' ";
         //print("$cbp_listobject->querystring ; <br>\n");
         $cbp_listobject->performQuery();
         $intable = $rovolobject->db_cache_name;

         $invals = count($rovolobject->tsvalues);
         print("table with ROVOL data = $intable ($invals values)<br>\n");
         if ($localdebug) {
            $cbp_listobject->querystring = " select * from $intable limit 3";
            print("<br>\n$cbp_listobject->querystring ; <br>\n");
            $cbp_listobject->performQuery();
            print_r($cbp_listobject->queryrecords);
            print("<br>\n");
         }
         $cbp_listobject->querystring = " insert into cbp_scenario_output (scenarioid, thisdate, location_id, ";
         $cbp_listobject->querystring .= " param_block, param_group, param_name, thisvalue ) ";
         $cbp_listobject->querystring .= " select $scid, thistime, $locid, '$pb', '$pg', '$pn', thisvalue from $intable ";
         print("$cbp_listobject->querystring ; <br>\n");
         $cbp_listobject->performQuery();
         $cbp_listobject->querystring = " select count(*) from cbp_scenario_output  ";
         $cbp_listobject->querystring .= " where scenarioid = $scid ";
         $cbp_listobject->querystring .= " and thisdate >= '$starttime' ";
         $cbp_listobject->querystring .= " and thisdate <= '$endtime' ";
         $cbp_listobject->querystring .= " and location_id = $locid ";
         $cbp_listobject->querystring .= " and param_group = '$pg' ";
         $cbp_listobject->querystring .= " and param_name = '$pn' ";
         print("$cbp_listobject->querystring ; <br>\n");
         $cbp_listobject->performQuery();
         //print_r($cbp_listobject->queryrecords);
         $j = $cbp_listobject->getRecordValue(1,'numrecs');
         print("Inserted $j values for $pn on $riverseg <br>\n");


      } else {
         print("DSN $rovoldsn is not an object.");
      }

      $i++;
      if ($i > 2) {
         //break;
      }
      $thisobject->finish();
      $thisobject->cleanUp();
   }
   unset($thisobject);
}

?>
</body>

</html>
