<html>
<?php

include("./config.php");
include("./header.php");

?>

<body>
<h3>Water Supply Modeling Demo of Web Hydrology Objects</h3>
<b>Project Site:</b> <a 
href='http://sourceforge.net/projects/npsource/'>http://sourceforge.net/projects/npsource/</a>
<br>
<?php
$goutdir = './out';
$gouturl = './out';

$today = new DateTime();
$tm = $today->format('m');
$td = $today->format('d');
$ty = $today->format('Y');
#print_r($_POST);
if (count($_POST) > 0) {
   $draw1 = $_POST['draw1'];
   $trigger1 = $_POST['trigger1'];
   $crit1 = $_POST['crit1'];
   $draw2 = $_POST['draw2'];
   $trigger2 = $_POST['trigger2'];
   $crit2 = $_POST['crit2'];
   $syear = $_POST['syear'];
   $eyear = $_POST['eyear'];
   $smonth = $_POST['smonth'];
   $emonth = $_POST['emonth'];
   $sday = $_POST['sday'];
   $eday = $_POST['eday'];
   $staid = $_POST['staid'];
   $areafact = $_POST['areafact'];
   $rbase = $_POST['rbase'];
   $rlength = $_POST['rlength'];
   $tstep = $_POST['tstep'];
   $slope = $_POST['slope'];
   $Z = $_POST['Z'];
   $n = $_POST['n'];
   $huc = $_POST['huc'];
   $rectype = $_POST['rectype'];
   $calibstaid = $_POST['calibstaid'];
} else {
   $trigger1 = array('468','470','1600','3600');
   $draw1 = array(0,468,560,768);
   $crit1 = 'Qin';
   $trigger2 = array('2500','2600','2700','2800','3200');
   $draw2 = array( 0,40,90,140,232);
   $crit2 = 'Qin';
   $syear = '1998';
   $eyear = '1999';
   $smonth = '06';
   $emonth = '03';
   $sday = '01';
   $eday = '01';
   $tstep = 24;
   $Z = 1;
   $n = 0.025;
   $slope = 0.005;
   $areafact = 1.08;
   $rbase = 300.0;
   $rlength = 3000000;
   $staid = '02035000';
   $rectype = 1; # 0 - realtime, 1 = daily 
}

if ((isset($_POST['gages']))) {
   $values = $_POST['gages'];
   $options = '';
}


$critsql = " ( ( select 'Qin' as criteria ) ";
$critsql .= " UNION ( select 'Qout' as criteria ) ";
$critsql .= " UNION ( select 'depth' as criteria ) ) as foo ";

# flow stations which we know have daily data
$stasql = "(select site_no from monitoring_sites where site_type = 1) as foo ";

print("<form action='./demo_huc.php' method=post>");
print("<table>");
print("<tr>");
print("<td valign=top>");
print("<b>HUC ID to model:</b><br>");
showActiveList($listobject, 'huc', 'huc_va', 'huc', 'huc', '', $huc, 'submit()', 'huc', $debug);
if (isset($_POST['huc'])) {
   $sitelist = getSitesHUC($dataitem, $huc, $debug);
   #print("Site List: $sitelist <br>");
   $options = split(',', $sitelist);
   if (isset($_POST['hucsites'])) {
      $hucsites = $_POST['hucsites'];
   }
   showMultiCheckBox('hucsites', $options, $hucsites, ' | ');
}
print("<br><b>Calibration Station:</b> ");
showWidthTextField('calibstaid', $calibstaid, 24);
print("<br><b>Area of HUC (sq. mi.):</b> ");
showWidthTextField('areafact', $areafact, 6);
print("<br><b>Reach Base Width (ft) (affects output hydrograph slightly):</b> ");
showWidthTextField('rbase', $rbase, 6);
print("<br><b>Reach Length (ft) (affects storage, warmup time and stability):</b> ");
showWidthTextField('rlength', $rlength, 12);
print("<br><b>Reach Slope (ft/ft) (affects depth and velocity):</b> ");
showWidthTextField('slope', $slope, 6);
print("<br><b>Side Slope, Z (ft/ft) (affects depth and velocity):</b> ");
showWidthTextField('Z', $Z, 6);
print("<br><b>Manning's n, Roughness):</b> ");
showWidthTextField('n', $n, 6);
print("<br><b>Time Step (hrs):</b> ");
showWidthTextField('tstep', $tstep, 8);
print("<br><b>USGS Data type (0-realtime, 1-daily):</b> ");
showWidthTextField('rectype', $rectype, 4);

print("<br><b>Enter Start Date (YYYY MM DD):</b> ");
showWidthTextField('syear', $syear, 8);
showWidthTextField('smonth', $smonth, 3);
showWidthTextField('sday', $sday, 3);
print("<br> ");
print("<b>Enter End Date (YYYY MM DD):</b> ");
showWidthTextField('eyear', $eyear, 8);
showWidthTextField('emonth', $emonth, 3);
showWidthTextField('eday', $eday, 3);

print("</td>");
print("<td valign=top>");
print("<b>Withdrawal #1:</b><br>");
print("<b>1) </b><i>criteria</i> ");
$carr = array(
   array('criteria'=>'Qin'),
   array('criteria'=>'Qout'),
   array('criteria'=>'depth')
);
showActiveList($carr, "crit1", $critsql, 'criteria', 'criteria', '', $crit1, '', '', $debug);
print("<br>");
   print("<table>");
   for ($i = 0; $i <= 9; $i++) {
      print("<tr>");
      print("<td><i>Trigger</i> ");
      showWidthTextField("trigger1[$i]", $trigger1[$i], 6);
      print("</td><td><i>Draw</i> ");
      showWidthTextField("draw1[$i]", $draw1[$i], 6);
      print("</td>");
      print("</tr>");
   }
   print("</table>");
print("</td>");
print("<td valign=top>");
print("<b>Withdrawal #2:</b><br>");
print("<b>2) </b><i>criteria</i> ");
showActiveList($carr, "crit2", $critsql, 'criteria', 'criteria', '', $crit2, '', '', $debug);
print("<br>");
   print("<table>");
   for ($i = 0; $i <= 9; $i++) {
      print("<tr>");
      print("<td><i>Trigger</i> ");
      showWidthTextField("trigger2[$i]", $trigger2[$i], 6);
      print("</td><td><i>Draw</i> ");
      showWidthTextField("draw2[$i]", $draw2[$i], 6);
      print("</td>");
      print("</tr>");
   }
   print("</table>");
print("</td>");


print("</tr>");
print("</table>");
showSubmitButton('showts', 'Show Time Series', 'Show Time Series');
print("</form>");

if (isset($_POST['showts']) and isset($_POST['hucsites'])) {

   
   if (!checkDate($smonth, $sday, $syear)) {
      print("<b>Error:</b> Start Date, $syear-$smonth-$sday is not valid.");
      die;
   }
   $sdate = new DateTime("$syear-$smonth-$sday");
   
   if (!checkDate($emonth, $eday, $eyear)) {
      print("<b>Error:</b> End Date, $eyear-$emonth-$eday is not valid.");
      die;
   }
   $edate = new DateTime("$eyear-$emonth-$eday");
   
   $su = $sdate->format('U');
   $eu = $edate->format('U');
   $startdate = $sdate->format('Y-m-d');
   $enddate = $edate->format('Y-m-d');
   
   if (!( $eu >= $su)) {
      print("<b>Error:</b>End Date must be >= start date.");
      die;
   }

   #print_r($_POST);
   $total_days = ($eu - $su) / (3600.0 * 24.0);
   #$total_days = 120;   
   $timestep_hrs = $tstep;
   $timestep_sec = 3600 * $timestep_hrs;
   $numsteps = $total_days * 24.0 / $timestep_hrs;
   print("Total Steps: $numsteps <br>");
   
   $timer = new simTimer;
   $timer->init();
   $timer->dt = $timestep_sec;
   $simstart = $startdate;
   $timer->thistime = new dateTime($simstart);

   $r2 = new flowTransformer;
   $r2->name = 'Simulated HUC';
   $r2->timer = $timer;
   $r2->debug = 0;
   $r2->method = 0;
   $r2->state['area'] = $areafact;

   # calibration object 
   $calibflow = new timeSeriesInput;
   $calibflow->init();
   createUSGSTimeSeries($calibflow, $calibstaid, $calibstaid, $startdate, $enddate, '', $rectype, $debug);
   
   $calibflow->timer = $timer;
   $calibflow->debug = 0;
   #print_r($calibflow->tsvalues);

#$staid = $hucsites[0];
   $k = 0;
   foreach (array_values($hucsites) as $staid) {
      # flow input - check area first
      $usgs_result = retrieveUSGSData($staid, $period, $debug, '', '', 3, '', '', '');
      $sitedata = $usgs_result['row_array'][0];
      #print_r($sitedata);
      $dav = $sitedata['drain_area_va'];
      #print("<br>Area = $dav<br>");
      if (($dav > $areafact)) {
         print("Source watershed is larger than target, using back-calculation algorithm to estimate flow inputs.<br>");
         # use reverse flow routing object to back calculate inputs to enable better hydrograph
         $flow[$k] = new reverseFlowObject;
         $flow[$k]->base = $rbase * $dav / $areafact;
         $flow[$k]->length = $rlength * $dav / $areafact;
         $flow[$k]->slope = $slope;
         $flow[$k]->Z = $Z;
         $flow[$k]->n = $n;
         $flow[$k]->debug = 0;
         $flcol = 'Iin';
         
      } else {
         $flow[$k] = new timeSeriesInput;
         $flcol = 'Qout';
      }
      createUSGSTimeSeries($flow[$k], $staid, $staid, $startdate, $enddate, '', $rectype, $debug);
      $flow[$k]->timer = $timer;
      #$flow[$k]->debug = 0;
      #$flow[$k]->state['area'] = 1.0;
      $thisarea = $flow[$k]->state['area'];
      print("Drainage area: $thisarea <br>");
      $thisop = new Equation();
      $thisop->equation = "$flcol / area";
      $thisop->debug = 0;
      $thisop->init();
      $flow[$k]->addOperator('flowpera', $thisop , 0.0);
      $thisop = new Equation();
      $thisop->equation = "area + (0.0 * $flcol)";
      $thisop->debug = 0;
      $thisop->init();
      $flow[$k]->addOperator('activearea', $thisop , 0.0);
      $flow[$k]->orderOperations();
      $r2->addInput('flow', $flcol, $flow[$k]);
      $r2->addInput('flowpera', 'flowpera', $flow[$k]);
      $r2->addInput('activearea', 'activearea', $flow[$k]);
      $k++;
      #print_r($flow[$k]->tsvalues);
   }
   
   
   $r1 = new channelObject;
   $r1->debug = 0;
   $r1->base = $rbase;
   $r1->length = $rlength;
   $r1->slope = $slope;
   $r1->Z = $Z;
   $r1->n = $n;
   $r1->init();
   $r1->timer = $timer;
   # for habitat suitability
   $r1->state['substrateclass'] = 'B';
   $r1->state['sp_sub_size'] = 4;
   $r1->state['pct_fines'] = 0.32;
   $r1->state['pdepth'] = 0.3; # average depth of pool below the mean reach bottom
   $r1->name = 'James River between Cartersville and Richmond';
   # add the USGS flow object as an input to this reach
   #$r1->addInput('Qin', 'Qout', $flow2);
   $r1->addInput('Qin', 'Qout', $r2);

   
   # step demand object, specifices an absolute pumping value
   $w1 = new pumpObject();
   $w1->name = "Richmond City";
   $w1->criteria = $crit1;
   $w1->priority = 0;
   /*
   $w1->withdrawals = array(
      '3600'=>768, 
      '1600'=>560, 
      '470'=>468, 
      '468'=>0
   );*/
   for ($h = 0; $h < count($trigger1); $h++) {
      if ( (strlen($trigger1[$h]) > 0) and (strlen($draw1[$h]) > 0) ) {
         $w1->withdrawals[$trigger1[$h]] = $draw1[$h];
      }
   }
   $w1->init();
   
   # step demand object, specifices an absolute pumping value
   $w2 = new pumpObject();
   $w2->name = "Cumberland Withdrawal";
   $w2->criteria = $crit2;
   $w2->priority = 0;
   /*
   $w2->withdrawals = array(
      '3200'=>232, 
      '2800'=>140,
      '2700'=>90, 
      '2600'=>40, 
      '2500'=>0
   );
   */
   for ($h = 0; $h < count($trigger2); $h++) {
      if ( (strlen($trigger2[$h]) > 0) and (strlen($draw2[$h]) > 0) ) {
         $w2->withdrawals[$trigger2[$h]] = $draw2[$h];
      }
   }
   $w2->init();
   
   # add these withdrawal to the river object
   $r1->addWithdrawalObject($w1);
   $r1->addWithdrawalObject($w2);


   # Cobb's Creek Reservoir
   $res1 = new storageObject();
   $res1->name = "Cobb's Creek Reservoir";
   $res1->maxstorage = 23205;
   $res1->init();
   $res1->timer = $timer;
   # set intially full
   $res1->state['Storage'] = 23205;
   
   # step demand object, specifices an absolute pumping value
   $w3 = new pumpObject();
   $w3->name = "Cumberland Yield";
   $w3->criteria = 'Storage';
   $w3->priority = 0;
   $w3->withdrawals = array(
      '72'=>72
   );
   $w3->init();
   
   # add this demand to Cobb's Creek Reservoir
   $res1->addWithdrawalObject($w3);
   # add the pump feed from the james
   $res1->addInput('Qin', 'Qout', $w2);


   # Habitat Suitabililty Object on James River
   $smb = new HabitatSuitabilityObject();
   $smb->name = "Smallmouth Bass";
   $smb->init();
   $smb->debug = 0;
   $smb->timer = $timer;
   # add flow inputs from James to estimate these
   $smb->addInput('flow', 'Qout', $r1);
   $smb->addInput('substrateclass', 'substrateclass', $r1);
   # add depth lookup for substrate
   $thisop = new lookupObject;
   $thisop->setUp('substrateclass', 0, array('A'=>0.2,'B'=>0.3,'C'=>1.0, 'D'=>0.2), 0.1);
   $smb->addOperator('V1', $thisop, 0.1);
   # percent pools - static
   $thisop = new Equation();
   $thisop->equation = '0.2';
   $thisop->init();
   $smb->addOperator('V2', $thisop , 0.2);
   # average depth of flow
   $smb->addInput('rdepth', 'depth', $r1);
   # average depth of pools
   $smb->addInput('pdepth', 'pdepth', $r1);
   # depth of lakes
   $thisop = new Equation();
   $thisop->equation = '2.5 + rdepth';
   $thisop->init();
   $smb->addOperator('ldepth', $thisop, 2.5);
   # lake depth habitat index
   $thisop = new lookupObject;
   $thisop->setUp('ldepth', 1, array('0.0'=>0.0,'9.0'=>1.0,'10.0'=>1.0, '20.0'=>0.5), 0.1);
   $smb->addOperator('V3', $thisop, 0.1);
   # total depth of pools below water surface
   $thisop = new Equation();
   $thisop->equation = 'pdepth + rdepth';
   $thisop->init();
   $smb->addOperator('tpdepth', $thisop, 0.2);
   # pool depth habitat index
   $thisop = new lookupObject;
   $thisop->setUp('tpdepth', 1, array('0.0'=>0.0,'1.3'=>1.0,'5.0'=>1.0, '8.0'=>0.9), 0.1);
   $smb->addOperator('V4', $thisop, 0.1);
   # percent cover with rocks, logs, other submerged features
   # can also add static variable in this manner
   $smb->state['V5'] = 0.35;
   # compute food availabilty from equation 
   $thisop = new Equation();
   $thisop->equation = '(V1 * V2 * V5)^(1/3)';
   $thisop->init();
   $smb->addOperator('Cf', $thisop , 0.1);
   # compute cover metric 
   $thisop = new Equation();
   $thisop->equation = '(V1 + V2 + V4 + V5)/4';
   $thisop->init();
   $smb->addOperator('Cc', $thisop , 0.1);
   # compute overall Habitat Suitability Index 
   $thisop = new Equation();
   $thisop->equation = '(Cf * Cc)^(1/2)';
   $thisop->init();
   $smb->addOperator('HSI', $thisop , 0.5);
   $smb->orderOperations();
   
   $rbt = new HabitatSuitabilityObject();
   $rbt->name = "Rainbow Trout Spawning";
   $rbt->init();
   $rbt->debug = 0;
   $rbt->timer = $timer;
   # add flow velocity inputs from James to estimate these
   $rbt->addInput('Vel', 'Vout', $r1);
   $rbt->addInput('flow', 'Qout', $r1);
   $rbt->addInput('depth', 'depth', $r1);
   $rbt->addInput('pdepth', 'pdepth', $r1);
   $rbt->addInput('pct_fines', 'pct_fines', $r1);
   $rbt->addInput('sp_sub_size', 'sp_sub_size', $r1);
   # total depth of pools below water surface
   $thisop = new Equation();
   $thisop->equation = 'pdepth + depth';
   $thisop->init();
   $rbt->addOperator('tpdepth', $thisop, 0.2);
   # compute overall Habitat Suitability Index 
   $thisop = new Equation();
   $thisop->equation = '(V5 * V7 * V16)^(1/3)';
   $thisop->init();
   $rbt->addOperator('HSI',  $thisop, 0.2);
   #$rbt->equations['tpdepth']->debug = 1;
   # convert velocity to cm
   # estimate the pool has a velocity equal to 10% of the mean velocity, which makes the pool 
   $thisop = new Equation();
   $thisop->equation = '0.1 * 30.48 * Vel';
   $thisop->init();
   $rbt->addOperator('Vel_cm', $thisop, 2.5);
   # calculate metric based on velocity over spawning areas (during spawming)
   $thisop = new lookupObject;
   $thisop->setUp('Vel_cm', 1, array('0.0'=>0.0,'10'=>0.0,'30.0'=>1.0, '70.0'=>1.0, '90.0'=>0.0), 0.0);
   $rbt->addOperator('V5', $thisop, 0.0);
   # average substrate size during spawning areas, cm
   $thisop = new lookupObject;
   $thisop->setUp('sp_sub_size', 1, array('0.0'=>0.0,'1.5'=>1.0,'6.0'=>1.0, '9.0'=>0.15, '10.0'=>0.1), 0.08);
   $rbt->addOperator('V7', $thisop, 0.08);
   # percent fines during spawning
   $thisop = new lookupObject;
   $thisop->setUp('pct_fines', 1, array('0.0'=>1.0,'0.075'=>1.0,'0.15'=>0.8, '0.28'=>0.22, '0.6'=>0.15), 0.1);
   $rbt->addOperator('V16', $thisop, 0.1);
   # perform equation ordering 
   $rbt->orderOperations();


   /*
   # percent demand object, specifies as percent of flow
   $w1 = new pumpPctObject();
   $w1->name = "Cobb's Creek Withdrawal";
   $w1->criteria = 'Qin';
   $w1->priority = 0;
   $w1->withdrawals = array(
      '4000'=>0.25,
      '3600'=>0.25, 
      '3200'=>0.25, 
      '2800'=>0.25,
      '2700'=>0.25, 
      '2600'=>0.25, 
      '2500'=>0.25, 
      '2400'=>0.25, 
      '400'=>0.1, 
      '0'=>0.0
   );
   $w1->init();
   $w1->debug = 0;
   */
   
   print_r($r1->withdrawals["Cobb's Creek Withdrawal"]['withdrawals']);
   
   $precip1 = new timeSeriesInput;
   $precip1->init();
   $precip1->name = 'Atmospheric Deposition';
   $precip1->timer = $timer;
   #$ts1->debug = 1;

   $surface1 = new surfaceObject;
   $surface1->debug = 0;
   $surface1->name = 'Land Area Runoff';
   $surface1->slope = 0.03;
   $surface1->pct_sand = 45;
   $surface1->pct_clay = 5;
   $surface1->init();
   print(" Ksat = $surface1->ksat, Wiltp = $surface1->wiltp, FC = $surface1->fc, ThetaSat = $surface1->thetasat, Sav = $surface1->Sav <br>");
   $surface1->timer = $timer;
   
   $tstep = new DateTime($simstart);
   $surge = 00;
   $baseflow = 100;
   $storm = 2.0;
   $q = $baseflow;
   $p = 0.0;
/*
   for ($i = 1;$i <= $numsteps; $i++) {
      $precip1->addValue($tstep->format('r'), 'precip', $p);
      $tstep->modify("$timer->dt seconds");
      if ( ($i > 10) and ($i <= 50) ) {
         $q += $surge;
      } else {
         if ( ($i > 50) and ($i <= 90) ) {
            $q -= $surge;
         } else {
            $q = $baseflow;
         }
      }
      if ( ($i > 5) and ($i <= 40) ) {
         $p = $storm;
      } else {
         $p = 0.0;
      }
   }
*/

   $surface1->addInput('Pin', 'Qout', $precip1);
   
   #$r1->addInput('Qin', $surface1);
   
   $repint = 100;
   
   $thistime = $timer->thistime->format('r');
   print("Executing Simulation start: $thistime <br>");
   for ($i = 1; $i <= $numsteps; $i++) {
      $timer->step();
      for ($d = 0; $d < count($flow); $d++) {
         #print("$d - <br>");
         #print("Stepping $flow[$d]->name <br>");
         if ($i == 1) {
            $flow[$d]->debug = 0;
         } else {
            $flow[$d]->debug = 0;
         }
         $flow[$d]->step();
         $revout = number_format($flow[$d]->state['Qold'],2);
         $revin = number_format($flow[$d]->state['Iold'],2);
      }
      $calibflow->step();
      $precip1->step();
      #print("Calculating Surface runoff.<br>");
      $surface1->step();
      #print("Calculating stream flow.<br>");
      $r1->step();
      # flow transformer test
      $r2->step();
      #error_reporting(E_ALL);
      $res1->step();
      # now evaluate the HSI
      $smb->step();
      $rbt->step();
      $hsi[$smb->state['flow']]['flow'] = number_format($smb->state['flow'],2);
      $hsi[$smb->state['flow']]['HSI'] = $smb->state['HSI'];
      #$hsi[$smb->state['flow']]['flow'] = number_format($smb->state['flow'],2);
      $hsi[$smb->state['flow']]['rbt_HSI'] = $rbt->state['HSI'];
      if ($rbt->state['HSI'] < 0.1) {
         $rflow = number_format($rbt->state['flow'],2);
         $si = $rbt->state['HSI'];
         $velcm = $rbt->state['Vel_cm'];
         $de = $rbt->state['depth'];
         if ($debug) {
            print("Flow: $rflow, depth: $de, Vel_cm: $velcm, HSI: $si <br>");
            print_r($rbt->state);
            print("<br>");
         }
      }
      $pumping = $r1->state['demand'];
      $storage = $r1->state['Storage'];
      $outflow = $r1->state['Qout'];
      $calib = $calibflow->state['Qout'];
      $calibflow->totalflow += $calib * $calibflow->timer->dt;
      $inflow = $r1->state['Qin'];
      $fdepth = $r1->state['depth'];
      $ccstorage = $res1->state['Storage'];
      $ccspill = $res1->state['Qout'];
      $ccin = $res1->state['Qin'];
      if (!isset($ccminstore) or ($ccminstore > $ccstorage)) {
         $ccminstore = $ccstorage;
      }
      $calibduration[$i]['Qout'] = $calib;
      $simduration[$i]['Qout'] = $outflow;
      $ratingcurve[$fdepth * 12.0]['depth'] = number_format($fdepth * 12.0, 2);
      $ratingcurve[$fdepth * 12.0]['flow'] = $outflow;
      
      $stepdate = $timer->thistime->format('Y-m-d');
      if ( (intval($i/$repint) == ($i / $repint)) 
      #or ($pumping <> $lastpump) 
      ) {
         print("$stepdate ($i / $numsteps) <br> ");
         print("<b>Withdrawal =</b> $pumping <b>@ storage =</b> $storage and <b>inflow/outflow =</b> $inflow/$outflow <br>");
         print("<b>CC Reservoir </b> <b>@ storage = </b> $ccstorage,  <b>inflow/outflow =</b> $ccin/$ccspill <br>");
         print("<b>Flow reversal: </b> <b>inflow/outflow =</b> $revin/$revout <br>");
      }
      $lastpump = $pumping;
      
   }
   $thistime = $timer->thistime->format('r');
   print(" Finished: $thistime <br>");
   print("<hr>");
   sort($calibduration);
   sort($simduration);
   ksort($ratingcurve);
   ksort($hsi);

   #Low Storage Value
   $ls = number_format($ccminstore, 1, '.', ',');
   print("Minimum storage value in reservoir: $ls <br><br>");

   #print_r($surface1->logtable);
   $rtf = number_format($r1->totalflow, 1, '.', ',');
   print("Total Simulated Discharge: $rtf <br>");
   $obsf = number_format($calibflow->totalflow, 1, '.', ',');
   print("Total Observed Discharge: $storage <br>");
   $mb = number_format(100.0*($r1->totalwithdrawn + $r1->totalflow + $storage) / $r1->totalinflow, 4);
   print("Stream Mass-Balance: $mb % <br>");
   $thisgraph['title'] = 'Test Flow Object';
   $thisgraph['xlabel'] = 'Time';
   $thisgraph['gwidth'] = 800;
   $thisgraph['gheight'] = 400;
   $thisgraph['scale'] = 'intlin';

   $thisgraph['bargraphs'][0]['graphrecs'] = $r1->logtable;
   $thisgraph['bargraphs'][0]['xcol'] = 'time';
   $thisgraph['bargraphs'][0]['ycol'] = 'Qout';
   $thisgraph['bargraphs'][0]['color'] = 'red';
   $thisgraph['bargraphs'][0]['ylegend'] = 'Simulated Flow';

   $thisgraph['bargraphs'][2]['graphrecs'] = $calibflow->logtable;
   $thisgraph['bargraphs'][2]['xcol'] = 'time';
   $thisgraph['bargraphs'][2]['ycol'] = 'Qout';
   $thisgraph['bargraphs'][2]['color'] = 'green';
   $thisgraph['bargraphs'][2]['ylegend'] = 'Actual Flow';

   $m = 3;
   foreach ($flow as $thissource) {
      $thisgraph['bargraphs'][$m]['graphrecs'] = $thissource->logtable;
      $thisgraph['bargraphs'][$m]['xcol'] = 'thisdate';
      $thisgraph['bargraphs'][$m]['ycol'] = 'Qout';
      $thisgraph['bargraphs'][$m]['color'] = 'orange';
      $thisgraph['bargraphs'][$m]['ylegend'] = 'Source ' . $thissource->name;
      $m++;
   }

   $thisimg = showGenericMultiLine($goutdir, $gouturl, $thisgraph, $debug);   
   print("<img src='$thisimg'>");

   $fdgraph = array();
   # flow duration curve
   $fdgraph['title'] = 'Flow Duration';
   $fdgraph['xlabel'] = 'Percent Exceedance';
   $fdgraph['gwidth'] = 600;
   $fdgraph['gheight'] = 400;
   $fdgraph['scale'] = 'linlog';

   $fdgraph['bargraphs'][0]['graphrecs'] = $simduration;
   $fdgraph['bargraphs'][0]['xcol'] = 'time';
   $fdgraph['bargraphs'][0]['ycol'] = 'Qout';
   $fdgraph['bargraphs'][0]['color'] = 'red';
   $fdgraph['bargraphs'][0]['ylegend'] = 'Synthetic Flow';

   $fdgraph['bargraphs'][1]['graphrecs'] = $calibduration;
   $fdgraph['bargraphs'][1]['xcol'] = 'time';
   $fdgraph['bargraphs'][1]['ycol'] = 'Qout';
   $fdgraph['bargraphs'][1]['color'] = 'blue';
   $fdgraph['bargraphs'][1]['ylegend'] = 'Actual Flow';
   $thisimg = showGenericMultiLine($goutdir, $gouturl, $fdgraph, $debug);   
   print("<img src='$thisimg'>");
   
   # graph rating curve
   $rcgraph = array();
   $rcgraph['title'] = 'Stage vs. Discharge';
   $rcgraph['labelangle'] = 90;
   $rcgraph['xlabel'] = 'Flow Depth (in)';
   $rcgraph['gwidth'] = 600;
   $rcgraph['gheight'] = 400;
   $rcgraph['scale'] = 'linlin';
   $rcgraph['bargraphs'][0]['graphrecs'] = $ratingcurve;
   $rcgraph['bargraphs'][0]['xcol'] = 'depth';
   $rcgraph['bargraphs'][0]['ycol'] = 'flow';
   $rcgraph['bargraphs'][0]['color'] = 'blue';
   $rcgraph['bargraphs'][0]['ylegend'] = 'Discharge (cfs)';
   $thisimg = showGenericMultiLine($goutdir, $gouturl, $rcgraph, $debug);   
   print("<img src='$thisimg'>");
   
   # graph HSI
   $hsigraph = array();
   # flow duration curve
   $hsigraph['title'] = 'HSI';
   $hsigraph['labelangle'] = 90;
   $hsigraph['xlabel'] = 'Flow';
   $hsigraph['gwidth'] = 600;
   $hsigraph['gheight'] = 400;
   $hsigraph['scale'] = 'linlin';
   $hsigraph['bargraphs'][0]['graphrecs'] = $hsi;
   $hsigraph['bargraphs'][0]['xcol'] = 'flow';
   $hsigraph['bargraphs'][0]['ycol'] = 'HSI';
   $hsigraph['bargraphs'][0]['color'] = 'red';
   $hsigraph['bargraphs'][0]['ylegend'] = $smb->name;
   $hsigraph['bargraphs'][1]['graphrecs'] = $hsi;
   $hsigraph['bargraphs'][1]['xcol'] = 'flow';
   $hsigraph['bargraphs'][1]['ycol'] = 'rbt_HSI';
   $hsigraph['bargraphs'][1]['color'] = 'green';
   $hsigraph['bargraphs'][1]['ylegend'] = $rbt->name;
   $thisimg = showGenericMultiLine($goutdir, $gouturl, $hsigraph, $debug);   
   print("<img src='$thisimg'>");
   
   
   # format for output
   $outarr = nestArraySprintf("\"%s\",%8.2f,%8.2f,%8.2f,%8.2f,%8.2f", $res1->logtable);
   #print_r($outarr);
   $colnames = array(array_keys($res1->logtable[0]));


   $filename = "reservoir_results.csv";
   putDelimitedFile("$goutdir/$filename",$colnames,",",1,'unix');

   putArrayToFilePlatform("$goutdir/$filename", $outarr,0,'unix');
   
   print("<hr><a href='$gouturl/$filename'>Download CSV of Reservoir Time Series.</a>");
   
   
   # format for output
   $outarr = nestArraySprintf("\"%s\",%8.2f,%8.2f,%8.2f,%8.2f,%8.2f", $r1->logtable);
   #print_r($outarr);
   $colnames = array(array_keys($r1->logtable[0]));

   $filename = "river_results.csv";
   putDelimitedFile("$goutdir/$filename",$colnames,",",1,'unix');

   putArrayToFilePlatform("$goutdir/$filename", $outarr,0,'unix');
   
   print("<br><a href='$gouturl/$filename'>Download CSV of River Time Series.</a>");
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
