<?php
  
function flowCreateForm($formValues) {
   
   global $listobject, $projectid;
   $innerHTML = '';

   $goutdir = './out';
   $gouturl = './out';

   $today = new DateTime();
   $tm = $today->format('m');
   $td = $today->format('d');
   $ty = $today->format('Y');
   #print_r($formValues);
   if (isset($formValues['projectid'])) {
      $projectid = $formValues['projectid'];
   }
   $debug = 0;
   if (isset($formValues['seglist'])) {
      $seglist = $formValues['seglist'];
   }
   if (isset($formValues['actiontype'])) {
      $actiontype = $formValues['actiontype'];
      $syear = $formValues['syear'];
      $eyear = $formValues['eyear'];
      $smonth = $formValues['smonth'];
      $emonth = $formValues['emonth'];
      $sday = $formValues['sday'];
      $eday = $formValues['eday'];
      $areafact = $formValues['areafact'];
      $tstep = $formValues['tstep'];
      $whichlist = $formValues['whichlist'];
      $huc = $formValues['huc'];
      $rectype = $formValues['rectype'];
      $stationcsv = $formValues['stationcsv'];
   } else {
      $syear = '1998';
      $eyear = '1999';
      $smonth = '06';
      $emonth = '03';
      $sday = '01';
      $eday = '01';
      $tstep = 24;
      $areafact = 1.0;
      $rectype = 1; # 0 - realtime, 1 = daily 
      $stationcsv = '';
      $huc = '';
      $actiontype = 'createflow';
      $whichlist = 'usews';
   }
   $dataitem = '00060';

   if ((isset($formValues['gages']))) {
      $values = $formValues['gages'];
      $options = '';
   }


   $critsql = " ( ( select 'Qin' as criteria ) ";
   $critsql .= " UNION ( select 'Qout' as criteria ) ";
   $critsql .= " UNION ( select 'depth' as criteria ) ) as foo ";

   # flow stations which we know have daily data
   $stasql = "(select site_no from monitoring_sites where site_type = 1) as foo ";

   $innerHTML .= "<form name='createflow' id='createflow'>";
   $innerHTML .= "<table>";
   $innerHTML .= "<tr>";
   $innerHTML .= "<td valign=top>";
   
   $innerHTML .= showRadioButton('whichlist', 'usews', $whichlist, '', 1);
   $innerHTML .= "<b>Select a Gage ID within this areas boundaries:</b><br>";
   if (strlen($formValues['huc']) > 0) {
      $hucsitelist = getSitesHUC($dataitem, $huc, $debug);
   }
   if (strlen($seglist) > 0) {
      $sscond = " b.subshedid in ( $seglist )";
   } else {
      $sscond = "(1 = 1)";
   }
   # show spatially contained gages
   if (isset($formValues['wssites'])) {
      $wssites = $formValues['wssites'];
   } else {
      $wssites = array();
   }
      
   $listobject->querystring = "  select trim(trailing ',' from concat_agg(a.pointname || ',')) as sitelist ";
   $listobject->querystring .= " from proj_points as a, proj_subsheds as b ";
   $listobject->querystring .= " where a.projectid = $projectid ";
   # screens for flow stations
   $listobject->querystring .= "    and a.pointtype = 1 ";
   $listobject->querystring .= "    and $sscond ";
   $listobject->querystring .= "    and b.projectid = $projectid ";
   $listobject->querystring .= "    and contains(b.the_geom, a.the_geom) ";
   $listobject->performQuery();
   $sitelist = $listobject->getRecordValue(1,'sitelist');
   #$innerHTML .= "Site List: $sitelist <br>";
   $options = split(',', $sitelist);
   #$innerHTML .= print_r($options,1) . "<br>";
   if (count($options) > 0) {
      $innerHTML .= showMultiCheckBox('wssites', $options, $wssites, ' | ', '', 1);
   }
   
   $innerHTML .= "<br>" . showRadioButton('whichlist', 'usehuc', $whichlist, '', 1);
   $innerHTML .= "<b>Or, Select a HUC ID to get a list of stations:</b><br>";
   $innerHTML .= showActiveList($listobject, 'huc', 'huc_va', 'huc', 'huc', '', $huc, "xajax_showCreateFlowForm(xajax.getFormValues(\"createflow\"))", 'huc', $debug, 1);
      
   if (isset($formValues['hucsites'])) {
      $hucsites = $formValues['hucsites'];
   } else {
      $hucsites = array();
   }
   $options = split(',', $hucsitelist);
   if (count($options) > 0) {
      $innerHTML .= "<br>" . showMultiCheckBox('hucsites', $options, $hucsites, ' | ', '', 1);
   }
   if (!isset($formValues['areafact'])) {
      # select an initial value for this, converted from sq meters to sq miles
      $listobject->querystring = "  select sum(b.area_sqm) as area_sqm ";
      $listobject->querystring .= " from proj_subsheds as b ";
      $listobject->querystring .= " where b.projectid = $projectid ";
      $listobject->querystring .= "    and $sscond ";
      $listobject->performQuery();
      $area_sqm = $listobject->getRecordValue(1,'area_sqm');
      $areafact = number_format( $area_sqm * 0.000000386102159, 2, '.', '');
   }


   $innerHTML .= "<br>" . showRadioButton('whichlist', 'uselist', $whichlist, '', 1);
   $innerHTML .= "<b>Or, enter a comma seperated list of station IDs:</b>";
   $innerHTML .= showWidthTextField('stationcsv', $stationcsv, 32, '', 1);
   $innerHTML .= "<br><b>Area of HUC (sq. mi.):</b> ";
   $innerHTML .= showWidthTextField('areafact', $areafact, 6, '', 1);
   $innerHTML .= "<br><b>Time Step (hrs):</b> ";
   $innerHTML .= showWidthTextField('tstep', $tstep, 8, '', 1);
   $innerHTML .= "<br><b>USGS Data type (0-realtime, 1-daily):</b> ";
   $innerHTML .= showWidthTextField('rectype', $rectype, 4, '', 1);

   $innerHTML .= "<br><b>Enter Start Date (YYYY MM DD):</b> ";
   $innerHTML .= showWidthTextField('syear', $syear, 8, '', 1);
   $innerHTML .= showWidthTextField('smonth', $smonth, 3, '', 1);
   $innerHTML .= showWidthTextField('sday', $sday, 3, '', 1);
   $innerHTML .= "<br> ";
   $innerHTML .= "<b>Enter End Date (YYYY MM DD):</b> ";
   $innerHTML .= showWidthTextField('eyear', $eyear, 8, '', 1);
   $innerHTML .= showWidthTextField('emonth', $emonth, 3, '', 1);
   $innerHTML .= showWidthTextField('eday', $eday, 3, '', 1);
   $innerHTML .= showHiddenField('actiontype', $actiontype, 1);
   $innerHTML .= showHiddenField('projectid', $projectid, 1);
   $innerHTML .= showHiddenField('seglist', $seglist, 1);

   $innerHTML .= "</td>";

   $innerHTML .= "</tr>";
   $innerHTML .= "<tr>";
   $innerHTML .= "<td valign=top>";
   $innerHTML .= showGenericButton('showts', 'Create Time Series', "xajax_doCreateFlow(xajax.getFormValues(\"createflow\"))", 1);
   $innerHTML .= "</td>";

   $innerHTML .= "</tr>";
   $innerHTML .= "</table>";
   $innerHTML .= "</form>";
   
   return $innerHTML;
   
}

function createSyntheticFlow($formValues) {
   
   global $listobject, $goutdir, $gouturl, $outdir, $outurl;
   
   $debug = 0;
   $innerHTML = '';
   $innerHTML .= "Beginning Simulation of flow.<br>";

   $actiontype = $formValues['actiontype'];
   $syear = $formValues['syear'];
   $eyear = $formValues['eyear'];
   $smonth = $formValues['smonth'];
   $emonth = $formValues['emonth'];
   $sday = $formValues['sday'];
   $eday = $formValues['eday'];
   $areafact = $formValues['areafact'];
   $tstep = $formValues['tstep'];
   $huc = $formValues['huc'];
   $rectype = $formValues['rectype'];
   $stationcsv = $formValues['stationcsv'];
   $hucsites = $formValues['hucsites'];
   $wssites = $formValues['wssites'];
   $projectid = $formValues['projectid'];
   $dataitem = '00060';
   $whichlist = $formValues['whichlist'];
   
   $sourcegages = array();

   
   if (!checkDate($smonth, $sday, $syear)) {
      $innerHTML .= "<b>Error:</b> Start Date, $syear-$smonth-$sday is not valid.";
      return $innerHTML;
   }
   $sdate = new DateTime("$syear-$smonth-$sday");

   if (!checkDate($emonth, $eday, $eyear)) {
      $innerHTML .= "<b>Error:</b> End Date, $eyear-$emonth-$eday is not valid.";
      return $innerHTML;
   }
   $edate = new DateTime("$eyear-$emonth-$eday");

   $su = $sdate->format('U');
   $eu = $edate->format('U');
   $startdate = $sdate->format('Y-m-d');
   $enddate = $edate->format('Y-m-d');

   if (!( $eu >= $su)) {
      $innerHTML .= "<b>Error:</b>End Date must be >= start date.";
      return $innerHTML;
   }

   switch ($whichlist) {
      case 'uselist':
         $stationar = array();
         $csv = split(',', $stationcsv);
         foreach ($csv as $thissta) {
            array_push($stationar, ltrim(rtrim($thissta)));
         }
         $sourcegages = $stationar;
      break;
      
      case 'usehuc':
         if (is_array($hucsites)) {
            $sourcegages = array_values($hucsites);
         }
      break;
      
      case 'usews':
         if (is_array($wssites)) {
            $sourcegages = array_values($wssites);
         }
      break;
   }

   #print_r($formValues);
   #$debug = 1;
   if (count($sourcegages) == 0) {
      $innerHTML .= "<b>Error: </b>You must specify at least one gage to run this routine.<br>";
      return;
   }
   #print_r($sourcegages);
   $synthflow = createSyntheticFlowFromUSGS($sourcegages, $rectype, $areafact, $startdate, $enddate, $tstep, 1, $debug);

   $innerHTML .= $synthflow['innerHTML'];
   $robject = $synthflow['robject'];
   $inflows = $synthflow['inflows'];
   
   # format for output
   $outarr = nestArraySprintf("\"%s\",%8.2f", $synthflow['robject']->logtable);
   #print_r($outarr);
   $colnames = array(array_keys($synthflow['robject']->logtable[0]));

   $filename = "river_results.csv";
   putDelimitedFile("$outdir/$filename",$colnames,",",1,'unix');

   putArrayToFilePlatform("$outdir/$filename", $outarr,0,'unix');
   

   $innerHTML .= "<br><a href='$outurl/$filename'>Download CSV of Synthetic Flow Time Series.</a><br>";
   $thisgraph['title'] = 'Synthetic Hydrograph';
   $thisgraph['xlabel'] = 'Time';
   $thisgraph['gwidth'] = 600;
   $thisgraph['gheight'] = 300;
   $thisgraph['scale'] = 'intlin';

   $thisgraph['bargraphs'][0]['graphrecs'] = $robject->logtable;
   $thisgraph['bargraphs'][0]['xcol'] = 'time';
   $thisgraph['bargraphs'][0]['ycol'] = 'Qout';
   $thisgraph['bargraphs'][0]['color'] = 'red';
   $thisgraph['bargraphs'][0]['ylegend'] = 'Simulated Flow';

   $m = 3;
   foreach ($inflows as $thissource) {
      $thisgraph['bargraphs'][$m]['graphrecs'] = $thissource->logtable;
      $thisgraph['bargraphs'][$m]['xcol'] = 'thisdate';
      $thisgraph['bargraphs'][$m]['ycol'] = 'Qout';
      $thisgraph['bargraphs'][$m]['color'] = 'orange';
      $thisgraph['bargraphs'][$m]['ylegend'] = 'Source ' . $thissource->name;
      $m++;
   }

   $thisimg = showGenericMultiLine($goutdir, $gouturl, $thisgraph, $debug);   
   $innerHTML .= "<img src='$thisimg'>";  
   return $innerHTML;
}


function getContainedGages($listobject, $huc, $debug) {
   $listobject->querystring = "  select site_no ";
   $listobject->querystring .= " from monitoring_sites as a, hucva_dd as b ";
   $listobject->querystring .= " where contains(b.the_geom, a.the_geom) ";
   if ($debug) {
      print("$listobject->querystring ; <br>");
   }
   $listobject->performQuery();
   
   $gagelist = array();
   foreach ($listobject->queryrecords as $thisrec) {
      array_push($gagelist, $thisrec['site_no']);
   }
   
   return $gagelist;
}



function createUSGSTimeSeriesHUC($wbname, $sitelist, $startdate, $enddate, $weightmethod, $debug) {
   
   # part of modeling widgets, expects lib_hydrology.php, and lib_usgs.php to be included
   # creates a time series object, and returns it
   $flow2 = new timeSeriesInput;
   $flow2->init();
   $flow2->name = $wbname;
   $dataitem = '00060';
   
   $sitelist = getSitesHUC($dataitem, $huc, $debug);
   
   # gets daily flow values for indicated period
   # in order to weight this, without using postGIS/postgreSQL, we can create separate objects
   # insert their time and value, 
   # then manually iterate through looking for matches on the time field.  When we get a match 
   # we use it, otherwise, we discard it.
   print("Obtaining Flow Data for station: $staid <br>");
   $site_result = retrieveUSGSData($sitelist, '', 0, $startdate, $enddate, 1, '', 'rdb', $dataitem, '', '', '1', "huc_cd=$huc");
   $gagedata = $site_result['row_array'];
   $thisno = $gagedata[0]['site_no'];
   foreach ($gagedata as $thisdata) {
      $thisdate = new DateTime($thisdata['datetime']);
      $ts = $thisdate->format('r');
      $thisflag = '';
      # default to missing
      $thisflow = '0.0';
      foreach (array_keys($thisdata) as $thiscol) {
         if (substr_count($thiscol, $dataitem)) {
            # this is a flow related column, check if it is a flag or data
            if (!substr_count($thiscol, 'cd')) {
               # must be a flow value
               if ($thisflow <> '') {
                  $thisflow = $thisdata[$thiscol];
               } else {
                  $thisflow = '0.0';
               }
            }
         }
      }
      # multiply by area factor to adjust for area factor at inlet
      $flow2->addValue($ts, 'Qout', $areafact * $thisflow);
   }
   
   return $flow2;
}
?>