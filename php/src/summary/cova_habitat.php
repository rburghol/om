<html>
<head>
<script language='JavaScript' src='/scripts/scripts.js'>");
</script>
<link href="/styles/clmenu.css" type="text/css" rel="stylesheet">
<link href="/styles/xajaxGrid.css" type="text/css" rel="stylesheet">
<?php
$noajax = 1;
$projectid=3;
include('../xajax_config.php');
include_once('../lib_verify.php');
include_once('./lib_cova_summary.php');
$xajax->printJavascript("$liburl/xajax");
//error_reporting(E_ALL);
$runid = 0;
$scenarioid = 37;
print("</head><body>");
if (isset($_GET['elementid'])) {
   $elementid = $_GET['elementid'];
   $formValues = $_GET;
}
if (isset($_GET['metrics'])) {
   $metrics = $_GET['metrics'];
} else {
   $metrics = array('median');
}
if (isset($_GET['internal_metrics'])) {
   $internal_metrics = $_GET['internal_metrics'];
} else {
   $internal_metrics = 'median';
}

if (isset($_GET['hsivar'])) {
   $hsivar = $_GET['hsivar'];
} else {
   $hsivar = array();
}
if (isset($_GET['xopt'])) {
   $xopt = $_GET['xopt'];
} else {
   $xopt = 'month';
}
if (isset($_GET['runs'])) {
   $runs = $_GET['runs'];
} else {
   $runs = array();
}
if (isset($_GET['startdate'])) {
   $startdate = $_GET['startdate'];
} else {
   $startdate = '1984-10-01';
}
if (isset($_GET['enddate'])) {
   $enddate = $_GET['enddate'];
} else {
   $enddate = '2005-09-30';
}
if (isset($_GET['Qmin'])) {
   $Qmin = $_GET['Qmin'];
} else {
   $Qmin = 20;
}
if (isset($_GET['Qmax'])) {
   $Qmax = $_GET['Qmax'];
} else {
   $Qmax = 700;
}
//print_r($_GET);

$allruns = array();
// START - AUTO RUN SETUP
// define runs based on Data base query
$runlist = getModelRunList($listobject, $elementid, -1);
foreach ($runlist as $thisrun) {
   $allruns[] = array('option'=>$thisrun['runid'], 'label'=>"Run " . $thisrun['runid']);
}
asort($allruns);
// DONE - AUTO RUN SETUP

/*
// START - MANUAL RUN SETUP
// define runs based on manual setup
// run ids / names
$allruns[] = array('option'=>'0', 'label'=>'Baseline');
$allruns[] = array('option'=>'1', 'label'=>'Historic');
//$allruns[] = array('option'=>'6', 'label'=>'3-Tier 20-30-40');
$allruns[] = array('option'=>'5', 'label'=>'2-Tier 20-30 (W)');
$allruns[] = array('option'=>'7', 'label'=>'2-Tier 20-30 (B)');
$allruns[] = array('option'=>'8', 'label'=>'WQ Target (rec)');
$allruns[] = array('option'=>'9', 'label'=>'WQ Target');
$allruns[] = array('option'=>'10', 'label'=>'WQ Target (May-June rec)');
// DONE - MANUAL RUN SETUP

*/


$allmetrics[] = array('option'=>'r_quantile,0.01', 'label'=>'1st %ile');
$allmetrics[] = array('option'=>'r_quantile,0.05', 'label'=>'5th %ile');
$allmetrics[] = array('option'=>'r_quantile,0.1', 'label'=>'10th %ile');
$allmetrics[] = array('option'=>'r_quantile,0.25', 'label'=>'25th %ile');
$allmetrics[] = array('option'=>'r_quantile,0.75', 'label'=>'75th %ile');
$allmetrics[] = array('option'=>'r_quantile,0.9', 'label'=>'90th %ile');
$allmetrics[] = array('option'=>'min', 'label'=>'Minimum');
$allmetrics[] = array('option'=>'median', 'label'=>'Median');
$allmetrics[] = array('option'=>'max', 'label'=>'Maximum');
$allmetrics[] = array('option'=>'avg', 'label'=>'Mean');
$allmetrics[] = array('option'=>'duration', 'label'=>'Duration Plot');
$allmetrics[] = array('option'=>'habitat_duration', 'label'=>'Habitat Duration Plot');
$allmetrics[] = array('option'=>'timeseries', 'label'=>'Timer Series');

// instantiate the object of interest
//print("Unserializing $elementid <br>");
$unres = unSerializeSingleModelObject($elementid);
$thisobject = $unres['object'];
 // get the possible variables that hold the WUA info
$subcomps = $thisobject->getPublicVars();
$inputs = $thisobject->getPublicInputs();
//print("Sub-Comps: " . print_r($subcomps,1) . "<br>");
// set up a where query for restricting conditions - UNDER DEVELOPMENT
//$qwiz = showAnalysisQueryWizard (array('elementid'=>$elementid), $session_table, $form_name, $mode = 'xajax')
//$whereform = $thisobject->showWhereFields('hsiform', 0);

$defvar = $subcomps[min(array_keys($subcomps))];
$varoptions = array();
$foundvar = 0;
$all_vars = array();
foreach ($subcomps as $thiscomp) {
   $varoptions[] = array('hsivar' => $thiscomp);
   if ($hsivar == $thiscomp) {
      $foundvar = 1;
   }
   $all_vars[] = $thiscomp;
}
// allow the user to grain it by month or season
$xoptions = array();
$xoptions[] = array('xopt'=>'month');
$xoptions[] = array('xopt'=>'season');
foreach ($inputs as $thisin) {
   $varoptions[] = array('hsivar' => $thisin);
   if ($hsivar == $thisin) {
      $foundvar = 1;
   }
   $all_vars[] = $thisin;
}
// set the first var as the default if none is currently selected
if ( !$foundvar ) {
   $hsivar = $defvar;
}
$options_only = 0;
$varselect = showActiveList($varoptions, 'hsivar', 'hsivar', 'hsivar', 'hsivar', '' ,$hsivar, '', 'hsivar', 0, 1, 0, '', 0, $options_only);
$xselect = showActiveList($xoptions, 'xopt', 'xopt', 'xopt', 'xopt', '' ,$xopt, '', 'xopt', 0, 1, 0, '', 0, $options_only);

$allspecies = array();
// get the values of variable to plot
// BEGIN - automatically define options
if (isset($thisobject->processors[$hsivar])) {
  if (isset($thisobject->processors[$hsivar]->wvars)) {
	 foreach ($thisobject->processors[$hsivar]->wvars as $thisvar) {
		$opt = $thisobject->processors[$hsivar]->getParentVarName($thisvar);
		$allspecies[] = array('option'=>$opt, 'label'=>$opt);
		$all_vars[] = $opt;
	 }
  } 
} else {
   // try this - the var IS a subcomp, but does not have any values for "wvars"
   $varcolname = array($hsivar);
   $allspecies[] = array('option'=>$hsivar, 'label'=>$hsivar);
   $all_vars[] = $hsivar;
}

//print("Possible column headers" . print_r($varcolnames,1) . "<br>");
if (count($allspecies) == 0) {  
   $allspecies[] = array('option'=>$hsivar, 'label'=>$hsivar);
   $all_vars[] = $hsivar;
   $species = $hsivar;
}
if (isset($_GET['species'])) {
   $species = $_GET['species'];
   error_log("GET Var - Species set to $species");
}

if (count($allspecies) == 1) {  
   $species = array();
   $species[] = $allspecies[0]['option'];
   error_log("Auto-select Species from allspecies: Species set to $species");
}


// RESTRICTIONS

// criteria
// check for selected session table  -- if none are selected, we will iterate through each runid, looking for an existing table
// if pone of the table exists, we load its variable list, if not, we wait until the form is submitted
$session_table = '';
$tocheck = $runs;
// add in to check the last run if loaded
$tocheck[] = -1;
//print("Run tables to check: " . print_r($tocheck,1) . "<br>");
if (count($tocheck) > 0) {
   foreach ($tocheck as $thisrun) {
      //print("Checking table for element $elementid run $thisrun<br>");
      $tbl_info = checkSessionTable($thisobject, $elementid, $thisrun);
      //print("Table Info: " . print_r($tbl_info,1) . "<br>");
      if ($tbl_info['table_exists']) {
         $session_table = $tbl_info['tablename'];
         break;
      }
   }
}
if ($session_table <> '') {
   $wizard = showAnalysisQueryWizard ($formValues, $session_table, 'hsiform', 'post');
   $where_fields = $wizard['object']->showWhereFields('hsiform');
}
// Now, set restrictions based on submitted criteria, later in form will need to make sure that all restriction fields are in table join get
$restrictions = array();
if (isset($formValues['wcols'])) {
   foreach ($formValues['wcols'] as $wkey => $wcol) {
      $restrictions[$wcol][] = array('op'=> $formValues['wcols_op'][$wkey], 'val'=>$formValues['wcols_value'][$wkey]);
      print("Adding Restriction " . $wcol . " " . $formValues['wcols_op'][$wkey] . $formValues['wcols_value'][$wkey] . "<br>");
   }
}

/*
if (in_array('Qout', $all_vars)) {
   //$restrictions['Qin'][] = array('op'=>'>', 'val'=> $Qmin);
   //$restrictions['Qin'][] = array('op'=>'<', 'val'=> $Qmax);
   $restrictions['Qout'][] = array('op'=>'>=', 'val'=> $Qmin);
   $restrictions['Qout'][] = array('op'=>'<=', 'val'=> $Qmax);
}
*/
// END - RESITRCTIONS


print("<form action='$scriptname' method=GET id='hsiform' name='hsiform'>");
$run_select = showMultiCheckBox('runs', $allruns, $runs, ' | ', '', 1, 0);
$stat_select = showMultiCheckBox('metrics', $allmetrics, $metrics, ' | ', '', 1, 0);
$imetrics = $allmetrics;
array_unshift($imetrics, array('option'=>'none', 'label'=>'None') );

$internal_stat_select = showActiveList($imetrics, 'internal_metrics', '', 'label', 'option', '',$internal_metrics, '', 'label', $debug, 1);
$species_select = showMultiCheckBox('species', $allspecies, $species, ' | ', '', 1, 0);
print("<b>Select Variable:</b> " . $varselect . "<br>");
print("<b>Select Temporal Resolution:</b> " . $xselect . "<br>");
print("Start Date: <input type='date' name='startdate' value='$startdate'><br>");
print("End Date: <input type='date' name='enddate' value='$enddate'><br>");
print("<b>Select Sub-variable:</b> " . $species_select . "<br>");
print("<b>Stats:</b> <i>Format: return the EXTERNAL_STAT of the INTERNAL STAT values in the dataset, ex: return the MEDIAN of the MINIMUM monthly values in the dataset</i><br>");
print("&nbsp;&nbsp;&nbsp;<b>Select External Statistics:</b> " . $stat_select . "<br>");
print("&nbsp;&nbsp;&nbsp;<b>Select Internal Statistics:</b> " . $internal_stat_select . "<br>");
print("<b>Select Scenarios:</b> " . $run_select . "<br>");
print("<b>Criteria:</b><br>$where_fields <br>");
// end criteria
showHiddenField('elementid', $elementid, 0);
showHiddenField('loadquery', 0, 0);
showSubmitButton('submit',"Show Graphs", '', 0, 0);
print("<hr>");

// should replace this definition with a query to look for children that are HSI's
$hsi_element = $elementid;

$elinfo = getElementInfo($listobject, $elementid);
if (isset($elinfo['scenarioid'])) {
   $scenarioid = $elinfo['scenarioid'];
}
//$debug = 1;
foreach ($runs as $thisrunid) {
   print("Retrieving element $hsi_element, run $thisrunid <br>");
   $rundata = retrieveRunSummary($listobject, $hsi_element, $thisrunid);
   if ($debug) {
      print(print_r($rundata,1) . "<hr>");
   }
   if (strlen(trim($rundata['run_summary'])) == 0) {
      $elname = getElementName($listobject, $elementid);
      $order = $rundata['order'];
      $status = $rundata['run_status'];
      print("No run info stored for $elname ($elementid) <br>\n");
      print("Run Status: $status <br>\n");
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
print("Retrieved all runs - beginning statistical analysis and graphing<br>");
//sort($runids);

// BEGIN - Create Fllow Comparison Graphs
// print out graphical comparisons of flows
// show check boxes for runs to compare (to do later)
//$debug = 1;
//$runids = array(0,2);

foreach ($species as $this_species) {
   // get the runids that actualy exist so as not to create bad graph objects
   //$runids = array(0,1);
   print("$listobject->querystring ; <br>" . print_r($runids,1) . "<br>");
   $doquery = 1; // set to 0 to just assemble but not execute the query
   $variables = $this_species;
   //print("Getting element: $hsi_element - runs: " . join(',',$runids) . ", vars: $variables from $startdate to $enddate, $doquery, $debug <br>");
   $result = compareRunData($hsi_element, join(',',$runids), $variables, $startdate, $enddate, $doquery, $debug, $restrictions, 'inner');
   // show 
   if ($debug) {
      print(print_r($result,1) . "<br>");
      print(print_r($result['query'],1) . "<br>");
   }
   print(print_r($result['query'],1) . "<br>");
   $legends = array();
   foreach ($allruns as $thisone) {
      $legends[$this_species . "_" . $thisone['option']] = $thisone['label'];
   }
   $result['legends'] = $legends;
   $result['ylabel'] = $this_species;
   $result['xlabel'] = 'Month';
   $r_restrict = array();
   foreach ($result['valid_cols'] as $spec_col) {
      $r_restrict[$spec_col][] = array('op'=>'is not', 'val'=> 'NULL');
   }
   $uniqueid = $elementid . "_" . join('.',$runids);
   foreach ($metrics as $thismetric) { 
      $title = ucwords($thismetric) . ' of ' . ucwords($internal_metrics) . ' ' . ucwords ($this_species);
      if ($debug) {
         print("Restrict: " . print_r($r_restrict,1) . "<br>");
      }
      switch ($thismetric) {
         
         case 'timeseries':
            //$vars = "$variables";
            if ($variables == 'Qout') {
               $vars = "Qout";
               $number_of_axis = 1;
               error_log("Variables: $vars");
            } else {
               //$vars = "$variables,Qout";
               $vars = "$variables";
               $number_of_axis = 2;
               error_log("Variables: $vars");
            }
             foreach ($allruns as $thisone) {
               $legends["Qout_" . $thisone['option']] = $thisone['label'];
            }
            $Qresult['legends'] = $legends;
            $Qresult['ylabel'] = $vars;
            $Qresult['xlabel'] = 'Month';
            $Qresult = compareRunData($hsi_element, join(',',$runids), $vars, $startdate, $enddate, $doquery, $debug, $restrictions, 'inner');
            //print("Valid Columns:" . print_r($Qresult['valid_cols'],1) . "<br>");
            $graphout = cova_graphHabitatTimeSeries($Qresult, $debug, $r_restrict, 2, 800, 600, $this_species . $thismetric . "_$uniqueid", $title, $number_of_axis);
            //if ($debug) {
               print($graphout['query'] . "<br>");
               print($graphout['debug'] . "<br>");
            //}
         break;
         
         case 'duration':
            $vars = $variables;
            $Qresult = compareRunData($hsi_element, join(',',$runids), $vars, $startdate, $enddate, $doquery, $debug, $restrictions, 'inner');
             foreach ($allruns as $thisone) {
               $legends["Qout_" . $thisone['option']] = $thisone['label'];
            }
            $Qresult['legends'] = $legends;
            //print("Valid Columns:" . print_r($Qresult['valid_cols'],1) . "<br>");
            $graphout = cova_graphDuration($Qresult, $debug, $r_restrict, 2, 800, 600, "flow_duration" . "_$uniqueid",  "Flow Duration");
            // create a joined query, get the column names
            // add a duration plot subcomp to a duration object for each requested run
            // generate the plot
            if ($debug) {
               print($graphout['query'] . "<br>");
               print($graphout['debug'] . "<br>");
            }
         break;
         
         case 'habitat_duration':
            $graphout = cova_graphDuration($result, $debug, $r_restrict, 2, 800, 600, "$this_species" . "_habitat_duration" . "_$uniqueid", "$this_species Habitat Duration", 'linlin');
            // create a joined query, get the column names
            // add a duration plot subcomp to a duration object for each requested run
            // generate the plot
            if ($debug) {
               print($graphout['query'] . "<br>");
               print($graphout['debug'] . "<br>");
            }
         break;
         
         default:
            switch ($internal_metrics) {
               case 'none':   
                  $graphout = cova_graphHabitatComparison2($result, $thismetric, $debug, $r_restrict, 2, 800, 600, $this_species . $thismetric, $title, $xopt);
               //  print($graphout['query'] . "<br>");
               // print($graphout['debug'] . "<br>");
               break;
               default:
                  //print("Calling cova_graphStatOfStatComparison( result, $thismetric, $internal_function, $debug,  r_restrict, 2, 800, 600, $this_species . $thismetric, $title, $xopt); <br>");
                  $graphout = cova_graphStatOfStatComparison($result, $thismetric, $internal_metrics, $debug, $r_restrict, 2, 800, 600, $this_species . $thismetric . "_$uniqueid", $title, $xopt);
               break;
           }
            if ($debug) {
               print($graphout['query'] . "<br>");
               print($graphout['debug'] . "<br>");
            }
         break;
      }
      
      $graphurl = $graphout['img_url'];
      if ($debug) {
         print($graphout['query'] . "<br>");
      }
      print("<img src='$graphurl'>");
      print($graphout['data_table'] . "<br>");
   }


} // end species loop


?>
</body>
</html>
