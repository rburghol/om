<?php

include('./config.php');
//require_once ('jpgraph/jpgraph.php');
// Ex - http://deq2.bse.vt.edu/wooommdev/remote/plot_growth_trend.php?startyear=2008&endyear=2060&rate=0.009&startval=2.71
require_once ("$libpath/jpgraph/jpgraph_scatter.php");
error_reporting(E_ALL);

// multiple start years may be entered, which will result in the ability to adjust the rate at each start year (multiple rates must be given)
$startvars=explode(",",$_GET['startyear']);
$startyear=floatval($startvars[0]);
if ( ($startyear == 0) or (trim($startyear) == '') ) {
   $startyear = 2010;
}
$debug=floatval($_GET['debug']);
$endyear=floatval($_GET['endyear']);

$years = array_unique($startvars);
if ($rate_type == 'fixed') {
   // add the end year to our table of rates
   $year[] = $endyear;
}

$ratevars=explode(",",$_GET['rate']);
$rate=floatval($ratevars[0]);

// set up rate-pairs table
foreach ($years as $index => $thisyear) {
   if (isset($ratevars[$index])) {
      $ratepairs[$thisyear] = $ratevars[$index];
   } else {
      $ratepairs[$thisyear] = $ratevars[count($ratevars) - 1];
   }
}

$startval=floatval($_GET['startval']);
$baseval = $startval; // stash this here in case we have type fixed-multiyear
$numpoints = floatval($endyear - $startyear);

$data = array();
if (isset($_GET['rate_type'])) {
   $rate_type = $_GET['rate_type'];
} else {
   $rate_type = 'compound';
}

// Create some data points
$j = 0;
for($i= $startyear ; $i<= $endyear; ++$i) {
   //$datay[$i]= round(($startval * pow((1 + $rate),($i - $startyear))),2);
   // if we requested a new rate at this year, get the new rate and make startval = lastval
   if (isset($ratepairs[$i])) {
      $rate = $ratepairs[$i];
      $startyear = $i; // reset the start year to allow flexible use of rate type within the multi-rate system
      $startval = $lastval;
   }       
   switch ($rate_type) {
      case 'compound':
      $lastval =  round(($startval * pow((1 + $rate),($i - $startyear))),2);
      break;
      
      case 'simple':
      $lastval =  round(($startval + $startval * $rate * ($i - $startyear)),2);
      break;
      
      case 'fixed':
      // we are given a table of values instead of rates and will interpolate between them
      // we will also scale these accoring to startval
      $nextval = 
      $lastval = ($baseval / $ratevars[0]) * $startval;
      break;
      
      default:
      $lastval =  round(($startval * pow((1 + $rate),($i - $startyear))),2);
      break;
   }
   $datay[$j]= $lastval;
	$xdata[$j] = $i;
	$j++;
   //print(" floatval($startval * (1 + $rate)^($i - $startyear)) = " . $j . " <br>");
}
 print $lastval;
?>