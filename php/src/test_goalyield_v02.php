<?php

$noajax = 1;
$projectid = 3;
include_once('xajax_modeling.element.php');
//error_reporting(E_ALL);

# This version will seek to identify the safe yield for a system given a 
# an existing set of flow by rules, capacities and conditions, 
# all it does is modify a single yield target in the controlling model object


# Rivanna water and sewer authority
$elementid = 785;
if (isset($_GET['elementid'])) {
   $elementid = $_GET['elementid'];
}
if (isset($argv[1])) {
   $elementid = $argv[1];
}

# define functions for calculating new guess here, these are then customizable based on what 
# type of method you are usuing to quantify the difficulty, remaining storage (actual), remaining percent,
# max flow diversion, etc.
# $tolerance - the HIGH value of storage to permit (otherwise, it increases the yield)
# $threshold - the LOW value of storage to permit
# $yield_goal - the current desired yield guess
# $zerodays - the number of timesteps in which the min_storage < threshold
$threshold = 4.0; #ac-ft
$tolerance = 16.0; 

$yield_col = 'system_demand';
$yield_goal = 18.0; # initial value, this will be changd by the iterative nature of hte rotine
$storage_col = 'storage';
$warmupdate = '2002-08-01';

function increaseGuess($zerodays, $yield_goal, $min_storage, $threshold, $tolerance) {
   
   # for a percent storage remaining method
   //$new_goal = $yield_goal * (1.0 + 0.1 * ( $threshold / $min_storage ) );
   $new_goal = $yield_goal * 1.05;
   
   
   return $new_goal;
}

function decreaseGuess($zerodays, $yield_goal, $min_storage, $threshold, $tolerance) {
   if ($zerodays > 9) {
      $zerodays = 5;
   }
   //$new_goal = $yield_goal * (1.0 - 0.1 * $zerodays );
   $new_goal = $yield_goal * 0.95;
   return $new_goal;
}



$ofile = "$outdir/safeyield.$elementid.csv";
$colnames = array(0=>array('yield_goal','zerodays','min_storage'));
putDelimitedFile("$ofile",$colnames,',',1,'unix');

$i = 0;
$zerodays = 1;
$min_storage = 4.0; # tolerance needs initial values
$last_goal = $yield_goal;

print("Beginning Goal Seeking Routine on Model $elementid \n ");

while ( ($min_storage > $tolerance) or ($zerodays > 0) ) {
   $i++;
   $thisobresult = unSerializeModelObject($elementid);
   $thisobject = $thisobresult['object'];
   $thisname = $thisobject->name;
   $thisobject->outdir = $outdir;
   $thisobject->outurl = $outurl;
   #$thisobject->cascadedebug = 1;
   #$thisobject->setDebug(1,2);
   # set the guess for storage
   error_log("Trying simulation with yield goal of $yield_goal .<br>");
   print("Trying simulation with yield goal of $yield_goal .<br>");
   
   $thisobject->processors[$yield_col]->equation = $yield_goal;
   $thisobject->processors[$yield_col]->defaultval = $yield_goal;
   
   $dbt = $thisobject->dbtblname;
   
   $thisobject->runModel();


   $thisobject->listobject->show = 1;
   $thisobject->listobject->querystring = "  select count(*) as numrecs ";
   $thisobject->listobject->querystring .= " FROM $dbt ";
   $thisobject->listobject->performQuery();
   $numrecs = $thisobject->listobject->getRecordValue(1,'numrecs');
   print($thisobject->listobject->querystring . "\n");
   error_log("$numrecs records in data log table<br>");
   print("$numrecs days in model run<br>");

   $thisobject->listobject->querystring = "  SELECT max(zerodays) as zerodays ";
   $thisobject->listobject->querystring .= " FROM (select \"year\", count(*) as zerodays ";
   $thisobject->listobject->querystring .= "       FROM $dbt ";
   $thisobject->listobject->querystring .= "       where \"$storage_col\" < $threshold ";
   $thisobject->listobject->querystring .= "       and thisdate >= '$warmupdate' ";
   $thisobject->listobject->querystring .= "       GROUP BY \"year\" ";
   $thisobject->listobject->querystring .= " ) as foo ";
   print($thisobject->listobject->querystring . "\n");
   $thisobject->listobject->performQuery();
   print_r($thisobject->listobject->queryrecords);
   $zerodays = $thisobject->listobject->getRecordValue(1,'zerodays');
   if (strlen($zerodays) == 0) {
      $zerodays = 0;
   }
   error_log("$zerodays days at < $threshold storage is worst yearly result<br>");
   print("$zerodays days at < $threshold storage is worst yearly result<br>");

   $thisobject->listobject->querystring = "  SELECT min(\"$storage_col\") as min_storage, ";
   $thisobject->listobject->querystring .= " max(\"$storage_col\") as max_storage, ";
   $thisobject->listobject->querystring .= " avg(\"$storage_col\") as mean_storage ";
   $thisobject->listobject->querystring .= " FROM $dbt where thisdate >= '$warmupdate' ";
   print($thisobject->listobject->querystring . "\n");
   $thisobject->listobject->performQuery();
   $min_storage = round(floatval($thisobject->listobject->getRecordValue(1,'min_storage')),1);
   error_log("$min_storage is lowest amount remaining<br>");
   print("$min_storage remaining amount <br>");
   print_r($thisobject->listobject->queryrecords);

   $outarr = array(0=>array($yield_goal,$zerodays,$min_storage));
   putDelimitedFile("$ofile",$outarr,',',0,'unix');
   
   if ( ($min_storage >= $threshold) and ($min_storage <= $tolerance) ) {
      print("Model goal of $yield_goal within specified tolerance $threshold < $min_storage < $tolerance <br>");
      break;
   }
   
   if ($zerodays <= 0) {
      if ($last_goal > $yield_goal) {
         # we just decreased, so try to split the difference
         $new_goal = ($last_goal + $yield_goal)/2.0;
      } else {
         $new_goal = increaseGuess($zerodays, $yield_goal, $min_storage, $threshold, $tolerance);
      }
   } else {
      if ($last_goal < $yield_goal) {
         # we just increased, so try to split the difference
         $new_goal = ($last_goal + $yield_goal)/2.0;
      } else {
         $new_goal = decreaseGuess($zerodays, $yield_goal, $min_storage, $threshold, $tolerance);
      }
   }
   $last_goal = $yield_goal;
   $yield_goal = $new_goal;
   
   # stash this result in the table, in case it is the last result
   $modelHTML = generateTabModelOutput($thisobject, $projectid, $scenarioid, $elementid);
   $listobject->querystring = "update scen_model_element set output_cache = '" . addslashes($modelHTML) . "' where elementid = $elementid ";
   //print($listobject->querystring . "<hr>");
   $listobject->performQuery();
   
   print("Re-initializing the list object<br>");
   # clean up
   $thisobject->listobject->init();
   unset($thisobject);
   
//die;
   print("Beginning next iteration with new goal: $new_goal<br>");
   if ($i > 10) {
      print("Reached maximum numbe of iterations -- exiting.<br>");
      $yield_goal = $last_goal;
      break;
   }

}

print("Final stats: Yield goal: $yield_goal , Min Storage: $min_storage , Tolerance: $tolerance , Zero Days: $zerodays <br>\n");

?>