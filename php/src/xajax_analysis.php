<?php

# xajax based library - CIA analysis
include_once("xajax_analysis.common.php");
include_once("lib_verify.php");

if (!$noajax) {
   $xajax->processRequest();
}

//*************************************************
//***      BASIC Xajax Function Pair Template   ***
//*************************************************

function showXajaxFunctionTemplate($formValues) {
   $objResponse = new xajaxResponse();
   $divname = "controlpanel";
   $divHTML = "";
   $divHTML .= showFunctionTemplate($formValues);
   $objResponse->assign($divname,"innerHTML",$divHTML);
   return $objResponse;

}

function showFunctionTemplate($formValues) {
   $innerHTML = "Cumulative Impact Analysis Viewer";
   return $innerHTML;
}

//*************************************************
//*** END - BASIC Xajax Function Pair Template  ***
//*************************************************


function showXajaxCIAViewer($formValues) {
   $objResponse = new xajaxResponse();
   $divname = "controlpanel";
   $divHTML = "";
   $divHTML .= showCIAViewer($formValues);
   $objResponse->assign($divname,"innerHTML",$divHTML);
   return $objResponse;

}

function showCIAViewer($formValues) {
   $innerHTML = "Cumulative Impact Analysis Viewer";
   return $innerHTML;
}





function showXajaxCOVAViewer($formValues) {
   $objResponse = new xajaxResponse();
   $divname = "cova_viewer";
   $divHTML = "";
   $divHTML .= showCOVAViewer($formValues);
   $objResponse->assign($divname,"innerHTML",$divHTML);
   return $objResponse;

}

include_once("$basedir/summary/lib_cova_summary.php");
function showCOVAViewer($formValues) {
   global $basedir, $listobject, $session_db, $scriptname;
   include_once("$basedir/summary/adminsetup.analysis.php");
   
   // POST form
   if (isset($formValues['elementid'])) {
      $elementid = $formValues['elementid'];
   }
   
   if (isset($formValues['bio_geo_scope'])) {
      $formvars = $formValues;
      $displayrun = $formValues['displayrun'];
      $runid = $formValues['runid'];
      $luname = $formValues['luname'];
      $geoscope = $formValues['bio_geo_scope'];
      $bio_geo_scope = $formValues['bio_geo_scope'];
      $flow_analysis_metrics = $formValues['flow_analysis_metrics'];
      $flow_analysis_pts = $formValues['flow_analysis_pts'];
      $quickid = $formValues['quickid'];
   } else {
      $formvars = array(
         'bio_geo_scope' => 'local',
         'flow_analysis_pts' => array('reach_out'),
         'flow_analysis_metrics' => array('mean', 'median')
      );
      $runid = 0;
      $luname = 'for';
      $bio_geo_scope = 'local';
      $flow_analysis_metrics = array('mean', 'median');
      $flow_analysis_pts = array('reach_out');
   }
   
   $elinfo = getElementInfo($listobject, $elementid);
   if (isset($elinfo['scenarioid'])) {
      $scenarioid = $elinfo['scenarioid'];
   }
   // SET UP types of containers to query up and downstream components
   switch ($scenarioid) {
      case 28:
         $pcnt = array('custom1'=>array('vahydro_lite_container'));
         $ncnt = array('custom1'=>array('vahydro_lite_container'));
         $ccnt = 'vahydro_lite_container';
      break;

      default:
         $pcnt = array('custom1'=>array('cova_ws_container'));
         $ncnt = array('custom1'=>array('cova_ws_container', 'cova_ws_subnodal'));
         $ccnt = 'cova_ws_container';
      break;
   }
   
   
   $segs = array();
   $outlet_seg = array();
   $models = getNestedContainersCriteria ($listobject, $elementid, array(), $ncnt);
   foreach ($models as $thismod) {
      $segs[] = $thismod['custom2'];
      if ($thismod['elementid'] == $elementid) {
         $outlet_seg[] = $thismod['custom2'];
      }
   }
   // get shape for overlapping queries
   switch ($geoscope) {
      case 'local':
         $wkt_segs = $outlet_seg;
         $alt_scope = 'cumulative';
      break;

      case 'cumulative':
         $wkt_segs = $segs;
         $alt_scope = 'local';
      break;

      default:
         $wkt_segs = $outlet_seg;
         $alt_scope = 'cumulative';
      break;
   }

   $wkt = getMergedCOVAShape($scenarioid, $listobject, $wkt_segs);


   // get contaiing "downstream" object
   $dsc = getContainingNodeType($elementid, 0, $pcnt, 10, $debug);
   $dsinfo = getElementInfo($listobject, $dsc);
   $dsname = $dsinfo['elemname'];
   // then get children of this container - upstream objects
   $ustribs = getChildComponentCustom1($listobject, $elementid, $ccnt, -1, $debug);

   // END - Upstream Tribs
$debug = 1;
   $innerHTML = '';
   if ($debug) {
      $innerHTML .= "Doing CIA For ElementID = $elementid <br>";
      $innerHTML .= print_r($formValues,1) . "<br>";
   }
   //$innerHTML .= "<a href='./cova_model_infotab.php?elementid=$dsc&geoscope=$geoscope&luname=$luname&displayrun=$displayrun'>Click here to see downstream container ($dsc)</a><br>";
   $innerHTML .= "<b>Display Options:</b>:";


   //$innerHTML .= "<br>BEGIN POST FORM<br>";
   $formatted = showFormVars($listobject,$formvars,$aset_analysis['cova_model_info'],0, 1, 0, 0, 1, 0, -1, NULL, 1);

   //$innerHTML .= "<br>FORM VARS<br>" . print_r($formatted,1) . "<br>";
   $formname = 'aform';
   $form = '';
   $form .= "<form action='$scriptname' method=post id='$formname' name='$formname'>";
   foreach ($ustribs as $thistrib) {
      $cid = $thistrib['elementid'];
      $cname = $thistrib['elemname'];
      $form .= showGenericButton('downstream',"View Upstream  ($cname)", "document.forms[\"$formname\"].elements.elementid.value=$cid; document.forms[\"$formname\"].submit()", 1, 0);
      $form . " | ";
   }
   $form .= "<br>";

   $form .= " Geographic Scope for Withdrawals: " . $formatted->formpieces['fields']['bio_geo_scope'] . "<BR>";
   $form .= " Flow Metrics: " . $formatted->formpieces['fields']['flow_analysis_metrics'] . "<BR>";
   $form .= " Flow Component: " . $formatted->formpieces['fields']['flow_analysis_pts'] . "<BR>";
   $form .= showHiddenField('luname', $luname, 1);
   //$form .= showHiddenField('runid', $runid, 1);
   $form .= " Baseline Run: " . showActiveList($listobject, 'runid', 'scen_model_run_elements', 'runid', 'runid', " elementid = $elementid ", $runid, "", 'runid', $debug, 1, 0);
   $form .= " Secondary Run: " . showActiveList($listobject, 'displayrun', 'scen_model_run_elements', 'runid', 'runid', " elementid = $elementid ", $displayrun, "", 'runid', $debug, 1, 0);
   $form .= "<br>";
   $form .= showHiddenField('elementid', $elementid, 1);
   $form .= showGenericButton('refreshview',"Refresh Analysis View", "document.forms[\"aform\"].submit()", 1, 0);
   $form .= showGenericButton('downstream',"View Downstream  ($dsname)", "document.forms[\"$formname\"].elements.elementid.value=$dsc; document.forms[\"$formname\"].submit()", 1, 0);
   $form .= showWidthTextField('quickid', $quickid, 12, '', 1);
   $form .= showGenericButton('downstream',"Jump to ID", "document.forms[\"$formname\"].elements.elementid.value=document.forms[\"$formname\"].elements.quickid.value; document.forms[\"$formname\"].submit()", 1, 0);
   $form .= "</form>";
   $innerHTML .= $form;
   //

   //$innerHTML .= "<li><a href='./cova_model_infotab.php?elementid=$elementid&geoscope=$geotoggle&luname=$luname&displayrun=$displayrun'>Click here to show $geotoggle results for withdrawal, discharge, and biological queries. </a><br>";
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
   $taboutput->tab_HTML['general'] .= "<b>Watershed Properties:</b><ul>";
   $taboutput->tab_HTML['general'] .= "<li>Total Drainage Area (sqmi): " . number_format($riverprops['props']['drainage_area'],2) . "<br>";
   $taboutput->tab_HTML['general'] .= "<li>Local Drainage Area (sqmi): " . number_format($riverprops['props']['area'],2) . "<br>";
   $taboutput->tab_HTML['general'] .= "<li>Local Channel Length (mi): " . number_format($riverprops['props']['length'] / 5280.0,2). "<br>";
   $taboutput->tab_HTML['general'] .= "<li>Local Channel Slope (ft/ft): " . number_format($riverprops['props']['slope'] / 5280.0,4). "<br>";

   //$debug = 1;
   $runs = array($runid,$displayrun);
   foreach ($runs as $thisrunid) {
      $rundata = retrieveRunSummary($listobject, $elementid, $thisrunid);
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
      $taboutput->tab_HTML['model_runs'] .= "<b>Run Summary for Run ID</b> $thisrunid <br>";
      $taboutput->tab_HTML['model_runs'] .= formatPrintMessages($rundata);
   }


   // BEGIN - Create Fllow Comparison Graphs
   // print out graphical comparisons of flows
   // show check boxes for runs to compare (to do later)
   //$debug = 1;
   //$runids = array(0,2);
   $variables = 'Qout';
   $startdate = '1984-10-01';
   $enddate = '2005-09-30';
   //$listobject->querystring = "  select runid from scen_model_run_elements where elementid = $elementid and runid <> -1 and starttime <= '$startdate' and endtime >= '$enddate' ";
   $listobject->querystring = "  select runid from scen_model_run_elements where elementid = $elementid and runid in ( " . join(',', $runs) . ") ";
   $listobject->performQuery();
   foreach ($listobject->queryrecords as $rr) {
      $runids[] = $rr['runid'];
   }
   //$runids = array(0,1);
   //$innerHTML .= "$listobject->querystring ; <br>" . print_r($runids,1) . "<br>";
   $doquery = 1; // set to 0 to just assemble but not execute the query
   $result = compareRunData($elementid, join(',',$runids), $variables, $startdate, $enddate, $doquery, $debug);
  // get withdrawals and discharges for currrent run:
   $runtable = $result['run_tables']["$displayrun"];
   $session_db->querystring = "  select month,  ";
   $session_db->querystring .= "    round(avg($pscol)::numeric,2) as ps_mgd ";
   $session_db->querystring .= " from \"$runtable\"  ";
   $session_db->querystring .= " group by month  ";
   $session_db->querystring .= " order by month ";
   $session_db->performQuery();
   $session_db->show = 0;
   $session_db->showList();
   $taboutput->tab_HTML['discharges'] .= print_r($result['run_tables'],1) . "<br>" . $session_db->querystring;
   $taboutput->tab_HTML['discharges'] .= "Error: " . $result['error'] . "<br>" . $session_db->querystring;
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

 
   $taboutput->tab_HTML['discharges'] .= "<br><table><tr>\n";
   $taboutput->tab_HTML['discharges'] .= "<td align=center class='panelInfo'><b>Local Discharges: </b><br>" . $local_ps . "</td>";
   $taboutput->tab_HTML['discharges'] .= "<td align=center class='panelInfo'><b>Upstream Discharges: </b><br>" . $upstream_ps . "</td>";
   $taboutput->tab_HTML['discharges'] .= "<td align=center class='panelInfo'><b>Cumulative Discharges: </b><br>" . $cumu_ps . "</td>";
   $taboutput->tab_HTML['discharges'] .= "</tr></table><br>";


   // get withdrawals and discharges for currrent run:
   $runtable = $result['run_tables'][$displayrun];
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

   $taboutput->tab_HTML['withdrawals'] .= "<br><table><tr>\n";
   $taboutput->tab_HTML['withdrawals'] .= "<td align=center class='panelInfo'><b>Local Withdrawals: </b><br>" . $local_wd . "</td>";
   $taboutput->tab_HTML['withdrawals'] .= "<td align=center class='panelInfo'><b>Upstream Withdrawals: </b><br>" . $upstream_wd . "</td>";
   $taboutput->tab_HTML['withdrawals'] .= "<td align=center class='panelInfo'><b>Cumulative Withdrawals: </b><br>" . $cumu_wd . "</td>";
   $taboutput->tab_HTML['withdrawals'] .= "</tr></table><br>";


$taboutput->createTabListView($activetab);
# add the tabbed view the this object
$innerHTML .= $taboutput->innerHTML . print_r($result,1);
return $innerHTML;

   // show Flow Duration
   $debug = 0;
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

         $taboutput->tab_HTML['flow_summary'] .= "Metric $thismetric ( $vars ) on $thispoint data <br>";
         $thisresult = compareRunData($thiselementid, join(',',$runids), $vars, $startdate, $enddate, $doquery, $debug);   
         if ($debug) {
            $taboutput->tab_HTML['flow_summary'] .= $thisresult['query'] . "<br>";   
         }
         $graphout = cova_graphFlowComparison($thisresult, $thismetric, 1, NULL, 2);
         $graphurl = $graphout['img_url'];
         if ($debug) {
            $taboutput->tab_HTML['flow_summary'] .= $graphout['debug'];
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
   foreach ($wd as $rowkey => $thiswd) {
      //$innerHTML .= $rowkey . " - " . print_r($thiswd,1) . " <br>";
      $pid = $thiswd['elementid'];
      $unser = unserializeSingleModelObject($pid);
      $wdobj = $unser['object'];
      $wdlist[$rowkey]['User ID'] = $wdobj->id1;
      $wdlist[$rowkey]['Description'] = $wdobj->name;
      $wdlist[$rowkey]['Action'] = $wdobj->action;
      $wdlist[$rowkey]['Type'] = $wdobj->wdtype;
      if (isset($wdobj->processors['safe_yield_mgy'])) {
         $safeyield_mgy = floatval($wdobj->processors['safe_yield_mgy']->equation);
         $wdlist[$rowkey]['Max Annual'] = number_format($safeyield_mgy / 365.0,2);
      }
      if (isset($wdobj->processors['current_mgy'])) {
         $current_mgy = floatval($wdobj->processors['current_mgy']->equation);
         $wdlist[$rowkey]['Mean MGD'] = number_format( $current_mgy / 365.0,2);
         if (is_object($wdobj->processors['historic_monthly_pct'])) {
            $wdobj->processors['historic_monthly_pct']->formatMatrix();
            foreach ($wdobj->processors['historic_monthly_pct']->matrix_formatted as $monum => $moval) {
               if (isset($momap[$monum])) {
                  $wdlist[$rowkey][$momap[$monum]] = number_format(floatval($moval) * $current_mgy / 30.0,2);
               }
            }
         }
      }
      $wdlist[$rowkey]['MPID'] = $wdobj->id2;
      //$wdrecs .= "$pid - " . print_r($wdobj->processors['current_monthly_discharge']->matrix_formatted,1) . "<br>";
      $t++;
      if ($t > 150) {
         //break;
      }
   }


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

   foreach ($ps as $rowkey => $thisps) {
      //$innerHTML .= $rowkey . " - " . print_r($thisps,1) . " <br>";
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
   $listobject->queryrecords = $wd_sort;
   $listobject->showList();
   $taboutput->tab_HTML['withdrawals'] .= $listobject->outstring;
   $listobject->queryrecords = $ps_sort;
   $listobject->showList();
   $taboutput->tab_HTML['discharges'] .= $listobject->outstring . "<br>Current Discharge output: $psrecs ";

   if ( !($basinshape === FALSE) ) {

      $bioquery = " select sppbova,taxagrp,genus,species,subspecies, common_nam, targetspp, count, waterbody from observed_vdgif_xy where contains(geomfromtext('$wkt',4269), setsrid(the_geom,4269)) and taxagrp = 'Fish' ";
      //$bioquery = " select sppbova,taxagrp,genus,species,subspecies, common_nam, targetspp, count, waterbody, y(the_geom) as lat, x(the_geom) as lon from observed_vdgif_xy where contains(geomfromtext('$wkt',4269), setsrid(the_geom,4269)) ";


      $taboutput->tab_HTML['biology'] .= "Geographic scope: $geoscope, segments: " . print_r($wkt_segs, 1) . "<br>";
   //error_reporting(E_ALL);   
      // USE THIS for analysis grid
      /*
      $form_name = "agridform_$elementid";
      $taboutput->tab_HTML['biology'] .= "<form name='$form_name' id='$form_name'>";

      // query wizard
      $bio_tbl = "($bioquery) as foo";
      $session_db = $aquatic_biodb;
      $qwiz = ag_showAnalysisQueryWizard($formValues, 'observed_vdgif_xy', $form_name);
      $taboutput->tab_HTML['biology'] .= $qwiz['innerHTML'];

      $taboutput->tab_HTML['biology'] .= "<input type='hidden' name='divname' value='agrid_$elementid'>";
      $result = ag_showAnalysisGrid($formValues, $bio_tbl, $form_name);
      //$taboutput->tab_HTML['biology'] .= $result['subquery'] . "<br>";
      $taboutput->tab_HTML['biology'] .= $result['innerHTML'];
      $taboutput->tab_HTML['biology'] .= "</form>";
      */

      // un-comment this for plain old vanilla
      $aquatic_biodb->querystring = $bioquery;
      $aquatic_biodb->performQuery();
      $aquatic_biodb->show = 0;
      $aquatic_biodb->showList();
      $taboutput->tab_HTML['biology'] .= $aquatic_biodb->outstring;

      //$innerHTML .= "QUERY: " . " $bioquery<br>";
   } else {
      $taboutput->tab_HTML['biology'] .= "Shape not returned for " . print_r($segs,1) . "<br>";
   }
   $taboutput->tab_HTML['biology'] .= "<br><a href='./cova_model_infotab.php?elementid=$elementid&geoscope=$alt_scope'>Click here to see records for $alt_scope basin boundaries.</a>";


   // append withdrawal and discharge data from beyond the modeling period
   $wdquery = " select \"YEAR\", round(sum(\"JANUARY\"/31.0)::numeric,2) as jan, ";
   $wdquery .= "   round(sum(\"FEBRUARY\"/28.0)::numeric,2) as feb, ";
   $wdquery .= "   round(sum(\"MARCH\"/31.0)::numeric,2) as mar, ";
   $wdquery .= "   round(sum(\"APRIL\"/30.0)::numeric,2) as apr, ";
   $wdquery .= "   round(sum(\"MAY\"/31.0)::numeric,2) as may, ";
   $wdquery .= "   round(sum(\"JUNE\"/30.0)::numeric,2) as jun, ";
   $wdquery .= "   round(sum(\"JULY\"/31.0)::numeric,2) as jul, ";
   $wdquery .= "   round(sum(\"AUGUST\"/31.0)::numeric,2) as aug, ";
   $wdquery .= "   round(sum(\"SEPTEMBER\"/30.0)::numeric,2) as sep, ";
   $wdquery .= "   round(sum(\"OCTOBER\"/31.0)::numeric,2) as oct, ";
   $wdquery .= "   round(sum(\"NOVEMBER\"/30.0)::numeric,2) as nov, ";
   $wdquery .= "   round(sum(\"DECEMBER\"/31.0)::numeric,2) as dec, ";
   $wdquery .= "   round(sum(\"ANNUAL/365\")::numeric,2) as total_mgd ";
   $wdquery .= " from vwuds_annual_mp_data where contains(geomfromtext('$wkt',4269), setsrid(the_geom,4269)) ";
   $wdquery .= "   and \"ACTION\" = 'WL' ";
   //$wdquery .= "   and \"YEAR\" >= '2001' ";
   // exclude north anna
   $wdquery .= "   and \"MPID\" not in ('380347077472601','380347077472602')";
   $wdquery .= "   and \"CAT_MP\" not in ('PH')";
   $wdquery .= " group by \"YEAR\" ";
   $wdquery .= " order by \"YEAR\" ";
   $vwuds_listobject->querystring = $wdquery;
   $vwuds_listobject->performQuery();
   $vwuds_listobject->show = 0;
   $vwuds_listobject->showList();
   $taboutput->tab_HTML['withdrawals'] .= "Recent reported Withdrawals: <br>" . $vwuds_listobject->outstring;
   // max for each month / year

   $vwuds_listobject->querystring = "select max(jan) as jan, max(feb) as feb, max(mar) as mar, max(apr) as apr, max(may) as may, max(jun) as jun, max(jul) as jul, max(aug) as aug, max(sep) as sep, max(oct) as oct, max(nov) as nov, max(dec) as dec, max(total_mgd) as max_annual from ( $wdquery ) as foo ";
   $vwuds_listobject->performQuery();
   $vwuds_listobject->show = 0;
   $vwuds_listobject->showList();
   $taboutput->tab_HTML['withdrawals'] .= "Max reported Withdrawals: <br>" . $vwuds_listobject->outstring;

   // point sources
   // not yet ready for prime time, because the database is cleaned up in WOOOMM, not in the source
   /*
   $wdquery = " select extract(year from mon_startdate) as \"year\", sum(mean_value) as annual_mgd ";
   $wdquery .= " from vpdes_discharge_no_ms4 where contains(geomfromtext('$wkt',4269), setsrid(the_geom,4269)) ";
   $wdquery .= "   and mon_startdate >= '2001-01-01' ";
   // exclude north anna
   $wdquery .= "   and vpdes_permit_no not in ('VA0052451')";
   $wdquery .= " group by extract(year from mon_startdate) ";
   $wdquery .= " order by extract(year from mon_startdate) ";
   $vpdes_listobject->querystring = $wdquery;
   $vpdes_listobject->performQuery();
   $vpdes_listobject->show = 0;
   $vpdes_listobject->showList();
   $taboutput->tab_HTML['discharges'] .= "Recent reported Discharges: <br>" . $vpdes_listobject->outstring;
   // max for each month / year

   $vpdes_listobject->querystring = "select max(annual_mgd) as max_annual from ( $wdquery ) as foo ";
   $vpdes_listobject->performQuery();
   $vpdes_listobject->show = 0;
   $vpdes_listobject->showList();
   $taboutput->tab_HTML['discharges'] .= "Max reported Discharges: <br>" . $vpdes_listobject->outstring;
   //
   */

   /* RENDER FINAL TABBED OBJECT */
   $taboutput->createTabListView($activetab);
   # add the tabbed view the this object
   $innerHTML .= $taboutput->innerHTML;
   return $innerHTML;
}

?>
