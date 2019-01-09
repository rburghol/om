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

$dt = 86400;
$starttime = '1984-01-01';
$endtime = '2005-12-31';
$scenarioname = 'p52An';
$forceoverwrite = 0; # whether or not to force a rerun of all UCI's, or just to do ones that are not in the db
// items of interest:
// WDM4 - SURO - 111 - surface runoff
// WDM4 - IFWO - 211 - interflow
// WDM4 - AGWO - 411 - groundwater
$dsns = array('111','211','411');

if (isset($_GET['scenarioname'])) {
   $scenarioname = $_GET['scenarioname'];
}
if (isset($argv[1])) {
   $scenarioname = $argv[1];
}
if (isset($argv[2])) {
   $onelu = $argv[2];
} else {
   $onelu = '';
}

$perlnds = array('afo','','for','','hvf','','hyo','','lwm','','nhi','','nhy','','npa','','puh','','trp
alf','','ext','','hom','','hwm','','hyw','','nal','','nho','','nlo','','pas','','pul','urs');
$implnds = array( 'bar','imh','iml');
$dsn_names = array(
   '111'=>'SURO',
   '211'=>'IFWO',
   '411'=>'AGWO'
);

# initilize cbp data connection
$cbp_connstring = "host=$dbip dbname=cbp user=$dbuser password=$dbpass ";
$cbp_dbconn = pg_connect($cbp_connstring, PGSQL_CONNECT_FORCE_NEW);
$cbp_listobject = new pgsql_QueryObject;
$cbp_listobject->ogis_compliant = 1;
$cbp_listobject->connstring = $cbp_connstring;
$cbp_listobject->dbconn = $cbp_dbconn;


# get list of files in uci directory
# iterate through list, using element 589 as the template, and simply setting the uciname to a new value and calling init()
$path = "/opt/model/p52/tmp/wdm/land/";
# to do a single file, simply include its full name here
#$path = "/var/www/html/wooomm/dirs/proj3/components/cbp/PS2_5560_5100.uci";

if (strlen($onefile) == 0) {
   $lunames = getFileArray($path,'');
} else {
   $lunames = array($onelu);
}
$i = 0;
foreach ($lunames as $luname) {
   $dirpath = $path . "/$luname/$scenarioname";
   $files = getFileArray($dirpath,'wdm');
   print("Handling $luname <br>\n");
   foreach ($files as $filename) {
      print("Handling $filename <br>\n");
      $wdmpath = $path . "/$luname/$scenarioname/$filename";
      $listobject->init();

      $landseg = substr($filename, 3,6);
      print("Land Segment: $landseg, LU: $luname<br>\n");
      if (in_array($luname, $perlnds)) {
         $pb = 'PERLND';
         $pg = 'PWATER';
      } else {
         $pb = 'IMPLND';
         $pg = 'IWATER';
      }
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
      $wdm_obj->listobject = $listobject;
      $wdm_obj->setSimTimer($newtimer);
      #error_log("Adding WDM Component $i");


      $cbp_listobject->querystring = " select location_id from cbp_model_location ";
      $cbp_listobject->querystring .= " where scenarioid = $scid ";
      $cbp_listobject->querystring .= " and id1 = 'river' ";
      $cbp_listobject->querystring .= " and id2 = '$landseg' ";
      $cbp_listobject->querystring .= " and id3 = '$luname' ";
      //print("$cbp_listobject->querystring ; <br>\n");
      $cbp_listobject->performQuery();
      if (count($cbp_listobject->queryrecords) > 0) {
         $locid = $cbp_listobject->getRecordValue(1,'location_id');
      } else {
         # need to insert this location
         $cbp_listobject->querystring = " insert into cbp_model_location (scenarioid, id1, id2, id3 ) ";
         $cbp_listobject->querystring .= " values ($scid, 'river', '$landseg', '$luname' ) ";
         //print("$cbp_listobject->querystring ; <br>\n");
         $cbp_listobject->performQuery();

         $cbp_listobject->querystring = " select location_id from cbp_model_location ";
         $cbp_listobject->querystring .= " where scenarioid = $scid ";
         $cbp_listobject->querystring .= " and id1 = 'river' ";
         $cbp_listobject->querystring .= " and id2 = '$landseg' ";
         $cbp_listobject->querystring .= " and id3 = '$luname' ";
         //print("$cbp_listobject->querystring ; <br>\n");
         $cbp_listobject->performQuery();
         if (count($cbp_listobject->queryrecords) > 0) {
            $locid = $cbp_listobject->getRecordValue(1,'location_id');
         } else {
            print("Could not insert 'river' named $landseg . <br>\n");
            break;
         }
      }

      // activate the DSNs that we want to retrieve
      foreach ($dsns as $thisdsn) {
         $wdm_obj->activateDSN($thisdsn);
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
         $wdm_obj->init();
         
         foreach ($dsns as $thisdsn) {
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
                  $cbp_listobject->querystring .= " and param_name = '$pn' ";
                  //print("$cbp_listobject->querystring ; <br>\n");
                  $cbp_listobject->performQuery();
                  $intable = $dsnobject->db_cache_name;

                  $invals = count($dsnobject->tsvalues);
                  print("table with $thisdsn data = $intable ($invals values)<br>\n");
                  $listobject->querystring = "select thistime, thisvalue from $intable ";
                  //print("$listobject->querystring ; <br>\n");
                  $listobject->performQuery();
                  $listobject->show = 1;
                  //$listobject->showList();
                  $tvals = $listobject->queryrecords;
                  $j =0;
                  foreach ($tvals as $thist) {
                     $tt = $thist['thistime'];
                     $tv = $thist['thisvalue'];
                     $cbp_listobject->querystring = " insert into cbp_scenario_output (scenarioid, thisdate, location_id, ";
                     $cbp_listobject->querystring .= " param_block, param_group, param_name, thisvalue ) ";
                     $cbp_listobject->querystring .= " values ($scid, '$tt', $locid, '$pb', '$pg', '$pn', $tv) ";
                     //print("$cbp_listobject->querystring ; <br>\n");
                     $cbp_listobject->performQuery();
                     $j++;

                  }
                  print("Inserted $j values for $pn on $landseg <br>\n");


               } else {
                  print("DSN $rovoldsn is not an object.");
               }
            }

            $i++;
            if ($i > 2) {
               //break;
            }
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
