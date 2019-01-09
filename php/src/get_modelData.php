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
   $variables = '';
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
if (isset($_GET['runid'])) {
   $runid = $_GET['runid'];
}
if (isset($_GET['startdate'])) {
   $startdate = $_GET['startdate'];
} 
if (isset($_GET['enddate'])) {
   $enddate = $_GET['enddate'];
} 
if (isset($_GET['convert'])) {
  $convert = $_GET['convert'];
  $conversions = array(
    'auglowflow' => array(
      21 => 'wsp_current_alf',
      22 => 'wsp_future_alf',
    ),
    '7q10' => array(
      21 => 'wsp_current_7q10',
      22 => 'wsp_future_7q10',
    ),
    'yr_2002_Qout_mean_mon10_mean' => array(
      21 => 'wsp_current_dor',
      22 => 'wsp_future_dor',
    ),
    'Qout_mean_mon09_pct10' => array(
      21 => 'wsp_current_w9w',
      22 => 'wsp_future_w9w',
    ),
  );
} else {
  $convert = FALSE;
  $conversions = array();
}

$result = compareRunData($elementid, $runid, $variables, $startdate, $enddate, $doquery = 1);
$query = $result['query'];
//print("$query\n");
print("$convert" . print_r($conversions,1));
if (count($result['records']) > 0) {
  $header = join(',', array_keys($result['records'][0]));
  print("$header\r\n");
  foreach ($result['records'] as $thisrec) {
    $runid = $thisrec['runid'];
    if ($convert) {
      $cname = $thisrec['dataname'];
      if (isset($conversions[$cname][$runid])) {
        $tname = $conversions[$cname][$runid];
        $thisrec['dataname'] = $tname;
      }
    }
    $line = join(',', array_values($thisrec));
    print("$line\r\n");
  } 
}
?>
