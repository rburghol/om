<html>

<?php
#$debug = 1;
#########################################
# Process Form Variable Inputs
#########################################

if (isset($_GET['projectid'])) {
   $actiontype = $_GET['actiontype'];
   $projectid = $_GET['projectid'];
   $currentgroup = $_GET['currentgroup'];
   $scenarioid = $_GET['scenarioid'];
   $thisyear = $_GET['thisyear'];
   $lreditlist = $_GET['lreditlist'];
   $gbIsHTMLMode = $_GET['gbIsHTMLMode'];
   $currentmodel = '';
}


if (isset($_POST['projectid'])) {
   $actiontype = $_POST['actiontype'];
   $projectid = $_POST['projectid'];
   $pollutanttype = $_POST['pollutanttype'];
   $currentgroup = $_POST['currentgroup'];
   $lastgroup = $_POST['lastgroup'];
   $scenarioid = $_POST['scenarioid'];
   $thisyear = $_POST['thisyear'];
   $theseyears = $_POST['theseyears'];
   $lastyear = $_POST['lastyear'];
   $typeid = $_POST['typeid'];
   $incoords = $_POST['INPUT_COORD'];
   $bmpname = $_POST['bmpname'];
   $lreditlist = $_POST['lreditlist'];
   $graphhist = $_POST['graphhist'];
   $landuses = $_POST['landuses'];
   $function = $_POST['function'];
   $srcyears = $_POST['srcyears'];
   $targetyears = $_POST['targetyears'];
   $function = $_POST['function'];
   $viewyear = $_POST['viewyear'];
   $modelscen = $_POST['modelscen'];
   $constit = $_POST['constit'];
   $gbIsHTMLMode = $_POST['gbIsHTMLMode'];
   $graphseplu = $_POST['graphseplu'];
   $currentmodel = $_POST['currentmodel'];
   if (is_array($landuses)) {
      $selus = join(",", $landuses);
   } else {
      $selus = '';
   }
}


$invars = $_POST;
#print_r($invars);
if (isset($invars['TOOL']) ) {
   $tool = $invars['TOOL'];
   $invars['tool'] = $tool;
} else {
   $tool = $invars['tool'];
   $invars['TOOL'] = $tool;
}
#########################################
# END - Process Form Variable Inputs
#########################################


#########################################
# Call The Map Interface Header File
# _______________________________________
# THIS HEADER FILE DOES THE FOLLOWING:
# 1. Initialize libraries and functions
# 2. Initialize map basics
# 3. First, check for actiontype over-rides
# 4. Do a preliminary rendering of the map
# this will process zooms and re-centers
# and queries
# 5. Check to see if we have a select query
# If so, save the list segments in a hidden form
# variable so that future actions will
# maintain the selected segments
#########################################
print("<head>");
include("./medit_header.php");
print("</head>");
print("<body bgcolor=ffffff onload=\"init()\">");

#########################################
# END - Call Header
#########################################


#########################################
# Custom Button To change to new BMP
#########################################
# check to see if the users pressed the button to Refresh Land Use/Change Function
$ed = $_POST['refreshlanduse'];
if (strlen($ed) > 0) {
   $actiontype = 'editlanduse';
}

# check to see if the users pressed the button to Apply Extrapolation
$ae = $_POST['applyextrapolation'];
if (strlen($ae) > 0) {
   $actiontype = 'applyextrapolation';
}

# check to see if the users pressed the button to Apply Extrapolation
$pi = $_POST['plotinout'];
if (strlen($pi) > 0) {
   $actiontype = 'plotinout';
}

# check to see if the users pressed the button to Apply Extrapolation
$du = $_POST['doupdate'];
if (strlen($du) > 0) {
   $doupdate = 1;
}

#########################################
# END actiontype over-rides
#########################################

#########################################
# Now, process actions
#########################################
switch ($actiontype) {
   case 'update':
      $as = $adminsetuparray['lrsegedit'];
      print("Editing Land Uses <br>");
      $formob = processMultiFormVars($listobject, $invars, $as, 0,$debug, -1);
      $udrecs = $formob['mapqueries'];
      foreach ($udrecs as $thisrec) {
         $vals = $thisrec[0];
         $pkval = $thisrec[4];
         $invals = $thisrec[6];

         $usql = "update scen_lrsegs set $vals where oid = $pkval";
         print("$usql <br>");
         $listobject->querystring = $usql;
         $listobject->performQuery();
      }

   break;

   case 'editbmps':
   if ( !(count(split(",",$incoords)) >= 2) and ($currentgroup == $lastgroup) ) {
      $actiontype = 'editbmps';

   } else {
      $actiontype = 'view';
   }
   break;

   case 'applyextrapolation':
      # re-extrapolate selected data and modify the data-set to reflect these changes
      performBestFitLU($listobject, $goutdir, $goutpath, $landuses, $allsegs, $srcyears, $targetyears, $scenarioid, $debug);
      #
   break;

}
#########################################
# END - process actions
#########################################

$totaltime = $timer->startSplit();


#########################################
# Print Headers
#########################################
include("./medit_menu.php");
#########################################
# END - Print Headers
#########################################
print("<form action='$scriptname' method=post name='activemap'>");

print("<table>");
print("<tr>");
print("   <td valign=top width=800>");
showHiddenField('projectid', $projectid);
showHiddenField('lastgroup', $currentgroup);

print("<b>Select Watershed Grouping:</b><br>");
showSegSelect($listobject, $userid, $projectid, $currentgroup, $debug);
# if this was an update form that got cancelled by a button click,
# we need to deal with the year variable, and set it right
if (is_array($thisyear)) {
   $thisyear = $lastyear;
}

print("<br><b>Select a scenario:</b><br>");
$scenclause = "projectid = $projectid and ( (ownerid = $userid  and operms >= 4) ";
$scenclause .= " or ( groupid in ($usergroupids) and gperms >= 4 ) ";
$scenclause .= " or (pperms >= 4) ) ";
#$debug = 1;


showList($listobject, 'scenarioid', 'scenario', 'scenario', 'scenarioid', $scenclause, $scenarioid, $debug);
#`print("<br><b>Enter A Year:</b><br>");
#showWidthTextField('thisyear', $thisyear, 10);
showHiddenField('projectid', $projectid);
showHiddenField('lastgroup', $currentgroup);
# make all layers visible
#
print("<br><b>Select Function:</b><br>");
print("<table width=100% border=1><tr>");
print("<td valign=top bgcolor=#E2EFF5>");
showRadioButton('function', 'createinputfiles', $function);
print("Download Model Files For Local Run<br>");
showRadioButton('function', 'submitinputrun', $function);
print("Submit Existing Scenario for Run at CBP <br>");
showRadioButton('function', 'updateinputs', $function);
print("Update Model Inputs<br>");
print("</td>");
print("<td valign=top bgcolor=#E2EFF5>");
showRadioButton('function', 'inputsummary', $function);
print("Summarize Model Input<br>");
showRadioButton('function', 'distrosummary', $function);
print("Summarize Detailed Model NPS Applications<br>");
showRadioButton('function', 'viewinout', $function);
print("View Model Inputs/Outputs<br>");
showRadioButton('function', 'delivered', $function);
print("Show Modeled Delivered Loads<br>");
showRadioButton('function', 'projection', $function);
print("Project Model Output<br>");
if ($usertype == 1) {
   showRadioButton('function', 'atminputs', $function);
   print("Generate Inputs for the Atmospheric Deposition Model <br>");
}
print("</td>");
print("</tr></table>");
print("<br>");
showSubmitButton('changefunction','Change Function');

print("   </td><td valign=top>");
include ('./medit_controlfooter.php');
print("   </td>");
print("</tr>");
print("</table>");

print("<hr>");


if (count($allsegs) <= 5) {
   $ls = join(', ', $allsegs);
   print("<b>Selected Segments:</b> $ls <br>");
}

if (count($allsegs) > 0) {
   $sslist = "'" . join("','", $allsegs) . "'";
   $lrclause = " lrseg in ($sslist) ";
} else {
   $lrclause = ' (1 = 1) ';
}

/* START segment of code to show the results of a query */

# if it is a map click, we get the areas that have been selected and their eligible BMPs
switch ($function) {

   case 'submitinputrun':
      print("<b>Notice:</b> This function is currently disabled.<br>");
   break;

   case 'projection':
      # check for button press, if so, let it rip!
      #$debug = 1;
      $constit = '1,2';

      print("<b>Select Year to base land use acreage </b>(one year only)<b>:</b> ");
      # screen for onle year only
      $yearfoo = "(select thisyear ";
      $yearfoo .= "from scen_lrsegs ";
      $yearfoo .= "where scenarioid = $scenarioid ";
      $yearfoo .= "   and $lrclause ";
      $yearfoo .= "group by thisyear order by thisyear) as foo ";
      showList($listobject, 'thisyear', $yearfoo, 'thisyear', 'thisyear', '', $thisyear, $debug);
      print("<br>");
      showSubmitButton('projection','Project Load');
      if (isset($_POST['projection'])) {

         $eofrecs = showPredictedDeliveredAll($listobject, $projectid, $scenarioid, $allsegs, $thisyear, $constit, $debug);

         print("<br><b>Projected annual mean Total EOS & Delivered Non-Point Source loads, based on land use for $thisyear : </b><br>");
         $listobject->queryrecords = $eofrecs;
         $listobject->tablename = 'del_projection';
         $listobject->showList();

         print("<hr><table><tr>");
         print("<td colspan=2 align=center><b>EOS & Deliveredloads by Major Land-Use Category:</b></td>");
         print("</tr><tr><td>");
         # by land use type for Nitrogen
         #$debug = 1;
         #$lutypeproj = projectModeledEOFGroups($listobject, $projectid, $scenarioid, $allsegs, $thisyear, 'totn', $debug);
         $lutypeproj = showPredictedDeliveredGroups($listobject, $projectid, $scenarioid, $allsegs, $thisyear, 1, $debug);
         $listobject->queryrecords = $lutypeproj;
         $listobject->tablename = 'landclasseof';
         $listobject->showList();
         print("</td><td>");
         #print_r($lutypeproj);
         $numrecs = count($lutypeproj);
         $gw = $numrecs * 40.0;
         $gh = $numrecs * 2.0;
         if ($gw < 420) { $gw = 440; }
         if ($gh < 240) { $gh = 240; }
         if (count($lutypeproj) > 0) {
            $pieurl = showGenericPie($listobject, $outdir, $outurl, $lutypeproj, 'lutypename', 'delivered_total', "Predicted EOS/Delivered N Loads By Major Source Type Based on $thisyear land-Use", $gw, $gh, $debug);
            print("<img src='$pieurl'>");
         } else {
            print("<b>Notice:</b> There are no results for this scenario.");
         }
         print("</td>");
         print("</tr><tr><td>");
         # by land use type for Phosphorus
         $lutypeproj = showPredictedDeliveredGroups($listobject, $projectid, $scenarioid, $allsegs, $thisyear, 2, $debug);
         $listobject->queryrecords = $lutypeproj;
         $listobject->tablename = 'landclasseof';
         $listobject->showList();
         print("</td><td>");
         $numrecs = count($lutypeproj);
         $gw = $numrecs * 40.0;
         $gh = $numrecs * 2.0;
         if ($gw < 420) { $gw = 440; }
         if ($gh < 240) { $gh = 240; }
         if (count($lutypeproj) > 0) {
            $pieurl = showGenericPie($listobject, $outdir, $outurl, $lutypeproj, 'lutypename', 'delivered_total', "Predicted EOS/Delivered P Loads By Major Source Type Based on $thisyear land-Use", $gw, $gh, $debug);
            print("<img src='$pieurl'>");
         } else {
            print("<b>Notice:</b> There are no results for this scenario.");
         }
         print("</td>");

         print("</tr><tr><td>");
         # by land use type for sediment
         $lutypeproj = showPredictedDeliveredGroups($listobject, $projectid, $scenarioid, $allsegs, $thisyear, 8, $debug);
         $listobject->queryrecords = $lutypeproj;
         $listobject->tablename = 'landclasseof';
         $listobject->showList();
         print("</td><td>");
         $numrecs = count($lutypeproj);
         $gw = $numrecs * 40.0;
         $gh = $numrecs * 2.0;
         if ($gw < 420) { $gw = 440; }
         if ($gh < 240) { $gh = 240; }
         if (count($lutypeproj) > 0) {
            $pieurl = showGenericPie($listobject, $outdir, $outurl, $lutypeproj, 'lutypename', 'delivered_total', "Predicted EOS/Delivered P Loads By Major Source Type Based on $thisyear land-Use", $gw, $gh, $debug);
            print("<img src='$pieurl'>");
         } else {
            print("<b>Notice:</b> There are no results for this scenario.");
         }
         print("</td></tr></table>");
      }

   break;


   case 'delivered':

      #$debug = 1;

      #print("<b>Enter Year to base land use acreage </b>(one year only)<b>:</b><br>");
      # screen for onle year only
      #$yearar = split(',', $thisyear);
      #$thisyear = $yearar[0];
      #showWidthTextField('thisyear', $thisyear, 10);
      print("<br><b>Create Loads by Land/River Segment for Downloa?</b> ");
      $makedownload = $_POST['makedownload'];
      showTFListType('makedownload',$makedownload,1);
      print("<br>");
      showSubmitButton('showdelivered','Show Delivered Load');
      $tt = $timer->startSplit();

      if (isset($_POST['showdelivered'])) {
         if ($makedownload) {

            $npsrecs = showModeledNPSDetail($listobject, $projectid, $scenarioid, $allsegs, $thisyear, 1, $debug);
            $outarr = nestArraySprintf("%s,%s,%s,%s,%s,%6.2f,%6.2f,%6.2f,%6.2f,%6.2f,%6.2f,%6.2f", $npsrecs);
            #print_r($outarr);

            $colnames = array(array_keys($npsrecs[0]));
            $filename = "nps_loads_$scenarioid.$userid.csv";

            putDelimitedFile("$outdir/$filename",$colnames,",",1,'unix');

            putArrayToFilePlatform("$outdir/$filename", $outarr,0,'unix');
            print("<br><a href='$outurl/$filename'>Download NPS Loads</a><br>");


            $psrecs = showModeledPSDetail($listobject, $projectid, $scenarioid, $allsegs, $thisyear, 1, $debug);
            $outarr = nestArraySprintf("%s,%s,%s,%s,%s,%s,%6.2f,%6.2f,%6.2f,%6.2f", $psrecs);
            #print_r($outarr);

            $colnames = array(array_keys($psrecs[0]));

            $filename = "ps_loads_$scenarioid.$userid.csv";
            putDelimitedFile("$outdir/$filename",$colnames,",",1,'unix');

            putArrayToFilePlatform("$outdir/$filename", $outarr,0,'unix');
            print("<a href='$outurl/$filename'>Download PS/Septic/Direct Atmospheric Loads</a><br>");

         } else {

            print("<hr><table><tr>");
            print("<td colspan=2 align=center><b>EOS by Major Land-Use Category:</b></td>");
            print("</tr><tr><td valign=top>");
            # by land use type for Nitrogen
            $lutypeproj = showModeledDeliveredGroups($listobject, $projectid, $scenarioid, $allsegs, $thisyear, 1, $debug);
            #print_r($lutypeproj);
            $listobject->queryrecords = $lutypeproj;
            $listobject->tablename = 'landclasseof';
            $listobject->showList();
            $numrecs = count($lutypeproj);
            $gw = $numrecs * 40.0;
            $gh = $numrecs * 2.0;
            if ($gw < 420) { $gw = 440; }
            if ($gh < 240) { $gh = 240; }
            print("</td><td valign=top>");
            if (count($lutypeproj) > 0) {
               $pieurl = showGenericPie($listobject, $outdir, $outurl, $lutypeproj, 'lutypename', 'delivered_total', 'Delivered N Loads By Major Source Type', $gw, $gh, $debug);
               print("<img src='$pieurl'>");
            } else {
               print("<b>Notice:</b> There are no results for this scenario.");
            }
            $tt = $timer->startSplit();
            print("<br>Total Time: $tt<br>");
            print("</td>");
            print("</tr><tr><td  valign=top>");
            # by land use type for Phosphorus
            $lutypeproj = showModeledDeliveredGroups($listobject, $projectid, $scenarioid, $allsegs, $thisyear, 2, $debug);
            $listobject->queryrecords = $lutypeproj;
            $listobject->tablename = 'landclasseof';
            $listobject->showList();
            $numrecs = count($lutypeproj);
            $gw = $numrecs * 40.0;
            $gh = $numrecs * 30.0;
            if ($gw < 420) { $gw = 440; }
            if ($gh < 240) { $gh = 240; }
            print("</td><td  valign=top>");
            if (count($lutypeproj) > 0) {
               $pieurl = showGenericPie($listobject, $outdir, $outurl, $lutypeproj, 'lutypename', 'delivered_total', 'Delivered P Loads By Major Source Type', $gw, $gh, $debug);
               print("<img src='$pieurl'>");
            } else {
               print("<b>Notice:</b> There are no results for this scenario.");
            }
            $tt = $timer->startSplit();
            print("<br>Total Time: $tt<br>");

            print("</td></tr>");
            print("<tr>");
            print("</tr><tr><td  valign=top>");
            # by land use type for Sediment
            $lutypeproj = showModeledDeliveredGroups($listobject, $projectid, $scenarioid, $allsegs, $thisyear, 8, $debug);
            $listobject->queryrecords = $lutypeproj;
            $listobject->tablename = 'landclasseof';
            $listobject->showList();
            $numrecs = count($lutypeproj);
            $gw = $numrecs * 40.0;
            $gh = $numrecs * 30.0;
            if ($gw < 420) { $gw = 440; }
            if ($gh < 240) { $gh = 240; }
            print("</td><td  valign=top>");
            if (count($lutypeproj) > 0) {
               $pieurl = showGenericPie($listobject, $outdir, $outurl, $lutypeproj, 'lutypename', 'delivered_total', 'Delivered TSS Loads By Major Source Type', $gw, $gh, $debug);
               print("<img src='$pieurl'>");
            } else {
               print("<b>Notice:</b> There are no results for this scenario.");
            }
            $tt = $timer->startSplit();
            print("<br>Total Time: $tt<br>");

            print("</td></tr>");
            print("<tr>");

            #$debug = 1;
            print("<td colspan=2 align=center><b>EOF by Major Land-Use Category:</b></td>");
            print("</tr><tr><td  valign=top>");
            # by land use type for Nitrogen
            $lutypeproj = showModeledDelivered($listobject, $projectid, $scenarioid, $allsegs, $thisyear, 1, $debug);
            $listobject->queryrecords = $lutypeproj;
            $listobject->tablename = 'landclasseof';
            $listobject->showList();
            $numrecs = count($lutypeproj);
            $gw = $numrecs * 40.0;
            $gh = $numrecs * 30.0;
            if ($gw < 420) { $gw = 440; }
            if ($gh < 240) { $gh = 240; }
            print("</td><td  valign=top>");
            if (count($lutypeproj) > 0) {
               $pieurl = showGenericPie($listobject, $outdir, $outurl, $lutypeproj, 'luname', 'delivered_total', 'Delivered NPS-N Loads By Model Land-Use Type', $gw, $gh, $debug);
               print("<img src='$pieurl'>");
            } else {
               print("<b>Notice:</b> There are no results for this scenario.");
            }
            $tt = $timer->startSplit();
            print("<br>Total Time: $tt<br>");
            print("</td>");
            print("</tr><tr><td  valign=top>");
            # by land use type for Phosphorus
            $lutypeproj = showModeledDelivered($listobject, $projectid, $scenarioid, $allsegs, $thisyear, 2, $debug);
            $listobject->queryrecords = $lutypeproj;
            $listobject->tablename = 'landclasseof';
            $listobject->showList();
            $numrecs = count($lutypeproj);
            $gw = $numrecs * 40.0;
            $gh = $numrecs * 30.0;
            if ($gw < 420) { $gw = 440; }
            if ($gh < 240) { $gh = 240; }
            print("</td><td  valign=top>");
            if (count($lutypeproj) > 0) {
               $pieurl = showGenericPie($listobject, $outdir, $outurl, $lutypeproj, 'luname', 'delivered_total', 'Delivered NPS-P Loads By Model Land-Use Type', $gw, $gh, $debug);
               print("<img src='$pieurl'>");
            } else {
               print("<b>Notice:</b> There are no results for this scenario.");
            }
            $tt = $timer->startSplit();
            print("<br>Total Time: $tt<br>");

            print("</td></tr>");
            print("</tr><tr><td  valign=top>");
            # by land use type for Seddiment
            $lutypeproj = showModeledDelivered($listobject, $projectid, $scenarioid, $allsegs, $thisyear, 8, $debug);
            $listobject->queryrecords = $lutypeproj;
            $listobject->tablename = 'landclasseof';
            $listobject->showList();
            $numrecs = count($lutypeproj);
            $gw = $numrecs * 40.0;
            $gh = $numrecs * 30.0;
            if ($gw < 420) { $gw = 440; }
            if ($gh < 240) { $gh = 240; }
            print("</td><td  valign=top>");
            if (count($lutypeproj) > 0) {
               $pieurl = showGenericPie($listobject, $outdir, $outurl, $lutypeproj, 'luname', 'delivered_total', 'Delivered NPS-TSS Loads By Model Land-Use Type', $gw, $gh, $debug);
               print("<img src='$pieurl'>");
            } else {
               print("<b>Notice:</b> There are no results for this scenario.");
            }
            $tt = $timer->startSplit();
            print("<br>Total Time: $tt<br>");

            print("</td></tr>");

            print("</td></tr></table>");
         }
      }

   break;


   case 'inputsummary':

      # create model input files for download
      # Inputs:
      #    $targetyears - the years to create values for
      #    $sourceyears - the years to use as input data
      # this can be done several ways:
      # 1) Replace all target years with a pure best fit line from the source years
      # 2) Replace all target years

      print("<b>Model Input Summary:</b><br>");
      print("<b>Current Active Group:</b> $groupname<br><br>");
      print("<b>Enter Year to Summarize: </b><br>");
      # screen for onle year only
      $yearfoo = "(select thisyear ";
      $yearfoo .= "from scen_lrsegs ";
      $yearfoo .= "where scenarioid = $scenarioid ";
      $yearfoo .= "   and $lrclause ";
      $yearfoo .= "group by thisyear order by thisyear) as foo ";
      showList($listobject, 'thisyear', $yearfoo, 'thisyear', 'thisyear', '', $thisyear, $debug);
      print("<br>");
      print("<br>");
      showSubmitButton('dosummary','Summarize Inputs');
      if (isset($_POST['dosummary'])) {

         print("<h3>Model Inputs Summary for $thisyear:</h3><hr>");
         print("<table border=1><tr>");
         print("<td valign=top>");
         print("<b>Source Application</b>");
         #$debug = 1;
         $polltype = '1,2';
         $srcrecs = getLRClassApplications($listobject, $allsegs, $scenarioid, $polltype, $thisyear, $debug);
         $listobject->queryrecords = $srcrecs;
         $listobject->tablename = 'sourcesbytype';
         if (count($srcrecs) > 0) {
            $listobject->showList();
         } else {
            print("<b>Notice:</b> There are no model inputs generated for this scenario. <br>");
         }
         print("</td>");
         print("<td valign=top>");
         print("<b>Source Production ***</b>");
         $polltype = '1,2';
         $srcrecs = getLRClassProduction($listobject, $allsegs, $scenarioid, $polltype, $thisyear, $debug);
         $listobject->queryrecords = $srcrecs;
         $listobject->tablename = 'sourcesbytype';
         if (count($srcrecs) > 0) {
            $listobject->showList();
         } else {
            print("<br><b>Notice:</b> There are no model inputs generated for this scenario. <br>");
         }
         print("</td>");
         print("</tr></table>");

         print("<b>Manure Transport Needed: </b><br>");
         $transportrecs = getTransportNeeded($listobject, $allsegs, $scenarioid, $thisyear, $debug);
         $listobject->tablename = 'transport';
         if (count($transportrecs) > 0) {
            $tr = array();
            foreach ($transportrecs as $thisrec) {
               $cs = $thisrec['pollutanttype'];
               if ($cs == 12) {
                  # this is manure total
                  $manure = number_format($thisrec['total_tons'], 2);
               } else {
                  array_push($tr, $thisrec);
               }
            }
            print("$manure tons of manure needs to be transported.<br>");
            $listobject->queryrecords = $tr;
            $listobject->showList();
         } else {
            print("<br><b>Notice:</b> There are no model inputs generated for this scenario. <br>");
         }

         print("<b>*** Note Regarding Livestock:</b> The 'Source Applications' table shows actual modeled ");
         print(" applications for all sources, including pastured and non-pastured animals. ");
         print("However, the 'Source Production' summary table shows the total produced, ");
         print("and an estimated amount volatilized ");
         print("if the animals were confined 100% of the time.  Therefore, the source applications in ");
         print("areas with large amounts of pastured animals may appear to be greater than ");
         print("'production - volatilization' in the 'Source Production' summary table. ");
         print("Additionally, rounding errors in the summary process may result in differences of ");
         print("&lt;= 0.5%. <br>");
         print("<b>*** Note Regarding Point Source:</b> This is an estimate of sewer INPUTS from humans ");
         print("distributed by the amount of high density urban area in a county. This ");
         print("is NOT actually a modeled input. Model sewer inputs are derived from actual point source ");
         print("discharge data.  This only serves as informational. <br>");
         print("<b>*** Note Regarding Fertilizer:</b> Fertilizer values in the production do not ");
         print("relate to actual fertilizer data.  Only applied modeled fertilizer value is accurate. ");
      }

   break;


   case 'distrosummary':

      # do a distribution

      print("<b>Model Input Distribution Data:</b><br>");
      print("<b>Current Active Group:</b> $groupname<br><br>");
      print("<b>Enter Year to Summarize: </b><br>");
      # screen for onle year only
      $yearfoo = "(select thisyear ";
      $yearfoo .= "from scen_lrsegs ";
      $yearfoo .= "where scenarioid = $scenarioid ";
      $yearfoo .= "   and $lrclause ";
      $yearfoo .= "group by thisyear order by thisyear) as foo ";
      # singe year
      #showList($listobject, 'thisyear', $yearfoo, 'thisyear', 'thisyear', '', $thisyear, $debug);

      # multiple years
      $selyears = join(',', $theseyears);
      #$debug = 1;
      showMultiList2($listobject, 'theseyears', $yearfoo, 'thisyear', 'thisyear', $selyears, '', 'thisyear', $debug, 4);
      print("<br><b>Select Land-Uses </b><br>");

      $selectedlus = join(',', $landuses);
      showMultiList2($listobject, 'landuses', 'landuses', 'hspflu', 'landuse, major_lutype', $selectedlus, "projectid = $projectid and hspflu <> ''", 'major_lutype, landuse', $debug, 4);
      print("<br><b>Select Constituents</b>:<br> ");
      $theseconstits = join(',', $pollutanttype);
      showMultiList2($listobject, 'pollutanttype', 'pollutanttype', 'typeid', 'pollutantname', $theseconstits, "typeid in (select pollutanttype from sourcepollutants where projectid = $projectid group by pollutanttype)", 'pollutantname', $debug);
      print("<br>");
      print("<br>");
      showSubmitButton('distrosummary','Summarize Inputs');
      if (isset($_POST['distrosummary'])) {

         print("<hr><b>Model Inputs Summary for $selyears:</b><br>");
         #$debug = 1;
         $timer->startsplit();
         # initialize counters for file append/overwrite test, one for each file
         $i = 0;
         $j = 0;
         $k = 0;

         foreach (split(',', $selyears) as $thisyear) {
            $srcrecs = showApplicationDetails($listobject, $projectid, $scenarioid, $thisyear, $selectedlus, $allsegs, $theseconstits, $debug);
            if (count($srcrecs) > 0) {
               # format for output
               $outarr = nestArraySprintf("%s,%s,%s,%s,%s,%s,%s,%6.2f,%6.2f,%6.2f,%6.2f,%6.2f,%6.2f,%6.2f,%6.2f,%6.2f,%6.2f,%6.2f,%6.2f,%6.2f", $srcrecs);
               #print_r($outarr);
               $qtime = $timer->startsplit();

               $colnames = array(array_keys($srcrecs[0]));

               $dfilename = "distro_$scenarioid" . ".$userid" . ".csv";
               if ($i == 0) {
                  # first group of records, add the header
                  putDelimitedFile("$outdir/$dfilename",$colnames,",",1,'unix');
               }
               putArrayToFilePlatform("$outdir/$dfilename", $outarr,0,'unix');
               $i++;
            }

            $prodrecs = showProductionDetails($listobject, $projectid, $scenarioid, $allsegs, $theseconstits, $thisyear, $debug);
            if (count($prodrecs) > 0) {
               # format for output
               $outarr = nestArraySprintf("%s,%s,%s,%s,%6.2f,%6.2f,%6.2f,%6.2f", $prodrecs);
               #print_r($outarr);
               $qtime = $timer->startsplit();

               $colnames = array(array_keys($prodrecs[0]));

               $pfilename = "production_$scenarioid" . ".$userid" . ".csv";
               if ($j == 0) {
                  # first group of records, add the header
                  putDelimitedFile("$outdir/$pfilename",$colnames,",",1,'unix');
               }
               putArrayToFilePlatform("$outdir/$pfilename", $outarr,0,'unix');
               $j++;
            }

            $needrecs = showLUCropNeed($listobject, $projectid, $scenarioid, $allsegs, $thisyear, 1);
            if (count($needrecs) > 0) {
               # format for output
               $outarr = nestArraySprintf("%s,%s,%s,%6.2f,%6.2f,%6.2f,%6.2f,%6.2f,%6.2f,%6.2f,%6.2f,%6.2f,%6.2f,%6.2f", $needrecs);
               #print_r($outarr);
               $qtime = $timer->startsplit();

               $colnames = array(array_keys($needrecs[0]));

               $cfilename = "cropneed_$scenarioid" . ".$userid" . ".csv";
               if ($k == 0) {
                  # first group of records, add the header
                  putDelimitedFile("$outdir/$cfilename",$colnames,",",1,'unix');
               }
               putArrayToFilePlatform("$outdir/$cfilename", $outarr,0,'unix');
               $k++;
            }
         }
         print("<a href='$outurl/$dfilename'>Download Detailed Distrubution table for: $selyears</a><br>");
         print("<a href='$outurl/$pfilename'>Download Detailed Production table for: $selyears</a><br>");
         print("<a href='$outurl/$cfilename'>Download Crop Need table for: $selyears</a><br>");

         print("Query Time: $qtime<br>");
      }

   break;

   case 'createinputfiles':

   # create model input files for download
   # Inputs:
   #    $targetyears - the years to create values for
   #    $sourceyears - the years to use as input data
   # this can be done several ways:
   # 1) Replace all target years with a pure best fit line from the source years
   # 2) Replace all target years

   print("<b>Model Input File Creation:</b><br>");
   print("Model Input Files will be created for the currently selected model segments. <br> ");
   print("If no segments are selected, the entire active watershed group will be used. <br> ");
   print("<b>Current Active Group:</b> $groupname<br><br>");
   if (!isset($_POST['domodelfiles'])) {
      # set default check boxes
      $makeseptic = 0;
      $makeuptakeappsum = 0;
      $domasslinks = 0;
      $annualuptake = 1;
      $makecanopycurves = 0;
      $makeuptakecurves = 0;
      $makelu = 1;
      $dofert = 1;
      $makemanure = 1;
      $legume = 1;
   } else {
      $makeseptic = $_POST['makeseptic'];
      $makeuptakeappsum = $_POST['makeuptakeappsum'];
      $domasslinks = $_POST['domasslinks'];
      $annualuptake = $_POST['annualuptake'];
      $makecanopycurves = $_POST['makecanopycurves'];
      $makeuptakecurves = $_POST['makeuptakecurves'];
      $makelu = $_POST['makelu'];
      $dofert = $_POST['dofert'];
      $makemanure = $_POST['makemanure'];
      $legume = $_POST['legume'];
      $theseconstits = $_POST['theseconstits'];
      $constits = join(',',$theseconstits);
   }

   print("<b>Select Years:</b><br>");
   # screen for this scenarios years only
   $yearfoo = "(select thisyear ";
   $yearfoo .= "from scen_lrsegs ";
   $yearfoo .= "where scenarioid = $scenarioid ";
   $yearfoo .= "   and $lrclause ";
   $yearfoo .= "group by thisyear order by thisyear) as foo ";
   # multiple years
   $selyears = join(',', $theseyears);
   #$debug = 1;

   showMultiList2($listobject, 'theseyears', $yearfoo, 'thisyear', 'thisyear', $selyears, '', 'thisyear', $debug, 4);
   print("<br><b>Select Constituents</b>:<br> ");

   showMultiList2($listobject, 'theseconstits', 'pollutanttype', 'typeid', 'pollutantname', $constits, "typeid in (select pollutanttype from sourcepollutants where projectid = $projectid group by pollutanttype)", 'pollutantname', $debug);
   # input file creation options
   print("<br>");
   showCheckBox('makeseptic',1, $makeseptic);
   print("<b>Create Septic Loading Tables? </b>");
   print("<br>");
   showCheckBox('makemanure',1, $makemanure);
   print("<b>Create Manure NPS Loading Tables? </b>");
   print("<br>");
   showCheckBox('dofert',1, $dofert);
   print("<b>Create Fertilizer NPS Loading Tables? </b>");
   print("<br>");
   showCheckBox('legume',1, $legume);
   print("<b>Create Legume NPS Loading Tables? </b>");
   print("<br>");
   showCheckBox('makelu',1, $makelu);
   print("<b>Create Landuse Input Tables? </b>");
   print("<br>");
   showCheckBox('makeuptakecurves',1, $makeuptakecurves);
   print("<b>Create Monthly Crop Uptake Curves? </b>");
   print("<br>");
   showCheckBox('makecanopycurves',1, $makecanopycurves);
   print("<b>Create Monthly Crop Cover Curves? </b>");
   print("<br>");
   showCheckBox('annualuptake',1, $annualuptake);
   print("<b>Create Annual Crop Uptake Totals </b>");
   print("<br>");
   showCheckBox('domasslinks',1, $domasslinks);
   print("<b>Create Mass-links Files </b>");
   print("<br>&nbsp;&nbsp;&nbsp;");
   showSubmitButton('domodelfiles','Create Model Files');

   if ($_POST['domodelfiles']) {

      # assemble some basic criteria regarding the requested land area, time frame
      if (count($allsegs) > 0) {
         $sslist = "'" . join("','", $allsegs) . "'";
         $subcond = " lrseg in ( $sslist) ";
         $asubcond = " a.lrseg in ( $sslist) ";
      } else {
         $subcond = ' ( 1 = 1 ) ';
         $asubcond = ' ( 1 = 1 ) ';
      }
      if (strlen($thisyear) > 0) {
         $yrcond = " thisyear = $thisyear ";
         $ayrcond = " a.thisyear = $thisyear ";
         $byrcond = " b.thisyear = $thisyear ";
      } else {
         $yrcond = " (1 = 1) ";
         $ayrcond = " (1 = 1) ";
         $byrcond = " (1 = 1) ";
      }

      foreach ($theseyears as $thisyear) {

        if ($listobject->tableExists("temp_lrsegs") ) {

            $listobject->querystring = " drop table temp_lrsegs ";
            $listobject->performQuery();

            $listobject->querystring = " drop table temp_landsegs ";
            $listobject->performQuery();

            $listobject->querystring = " drop table temp_scensource ";
            $listobject->performQuery();
         }

         print("Querying segment list for $groupname <br>");
         # this should speed up some querying since the matching of sub-watersheds is a laborious process
         # many of the sub-routines referenced herein require these temp tables.
         # butt-nasty? Yes. Oh well.
         $listobject->querystring = "create temp table temp_lrsegs as select a.thisyear, ";
         $listobject->querystring .= "    a.lrseg, a.landseg, a.riverseg, ";
         $listobject->querystring .= "    a.subshedid, b.hspflu as luname,  ";
         $listobject->querystring .= "    CASE ";
         $listobject->querystring .= "       WHEN sum(a.luarea) is null THEN 0.0 ";
         $listobject->querystring .= "       ELSE sum(a.luarea) ";
         $listobject->querystring .= "    END as luarea ";
         $listobject->querystring .= " from scen_lrsegs as a left outer join landuses as b ";
         $listobject->querystring .= " on (a.luname = b.hspflu and b.hspflu <> '' ";
         $listobject->querystring .= "    and b.hspflu is not null ";
         $listobject->querystring .= "    and b.projectid = $projectid ) ";
         $listobject->querystring .= " where scenarioid = $scenarioid ";
         $listobject->querystring .= " and $asubcond ";
         $listobject->querystring .= " and a.thisyear = $thisyear  ";
         $listobject->querystring .= "  group by a.thisyear, a.lrseg, a.landseg, a.riverseg, a.subshedid, b.hspflu ";
         if ($debug) { print("$listobject->querystring ; <br>"); }
         $listobject->performQuery();

         $listobject->querystring = "  create temp table temp_landsegs as ";
         $listobject->querystring .= " select a.thisyear, a.landseg, ";
         $listobject->querystring .= "    a.subshedid, b.hspflu as luname,  ";
         $listobject->querystring .= "    CASE ";
         $listobject->querystring .= "       WHEN sum(a.luarea) is null THEN 0.0 ";
         $listobject->querystring .= "       ELSE sum(a.luarea) ";
         $listobject->querystring .= "    END as luarea ";
         $listobject->querystring .= " from scen_lrsegs as a left outer join landuses as b ";
         $listobject->querystring .= " on (a.luname = b.hspflu and b.hspflu <> '' and b.hspflu is not null";
         $listobject->querystring .= "    and b.projectid = $projectid ) ";
         $listobject->querystring .= " where scenarioid = $scenarioid ";
         $listobject->querystring .= " and $asubcond ";
         $listobject->querystring .= " and a.thisyear = $thisyear  ";
         $listobject->querystring .= "  group by a.thisyear, a.landseg, a.subshedid, b.hspflu ";
         if ($debug) { print("$listobject->querystring ; <br>"); }
         $listobject->performQuery();

         print("Querying sources for $groupname <br>");
         # this should speed up some querying since the matching of sub-watersheds is a laborious process
         $listobject->querystring = "create temp table temp_scensource as select * ";
         $listobject->querystring .= " from scen_sourceperunitarea as a ";
         $listobject->querystring .= " where scenarioid = $scenarioid ";
         $listobject->querystring .= " and subshedid in (select subshedid from temp_landsegs group by subshedid) ";
         $listobject->querystring .= " and thisyear = $thisyear ";
         if ($debug) { print("$listobject->querystring ; <br>"); }
         $listobject->performQuery();

         if ($makeseptic) {
            makeSepticInputFiles($listobject, $projectid, $scenarioid, $sceninfo['shortname'], $outurl, $outdir, $thisyear, $allsegs, $septicpolls, $septicid, $debug);
         }
         if ($makemanure) {
            # this routine wants to know what source classes are considered to be "manure". We input
            # the variable "manure_sclass" which is set in local_variables.php to indicate this.
            # if we wanted to do true manure, not just all organic w2astes, we would put in
            # "manure_sclass_nobio" which excludes biosolids
            makeManureInputFiles($listobject, $projectid, $scenarioid, $sceninfo['shortname'], $outurl, $outdir, $thisyear, $allsegs, $constits, $manure_sclass, $spread_manure, $nullval, $debug);
         }
         if ($dofert) {
            # this routine wants to know what source classes are considered to be "fertilizer". We input
            # the variable "fertsourceclasses" which is set in local_variables.php to indicate this.
            makeFertilizerInputFiles($listobject, $projectid, $scenarioid, $sceninfo['shortname'], $outurl, $outdir, $thisyear, $allsegs, $constits, $fertsourceclasses, $spread_fert, $nullval, $debug);
         }
         if ($legume) {
            # this routine wants to know what source polls are considered to be "legume". We input
            # the variable "legume_nut" which is set in local_variables.php to indicate this.
            # this is a result of the method used to simulate legume fixation
            makeLegumeInputFiles($listobject, $projectid, $scenarioid, $sceninfo['shortname'], $outurl, $outdir, $thisyear, $allsegs, $constits, $legume_nut, $nullval, $debug);
         }
         if ($makelu) {
            # this routine wants to know what source polls are considered to be "legume". We input
            # the variable "legume_nut" which is set in local_variables.php to indicate this.
            # this is a result of the method used to simulate legume fixation
            makeLanduseInputFiles($listobject, $projectid, $scenarioid, $sceninfo['shortname'], $outurl, $outdir, $thisyear, $allsegs, $nullval, $debug);
         }
         if ($makeuptakecurves) {
            # this routine wants to know what source polls are considered to be "legume". We input
            # the variable "legume_nut" which is set in local_variables.php to indicate this.
            # this is a result of the method used to simulate legume fixation
            makeUptakeCurveFiles($listobject, $projectid, $scenarioid, $sceninfo['shortname'], $outurl, $outdir, $thisyear, $allsegs, $constits, $crop_lutypes, $nullval, $debug);
         }
         if ($makecanopycurves) {
            # this routine wants to know what source polls are considered to be "legume". We input
            # the variable "legume_nut" which is set in local_variables.php to indicate this.
            # this is a result of the method used to simulate legume fixation
            makeCanopyCurveFiles($listobject, $projectid, $scenarioid, $sceninfo['shortname'], $outurl, $outdir, $thisyear, $allsegs, $maxc, $crop_lutypes, $res_canopy, $nullval, $debug);
         }
         if ($annualuptake) {
            # this routine wants to know what source polls are considered to be "legume". We input
            # the variable "legume_nut" which is set in local_variables.php to indicate this.
            # this is a result of the method used to simulate legume fixation
            makeAnnualUptakeFiles($listobject, $projectid, $scenarioid, $sceninfo['shortname'], $outurl, $outdir, $thisyear, $allsegs, $crop_lutypes, $nullval, $debug);
         }
         if ($domasslinks) {
            # this routine wants to know what source polls are considered to be "legume". We input
            # the variable "legume_nut" which is set in local_variables.php to indicate this.
            # this is a result of the method used to simulate legume fixation
            exportMasslinks($listobject, $scenarioid, $outdir, $outurl, $allsegs, $sceninfo['shortname'], array(), array($thisyear), $theseconstits, $debug);
         }
      }
   }

   break;

   case 'atminputs':
   # this function needs:
      #    $targetyears - the years to create values for
      #    $sourceyears - the years to use as input data

      $makeatm = 0;
      if (isset($_POST['makeatm'])) {
         $dostored = $_POST['dostored'];
         $doapplied = $_POST['doapplied'];
         $targetyears = $_POST['targetyears'];
         $selyears = join(',', $targetyears);
         $makeatm = 1;
      }
      $makecsv = 1;

      print("<b>Create Inputs for the Atmospheric Deposition Model:</b><br>");
      print("This routine will create inputs of monthly stored manure loads, and monthly applied manure and fertilizer. <br> ");
      print("<b>Current Active Group:</b> $groupname<br><br>");
      print("<b>Select Years</b>(blank will use all)<b>:</b><br>");
      # screen for onle year only
      $yearfoo = "(select thisyear ";
      $yearfoo .= "from scen_lrsegs ";
      $yearfoo .= "where scenarioid = $scenarioid ";
      $yearfoo .= "   and $lrclause ";
      $yearfoo .= "group by thisyear order by thisyear) as foo ";
      showMultiList2($listobject, 'targetyears', $yearfoo, 'thisyear', 'thisyear', $selyears, '', 'thisyear', $debug, 4);
      print("<br>");
      print("<b>Create Stored Consituent Input? </b>");
      showTFListType('dostored',$dostored,1);
      print("<br><b>Create Applied Consituent Inputs? </b>");
      showTFListType('doapplied',$doapplied,1);
      print("<br>");
      showSubmitButton('makeatm','Create Atmospheric Model Files');

      #print(" years $selyears <br>");

      if ($makeatm) {
         print("<hr>");

         foreach ($targetyears as $thisyear) {

            $constit = '1,7,6,3';

            $spreadtypes = $spread_manure . ',' . $spread_fert;

            #$debug = 1;
            if ($dostored) {
               $storerecs = getLRSegStored($listobject, '', $spread_manure, $tracerpoll, $constit, $allsegs, $scenarioid, $thisyear, $debug);
               if ($makecsv) {
                  $colnames = array(array_keys($storerecs[0]));
                  $filename = "stored_$userid.$scenarioid" . "_$thisyear.csv";
                  putDelimitedFile("$outdir/$filename", $colnames, ',',1,'unix');
                  putDelimitedFile("$outdir/$filename", $storerecs, ',',0,'unix');
                  print("<a href='$outurl/$filename'>Download Stored Loads Input table for $thisyear.</a><br>");
               } else {
                  $listobject->showlist();
               }
            }

            if ($doapplied) {
               $apprecs = getLRSegApplied($listobject, $spreadtypes, $tracerpoll, $constit, $allsegs, $scenarioid, $thisyear, $debug);

               if ($makecsv) {
                  $colnames = array(array_keys($apprecs[0]));
                  $filename = "applied_$userid.$scenarioid" . "_$thisyear.csv";
                  putDelimitedFile("$outdir/$filename", $colnames, ',',1,'unix');
                  putDelimitedFile("$outdir/$filename", $apprecs, ',',0,'unix');
                  print("<a href='$outurl/$filename'>Download Applied Loads Input table for $thisyear.</a><br>");
               } else {
                  $listobject->showlist();
               }
            }
         }
         $tt = $timer->startSplit();
         print("<br>Total Time: $tt<br>");
      }

   break;

   case 'viewinout':
   # this function needs:
      if (isset($_POST['showeof'])) {
         $showeof = $_POST['showeof'];
      }

      print("<b>Model Input and Output Visiualization:</b><br>");
      print("This routine will plot model inputs and outputs for the selected land use. <br> ");
      print("<b>Current Active Group:</b> $groupname<br><br>");
      print("<b>Enter Years, comma separated </b>(blank will use all)<b>:</b><br>");
      showWidthTextField('targetyears', $targetyears, 10);
      /*
      print("<br><b>Enter Model Run Name:</b><br>");
      showWidthTextField('modelscen', $modelscen, 10);
      */
      print("<br><b>Select Land-Uses </b><br>");
      $selectedlus = join(',', $landuses);
      showMultiList2($listobject, 'landuses', 'landuses', 'hspflu', 'landuse, major_lutype', $selectedlus, "projectid = $projectid and hspflu <> ''", 'major_lutype, landuse', $debug, 4);
      print("<br>");
      showCheckBox('graphseplu',1, $graphseplu);
      print("<b>Graph Selected Land Uses Separately?</b><br>");
      print("<br><b>Select a constituent:</b>");
      showList($listobject, 'constit', 'pollutanttype', 'pollutantname', 'typeid', '', $constit, $debug);
      print("<br>");
      print("<b>Show Edge of Stream Loss on Uptake Graph?:</b> ");
      showTFListType('showeof',$showeof,1);
      print("<br>");
      showSubmitButton('plotinout','Show Applications and Uptake Plots');

      if ( is_array($landuses) and ($constit > 0) ) {
         $timer->startSplit();
         if ($graphseplu) {
            $lugroups = $landuses;
         } else {
            $lugroups = array(join(',', $landuses));
         }
         #$debug = 1;

         #
         # get model_scen from scenario table
         # changed RWB - 10/16/2006
         $listobject->querystring = " select model_scen from scenario where scenarioid = $scenarioid ";
         $listobject->performQuery();
         $modelscen = $listobject->getRecordValue(1,'model_scen');

         foreach ($lugroups as $luname) {
            print("<br><b>Annual Application parameters for selected years - $luname</b><br>");
            if (strlen($targetyears) > 0) {
               $qr = showApplicationStats($listobject, $projectid, $scenarioid, $targetyears, $luname, $allsegs, $constit, 1, $debug);
               $listobject->queryrecords = $qr;
               $tt = $timer->startSplit();
               print("<br>Total Time: $tt<br>");

               foreach (split(',', $targetyears) as $ty) {
                  $adminsetuparray['applycross']['column info'][$ty] = $adminsetuparray['applycross']['column info']['1980'];
                  $adminsetuparray['applycross']['column info'][$ty]['label'] = $ty;
               }
               $listobject->adminsetuparray = $adminsetuparray;
               $listobject->tablename = 'applycross';
               $listobject->showList();

               $tt = $timer->startSplit();
               #print("<br>Total Time: $tt<br>");

               $seglist = join(',', $allsegs);
               #$debug = 1;
               $subgraph = showApplicationUptakeCurve($listobject,$goutdir, $goutpath, $targetyears, $seglist, $luname, $scenarioid, $constit, $debug, 1);
               if ($subgraph <> '-1') {
                  print("<br><img src='$subgraph'>");
               } else {
                  print("No Application/Uptake Data Available<br>");
               }

               $tt = $timer->startSplit();
               print("<br>Total Time: $tt<br>");
            } else {
               print("You must selected at least one year to see application data.<br>");
            }

            $eoftargets = showEOFTargets($listobject, $projectid, $scenarioid, $thisyear, $luname, $allsegs, $constit, $debug);
            $listobject->queryrecords = $eoftargets;
            $listobject->tablename = '';
            $listobject->showList();


            if (strlen($modelscen) > 0) {
              # print("<tr><td colspan=$numyrs>");
               print("<b>Annual Modeled Crop Uptake, Estimated Crop Uptake (from Ag. Census/NASS), and Modeled Edge Of Stream Loading</b><br>");
               #$debug = 1;
               $yldswitch = 2; # area-weight and base on % of max yields
               $fyear = min(split(',',$targetyears));
               $lyear = max(split(',',$targetyears));

               #$debug = 1;
               $modoutput = showModeledObservedUptakeCurve($cropobject, $listobject, $goutdir, $goutpath, $seglist, $luname, $yldswitch, $showeof, $scenarioid, $modelscen, $constit, $fyear, $lyear, $debug, 1, 1);
               $modgraph = $modoutput['imgpath'];
               $modeof = $modoutput['wgtd_eof'];
               $listobject->queryrecords = $modoutput['listformat'];
               $listobject->tablename = '';
               $listobject->showList();
               #print_r($modeof);
               #print_r($modoutput['total_eof']);
               print("<img src='$modgraph'>");

               /*
               $qr = showModelOutputStats($listobject, $projectid, $scenarioid, $targetyears, $luname, $allsegs, $constit, $modelscen, 1, $debug);

               $listobject->queryrecords = $qr;

               $adminsetuparray['applycross']['column info']['Average'] = $adminsetuparray['applycross']['column info']['1980'];
               $adminsetuparray['applycross']['column info']['Average']['label'] = 'Average';

               $listobject->adminsetuparray = $adminsetuparray;
               $listobject->tablename = 'applycross';
               $listobject->showList();
               */

            } else {
               print("<b>Error:</b>You must enter a model scenario name to view model output<br>");
            }

            $tt = $timer->startSplit();
            print("<br>Total Time: $tt<br>");
         }
      }
   break;

   case 'updateinputs':
   # check for write permissions
   #print(" PERMS: $perms <br>");
   if ( !($perms & 2) ) {
      print("<b>Error:</b> You do not have edit permissions on this scenario. <br>");
   } else {
      # this function needs:
      # lreditlist - lrsegs to edit
      # listobject
      # bmpname
      # year
      # scenarioid
      # projectid

      if ( !(strlen($viewyear ) > 0) ) {
         if (strlen($thisyear) > 0) {
            $viewyear = $thisyear;
         } else {
            $viewyear = date('Y');
         }
      }

      print("<b>Update Model Inputs for: </b>");
      print("<b>Select Years:</b><br>");
      # screen for this scenarios years only
      $yearfoo = "(select thisyear ";
      $yearfoo .= "from scen_lrsegs ";
      $yearfoo .= "where scenarioid = $scenarioid ";
      $yearfoo .= "   and $lrclause ";
      $yearfoo .= "group by thisyear order by thisyear) as foo ";
      # multiple years
      $selyears = join(',', $theseyears);
      #$debug = 1;
      showMultiList2($listobject, 'theseyears', $yearfoo, 'thisyear', 'thisyear', $selyears, '', 'thisyear', $debug, 4);
      if ($doupdate) {
         $refreshbmps = $_POST['refreshbmps'];
         $refreshcropneed = $_POST['refreshcropneed'];
         $regenerateinputs = $_POST['regenerateinputs'];
      }
      print("<br>");
      showCheckBox('refreshbmps',1, $refreshbmps);
      print("<b>Re-apply BMPs? </b>");
      print("<br>");
      showCheckBox('refreshcropneed',1, $refreshcropneed);
      print("<b>Re-calculate Crop-need? </b>");
      print("<br>");
      showCheckBox('regenerateinputs',1, $regenerateinputs);
      print("<b>Regenerate Model Inputs? </b>");

      showSubmitButton('doupdate','Update Inputs');

      if ($doupdate) {

         foreach ($theseyears as $thisyear) {

            $thisyear = ltrim(rtrim($thisyear));

            if ($refreshbmps) {
               $timer->startsplit();
               print("Re-distributing BMPs for $thisyear<br>");
               $timer->startsplit();
               $mastertimer = new timerObject;
               $mastertimer->startsplit();
               print("Started $thisyear <br>");
               print("Re-distributing and Implementing BMPs <br>");
               reImplementAllBMPs($listobject, $projectid, $scenarioid, $thisyear, $sceninfo['landuseyear'], $allsegs, 1, 1, $debug);
               $totaltime = $timer->startsplit();
               print("Finished Re-distributing. Total Time: $totaltime <br>");
               print("Re-calculating BMP efficiencies <br>");
               calculateBMPEfficiencies($listobject, $projectid, $scenarioid, $allsegs, -1, $thisyear, $debug);
               $totaltime = $timer->startsplit();
               print("Finished Re-calculating. Total Time: $totaltime <br>");
               print("Creating Masslinks <br>");
               createMasslinks($listobject, $projectid, $scenarioid, $allsegs, $thisyear, $debug);
               $totaltime = $timer->startsplit();
               print("Finished Re-calculating. Total Time: $totaltime <br>");
               $bt = $mastertimer->startsplit();
               $rdmesg = "<b>Notice:</b>BMPs for $thisyear Re-distributed, implemented, calculated, $bt seconds.<br>";
               print("$rdmesg");

               $debug = 0;
               print("Finished.<br>");
            }

            if ($refreshcropneed) {
               print("<br>Re-calculating $thisyear application rates ... ");
               updateAppValues($listobject, $projectid, $scenarioid, $allsegs, $thisyear, $landuses, $debug);
               print("Finished.<br>");
               $croplus = getCropLandUses($listobject, $projectid, $scenarioid, $thisyear, $allsegs, '', $debug);
               print("Re-calculating $thisyear land-use nutrient needs and crop curves for ... ");
               foreach ($croplus as $thislu) {
                  $lun = $thislu['luname'];
                  print("$lun ... ");
                  calculateLUNeedFromCrops($listobject, $projectid, $scenarioid, $allsegs, $thisyear, $lun, $dc_method, $dc_pct, $debug);
                  calculateCropCurves($listobject, $projectid, $scenarioid, $allsegs, $thisyear, $lun, $debug);
               }
               print("Finished.<br>");
            }

            if ($regenerateinputs) {
               pg_close($listobject->dbconn);
               #$debug = 1;

               $dbc = pg_connect($connstring, PGSQL_CONNECT_FORCE_NEW);
               $listobject->dbconn = $dbc;
               $thisdate = date('r',time());
               if (count($allsegs) > 0) {
                  $sslist = "'" . join("','", $allsegs) . "'";
                  $listobject->querystring = " select trim(trailing ',' from concat_agg(subshedid || ',')) as subsheds ";
                  $listobject->querystring .= " from ";
                  $listobject->querystring .= " (select subshedid ";
                  $listobject->querystring .= " from scen_lrsegs ";
                  $listobject->querystring .= " where thisyear = $thisyear ";
                  $listobject->querystring .= "    and lrseg in ($sslist) ";
                  $listobject->querystring .= "    and scenarioid = $scenarioid ";
                  $listobject->querystring .= " group by subshedid) as foo ";
                  $listobject->performQuery();
                  $subsheds = $listobject->getRecordValue(1,'subsheds');
                  if ($debug) { print("$listobject->querystring ; <br>"); }
               } else {
                  $subsheds = '';
               }

               print("<br>Starting Model Input Updates<br>");
               $tt = $timer->startSplit();
               calcAllLoads ($listobject, $projectid, $subsheds, $polltypes, $thisyear, $legume_rate, $legume_nut, $def_nm_planbase, $defopttarg, $defmaxtarg, $vwaste_storage_lutype, $scenarioid, $debug);
               $tt = $timer->startSplit();
               print("<br>Calculating and Distributing Inputs, Total Time: $tt<br>");
               archiveRunData($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $thisdate);
               $tt = $timer->startSplit();
               print("<br>Archiving Data, Total Time: $tt<br>");

               print("<h3>Model Inputs Generated for $thisyear:</h3><hr>");

               $polltype = '1,2';
               $srcrecs = getLRClassApplications($listobject, $allsegs, $scenarioid, $polltype, $thisyear, $debug);
               $listobject->queryrecords = $srcrecs;
               $listobject->tablename = 'sourcesbytype';
               $listobject->showList();
            }

         } /* end multi-year loop */
      }
   }

   break;

}
?>

</form>
</body>
</html>
