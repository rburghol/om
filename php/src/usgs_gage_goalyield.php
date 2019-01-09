<?php

// this script will perform a safe yield estimate by seeking to match the minimum storage required to obtain the desired 
// safe yield.  It wu

$noajax = 1;
$projectid = 3;
include_once('xajax_modeling.element.php');

$elementid = 240;
$resid = 239;
$gageid = 241;
$pct_flowby = 0.85;
$startdate = '2001-01-01';
$enddate = '2002-11-30';
$usgs = '01635500';
$area_ratio = 1.0; // used to scale stream flow to basin size
if (isset($argv[1])) {
   $usgs = $argv[1];
}

if ($usgs == -1) {

   $listobject->querystring = "  select station_nu from usgs_drainage_dd where station_nu like '0%' ";
   $listobject->performQuery();
   $gages = $listobject->queryrecords;
} else {
   $gages = array(
      array('station_nu'=>$usgs)
   );
}


foreach ($gages as $thisgage) {
   $usgs = $thisgage['station_nu'];
   print("Analyzing safe yields for $usgs <br>\n");
   

   $outdir = "./out";
   $ofile = "$outdir/safeyield.$usgs" . ".csv";
   $ofile_final = "$outdir/safeyield_final.$usgs" . ".csv";
   $colnames = array(0=>array('yield_goal_mgd','storage_mg','zerodays','mindays','pct_flowby'));
   putDelimitedFile("$ofile",$colnames,',',1,'unix');
   putDelimitedFile("$ofile_final",$colnames,',',1,'unix');

   # auto iterate through goal safe yields and storages
   # $safe_yields = array(250, 300, 350, 400, 450, 500, 550, 600);
   # initial guess on storage = 30 * target yield
   # if we have empty days, use the count in the worst year to estimate the 
   # number that we should increase the storage by
   # if we have NO zero days, then we want to take the minimum number of days remaining and 
   # use that to reduce the storage estimate, till we get within +3 days remaining as our tolerance

   //$yield_goals = array(5, 10, 20, 30, 40, 50, 75, 100, 125, 150, 200, 250, 300, 350);
   //$yield_goals = array(1,2,5,10);
   $yield_goals = array(10);
   $last_storage = -1;
   $last_yield = -1;
   $guess_factor = 1.0;

   foreach ($yield_goals as $yield_goal) {
      if ( ($last_storage > 0) and ($last_yield > 0)) {
         //we have gotten a storage from the last one, so use it as our basis
         $storage_guess = ($last_storage / $last_yield) * $yield_goal;
      } else {

         $storage_guess = $guess_factor * $yield_goal;
      }
      $i = 0;
      $tolerance = 30; # minimum number of days remaining to achieve our goal
      $mindays = 40; # tolerance needs to be less than initial values
      $zerodays = 1;
      $last_yield = $yield_goal;
      while ( (round($mindays) < (0.9*$tolerance)) or (round($mindays) > (1.1*$tolerance)) or ($zerodays > 0) ) {
         $listobject->init();
         $i++;
         unset($unserobjects);
         $thisobresult = unSerializeModelObject($elementid, array(), $modeldb);
         $thisobject = $thisobresult['object'];
          $thisobject->starttime = $startdate;
          $thisobject->endtime = $enddate;
         $components = $thisobresult['complist'];
         $thisname = $thisobject->name;
         $thisobject->outdir = $outdir;
         $thisobject->outurl = $outurl;
         $storage_guess_acft = 3.07 * $storage_guess;
         #$thisobject->cascadedebug = 1;
         #$thisobject->setDebug(1,2);
         # set the guess for storage
         error_log("Trying simulation with yield goal of $yield_goal and $storage_guess MG of storage.");
         // SET UP PARAMETERS OF THE SAFE YIELD, Gage (river), percent diversion allowed, and storage and yield goals
         $thisobject->components[$resid]->initstorage = $storage_guess_acft;
         $thisobject->components[$resid]->maxcapacity = $storage_guess_acft;
         $thisobject->components[$resid]->fulldepth = $storage_guess_acft * 0.1;
         $thisobject->components[$resid]->full_surface_area = $storage_guess_acft * 0.1;
         //$thisobject->components[$resid]->create();
         $thisobject->components[$resid]->processors['demand']->equation = $yield_goal;
         $thisobject->components[$resid]->processors['demand']->defaultval = $yield_goal;
         // set usgs gage and re-initialize
         $thisobject->components[$gageid]->staid = $usgs;
         //$thisobject->components[$gageid]->init();
         // not jsut yet, want to make sure that we get it right first.
         $thisobject->components[$resid]->processors['pct_flowby']->equation = $pct_flowby;
         $thisobject->components[$resid]->processors['pct_flowby']->defaultval = $pct_flowby;
         $thisobject->runModel();
         storeElementRunData($listobject, $elementid, $components, -1, date('r'), $startdate, $enddate, 0, 0);

         //$session_db->debug = 1;
         $st_info = loadSessionTable($thisobject->components[$resid], $resid, -1);
         error_log("Session table load: " . print_r($st_info,1));
         $dbt = $st_info['session_table'];
         $session_db->querystring = "  select count(*) as numrecs ";
         $session_db->querystring .= " FROM \"$dbt\" ";
         $session_db->performQuery();
         $numrecs = $session_db->getRecordValue(1,'numrecs');
         error_log("$numrecs records in data log table");
         //print("$numrecs records in data log table\n");

         $session_db->querystring = "  SELECT count(*) as alldays ";
         $session_db->querystring .= "       FROM \"$dbt\" ";
         $session_db->performQuery();
         $alldays = $session_db->getRecordValue(1,'alldays');
         error_log("$alldays days simulated\n");

         $session_db->querystring = "  SELECT \"year\", count(*) as zerodays ";
         $session_db->querystring .= "       FROM \"$dbt\" ";
         $session_db->querystring .= "       where days_remaining < 1 ";
         $session_db->querystring .= "       GROUP BY \"year\" ";
         $session_db->querystring .= "       ORDER BY count(*) ";
         $session_db->performQuery();
         $zerodays = $session_db->getRecordValue(1,'zerodays');
         if ( (strlen($zerodays) == 0) or ($session_db->numrows == 0) ) {
            $zerodays = 0;
         }
         error_log("$zerodays days at zero storage is worst yearly result<br>");
         print("$zerodays days at zero storage is worst yearly result<br>");

         $session_db->querystring = "  SELECT min(days_remaining) as mindays ";
         $session_db->querystring .= "       FROM \"$dbt\" ";
         $session_db->querystring .= " where thisdate > ('$startdate'::date + interval '1 days' ) ";
         $session_db->performQuery();
         $mindays = round(floatval($session_db->getRecordValue(1,'mindays')),2);
         error_log("$mindays is lowest number of days remaining at $storage_guess MG of storage");

         $outarr = array(0=>array($yield_goal,$storage_guess,$zerodays,$mindays,$pct_flowby));
         putDelimitedFile("$ofile",$outarr,',',0,'unix');

         $last_storage = $storage_guess;

         if ($zerodays > 0) {
            $storage_guess += $yield_goal * ($zerodays + $tolerance);
         } else {
            $storage_guess = $storage_guess - ($yield_goal * ($mindays - $tolerance));
         }
         
         // all done - clean up
         $thisobject->cleanUp();
         unset($thisobject);

         if ($storage_guess < 0) {
            # qualifies as a run of river (no storage needed), so move on to next yield goal
            error_log("qualifies as a run of river (no storage needed), so move on to next yield goal");
            break;
         }
      }
      $outarr = array(0=>array($yield_goal,$last_storage,$zerodays,$mindays,$pct_flowby));
      putDelimitedFile("$ofile_final",$outarr,',',0,'unix');

   }
}
?>
