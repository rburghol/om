<html>
<body>
<h3>Water Supply Modeling Demo of Web Hydrology Objects</h3>
<b>Project Site:</b> <a 
href='http://sourceforge.net/projects/npsource/'>http://sourceforge.net/projects/npsource/</a>
<br>
This demo simulates the effects of water withdrawals on the hydrograph of a river below the withdrawal point, as well as projecting the effects of this modified flow regime on instream resources, using Habitat Suitability Indices (this demo has Small Mouth Bass, Rainbow Trout, and Canoeing).<br>
Up to two different sets of withdrawal rules may be specified (withdrawal 1 and 2).  Withdrawals are specified by selecting a stream property, criteria, the value (trigger) of this criteria at which a certain withdrawal (draw) is invoked, and the value of the draw itself.<br>
Stream properties include an upstream inflow, specified by the actual recorded results from a USGS stream gage, and then the physical characteristics of the simulated reach.  These physical characteristics will govern the behaviour of the channel, so they should be chosen carefully.
<?php

#error_reporting(E_ALL);
include("./config.php");
$today = new DateTime();
$tm = $today->format('m');
$td = $today->format('d');
$ty = $today->format('Y');

if ((isset($_POST['submit']))) {
   $draw1 = $_POST['draw1'];
   $trigger1 = $_POST['trigger1'];
   $crit1 = $_POST['crit1'];
   $draw2 = $_POST['draw2'];
   $trigger2 = $_POST['trigger2'];
   $crit2 = $_POST['crit2'];
   $staid = $_POST['staid'];
   $syear = $_POST['syear'];
   $eyear = $_POST['eyear'];
   $smonth = $_POST['smonth'];
   $emonth = $_POST['emonth'];
   $sday = $_POST['sday'];
   $eday = $_POST['eday'];
   $areafact = $_POST['areafact'];
   $rbase = $_POST['rbase'];
   $rlength = $_POST['rlength'];
   $tstep = $_POST['tstep'];
   $slope = $_POST['slope'];
   $Z = $_POST['Z'];
   $n = $_POST['n'];
} else {
   $trigger1 = array('0','0','0','0');
   $draw1 = array(0,0,0,0);
   $crit1 = 'Qin';
   $trigger2 = array('0','0','0','0','0');
   $draw2 = array( 0,0,0,0,0);
   $crit2 = 'Qin';
   $staid = '01633000';
   $syear = '1999';
   $eyear = '1999';
   $smonth = '06';
   $emonth = '10';
   $sday = '01';
   $eday = '01';
   $tstep = 12;
   $Z = 1;
   $n = 0.025;
   $slope = 0.005;
   $areafact = 1.0;
   $rbase = 200.0;
   $rlength = 300000;
}

$critsql = " ( ( select 'Qin' as criteria ) ";
$critsql .= " UNION ( select 'Qout' as criteria ) ";
$critsql .= " UNION ( select 'depth' as criteria ) ) as foo ";

# flow stations which we know have daily data
$stasql = "(select site_no from monitoring_sites where site_type = 1) as foo ";

print("<form action='./demo_hsi.php' method=post>");
print("<table>");
print("<tr>");
print("<td valign=top>");
print("<b>Gage with Flow at Start of Reach:</b><br>");
#showActiveList($listobject, 'staid', $stasql, 'site_no', 'site_no', '', $staid, '', 'site_no', $debug);
showWidthTextField('staid', $staid, 24);
print("<a href='http://waterdata.usgs.gov/va/nwis/current/?type=flow&group_key=huc_cd' target=_new>Click for VA USGS Gages</a>");
print("<br><b>Area Factor (to scale flow at start):</b> ");
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
showSubmitButton('submit', 'Show Time Series', 'Show Time Series');
print("</form>");

if (isset($_POST['submit'])) {

   
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
   
   $flow2 = new timeSeriesInput;
   $flow2->init();
   $flow2->timer = $timer;
   $flow2->name = "James River";
   $flow2->debug = 0;
   $flow2->intmethod = 3; 
   $dataitem = '00060';
   
   
   # gets daily flow values for indicated period
   print("Obtaining Flow Data for station: $staid <br>");
   $site_result = retrieveUSGSData($staid, '', 0, $startdate, $enddate, 1, '', 'rdb', $dataitem);
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
   
   #print_r($flow2->tsvalues);

   
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
   $r1->setStateVar('substrateclass','B');
   $r1->setStateVar('sp_sub_size',4);
   $r1->setStateVar('pct_fines',0.12);
   $r1->setStateVar('pdepth',0.3); # average depth of pool below the mean reach bottom
   $r1->name = 'James River between Cartersville and Richmond';
   # add the USGS flow object as an input to this reach
   $r1->addInput('Qin', 'Qout', $flow2);
   
   # add withdrawal lookup - with date constraints
   $thisop = new lookupObject;
   $thisop->debug = 0;
   $thisop->timer = $timer;
   #$opdates = array('startyear'=>2000, 'startmonth'=>9, 'startday'=>1, 'startweekday'=>'Mon', 'starthour'=>12);
   # calendar day constraint
   #$opdates = array('startday'=>9, 'endday'=>15);
   # week day constraint, i.e., only pump on weekends
   $opdates = array('startweekday'=>6, 'endweekday'=>7);
   $thisop->setUp('Qout', 2, array('0'=>0,'200'=>50,'400'=>100,'600'=>200,'800'=>400,'1000'=>500), 0, 0, $opdates);
   $r1->addOperator('pumpout', $thisop, 0.1);
   # total depth of pools below water surface
   $thisop = new Equation();
   $thisop->equation = 'Qout - pumpout';
   $thisop->debug = 0;
   $thisop->init();
   $r1->addOperator('Qout', $thisop, 0);

   
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
   $res1->setStateVar('Storage',23205);
   
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
   $smb->setStateVar('V5',0.35);
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
   
   $rbt = new HabitatSuitabilityObject();
   $rbt->name = "Rainbow Trout Spawning";
   $rbt->init();
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
   # Rainbow trout adult factors
   $thisop = new lookupObject;
   $thisop->setUp('Vel', 1, array('0.0'=>0.8,'0.5'=>1.0,'2.0'=>1.0, '2.5'=>0.02), 0.02);
   $rbt->addOperator('Vva', $thisop, 0.0);
   # average substrate size during spawning areas, cm
   $thisop = new lookupObject;
   $thisop->setUp('sp_sub_size', 1, array('0.0'=>0.0,'2.0'=>0.0,'3.0'=>0.75,'5.0'=>0.75,'6.0'=>1.0,'7.0'=>1.0, '8.0'=>0.2, '20.0'=>0.1), 0.1);
   $rbt->addOperator('Vsa', $thisop, 0.1);
   # percent fines during spawning
   $thisop = new lookupObject;
   $thisop->setUp('tpdepth', 1, array('0.0'=>0.0,'2.0'=>1.0,'20.0'=>1.0), 1.0);
   $rbt->addOperator('Vda', $thisop, 0.1);
   # compute overall Habitat Suitability Index 
   $thisop = new Equation();
   $thisop->equation = '(Vva * Vsa * Vda)^(1/3)';
   $thisop->init();
   $rbt->addOperator('HSI',  $thisop, 0.2);
   # compute overall Habitat Suitability Index during spawning
   $thisop = new Equation();
   $thisop->equation = '(V5 * V7 * V16)^(1/3)';
   $thisop->init();
   $rbt->addOperator('HSIspawn',  $thisop, 0.2);
   # scale Habitat Suitability Index 
   $thisop = new Equation();
   $thisop->equation = '(HSI * 20000)';
   $thisop->init();
   $rbt->addOperator('HSI20k',  $thisop, 0);
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


   # Habitat Suitabililty Object Canoeing
   $canoe = new HabitatSuitabilityObject();
   $canoe->name = "Recreation: Canoeing";
   $canoe->init();
   $canoe->debug = 0;
   $canoe->timer = $timer;
   # add flow inputs from James to estimate these
   $canoe->addInput('velocity', 'Vout', $r1);
   $canoe->addInput('rdepth', 'depth', $r1);
   # canoeing depth habitat index
   $thisop = new lookupObject;
   $thisop->setUp('rdepth', 1, array('0.0'=>0.0,'0.25'=>0.0,'0.5'=>0.5, '1.5'=>0.75, '2.25'=>1.0), 1.0);
   $canoe->addOperator('V1', $thisop, 0.1);
   # canoeing velocity habitat index
   $thisop = new lookupObject;
   $thisop->debug = 0;
   $thisop->setUp('velocity', 1, array('0.0'=>0.0,'0.2'=>0.0,'0.5'=>1.0, '2.5'=>1.0, '3.05'=>0.2, '5.0'=>0.0), 0.0);
   $canoe->addOperator('V2', $thisop, 0.1);
   
   # compute overall Habitat Suitability Index 
   $thisop = new Equation();
   $thisop->equation = '(V1 * V2)^(1/2)';
   $thisop->init();
   $canoe->addOperator('HSI', $thisop , 0.5);
   # perform equation ordering 
   $canoe->orderOperations();



   # Habitat Suitabililty Object on Fast Generalist
   $ghsi = new HabitatSuitabilityObject();
   $ghsi->name = "Fast Generalist";
   $ghsi->init();
   $ghsi->debug = 0;
   $ghsi->timer = $timer;
   # add flow inputs from James to estimate these
   #$ghsi->addInput('Qin', 'Qin', $r1);
   # uses observed flow
   $ghsi->addInput('Qin', 'Qout', $flow2);
   # WUA versus flow habitat index - fast generalist guild
   $thisop = new lookupObject;
   $thisop->debug = 0;
   $flowwua = readDelimitedFile('wua_fg_01633000.csv');
   $wua = array();
   foreach($flowwua as $thiswua) {
      $f = $thiswua[0];
      $wukey = "'$f'";
      $wua[$f] = $thiswua[1];
   }
   #print_r($wua);
   $thisop->setUp('Qin', 1, $wua, 0.0);
   $ghsi->addOperator('fg', $thisop, 0.0);
   # WUA versus flow habitat index - Pool run guild
   $thisop = new lookupObject;
   $thisop->debug = 0;
   $flowwua = readDelimitedFile('wua_pr_01633000.csv');
   $wua = array();
   foreach($flowwua as $thiswua) {
      $f = $thiswua[0];
      $wukey = "'$f'";
      $wua[$f] = $thiswua[1];
   }
   #print_r($wua);
   $thisop->setUp('Qin', 1, $wua, 0.0);
   $ghsi->addOperator('pr', $thisop, 0.0);
   
   # WUA versus flow habitat index - pool cover guild
   $thisop = new lookupObject;
   $thisop->debug = 0;
   $flowwua = readDelimitedFile('wua_pc_01633000.csv');
   $wua = array();
   foreach($flowwua as $thiswua) {
      $f = $thiswua[0];
      $wukey = "'$f'";
      $wua[$f] = $thiswua[1];
   }
   #print_r($wua);
   $thisop->setUp('Qin', 1, $wua, 0.0);
   $ghsi->addOperator('pc', $thisop, 0.0);
   
   # WUA versus flow habitat index - pool cover guild
   $thisop = new lookupObject;
   $thisop->debug = 0;
   $flowwua = readDelimitedFile('wua_rg_01633000.csv');
   $wua = array();
   foreach($flowwua as $thiswua) {
      $f = $thiswua[0];
      $wukey = "'$f'";
      $wua[$f] = $thiswua[1];
   }
   #print_r($wua);
   $thisop->setUp('Qin', 1, $wua, 0.0);
   $ghsi->addOperator('rg', $thisop, 0.0);
   
   # WUA versus flow habitat index - pool cover guild
   $thisop = new lookupObject;
   $thisop->debug = 0;
   $flowwua = readDelimitedFile('wua_am_01633000.csv');
   $wua = array();
   foreach($flowwua as $thiswua) {
      $f = $thiswua[0];
      $wukey = "'$f'";
      $wua[$f] = $thiswua[1];
   }
   #print_r($wua);
   $thisop->setUp('Qin', 1, $wua, 0.0);
   $ghsi->addOperator('am', $thisop, 0.0);
   
   # perform equation ordering 
   $ghsi->orderOperations();

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
   
   $surface1->addInput('Pin', 'Qout', $precip1);
   
   #$r1->addInput('Qin', $surface1);
   
   $mc = new modelContainer;
   $mc->timer = $timer;
   $mc->debug = 1;
   $mc->addComponent($r1);
   $mc->addComponent($canoe);
   $mc->addComponent($rbt);
   $mc->addComponent($surface1);
   $mc->addComponent($res1);
   $mc->addComponent($ghsi);
   $mc->addComponent($precip1);
   $mc->addComponent($flow2);
   $mc->addComponent($smb);
   $mc->orderOperations();
   
   $thistime = $timer->thistime->format('r');
   print("Executing Simulation start: $thistime <br>");
   # debugging output interval
   $outint = 50;
   for ($i = 1; $i <= $numsteps; $i++) {
      /*
      $timer->step();
      $flow2->step();
      $precip1->step();
      #print("Calculating Surface runoff.<br>");
      $surface1->step();
      #print("Calculating stream flow.<br>");
      $r1->step();
      #error_reporting(E_ALL);
      $res1->step();
      # now evaluate the HSI
      $smb->step();
      $rbt->step();
      $canoe->step();
      $ghsi->step();
      */
      $mc->step();
      $hsi[$smb->getCurrentValue('flow')]['flow'] = number_format($smb->getCurrentValue('flow'],2);
      $hsi[$smb->getCurrentValue('flow')]['HSI'] = $smb->getCurrentValue('HSI'];
      #$hsi[$smb->getCurrentValue('flow')]['flow'] = number_format($smb->getCurrentValue('flow'],2);
      $hsi[$smb->getCurrentValue('flow')]['rbt_HSI'] = $rbt->getCurrentValue('HSI'];
      $hsi[$smb->getCurrentValue('flow')]['canoe_HSI'] = $canoe->getCurrentValue('HSI'];
      $hsi[$smb->getCurrentValue('flow')]['fg_WUA'] = $ghsi->getCurrentValue('fg'];
      $hsi[$smb->getCurrentValue('flow')]['pr_WUA'] = $ghsi->getCurrentValue('pr'];
      $V5 = $rbt->getCurrentValue('V5');
      $V7 = $rbt->getCurrentValue('V7');
      $V16 = $rbt->getCurrentValue('V16');
      if ($rbt->getCurrentValue('HSI') < 0.1) {
         $flow = number_format($rbt->getCurrentValue('flow'),2);
         $si = $rbt->getCurrentValue('HSI');
         $velcm = $rbt->getCurrentValue('Vel_cm');
         $de = $rbt->getCurrentValue('depth');
         if ($debug) {
            print("Flow: $flow, depth: $de, Vel_cm: $velcm, HSI: $si <br>");
            print_r($rbt->state);
            print("<br>");
         }
      }
      $pumping = $r1->getCurrentValue('demand');
      $storage = $r1->getCurrentValue('Storage');
      $outflow = $r1->getCurrentValue('Qout');
      $inflow = $r1->getCurrentValue('Qin');
      $fdepth = $r1->getCurrentValue('depth');
      $ccstorage = $res1->getCurrentValue('Storage');
      $ccspill = $res1->getCurrentValue('Qout');
      $ccin = $res1->getCurrentValue('Qin');
      if (!isset($ccminstore) or ($ccminstore > $ccstorage)) {
         $ccminstore = $ccstorage;
      }
      $induration[$i]['Qin'] = $inflow;
      $outduration[$i]['Qout'] = $outflow;
      $ratingcurve[$fdepth * 12.0]['depth'] = number_format($fdepth * 12.0, 2);
      $ratingcurve[$fdepth * 12.0]['flow'] = $outflow;
      
      $stepdate = $timer->thistime->format('Y-m-d');
      if ( (intval($i/$outint) == ($i / $outint)) 
      #or ($pumping <> $lastpump) 
      ) {
         print("$stepdate ($i / $numsteps) <br> ");
         print("<b>Withdrawal =</b> $pumping <b>@ storage =</b> $storage and <b>inflow/outflow =</b> $inflow/$outflow <br>");
         print("<b>CC Reservoir </b> <b>@ storage = </b> $ccstorage,  <b>inflow/outflow =</b> $ccin/$ccspill <br>");
      }
      $lastpump = $pumping;
      
   }
   $thistime = $timer->thistime->format('r');
   print(" Finished: $thistime <br>");
   print("<hr>");
   sort($induration);
   sort($outduration);
   ksort($ratingcurve);
   ksort($hsi);

   #Low Storage Value
   $ls = number_format($ccminstore, 1, '.', ',');
   print("Minimum storage value in reservoir: $ls <br><br>");

   #print_r($surface1->logtable);
   $ro = number_format($surface1->totalflow, 1, '.', ',');
   print("Total runoff into stream: $ro <br>");
   $rti = number_format($r1->totalinflow, 1, '.', ',');
   print("Total Stream Input: $rti <br>");
   $rtw = number_format($r1->totalwithdrawn, 1, '.', ',');
   print("Total Stream Withdrawals: $rtw <br>");
   $rtf = number_format($r1->totalflow, 1, '.', ',');
   print("Total Stream Discharge: $rtf <br>");
   $storage = number_format($r1->getCurrentValue('Storage'), 1, '.', ',');
   print("Ending Stream Storage: $storage <br>");
   $mb = number_format(100.0*($r1->totalwithdrawn + $r1->totalflow + $storage) / $r1->totalinflow, 4);
   print("Stream Mass-Balance: $mb % <br>");
   #$goutdir = './out';
   #$gouturl = './out';
   $thisgraph['title'] = 'Test Flow Object';
   $thisgraph['xlabel'] = 'Date';
   $thisgraph['ylabel'] = 'Flow (cfs)';
   $thisgraph['y2label'] = 'WUA';
   $thisgraph['gwidth'] = 800;
   $thisgraph['gheight'] = 400;
   $thisgraph['scale'] = 'intlin';
   $thisgraph['labelangle'] = 90;

   $thisgraph['bargraphs'][0]['graphrecs'] = $r1->getLog();
   $thisgraph['bargraphs'][0]['xcol'] = 'thisdate';
   $thisgraph['bargraphs'][0]['ycol'] = 'Qout';
   $thisgraph['bargraphs'][0]['color'] = 'blue';
   $thisgraph['bargraphs'][0]['ylegend'] = 'Observed Flow';

   $thisgraph['bargraphs'][6]['graphrecs'] = $r1->getLog();
   $thisgraph['bargraphs'][6]['xcol'] = 'thisdate';
   $thisgraph['bargraphs'][6]['ycol'] = 'Qin';
   $thisgraph['bargraphs'][6]['color'] = 'red';
   $thisgraph['bargraphs'][6]['ylegend'] = 'Pre Withdrawal Flow';
 
   $thisgraph['bargraphs'][1]['graphrecs'] = $ghsi->getLog();
   $thisgraph['bargraphs'][1]['xcol'] = 'thisdate';
   $thisgraph['bargraphs'][1]['ycol'] = 'pc';
   $thisgraph['bargraphs'][1]['color'] = 'purple';
   $thisgraph['bargraphs'][1]['yaxis'] = 2;
   $thisgraph['bargraphs'][1]['ylegend'] = 'WUA - Pool Cover';
   
   $thisgraph['bargraphs'][2]['graphrecs'] = $ghsi->getLog();
   $thisgraph['bargraphs'][2]['xcol'] = 'thisdate';
   $thisgraph['bargraphs'][2]['ycol'] = 'fg';
   $thisgraph['bargraphs'][2]['color'] = 'orange';
   $thisgraph['bargraphs'][2]['yaxis'] = 2;
   $thisgraph['bargraphs'][2]['ylegend'] = 'WUA - Fast Generalist';
   
   $thisgraph['bargraphs'][3]['graphrecs'] = $ghsi->getLog();
   $thisgraph['bargraphs'][3]['xcol'] = 'thisdate';
   $thisgraph['bargraphs'][3]['ycol'] = 'pr';
   $thisgraph['bargraphs'][3]['color'] = 'yellow';
   $thisgraph['bargraphs'][3]['yaxis'] = 2;
   $thisgraph['bargraphs'][3]['ylegend'] = 'WUA - Pool Run';

   $thisgraph['bargraphs'][4]['graphrecs'] = $ghsi->getLog();
   $thisgraph['bargraphs'][4]['xcol'] = 'thisdate';
   $thisgraph['bargraphs'][4]['ycol'] = 'rg';
   $thisgraph['bargraphs'][4]['color'] = 'green';
   $thisgraph['bargraphs'][4]['yaxis'] = 2;
   $thisgraph['bargraphs'][4]['ylegend'] = 'WUA - Riffle';
 
   $thisgraph['bargraphs'][5]['graphrecs'] = $ghsi->getLog();
   $thisgraph['bargraphs'][5]['xcol'] = 'thisdate';
   $thisgraph['bargraphs'][5]['ycol'] = 'am';
   $thisgraph['bargraphs'][5]['color'] = 'brown';
   $thisgraph['bargraphs'][5]['yaxis'] = 2;
   $thisgraph['bargraphs'][5]['ylegend'] = 'WUA - Algae/Midge';

/*  
   
   $thisgraph['bargraphs'][6]['graphrecs'] = $rbt->getLog();
   $thisgraph['bargraphs'][6]['xcol'] = 'thisdate';
   $thisgraph['bargraphs'][6]['ycol'] = 'HSI20k';
   $thisgraph['bargraphs'][6]['color'] = 'black';
   $thisgraph['bargraphs'][6]['yaxis'] = 2;
   $thisgraph['bargraphs'][6]['ylegend'] = 'HSI * 20000 - Adult Rainbow Trout';
*/

#   $debug = 1;
   $thisimg = showGenericMultiLine($goutdir, $gouturl, $thisgraph, $debug);   
   print("<img src='$thisimg'>");

   $fdgraph = array();
   # flow duration curve
   $fdgraph['title'] = 'Flow Duration';
   $fdgraph['xlabel'] = 'Percent Exceedance';
   $fdgraph['gwidth'] = 600;
   $fdgraph['gheight'] = 400;
   $fdgraph['scale'] = 'linlog';

   $fdgraph['bargraphs'][0]['graphrecs'] = $outduration;
   $fdgraph['bargraphs'][0]['xcol'] = 'time';
   $fdgraph['bargraphs'][0]['ycol'] = 'Qout';
   $fdgraph['bargraphs'][0]['color'] = 'red';
   $fdgraph['bargraphs'][0]['ylegend'] = 'Post-WD';

   $fdgraph['bargraphs'][1]['graphrecs'] = $induration;
   $fdgraph['bargraphs'][1]['xcol'] = 'time';
   $fdgraph['bargraphs'][1]['ycol'] = 'Qin';
   $fdgraph['bargraphs'][1]['color'] = 'blue';
   $fdgraph['bargraphs'][1]['ylegend'] = 'Pre-WD';
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
   #print_r($hsi);
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
   $hsigraph['bargraphs'][2]['graphrecs'] = $hsi;
   $hsigraph['bargraphs'][2]['xcol'] = 'flow';
   $hsigraph['bargraphs'][2]['ycol'] = 'canoe_HSI';
   $hsigraph['bargraphs'][2]['color'] = 'blue';
   $hsigraph['bargraphs'][2]['ylegend'] = $canoe->name;
   $thisimg = showGenericMultiLine($goutdir, $gouturl, $hsigraph, $debug);   
   print("<img src='$thisimg'>");
  
   
   # format for output
   $outarr = nestArraySprintf("\"%s\",%8.2f,%8.2f,%8.2f,%8.2f,%8.2f", $res1->getLog());
   #print_r($outarr);
   $colnames = array(array_keys($res1->getLog()[0]));


   $filename = "reservoir_results.csv";
   putDelimitedFile("$goutdir/$filename",$colnames,",",1,'unix');

   putArrayToFilePlatform("$goutdir/$filename", $outarr,0,'unix');
   
   print("<hr><a href='$gouturl/$filename'>Download CSV of Reservoir Time Series.</a>");
   
   
   # format for output
   $outarr = nestArraySprintf("\"%s\",%8.2f,%8.2f,%8.2f,%8.2f,%8.2f", $r1->getLog());
   #print_r($outarr);
   $colnames = array(array_keys($r1->getLog()[0]));

   $filename = "river_results.csv";
   putDelimitedFile("$goutdir/$filename",$colnames,",",1,'unix');

   putArrayToFilePlatform("$goutdir/$filename", $outarr,0,'unix');
   
   print("<br><a href='$gouturl/$filename'>Download CSV of River Time Series.</a>");
}

?>
