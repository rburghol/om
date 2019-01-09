<?php


# set up db connection
#include('config.php');
$noajax = 1;
$projectid = 3;
$scid = 3;

include_once('xajax_modeling.element.php');
//include_once('config.php');
#include('qa_functions.php');
#include('ms_config.php');
/* also loads the following variables:
   $libpath - directory containing library files
   $indir - directory for files to be read
   $outdir - directory for files to be written
*/
error_reporting(E_NONE);
print("Un-serializing Model Object <br>\n");
$debug = 0;

$dt = 86400;
$starttime = '1984-01-01';
$use_tmpdb = 0; // whether or not to use the tmp db function of the WDM object for faster insert
$use_copyfile = 0;
//$endtime = '1984-01-02';
$endtime = '2005-12-31';
$scenarioname = 'p52An';
$forceoverwrite = 0; # whether or not to force a rerun of all UCI's, or just to do ones that are not in the db
// items of interest:
// WDM4 - SURO - 111 - surface runoff
// WDM4 - IFWO - 211 - interflow
// WDM4 - AGWO - 411 - groundwater

// this syntax will call a single Land segment, but with ALL land uses:
// php ~/www-html/wooommdev/test_cbpdump_wdm-land.php p52An '' A24043

if (isset($_GET['scenarioname'])) {
   $scenarioname = $_GET['scenarioname'];
}
if (isset($argv[1])) {
   $scenarioname = $argv[1];
}
if (isset($argv[2])) {
   $onelu = $argv[2];
   print("Single landuse requested: $onelu \n");
} else {
   $onelu = '';
}
if (isset($argv[3])) {
   $onelseg = $argv[3];
   print("Single land segment requested: $onelseg \n");
} else {
   $onelseg = '';
}
if (isset($argv[4])) {
   $onedsn = $argv[4];
   print("Single DSN requested: $onedsn \n");
} else {
   $onedsn = '';
}
if (isset($argv[5])) {
   $forceoverwrite = $argv[5];
   print("Over-write?: $forceoverwrite \n");
} else {
   $forceoverwrite = 0;
}
if (isset($argv[6])) {
   $copyfilename = $argv[6];
   print("Creating postgresql COPY file: $copyfilename \n");
   $use_copyfile = 1;
} else {
   $rnd = rand(1000,9999);
   $copyfilename = "$outdir/copyland_$rnd" . ".csv";
}
print_r($argv);

$perlnds = array('afo','for','hvf','hyo','lwm','nhi','nhy','npa','puh','trp','alf','ext','hom','hwm','hyw','nal','nho','nlo','pas','pul','urs');
$implnds = array( 'bar','imh','iml');

if (strlen($onedsn) > 0) {
   $dsns = array($onedsn);
} else {
   $dsns = array('111');
}
//$dsns = array('211','411');
// right now, for various reasons, only one DSN may be read at a time.  This is a problem from a time 
// stand point, but not that big a deal in the grand scheme of things
$dsn_names = array(
   '111'=>array('param_name'=>'SURO', 'param_block'=>'PERLND', 'param_group'=>'PWATER', 'wdm'=>3),
   '211'=>array('param_name'=>'IFWO', 'param_block'=>'PERLND', 'param_group'=>'PWATER', 'wdm'=>3),
   '411'=>array('param_name'=>'AGWO', 'param_block'=>'PERLND', 'param_group'=>'PWATER', 'wdm'=>3),
   '2000'=>array('param_name'=>'PREC', 'param_block'=>'PERLND', 'param_group'=>'EXTNL', 'wdm'=>2),
   '1000'=>array('param_name'=>'PETINP', 'param_block'=>'PERLND', 'param_group'=>'EXTNL', 'wdm'=>1)
);

switch ($scid) {
   case 2:
   $wdms = array(
      1=>array('path'=>'/opt/model/p52/input/scenario/climate/met/janstorm/'),
      2=>array('path'=>'/opt/model/p52/input/scenario/climate/prad/ns611a902/'),
      3=>array('path'=>'/opt/model/p52/tmp/wdm/land/')
   );
   break;
   
   case 3:
   $wdms = array(
      1=>array('path'=>'/opt/model/p52/input/scenario/climate/met/janstorm/'),
      2=>array('path'=>'/opt/model/p52/input/scenario/climate/prad/ns611a902/'),
      3=>array('path'=>'/opt/model/p52icprb/Land/')
   );
   break;
}

$path = "/opt/model/p52/tmp/wdm/land/";
# to do a single file, simply include its full name here
#$path = "/var/www/html/wooomm/dirs/proj3/components/cbp/PS2_5560_5100.uci";
// since we can only handle one DSN at a time, we can retrieve the custom path for the WDM
$wdm_no = $dsn_names[$dsns[0]]['wdm'];
$path = $wdms[$wdm_no]['path'];

# initilize cbp data connection
$cbp_connstring = "host=$dbip dbname=cbp user=$dbuser password=$dbpass ";
$cbp_dbconn = pg_connect($cbp_connstring, PGSQL_CONNECT_FORCE_NEW);
$cbp_listobject = new pgsql_QueryObject;
$cbp_listobject->ogis_compliant = 1;
$cbp_listobject->connstring = $cbp_connstring;
$cbp_listobject->dbconn = $cbp_dbconn;


# get list of files in uci directory
# iterate through list, using element 589 as the template, and simply setting the uciname to a new value and calling init()

if ( ($onelu == '') ) {
   $lunames = getFileArray($path,'');
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
      break;
      
      default:
      $dirpath = $path . "/$luname/$scenarioname";
      break;
   }
   
   print("Selected path $dirpath <br>\n");
   
   $files = getFileArray($dirpath,'wdm');
   print("Handling $luname <br>\n");
   $desiredfiles = array();
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
   foreach ($desiredfiles as $filename) {
      
      print("Handling $filename <br>\n");
      switch ($wdm_no) {
         case 3:
         $wdmpath = $path . "/$luname/$scenarioname/$filename";
         $landseg = substr($filename, 3,6);
         break;

         case 2:
         $wdmpath = $path . "/$filename";
         $landseg = substr($filename, 5,6);
         break;

         default:
         $wdmpath = $path . "/$luname/$scenarioname/$filename";
         $landseg = substr($filename, 3,6);
         break;
      }
      
      print("Found $wdmpath <br>\n");
      $cbp_listobject->init();

      
      print("Land Segment: $landseg, LU: $luname<br>\n");
      $newtimer = new simTimer;
      $newtimer->setStep($dt);
      $newtimer->setTime($starttime, $endtime);

      $wdm_obj = new HSPFWDM;
      $wdm_obj->setSimTimer( $newtimer);


      $wdm_obj->filepath = $wdmpath;
      $wdm_obj->wdimex_exe = $wdimex_exe;
      if ($use_tmpdb) {
         $wdm_obj->max_memory_values = 1;
      } else {
         $wdm_obj->max_memory_values = -1;
      }
      $wdm_obj->name = $fp;
      $wdm_obj->tmpdir = $tmpdir;
      $wdm_obj->outdir = $outdir;
      $wdm_obj->wdm_messagefile = $wdm_messagefile;
      $wdm_obj->listobject = $cbp_listobject;
      $wdm_obj->setSimTimer($newtimer);
      //$wdm_obj->debug = 1;
      #error_log("Adding WDM Component $i");


      $cbp_listobject->querystring = " select location_id from cbp_model_location ";
      $cbp_listobject->querystring .= " where scenarioid = $scid ";
      $cbp_listobject->querystring .= " and id1 = 'land' ";
      $cbp_listobject->querystring .= " and id2 = '$landseg' ";
      $cbp_listobject->querystring .= " and id3 = '$luname' ";
      //print("$cbp_listobject->querystring ; <br>\n");
      $cbp_listobject->performQuery();
      if (count($cbp_listobject->queryrecords) > 0) {
         $locid = $cbp_listobject->getRecordValue(1,'location_id');
      } else {
         # need to insert this location
         $cbp_listobject->querystring = " insert into cbp_model_location (scenarioid, id1, id2, id3 ) ";
         $cbp_listobject->querystring .= " values ($scid, 'land', '$landseg', '$luname' ) ";
         //print("$cbp_listobject->querystring ; <br>\n");
         $cbp_listobject->performQuery();

         $cbp_listobject->querystring = " select location_id from cbp_model_location ";
         $cbp_listobject->querystring .= " where scenarioid = $scid ";
         $cbp_listobject->querystring .= " and id1 = 'land' ";
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
      reset($dsns);
      foreach ($dsns as $thisdsn) {
         $wdm_obj->activateDSN($thisdsn);
      }
      reset($dsns);
      $wdm_initialized = 0;
      print("WDM File: $wdm_messagefile \n");
  
      foreach ($dsns as $thisdsn) {
         $pb = $dsn_names[$thisdsn]['param_block'];
         $pg = $dsn_names[$thisdsn]['param_group'];
         $pn = $dsn_names[$thisdsn]['param_name'];       
         print("Loading: Block: $pb, Group: $pg, Name: $pn <br>\n");
         # now, check to see if we want to overwrite, or ONLY add sites that have noe data
         # without checking for the period length, we are making aq big assumption that ALL data 
         # for a matching site is there, but that is OK for now.
         $cbp_listobject->querystring = "  select count(*) as numrecs from cbp_scenario_output  ";
         $cbp_listobject->querystring .= " where location_id = $locid ";
         $cbp_listobject->querystring .= "    and param_block = '$pb' ";
         $cbp_listobject->querystring .= "    and param_group = '$pg' ";
         $cbp_listobject->querystring .= "    and param_name = '$pn' ";
         $cbp_listobject->performQuery();
         if (count($cbp_listobject->queryrecords) > 0) {
            $numrecs = $cbp_listobject->getRecordValue(1,'numrecs');
         } else {
            $numrecs = 0;
         }
         print("$numrecs records already in scenario data table for $locid <br>\n");
         if ( ($numrecs == 0) or $forceoverwrite) {
            # now, intialize the object and stash the data (only do this once)
            if ($wdm_initialized == 0) {
               print(" Initializing WDM <br>\n");
               //$wdm_obj->listobject->debug = 1;
               $wdm_obj->init();
               $wdm_initialized = 1;
               if ($wdm_obj->debug) {
                  print("Debugging " . $wdm_obj->debugstring . "\n");
               }
               print("Table creation SQL for " . $wdm_obj->processors[$thisdsn]->name . ": " . $wdm_obj->processors[$thisdsn]->cache_create_sql . "\n");
               
               if (!$use_tmpdb) {
                  // OK, here we either opt to store the data in a permanent database (which avoids the problem with corrupted
                  // database catalogs when the backend dies during a long insert into a tmp table
                  // OR, we opt to create csv files that can then be used with a copy command
                  if ($use_copyfile) {
                     // scenarioid, thisdate, location_id, param_block, param_group, param_name, thisvalue 
                     $dsnobject = $wdm_obj->processors[$thisdsn];
                     foreach ($dsnobject->tsvalues as $thisrow) {
                        $tt = $thisrow['thistime'];
                        $tv = $thisrow['thisvalue'];
                        $ts[] = "$scid|$tt|$locid|$pb|$pg|$pn|$tv";
                        //print("$ts ; <br>\n");
                        $j++;
                     }
                     
                  } else {
                     // try to use a regular existing table - clear it first, however
                     print("Storing DSN data in permanent table <br>\n");
                     $scratch_tablename = 'tmp_wdm_timeseries_bin';
                     $cbp_listobject->querystring = " delete from $scratch_tablename ";
                     print("$cbp_listobject->querystring ; <br>\n");
                     $cbp_listobject->performQuery();
                     $cols = array('thistime','thisvalue');
                     $formats = array('thistime'=>'timestamp','thisvalue'=>'float8');
                     $cbp_listobject->array2Table($wdm_obj->processors[$thisdsn]->tsvalues, $scratch_tablename, $cols,  $formats, 1, 0, 1);
                  }
               }
            }
            
            print(" DSN object id = $thisdsn<br>\n");
            if (isset($wdm_obj->processors[$thisdsn])) {
               print("Loading $pb $pg $pb <br>\n");
               if (is_object($dsnobject)) {
                  $cbp_listobject->querystring = " delete from cbp_scenario_output ";
                  $cbp_listobject->querystring .= " where scenarioid = $scid ";
                  $cbp_listobject->querystring .= " and thisdate >= '$starttime' ";
                  $cbp_listobject->querystring .= " and thisdate <= '$endtime' ";
                  $cbp_listobject->querystring .= " and location_id = $locid ";
                  $cbp_listobject->querystring .= " and param_group = '$pg' ";
                  $cbp_listobject->querystring .= " and param_name = '$pn' ";
                  $cbp_listobject->querystring .= " and param_block = '$pb' ";
                  print("$cbp_listobject->querystring ; <br>\n");
                  $cbp_listobject->performQuery();
                  $intable = $dsnobject->db_cache_name;

                  $invals = count($dsnobject->tsvalues);
                  print("table with $thisdsn data = $intable ($invals values)<br>\n");
                  
                  if (!$use_tmpdb) {
                     if ($use_copyfile) {
                        putArrayToFilePlatform($copyfilename,$ts,0,'unix');
                        print("Outputting $j records to $copyfilename <br>\n");
                     } else {
                        // count
                        $cbp_listobject->querystring = " select count(*) as numrecs from $scratch_tablename ";
                        print("$cbp_listobject->querystring ; <br>\n");
                        $cbp_listobject->performQuery();
                        $num = $cbp_listobject->getRecordValue(1,'numrecs');
                        print("****************************************** <br>\n");
                        print("$num records in $scratch_tablename <br>\n");
                        print("****************************************** <br>\n");
                        // load records as individual inserts
                        // load records en masse from the tmp database
                        $cbp_listobject->querystring = " insert into cbp_scenario_output (scenarioid, thisdate, ";
                        $cbp_listobject->querystring .= " location_id, param_block, param_group, param_name, ";
                        $cbp_listobject->querystring .= " thisvalue ) ";
                        $cbp_listobject->querystring .= " select $scid, thistime, $locid, '$pb', '$pg', '$pn', ";
                        $cbp_listobject->querystring .= " thisvalue ";
                        $cbp_listobject->querystring .= " from $scratch_tablename ";
                        print("$cbp_listobject->querystring ; <br>\n");
                        $cbp_listobject->performQuery();
                     }
                  } else {
                     // load records en masse from the tmp database
                     $cbp_listobject->querystring = " insert into cbp_scenario_output (scenarioid, thisdate, ";
                     $cbp_listobject->querystring .= " location_id, param_block, param_group, param_name, ";
                     $cbp_listobject->querystring .= " thisvalue ) ";
                     $cbp_listobject->querystring .= " select $scid, thistime, $locid, '$pb', '$pg', '$pn', ";
                     $cbp_listobject->querystring .= " thisvalue ";
                     $cbp_listobject->querystring .= " from $intable ";
                     print("$cbp_listobject->querystring ; <br>\n");
                     $cbp_listobject->performQuery();
                     
                  }
                  print("Inserted $j values for $pn on $landseg <br>\n");
            
                  // make sure that an entry in the parameter name table is here so tht we can keep a catalog 
                  // of available data for the various data access routines
                  // this will eliminate the need todo massive indexing of data, and speed up any queries that 
                  // we have particlularly when we are polling the thigns via RSS to just try to determine what we 
                  // have available to us.  
                  $cbp_listobject->querystring = "  delete from cbp_scenario_param_name  ";
                  $cbp_listobject->querystring .= " where location_id = $locid ";
                  $cbp_listobject->querystring .= "    and param_block = '$pb' ";
                  $cbp_listobject->querystring .= "    and param_group = '$pg' ";
                  $cbp_listobject->querystring .= "    and param_name = '$pn' ";
                  $cbp_listobject->querystring .= "    and scenarioid = $scid ";
                  $cbp_listobject->performQuery();
                  // now inser the record of this transacation, later we could inlude a bunch opf contextual informatuion, 
                  // but for now we can just put the fact that this set of data is present, operhaps we owuld store things like the 
                  // the start date and end date of our data, and maybe the range of values?  i dunno.
                  $cbp_listobject->querystring = "  insert into cbp_scenario_param_name (scenarioid, location_id, param_name, param_block, param_group) values ($scid, $locid, '$pn', '$pb', '$pg' ) ";
                  $cbp_listobject->performQuery();
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
      // quit after one segment
      //break;   
   }
}
shell_exec('rm /tmp/error*');
if ($use_copyfile) {
   print("Import with command: 'COPY cbp_scenario_output ( scenarioid, thisdate, location_id, param_block, param_group, param_name, thisvalue ) FROM '$copyfilename' WITH DELIMITER AS '|' <br>\n");
}
session_destroy();
?>
