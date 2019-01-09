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
if (isset($_GET['rate_type'])) {
   $rate_type = $_GET['rate_type'];
} else {
   $rate_type = 'compound';
}
if (isset($_GET['default_multiplier'])) {
   $default_multiplier = $_GET['default_multiplier'];
} else {
   $default_multiplier = NULL;
}
$startval=floatval($_GET['startval']);

if ($rate_type == 'fixed') {
   // add the end year to our table of rates
   $startvars[] = $endyear;
}
$years = array_unique($startvars);

$ratevars=explode(",",$_GET['rate']);
if ( ($rate == '') and !($default_multiplier === NULL) ) {
  // look for a default multiplier
  $rate_type = 'default_multiplier';
} else {
   $rate=floatval($ratevars[0]);
}

// set up rate-pairs table
foreach ($years as $index => $thisyear) {
   if (isset($ratevars[$index])) {
      $ratepairs[$thisyear] = $ratevars[$index];
   } else {
      $ratepairs[$thisyear] = $ratevars[count($ratevars) - 1];
   }
}

$lastval = $startval;
$baseval = $startval; // stash this here in case we have type fixed-multiyear
$numpoints = floatval($endyear - $startyear);

$data = array();
if ($debug) {
   error_log("Rate Pairs: " . print_r($ratepairs,1) . "<br>");
   error_log("Years: " . print_r($years,1) . "<br>");
}
// Create some data points
$j = 0;
for($i= $startyear ; $i<= $endyear; ++$i) {
   //$datay[$i]= round(($startval * pow((1 + $rate),($i - $startyear))),2);
   // if we requested a new rate at this year, get the new rate and make startval = lastval
   if (isset($ratepairs[$i])) {
      $rate = $ratepairs[$i];
      $startyear = $i; // reset the start year to allow flexible use of rate type within the multi-rate system
      if ($debug) {
         error_log("Found $i in rate pairs");
      }
      if ( ($rate_type == 'fixed') ) {
         $startval = $rate;
      } else {
         $startval = $lastval;
      }
   }       
   switch ($rate_type) {
      case 'compound':
      $lastval =  ($startval * pow((1 + $rate),($i - $startyear)));
      if ($debug) {
         error_log("Solving  $lastval =  round(($startval * pow((1 + $rate),($i - $startyear))),2) ");
      }
      break;
      
      case 'simple':
      $lastval =  ($startval + $startval * $rate * ($i - $startyear));
      break;
      
      case 'fixed':
      // we are given a table of values instead of rates and will interpolate between them
      // we will also scale these accoring to startval
      // the previous static value is always equal to startval
      // the next value should be grabbed from the ratepairs array
      $nextkey = array_search($startyear,$years) + 1;
      if ($nextkey > count($years)) {
         $nextkey = count($years) - 1;
      }
      $nextyear = $years[$nextkey];
      if (isset($ratepairs[$years[$nextkey]])) {
         $nextval = $ratepairs[$nextyear];
      } else {
         $nextval = $baseval;
      }
      $lastval = ($baseval / $ratevars[0]) * ($startval + ($nextval - $startval) * ( ($i - $startyear) / ($nextyear - $startyear) ));
      if ($debug) {
         error_log("Solving $lastval = ($baseval / $ratevars[0]) * ($startval + ($nextval - $startval) * ( ($i - $startyear) / ($nextyear - $startyear) )); ");
      }
      break;
      
      case 'default_multiplier':
      $lastval =  $startval + $startval * (($i - $startyear) / ($endyear - $startyear)) * ($default_multiplier - 1.0);
      break;
      
      default:
      $lastval =  ($startval * pow((1 + $rate),($i - $startyear)));
      break;
   }
   $datay[$j]= $lastval;
	$xdata[$j] = $i;
	$j++;
   //print(" floatval($startval * (1 + $rate)^($i - $startyear)) = " . $j . " <br>");
}
 //print_r($datay);
global $decs;

if ($lastval > 50.0) {
   $decs = 1;
} else {
   $decs = 2;
}
if ($lastval > 100.0) {
   $decs = 0;
}
if ($debug) {
   print("Decimal places: $decs <br>");
}
 
// A format callbakc function
function mycallback($l) {
   global $decs;
   $str = "%02.$decs" . "f";
   //error_log("Format String $str ");
    return sprintf($str,$l);
}
 
// Setup the basic parameters for the graph
$graph = new Graph(400,200);
$graph->SetScale("intlin");
$graph->SetShadow();
$graph->SetBox();
if (count($ratepairs) > 0) {
   switch($rate_type) {
      case 'fixed':
      $text = "Varying Rates";
      break;
      
      default:
      $text = implode(",", $ratepairs);
      break;
   } 
} else {
   $text = round((100.0 * $rate),2);
}
$graph->title->Set("Growth trend - $baseval @ " . $text . "%/yr");
$graph->title->SetFont(FF_FONT1,FS_BOLD);
 
// Set format callback for labels
$graph->yaxis->SetLabelFormatCallback("mycallback");
 
// Set X-axis at the minimum value of Y-axis (default will be at 0)
$graph->xaxis->SetPos("min");    // "min" will position the x-axis at the minimum value of the Y-axis
 
// Extend the margin for the labels on the Y-axis and reverse the direction
// of the ticks on the Y-axis
$graph->yaxis->SetLabelMargin(8);
$graph->xaxis->SetLabelMargin(6);
$graph->yaxis->SetTickSide(SIDE_LEFT);
$graph->xaxis->SetTickSide(SIDE_DOWN);
$graph->xaxis->SetTickLabels($xdata);
 
// Create a new impuls type scatter plot
$sp1 = new ScatterPlot($datay);
$sp1->mark->SetType(MARK_SQUARE);
$sp1->mark->SetFillColor("red");
//$sp1->SetImpuls();
$sp1->SetColor("blue");
$sp1->SetWeight(1);
$sp1->mark->SetWidth(3);
 
$graph->Add($sp1);
 
$graph->Stroke();
 
?>