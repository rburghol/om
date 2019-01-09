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
   $lreditlist = $_GET['lreditlist'];
   $gbIsHTMLMode = $_GET['gbIsHTMLMode'];
   $extent = $_GET['extent'];
}


if (isset($_POST['projectid'])) {
   $actiontype = $_POST['actiontype'];
   $projectid = $_POST['projectid'];
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
   $constit = $_POST['constit'];
   $gbIsHTMLMode = $_POST['gbIsHTMLMode'];
   if (is_array($landuses)) {
      $selus = join(",", $landuses);
   } else {
      $selus = $landuses;
      if (strlen($landuses) > 0) {
         $landuses = array($selus);
      }
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
require("xajax_watersupply.common.php");
print("</head>");
print("<body bgcolor=ffffff onload=\"init()\">");

#########################################
# END - Call Header Interface
#########################################

#############################
#      Process Actions     ##
#############################
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

# check to see if the users pressed the button to Import Land use
$di = $_POST['doimport'];
if (strlen($di) > 0) {
   $doimport = 1;
} else {
   $doimport = 0;
}

# check to see if the users pressed the button to Import Land use fronm the base land use table
$bl = $_POST['baselu'];
if (strlen($bl) > 0) {
   $baselu = 1;
} else {
   $baselu = 0;
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

   case 'upload':
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

   case 'editbmps':
   if ( !(count(split(",",$incoords)) >= 2) and ($currentgroup == $lastgroup) ) {
      $actiontype = 'editbmps';

   } else {
      $actiontype = 'view';
   }
   break;

   case 'applyextrapolation':
      # re-extrapolate selected data and modify the data-set to reflect these changes
      #$debug = 1;

      $selyears = join(',', $srcyears);
      if ( isset($_POST['bflu']) ) {
         $bfrecs = performBestFitLU($listobject, $goutdir, $goutpath, $landuses, $allsegs, $selyears, $targetyears, $scenarioid, $debug);
         applyBestFitLU($listobject, $landuses, $allsegs, $targetyears, $scenarioid, $projectid, $debug);
         # eliminate any BMP lu-changes that may have occured from the history, so that they are not rolled back, mucking up
         # the balance.
         foreach (split(',', $targetyears) as $tyear) {
            deleteLUChangeBMPHistory($listobject, $projectid, $scenarioid, $tyear, $allsegs, -1, $debug);
         }

         $extrapmesg = "<b>Notice:</b> Extrapolation applied for $targetyears. <br>";
      }
   break;

}
#########################################
# END - process actions
#########################################

$totaltime = $timer->startSplit();


#########################################
# Print Header Menu
#########################################
include("./medit_menu.php");
#########################################
# END - Print Header Menu
#########################################
print("<form action='$scriptname' enctype='Multipart/form-data' method=post name='activemap'>");

print("<table>");
print("<tr>");
print("   <td valign=top width=800>");
showHiddenField('projectid', $projectid);
showHiddenField('lastgroup', $currentgroup);

print("<b>Current Watershed Grouping:</b><br>");
showSegSelect($listobject, $userid, $projectid, $currentgroup, $debug);
# if this was an update form that got cancelled by a button click,
# we need to deal with the year variable, and set it right
if (is_array($thisyear)) {
   $thisyear = $lastyear;
}
#print("<table><tr><td valign=top>");
#showMultiList2($listobject, 'landuses', 'landuses', 'hspflu', 'landuse, major_lutype', $selus, "projectid = $projectid and hspflu <> ''", 'major_lutype, landuse', $debug, 4);
#print("</td><td valign=top>");
print("<br><b>Select a scenario:</b><br>");
showViewableScenarioList($listobject, $projectid, $scenarioid, $userid, $usergroupids, 'scenarioid', '', '', $debug);

showHiddenField('lastyear', $thisyear);
#print("</td></tr></table>");
showHiddenField('projectid', $projectid);
showHiddenField('lastgroup', $currentgroup);
# make all layers visible
include('./medit_layers.php');
print("<br><b>Select Function:</b><br>");
print("<table width=100% border=1><tr>");
print("<td valign=top bgcolor=#E2EFF5>");
showRadioButton('function', 'viewlanduse', $function);
print("View Land Use Summary <br>");
/*
showRadioButton('function', 'interpolate', $function);
print("Perform Linear Interpolation <br>");
*/
showRadioButton('function', 'editlanduse', $function);
print("Manually Edit Landuse<br>");
showRadioButton('function', 'editdistros', $function);
print("Edit Nutrient Application/Uptake Information<br>");
showRadioButton('function', 'editcroparea', $function);
print("Edit Crop Area<br>");
showRadioButton('function', 'editcrops', $function);
print("Edit Crop Uptake/Application Curves <br>");
print("</td>");
print("<td valign=top bgcolor=#E2EFF5>");
showRadioButton('function', 'extrapolate', $function);
print("Perform Best Fit Interpolation/extrapolation <br>");
showRadioButton('function', 'upload', $function);
print("Upload Land-Use File<br> ");
showRadioButton('function', 'downloadlanduse', $function);
print("Download Model Land-Use<br> ");
showRadioButton('function', 'import', $function);
print("Import Land Use from File (file must be uploaded first)<br> ");
showRadioButton('function', 'baselanduse', $function);
print("Copy Land Use/Crops from Project/Scenario Table ");
print("</td>");
print("</tr></table>");
print("<br>");
showSubmitButton('refreshlanduse','Change Function/Refresh');

/* START segment of code to show the results of a query */
if ( (count($allsegs) > 0) and (count($allsegs) <= 10) ) {
   $ls = join(', ', $allsegs);
   print("<br><b>Selected Segments:</b> $ls <br>");
}
print("<hr>");
# if it is a map click, we get the areas that have been selected and their eligible BMPs
switch ($function) {
   case 'interpolate':
      #$amap->debug = 1;
      # Linear Interpolation
      # Inputs:
      #    $targetyears - the years to create values for
      #    $sourceyears - the years to use as input data
      # this can be done several ways:
      # 1) Replace all target years with a pure best fit line from the source years
      # 2) Replace all target years

      print("<br><b>Select Land-Uses:</b><br>");
      showMultiLandUseMenu($listobject, $projectid, $scenarioid, $selus, 'landuses', '', 4, $debug);

      print("<b>Linear Interpolation:</b><br>");
      print("<br><b>Enter Source Years (blank will use all):</b><br>");
      showWidthTextField('srcyears', $srcyears, 10);
      print("<br><b>Enter Target Years:</b><br>");
      showWidthTextField('targetyears', $targetyears, 10);
      ShowHiddenField('actiontype','doextrapolation');
      print("<br>");
      showSubmitButton('doextrapolation','Perform Linear Interpolation');
      print("<br><b>Current Area:</b><br>");
      #print_r($lurecs);
      $lus = join(", ", $landuses);

      #$bfgraph = performLinearLU($listobject, $goutdir, $goutpath, $landuses, $allsegs, $srcyears, $targetyears, $scenarioid, $debug);
      print("Function Disabled");
      #print("<br><img src='$bfgraph'><br>");

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

      print("<b>Best Fit Extrapolation/Interpolation:</b><br>");
      print("$extrapmesg ");
      print("<br><b>Select Land-Uses:</b><br>");
      showMultiLandUseMenu($listobject, $projectid, $scenarioid, $selus, 'landuses', '', 4, $debug);

      print("<br><b>Enter Source Years (blank will use all):</b><br>");
      # screen for multiple years
      $yearfoo = "(select thisyear ";
      $yearfoo .= "from scen_lrsegs ";
      $yearfoo .= "where scenarioid = $scenarioid ";
      $yearfoo .= "group by thisyear order by thisyear) as foo ";
      $selyears = join(',', $srcyears);
      showMultiList2($listobject, 'srcyears', $yearfoo, 'thisyear', 'thisyear', $selyears, '', 'thisyear', $debug, 4);

      print("<br><b>Enter Target Years (blank will do for all source years):</b><br>");
      showWidthTextField('targetyears', $targetyears, 10);
      ShowHiddenField('actiontype','doextrapolation');
      print("<br><b>Select operations:</b>");
      print("<br>");
      $bflu = $_POST['bflu'];
      $bfcrop = $_POST['bfcrop'];
      showCheckBox('bflu',1, $bflu);
      print("Do Land Use Area<br>");
      showCheckBox('bfcrop',1, $bfcrop);
      print("Do Crop Area/Application Parameters<br>");
      print("<br>");
      showSubmitButton('doextrapolation','Refresh Best Fit Calculations');
      #print_r($lurecs);

      if (isset($_POST['doextrapolation']) or isset($_POST['applyextrapolation'])) {
         if ($bflu) {
            print("<br><b>Results of Extrapolation:</b><br>");
            # if we have just applied the extrapolation, then these tables are already created
            # Otherwise, we need to create them
            if (!($actiontype == 'applyextrapolation') ) {
               #print("Refreshing Extrapolation<br>");
               $bfrecs = performBestFitLU($listobject, $goutdir, $goutpath, $landuses, $allsegs, $selyears, $targetyears, $scenarioid, $debug);
            }
            $bfpct = $bfrecs['pct'];


            $bfgraph = graphBestFitLU($listobject, $goutdir, $goutpath, $landuses, $allsegs, $selyears, $targetyears, $scenarioid, $debug);


            print("<br><img src='$bfgraph'><br>");
         }

         if ($bfcrop) {
            $croprecs = performBestFitCrops($listobject, $goutdir, $goutpath, $landuses, $allsegs, $selyears, $targetyears, $scenarioid, $yieldcols, $debug);
            print("<br><b>Average Interpolation Highlights:</b>");
            $listobject->querystring = "select thisyear, luname, avg(maxn) as maxn, avg(maxp) as maxp from multi_yieldbestfit group by thisyear, luname order by luname, thisyear";
            $listobject->performQuery();
            $listobject->showList();
            /*
            $listobject->querystring = "select * from multi_yieldbestfit order by thisyear, subshedid, luname";
            $listobject->performQuery();
            $listobject->showList();
            */
            if (isset ($_POST['applyextrapolation']) ) {
               # replace the extrapolated/interpolated records
               applyBestFitCrops($listobject, $goutdir, $goutpath, $landuses, $allsegs, $targetyears, $scenarioid, $yieldcols, $debug);
               print("<b>Notice: </b>Crop areas and application parameters applied.<br>");
            }
         }
         print("<b>Note:</b> Applying a best fit curve to land use will delete any record of land change BMPs that were applied previously. <br> ");
         showSubmitButton('applyextrapolation','Apply Best Fit Calculations To Target Years');
      }

   break;

   case 'viewlanduse':
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

   if (isset($_POST['graphhist'])) {
      $graphhist = $_POST['graphhist'];
   }
   print("<br><b>Select Land-Uses:</b><br>");
   showMultiLandUseMenu($listobject, $projectid, $scenarioid, $selus, 'landuses', '', 4, $debug);

   print("<br><b>Select Year: </b>");
      # screen for onle year only
      $yearfoo = "(select thisyear ";
      $yearfoo .= "from scen_lrsegs ";
      $yearfoo .= "where scenarioid = $scenarioid ";
      $yearfoo .= "group by thisyear order by thisyear) as foo ";
      # singe year
      showList($listobject, 'viewyear', $yearfoo, 'thisyear', 'thisyear', '', $viewyear, $debug);
   #showWidthTextField('viewyear', $viewyear, 10);
   print("<br>");
   showCheckBox('graphhist',1, $graphhist);
   print("Graph historic land-use trend.<br>");
   showSubmitButton('refreshlu','Show Land Use');
   print("<hr>");

   if ( $graphhist ) {
      $lurecs = getHistLUArea($listobject, $allsegs, $scenarioid, $landuses, '', $debug);
      $gurl = showGenericBar($listobject, $goutdir, $goutpath, $lurecs, 'thisyear', 'luarea', 'blue', 'Historic Land-Use', 'Year', 'Area', $debug);
      print("<br><img src='$gurl'><br>");
   }

   #$debug = 1;
   $explodelu = '';
   #$debug = 1;
   $lupie = landUseTypePieChart($listobject, $projectid, $viewyear, $allsegs, $scenarioid, $explodelu, $outdir, $outurl, $debug);

   print("<table><tr>");
   print("<td colspan=2 align=center><b>Land Use by Major Category:</b></td>");
   print("</tr><tr><td>");
   if (count($lupie['data']) > 0) {
      $listobject->queryrecords = $lupie['data'];
      $listobject->tablename = 'landclass';
      $listobject->showList();
      $totalarea = 0;
      foreach ($lupie['data'] as $thisslice) {
         $totalarea += $thisslice['luarea'];
      }
      $tarea = number_format($totalarea,2);
      print("<b>Total Area:</b> $tarea <br>");
   } else {
      print("<b>Notice:</b> No records returned for $viewyear.<br>");
   }
   print("</td>");
   print("<td>");
   if (count($lupie['data']) > 0) {
      $pieurl = $lupie['url'];
      print("<img src='$pieurl'>");
   } else {
   }
   print("</td>");
   print("</tr></table>");

   print("<hr><b>Land Use by Modeled Type:</b><br>");
   $lurecs = getGroupLUArea($listobject, $landuses, $allsegs, $scenarioid, $viewyear, $debug);
   $listobject->queryrecords = $lurecs;
   $listobject->tablename = 'lusum';
   $listobject->showList();


   break;

   case 'editcroparea':
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

   print("<b>View/Edit Crops for: </b>");
   if ( !($perms & 2) ) {
      print("<br><b>Notice:</b> You do not have edit permissions on this scenario. Values will be read-only.<br>");
   }
   # screen for onle year only
   $yearfoo = "(select thisyear ";
   $yearfoo .= "from scen_lrsegs ";
   $yearfoo .= "where scenarioid = $scenarioid ";
   $yearfoo .= "group by thisyear order by thisyear) as foo ";
   # singe year
   showList($listobject, 'viewyear', $yearfoo, 'thisyear', 'thisyear', '', $viewyear, $debug);
   print("<br>");
   print("<b>Select Land-Use: </b>");
   showSingleLandUseMenu($listobject, $projectid, $scenarioid, $selus, 'landuses', '', $debug);
   print("<br>");
   showSubmitButton('refreshlu','Show Crop Info');
   print("<br>");
   # appication uptake graphing routine expects csv
   $qsegs = join(",", $allsegs);
   print("<br><i>Note: Crop values are stored by county, edits to part of a county will be applied to the whole county.</i>");
   print("<br><i> If editing multiple counties, crop acreage will be split proportional to the land use acres in each county.</i>");

   if (!isset($_POST['dc_method'])) {
      $dc_pct = 0.0;
      $dc_method = 1;
   }

   if (isset($_POST['updatecrops'])  and ($perms & 2) ) {
      # update rates if permissions are OK
      print("<br>Updating Crops ...");
      # get submitted values
      $cropname = $_POST['cropname'];
      $croparea = $_POST['croparea'];
      $dc_pct = $_POST['dc_pct'];
      $dc_method = $_POST['dc_method'];
      $cropdata = array();
      $cropdata['cropname'] = $cropname;
      $cropdata['croparea'] = $croparea;
      distributeGroupCrops($listobject, $projectid, $scenarioid, $allsegs, $viewyear, $landuses, $cropdata, $debug);
      print(" Crop area updated.<br>");
      print("Re-calculating land-use nutrient needs from crops ... ");
      calculateLUNeedFromCrops($listobject, $projectid, $scenarioid, $allsegs, $viewyear, $selus, $dc_method, $dc_pct, $debug);
      print("Finished.<br>");
      print("Re-calculating application distributions ... ");
      updateAppValues($listobject, $projectid, $scenarioid, $allsegs, $viewyear, $landuses, $debug);
      $croplus = getCropLandUses($listobject, $projectid, $scenarioid, $viewyear, $allsegs, '', $debug);
      foreach ($croplus as $thislu) {
         $lun = $thislu['luname'];
         calculateCropCurves($listobject, $projectid, $scenarioid, $allsegs, $viewyear, $lun, $debug);
      }
      print("Finished.<br>");
   }

   if (isset($_POST['refreshlu']) or isset($_POST['updatecrops']) ) {

      $groupcrops = getGroupCrops($listobject, $projectid, $scenarioid, $allsegs, $viewyear, $landuses, $debug);
      # call this just to get double crop percent from last iteration
      $grouprates = getGroupAppRates($listobject, $landuses, $allsegs, $scenarioid, $viewyear, $debug);
      $dc_pct = $grouprates['dc_pct'];
      $dc_method = $grouprates['dc_method'];

      print("<table>");
      print("<tr><td colspan=2 valign=top>");
      print("<b>All Crops:</b>");
      print("</td></tr>");
      $i = 0;
      $totalcroparea = 0;
      # add up the area, so we can display the percentage of each crop
      foreach ($groupcrops as $thiscrop) {
         $totalcroparea += $thiscrop['croparea'];
      }
      reset($groupcrops);
      foreach ($groupcrops as $thiscrop) {
         $cropname = $thiscrop['cropname'];
         $yld_units = $thiscrop['yld_units'];
         $croparea = number_format($thiscrop['croparea'],2, '.', '');
         $high_yld = number_format($thiscrop['high_yld'],2, '.', '');
         $mean_yld = number_format($thiscrop['mean_yld'],2, '.', '');
         $crop_pct = number_format(100.0*($thiscrop['croparea']/$totalcroparea),2, '.', '');
         $nper = number_format($thiscrop['nper'],2, '.', '');
         $pper = number_format($thiscrop['pper'],2, '.', '');
         print("<tr><td>$cropname (<font size=-1>High Yield = $high_yld $yld_units, Mean Yield = $mean_yld $yld_units, N/unit = $nper, P/unit = $pper</font>) : </td><td>");
         showHiddenField("cropname[$i]", $cropname);
         showWidthTextField("croparea[$i]", $croparea, 7);
         print("<font size=-1>($crop_pct%)</font></td></tr>");
         $i++;
      }
      $totalcrop = number_format($totalcroparea, 2);
      print("<tr><td colspan=2 valign=top><b>Total Crop Area:</b> $totalcrop</td></tr>");
      print("<tr><td colspan=2 valign=top><b>Double-Crop Method: </b>");
      showList($listobject, 'dc_method', 'dc_method', 'dc_method', 'mid', '', $dc_method, $debug);
      print("</td></tr>");
      print("<tr><td colspan=2 valign=top><b>Double-Crop Fraction (if manual): </b>");
      showWidthTextField("dc_pct", $dc_pct, 7);
      print("</td></tr>");
      print("<tr><td colspan=2 valign=top>");
      showSubmitButton('updatecrops','Save Crop Info');
      print("</td></tr>");

      print("</table>");
   }

   break;



   case 'editcrops':
   # this function needs:

   if ( !(strlen($viewyear ) > 0) ) {
      if (strlen($thisyear) > 0) {
         $viewyear = $thisyear;
      } else {
         $viewyear = date('Y');
      }
   }

   if ( !($perms & 2) ) {
      print("<br><b>Notice:</b> You do not have edit permissions on this scenario. Values will be read-only.<br>");
   }

   $viewcrop = $_POST['viewcrop'];

   print("<b>Select Crop: </b>");
   # screen for onle crop only
   $cropfoo = "(select cropname ";
   $cropfoo .= "from proj_crop_type ";
   $cropfoo .= "where projectid = $projectid ";
   $cropfoo .= "group by cropname order by cropname ) as foo ";
   # singe year
   showList($listobject, 'viewcrop', $cropfoo, 'cropname', 'cropname', '', $viewcrop, $debug);
   print("<br>");
   showSubmitButton('editcrop','Edit Crop Info');
   print("<br>");
   print("<br><i>Note: Crop information is stored by county, edits to part of a county will be applied to the whole county.</i>");
   print("<br><i> Editing information will affect the entire selected area.</i>");

   if (!isset($_POST['dc_method'])) {
      $dc_pct = 0.0;
      $dc_method = 1;
   }

   if (isset($_POST['updatecropcurves'])  and ($perms & 2) ) {

      print("<br>Updating Crop Curves ... ");
      # get submitted values

      if (isset($_POST['edit_distro'])) {
         # the array edit_distro should contain only those distros selected to be updated,
         # this allows us to modify only a single distro, even though the whole batch are
         # shown on the screen at the same time
         $ccinfo['edit_distro'] = $_POST['edit_distro'];
         $ccinfo['source_type'] = $_POST['source_type'];
         $ccinfo['curvetype'] = $_POST['curvetype'];
         $ccinfo['plantdate'] = $_POST['plantdate'];
         $ccinfo['harvestdate'] = $_POST['harvestdate'];
         $ccinfo['model_plant'] = $_POST['model_plant'];
         $ccinfo['need_pct'] = $_POST['need_pct'];
         # actual monthly values
         $ccinfo['jan'] = $_POST['jan'];
         $ccinfo['feb'] = $_POST['feb'];
         $ccinfo['mar'] = $_POST['mar'];
         $ccinfo['apr'] = $_POST['apr'];
         $ccinfo['may'] = $_POST['may'];
         $ccinfo['jun'] = $_POST['jun'];
         $ccinfo['jul'] = $_POST['jul'];
         $ccinfo['aug'] = $_POST['aug'];
         $ccinfo['sep'] = $_POST['sep'];
         $ccinfo['oct'] = $_POST['oct'];
         $ccinfo['nov'] = $_POST['nov'];
         $ccinfo['dec'] = $_POST['dec'];
         # update rates if permissions are OK
         updateCropCurves($listobject, $projectid, $scenarioid, $allsegs, $viewcrop, $ccinfo, $debug);
         print("Finished.<br>");
         $croplus = getCropLandUses($listobject, $projectid, $scenarioid, $thisyear, $allsegs, $viewcrop, $debug);
         print("Regenerating Weighted Curves For Land-Uses with this crop ");
         foreach ($croplus as $thislu) {
            $lun = $thislu['luname'];
            print(" ... $lun ");
            calculateCropCurves($listobject, $projectid, $scenarioid, $allsegs, $thisyear, $lun, $debug);
         }
         print("<br>Finished.<br>");
      } else {
         print("<br><b>Error:</b> No distributions selected to edit. <br>");
         print("You must select the 'Edit' checkbox next to a distribution in order to edit it.");
      }
   }

   if ( isset($_POST['editcrop']) or isset($_POST['updatecropcurvess']) ) {

      $groupcropcurves = getGroupCropCurves($listobject, $projectid, $scenarioid, $allsegs, $viewcrop, $debug);

      print("<br><b>Monthly Distributions for: </b>$viewcrop <br> ");
      print("<table cellpadding=3>");
      $i = 0;
      print("<tr><td>");
      print("<b>Edit</b>");
      print("</td>");
      print("<td>");
      print("<b>Distro Type</b>");
      print("</td>");
      print("<td>");
      print("<b>Fraction of Need</b>");
      print("</td>");
      $mos = array('jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec');
      foreach ($mos as $thismo) {
         $moval = number_format($thiscurve[$thismo],5, '.', '');
         print("<td valign=top><b>$thismo</b>");
         print("</td>");
      }
      print("<td>");
      print("<b>CheckSum</b>");
      print("</td>");

      print("</tr>");
      $o = 1;
      foreach ($groupcropcurves as $thiscurve) {
         $x = ceil(fmod($o,2));
         print("<tr bgcolor=$rc[$x] bordercolor=$rc[$x]>");
         print("<td bgcolor=$rc[$x] bordercolor=$rc[$x]>");
         showCheckBox("edit_distro[$i]", 1, $edit_nr);
         print("</td>");
         print("<td bgcolor=$rc[$x] bordercolor=$rc[$x]>");
         $source_type = $thiscurve['source_type'];
         $curvetype = $thiscurve['curvetype'];
         $plantdate = $thiscurve['plantdate'];
         $harvestdate = $thiscurve['harvestdate'];
         $model_plant = $thiscurve['model_plant'];
         $need_pct = number_format($thiscurve['need_pct'],3, '.', '');
         print(" $source_type ");
         showHiddenField("source_type[$i]", $source_type);
         showHiddenField("curvetype[$i]", $curvetype);
         showHiddenField("plantdate[$i]", $plantdate);
         showHiddenField("harvestdate[$i]", $harvestdate);
         showHiddenField("model_plant[$i]", $model_plant);
         print("</td>");
         print("<td bgcolor=$rc[$x] bordercolor=$rc[$x]>");
         showWidthTextField("need_pct[$i]", $need_pct,4);
         print("</td>");
         $mos = array('jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec');
         $moto = 0;
         foreach ($mos as $thismo) {
            $moval = number_format($thiscurve[$thismo],5, '.', '');
            $moto += $moval;
            print("<td valign=top bgcolor=$rc[$x] bordercolor=$rc[$x]>");
            showWidthTextField("$thismo" . "[$i]",$moval,4);
            print("</td>");
         }
         if ( ($moto < 0.99) or ($moto > 1.01) ) {
            $fs = "bold";
            $fc = 'red';
            $fp = '!!!';
         } else {
            $fs = "";
            $fc = 'black';
            $fp = '';
         }
         $motof = number_format($moto,3, '.', '');
         print("<td valign=top bgcolor=$rc[$x] bordercolor=$rc[$x]>");
         print("<font color='$fc' style='$fs'> $motof $fp </font>");
         print("</td>");
         $o++;

         print("</tr>");
         $i++;
      }

      print("<tr><td valign=top colspan=16>");
      showSubmitButton('updatecropcurves','Save Crop Info');
      print("</td></tr>");

      print("</table>");
   }

   break;


   case 'editdistros':
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

   print("<b>View/Edit Constituent Application Rates for: </b>");
   if ( !($perms & 2) ) {
      print("<br><b>Notice:</b> You do not have edit permissions on this scenario. Values will be read-only.<br>");
   }
   # screen for onle year only
   $yearfoo = "(select thisyear ";
   $yearfoo .= "from scen_lrsegs ";
   $yearfoo .= "where scenarioid = $scenarioid ";
   $yearfoo .= "group by thisyear order by thisyear) as foo ";
   # singe year
   showList($listobject, 'viewyear', $yearfoo, 'thisyear', 'thisyear', '', $viewyear, $debug);
   print("<br>");
   print("<b>Select Land-Use: </b>");
   showSingleLandUseMenu($listobject, $projectid, $scenarioid, $selus, 'landuses', '', $debug);
   print("<br>");
   showSubmitButton('refreshlu','Show Application/Uptake Info');
   print("<br>");
   # appication uptake graphing routine expects csv
   $qsegs = join(",", $allsegs);

   if (isset($_POST['updaterates'])  and ($perms & 2) ) {
      # update rates if permissions are OK
      print("Updating Rates ... ");
      # get submitted values
      $apprateinfo['limconstit'] = $_POST['limconstit'];
      $apprateinfo['opttarg'] = $_POST['opttarg'];
      $apprateinfo['maxtarg'] = $_POST['maxtarg'];
      $apprateinfo['nrate'] = $_POST['nrate'];
      $apprateinfo['prate'] = $_POST['prate'];
      $apprateinfo['maxnrate'] = $_POST['maxnrate'];
      $apprateinfo['maxprate'] = $_POST['maxprate'];
      $apprateinfo['maxyieldtarget'] = $_POST['maxyieldtarget'];
      $apprateinfo['optyieldtarget'] = $_POST['optyieldtarget'];
      # Should the need value be edited?
      $apprateinfo['edit_nr'] = $_POST['edit_nr'];
      # actual values for high, mean, target, and max uptake
      $apprateinfo['mean_needn'] = $_POST['mean_needn'];
      $apprateinfo['mean_needp'] = $_POST['mean_needp'];
      $apprateinfo['mean_uptn'] = $_POST['mean_uptn'];
      $apprateinfo['mean_uptp'] = $_POST['mean_uptp'];
      $apprateinfo['targ_needn'] = $_POST['targ_needn'];
      $apprateinfo['targ_needp'] = $_POST['targ_needp'];
      $apprateinfo['targ_uptn'] = $_POST['targ_uptn'];
      $apprateinfo['targ_uptp'] = $_POST['targ_uptp'];
      $apprateinfo['high_needn'] = $_POST['high_needn'];
      $apprateinfo['high_needp'] = $_POST['high_needp'];
      $apprateinfo['high_uptn'] = $_POST['high_uptn'];
      $apprateinfo['high_uptp'] = $_POST['high_uptp'];
      $apprateinfo['uptake_n'] = $_POST['uptake_n'];
      $apprateinfo['uptake_p'] = $_POST['uptake_p'];
      updateAppRates($listobject, $projectid, $scenarioid, $allsegs, $viewyear, $landuses, $apprateinfo, $debug);
      print(" Finished.<br>");
   }

   if (isset($_POST['refreshlu']) or isset($_POST['updaterates']) ) {
      $grouprates = getGroupAppRates($listobject, $landuses, $allsegs, $scenarioid, $viewyear, $debug);

      if (isset($grouprates['nrate'])) {
         # valid date returned, populate form fields with it
         $nrate = number_format($grouprates['nrate'],2);
         $prate = number_format($grouprates['prate'],2);
         $maxnrate = number_format($grouprates['maxnrate'],2);
         $maxprate = number_format($grouprates['maxprate'],2);
         $limconstit = $grouprates['limconstit'];
         # get message about limiting constituent
         $limconstit_mesg = $grouprates['limconstit_mesg'];
         # get message about rates
         $nrate_mesg = $grouprates['nrate_mesg'];
         $prate_mesg = $grouprates['prate_mesg'];
         $maxnrate_mesg = $grouprates['maxnrate_mesg'];
         $maxprate_mesg = $grouprates['maxprate_mesg'];
         $maxyieldtarget = $grouprates['maxyieldtarget'];
         $optyieldtarget = $grouprates['optyieldtarget'];
         $maxyieldtarget_msg = $grouprates['maxyieldtarget_mesg'];
         $optyieldtarget_msg = $grouprates['optyieldtarget_mesg'];
         # actual values for high, mean, target, and max uptake
         $mean_needn = number_format($grouprates['mean_needn'],2);
         $mean_needp = number_format($grouprates['mean_needp'],2);
         $mean_uptn = number_format($grouprates['mean_uptn'],2);
         $mean_uptp = number_format($grouprates['mean_uptp'],2);
         $targ_needn = number_format($grouprates['targ_needn'],2);
         $targ_needp = number_format($grouprates['targ_needp'],2);
         $targ_uptn = number_format($grouprates['targ_uptn'],2);
         $targ_uptp = number_format($grouprates['targ_uptp'],2);
         $high_needn = number_format($grouprates['high_needn'],2);
         $high_needp = number_format($grouprates['high_needp'],2);
         $high_uptn = number_format($grouprates['high_uptn'],2);
         $high_uptp = number_format($grouprates['high_uptp'],2);
         $uptake_n = number_format($grouprates['uptake_n'],2);
         $uptake_p = number_format($grouprates['uptake_p'],2);
      } else {
         # use default values
         $limconstit = $def_nm_planbase;
         $nrate = $defnrate;
         $prate = $defprate;
         $maxnrate = $defmaxnrate;
         $maxprate = $defmaxprate;
         $maxyieldtarget = $defmaxtarg;
         $optyieldtarget = $defopttarg;
      }
      print("<table>");
      print("<tr><td colspan=3 valign=top>");
      print("<b>All Applications:</b>");
      print("<ul>");
      print("<li>Limiting constituent for application of organic or multi-constituent source:");
      showList($listobject, 'limconstit', 'pollutanttype', 'pollutantname', 'typeid', '', $limconstit, $debug);
      if (strlen($limconstit_mesg) > 0) {
         print(" (<i>$limconstit_mesg</i>) ");
      }
      print("</ul>");
      print("<br><b>Optimal Nutrient Applications: </b>");
      print("<ul>");
      # what yield value to base optimal application rate on?
      print("<li>Optimal Fertilizer Target Yield: ");
      $onchange = '';
      showActiveList($listobject, 'optyieldtarget', 'targetyieldtype', 'targetyield', 'tyid', '', $optyieldtarget, $onchange, 'tyid', $debug);
      if (strlen($optyieldtarget_mesg) > 0) {
         print(" (<i>$optyieldtarget_mesg</i>) ");
      }
      print("<li>Optimal N Application Rate Multiplier: ");
      showWidthTextField('nrate',$nrate, 5);
      if (strlen($nrate_mesg) > 0) {
         print(" (<i>$nrate_mesg</i>) ");
      }
      print("<li>Optimal P Application Rate Multiplier: ");
      showWidthTextField('prate',$prate, 5);
      if (strlen($prate_mesg) > 0) {
         print(" (<i>$prate_mesg</i>) ");
      }
      print("</ul>");
      print("<br><b>Maximum Nutrient Applications: </b><br>");
      print("<ul>");
      # what yield value to base maximum application rate on?
      print("<li>Maximum Fertilizer Target Yield: ");
      showActiveList($listobject, 'maxyieldtarget', 'targetyieldtype', 'targetyield', 'tyid', '', $maxyieldtarget, $onchange, 'tyid', $debug);
      if (strlen($maxyieldtarget_mesg) > 0) {
         print(" (<i>$maxyieldtarget_mesg</i>) ");
      }
      print("<li>Maximum N Application Rate Multiplier: ");
      showWidthTextField('maxnrate',$maxnrate, 5);
      if (strlen($maxnrate_mesg) > 0) {
         print(" (<i>$maxnrate_mesg</i>) ");
      }
      print("<li>Maximum P Application Rate Multiplier: ");
      showWidthTextField('maxprate',$maxprate, 5);
      if (strlen($maxprate_mesg) > 0) {
         print(" (<i>$maxprate_mesg</i>) ");
      }
      print("</ul>");
      print("<br>");

      showSubmitButton('updaterates','Save Application/Uptake Info');

      print("</td>");
      print("<td colspan=1 valign=top>&nbsp;");
      print("<table width=100%><tr><td bgcolor='#C5CFCB' width=100%>");
      showCheckBox('edit_nr', 1, $edit_nr);
      print("<b>Manually Edit Need/Removal values</b>");
      print("<br>Max. Removal N: ");
      showWidthTextField('uptake_n', $uptake_n, 7);
      print(" P: ");
      showWidthTextField('uptake_p', $uptake_p, 7);
      print("<br>High Need N: ");
      showWidthTextField('high_needn', $high_needn, 7);
      print(" P: ");
      showWidthTextField('high_needp', $high_needp, 7);
      print("<br>High Removal N: ");
      showWidthTextField('high_uptn', $high_uptn, 7);
      print(" P: ");
      showWidthTextField('high_uptp', $high_uptp, 7);
      print("<br>Target Need N: ");
      showWidthTextField('targ_needn', $targ_needn, 7);
      print(" P: ");
      showWidthTextField('targ_needp', $targ_needp, 7);
      print("<br>Target Removal N: ");
      showWidthTextField('targ_uptn', $targ_uptn, 7);
      print(" P: ");
      showWidthTextField('targ_uptp', $targ_uptp, 7);
      print("<br>Mean Need N: ");
      showWidthTextField('mean_needn', $mean_needn, 7);
      print(" P: ");
      showWidthTextField('mean_needp', $mean_needp, 7);
      print("<br>Mean Removal N: ");
      showWidthTextField('mean_uptn', $mean_uptn, 7);
      print(" P: ");
      showWidthTextField('mean_uptp', $mean_uptp, 7);
      print("</td></tr></table>");
      #$constit = 1;
      if ($limconstit > 0) {
         foreach ($landuses as $luname) {
            $subgraph = showApplicationUptakeCurve($listobject,$goutdir, $goutpath, $viewyear, $qsegs, $luname, $scenarioid, $limconstit, $debug, 1);
            if ($subgraph <> '-1') {
               print("<br><b>Application and Uptake Data for $luname:</b><br><img src='$subgraph'>");
            } else {
               print("<br><b>Error:</b>No Application/Uptake Data Available for $luname<br>");
            }
         }
      } else {
         print("<b>Notice:</b> You must select a constituent to chart application/uptake curves.<br>");
      }
      print("</td>");

      print("</tr></table>");
   }

   break;

   case 'import':
   # check for write permissions
   #print(" PERMS: $perms <br>");
   if ( !($perms & 2) ) {
      print("<b>Error:</b> You do not have edit permissions on this scenario. <br>");
   } else {

      # import land use from a file

      # do the importing by creating a temporary table for the file contents
      # then inserting them into scen_lrsegs
      #

      # show file import dialog

      $infilename = $_POST['infilename'];
      print("<table><tr>");
      print("<td>");
      print("<b>Select a File</b>");
      $indir = $_SESSION['indir'];
      fileSelectedForm('infilename',$indir,'',$infilename);

      print("<br><b>Input in 'Columnar Format'?</b> ");
      $columns = $_POST['columns'];
      showTFListType('columns',$columns,1);
      print("<img src='/icons/info_icon_sm.gif' height=16 width=24 ");
      print(" onClick=openWindow(\"./help.php?topicname=lufileformat\",320,200)> ");
      print(" Click Here to See example of file format");
      print("<br><b>Replace All Existing Records?</b> (False will simply update lrseg/landuses in import file) ");
      $replaceall = $_POST['replaceall'];
      showTFListType('replaceall',$replaceall,1);
      print("</tr></table>");
      print("<br>");
      #print("<b>Notice:</b> This function is currently disabled.<br>");
      showSubmitButton('doimport','Import Land Use From Selected File');

      #$debug = 1;

      if ($doimport) {

         if ($columns) {
            $format = 'column';
         } else {
            $format = '';
         }
         $importtable = importLanduseFile($projectid, $scenarioid, $listobject, "$indir/$infilename", 1, $replaceall, $format, $debug);

         # after importing, set the landuseyear (max year where land use is not a projection) in the scenario table

         $listobject->querystring = "select max(thisyear) as luyear from $importtable ";
         if ($debug) {
            print("$listobject->querystring<br>");
         }
         $listobject->performQuery();
         $luyear = $listobject->getRecordValue(1,'luyear');
         if (is_numeric($luyear)) {
            $listobject->querystring = "update scenario set landuseyear = $luyear where scenarioid = $scenarioid ";
            if ($debug) {
               print("$listobject->querystring<br>");
            }
            $listobject->performQuery();
         }
      }
   }
   break;

   case 'editlanduse':
   # check for write permissions
   #print(" PERMS: $perms <br>");
   if ( !($perms & 2) ) {
      print("<b>Error:</b> You do not have edit permissions on this scenario. <br>");
   } else {

      # import land use from a file

      # do the importing by creating a temporary table for the file contents
      # then inserting them into scen_lrsegs
      #

      # show file import dialog

      $infilename = $_POST['infilename'];
      $indir = $_SESSION['indir'];
      $srclus = join(',', $_POST['srcluarr']);
      $chgpct = $_POST['chgpct'];
      $chgyear = $_POST['chgyear'];
      $dstlus = join(',', $_POST['destluarr']);
      $fromfile = $_POST['fromfile'];
      print("<b>Select Year to Edit:</b>");
      # screen for onle year only
      $yearfoo = "(select thisyear ";
      $yearfoo .= "from scen_lrsegs ";
      $yearfoo .= "where scenarioid = $scenarioid ";
      $yearfoo .= "group by thisyear order by thisyear) as foo ";
      # singe year
      showList($listobject, 'chgyear', $yearfoo, 'thisyear', 'thisyear', '', $chgyear, $debug);

      print("<br><b>Select Source Land-Uses:</b><br>");
      print("<table><tr>");
      print("<td valign=top><b>From Land Use(s)</b><br>");
      showMultiList2($listobject, 'srcluarr', 'landuses', 'hspflu', 'landuse, major_lutype', $srclus, "projectid = $projectid and hspflu <> ''", 'major_lutype, landuse', $debug, 4);
      print("</td>");
      print("<td valign=center><b> --&gt;");
      print("</td>");
      print("<td valign=top><b>To Land Use(s)</b><br>");
      showMultiList2($listobject, 'destluarr', 'landuses', 'hspflu', 'landuse, major_lutype', $dstlus, "projectid = $projectid and hspflu <> ''", 'major_lutype, landuse', $debug, 4);
      print("</td>");
      print("</tr></table>");
      print("<b>Enter a fraction to take from the source land use (number between 0.0 and 1.0)</b>");
      showWidthTextField('chgpct', $chgpct, 10);
      print("<br><b>Or, select a file containing individual fractions by model segment: </b>");
      showTFListType('fromfile',$fromfile,1);
      fileSelectedForm('infilename',$indir,'',$infilename);

      print("<img src='/icons/info_icon_sm.gif' height=16 width=24 ");
      print(" onClick=openWindow(\"./help.php?topicname=luchgfileformat\",320,200)> ");
      print(" Click Here to See example of file format");

      print("<br>");
      showSubmitButton('doadjust','Adjust Land Use');

      #$debug = 1;
      $i = 0;

      if ($_POST['doadjust']) {
         print("Processing record ");

         if ($fromfile) {
            # put the file contents into an array to loop through
            $colinfo = array(
               'srclus'=>array('required'=>0, 'type'=>'varchar(128)'),
               'dstlus'=>array('required'=>0, 'type'=>'varchar(128)'),
               'chgpct'=>array('required'=>1, 'type'=>'float8'),
               'thisyear'=>array('required'=>1, 'type'=>'float8'),
               'landseg'=>array('required'=>1, 'type'=>'varchar(16)'),
               'riverseg'=>array('required'=>1, 'type'=>'varchar(24)')
            );

            $createonly = 0;
            $info = parseCSVToFormat($listobject, $colinfo, "$indir/$infilename", 'tmp_luchgpct', 1, ',', $createonly, $debug);

            $j = $info['number'];
            print("$j records retrieved from $infilename.<br>");
            # for now, just use the values passed in in the select box, later we could allow
            # the user to specify multiple src/dest lus in a single file
            $listobject->querystring = "select riverseg || landseg as lrsegs, thisyear, chgpct ";
            $listobject->querystring .= " from tmp_luchgpct ";
            print("$listobject->querystring ; <br>");
            $listobject->performQuery();
            #$listobject->showList();
            $adjrecs = $listobject->queryrecords;
         } else {

            $adjrecs = array(
               array(
               'thisyear'=>$chgyear,
               'srclus'=>"$srclus",
               'dstlus'=>"$dstlus",
               'chgpct'=>$chgpct,
               'lrsegs'=>$allsegs
               )
            );
         }
         #print_r($adjrecs);
         foreach ($adjrecs as $thisadj) {
            $i++;
            print(" $i");
            if ( intval($i / 20.0) == ($i / 20.0) ) {
               print("<br>");
            }
            # for now, just use the values passed in in the select box, later we could allow
            # the user to specify multiple src/dest lus in a single file
            $tsrclus = "'" . join("','",split(',',$srclus) ) . "'";
            $tdstlus = "'" . join("','",split(',',$dstlus) ) . "'";
            /*
            $srclus = "'" . join("','",split(',',str_replace('|', ',', $thisadj['srclus']) )) . "'";
            $dstlus = "'" . join("','",split(',',str_replace('|', ',', $thisadj['dstlus']) )) . "'";
            */
            $subsheds = $thisadj['lrsegs'];
            if (!is_array($subsheds)) {
               $subsheds = array($thisadj['lrsegs']);
            }
            $chgpct = $thisadj['chgpct'];
            $chgyear = $thisadj['thisyear'];

            #print("$subsheds, $chgpct, $chgyear <br> ");
            distributeLandUsePct($listobject, $subsheds, $chgyear, $chgpct, $tsrclus, $tdstlus, $scenarioid, $projectid, $debug);
         }
         print(". Done.");
      }
   }
   break;

   case 'upload':

   # check for write permissions
   #print(" PERMS: $perms <br>");
   print("<b>Upload a land-use file</b><br>");
   print("<img src='/icons/info_icon_sm.gif' height=16 width=24 ");
   print(" onClick=openWindow(\"./help.php?topicname=lufileformat\",320,200)> ");
   print(" Click Here to See example of file format");
   print("<img src='/icons/info_icon_sm.gif' height=16 width=24 ");
   print(" onClick=openWindow(\"./info_lu.php?projectid=$projectid\",320,200)> ");
   print(" Click Here to See Land Use codes for this project.");
   print("<input type=hidden name='actiontype' value='upload'>");
   print("<br>");
   showHiddenField('MAX_FILE_SIZE',$maxfilesize);
   print("Choose File: <input name='userfile' type='file'>");
   print("<br>");
   showSubmitButton('submit','Upload File');


   break;

   case 'downloadlanduse':

      # do a distribution

      print("<b>Download Model Land-Use Data:</b><br>");
      print("<b>Current Active Group:</b> $groupname<br><br>");
      $baselu = $_POST['baselu'];
      print("<br><b>Select Land-Uses:</b><br>");
      showMultiLandUseMenu($listobject, $projectid, $scenarioid, $selus, 'landuses', '', 4, $debug);
      print("<br><b>Export Base Land Use?</b> (False will import the current scenario land-use) ");
      showTFListType('baselu',$baselu,1);
      print("<br><b>Enter Year to Retrieve: </b><br>");
      # screen for multiple years
      $yearfoo = "(select thisyear ";
      $yearfoo .= "from scen_lrsegs ";
      $yearfoo .= "where scenarioid = $scenarioid ";
      $yearfoo .= "group by thisyear order by thisyear) as foo ";
      # singe year
      #showList($listobject, 'thisyear', $yearfoo, 'thisyear', 'thisyear', '', $thisyear, $debug);
      # multiple years

      $selyears = join(',', $theseyears);

      showMultiList2($listobject, 'theseyears', $yearfoo, 'thisyear', 'thisyear', $selyears, '', 'thisyear', $debug, 4);
      print("<br><b>Select Land-Uses </b><br>");
      $selectedlus = join(',', $landuses);
      showMultiList2($listobject, 'landuses', 'landuses', 'hspflu', 'landuse, major_lutype', $selectedlus, "projectid = $projectid and hspflu <> ''", 'major_lutype, landuse', $debug, 4);
      print("<br><b>Select Constituents</b>:<br> ");
      print("<br>");
      showSubmitButton('ludownload','Generate Land Use File');
      #$debug = 1;
      if (isset($_POST['ludownload'])) {


         $timer->startsplit();
         print("<hr><b>Model Land-Use Data for $selyears:</b><br>");
         $filename = "landuse_$scenarioid" . ".$userid" . ".csv";
         exportLandUses($listobject, $outdir, $outurl, $filename, $scenarioid, $projectid, $allsegs, $selyears, $baselu, $debug);
         $qtime = $timer->startsplit();
         print("Query Time: $qtime<br>");
      }

   break;

   case 'baselanduse':
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

      $getlu = $_POST['getlu'];
      $getcrops = $_POST['getcrops'];
      $getcropcurves = $_POST['getcropcurves'];
      $src_scenario = $_POST['src_scenario'];
      $otherscen = $_POST['otherscen'];

      print("<b>Base Land Use Retrieval: </b><br>");
      print("This will retrieve land use values from project or scenario land-use table. <br>");
      print("<b>Get Data Year: </b>");
      # screen for onle year only
      if ($otherscen) {
         $yearfoo = "(select thisyear ";
         $yearfoo .= "from scen_subsheds ";
         $yearfoo .= "where scenarioid = $src_scenario ";
         $yearfoo .= "group by thisyear order by thisyear) as foo ";
      } else {
         $yearfoo = "(select thisyear ";
         $yearfoo .= "from lucomposite ";
         $yearfoo .= "where projectid = $projectid ";
         $yearfoo .= "group by thisyear order by thisyear) as foo ";
      }
      # singe year
      showList($listobject, 'viewyear', $yearfoo, 'thisyear', 'thisyear', '', $viewyear, $debug);
      print("<br>");
      showTFListType('getlu',$getlu,1);
      print("<b>Import Land-Use?</b><br> ");
      showTFListType('getcrops',$getcrops,1);
      print("<b>Get Crop Areas?</b><br>");
      showTFListType('getcropcurves',$getcropcurves,1);
      print("<b>Get Crop Uptake/Application Curves?</b><br>");
      showTFListType('otherscen',$otherscen,1, 'submit()');
      print("<b>Import From another scenario?</b> (False will import project base data) ");
      print("<br> &nbsp;&nbsp;&nbsp;Scenario to import from: ");
      # this shows the available scenarios to choose from. Since we want to inly allow the user to
      # import data for a year that actually exists in the scenario, we add the 'submit()' parameter as the
      # "onCHange" function, so that the screen  will refresh when the user changes the scenario to import from
      showViewableScenarioList($listobject, $projectid, $src_scenario, $userid, $usergroupids, 'src_scenario', "scenarioid <> $scenarioid ", 'submit()', $debug);
      print("<br>");
      showSubmitButton('baselu','Import Land-Use/Crops');

      #$debug = 1;
      if ($baselu) {
         if (!$otherscen) {
            $src_scenario = -1;
         }
         if ($getlu) {
            print("<br>Retrieving Land-Use Data.<br>");
            importBaseLanduse($projectid, $scenarioid, $src_scenario, $listobject, $allsegs, $viewyear, $debug);
         }
         if ($getcrops) {
            print("<br>Retrieving Crop Area Data.<br>");
            importCropArea($projectid, $scenarioid, $src_scenario, $listobject, $allsegs, $viewyear, $debug);
            print("Re-calculating land-use nutrient needs from crops ... ");
            $croplus = getCropLandUses($listobject, $projectid, $scenarioid, $viewyear, $allsegs, '', $debug);
            foreach ($croplus as $thislu) {
               $lun = $thislu['luname'];
               print(" ... $lun ");
               calculateLUNeedFromCrops($listobject, $projectid, $scenarioid, $allsegs, $viewyear, $lun, -1, 0, $debug);
            }
            print("Finished.<br>");
            print("Re-calculating application distributions ... ");
            updateAppValues($listobject, $projectid, $scenarioid, $allsegs, $viewyear, '', $debug);
            print("Finished.<br>");
         }
         if ($getcropcurves) {
            print("<br>Retrieving Crop Curve Data.<br>");
            importCropCurves($projectid, $scenarioid, $src_scenario, $listobject, $allsegs, $viewyear, $debug);
            print("Finished. <br>");
            $croplus = getCropLandUses($listobject, $projectid, $scenarioid, $viewyear, $allsegs, '', $debug);
            print("Regenerating Weighted Curves For Land-Uses with imported crops ");
            foreach ($croplus as $thislu) {
               $lun = $thislu['luname'];
               print(" ... $lun ");
               calculateCropCurves($listobject, $projectid, $scenarioid, $allsegs, $viewyear, $lun, $debug);
            }
            print("Finished. <br>");
         }
         print("Base Land Use Retrieved for $viewyear.<br>");
      }
   }

   break;

}

print("   </td><td valign=top>");
include ('./medit_controlfooter.php');
print("   </td>");
print("</tr>");
print("</table>");


?>

</form>
</body>
</html>
