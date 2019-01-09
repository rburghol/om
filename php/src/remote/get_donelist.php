<?php

include('./config.php');

$startdate = '1984-01-01';
//$enddate = '1984-12-31';
$enddate = '2005-12-31';
$cache_date = '2011-12-01';
$scid = 28;

if (isset($argv[3])) {
   $runid = $argv[3];
} else {
   $runid = 1;
}

if (isset($_GET['runid'])) {
   $runid = $_GET['runid'];
}
if (isset($_GET['scenarioid'])) {
   $scenid = $_GET['scenarioid'];
}
if (isset($_GET['rundate'])) {
   $cache_date = $_GET['rundate'];
}


$q = "  select b.elementid, a.elemname, a.custom2, b.run_date ";
$q .= " from scen_model_element as a, scen_model_run_elements as b ";
$q .= " where a.scenarioid = $scid ";
$q .= "    and a.objectclass = 'modelContainer' ";
$q .= "    and a.elementid = b.elementid ";
$q .= "    and b.run_date >= '$cache_date' ";
$q .= "    and b.runid = $runid ";
$q .= "    and b.run_verified = 1 ";
$q .= "  order by b.run_date ";
error_log("$q <br>");
$listobject->querystring .= $q;
$listobject->performQuery();
$outrecs = $listobject->queryrecords;

   $header = join(',', array_keys($outrecs[0]));
   print("$header\r\n");
   foreach ($outrecs as $thisrec) {
      $line = join(',', array_values($thisrec));
      print("$line\r\n");
   }

?>
