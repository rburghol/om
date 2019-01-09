<?php

include('./config.php');

$startdate = '1984-01-01';
//$enddate = '1984-12-31';
$enddate = '2005-12-31';
$cache_date = '2010-10-18 12:00:00';
// specify max models to run at a time
$max_simultaneous = 7;
$scid = 28;

if (isset($argv[1])) {
   $elementid = $argv[1];
} else {
   $elementid = -1;
}
if (isset($argv[2])) {
   $variables = $argv[2];
} else {
   $variables = 'Qout';
}
if (isset($argv[3])) {
   $runid = $argv[3];
} else {
   $runid = -1;
}
if (isset($argv[4])) {
   $startdate = $argv[4];
} else {
   $startdate = '';
}
if (isset($argv[5])) {
   $enddate = $argv[5];
} else {
   $enddate = '';
}

if (isset($_GET['elementid'])) {
   $elementid = $_GET['elementid'];
}
if (isset($_GET['variables'])) {
   $variables = $_GET['variables'];
}
if (isset($_GET['elementid'])) {
   $elemname = $_GET['elementid'];
   $operation = 2;
}
if (isset($_GET['scenarioid'])) {
   $scid = $_GET['scenarioid'];
}
if (isset($_GET['runid'])) {
   $runid = $_GET['runid'];
}
if (isset($_GET['startdate'])) {
   $startdate = $_GET['startdate'];
} 
if (isset($_GET['enddate'])) {
   $enddate = $_GET['enddate'];
} 
if (isset($_GET['format'])) {
   $format = $_GET['format'];
} else {
   $format = 'csv';
}
if (isset($_GET['debug'])) {
   $debug = $_GET['debug'];
} else {
   $debug = 0;
}
// use CBP segment name to select ID
if (isset($_GET['cbpsegment'])) {
   $cbpsegment = $_GET['cbpsegment'];
   $elementid = getCOVACBPContainer($listobject, $scid, $cbpsegment);
}
$doquery = 1;
if ($debug) {
   //$doquery = 0;
   error_reporting(E_ALL);
   print("Calling compareRunData($elementid, $runid, $variables, $startdate, $enddate, $doquery, $debug); <br>\n");
}
$result = compareRunData($elementid, $runid, $variables, "$startdate", "$enddate", $doquery, $debug);
$query = $result['query'];
if ($debug) {
   print("$query; <br>\n");
}
if (count($result['records']) > 0) {
   $outstring = '';
   switch ($format) {
      case 'nwis':
         $header = "#\r\n";
         $header .= "# U.S. Geological Survey\r\n";
         $header .= "# National Water Information System\r\n";
         $header .= "# Retrieved: 2001-07-02 15:08:57 EDT\r\n";
         $header .= "#\r\n";
         $header .= "# ---------------------WARNING---------------------\r\n";
         $header .= "# The data you have obtained from this automated\r\n";
         $header .= "# U.S. Geological Survey database have not received\r\n";
         $header .= "# Director's approval and as such are provisional\r\n";
         $header .= "# and subject to revision.  The data are released\r\n";
         $header .= "# on the condition that neither the USGS nor the\r\n";
         $header .= "# United States Government may be held liable for\r\n";
         $header .= "# any damages resulting from its use.\r\n";
         $header .= "#\r\n";
         $header .= "# This file contains published daily mean streamflow data.\r\n";
         $header .= "#\r\n";
         $header .= "# This information includes the following fields:\r\n";
         $header .= "#\r\n";
         $header .= "#  agency_cd   Agency Code\r\n";
         $header .= "#  site_no     USGS station number\r\n";
         $header .= "#  dv_dt       date of daily mean streamflow\r\n";
         $header .= "#  dv_va       daily mean streamflow value, in cubic-feet per-second\r\n";
         $header .= "#  dv_cd       daily mean streamflow value qualification code\r\n";
         $header .= "#\r\n";
         $header .= "# Sites in this file include:\r\n";
         $elname = getElementName($listobject, $elementid);
         $header .= "#  USGS " . str_pad($elementid,8,"X") . " $elname\r\n";
         $header .= "# \r\n";
         $header .= "# \r\n";
         $header .= "agency_cd   site_no  dv_dt dv_va dv_cd\r\n";
         $header .= "5s 15s   10d   12n   3s\r\n";
         $outstring .= $header;
         $runs = split(",", $runid);
         $first = $runs[0];
         foreach ($result['records'] as $thisrec) {
            $line = join("\t", array('USGS',str_pad($elementid,8,"X"), $thisrec['thisdate'], round($thisrec["Qout_$first"]), "") );
            $outstring .= "$line\r\n";
         }
      break;
      
      default:
         $header = join(',', array_keys($result['records'][0]));
         $outstring .= "$header\r\n";
         foreach ($result['records'] as $thisrec) {
            $line = join(',', array_values($thisrec));
            $outstring .= "$line\r\n";
         }
      break;
      
   }
   print ($outstring);
}
?>
