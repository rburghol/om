<html>
<head>
<script language='JavaScript' src='/scripts/scripts.js'>");
</script>
<link href="/styles/clmenu.css" type="text/css" rel="stylesheet">
<link href="/styles/xajaxGrid.css" type="text/css" rel="stylesheet">
<?php
$noajax = 1;
include('./config.php');
//$xajax->printJavascript("$liburl/xajax");

$noajax = 0;
include_once("$basedir/xajax_modeling.common.php");
error_log("Loading XAJAX libs: $basedir/xajax_modeling.common.php");
if ($debug) {
   print("Loading xajax javascript: $liburl/xajax");
}
$xajax->printJavascript("$liburl/xajax");


error_reporting(E_ERROR);
$runid = 0;
$scenarioid = 37;
print("</head><body>");

// *******************
// ***   defaults ****
// *******************
$formvars = array(
   'geoscope' => 'local',
   'flow_analysis_pts' => array('reach_out'),
   'flow_analysis_metrics' => array('mean', 'median'),
   'wd_datasources' => array('vwuds')
);
$defaults = $formvars;
$elementid = -1;
$displayrun = 2;
$runid = 0;
$luname = 'for';
$geoscope = 'local';
$flow_analysis_metrics = array('mean', 'median');
$flow_analysis_pts = array('reach_out');
// *******************
// *** END defaults **
// *******************
include('./adminsetup.analysis.php');
// _GET Form
if (isset($argv[1])) {
   $elementid = $argv[1];
}
if (isset($argv[2])) {
   $displayrun = $argv[1];
} 
if (isset($_GET['elementid'])) {
   $elementid = $_GET['elementid'];
   foreach ($_GET as $key => $val) {
      $formvars[$key] = $val;
   }
   $elementid = $_GET['elementid'];
   $displayrun = $_GET['displayrun'];
   $runid = $_GET['runid'];
   $luname = $_GET['luname'];
   $geoscope = $_GET['geoscope'];
   $flow_analysis_metrics = $_GET['flow_analysis_metrics'];
   $flow_analysis_pts = $_GET['flow_analysis_pts'];
   $wd_datasources = $_GET['wd_datasources'];
   $quickid = $_GET['quickid'];
   $formvars = $_GET;
}
if (isset($_GET['geoscope'])) {
   $geoscope = $_GET['geoscope'];
}

// POST form
if (isset($_POST['elementid'])) {
   foreach ($_POST as $key => $val) {
      $formvars[$key] = $val;
   }
   $elementid = $_POST['elementid'];
   $displayrun = $_POST['displayrun'];
   $runid = $_POST['runid'];
   $luname = $_POST['luname'];
   $geoscope = $_POST['geoscope'];
   $flow_analysis_metrics = $_POST['flow_analysis_metrics'];
   $flow_analysis_pts = $_POST['flow_analysis_pts'];
   $wd_datasources = $_POST['wd_datasources'];
   $quickid = $_POST['quickid'];
   $formvars = $_POST;
}
foreach ($defaults as $key => $val) {
   if (!isset($formvars[$key])) {
      $formvars[$key] = $val;
   }
}
print_r($formvars);
$pairs = array(); 
foreach ($formvars as $key => $val) {
   if (!is_array($val)) {
      $pairs[] = $key . "=" . $val;
   } else {
      foreach ($val as $thisval) {
         $pairs[] = $key . "[]=" . $thisval;
      }
   }
}
$weblink = $scriptname . "?" . implode("&", $pairs);
   
$startdate = '1984-10-01';
$enddate = '2005-09-30';
if (isset($formvars['enddate'])) {
   $enddate = $formvars['enddate'];
}
if (isset($formvars['startdate'])) {
   $startdate = $formvars['startdate'];
}
if (isset($formvars['summary_resolution'])) {
   $summary_resolution = $formvars['summary_resolution'];
} else {
   $summary_resolution = 'monthly';
   $formvars['summary_resolution'] = $summary_resolution;
}

switch ($geoscope) {
   case 'local':
   $geotoggle = 'cumulative';
   break;

   default:
   $geotoggle = 'local';
   break;
}

$elinfo = getElementInfo($listobject, $elementid);
if (isset($elinfo['scenarioid'])) {
   $scenarioid = $elinfo['scenarioid'];
}

// SET UP types of containers to query up and downstream components
switch ($scenarioid) {
   case 28:
      $pcnt = array('custom1'=>array('vahydro_lite_container'));
      $mpc = 1;
      $ncnt = array('vahydro_lite_container');
      $ccnt = 'vahydro_lite_container';
      $uscont_id = $elementid;
      $trib_contid = -1;
   break;
   
   default:
      $pcnt = array('custom1'=>array('cova_ws_container'));
      $mpc = 2;
      $ncnt = array('cova_ws_container', 'cova_ws_subnodal');
      $ccnt = 'cova_ws_container';
      $uscont_id = getCOVAUpstream($listobject, $elementid, $debug);
      $trib_contid = getCOVATribs($listobject, $elementid);
   break;
}

$wkt_segs = getCOVASegments($elementid, $ncnt, $geoscope);
//print("getCOVASegments($elementid, $ncnt); = " . print_r($wkt_segs,1) . " <br>");
$wkt = getMergedCOVAShape($scenarioid, $listobject, $wkt_segs);

// get containing "downstream" object
$dsc = getContainingNodeType($elementid, 0, $pcnt, 10, $debug);
//print("getContainingNodeType($elementid, 0, " . print_r($pcnt,1) . ", $mpc, $debug);<br>");
$dsinfo = getElementInfo($listobject, $dsc);
$dsname = $dsinfo['elemname'];
// then get children of this container - upstream objects
//$ustribs = getChildComponentCustom1($listobject, $uscont_id, $ccnt, -1, $debug);
$ustribs = getChildComponentCustom($listobject, $tc2, '', -1, $uscont_id);
// local tributaries (COVA framework distinguishes between upstream and side stream tribs)
//$localtribs = getChildComponentCustom($listobject, $tc2, '', -1, $trib_contid);
$folder_tribs = getChildComponentCustom($listobject, $tc2, '', -1, $trib_contid);
$other_local_tribs = getChildComponentCustom($listobject, $ncnt, '', -1, $elementid);
$localtribs = array_merge($folder_tribs, $other_local_tribs);

// END - Upstream Tribs

//print("<a href='./cova_model_infotab.php?elementid=$dsc&geoscope=$geoscope&luname=$luname&displayrun=$displayrun'>Click here to see downstream container ($dsc)</a><br>");


//print("<br>BEGIN POST FORM<br>");
$formatted = showFormVars($listobject,$formvars,$aset_analysis['cova_model_info'],0, 1, 0, 0, 1, 0, -1, NULL, 1);

//print("<br>FORM VARS<br>" . print_r($formatted,1) . "<br>");
$formname = 'aform';
$form = '<b>Navigation:</b>';
$form .= "<form action='$scriptname' method=post id='$formname' name='$formname'>";
$form .= "<table>";
$form .= "<tr><td align=left><i>Upstream Reach Segments</i>: ";
foreach ($ustribs as $thistrib) {
   $cid = $thistrib['elementid'];
   $cname = $thistrib['elemname'];
   $form .= showGenericButton('upstream',"$cname", "document.forms[\"$formname\"].elements.elementid.value=$cid; document.forms[\"$formname\"].submit()", 1, 0);
   $form . " | ";
}
$form .= "</td>";
$form .= "<td align=right>Share This Data View <a href='$weblink'>as a link</a></td></tr>";
$form .= "<tr><td align=left><i>Local Tributary Reaches</i>: ";
foreach ($localtribs as $thistrib) {
   $cid = $thistrib['elementid'];
   $cname = $thistrib['elemname'];
   $form .= showGenericButton('sidestream',"$cname", "document.forms[\"$formname\"].elements.elementid.value=$cid; document.forms[\"$formname\"].submit()", 1, 0);
   $form . " | ";
}
$form .= "</td></tr>";
$form .= "</table>";

$form .= "<b>Display Options:</b>:";
$form .= "<br>";
$form .= " Geographic Scope for Withdrawals: " . $formatted->formpieces['fields']['geoscope'] . "<BR>";
$form .= " Withdrawal Data Sources: " . $formatted->formpieces['fields']['wd_datasources'] . "<BR>";
$form .= " Flow Metrics: " . $formatted->formpieces['fields']['flow_analysis_metrics'] . "<BR>";
$form .= " Flow Component: " . $formatted->formpieces['fields']['flow_analysis_pts'] . "<BR>";
$form .= showHiddenField('luname', $luname, 1);
//$form .= showHiddenField('runid', $runid, 1);
$form .= " Baseline Run: " . showActiveList($listobject, 'runid', 'scen_model_run_elements', 'runid', 'runid', " elementid = $elementid ", $runid, "", 'runid', $debug, 1, 0);
$form .= " Secondary Run: " . showActiveList($listobject, 'displayrun', 'scen_model_run_elements', 'runid', 'runid', " elementid = $elementid ", $displayrun, "", 'runid', $debug, 1, 0);
$form .= "Analysis Start Date:" . showWidthTextField('startdate', $startdate, 12, '', 1);
$form .= "Analysis End Date:" . showWidthTextField('enddate', $enddate, 12, '', 1);
$form .= " Summary Temporal Resolution: " . $formatted->formpieces['fields']['summary_resolution'] . "<BR>";
$form .= "<br>";
$form .= showHiddenField('elementid', $elementid, 1);
$form .= showGenericButton('refreshview',"Refresh Analysis View", "document.forms[\"aform\"].submit()", 1, 0);
$form .= showGenericButton('downstream',"View Downstream  ($dsname)", "document.forms[\"$formname\"].elements.elementid.value=$dsc; document.forms[\"$formname\"].submit()", 1, 0);
$form .= showWidthTextField('quickid', $quickid, 12, '', 1);
$form .= showGenericButton('downstream',"Jump to ID", "document.forms[\"$formname\"].elements.elementid.value=document.forms[\"$formname\"].elements.quickid.value; document.forms[\"$formname\"].submit()", 1, 0);
$form .= "</form>";
print($form);
//

//print("<li><a href='./cova_model_infotab.php?elementid=$elementid&geoscope=$geotoggle&luname=$luname&displayrun=$displayrun'>Click here to show $geotoggle results for withdrawal, discharge, and biological queries. </a><br>");
// END - next downstream segment



# format output into tabbed display object
$taboutput = new tabbedListObject;
$taboutput->name = 'model_element';
$taboutput->height = '600px';
#$taboutput->width = '100%';
$taboutput->width = '800px';
$taboutput->tab_names = array('general','landuse','withdrawals','discharges','model_runs', 'flow_summary','biology');
$taboutput->tab_buttontext = array(
   'general'=>'General Properties',
   'landuse'=>'Land Use',
   'discharges'=>'Discharges',
   'withdrawals'=>'Withdrawals',
   'model_runs'=>'Model Run Info',
   'flow_summary'=>'Model Flow Summary',
   'biology' => 'Biological Data'
);
$taboutput->init();
$taboutput->tab_HTML['general'] .= "<b>General Properties:</b><br>";
$taboutput->tab_HTML['landuse'] .= "<b>Land Use:</b><br>";
$taboutput->tab_HTML['discharges'] .= "<b>Discharges:</b><br>";
$taboutput->tab_HTML['withdrawals'] .= "<b>Withdrawals:</b><br>";
$taboutput->tab_HTML['model_runs'] .= "<b>Model Run Info:</b><br>";
$taboutput->tab_HTML['flow_summary'] .= "<b>Model Flow Summary Info:</b><br>";
$taboutput->tab_HTML['biology'] .= "<b>Biological Data:</b><br>";


switch ($scenarioid) {
   case 28:
      $pscol = 'discharge_mgd';
      $wdcol = 'demand_mgd';
      $upscol = 'discharge_mgd';
      $uwdcol = 'demand_mgd';
      $cpscol = 'discharge_mgd';
      $cwdcol = 'demand_mgd';
   break;

   default:
      $pscol = 'ps_mgd';
      $wdcol = 'wd_mgd';
      $upscol = 'ps_upstream_mgd';
      $uwdcol = 'wd_upstream_mgd';
      $cpscol = 'ps_cumulative_mgd';
      $cwdcol = 'wd_cumulative_mgd';
   break;
}
$listobject->show = 0;
$listobject->queryrecords = array($elinfo);
$listobject->showList();
$taboutput->tab_HTML['general'] .= $listobject->outstring;
$riverel = getCOVAMainstem($listobject, $elementid);
$riverprops = getElementProperties($riverel, 1);
//$taboutput->tab_HTML['general'] .= print_r($riverprops,1) . "<br>";
$taboutput->tab_HTML['general'] .= "<table><tr>";
$taboutput->tab_HTML['general'] .= "<td width=50% valign=top>";
$taboutput->tab_HTML['general'] .= "<b>Watershed Properties:</b><ul>";
$taboutput->tab_HTML['general'] .= "<li>Total Drainage Area (sqmi): " . number_format($riverprops['props']['drainage_area'],2) . "<br>";
$taboutput->tab_HTML['general'] .= "<li>Local Drainage Area (sqmi): " . number_format($riverprops['props']['area'],2) . "<br>";
$taboutput->tab_HTML['general'] .= "<li>Local Channel Length (mi): " . number_format($riverprops['props']['length'] / 5280.0,2). "<br>";
$taboutput->tab_HTML['general'] .= "<li>Local Channel Slope (ft/ft): " . number_format($riverprops['props']['slope'] / 5280.0,4). "<br>";
$taboutput->tab_HTML['general'] .= "</td>";
$taboutput->tab_HTML['general'] .= "<td width=50% valign=top>";
// set up map view
$ext = getGroupExtents($listobject, 'scen_model_element', 'poly_geom', '', '', "elementid=$elementid", 0.15, $debug);
//print("Geometry extent returned: $ext <br>");
// gmap view
list($lon1,$lat1,$lon2,$lat2) = split(',',$ext);
$mapurl = "http://deq2.bse.vt.edu/om/nhd_tools/gmap_test.php?";
$mapurl .= "lon1=$lon1&lat1=$lat1&lon2=$lon2&lat2=$lat2&elementid=$elementid&mapwidth=400&mapheight=400";
$taboutput->tab_HTML['general'] .= "<iframe height=400 width=400 src='" . $mapurl . "'></iframe>";
$taboutput->tab_HTML['general'] .= "</td>";
$taboutput->tab_HTML['general'] .= "</tr>";
$taboutput->tab_HTML['general'] .= "</table>";

//$debug = 1;
// get data for each run
$runs = array($runid,$displayrun);
sort($runs);
foreach ($runs as $thisrunid) {
   $rundata = retrieveRunSummary($listobject, $elementid, $thisrunid);
   if (isset($rundata['starttime'])) {
      if (strtotime($rundata['starttime']) > strtotime($startdate)) {
         $startdate = $rundata['starttime'];
      }
   }
   if (isset($rundata['endtime'])) {
      if (strtotime($rundata['endtime']) < strtotime($enddate)) {
         $enddate = $rundata['endtime'];
      }
   }
   $taboutput->tab_HTML['model_runs'] .= print_r($rundata,1) . "<br>";
   $taboutput->tab_HTML['model_runs'] .= "Start time: $startdate - End Date: $enddate <hr>";
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
      // find the intersection of the dates from these two runs so that the comparison is apples to apples
   }
   $taboutput->tab_HTML['model_runs'] .= "<b>Run Summary for Run ID</b> $thisrunid <br>";
   $taboutput->tab_HTML['model_runs'] .= formatPrintMessages($rundata);
}


// BEGIN - Create Fllow Comparison Graphs
// print out graphical comparisons of flows
// show check boxes for runs to compare (to do later)
//$debug = 1;
//$runids = array(0,2);
$variables = 'Qout';

//$listobject->querystring = "  select runid from scen_model_run_elements where elementid = $elementid and runid <> -1 and starttime <= '$startdate' and endtime >= '$enddate' ";
//$listobject->querystring = "  select runid from scen_model_run_elements where elementid = $elementid and runid <> -1 and starttime <= '$startdate' and endtime >= '$enddate' ";
$listobject->querystring = "  select runid from scen_model_run_elements where elementid = $elementid and runid in ( " . join(',', $runs) . ") order by runid";
$listobject->performQuery();
foreach ($listobject->queryrecords as $rr) {
   $runids[] = $rr['runid'];
}
//$runids = array(0,1);
//print("$listobject->querystring ; <br>" . print_r($runids,1) . "<br>");
$doquery = 1; // set to 0 to just assemble but not execute the query
$result = compareRunData($elementid, join(',',$runids), $variables, $startdate, $enddate, $doquery, $debug);

// get withdrawals and discharges for currrent run:
foreach (array_keys($result['run_tables']) as $trun) {
   $runtable = $result['run_tables'][$trun];
   $session_db->querystring = "  select month,  ";
   $session_db->querystring .= "    round(avg($pscol)::numeric,2) as ps_mgd ";
   $session_db->querystring .= " from \"$runtable\"  ";
   $session_db->querystring .= " group by month  ";
   $session_db->querystring .= " order by month ";
   $session_db->performQuery();
   $session_db->show = 0;
   $session_db->showList();
   //$taboutput->tab_HTML['discharges'] .= print_r($result['run_tables'],1) . "<br>" . $session_db->querystring;
   //$taboutput->tab_HTML['discharges'] .= "Error: " . $result['error'] . "<br>" . $session_db->querystring;
   $local_ps = $session_db->outstring;
   $session_db->querystring = "  select month,  ";
   $session_db->querystring .= "    round(avg($upscol)::numeric,2) as ps_upstream_mgd ";
   $session_db->querystring .= " from \"$runtable\"  ";
   $session_db->querystring .= " group by month  ";
   $session_db->querystring .= " order by month ";
   $session_db->performQuery();
   $session_db->show = 0;
   $session_db->showList();
   //$taboutput->tab_HTML['discharges'] .= $session_db->querystring;
   $upstream_ps = $session_db->outstring;
   $session_db->querystring = "  select month,  ";
   $session_db->querystring .= "    round(avg($cpscol)::numeric,2) as total_ps_mgd ";
   $session_db->querystring .= " from \"$runtable\"  ";
   $session_db->querystring .= " group by month  ";
   $session_db->querystring .= " order by month ";
   $session_db->performQuery();
   $session_db->show = 0;
   $session_db->showList();
   //$taboutput->tab_HTML['discharges'] .= $session_db->querystring;
   $cumu_ps = $session_db->outstring;

   $taboutput->tab_HTML['discharges'] .= "<br><table><tr><th colspan=3>Run # $trun</th>\n";
   $taboutput->tab_HTML['discharges'] .= "<td align=center class='panelInfo'><b>Local Discharges: </b><br>" . $local_ps . "</td>";
   $taboutput->tab_HTML['discharges'] .= "<td align=center class='panelInfo'><b>Upstream Discharges: </b><br>" . $upstream_ps . "</td>";
   $taboutput->tab_HTML['discharges'] .= "<td align=center class='panelInfo'><b>Cumulative Discharges: </b><br>" . $cumu_ps . "</td>";
   $taboutput->tab_HTML['discharges'] .= "</tr></table><br>";
}

// get withdrawals and discharges for current run:
foreach (array_keys($result['run_tables']) as $trun) {
   $runtable = $result['run_tables'][$trun];
   $session_db->querystring = "  select month,  ";
   $session_db->querystring .= "    round(avg($wdcol)::numeric,2) as wd_mgd ";
   $session_db->querystring .= " from \"$runtable\"  ";
   $session_db->querystring .= " group by month  ";
   $session_db->querystring .= " order by month ";
   $session_db->performQuery();
   $session_db->show = 0;
   $session_db->showList();
   $local_wd = $session_db->outstring;
   //$taboutput->tab_HTML['withdrawals'] .= $result['query'] . "<br>";
   //$taboutput->tab_HTML['withdrawals'] .= $session_db->querystring;
   $session_db->querystring = "  select month,  ";
   $session_db->querystring .= "    round(avg($uwdcol)::numeric,2) as wd_upstream_mgd ";
   $session_db->querystring .= " from \"$runtable\"  ";
   $session_db->querystring .= " group by month  ";
   $session_db->querystring .= " order by month ";
   $session_db->performQuery();
   $session_db->show = 0;
   $session_db->showList();
   $upstream_wd = $session_db->outstring;
   $session_db->querystring = "  select month,  ";
   $session_db->querystring .= "    round(avg($cwdcol)::numeric,2) as total_wd_mgd ";
   $session_db->querystring .= " from \"$runtable\"  ";
   $session_db->querystring .= " group by month  ";
   $session_db->querystring .= " order by month ";
   $session_db->performQuery();
   $session_db->show = 0;
   $session_db->showList();
   $cumu_wd = $session_db->outstring;

   $taboutput->tab_HTML['withdrawals'] .= "<br><table><tr><th colspan=3>Run # $trun</th>\n";
   $taboutput->tab_HTML['withdrawals'] .= "<td align=center class='panelInfo'><b>Local Withdrawals: </b><br>" . $local_wd . "</td>";
   $taboutput->tab_HTML['withdrawals'] .= "<td align=center class='panelInfo'><b>Upstream Withdrawals: </b><br>" . $upstream_wd . "</td>";
   $taboutput->tab_HTML['withdrawals'] .= "<td align=center class='panelInfo'><b>Cumulative Withdrawals: </b><br>" . $cumu_wd . "</td>";
   $taboutput->tab_HTML['withdrawals'] .= "</tr></table><br>";
}


// show Flow Duration
//$debug = 1;
$graphout = cova_graphFlowDuration($result, $debug);
$graphurl = $graphout['img_url'];
if ($debug) {
   $taboutput->tab_HTML['flow_summary'] .= $graphout['debug'];
}
$taboutput->tab_HTML['flow_summary'] .= "<img src='$graphurl'>";
//$taboutput->tab_HTML['flow_summary'] .= $graphout['data_table'] . "<br>";


$taboutput->tab_HTML['flow_summary'] .= "<b>Custom Queries</b><br>";
$taboutput->tab_HTML['flow_summary'] .= "Vars: " . print_r($variables,1) . "<br>";

foreach ($flow_analysis_metrics as $thismetric) {
   foreach ($flow_analysis_pts as $thispoint) {
      switch ($thispoint) {
         case 'reach_out':
            // default to the main object
             $thiselementid = $elementid;
             $vars = "Qout";
         break;
         
         case 'reach_in':
            // default to the main object
             $thiselementid = $riverel;
             $vars = "Qin";
         break;
         
         case 'local_in':
            // default to the main object
             $thiselementid = $riverel;
             $vars = "Runit";
         break;

         case 'local_pswd':
            // default to the main object
             $thiselementid = $riverel;
             $vars = "wd_mgd,ps_mgd";
         break;

         case 'cumu_pswd':
            // default to the main object
             $thiselementid = $elementid;
             $vars = "wd_cumulative_mgd,ps_cumulative_mgd";
         break;
         
         case 'upstream_in':
            // default to the main object
             $thiselementid = $riverel;
             $vars = "Qup";
         break;
         
         default:
            // default to the main object
             $thiselementid = $elementid;
             $vars = "Qout";
         break;
         
      }
      //$debug = 1;
      error_log("Calculating flow analysis data for  $thismetric ( $vars ) on $thispoint ");
      $taboutput->tab_HTML['flow_summary'] .= "Metric $thismetric ( $vars ) on $thispoint data <br>";
      $thisresult = compareRunData($thiselementid, join(',',$runids), $vars, $startdate, $enddate, $doquery, $debug);   
      if ($debug) {
         $taboutput->tab_HTML['flow_summary'] .= "Run Data Query: " . $thisresult['query'] . "<br>";   
      }
      $graphout = cova_graphFlowComparison($thisresult, $thismetric, 1, NULL, 2, $summary_resolution);
      $graphurl = $graphout['img_url'];
      if ($debug) {
         $taboutput->tab_HTML['flow_summary'] .= "Graph Debug Info: " . $graphout['debug'] . "<br>";
      }
      $taboutput->tab_HTML['flow_summary'] .= "<img src='$graphurl'>";
      $taboutput->tab_HTML['flow_summary'] .= $graphout['data_table'] . "<br>";
   }
}

//$robj['innerHTML'] .= "Finished <br>";
if ($debug) {
   $taboutput->tab_HTML['flow_summary'] .= "<hr>" . $session_db->error;
}
$debug = 0;
//$debug = 0;
// END - Create Fllow Comparison Graphs


$landuse = getNestedContainersCriteria ($listobject, $elementid, array(), array('cova_cbp_lrseg'));

// get shape for overlapping queries
$taboutput->tab_HTML['withdrawals'] .= "Getting info for geoscope = $geoscope <br>";
switch ($geoscope) {
   case 'local':
      $wd = getCOVAWithdrawals($listobject, $elementid, array(), $debug);
   break;
   
   case 'cumulative':
   // gets all cumulative withdrawals
      $wd = getNestedContainersCriteria ($listobject, $elementid, array('wsp_vpdesvwuds'), array('','cova_withdrawal'));
   break;
   
   default:
      $wd = getCOVAWithdrawals($listobject, $elementid, array(), $debug);
   break;
}

// gets only local withdrawals
$t = 0;
$wdlist = array();
$momap = array(1=>'jan',2=>'feb',3=>'mar',4=>'apr',5=>'may',6=>'jun',7=>'jul',8=>'aug',9=>'sep', 10=>'oct',11=>'nov',12=>'dec');
error_log("Getting withdrawal objects");
$totals = array();
$totals['Max Annual'] = 0.0;
$totals['Max MGD'] = 0.0;
$totals['Current Annual'] = 0.0;
$totals['Current MGD'] = 0.0;
foreach ($wd as $rowkey => $thiswd) {
   //print($rowkey . " - " . print_r($thiswd,1) . " <br>");
   $pid = $thiswd['elementid'];
   $unser = unserializeSingleModelObject($pid);
   $wdobj = $unser['object'];
   $wdlist[$rowkey]['User ID'] = $wdobj->id1;
   $wdlist[$rowkey]['Description'] = $wdobj->name;
   $wdlist[$rowkey]['Action'] = $wdobj->action;
   $wdlist[$rowkey]['Type'] = $wdobj->wdtype;
   $wdlist[$rowkey]['Max Annual'] = 'Not Set';
   $wdlist[$rowkey]['Current Annual'] = 'Not Set';
   $wdlist[$rowkey]['Max MGD'] = 'Not Set';
   $wdlist[$rowkey]['Current MGD'] = 'Not Set';
   if (isset($wdobj->processors['safe_yield_mgy'])) {
      $safeyield_mgy = floatval($wdobj->processors['safe_yield_mgy']->equation);
      $wdlist[$rowkey]['Max Annual'] = number_format($safeyield_mgy ,2);
      $wdlist[$rowkey]['Max MGD'] = number_format($safeyield_mgy / 365.0,2);
      $totals['Max Annual'] += $safeyield_mgy;
      $totals['Max MGD'] += $safeyield_mgy / 365.0;
   }
   if (isset($wdobj->processors['current_mgy'])) {
      $current_mgy = floatval($wdobj->processors['current_mgy']->equation);
      $wdlist[$rowkey]['Current Annual'] = number_format( $current_mgy, 2);
      $wdlist[$rowkey]['Current MGD'] = number_format( $current_mgy / 365.0,2);
      $totals['Current Annual'] += $current_mgy;
      $totals['Current MGD'] += $current_mgy / 365.0;
      /*
      if (is_object($wdobj->processors['historic_monthly_pct'])) {
         $wdobj->processors['historic_monthly_pct']->formatMatrix();
         foreach ($wdobj->processors['historic_monthly_pct']->matrix_formatted as $monum => $moval) {
            if (isset($momap[$monum])) {
               $wdlist[$rowkey][$momap[$monum]] = number_format(floatval($moval) * $current_mgy / 30.0,2);
            }
         }
      }
      */
   }
   $wdlist[$rowkey]['MPID'] = $wdobj->id2;
   //$wdrecs .= "$pid - " . print_r($wdobj->processors['current_monthly_discharge']->matrix_formatted,1) . "<br>";
   $t++;
   if ($t > 150) {
      //break;
   }
}
$totals['Max Annual'] = number_format($totals['Max Annual'],2);
$totals['Max MGD'] = number_format($totals['Max MGD'],2);
$totals['Current Annual'] = number_format($totals['Current Annual'],2);
$totals['Current MGD'] = number_format($totals['Current MGD'],2);


// get Point Sources, pull current values for display
switch ($geoscope) {
   case 'local':
      $ps = getCOVADischarges($listobject, $scenarioid, $elementid, array(), $debug);
   break;
   
   case 'cumulative':
   // gets all cumulative discharges
      $ps = getNestedContainersCriteria ($listobject, $elementid, array(), array('cova_pointsource'));
   break;
   
   default:
      $ps = getCOVADischarges($listobject, $scenarioid, $elementid, array(), $debug);
   break;
}

$psrecs = '';
$t = 0;
$pslist = array();

error_log("Getting point source objects - found: " . count($ps));
foreach ($ps as $rowkey => $thisps) {
   //print($rowkey . " - " . print_r($thisps,1) . " <br>");
   $pid = $thisps['elementid'];
   $unser = unserializeSingleModelObject($pid);
   $psobj = $unser['object'];
   $pslist[$rowkey]['Name'] = $psobj->name;
   $pslist[$rowkey]['VPDES #'] = $psobj->vpdes_permitno;
   if (isset($psobj->processors['current_monthly_discharge'])) {
      $pslist[$rowkey]['Mean MGD'] = 0.0;
      if (is_object($psobj->processors['current_monthly_discharge'])) {
         $psobj->processors['current_monthly_discharge']->formatMatrix();
         foreach ($psobj->processors['current_monthly_discharge']->matrix_formatted as $monum => $moval) {
            $pslist[$rowkey][$momap[$monum]] = $moval;
            $pslist[$rowkey]['Mean MGD'] += $moval/12.0;
         }
      }
   }
   //$psrecs .= "$pid - " . print_r($psobj->processors['current_monthly_discharge']->matrix_formatted,1) . "<br>";
   $t++;
   if ($t > 150) {
      //break;
   }
}

$wd_sort = subval_sort($wdlist,'Mean MGD', 'desc');
$ps_sort = subval_sort($pslist,'Mean MGD', 'desc');

$listobject->show = 0;
$listobject->queryrecords = $landuse;
$listobject->showList();
$taboutput->tab_HTML['landuse'] .= $listobject->outstring;

// show withdrawals
$fname = 'tmp_wd' . $userid . '.' . rand(1000,9999) . '.csv';
$robj['innerHTML'] .= "Writing $tmpdir/$fname<br>";
putDelimitedFile("$tmpdir/$fname",$wd_sort,',',1,'unix',1);
$taboutput->tab_HTML['withdrawals'] .= "Historical and Projected Withdrawals<br>";
$listobject->queryrecords = array(0=>$totals);
$listobject->showList();
$taboutput->tab_HTML['withdrawals'] .= $listobject->outstring;
$taboutput->tab_HTML['withdrawals'] .= "<a href='/tmp/$fname' target='_blank'>Click Here to Download CSV File</a><br>";
$listobject->queryrecords = $wd_sort;
$listobject->showList();
$taboutput->tab_HTML['withdrawals'] .= $listobject->outstring;

$listobject->queryrecords = $ps_sort;
$fname = 'tmp_ps' . $userid . '.' . rand(1000,9999) . '.csv';
$robj['innerHTML'] .= "Writing $tmpdir/$fname<br>";
putDelimitedFile("$tmpdir/$fname",$ps_sort,',',1,'unix',1);
$taboutput->tab_HTML['discharges'] .= "<a href='/tmp/$fname' target='_blank'>Click Here to Download CSV File</a><br>";
$listobject->showList();
$taboutput->tab_HTML['discharges'] .= $listobject->outstring . "<br>Current Discharge output: $psrecs ";

error_log("Searching Biological databases");

if ( !($basinshape === FALSE) ) {
   
   // set up conn to new edas db
   $edas_db_dbconn = pg_connect("host=$vwuds_dbip port=8080 dbname='aquatic_bio' user='aquatic_bio_ro' password=@quaticB10");
   $edas_db = new pgsql_QueryObject;
   $edas_db->dbconn = $edas_db_dbconn;
   $edas_db->querystring = " select a.\"Latitude\", a.\"Longitude\", b.* ";
   $edas_db->querystring .= " from \"Stations\" as a, \"edas_xtab_export1\" as b ";
   $edas_db->querystring .= " where contains(geomfromtext('$wkt',4326), setsrid(a.the_geom,4326))";
   $edas_db->querystring .= " and a.\"StationID\" = b.\"StationID\" ";
   $edas_db->performQuery();
   $edas_db->show = 0;
   $edas_db->showList();
   $taboutput->tab_HTML['biology'] .= $edas_db->outstring;
   $foo_table = "( " . $edas_db->querystring . ") as foo ";
   //$wiz = showGenericQueryWizard ($edas_db, array(), 'edas_xtab_export1', 'edas_bio', 'xajax');
   $formvals = array('tablename'=>'edas_xtab_export1', 'xajax_submit' => 'xajax_refreshAquaticBioAnalysisWindow');
   $wiz = showGenericAnalysisWindow( $formvals, $aquatic_biodb,$debug, 'xajax', $foo_table);
   $taboutput->tab_HTML['biology'] .= "<div id='edas_bio" . "' style=\"border: 1px solid rgb(0 , 0, 0); border-style: dotted; overflow: auto; height: 480px; width: 720px; display: block;  background: #eee9e9;\">";;
   $taboutput->tab_HTML['biology'] .= "<form name='edas_bio' id='edas_bio'>";
   $taboutput->tab_HTML['biology'] .= $wiz['innerHTML'];
   $taboutput->tab_HTML['biology'] .= "</form>";
   $taboutput->tab_HTML['biology'] .= "</div>";
   //$taboutput->tab_HTML['biology'] .= $edas_db->querystring;
   
   $bioquery = " select sppbova,taxagrp,genus,species,subspecies, common_nam, targetspp, count, waterbody from observed_vdgif_xy where contains(geomfromtext('$wkt',4269), setsrid(the_geom,4269)) and taxagrp = 'Fish' ";
   //$bioquery = " select sppbova,taxagrp,genus,species,subspecies, common_nam, targetspp, count, waterbody, y(the_geom) as lat, x(the_geom) as lon from observed_vdgif_xy where contains(geomfromtext('$wkt',4269), setsrid(the_geom,4269)) ";
   
   
   $taboutput->tab_HTML['biology'] .= "Geographic scope: $geoscope, segments: " . print_r($wkt_segs, 1) . "<br>";
//error_reporting(E_ALL);   
   // USE THIS for analysis grid
   
   
   // un-comment this for plain old vanilla
   $aquatic_biodb->querystring = $bioquery;
   $aquatic_biodb->performQuery();
   $aquatic_biodb->show = 0;
   $aquatic_biodb->showList();
   $taboutput->tab_HTML['biology'] .= $aquatic_biodb->outstring;
   
   //print("QUERY: " . " $bioquery<br>");
} else {
   $taboutput->tab_HTML['biology'] .= "Shape not returned for " . print_r($segs,1) . "<br>";
}

//$taboutput->tab_HTML['biology'] .= "temporarily disabled";
//$taboutput->tab_HTML['biology'] .= "<br><a href='./cova_model_infotab.php?elementid=$elementid&geoscope=$alt_scope'>Click here to see records for $alt_scope basin boundaries.</a>";


$anwd_recs = getTotalAnnualSurfaceWithdrawalByWKT($vwuds_listobject, 1980, date('Y'), $wkt, $debug, 1, 0);
$vwuds_listobject->queryrecords = $anwd_recs['annual_records'];
$vwuds_listobject->show = 0;
$vwuds_listobject->showList();

$taboutput->tab_HTML['withdrawals'] .= "<table><tr>";
$taboutput->tab_HTML['withdrawals'] .= "<td>Recent reported Surface Withdrawals: <br>Method 1:" . $vwuds_listobject->outstring . "</td>";

$graph = array();
$graph['graphrecs'] = $anwd_recs['annual_records'];
$graph['xcol'] = 'thisyear';
$graph['ycol'] = 'total_mgd';
$graph['yaxis'] = 1;
$graph['plottype'] = 'bar';
$graph['color'] = 'blue';
$graph['ylegend'] = 'WD';
$bgs[] = $graph;

$multibar = array(
   'title'=> "Annual Withdrawals",
   'xlabel'=>'Year',
   'ylabel'=>'Mean Withdrawal (MGD)',
   'num_xlabels'=>15,
   'gwidth'=>600,
   'gheight'=>400,
   'overlapping'=>0,
   'labelangle'=>90,
   'randomname'=>0,
   'legendlayout'=>LEGEND_HOR,
   'legendpos'=>array(0.20,0.95,'left','bottom'),
   'base
name'=>"wd_$elementid",
   'bargraphs'=>$bgs
);

$graphurl = showGenericMultiPlot($goutdir, $goutpath, $multibar, $debug);


$taboutput->tab_HTML['withdrawals'] .= "<td>Graph: <br><img src='$graphurl'></td>";
$taboutput->tab_HTML['withdrawals'] .= "</tr></table>";
//$taboutput->tab_HTML['withdrawals'] .= "Query: <br>" . $vwuds_listobject->querystring;
// max for each month / year

$vwuds_listobject->querystring = "select max(jan) as jan, max(feb) as feb, max(mar) as mar, max(apr) as apr, max(may) as may, max(jun) as jun, max(jul) as jul, max(aug) as aug, max(sep) as sep, max(oct) as oct, max(nov) as nov, max(dec) as dec, max(total_mgd) as max_annual from ( $wdquery ) as foo ";
$vwuds_listobject->performQuery();
$vwuds_listobject->show = 0;
$vwuds_listobject->showList();
$taboutput->tab_HTML['withdrawals'] .= "Max reported Withdrawals: <br>" . $vwuds_listobject->outstring;

if (in_array('wsp', $wd_datasources)) {


}

// point sources
$vp_result = getVPDESAnnualDischargeWKT($vpdes_listobject, $wkt);
$vpdes_listobject->queryrecords = $vp_result['records'];
$vpdes_listobject->showList();
$taboutput->tab_HTML['discharges'] .= "<table><tr><td>Reported Discharges: <br>" . $vpdes_listobject->outstring;
//$taboutput->tab_HTML['discharges'] .= "Reported Discharges SQL: <br>" . $vpdes_listobject->querystring;


$graph = array();
$bgs = array();
$graph['graphrecs'] = $vpdes_listobject->queryrecords;
$graph['xcol'] = 'thisyear';
$graph['ycol'] = 'annual_mgd';
$graph['yaxis'] = 1;
$graph['plottype'] = 'bar';
$graph['color'] = 'blue';
$graph['ylegend'] = 'PS';
$bgs[] = $graph;

$multibar = array(
   'title'=> "Annual Discharge Rate",
   'xlabel'=>'Year',
   'ylabel'=>'Mean Discharge (MGD)',
   'num_xlabels'=>15,
   'gwidth'=>600,
   'gheight'=>400,
   'overlapping'=>0,
   'labelangle'=>90,
   'randomname'=>0,
   'legendlayout'=>LEGEND_HOR,
   'legendpos'=>array(0.20,0.95,'left','bottom'),
   'base
name'=>"wd_$elementid",
   'bargraphs'=>$bgs
);

$graphurl = showGenericMultiPlot($goutdir, $goutpath, $multibar, $debug);


$taboutput->tab_HTML['discharges'] .= "<td>Graph: <br><img src='$graphurl'></td>";
$taboutput->tab_HTML['discharges'] .= "</tr></table>";
         
         

/* RENDER FINAL TABBED OBJECT */
$taboutput->createTabListView($activetab);
# add the tabbed view the this object
$innerHTML .= $taboutput->innerHTML;

print($innerHTML);
error_log("Finished watershed summary");

?>
</body>
</html>
