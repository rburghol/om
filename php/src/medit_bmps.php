<html>


<?php
#########################################
# Process Form Variable Inputs
#########################################

if (isset($_GET['projectid'])) {
   $actiontype = $_GET['actiontype'];
   $projectid = $_GET['projectid'];
   $currentgroup = $_GET['currentgroup'];
   $scenarioid = $_GET['scenarioid'];
   $thisyear = $_GET['thisyear'];
   #$lreditlist = $_GET['lreditlist'];
   $gbIsHTMLMode = $_GET['gbIsHTMLMode'];
}

if (isset($_POST['projectid'])) {
   $actiontype = $_POST['actiontype'];
   $projectid = $_POST['projectid'];
   $currentgroup = $_POST['currentgroup'];
   $lastgroup = $_POST['lastgroup'];
   $scenarioid = $_POST['scenarioid'];
   $thisyear = $_POST['thisyear'];
   $targetyears = $_POST['targetyears'];
   $srcyears = $_POST['srcyears'];
   $typeid = $_POST['typeid'];
   $incoords = $_POST['INPUT_COORD'];
   $bmpname = $_POST['bmpname'];
   $lreditlist = $_POST['lreditlist'];
   $graphhist = $_POST['graphhist'];
   $function = $_POST['function'];
   $gbIsHTMLMode = $_POST['gbIsHTMLMode'];
   $rejectone = $_POST['rejectone'];
}

if (!isset($debug)) {
   $debug = 0;
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
require("xajax_watersupply.common.php");
print("</head>");
print("<body bgcolor=ffffff onload=\"init()\">");

#########################################
# END - Call Header
#########################################


#############################
#      Process Actions     ##
#############################
#########################################
# Custom Button To change to new BMP
#########################################
$ed = $_POST['refreshbmps'];
if (strlen($ed) > 0) {
   $actiontype = 'viewbmps';
}
$ed = $_POST['editbmps'];
if (strlen($ed) > 0) {
   $actiontype = 'editbmps';
}
$ed = $_POST['implementbmps'];
if (strlen($ed) > 0) {
   $actiontype = 'reimplement';
}

$ed = $_POST['reimplementbmps'];
if (strlen($ed) > 0) {
   $actiontype = 'reimplementall';
   $doluchange = $_POST['doluchange'];
}

$ed = $_POST['doimport'];
if (strlen($ed) > 0) {
   $doimport = 1;
}
$ed = $_POST['implementluchange'];
if (strlen($ed) > 0) {
   $actiontype = 'implementluchange';
}
$ed = $_POST['applyextrapolation'];
if (strlen($ed) > 0) {
   $actiontype = 'applyextrapolation';
}
$ed = $_POST['rollbackbmps'];
if (strlen($ed) > 0) {
   $actiontype = 'rollbackbmps';
}
$ed = $_POST['showlubmps'];
if (strlen($ed) > 0) {
   $showlubmps = 1;
}

#########################################
# END actiontype over-rides
#########################################

#########################################
# Now, process actions
#########################################
switch ($actiontype) {

   case 'upload':
   #print("Trying to Upload File.<br>");
   #print_r($_FILES);
   if ( isset($_FILES['userfile'])) {
      if ($_FILES['userfile']['name'] <> '') {
        $userfile_name = $_FILES['userfile']['name'];
        $userfile = $_FILES['userfile']['tmp_name'];
        $file_id = $userfile_name;
        $fpath = $_SESSION['indir'];

        if (!copy($userfile, "$fpath/$file_id")) {
           #print_r($_FILES);
           print ("failed to copy $userfile_name to $fpath/$file_id <br>\n");
        } else {
          print ("$userfile_name uploaded successfully.<br>");
          $resfile = "$fpath/$file_id";
        }
      }
   }
   break;

   case 'updatebmp':
   if (!(count(split(",",$incoords)) >= 2) and ($currentgroup == $lastgroup)) {
      $as = $adminsetuparray['lrsegbmpedit'];
      print("Updating - $lreditlist<br>");
      $totalarea = $invars['eligarea'];
      $bmparea = $invars['bmparea'];
      #$debug = 1;
      distributeBMP($listobject, $bmpname, $allsegs, $thisyear, $bmparea, $scenarioid, $projectid, $debug);
     # $listobject->queryrecords = $bmprecs;
      #$listobject->showList();
      $actiontype = 'viewbmps';
   }
   break;

   case 'editbmps':
   if ( !(count(split(",",$incoords)) >= 2) and ($currentgroup == $lastgroup) ) {
      $actiontype = 'editbmps';
      $function = 'editbmps';
   } else {
      $actiontype = 'viewbmps';
   }
   break;

   case 'reimplementall':
      $timer->startsplit();
      print("Re-distributing for $targetyears<br>");
      $tyrs = split(',',$targetyears);
      #$debug = 1;
      foreach ($tyrs as $thisyear) {
         $timer->startsplit();
         $mastertimer = new timerObject;
         $mastertimer->startsplit();
         print("Started $thisyear <br>");
         print("Re-distributing and Implementing BMPs <br>");
         reImplementAllBMPs($listobject, $projectid, $scenarioid, $thisyear, $sceninfo['landuseyear'], $allsegs, $doluchange, 1, $debug);
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
      }
      $debug = 0;

   break;

   case 'rollbackbmps':
      $debug = 0;
      $tyrs = split(',',$targetyears);
      foreach ($tyrs as $thisyear) {
         $timer->startsplit();
         rollBackAllLUChangeBmps($listobject, $projectid, $scenarioid, $thisyear, $allsegs, $debug);
         $bt = $timer->startsplit();
         $impmesg = "<b>Processed:</b> Rolled back LU Change BMPs for $thisyear, $bt seconds.<br>";
         print("$impmesg");
      }
      $impmesg = "<b>Processed:</b> Rolled back LU Change BMPs for $targetyears, $bt seconds.<br>";
      $debug = 0;
   break;

   case 'reimplement':
      $timer->startsplit();
      $debug = 0;
      distributeBMPsToLU($listobject, $projectid, $scenarioid, $thisyear, $allsegs, $typeid, $debug);
      clearBMPImplementation($listobject, $projectid, $scenarioid, $targetyears, $allsegs, $typeid, $debug);
      implementOneEfficGroup($listobject, $projectid, $scenarioid, $thisyear, $allsegs, $typeid, $debug);
      calculateBMPEfficiencies($listobject, $projectid, $scenarioid, $allsegs, $typeid, $thisyear, $debug);
      $bt = $timer->startsplit();
      $impmesg = "<b>Processed:</b> BMP Efficiency Values Implemented, $bt seconds.";
      $debug = 0;
   break;

   case 'implementluchange':
      $timer->startsplit();
      $debug = 0;
      implementAllLUChangeBMPs($listobject, $projectid, $scenarioid, $thisyear, $sceninfo['landuseyear'], $allsegs, $debug);
      calculateBMPEfficiencies($listobject, $projectid, $scenarioid, $allsegs, $typeid, $thisyear, $debug);
      $bt = $timer->startsplit();
      $luchgmesg = "<b>Processed:</b> Land Use Change BMPs Performed, $bt seconds.";
      $debug = 0;
   break;

   case 'applyextrapolation':

      # check for write permissions
      if ( !($perms & 2) ) {
         print("<b>Error:</b> You do not have edit permissions on this scenario. <br>");
      } else {
         $timer->startsplit();
         $debug = 0;
         $selyears = join(',', $srcyears);
         performBestFitBMP($listobject, $typeid, $allsegs, $selyears, $targetyears, $scenarioid, $projectid, $rejectone, $debug);
         applyBestFitBMP($listobject, $typeid, $allsegs, $targetyears, $scenarioid, $projectid, $debug);
         $bt = $timer->startsplit();
         $luchgmesg = "<b>Processed:</b> BMP Extrapolation Applied, $bt seconds.";
         $debug = 0;
      }
   break;



}
#########################################
# END - process actions
#########################################

$totaltime = $timer->startSplit();

#########################################
# Print HTM Interface Headers and Menu
#########################################
include("./medit_menu.php");
#########################################
# END - Print Headers
#########################################
print("<form action='$scriptname' enctype='Multipart/form-data' method=post name='activemap'>");

print("<table>");
print("<tr>");
print("   <td valign=top width=800>");
showHiddenField('projectid', $projectid);
showHiddenField('lastgroup', $currentgroup);

print("<b>Current Watershed Grouping:</b><br>");
showSegSelect($listobject, $userid, $projectid, $currentgroup, $debug);
print("<br><b>Select a scenario:</b>");
$scenclause = "projectid = $projectid and ( (ownerid = $userid  and operms >= 4) ";
$scenclause .= " or ( groupid in ($usergroupids) and gperms >= 4 ) ";
$scenclause .= " or (pperms >= 4) ) ";
#$debug = 1;
showList($listobject, 'scenarioid', 'scenario', 'scenario', 'scenarioid', $scenclause, $scenarioid, $debug);
print("<b>Enter A Year:</b>");
showWidthTextField('thisyear', $thisyear, 10);
print("<br><b>Select BMP </b><br>");
$bmpfoo = " ( (select bmp_desc, typeid  ";
$bmpfoo .= "   from bmp_types  ";
$bmpfoo .= "   where projectid = $projectid ";
$bmpfoo .= "   order by bmp_desc ";
$bmpfoo .= "  ) UNION (  ";
$bmpfoo .= "  select '*** All Bmps ***' as bmp_desc, -1 as typeid ";
$bmpfoo .= "  ) ";
$bmpfoo .= " ) as foo ";

showList($listobject, 'typeid', $bmpfoo, 'bmp_desc', 'typeid', '', $typeid, $debug);
# make all layers visible
include('./medit_layers.php');
print("<br><b>Select Function:</b><br>");
print("<table width=100% border=1><tr>");
print("<td valign=top bgcolor=#E2EFF5>");
showRadioButton('function', 'viewbmps', $function);
print("View/Edit BMP Group<br>");
showRadioButton('function', 'lubmps', $function);
print("View BMPs By Land-Use<br>");
/*
showRadioButton('function', 'interpolate', $function);
print("Perform Linear Interpolation <br>");
*/
showRadioButton('function', 'extrapolate', $function);
print("Perform Best Fit Interpolation/extrapolation <br>");
showRadioButton('function', 'importscenbmps', $function);
print("Copy BMPs from Another Scenario ");

print("</td>");
print("<td valign=top bgcolor=#E2EFF5>");
/*
showRadioButton('function', 'implement', $function);
print("Implement BMPs <br>");
*/
showRadioButton('function', 'upload', $function);
print("Upload BMP File<br> ");
showRadioButton('function', 'import', $function);
print("Import BMPs from File <br>");
showRadioButton('function', 'export', $function);
print("Export BMPs to File <br>");
/*
showRadioButton('function', 'luchange', $function);
print("Apply LU Change BMPs <br>");
*/
showRadioButton('function', 'rollback', $function);
print("Roll-back all LU Change BMPs <br>");
showRadioButton('function', 'reimplement', $function);
print("Re-implement All BMPs/Recalculate Masslinks ");
print("</td>");
print("</tr></table>");
print("<br>");
showSubmitButton('refreshbmps','Change Function');
print("<img src='/icons/info_icon_sm.gif' height=16 width=24 ");
print(" onClick=openWindow(\"./info_allbmps.php?projectid=$projectid&debug=$debug\",320,200)> Click for BMP Efficiency Summary <br>");

print("<hr>");

/* START segment of code to show the results of a query */

# if it is a map click, we get the areas that have been selected and their eligible BMPs
if ( !(strlen($targetyears ) > 0) ) {
   if (strlen($thisyear) > 0) {
      $targetyears = $thisyear;
   } else {
      $targetyears = $sceninfo['landuseyear'];
   }
}

if (count($allsegs) <= 5) {
   $ls = join(', ', $allsegs);
   print("<b>Selected Segments:</b> $ls <br>");
}

switch ($function) {

   case 'export':
   if (isset($_POST['srcyears'])) {
      $landuses = $_POST['landuses'];
      $selus = join(',', $landuses);
      $constits = $_POST['constits'];
      $selcons = join(',', $constits);
      $bmpres = $_POST['bmpres'];
   }
   print("<br><b>Select Land-Uses:</b><br>");
   showMultiList2($listobject, 'landuses', 'landuses', 'hspflu', 'landuse, major_lutype', $selus, "projectid = $projectid and hspflu <> ''", 'major_lutype, landuse', $debug, 4);
   print("<br><b>Select Constituents:</b><br>");
   showMultiList2($listobject, 'constits', 'pollutanttype', 'typeid', 'pollutantname', $selcons, " typeid in (1,2, 8) ", 'pollutantname', $debug, 4);
   print("<br><b>Enter Desired Years (blank will use all):</b><br>");
   # screen for multiple years
   $yearfoo = "(select thisyear ";
   $yearfoo .= "from scen_lrseg_bmps ";
   $yearfoo .= "where scenarioid = $scenarioid ";
   #$yearfoo .= "   and $lrclause ";
   $yearfoo .= "group by thisyear order by thisyear) as foo ";
   $baseyear = $_POST['baseyear'];
   $selyears = join(',', $srcyears);
   showMultiList2($listobject, 'srcyears', $yearfoo, 'thisyear', 'thisyear', $selyears, '', 'thisyear', $debug, 4);

   print("<br>");
   showRadioButton('bmpres', 'masslinks', $bmpres);
   print("Export Mass-Link Values (1 per land-use) <br>");
   showRadioButton('bmpres', 'bmpeffic', $bmpres);
   print("Export Individual BMP Values <br>");
   print("<br>");
   showSubmitButton('exportbmps','Export BMPs');
   if (isset($_POST['exportbmps'])) {
      print("<b>Notice:</b> Exporting BMPS<br>");
      #$debug = 1;
      switch ($bmpres) {

         case 'masslinks':
            exportMasslinks($listobject, $scenarioid, $outdir, $outurl, $allsegs, $sceninfo['shortname'], $landuses, $srcyears, $constits, $debug);
         break;

         case 'bmpeffic':
            exportSubTypeBMPs($listobject, $projectid, $scenarioid, $outdir, $outurl, $allsegs, $landuses, $srcyears, $constits, $debug);
            exportBMPAreaEffic($listobject, $scenarioid, $outdir, $outurl, $allsegs, $landuses, $srcyears, $constits, $debug);
            exportLUChangeBMPs($listobject, $projectid, $scenarioid, $outdir, $outurl, $allsegs, $landuses, $srcyears, $constits, $debug);
         break;

         default:
            exportMasslinks($listobject, $scenarioid, $outdir, $outurl, $allsegs, $landuses, $srcyears, $constits, $debug);
         break;
      }
   }

   break;

   case 'lubmps':
   # view the bmps for one year, and land use
      $luname = $_POST['luname'];
      print("<b>Select Land-Use: </b> ");
      showList($listobject, 'luname', 'landuses', 'landuse', 'hspflu', " projectid = $projectid and hspflu <> '' and hspflu is not null ", $luname, $debug);
      print("<br>");
      showSubmitButton('showlubmps','Show BMPs');
      #$debug = 1;
      if (strlen($luname) > 0) {
         $lumasslinks = getOneYearLandUseLRMasslinks($listobject, $luname, $allsegs, $thisyear, $scenarioid, $projectid, $debug);
         $lubmps = getOneYearLandUseLRBmps($listobject, $luname, $allsegs, $thisyear, $scenarioid, $projectid, $debug);
      }
      $listobject->queryrecords = $lumasslinks;
      $listobject->tablename = 'lumasslink';
      $listobject->showList();

      $listobject->queryrecords = $lubmps;
      $listobject->tablename = 'lrseglubmp';
      $listobject->showList();

   break;

   case 'viewbmps':
   #$debug = 1;

#   if ( (count($allsegs) > 0) or 1 ) {
   if ( $typeid > 0 ) {
      # process query -

      # get information for this BMP Super-type
      $split = $timer->startsplit();
      $qr = getOneLRBmpType($listobject, $typeid, $allsegs, $thisyear, $scenarioid, $projectid, $debug);
      $split = $timer->startsplit();
      #print_r($qr);
      #print("Query Time: $split<br>");
      if (count($qr) > 0) {
         $bmp_desc = $listobject->getRecordValue(1,'bmp_desc');
         $eligarea = $listobject->getRecordValue(1,'eligarea');
         $elig = number_format( $eligarea,1);
         $bmparea = number_format($listobject->getRecordValue(1,'bmparea'),1);
         $bmpimplemented = number_format($listobject->getRecordValue(1,'bmpimplemented'),1);
      } else {
         $norecs = 1;
      }

      showCheckBox('graphhist',1, $graphhist);
      print("Graph historic application rates of $bmp_desc<br>");

      if ($graphhist) {
         # show graph of all BMPs in this category   #
         print("<b>Historic implementation rates for $bmp_desc:</b><br>");
         $split = $timer->startsplit();
         $bmpgraph = showBMPTypeAcreage($listobject, $goutdir, $goutpath, $typeid, $allsegs, '', $scenarioid, $projectid, $debug);
         $split = $timer->startsplit();
        # print("Query Time: $split<br>");
         print("<img src='$bmpgraph'>");
      }

      # Display this BMP summary information
      print("<br><b>Summary BMP Report for:</b> $thisyear <br>");
      $ge = getBMPGroupEffic($listobject, $typeid, $allsegs, $thisyear, $scenarioid, $projectid, $debug);
      $split = $timer->startsplit();
      #print_r($qr);
     # print("Query Time: $split<br>");

      if ($norecs) {
         print("There are no bmps records entered for this year.<br>");
      } else {
         print("<br><b>BMPs of type:</b> $bmp_desc <br>");
         print("<b>Eligible area:</b> $elig <br>");
         print("<b>Area of bmps submitted:</b> $bmparea <br>");
         print("<b>Area of bmps applied:</b> $bmpimplemented <br>");
         print("<b>Constituents Affected / Reduction %:</b><br>");
         print("(Estimated efficiency values represent the reduction over ALL eligible land uses, not just those which have BMPs applied. Note that Efficiency Values Will Not Reflect BMP Edits Until you run <i>Implement BMPs/Recalculate Masslinks</i>.)");
         print("<ul>");
         foreach ($ge as $thisge) {
            $gn = $thisge['pollutantname'];
            $wr = number_format(100.0 * $thisge['wgted'] / $eligarea,2);
            print("<li><b>$gn / </b> $wr %");
         }
         print("</ul>");
      }

      #print_r($allsegs);
      $split = $timer->startsplit();
      $theserecs = getLRBmps($listobject, $typeid, $allsegs, $thisyear, $scenarioid, $projectid, $debug);
      $split = $timer->startsplit();
      #print("Query Time: $split<br>");
      #print_r($theserecs);

      $listobject->tablename = 'lrsegbmpedit';
      $listobject->queryrecords = $theserecs;
      #$listobject->showList();

      $as = $adminsetuparray['lrsegedit'];
      $sl = 1;
      $i = 0;
      print("<b>Sub-Types of this BMP:</b><br>");
      print("<table>");
      print("<tr><td><b>Select</b></td><td><b>BMP Abbrev</b></td><td><b>BMP</b></td><td><b>Eligible Area</b></td><td><b>BMP <i>Area</i></b></td><td><b>BMP S<i>ubmitted</i></b></td></tr>");
      foreach ($theserecs as $thisrec) {
         if ($i > 0) {
            $sl = 0;
         }
         $bmptext = $thisrec['bmptext'];
         $bmpname = $thisrec['bmpname'];
         $bmpid = $thisrec['bmpid'];
         $eligarea = number_format($thisrec['eligarea'],2);
         $bmparea = number_format($thisrec['bmparea'],2);
         $bmpsubbed = number_format($thisrec['bmpsubbed'],2);
         print("<tr><td valign=top><INPUT TYPE=RADIO NAME='bmpname' VALUE='$bmpname'></td>");
         print("<td valign=top><img src='/icons/info_icon_sm.gif' height=16 width=24 ");
         print(" onClick=openWindow(\"./info_bmp.php?bmpid=$bmpid\",320,200)></td>");
         print("<td valign=top>$bmpname</td><td valign=top>$bmptext</td>");
         print("<td valign=top>$eligarea</td><td valign=top>$bmparea</td>");
         print("<td valign=top>$bmpsubbed</td></tr>");
         $i++;
      }
      print("</table>");
     # print("Query Time: $split<br>");

      showSubmitButton('editbmps','Edit Selected BMP');
   } else {
      print("<b>Error:</b> You must select a single BMP Type for this query.<br>");
   }
   break;

   case 'editbmps':
      # this function needs:
      # lreditlist - lrsegs to edit
      # listobject
      # bmpname
      # year
      # scenarioid
      # projectid

      # check for write permissions
      if ( !($perms & 2) ) {
         print("<b>Error:</b> You do not have edit permissions on this scenario. <br>");
      } else {

         # force creation of bmps with all years present, default will do for only selected
         tempBMPTables($listobject, $typeid, $allsegs, $scenarioid, '', $debug);

         # lrsegs and bmps have been submitted, try to update them
         $conv = 0; # true or false to convert BMP input unts to area
         $theserecs = getOneLRBmps($listobject, $bmpname, $allsegs, $thisyear, $scenarioid, $projectid, $conv, $debug);

         #print("<br>Editing BMP - $bmpname <br>");
         $listobject->tablename = '';
         $listobject->queryrecords = $theserecs;
        # $listobject->showList();

         $as = $adminsetuparray['lrsegbmpedit'];
         $sl = 1;
         $i = 0;
         print("<table>");
         foreach ($theserecs as $thisrec) {
            if ($i > 0) {
               $sl = 0;
            }
            showFormVars($listobject,$thisrec,$as, $sl, 0, $debug, 0);
            $i++;
         }
         print("</table>");
         ShowHiddenField('actiontype','updatebmp');
         ShowHiddenField('function','viewbmps');
         ShowHiddenField('lreditlist',$lreditlist);
         showSubmitButton('updatebmp','Update BMP');

         # show graph of bmp over time
         if (strlen($bmpname) > 0) {
            $bmpgraph = showBMPAcreage($listobject, $goutdir, $goutpath, $bmpname, $allsegs, '', $scenarioid, $projectid, $debug);
            print("<br><img src='$bmpgraph'>");
            #print("Location- $bmpgraph");
         }
      }
   break;

   case 'rollback':

      # check for write permissions
      if ( !($perms & 2) ) {
         print("<b>Error:</b> You do not have edit permissions on this scenario. <br>");
      } else {
         # this function needs:
         print("$impmesg<br>");
         print("This roll back all Land Use Change BMPs. <br>");
         print("<b>Enter years(s) to process:<br>");
         showWidthTextField('targetyears', $targetyears, 10);
         showSubmitButton('rollbackbmps','Roll-back BMPs');
      }

   break;

   case 'implement':
      # this function needs:
      print("$impmesg<br>");
      print("This will implement submitted Efficiency BMPs. (Land-Use change BMPs should be implemented prior to this step. Go to Land-Use screen to process Land-Use Change BMPs)<br>");
      print("<b>Enter years(s) to process:<br>");
      showWidthTextField('targetyears', $targetyears, 10);
      showSubmitButton('implementbmps','Implement BMPs');

   break;

   case 'reimplement':

      # check for write permissions
      if ( !($perms & 2) ) {
         print("<b>Error:</b> You do not have edit permissions on this scenario. <br>");
      } else {

         # this function needs:
         print("$impmesg<br>");
         print("This will re-implement all BMPs. Land-Use change BMPs will be implemented as the first step in this process if selected, otherwise, only efficiencies will be recalculated. BMPs which are both lu change and efficiency, such as buffers, will have their efficiency re-calculated. <br>");
         print("<b>Enter years(s) to process:<br>");
         showWidthTextField('targetyears', $targetyears, 10);
         ShowHiddenField('doluchange',1);
         /*
         print("<br>&nbsp;&nbsp;&nbsp;<b>Do Land Use Change BMPs? </b>");
         showTFListType('doluchange',$doluchange,1);
         */
         showSubmitButton('reimplementbmps','Implement All BMPs');
      }

   break;

   case 'luchange':
      # this function needs:
      print("$luchgmesg<br>");
      print("This will implement submitted Land Use Change BMPs.<br>");
      print("<b>Enter years(s) to process:<br>");
      showWidthTextField('targetyears', $targetyears, 10);
      showSubmitButton('implementluchange','Implement BMPs');

   break;

   case 'extrapolate':
      #$amap->debug = 1;
      #print_r($allsegs);
      # best fit extrap/interp
      # Inputs:
      #    $targetyears - the years to create values for
      #    $sourceyears - the years to use as input data
      # this can be done several ways:
      # 1) Replace all target years with a pure best fit line from the source years
      # 2) Replace all target years

      # Print ou the extrapolation Menu
      print("$luchgmesg<br>");
      print("<b>Best Fit Extrapolation/Interpolation:</b><br>");
      # screen for onle year only
      $yearfoo = "(select thisyear ";
      $yearfoo .= "from scen_lrseg_bmps ";
      $yearfoo .= "where scenarioid = $scenarioid ";
      #$yearfoo .= "   and $lrclause ";
      $yearfoo .= "group by thisyear order by thisyear) as foo ";
      $baseyear = $_POST['baseyear'];
      print("<b>Select Years for Historical Best Fit curve :</b><br> ");
      $selyears = join(',', $srcyears);
      showMultiList2($listobject, 'srcyears', $yearfoo, 'thisyear', 'thisyear', $selyears, '', 'thisyear', $debug, 4);

      print("<br><b>Enter Target Years (blank will do for all source years):</b><br>");
      showWidthTextField('targetyears', $targetyears, 10);
      ShowHiddenField('actiontype','doextrapolation');
      print("<br><b>Exclude single entries from the extrapolation?: </b>");
      showTFListType('rejectone',$rejectone,1);
      print("<br>");
      showSubmitButton('doextrapolation','Refresh Best Fit Calculations');
      print("<br><b>Results of Extrapolation:</b><br>");
      #print_r($lurecs);

     # $debug = 1;

      # if we have just applied the extrapolation, then these tables are already created
      # Otherwise, we need to create them
      if (!($actiontype == 'applyextrapolation') ) {
         print("Refreshing Extrapolation<br>");
         performBestFitBMP($listobject, $typeid, $allsegs, $selyears, $targetyears, $scenarioid, $projectid, $rejectone, $debug);
      }
      $bfgraph = graphBestFitBMP($listobject, $goutdir, $goutpath, $typeid, $allsegs, $selyears, $targetyears, $scenarioid, $debug);

      $bmpgraphtext = '1st &#10;&#13 2nd';
      print("<br><img src='$bfgraph' title='$bmpgraphtext'><br>");

      # check for write permissions
      if ( ($perms & 2) ) {
         showSubmitButton('applyextrapolation','Apply Best Fit Calculations To Target Years');
      }


   break;

   case 'upload':

   # check for write permissions
   #print(" PERMS: $perms <br>");
   print("<b>Upload a BMP file</b><br>");
   print("<img src='/icons/info_icon_sm.gif' height=16 width=24 ");
   print(" onClick=openWindow(\"./help.php?topicname=bmpfileformat\",320,200)> ");
   print(" Click Here to See example of file format");
   print("<input type=hidden name='actiontype' value='upload'>");
   print("<br>");
   showHiddenField('MAX_FILE_SIZE',$maxfilesize);
   print("Choose File: <input name='userfile' type='file'>");
   print("<br>");
   showSubmitButton('submit','Upload File');

   break;

   case 'import':

      # check for write permissions
      if ( !($perms & 2) ) {
         print("<b>Error:</b> You do not have edit permissions on this scenario. <br>");
      } else {
         # show file import dialog
         $infilename = $_POST['infilename'];
         print("<table><tr>");
         print("<td>");
         $indir = $_SESSION['indir'];
         fileSelectedForm('infilename', $indir,'',$infilename,1);
         print("<br><b>Input in 'Columnar Format'?</b> ");
         $columns = $_POST['columns'];
         showTFListType('columns',$columns,1);
         print("<br><b>BMP Spatial Resolution</b>: ");
         # hard wire this input resolution
         # later, this can be a select function perhaps
         # $bmpres = 'lrseg';
         $bmpres = $_POST['bmpres'];
         showList($listobject, 'bmpres', 'bmpresolution', 'res_desc', 'res_abbrev', '', $bmpres, $debug);
         print("<br><b>Replace All Existing Records?</b> (False will simply update lrseg/landuses in import file) ");
         $replaceall = $_POST['replaceall'];
         showTFListType('replaceall',$replaceall,1);
         print("</td>");
         print("<td>");
   /*
         # select input bmp resolution - currently disable - must use lrseg
         showRadioButton('bmpres', 'catfips', $bmpres);
         print("Phase 5 Land/River Segment ");
   */
         print("</td>");
         print("</tr></table>");
         print("<br>");
         showSubmitButton('doimport','Import BMPs From Selected File');

         #$debug = 1;
         if ( (!$disabled) and (isset($_POST['doimport'])) ) {
            # multip[le files is disabled
            #foreach ($infiles as $infilename) {
            print("<br>Parsing $infilename<br>");

            if ($columns) {
               $format = 'column';
            } else {
               $format = 'row';
            }
            importBMPFile($projectid, $scenarioid, $listobject, $allsegs, "$indir/$infilename", 20, $replaceall, $format, $bmpres, $debug);
         } else {
            print("The selected BMP input resolution, '$bmpres', is disabled.<br>");
         }
      }

   break;

   case 'importscenbmps':
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

      print("<b>BMP Retrieval: </b><br>");
      print("This will retrieve BMP values from project or scenario land-use table. <br>");
      print("<b>Get BMPs for: </b>");
      showWidthTextField('viewyear', $viewyear, 10);
      $src_scenario = $_POST['src_scenario'];
      $otherscen = $_POST['otherscen'];
      print("<br><b>Import From another scenario?</b> (False will import project base data) ");
      showTFListType('otherscen',$otherscen,1);
      print(" Scenario to import from: ");
      showViewableScenarioList($listobject, $projectid, $src_scenario, $userid, $usergroupids, 'src_scenario', "scenarioid <> $scenarioid ", '', $debug);
      print("<br>");
      showSubmitButton('copybmps','Import BMPs');

      #$debug = 1;
      if (isset($_POST['copybmps'])) {
         if (!$otherscen) {
            $src_scenario = -1;
         }
         importBaseBMPs($projectid, $scenarioid, $src_scenario, $listobject, $allsegs, $viewyear, $debug);
         print("BMPs Retrieved for $viewyear.<br>");
      }
   }

   break;

}

print("   </td><td valign=top>");
include ('./medit_controlfooter.php');
print("   </td>");
print("</tr>");
print("</table>");
print("</form>");

?>

</body>
</html>
