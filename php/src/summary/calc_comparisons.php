<?php

$noajax = 1;
include('./config.php');
include('./adminsetup.analysis.php');

$invars = $argv;

if (isset($_GET['elementid'])) {
   $invars = array();
   foreach ( array('elementid', 'runids', 'thismetric', 'tempres', 'startdate', 'enddate') as $thisvar) {
      if (isset($_GET[$thisvar])) {
         $invars[] = $_GET[$thisvar];
      } else {
         $invars[] = '';
      }
   }
}

if (count($invars) < 3) {
   print("This routine calculates the difference between two runs Qout monthly metrics and stores them in the table scen_model_run_data. \n");
   print("Usage: php calc_comparisons.php elementid runids (csv) [metric (=gini)] [temporal res (=daily)] [startdate] [enddate]' \n");
   die;
}

$elementid = $invars[1];
$runids = $invars[2];
$variables = 'Qout';
if (isset($invars[3])) {
   $thismetric = $invars[3];
} else {
   $thismetric = 'gini';
}
if (isset($invars[4])) {
   $tempres = $invars[4];
} else {
   $tempres = 'daily';
}
if (isset($invars[5])) {
   $startdate = $invars[5];
} else {
   $startdate = '';
}
if (isset($invars[6])) {
   $enddate = $invars[6];
} else {
   $enddate = '';
}
//error_reporting(E_ERROR);
// get the two desired runs
$result = compareRunData($elementid, $runids, $variables, $startdate, $enddate, 1, $debug);
//print("compareRunData: " . print_r($result,1) . " <br>\n");
//print("Error: " . $session_db->error . " <br>\n");
$graphout = cova_graphFlowComparison($result, $thismetric, 1, NULL, 2, $tempres);
print("Graphic Routine Output: " . print_r($graphout['data_records'],1) . "<br>\n");

foreach ($graphout['data_records'] as $thisrec) {
   $thismo = $thisrec['thismo'];
   foreach (split(",", $runids) as $thisrun) {
      $metric_val = $thisrec["Qout_$thisrun"];
      $listobject->querystring = " delete from scen_model_run_data   ";
      $listobject->querystring .= " where elementid = $elementid ";
      $listobject->querystring .= " and statname = '$thismetric_$thismo' ";
      $listobject->querystring .= " and temporal_res = '$tempres' ";
      if ( (strtolower($startdate) == 'null') or (strlen($startdate) == 0)) {
         $listobject->querystring .= " and starttime is null ";
      } else {
         $listobject->querystring .= " and starttime = '$tempres' ";
      }
      if ( (strtolower($enddate) == 'null') or (strlen($enddate) == 0)) {
         $listobject->querystring .= " and endtime is null ";
      } else {
         $listobject->querystring .= " and endtime = '$tempres' ";
      }
      $listobject->querystring .= " and runid = $thisrun  ";
      print("$listobject->querystring ; <br>\n");
      $listobject->performQuery();
      $listobject->querystring = " insert into scen_model_run_data  (elementid, statname, temporal_res, starttime, endtime, statval, runid ) ";
      $listobject->querystring .= " values ($elementid, '$thismetric_$thismo', '$tempres', ";
      if ( (strtolower($startdate) == 'null') or (strlen($startdate) == 0)) {
         $listobject->querystring .= "NULL, ";
      } else {
         $listobject->querystring .= "'$startdate', ";
      }
      if ( (strtolower($enddate) == 'null') or (strlen($enddate) == 0)) {
         $listobject->querystring .= "NULL,";
      } else {
         $listobject->querystring .= "'$enddate',";
      }
      $listobject->querystring .=  " $metric_val, $thisrun ) ";
      print("$listobject->querystring ; <br>\n");
      $listobject->performQuery();
   }
}

?>