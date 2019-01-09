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
#include('ms_config.php');5
/* also loads the following variables:
   $libpath - directory containing library files
   $indir - directory for files to be read
   $outdir - directory for files to be written
*/
error_reporting(E_ERROR);
print("Un-serializing Model Object <br>\n");
$debug = 0;

$dt = 86400;
$starttime = '1984-01-01';
$endtime = '2005-12-31';
$scenarioname = 'p52An';
$forceoverwrite = 0; # whether or not to force a rerun of all UCI's, or just to do ones that are not in the db
// items of interest:
// WDM4 - SURO - 111 - surface runoff
// WDM4 - IFWO - 211 - interflow
// WDM4 - AGWO - 411 - groundwater
$dsns = array('2000');
$dsn_names = array(
   '2000'=>'PREC'
);

if (isset($_GET['basin_outlet'])) {
   $basin_outlet = $_GET['basin_outlet'];
}
if (isset($argv[1])) {
   $basin_outlet = $argv[1];
}

$mapfile = '/opt/model/p52/config/catalog/geo/p52/river_prad_wdm.csv';
$maparray = readDelimitedFile($mapfile,',', 1);
$r2wdm = array();
foreach($maparray as $thisline) {
   $r2wdm[$thisline['river']] = $thisline['wdm'];
}


# initilize cbp data connection
$cbp_connstring = "host=$dbip dbname=cbp user=$dbuser password=$dbpass ";
$cbp_dbconn = pg_connect($cbp_connstring, PGSQL_CONNECT_FORCE_NEW);
$cbp_listobject = new pgsql_QueryObject;
$cbp_listobject->ogis_compliant = 1;
$cbp_listobject->connstring = $cbp_connstring;
$cbp_listobject->dbconn = $cbp_dbconn;

if ( strlen($basin_outlet) == 0 ) {
   # get list of desired riversegs that are in the cbp_model_location table
   # iterate through list, check in the csv file for the matching prad wdm
   $cbp_listobject->querystring = " select location_id, id2 from cbp_model_location ";
   $cbp_listobject->querystring .= " where id1 = 'river' and id3 = '' and id4 = '' ";
   //print("$cbp_listobject->querystring ; <br>\n");
   $cbp_listobject->performQuery();
   $riversegs = $cbp_listobject->queryrecords;
} else {
   $data = getCBPSegList($listobject, 'sc_cbp5', 'catcode2', $basin_outlet, $debug,-1);
   $segments = $data['segments'];
   $segnames = $data['segnames'];
   $seglist = "'" . join("','", $segnames) . "'";
   $cbp_listobject->querystring = " select location_id, id2 from cbp_model_location ";
   $cbp_listobject->querystring .= " where id1 = 'river' and id2 in ($seglist) ";
   print("$cbp_listobject->querystring ; <br>\n");
   $cbp_listobject->performQuery();
   $riversegs = $cbp_listobject->queryrecords;
}

$path = "/opt/model/p52/input/scenario/climate/prad/ns611a902";
print_r($riversegs);

$i = 0;
foreach ($riversegs as $thisseg) {
   $locid = $thisseg['location_id'];
   $riverseg = $thisseg['id2'];
   if (isset($r2wdm[$riverseg])) {
      # we have the wdm fr this, so proceed
      $filename = $r2wdm[$riverseg];
      $wdmpath = $path . "/$filename";
      print($riverseg . " " . $wdmpath . "\n");
      //die;
      $cbp_listobject->init();
      
      $pb = 'RCHRES';
      $pg = 'EXTNL';

      print("Loading: Block: $pb, Group: $pg <br>\n");
      $newtimer = new simTimer;
      $newtimer->setStep($dt);
      $newtimer->setTime($starttime, $endtime);

      $wdm_obj = new HSPFWDM;
      $wdm_obj->setSimTimer( $newtimer);


      $wdm_obj->filepath = $wdmpath;
      $wdm_obj->wdimex_exe = $wdimex_exe;
      $wdm_obj->max_memory_values = $max_memory_values;
      $wdm_obj->max_memory_values = 1;
      $wdm_obj->name = $fp;
      $wdm_obj->tmpdir = $tmpdir;
      $wdm_obj->outdir = $outdir;
      $wdm_obj->wdm_messagefile = $wdm_messagefile;
      $wdm_obj->listobject = $cbp_listobject;
      $wdm_obj->setSimTimer($newtimer);
      #error_log("Adding WDM Component $i");

      // activate the DSNs that we want to retrieve
      foreach ($dsns as $thisdsn) {
         $wdm_obj->activateDSN($thisdsn);
      }

      # now, check to see if we want to overwrite, or ONLY add sites that have noe data
      # without checking for the period length, we are making aq big assumption that ALL data 
      # for a matching site is there, but that is OK for now.
      $cbp_listobject->querystring = "  select count(*) as numrecs from cbp_scenario_output  ";
      $cbp_listobject->querystring .= " where location_id = $locid ";
      $cbp_listobject->querystring .= "    and param_block = '$pb' ";
      $cbp_listobject->querystring .= "    and param_group = '$pg' ";
      $cbp_listobject->performQuery();
      if (count($cbp_listobject->queryrecords) > 0) {
         $numrecs = $cbp_listobject->getRecordValue(1,'numrecs');
      } else {
         $numrecs = 0;
      }
      print("$numrecs records already in scenario data table for $locid <br>\n");
      if ( ($numrecs == 0) or $forceoverwrite) {
         # now, intialize the object and stash the data
         $wdm_obj->init();

         foreach ($dsns as $thisdsn) {
            $pn = $dsn_names[$thisdsn];         
            print(" DSN object id = $thisdsn<br>\n");
            if (isset($wdm_obj->processors[$thisdsn])) {
               $dsnobject = $wdm_obj->processors[$thisdsn];
               $pn = $dsn_names[$thisdsn];
               print("Loading $pb $pg $pb <br>\n");
               if (is_object($dsnobject)) {
                  $cbp_listobject->querystring = " delete from cbp_scenario_output ";
                  $cbp_listobject->querystring .= " where scenarioid = $scid ";
                  $cbp_listobject->querystring .= " and thisdate >= '$starttime' ";
                  $cbp_listobject->querystring .= " and thisdate <= '$endtime' ";
                  $cbp_listobject->querystring .= " and location_id = $locid ";
                  $cbp_listobject->querystring .= " and param_group = '$pg' ";
                  $cbp_listobject->querystring .= " and param_block = '$pb' ";
                  $cbp_listobject->querystring .= " and param_name = '$pn' ";
                  //print("$cbp_listobject->querystring ; <br>\n");
                  $cbp_listobject->performQuery();
                  $intable = $dsnobject->db_cache_name;

                  $invals = count($dsnobject->tsvalues);
                  print("table with $thisdsn data = $intable ($invals values)<br>\n");
                  $j =0;
                  $cbp_listobject->querystring = " insert into cbp_scenario_output (scenarioid, thisdate, ";
                  $cbp_listobject->querystring .= " location_id, param_block, param_group, param_name, ";
                  $cbp_listobject->querystring .= " thisvalue ) ";
                  $cbp_listobject->querystring .= " select $scid, thistime, $locid, '$pb', '$pg', '$pn', ";
                  $cbp_listobject->querystring .= " thisvalue ";
                  $cbp_listobject->querystring .= " from $intable ";
                  print("$cbp_listobject->querystring ; <br>\n");
                  $cbp_listobject->performQuery();
                  print("Inserted $j values for $pn on $landseg <br>\n");
               } else {
                  print("DSN $rovoldsn is not an object.");
               }
            }
         }

         $i++;
         if ($i > 2) {
            //break;
         }
         $wdm_obj->finish();
         $wdm_obj->cleanUp();
      }
      unset($wdm_obj);
      
   }
}

?>
</body>

</html>
