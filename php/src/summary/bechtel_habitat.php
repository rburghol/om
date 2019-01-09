<html>
<head>
<script language='JavaScript' src='/scripts/scripts.js'>");
</script>
<link href="/styles/clmenu.css" type="text/css" rel="stylesheet">
<link href="/styles/xajaxGrid.css" type="text/css" rel="stylesheet">
<?php
$noajax = 1;
include('./config.php');
$xajax->printJavascript("$liburl/xajax");
error_reporting(E_ERROR);
$runid = 0;
$scenarioid = 37;
print("</head><body>");
if (isset($_GET['elementid'])) {
   $elementid = $_GET['elementid'];
}

if (isset($_GET['metrics'])) {
   $metrics = $_GET['metrics'];
} else {
   $metrics = array('avg');
}
if (isset($_GET['runs'])) {
   $runs = $_GET['runs'];
} else {
   $runs = array(0,1,7,9);
}
// run ids / names
$allruns[] = array('option'=>'0', 'label'=>'Baseline');
$allruns[] = array('option'=>'1', 'label'=>'Historic (Bechtel)');
$allruns[] = array('option'=>'7', 'label'=>'Unit 3 (Bechtel)');
$allruns[] = array('option'=>'9', 'label'=>'Unit 3 (woooomm)');


$allmetrics[] = array('option'=>'r_quantile,0.01', 'label'=>'1st %ile');
$allmetrics[] = array('option'=>'r_quantile,0.05', 'label'=>'5th %ile');
$allmetrics[] = array('option'=>'r_quantile,0.1', 'label'=>'10th %ile');
$allmetrics[] = array('option'=>'median', 'label'=>'Median');
$allmetrics[] = array('option'=>'avg', 'label'=>'Mean');
if (isset($_GET['species'])) {
   $species = $_GET['species'];
} else {
   $species = array('shad_spawning');
}
$allspecies[] = array('option'=>'shad_spawning', 'label'=>'Shad, Spawning');
$allspecies[] = array('option'=>'shad_juvenile', 'label'=>'Shad, Juvenile');
$allspecies[] = array('option'=>'hogsucker_adult', 'label'=>'Northern Hogsucker, Adult');
$allspecies[] = array('option'=>'hogsucker_spawn', 'label'=>'Northern Hogsucker, Spawning');
$allspecies[] = array('option'=>'lampsilis_radiata', 'label'=>'Lampsilis Radiata');
$allspecies[] = array('option'=>'elliptio_complanata', 'label'=>'Elliptio Complanata');
print("<form action='$scriptname' method=GET>");
$run_select = showMultiCheckBox('runs', $allruns, $runs, ' | ', '', 1, 0);
$stat_select = showMultiCheckBox('metrics', $allmetrics, $metrics, ' | ', '', 1, 0);
$species_select = showMultiCheckBox('species', $allspecies, $species, ' | ', '', 1, 0);
print("<b>Select Species:</b> " . $species_select . "<br>");
print("<b>Select Habitat Suitability Statistics:</b> " . $stat_select . "<br>");
print("<b>Select Scenarios:</b> " . $run_select . "<br>");
showSubmitButton('submit',"Show Graphs", '', 0, 0);
print("<hr>");

// should replace this definition with a query to look for children that are HSI's
$hsi_element = 320815;

$elinfo = getElementInfo($listobject, $elementid);
if (isset($elinfo['scenarioid'])) {
   $scenarioid = $elinfo['scenarioid'];
}

//$debug = 1;
foreach ($runs as $thisrunid) {
   $rundata = retrieveRunSummary($listobject, $hsi_element, $thisrunid);
   //$taboutput->tab_HTML['model_runs'] .= print_r($rundata,1) . "<hr>";
   if (strlen(trim($rundata['run_summary'])) == 0) {
      $elname = getElementName($listobject, $elementid);
      $order = $rundata['order'];
      $status = $rundata['run_status'];
      $rundata['message'] = "No run info stored for $elname ($elementid) <br>\n";
      $rundata['message'] .= "Run Status: $status <br>\n";
      if ($debug) {
         $rundata['message'] .= "Query: " . $rundata['query'] . "<br>\n";
      }
   } else {
      $rundata['message'] = $rundata['run_summary'];
   }
}
$listobject->querystring = "  select runid from scen_model_run_elements where elementid = $hsi_element and runid in ( " . join(',', $runs) . ") ";
$listobject->performQuery();
foreach ($listobject->queryrecords as $rr) {
   $runids[] = $rr['runid'];
}

// BEGIN - Create Fllow Comparison Graphs
// print out graphical comparisons of flows
// show check boxes for runs to compare (to do later)
//$debug = 1;
//$runids = array(0,2);
$startdate = '1984-10-01';
$enddate = '2005-09-30';

foreach ($species as $this_species) {
if ($species == 'hogsucker_spawn') { $debug = 1; } else { $debug = 0;}
   // get the runids that actualy exist so as not to create bad graph objects
   //$runids = array(0,1);
   //print("$listobject->querystring ; <br>" . print_r($runids,1) . "<br>");
   $doquery = 1; // set to 0 to just assemble but not execute the query
   $variables = $this_species;
   $result = compareRunData($hsi_element, join(',',$runids), $variables, $startdate, $enddate, $doquery, $debug);
   // show 
   $legends = array();
   foreach ($allruns as $thisone) {
      $legends[$this_species . "_" . $thisone['option']] = $thisone['label'];
   }
   /*
   $legends[$this_species . "_0"] = 'Unaltered';
   $legends[$this_species . "_1"] = 'Historic';
   $legends[$this_species . "_6"] = '3-Tiered (20,30,40)';
   $legends[$this_species . "_5"] = '2-Tiered (20,40)';
   $legends[$this_species . "_8"] = 'WQ Target';
   */
   //$metrics = array('min', 'max', 'mean', 'median');
/*
   print($result['debug'] . "<br>");
   print($result['error'] . "<br>");
   print($result['query'] . "<br>");
*/
   //$debug = 1;
   $result['legends'] = $legends;
   $result['ylabel'] = 'Weighted Usable Area';
   $result['xlabel'] = 'Month';
   foreach ($metrics as $thismetric) { 
      $title = ucwords($thismetric) . ' ' . ucwords ($this_species);
      $graphout = cova_graphHabitatComparison($result, $thismetric, $debug, NULL, 2, 600, 400, $this_species . $thismetric, $title);
      $graphurl = $graphout['img_url'];
      if ($debug) {
         print($graphout['debug'] . "<br>");
      }
      print("<img src='$graphurl'>");
      print($graphout['data_table'] . "<br>");
   }


} // end species loop


?>
</body>
</html>
