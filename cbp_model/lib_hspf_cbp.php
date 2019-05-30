<?php

// CBP hspf functions

function getCBPScenarioInfo($scenarioname) {
   global $cbp_listobject;
   $scinfo = array();
   
   switch ($scenarioname) {
      case 'p5186':
         $scid = 1;
         $modelbase = '/opt/model/p518';
         $metbase = "$modelbase/wdm";
         $landbase = $modelbase;
         $wdms = array(
            1=>array('path'=>"$metbase/met/janstorm/"),
            2=>array('path'=>"$metbase/prad/ns611a902/"),
            4=>array('path'=>"$landbase/wdm/land/")
         );
		 $reachpath = "$modelbase/wdm/river/p5186/stream";
         $ucidir = "$modelbase/uci/river/p5186";
      break;

      case 'p52An':
         $scid = 2;
         $modelbase = '/opt/model/p52';
         $metbase = "$modelbase/input/scenario/climate";
         $landbase = "$modelbase/tmp";
         $wdms = array(
            1=>array('path'=>"$metbase/met/janstorm/"),
            2=>array('path'=>"$metbase/prad/ns611a902/"),
            4=>array('path'=>"$landbase/wdm/land/")
         );
         $reachpath = "$modelbase/tmp/wdm/river/p52An/stream";
         $ucidir = "$modelbase/tmp/uci/river/p52An";
      break;

      case 'p52icprb':
         $scid = 3;
         $modelbase = '/opt/model/p52icprb';
         $metbase = "$modelbase/input/scenario/climate";
         $landbase = "$modelbase/tmp";
         $wdms = array(
            1=>array('path'=>"$metbase/met/janstorm/"),
            2=>array('path'=>"$metbase/prad/ns611a902/"),
            4=>array('path'=>"$landbase/wdm/land/")
         );
         $reachpath = "$modelbase/tmp/wdm/river/p52An/stream";
         $ucidir = "$modelbase/tmp/uci/river/p52An";
      break;

      case 'p53cal':
         $scid = 4;
         $modelbase = "/opt/model/p53/p532c-sova";
         $metbase = "$modelbase/input/scenario/climate";
         $landbase = "$modelbase/tmp";
         $wdms = array(
            1=>array('path'=>"$metbase/met/js8405xyz/"),
            2=>array('path'=>"$metbase/prad/ns611a902/"),
            4=>array('path'=>"$landbase/wdm/land/")
         );
		 $reachpath = "$modelbase/tmp/wdm/river/p532sova/stream";
         $ucidir = "$modelbase/tmp/uci/river/p532sova";
      break;

      case 'p53sova':
         $scid = 4; // stores in same as p53cal since they are the same
         $modelbase = "/opt/model/p53/p532c-sova";
         $metbase = "$modelbase/input/scenario/climate";
         $landbase = "$modelbase/tmp";
         $wdms = array(
            1=>array('path'=>"$metbase/met/js8405xyz/"),
            2=>array('path'=>"$metbase/prad/ns611a902/"),
            4=>array('path'=>"$landbase/wdm/land/")
         );
		 $reachpath = "$modelbase/tmp/wdm/river/p532sova/stream";
         $ucidir = "$modelbase/tmp/uci/river/p532sova";
      break;

      default:
         $scid = 2;
         $modelbase = '/opt/model/p52';
         $metbase = "$modelbase/input/scenario/climate";
         $landbase = "$modelbase/tmp";
         $wdms = array(
            1=>array('path'=>"$metbase/met/janstorm/"),
            2=>array('path'=>"$metbase/prad/ns611a902/"),
            4=>array('path'=>"$landbase/wdm/land/")
         );
         $reachpath = "$modelbase/tmp/wdm/river/p52An/stream";
         $ucidir = "$modelbase/tmp/uci/river/p52An";
      break;

   }
   // get tyable specs for river routing
	$stab = getCBPSpatialTable($cbp_listobject, $scid);
	$scinfo['linkage_table'] = $stab['linkage_table'];
	$scinfo['linkage_column'] = $stab['linkage_column'];
	
   // set defaults here
   $scinfo['scid'] = $scid;
   $scinfo['modelbase'] = $modelbase;
   $scinfo['ucipath'] = $ucidir;
   $scinfo['metbase'] = $metbase;
   $scinfo['landbase'] = $landbase;
   $scinfo['reachpath'] = $reachpath;
   $scinfo['wdms'] = $wdms;
   $scinfo['dsn_names'] = array(
      '111'=>array('param_name'=>'SURO', 'param_block'=>'PERLND', 'param_group'=>'PWATER', 'wdm'=>4),
      '121'=>array('param_name'=>'SOSED', 'param_block'=>'PERLND', 'param_group'=>'SEDMNT', 'wdm'=>4),
      // begin - nitrogen reach outflow
      '142'=>array('param_name'=>'DNH3', 'param_block'=>'RIVER', 'param_group'=>'NITR', 'wdm'=>4),
      '144'=>array('param_name'=>'SNH3', 'param_block'=>'RIVER', 'param_group'=>'NITR', 'wdm'=>4),
      '244'=>array('param_name'=>'INH3', 'param_block'=>'RIVER', 'param_group'=>'NITR', 'wdm'=>4),
      '444'=>array('param_name'=>'ANH3', 'param_block'=>'RIVER', 'param_group'=>'NITR', 'wdm'=>4),
      '145'=>array('param_name'=>'SNO3', 'param_block'=>'RIVER', 'param_group'=>'NITR', 'wdm'=>4),
      '245'=>array('param_name'=>'INO3', 'param_block'=>'RIVER', 'param_group'=>'NITR', 'wdm'=>4),
      '445'=>array('param_name'=>'ANO3', 'param_block'=>'RIVER', 'param_group'=>'NITR', 'wdm'=>4),
      '141'=>array('param_name'=>'DLON', 'param_block'=>'RIVER', 'param_group'=>'NITR', 'wdm'=>4),
      '146'=>array('param_name'=>'SLON', 'param_block'=>'RIVER', 'param_group'=>'NITR', 'wdm'=>4),
      '246'=>array('param_name'=>'ILON', 'param_block'=>'RIVER', 'param_group'=>'NITR', 'wdm'=>4),
      '446'=>array('param_name'=>'ALON', 'param_block'=>'RIVER', 'param_group'=>'NITR', 'wdm'=>4),
      '143'=>array('param_name'=>'DRON', 'param_block'=>'RIVER', 'param_group'=>'NITR', 'wdm'=>4),
      '147'=>array('param_name'=>'SRON', 'param_block'=>'RIVER', 'param_group'=>'NITR', 'wdm'=>4),
      '247'=>array('param_name'=>'IRON', 'param_block'=>'RIVER', 'param_group'=>'NITR', 'wdm'=>4),
      '447'=>array('param_name'=>'ARON', 'param_block'=>'RIVER', 'param_group'=>'NITR', 'wdm'=>4),
      // end - nitrogen reach outflow
      '211'=>array('param_name'=>'IFWO', 'param_block'=>'PERLND', 'param_group'=>'PWATER', 'wdm'=>4),
      '411'=>array('param_name'=>'AGWO', 'param_block'=>'PERLND', 'param_group'=>'PWATER', 'wdm'=>4),
      '2000'=>array('param_name'=>'PREC', 'param_block'=>'PERLND', 'param_group'=>'EXTNL', 'wdm'=>2),
      '2001'=>array('param_name'=>'NO23', 'param_block'=>'PERLND', 'param_group'=>'NIADFX', 'wdm'=>2),
      '2002'=>array('param_name'=>'NH4A', 'param_block'=>'PERLND', 'param_group'=>'NIADFX', 'wdm'=>2),
      '2003'=>array('param_name'=>'NO3D', 'param_block'=>'PERLND', 'param_group'=>'NIADFX', 'wdm'=>2),
      '2004'=>array('param_name'=>'NH4D', 'param_block'=>'PERLND', 'param_group'=>'NIADFX', 'wdm'=>2),
      '1000'=>array('param_name'=>'PETINP', 'param_block'=>'PERLND', 'param_group'=>'EXTNL', 'wdm'=>1)
   );
   $scinfo['reach_dsn_names'] = array(
	  // river DSN stuff
	   '12'=>array('param_name'=>'IHEAT', 'param_block'=>'RCHRES', 'param_group'=>'INFLOW', 'wdm'=>4),
	   '112'=>array('param_name'=>'OHEAT', 'param_block'=>'RCHRES', 'param_group'=>'OUTFLOW', 'wdm'=>4),
	   '111'=>array('param_name'=>'OVOL', 'param_block'=>'RCHRES', 'param_group'=>'OUTFLOW', 'wdm'=>4),
	   '11'=>array('param_name'=>'IVOL', 'param_block'=>'RCHRES', 'param_group'=>'INFLOW', 'wdm'=>4),
	   '1000'=>array('param_name'=>'POTEV', 'param_block'=>'RCHRES', 'param_group'=>'EXTNL', 'wdm'=>1),
	   '2000'=>array('param_name'=>'PREC', 'param_block'=>'RCHRES', 'param_group'=>'EXTNL', 'wdm'=>2),
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
   
   return $scinfo;
}


function getModelLocation($dbobj, $scid, $id1, $id2, $id3, $id4, $create=1, $debug = 0) {
   
   $info = array();
   
   $dbobj->querystring = " select location_id from cbp_model_location ";
   $dbobj->querystring .= " where scenarioid = $scid ";
   $dbobj->querystring .= " and id1 = '$id1' ";
   $dbobj->querystring .= " and id2 = '$id2' ";
   $dbobj->querystring .= " and id3 = '$id3' ";
   $dbobj->querystring .= " and id4 = '$id4' ";
   if ($debug) {
      error_log("$dbobj->querystring ; <br>\n");
   }
   $dbobj->performQuery();
   if (count($dbobj->queryrecords) > 0) {
      $locid = $dbobj->getRecordValue(1,'location_id');
      $info['location_id'] = $locid;
      $info['status'] = 1;
      $info['inserted'] = 0;
   } else {
      # need to insert this location
      $dbobj->querystring = " insert into cbp_model_location (scenarioid, id1, id2, id3, id4 ) ";
      $dbobj->querystring .= " values ($scid, '$id1', '$id2', '$id3', '$id4' ) ";
      if ($debug) {
         error_log("$dbobj->querystring ; <br>\n");
      }
      $dbobj->performQuery();

      $dbobj->querystring = " select location_id from cbp_model_location ";
      $dbobj->querystring .= " where scenarioid = $scid ";
      $dbobj->querystring .= " and id1 = '$id1' ";
      $dbobj->querystring .= " and id2 = '$id2' ";
      $dbobj->querystring .= " and id3 = '$id3' ";
      $dbobj->querystring .= " and id4 = '$id4' ";
      if ($debug) {
         error_log("$dbobj->querystring ; <br>\n");
      }
      $dbobj->performQuery();
      if (count($dbobj->queryrecords) > 0) {
         $locid = $dbobj->getRecordValue(1,'location_id');
         $info['location_id'] = $locid;
         $info['status'] = 1;
         $info['inserted'] = 1;
      } else {
         $info['status'] = 0;
         $info['error_msg'] = "Could not insert $id1, $id2, $id3, $id4 . <br>\n";
         $info['inserted'] = 0;
      }
   }
   
   return $info;
}




function dumpLandData($formValues) {
   global $listobject, $basedir, $php_exe, $scenarioid, $serverip;
   // variable $formValues should contain:
   // cbpscenario - the scenario abbreviation of the data memeber in the wooomm CBP database
   //   options are - p5186, p52An, p52icprb, p53cal, p53sova
   // landseg - the cbp land segment
   // dsnid - the WDM data source number (DSN) of the desired data component
   //   the cbp wdms are configured such that even though there are multiple wdm;s, there are no duplicate
   //   DSN's, so a DSN number implicitly indicates a specific constituent AND a specific WDM
   $basedir = '/opt/model/p53/run/php';
   
   if (isset($formValues['cbpscenario'])) {
      $cbpscenario = $formValues['cbpscenario'];
      if (!isset($formValues['landseg'])) {
         $controlHTML .= "Land Segment Undefined, can not load data.";
         return $controlHTML;
      }
      $landseg = $formValues['landseg'];
      // the wdm data element ID for the desired constituent
      if (!isset($formValues['dsnid'])) {
         $controlHTML .= "Constituent WDM data ID Undefined, can not load data.";
         return $controlHTML;
      }
      $dsnid = $formValues['dsnid'];
      
      $command = "$php_exe -f $basedir/cbpdump_wdm-land.php $cbpscenario \"\" $landseg $dsnid ";
      $controlHTML .= "Spawning process for $elementid <br>\n";
      
      error_log("$command > /dev/null &");
      $forkout = exec( "$command > /dev/null &", $arrOutput );
      
      $controlHTML .= $command;
      //$forkout = launchBackgroundProcess($command);
      //$forkout = pclose(popen("start /b $command ", "r"));
      $controlHTML .= "Command result: " . $forkout;
      //$controlHTML .= print_r($arrOutput,1) . "<br>\n";
   } else {
      $controlHTML .= "Could not run element Scenario ID undefined .<br>\n";
   }
   
   return $controlHTML;
}
;
function importWDMData($cbp_listobject, $dsn_names, $wdmpath, $dsns, $scid, $landseg, $luname, $starttime = '1984-01-01', $endtime = '2005-12-31') {
   global $wdimex_exe, $tmpdir, $outdir, $wdm_messagefile;
   print("Found $wdmpath <br>\n");
   $cbp_listobject->init();
   $dt = 3600;

   print("Land Segment: $landseg, LU: $luname<br>\n");

   $newtimer = new simTimer;
   $newtimer->setStep($dt);
   $newtimer->setTime($starttime, $endtime);

   $wdm_obj = new HSPFWDM;
   $wdm_obj->setSimTimer( $newtimer);

error_reporting(E_ALL);
   $wdm_obj->filepath = $wdmpath;
   $wdm_obj->wdimex_exe = $wdimex_exe;
   $wdm_obj->max_memory_values = 1;
   $wdm_obj->debug = 0;
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
   print("$cbp_listobject->querystring ; <br>\n");
   $cbp_listobject->performQuery();
   if (count($cbp_listobject->queryrecords) > 0) {
      $locid = $cbp_listobject->getRecordValue(1,'location_id');
   } else {
      # need to insert this location
      $cbp_listobject->querystring = " insert into cbp_model_location (scenarioid, id1, id2, id3 ) ";
      $cbp_listobject->querystring .= " values ($scid, 'land', '$landseg', '$luname' ) ";
      print("$cbp_listobject->querystring ; <br>\n");
      $cbp_listobject->performQuery();

      $cbp_listobject->querystring = " select location_id from cbp_model_location ";
      $cbp_listobject->querystring .= " where scenarioid = $scid ";
      $cbp_listobject->querystring .= " and id1 = 'land' ";
      $cbp_listobject->querystring .= " and id2 = '$landseg' ";
      $cbp_listobject->querystring .= " and id3 = '$luname' ";
      print("$cbp_listobject->querystring ; <br>\n");
      $cbp_listobject->performQuery();
      if (count($cbp_listobject->queryrecords) > 0) {
         $locid = $cbp_listobject->getRecordValue(1,'location_id');
      } else {
         print("Could not insert 'river' named $landseg . <br>\n");
         //break;
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
   print("Location ID: $locid \n");

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
      if ( !(($numrecs == 0) or $forceoverwrite)) {
         print("$locid already contains Block: $pb, Group: $pg, Name: $pn -- overwrite NOT selected. Finding next. <br>\n");
      } else {
         # now, intialize the object and stash the data (only do this once)
         if ($wdm_initialized == 0) {
            print(" Initializing WDM <br>\n");
            $wdm_obj->init();
            $wdm_initialized = 1;
            if ($wdm_obj->debug) {
               print("Debugging " . $wdm_obj->debugstring . "\n");
            }
         }

         print(" DSN object id = $thisdsn<br>\n");
         if (isset($wdm_obj->processors[$thisdsn])) {
            $dsnobject = $wdm_obj->processors[$thisdsn];
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
               print("Inserted $invals values for $pn on $landseg <br>\n");

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
      //$wdm_obj->finish();
      //$wdm_obj->cleanUp();
   }
   unset($wdm_obj);
   // quit after one segment
   //break;   
}

function getExtSourceUCI($uciobject, $dsn) {
   // this should be moved to the uci object at some point, but for now it is OK
   // returns the ext-source info as well as the file info from the FILES block
  error_log("**** CALLING getExtSourceUCI(uciobject, $dsn)");
   $uciobject->listobject->querystring = " select * from \"$file_tbl\"  ";
   $uciobject->listobject->performQuery();
   error_log("Files: " . print_r($uciobject->listobject->queryrecords,1));
   $uciobject->listobject->querystring = " select * from \"$src_tbl\"   ";
   $uciobject->listobject->performQuery();
   error_log("Sources: " . print_r($uciobject->listobject->queryrecords,1));
   $src_tbl = $uciobject->ucitables['EXT SOURCES']['tablename'];
   $file_tbl = $uciobject->ucitables['FILES']['tablename'];
   $uciobject->listobject->querystring = " select a.*, b.* from \"$src_tbl\" as a, \"$file_tbl\" as b ";
   $uciobject->listobject->querystring .= " where a.recid = '$dsn' ";
   $uciobject->listobject->querystring .= " and a.wdmid = b.handle ";
   error_log($uciobject->listobject->querystring);
   $uciobject->listobject->performQuery();
   error_log("Recs: " . print_r($uciobject->listobject->queryrecords,1));
  if ($uciobject->listobject->numrows > 0) {
    return $uciobject->listobject->queryrecords[0];
  } else {
    return FALSE;
  }
}

function getCBPLocationID($cbp_listobject, $scid, $id1, $id2, $id3) {

   $cbp_listobject->querystring = " select location_id from cbp_model_location ";
   $cbp_listobject->querystring .= " where scenarioid = $scid ";
   $cbp_listobject->querystring .= " and id1 = '$id1' ";
   $cbp_listobject->querystring .= " and id2 = '$id2' ";
   $cbp_listobject->querystring .= " and id3 = '$id3' ";
   //print("$cbp_listobject->querystring ; <br>\n");
   $cbp_listobject->performQuery();
   if (count($cbp_listobject->queryrecords) > 0) {
      $locid = $cbp_listobject->getRecordValue(1,'location_id');
      return $locid;
   } else {
      error_log("Could not find scenarioid = $scid, id1 = '$id1', id2 = '$id2', id3 = '$id3' \n");
      return false;
   }
}

function importWDMData2($cbp_listobject, $dsn_names, $wdmpath, $dsns, $scid, $landseg, $luname, $starttime = '1984-01-01', $endtime = '2005-12-31') {
   global $wdimex_exe, $tmpdir, $outdir, $wdm_messagefile;
   error_log("Found $wdmpath <br>\n");
   $cbp_listobject->init();
   $dt = 3600;

   error_log("Land Segment: $landseg, LU: $luname<br>\n");

   $newtimer = new simTimer;
   $newtimer->setStep($dt);
   $newtimer->setTime($starttime, $endtime);

   $wdm_obj = new HSPFWDM;
   $wdm_obj->setSimTimer( $newtimer);

//error_reporting(E_ALL);
   $wdm_obj->filepath = $wdmpath;
   $wdm_obj->wdimex_exe = $wdimex_exe;
   $wdm_obj->max_memory_values = 1;
   $wdm_obj->debug = 0;
   $wdm_obj->name = $fp;
   $wdm_obj->tmpdir = $tmpdir;
   $wdm_obj->outdir = $outdir;
   $wdm_obj->wdm_messagefile = $wdm_messagefile;
   $wdm_obj->listobject = $cbp_listobject;
   $wdm_obj->setSimTimer($newtimer);
   //$wdm_obj->debug = 1;
   #error_log("Adding WDM Component $i");
   //$wdm_obj->setFormats();
   
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
         error_log("Could not insert 'river' named $landseg . <br>\n");
         //break;
      }
   }

   // activate the DSNs that we want to retrieve
   reset($dsns);
   foreach ($dsns as $thisdsn) {
      $wdm_obj->activateDSN($thisdsn);
   }
   reset($dsns);
   $wdm_initialized = 0;
   error_log("WDM File: $wdm_messagefile \n");
   error_log("Location ID: $locid \n");

   foreach ($dsns as $thisdsn) {
      $dsn_recs = $wdm_obj->loadDataSetMemory($thisdsn,'c');
      putDelimitedFile("loc$locid.txt",$dsn_recs[$thisdsn]['tsvalues'],"\t",1,'unix',0);
      //print_r(array_keys($dsn_recs[$thisdsn]));
      return "loc$locid.txt";
      die;
   
      $pb = $dsn_names[$thisdsn]['param_block'];
      $pg = $dsn_names[$thisdsn]['param_group'];
      $pn = $dsn_names[$thisdsn]['param_name'];       
      error_log("Loading: Block: $pb, Group: $pg, Name: $pn <br>\n");
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
      error_log("$numrecs records already in scenario data table for $locid <br>\n");
      if ( !(($numrecs == 0) or $forceoverwrite)) {
         error_log("$locid already contains Block: $pb, Group: $pg, Name: $pn -- overwrite NOT selected. Finding next. <br>\n");
      } else {
         # now, intialize the object and stash the data (only do this once)
         if ($wdm_initialized == 0) {
            error_log(" Initializing WDM <br>\n");
            $wdm_obj->init();
            $wdm_initialized = 1;
            if ($wdm_obj->debug) {
               error_log("Debugging " . $wdm_obj->debugstring . "\n");
            }
         }

         error_log(" DSN object id = $thisdsn<br>\n");
         if (isset($wdm_obj->processors[$thisdsn])) {
            $dsnobject = $wdm_obj->processors[$thisdsn];
            error_log("Loading $pb $pg $pb <br>\n");
            if (is_object($dsnobject)) {
               $cbp_listobject->querystring = " delete from cbp_scenario_output ";
               $cbp_listobject->querystring .= " where scenarioid = $scid ";
               $cbp_listobject->querystring .= " and thisdate >= '$starttime' ";
               $cbp_listobject->querystring .= " and thisdate <= '$endtime' ";
               $cbp_listobject->querystring .= " and location_id = $locid ";
               $cbp_listobject->querystring .= " and param_group = '$pg' ";
               $cbp_listobject->querystring .= " and param_name = '$pn' ";
               $cbp_listobject->querystring .= " and param_block = '$pb' ";
               //print("$cbp_listobject->querystring ; <br>\n");
               $cbp_listobject->performQuery();
               $intable = $dsnobject->db_cache_name;

               $invals = count($dsnobject->tsvalues);
               error_log("table with $thisdsn data = $intable ($invals values)<br>\n");
               $j =0;
               $cbp_listobject->querystring = " insert into cbp_scenario_output (scenarioid, thisdate, ";
               $cbp_listobject->querystring .= " location_id, param_block, param_group, param_name, ";
               $cbp_listobject->querystring .= " thisvalue ) ";
               $cbp_listobject->querystring .= " select $scid, thistime, $locid, '$pb', '$pg', '$pn', ";
               $cbp_listobject->querystring .= " thisvalue ";
               $cbp_listobject->querystring .= " from $intable ";
               error_log("$cbp_listobject->querystring ; <br>\n");
               $cbp_listobject->performQuery();
               error_log("Inserted $invals values for $pn on $landseg <br>\n");

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
               error_log("DSN $rovoldsn is not an object.");
            }
         }

         $i++;
         if ($i > 2) {
            //break;
         }
      }
      //$wdm_obj->finish();
      //$wdm_obj->cleanUp();
   }
   unset($wdm_obj);
   // quit after one segment
   //break;   
}


function importWDMData3($cbp_listobject, $dsn_names, $wdmpath, $dsns, $scid, $id1, $id2, $id3, $starttime = '1984-01-01', $endtime = '2005-12-31') {
   global $wdimex_exe, $tmpdir, $outdir, $wdm_messagefile;
   error_log("Found $wdmpath <br>\n");
   $cbp_listobject->init();
   $dt = 3600;

   error_log("ID1: $id1, ID2: $id2, ID3: $id3\n");

   $newtimer = new simTimer;
   $newtimer->setStep($dt);
   $newtimer->setTime($starttime, $endtime);

   $wdm_obj = new HSPFWDM;
   $wdm_obj->setSimTimer( $newtimer);

//error_reporting(E_ALL);
   $wdm_obj->filepath = $wdmpath;
   $wdm_obj->wdimex_exe = $wdimex_exe;
   $wdm_obj->max_memory_values = 1;
   $wdm_obj->debug = 0;
   $wdm_obj->debugmode = 1;
   $wdm_obj->name = $fp;
   $wdm_obj->tmpdir = $tmpdir;
   $wdm_obj->outdir = $outdir;
   $wdm_obj->wdm_messagefile = $wdm_messagefile;
   $wdm_obj->listobject = $cbp_listobject;
   $wdm_obj->setSimTimer($newtimer);
   //$wdm_obj->debug = 1;
   #error_log("Adding WDM Component $i");
   //$wdm_obj->setFormats();
   
   $cbp_listobject->querystring = " select location_id from cbp_model_location ";
   $cbp_listobject->querystring .= " where scenarioid = $scid ";
   $cbp_listobject->querystring .= " and id1 = '$id1' ";
   $cbp_listobject->querystring .= " and id2 = '$id2' ";
   $cbp_listobject->querystring .= " and id3 = '$id3' ";
   //print("$cbp_listobject->querystring ; <br>\n");
   $cbp_listobject->performQuery();
   if (count($cbp_listobject->queryrecords) > 0) {
      $locid = $cbp_listobject->getRecordValue(1,'location_id');
   } else {
      # need to insert this location
      $cbp_listobject->querystring = " insert into cbp_model_location (scenarioid, id1, id2, id3 ) ";
      $cbp_listobject->querystring .= " values ($scid, '$id1', '$id2', '$id3' ) ";
      //print("$cbp_listobject->querystring ; <br>\n");
      $cbp_listobject->performQuery();

      $cbp_listobject->querystring = " select location_id from cbp_model_location ";
      $cbp_listobject->querystring .= " where scenarioid = $scid ";
      $cbp_listobject->querystring .= " and id1 = 'land' ";
      $cbp_listobject->querystring .= " and id2 = '$id2' ";
      $cbp_listobject->querystring .= " and id3 = '$id3' ";
      //print("$cbp_listobject->querystring ; <br>\n");
      $cbp_listobject->performQuery();
      if (count($cbp_listobject->queryrecords) > 0) {
         $locid = $cbp_listobject->getRecordValue(1,'location_id');
      } else {
         error_log("Could not insert 'river' named $id2 . <br>\n");
         //break;
      }
   }

   // activate the DSNs that we want to retrieve
   reset($dsns);
   foreach ($dsns as $thisdsn) {
      $wdm_obj->activateDSN($thisdsn);
   }
   reset($dsns);
   $wdm_initialized = 0;
   error_log("WDM Message File: $wdm_messagefile \n");
   error_log("Location ID: $locid \n");

   foreach ($dsns as $thisdsn) {
     error_log("calling loadDataSetMemory($thisdsn,'c')");
      $dsn_recs = $wdm_obj->loadDataSetMemory($thisdsn,'c');
      putDelimitedFile("loc$locid.txt",$dsn_recs[$thisdsn]['tsvalues'],"\t",1,'unix',0);
      //print_r(array_keys($dsn_recs[$thisdsn]));
       error_log("Returning ID: $locid \n");
      return "loc$locid.txt";
      die;
   
      $pb = $dsn_names[$thisdsn]['param_block'];
      $pg = $dsn_names[$thisdsn]['param_group'];
      $pn = $dsn_names[$thisdsn]['param_name'];       
      error_log("Loading: Block: $pb, Group: $pg, Name: $pn <br>\n");
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
      error_log("$numrecs records already in scenario data table for $locid <br>\n");
      if ( !(($numrecs == 0) or $forceoverwrite)) {
         error_log("$locid already contains Block: $pb, Group: $pg, Name: $pn -- overwrite NOT selected. Finding next. <br>\n");
      } else {
         # now, intialize the object and stash the data (only do this once)
         if ($wdm_initialized == 0) {
            error_log(" Initializing WDM <br>\n");
            $wdm_obj->init();
            $wdm_initialized = 1;
            if ($wdm_obj->debug) {
               error_log("Debugging " . $wdm_obj->debugstring . "\n");
            }
         }

         error_log(" DSN object id = $thisdsn<br>\n");
         if (isset($wdm_obj->processors[$thisdsn])) {
            $dsnobject = $wdm_obj->processors[$thisdsn];
            error_log("Loading $pb $pg $pb <br>\n");
            if (is_object($dsnobject)) {
               $cbp_listobject->querystring = " delete from cbp_scenario_output ";
               $cbp_listobject->querystring .= " where scenarioid = $scid ";
               $cbp_listobject->querystring .= " and thisdate >= '$starttime' ";
               $cbp_listobject->querystring .= " and thisdate <= '$endtime' ";
               $cbp_listobject->querystring .= " and location_id = $locid ";
               $cbp_listobject->querystring .= " and param_group = '$pg' ";
               $cbp_listobject->querystring .= " and param_name = '$pn' ";
               $cbp_listobject->querystring .= " and param_block = '$pb' ";
               //print("$cbp_listobject->querystring ; <br>\n");
               $cbp_listobject->performQuery();
               $intable = $dsnobject->db_cache_name;

               $invals = count($dsnobject->tsvalues);
               error_log("table with $thisdsn data = $intable ($invals values)<br>\n");
               $j =0;
               $cbp_listobject->querystring = " insert into cbp_scenario_output (scenarioid, thisdate, ";
               $cbp_listobject->querystring .= " location_id, param_block, param_group, param_name, ";
               $cbp_listobject->querystring .= " thisvalue ) ";
               $cbp_listobject->querystring .= " select $scid, thistime, $locid, '$pb', '$pg', '$pn', ";
               $cbp_listobject->querystring .= " thisvalue ";
               $cbp_listobject->querystring .= " from $intable ";
               error_log("$cbp_listobject->querystring ; <br>\n");
               $cbp_listobject->performQuery();
               error_log("Inserted $invals values for $pn on $id2 <br>\n");

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
               error_log("DSN $rovoldsn is not an object.");
            }
         }

         $i++;
         if ($i > 2) {
            //break;
         }
      }
      //$wdm_obj->finish();
      //$wdm_obj->cleanUp();
   }
   unset($wdm_obj);
   // quit after one segment
   //break;   
}

function getCBPSpatialTable($cbp_listobject, $scid) {
   $cbp_listobject->querystring = " select linkage_table, linkage_column from cbp_scenario where scenarioid = $scid ";
   $cbp_listobject->performQuery();
   error_log("$cbp_listobject->querystring ; <br>\n");
   $linkage_table = $cbp_listobject->getRecordValue(1,'linkage_table');
   $linkage_column = $cbp_listobject->getRecordValue(1,'linkage_column');
   if ($cbp_listobject->numrows > 0) {
      return $cbp_listobject->queryrecords[0];
	} else {
	   return FALSE;
	}
}
?>
