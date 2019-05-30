<html>
<body>
<h3>Test Model Run</h3>

<?php


# set up db connection
include('config.php');
$noajax = 1;
$projectid = 3;
$scid = 4;
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
//$endtime = '1984-01-04';
//$scenarioname = 'p52An';
$scenarioname = 'p53cal';
$forceoverwrite = 0; # whether or not to force a rerun of all UCI's, or just to do ones that are not in the db
// items of interest:
// WDM4 - SURO - 111 - surface runoff
// WDM4 - IFWO - 211 - interflow
// WDM4 - AGWO - 411 - groundwater
//$dsns = array('11','111','12','112');
//$dsns = array('12','112','2000');
$dsns = array('111','11');
//$dsns = array('2000');
$dsn_names = array(
   '2000'=>array('param_name'=>'PREC', 'param_block'=>'RCHRES', 'param_group'=>'EXTNL', 'wdm'=>2),
   '12'=>array('param_name'=>'IHEAT', 'param_block'=>'RCHRES', 'param_group'=>'INFLOW', 'wdm'=>4),
   '112'=>array('param_name'=>'OHEAT', 'param_block'=>'RCHRES', 'param_group'=>'OUTFLOW', 'wdm'=>4),
   '111'=>array('param_name'=>'OVOL', 'param_block'=>'RCHRES', 'param_group'=>'OUTFLOW', 'wdm'=>4),
   '11'=>array('param_name'=>'IVOL', 'param_block'=>'RCHRES', 'param_group'=>'INFLOW', 'wdm'=>4),
   '1000'=>array('param_name'=>'POTEV', 'param_block'=>'RCHRES', 'param_group'=>'EXTNL', 'wdm'=>1),
   '3000'=>array('param_name'=>'FLOW', 'param_block'=>'RCHRES', 'param_group'=>'INFLOW', 'wdm'=>3),
   '3001'=>array('param_name'=>'HEAT', 'param_block'=>'RCHRES', 'param_group'=>'INFLOW', 'wdm'=>3),
   '3002'=>array('param_name'=>'NH3X', 'param_block'=>'RCHRES', 'param_group'=>'INFLOW', 'wdm'=>3),
   '3003'=>array('param_name'=>'NO3X', 'param_block'=>'RCHRES', 'param_group'=>'INFLOW', 'wdm'=>3),
   '3004'=>array('param_name'=>'ORNX', 'param_block'=>'RCHRES', 'param_group'=>'INFLOW', 'wdm'=>3),
   '3005'=>array('param_name'=>'PO4X', 'param_block'=>'RCHRES', 'param_group'=>'INFLOW', 'wdm'=>3),
   '3006'=>array('param_name'=>'ORPX', 'param_block'=>'RCHRES', 'param_group'=>'INFLOW', 'wdm'=>3),
   '3007'=>array('param_name'=>'DIVR', 'param_block'=>'RCHRES', 'param_group'=>'EXTNL', 'wdm'=>3),
   '3008'=>array('param_name'=>'DIVA', 'param_block'=>'RCHRES', 'param_group'=>'EXTNL', 'wdm'=>3),
   '3021'=>array('param_name'=>'BODX', 'param_block'=>'RCHRES', 'param_group'=>'INFLOW', 'wdm'=>3),
   '3022'=>array('param_name'=>'TSSX', 'param_block'=>'RCHRES', 'param_group'=>'INFLOW', 'wdm'=>3),
   '3023'=>array('param_name'=>'DOXX', 'param_block'=>'RCHRES', 'param_group'=>'INFLOW', 'wdm'=>3),
   '3024'=>array('param_name'=>'TOCX', 'param_block'=>'RCHRES', 'param_group'=>'INFLOW', 'wdm'=>3),
   '3010'=>array('param_name'=>'SNO3', 'param_block'=>'RCHRES', 'param_group'=>'INFLOW', 'wdm'=>3),
   '3061'=>array('param_name'=>'SFAS', 'param_block'=>'RCHRES', 'param_group'=>'INFLOW', 'wdm'=>3),
   '3062'=>array('param_name'=>'SFAC', 'param_block'=>'RCHRES', 'param_group'=>'INFLOW', 'wdm'=>3)
);

if (count($argv) < 2) {
   print("Usage: php cbpdump_river_wdm reachid(4-digit) dsn [scenarioname] \n");
   die;
}

if (isset($_GET['basin_outlet'])) {
   $basin_outlet = $_GET['basin_outlet'];
}
if (isset($argv[1])) {
   $basin_outlet = $argv[1];
}
if (isset($argv[2])) {
   $dsns = array($argv[2]);
}
if (isset($argv[3])) {
   $scenarioname = $argv[3];
}

// *** should already be done in config.php
# initilize cbp data connection

if ( strlen($basin_outlet) == 0 ) {
   # get list of desired riversegs that are in the cbp_model_location table
   # iterate through list, check in the csv file for the matching prad wdm
   $cbp_listobject->querystring = " select location_id, id2 from cbp_model_location ";
   $cbp_listobject->querystring .= " where id1 = 'river' and id3 = '' and id4 = '' ";
   print("$cbp_listobject->querystring ; <br>\n");
   $cbp_listobject->performQuery();
   $riversegs = $cbp_listobject->queryrecords;
} else {
   $cbp_listobject->querystring = " select linkage_table, linkage_column from cbp_scenario where scenarioid = $scid ";
   $cbp_listobject->performQuery();
   print("$cbp_listobject->querystring ; <br>\n");
   $linkage_table = $cbp_listobject->getRecordValue(1,'linkage_table');
   $linkage_column = $cbp_listobject->getRecordValue(1,'linkage_column');
   $data = getCBPSegList($cbp_listobject, $linkage_table, $linkage_column, $basin_outlet, 1,-1);
   print(print_r($data,1) . "\n");
   //$data = getCBPSegList($cbp_listobject, 'sc_cbp5', 'catcode2', $basin_outlet, 1,-1);
   $segments = $data['segments'];
   $segnames = $data['segnames'];
   $seglist = "'" . join("','", $segnames) . "'";
   $cbp_listobject->querystring = "  select location_id, id2 from cbp_model_location ";
   $cbp_listobject->querystring .= " where id1 = 'river' ";
   $cbp_listobject->querystring .= "    and id2 in ($seglist) ";
   $cbp_listobject->querystring .= "    and (id3 = '' or id3 is null) ";
   $cbp_listobject->querystring .= "    and (id4 = '' or id4 is null) ";
   print("$cbp_listobject->querystring ; <br>\n");
   $cbp_listobject->performQuery();
   $riversegs = $cbp_listobject->queryrecords;
}

switch ($scenarioname) {
   case 'p53sova':
   // 5.3
   $modelbasedir = "/opt/model/p53/p532c-sova";
   $path = "$modelbasedir/tmp/wdm/river/p53so2va/stream";
   $ucidir = "$modelbasedir/tmp/uci/river/p532sova";
   break;
   
   case 'p53sova':
   // 5.3
   $modelbasedir = "/opt/model/p53/p532c-sova";
   $path = "$modelbasedir/tmp/wdm/river/p532sova/stream";
   $ucidir = "$modelbasedir/tmp/uci/river/p532sova";
   break;
   
   case 'p52An':
   // 5.2
   $modelbasedir = "/opt/model/p52";
   $path = "/opt/model/p52/tmp/wdm/river/p52An/stream";
   $ucidir = "/opt/model/p52/tmp/uci/river/p52An";
   break;
   
   case 'p5186':
   // 5.18
   $modelbasedir = "/opt/model/p18";
   $path = "/opt/model/p518/wdm/river/p5186/stream";
   $ucidir = "/opt/model/p518/uci/river/p5186";
   break;
   
   default:
   // 5.3
   $modelbasedir = "/opt/model/p53/p532c-sova";
   $path = "/opt/model/p53/tmp/wdm/river/p532sova/stream";
   $ucidir = "/opt/model/p53/tmp/uci/river/p532sova";
   break;
}
print_r($riversegs);


$i = 0;
foreach ($riversegs as $thisseg) {
   $locid = $thisseg['location_id'];
   $riverseg = $thisseg['id2'];
   $filename = $riverseg . ".wdm";
   $uciname = $riverseg . ".uci";
   $wdmpath = $path . "/$filename";
   $ucipath = $ucidir . "/$uciname";
   print($riverseg . " : " . $wdmpath . "\n");
   print($riverseg . " : " . $ucipath . "\n");
   $uciobject->ucidir = $ucidir;
   $uciobject->uciname = $uciname;
   $uciobject->init();
   //$precipwdm = 
   
   if (count($dsns) == 1) {
      switch ($dsns[0]) {
         case '2000':
         $listobject->querystring = "  select * from files where handle = 'WDM2' ";
         $listobject->performQuery();
         print($listobject->querystring. "\n");
         $listobject->showList();
         $uciobject->debug = 1;
         $wdm = ltrim($listobject->getRecordValue(1,'filepath'),'../');
         print("$wdm \n");
         $wdmpath = $modelbasedir . "/$wdm";
         print($riverseg . " : " . $wdmpath . "\n");
         break;
         
         case '1000':
         $listobject->querystring = "  select * from files where handle = 'WDM1' ";
         $listobject->performQuery();
         print($listobject->querystring. "\n");
         $listobject->showList();
         $uciobject->debug = 1;
         $wdm = ltrim($listobject->getRecordValue(1,'filepath'),'../');
         print("$wdm \n");
         $wdmpath = $modelbasedir . "/$wdm";
         print($riverseg . " : " . $wdmpath . "\n");
         break;
         
         case '3000':
         case '3001':
         case '3002':
         case '3003':
         case '3004':
         case '3005':
         case '3006':
         case '3007':
         case '3008':
         case '3010':
         case '3021':
         case '3022':
         case '3023':
         case '3024':
         case '3061':
         case '3062':
         $listobject->querystring = "  select * from files where handle = 'WDM3' ";
         $listobject->performQuery();
         print($listobject->querystring. "\n");
         $listobject->showList();
         $uciobject->debug = 1;
         $wdm = ltrim($listobject->getRecordValue(1,'filepath'),'../');
         print("$wdm \n");
         $wdmpath = $modelbasedir . "/tmp/wdm/river/$scenarioname/eos/$wdm";
         print($riverseg . " : " . $wdmpath . "\n");
         break;
      }
   }
   
   /*
   $uciobject->parseEXTSOURCES();
   $listobject->querystring = "  select * from extsources ";
   $listobject->performQuery();
   print($listobject->querystring. "\n");
   $listobject->showList();
   */
   //die;
   
   if (file_exists($wdmpath)) {
      print("Found $wdmpath <br>\n");
      # we have the wdm fr this, so proceed
      $cbp_listobject->init();
      
      $newtimer = new simTimer;
      $newtimer->setStep($dt);
      $newtimer->setTime($starttime, $endtime);

      $wdm_obj = new HSPFWDM;
      $wdm_obj->setSimTimer( $newtimer);


      $wdm_obj->filepath = $wdmpath;
      $wdm_obj->wdimex_exe = $wdimex_exe;
      $wdm_obj->max_memory_values = 1;
      $wdm_obj->name = $fp;
      $wdm_obj->tmpdir = $tmpdir;
      $wdm_obj->outdir = $outdir;
      $wdm_obj->wdm_messagefile = $wdm_messagefile;
      $wdm_obj->listobject = $cbp_listobject;
      $wdm_obj->setSimTimer($newtimer);
      error_log("Adding WDM Component $i");

      // activate the DSNs that we want to retrieve
      foreach ($dsns as $thisdsn) {
         $wdm_obj->activateDSN($thisdsn);
      }
      reset($dsns);
      $wdm_initialized = 0;

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
               $wdm_obj->init();
               $wdm_initialized = 1;
            }
  
            print(" DSN object id = $thisdsn<br>\n");
            if (isset($wdm_obj->processors[$thisdsn])) {
               $dsnobject = $wdm_obj->processors[$thisdsn];
               print("Loading $pb $pg $pn <br>\n");
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
                  $cbp_listobject->querystring .= "    and scenarioid = $scenarioid ";
                  $cbp_listobject->performQuery();
                  // now inser the record of this transacation, later we could inlude a bunch opf contextual informatuion, 
                  // but for now we can just put the fact that this set of data is present, operhaps we owuld store things like the 
                  // the start datae and end date of our data, and maybe the range of values?  i dunno.
                  $cbp_listobject->querystring = "  insert into cbp_scenario_param_name (scenarioid, location_id, param_name, param_block, param_group) values ($scenarioid, $locid, '$pn', '$pb', '$pg' ) ";
                  $cbp_listobject->performQuery();
               } else {
                  print("DSN $rovoldsn is not an object.");
               }
            }
         }

         $i++;
         if ($i > 2) {
            //break;
         }
      }
      $wdm_obj->finish();
      $wdm_obj->cleanUp();
      unset($wdm_obj);
      
   } else {
      print("Can not locate file: $wdmpath \n");
   }
}

?>
</body>

</html>
