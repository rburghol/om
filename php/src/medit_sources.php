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
}


if (isset($_POST['projectid'])) {
   $actiontype = $_POST['actiontype'];
   $projectid = $_POST['projectid'];
   $currentgroup = $_POST['currentgroup'];
   $lastgroup = $_POST['lastgroup'];
   $scenarioid = $_POST['scenarioid'];
   $thisyear = $_POST['thisyear'];
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
   $sources = $_POST['sources'];
   $function = $_POST['function'];
   $viewyear = $_POST['viewyear'];
   $constits = $_POST['constits'];
   $gbIsHTMLMode = $_POST['gbIsHTMLMode'];
   if (is_array($landuses)) {
      $selus = join(",", $landuses);
   } else {
      $selus = '';
   }
}

if ( !(strlen($thisyear ) > 0) ) {
   if (strlen($lastyear) > 0) {
      $thisyear = $lastyear;
   } else {
      $thisyear = date('Y');
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
# Now, process actions
#########################################

#########################################
# Check for action button over-rides
#########################################
$ed = $_POST['doextrapolation'];
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
$ed = $_POST['applyextrapolation'];
if (strlen($ed) > 0) {
   $actiontype = 'applyextrapolation';
}
$ed = $_POST['getbase'];
if (strlen($ed) > 0) {
   $getbase = 1;
}
#########################################
# END actiontype over-rides
#########################################

switch ($actiontype) {
   case 'update':
   # check for write permissions
   #print(" PERMS: $perms <br>");
   if ( !($perms & 2) ) {
      print("<b>Error:</b> You do not have edit permissions on this scenario. <br>");
   } else {
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
      # extrapolate selected data
      lsrGroupPop($listobject, $allsegs, $srcyears, $targetyears, $sources, $scenarioid, $projectid,  $debug);
      # modify the data-set to reflect these changes
      # check for write permissions before applying extrapolations
      #print(" PERMS: $perms <br>");
      if ( !($perms & 2) ) {
         print("<b>Error:</b> You do not have edit permissions on this scenario. <br>");
         $popmesg = "<b>Error:</b> You do not have edit permissions on this scenario. <br>";
      } else {
         # Update query not yet written
         applyBestFitPops($listobject, $sources, $allsegs, $targetyears, $scenarioid, $projectid, $debug);
      }
   break;

   case 'upload':
   print("Trying to Upload File.<br>");
   #print_r($_POST);
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
print("<form action='$scriptname' enctype='Multipart/form-data' method=post name='activemap'>");

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
showHiddenField('projectid', $projectid);
showHiddenField('lastgroup', $currentgroup);
# make all layers visible
include('./medit_layers.php');
print("<br><b>Select Function:</b><br>");
print("<table width=100% border=1><tr>");
print("<td valign=top bgcolor=#E2EFF5>");
showRadioButton('function', 'viewpops', $function);
print("View Source Populations<br>");
showRadioButton('function', 'viewdistros', $function);
print("View Source Distributions <br>");
showRadioButton('function', 'viewpolls', $function);
print("View NPS Sources By Land-Use <br>");
showRadioButton('function', 'transport', $function);
print("View/Edit Source Transport <br>");
print("</td>");
print("<td valign=top bgcolor=#E2EFF5>");
showRadioButton('function', 'extrapolate', $function);
print("Extrapolate Source Populations <br>");
showRadioButton('function', 'landpop', $function);
print("Predict Source Populations From Land Use<br>");
showRadioButton('function', 'upload', $function);
print("Upload Source File <br>");
showRadioButton('function', 'import', $function);
print("Import Sources from File <br>");
showRadioButton('function', 'basesources', $function);
print("Import Sources from Other Scenario/Project Table ");
print("</td>");
print("</tr></table>");
print("<br>");
showSubmitButton('changefunction','Change Function');
print("<hr>");

/* START segment of code to show the results of a query */
# start timer
$timer->startSplit();

if ( !(strlen($viewyear ) > 0) ) {
   if (strlen($thisyear) > 0) {
      $viewyear = $thisyear;
   } else {
      $viewyear = date('Y');
   }
}
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

# if it is a map click, we get the areas that have been selected and their eligible BMPs
switch ($function) {
   case 'upload':
      # check for write permissions
      #print(" PERMS: $perms <br>");
      print("<b>Upload a Source file</b><br>");
      print("<img src='/icons/info_icon_sm.gif' height=16 width=24 ");
      print(" onClick=openWindow(\"./help.php?topicname=sourcefileformat\",320,200)> ");
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
         print("<br><b>Select a File Containing Source Records:</b>(use function 'Upload Source File' to upload) <br> ");
         fileSelectedForm('infilename', $indir,'',$infilename,1);
         print("<br><b>Input in 'Columnar Format'?</b> ");
         $columns = $_POST['columns'];
         showTFListType('columns',$columns,1);
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
         showSubmitButton('doimport','Import Sources From Selected File');

         #$debug = 1;
         if ( (!$disabled) and (isset($_POST['doimport'])) ) {
            # multiple files is disabled
            #foreach ($infiles as $infilename) {
            print("<br>Parsing $infilename<br>");

            if ($columns) {
               $format = 'column';
            } else {
               $format = 'row';
            }
            importSourceFile($projectid, $scenarioid, $listobject, "$indir/$infilename", 20, $replaceall, $format, 1);
         }
      }
   break;

   case 'viewpops':
      print("<h3>Query Sources</h3>");
      print("<br><b>Select a Year(s)</b><br>");
      # screen for multiple years
      $yearfoo = "(select thisyear ";
      $yearfoo .= "from scen_sourcepops ";
      $yearfoo .= "where scenarioid = $scenarioid ";
      $yearfoo .= "group by thisyear order by thisyear) as foo ";
      $selyears = join(',', $viewyear);
      showMultiList2($listobject, 'viewyear', $yearfoo, 'thisyear', 'thisyear', $selyears, '', 'thisyear', $debug, 4);

      showHiddenField('projectid',$projectid);
      showHiddenField('actiontype','querysources');

      print("<br><b>Export To File?</b> ");
      $tofile = $_POST['tofile'];
      showTFListType('tofile',$tofile,1);
      print("<br><b>Select Resolution Type For Exported File</b> ");
      $resid = $_POST['resid'];
      showList($listobject, 'resid', 'report_resolution', 'restype', 'resid', '', $resid, $debug);

      print("<input type=submit name=submitquery value='Query Sources'>");

      if (isset($_POST['submitquery'])) {

         if ($tofile) {
            # export sources - currently only works if lrseg sources are defined
            $filename = "sourcepops_$currentgroup.csv";
            #$debug = 1;
            exportSourcePops($listobject, $outdir, $outurl, $filename, $scenarioid, $allsegs, $selyears, $tracerpoll, $sources, $resid, $debug);
         } else {
            foreach (split(',', $selyears) as $thisyear) {
               $srcrecs = getGroupSources($listobject, $sources, $tracerpoll, $allsegs, $scenarioid, $thisyear, $debug);
               if (count($srcrecs) == 0) {
                  # try subshed level sources, and warn that they are not disaggregated
                  $srcrecs = getSubshedGroupSources($listobject, $sources, $allsegs, $scenarioid, $thisyear, $debug);
                  $msg = "<br><b>Warning:</b>Source Populations for $thisyear are estimated from overlapping counties.<br>";
               }

               if (count($srcrecs) == 0) {
                  print("<br><b>Error:</b>There are no sources entered for $thisyear.<br>");
               } else {
                  print($msg);
                  $listobject->queryrecords = $srcrecs;
                  $listobject->tablename = 'sources';
                  $listobject->showList();
               }
            }
         }


      }
   break;

   case 'downloadsources':
/*
      # do a distribution

      print("<b>Download Model Source Data:</b><br>");
      print("<b>Current Active Group:</b> $groupname<br><br>");
      print("<b>Enter Year to Retrieve: </b><br>");
      # screen for onle year only
      $yearfoo = "(select thisyear ";
      $yearfoo .= "from scen_lrsegs ";
      $yearfoo .= "where scenarioid = $scenarioid ";
      $yearfoo .= "group by thisyear order by thisyear) as foo ";
      $selyears = join(',', $theseyears);
      showMultiList2($listobject, 'theseyears', $yearfoo, 'thisyear', 'thisyear', $selyears, '', 'thisyear', $debug, 4);
      print("<br><b>Select Sources </b><br>");
      $selsrc = join(',', $sources);
      showMultiList2($listobject, 'sources', 'sources', 'sourceid', 'sourcename', $selsrc, "projectid = $projectid ", 'sourcename', $debug, 4);
      print("<br>");
      showSubmitButton('srcdownload','Generate Source File');
      #$debug = 1;
      if (isset($_POST['srcdownload'])) {
         $timer->startsplit();
         print("<hr><b>Model Land-Use Data for $selyears:</b><br>");
         $filename = "landuse_$scenarioid" . ".$userid" . ".csv";
         exportLandUses($listobject, $outdir, $outurl, $filename, $scenarioid, $allsegs, $selyears, $debug);
         $qtime = $timer->startsplit();
         print("Query Time: $qtime<br>");
      }
*/
   break;


   case 'viewpolls':
      print("<h3>Query Sources By Land-Use</h3>");
      print("<b>Enter a Year(s)</b><br>");
      print("<input type=text name=thisyear value='$thisyear'>");
      $selpoll = join(',', $constits);
      print("<br><b>Select Constituents to Query:</b><br>");
      showMultiList2($listobject, 'constits', 'pollutanttype', 'typeid', 'pollutantname', $selpoll, '', 'pollutantname', $debug, 4);
      print("<br><b>Select Land-Uses </b><br>");
      $selectedlus = join(',', $landuses);
      showMultiList2($listobject, 'landuses', 'landuses', 'hspflu', 'landuse, major_lutype', $selectedlus, "projectid = $projectid and hspflu <> ''", 'major_lutype, landuse', $debug, 4);
      print("<br>");
      showHiddenField('projectid',$projectid);
      showHiddenField('actiontype','querysourcepolls');
      #showHiddenField('function','viewpolls');
      showSubmitButton('getsourcepolls','Query Sources');

      # get the results of this query
      $srcrecs = getGroupSourceTotals($listobject, $selectedlus, $allsegs, $selpoll, $scenarioid, $thisyear, $debug);

      if (count($srcrecs) == 0) {
         print("<br><b>Error:</b>There are no sources entered for $viewyear.<br>");
      } else {
         $listobject->queryrecords = $srcrecs;
         $listobject->adminsetuparray['subsource']['column info']['totalapp']['visible'] = 0;
         $listobject->tablename = 'subsource';
         $listobject->showList();
      }
   break;

   case 'basesources':
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

      $debug = 0;
      print("<b>Base Sources Retrieval: </b><br>");
      print("This will retrieve source definitions and populations from the projects base land use table. <br>");
      $selsrc = join(',', $sources);
      print("<b>Sources: </b><BR>");
      showMultiList2($listobject, 'sources', 'sources', 'sourceid', 'sourcename', $selsrc, "projectid = $projectid ", 'sourcename', $debug, 4);
      print("<br><b>Get Sources for: </b>");
      showWidthTextField('viewyear', $viewyear, 10);
      $src_scenario = $_POST['src_scenario'];
      $otherscen = $_POST['otherscen'];
      print("<br><b>Import From another scenario?</b> (False will import project base data) ");
      showTFListType('otherscen',$otherscen,1);
      showViewableScenarioList($listobject, $projectid, $src_scenario, $userid, $usergroupids, 'src_scenario', '', '', $debug);
      print("<br>");
      showSubmitButton('getbase','Get Base Sources');

      if ($getbase) {
         if (!$otherscen) {
            $src_scenario = -1;
         }

         importBaseSources($projectid, $scenarioid, $src_scenario, $listobject, $allsegs, $viewyear, $sources, $debug);
         print("Base Sources Retrieved for $viewyear.<br>");
      }
   }

   break;

   case 'extrapolate':
      $debug = 0;
      print("<b>Best Fit Extrapolation/Interpolation:</b><br>");
      print("$popmesg");
      print("<b>Note:</b> These population estimates are done on a county level, and therefore do not reflect the actual population in the selected basin, unless the basin outlines are restricted to full counties.<br>");
      print("<br><b>Enter Source Years(s):</b><br>");
      # screen for multiple years
      $yearfoo = "(select thisyear ";
      $yearfoo .= "from scen_sourcepops ";
      $yearfoo .= "where scenarioid = $scenarioid ";
      $yearfoo .= "group by thisyear order by thisyear) as foo ";
      $srcyears = join(',', $viewyear);
      showMultiList2($listobject, 'viewyear', $yearfoo, 'thisyear', 'thisyear', $selyears, '', 'thisyear', $debug, 4);
      print("<br><b>Enter Target Years (blank will do for all source years):</b><br>");
      showWidthTextField('targetyears', $targetyears, 10);
      ShowHiddenField('actiontype','doextrapolation');
      print("<br>");
      $selsrc = join(',', $sources);
      showMultiList2($listobject, 'sources', 'sources', 'sourceid', 'sourcename', $selsrc, "projectid = $projectid ", 'sourcename', $debug, 4);
      print("<br><b>Create Land Use Extrapolation Based on Ag Pops </b> (must select all poultry and cattle types): ");
      $dopopland = $_POST['dopopland'];
      showTFListType('dopopland',$dopopland,1);
      print("<br>");
      showSubmitButton('doextrapolation','Refresh Best Fit Calculations');
      print("<br><b>Results of Extrapolation:</b><br>");
      #print_r($lurecs);

      $currentyear = $sy[count($sy) - 1];

      if (isset($_POST['doextrapolation']) or isset($_POST['applyextrapolation']) ) {
         # if we have just applied the extrapolation, then these tables are already created
         # Otherwise, we need to create them
         if (!($actiontype == 'applyextrapolation') ) {
            print("Refreshing Extrapolation<br>");
            lsrGroupPop($listobject, $allsegs, $srcyears, $targetyears, $sources, $scenarioid, $projectid, $debug);
         }

         $listobject->querystring = "select a.thisyear, a.sourceid, b.sourcename, a.totalpop ";
         $listobject->querystring .= " from tmp_grpextrap as a, sources as b ";
         $listobject->querystring .= " where a.sourceid = b.sourceid";
         $listobject->querystring .= "    and thisyear in ($targetyears) ";
         $listobject->querystring .= " order by thisyear";
         $listobject->performQuery();

         $srcpops = array();
         $srcrecs = $listobject->queryrecords;
         foreach($srcrecs as $thisrec) {
            $srcname = $thisrec['sourcename'];
            $srcpops[$srcname] = $thisrec['totalpop'];
            $spop = number_format($thisrec['totalpop'],0);
            $tyear = $thisrec['thisyear'];
            print("<b>$srcname, $tyear: </b> $spop <br>");
         }
         $ak = array_keys($srcpops);
         #print_r($ak);
         #print_r($srcrecs);


         if ($dopopland) {
        # this has been moved to the function 'popland' that is not yet enabled.
         }

         $bfgraph = graphBestFitPops($listobject, $goutdir, $goutpath, $sources, $allsegs, $srcyears, $targetyears, $scenarioid, $debug);

         print("<br><img src='$bfgraph'><br>");
      }
      showSubmitButton('applyextrapolation','Apply Best Fit Calculations To Target Years');
   break;


   case 'popland':
      $debug = 0;
      print("<b>Best Fit Extrapolation/Interpolation:</b><br>");
      print("$popmesg");
      print("<b>Note:</b> These population estimates are done on a county level, and therefore do not reflect the actual population in the selected basin, unless the basin outlines are restricted to full counties.<br>");
      print("<br><b>Enter Source Years (blank will use all):</b><br>");
      showWidthTextField('srcyears', $srcyears, 10);
      print("<br><b>Enter Target Years (blank will do for all source years):</b><br>");
      showWidthTextField('targetyears', $targetyears, 10);
      ShowHiddenField('actiontype','doextrapolation');
      print("<br>");
      $selsrc = join(',', $sources);
      showMultiList2($listobject, 'sources', 'sources', 'sourceid', 'sourcename', $selsrc, "projectid = $projectid ", 'sourcename', $debug, 4);
      print("<br><b>Create Land Use Extrapolation Based on Ag Pops </b> (must select all poultry and cattle types): ");
      $dopopland = $_POST['dopopland'];
      showTFListType('dopopland',$dopopland,1);
      print("<br>");
      showSubmitButton('doextrapolation','Refresh Best Fit Calculations');
      print("<br><b>Results of Extrapolation:</b><br>");
      #print_r($lurecs);

      $currentyear = $sy[count($sy) - 1];

      # if we have just applied the extrapolation, then these tables are already created
      # Otherwise, we need to create them
      if (!($actiontype == 'applyextrapolation') ) {
         print("Refreshing Extrapolation<br>");
         lsrGroupPop($listobject, $allsegs, $srcyears, $targetyears, $sources, $scenarioid, $projectid, $debug);
      }

      $listobject->querystring = "select a.thisyear, a.sourceid, b.sourcename, a.totalpop ";
      $listobject->querystring .= " from tmp_grpextrap as a, sources as b ";
      $listobject->querystring .= " where a.sourceid = b.sourceid";
      $listobject->querystring .= "    and thisyear in ($targetyears) ";
      $listobject->querystring .= " order by thisyear";
      $listobject->performQuery();

      $srcpops = array();
      $srcrecs = $listobject->queryrecords;
      foreach($srcrecs as $thisrec) {
         $srcname = $thisrec['sourcename'];
         $srcpops[$srcname] = $thisrec['totalpop'];
         $spop = number_format($thisrec['totalpop'],0);
         $tyear = $thisrec['thisyear'];
         print("<b>$srcname, $tyear: </b> $spop <br>");
      }
      $ak = array_keys($srcpops);
      #print_r($ak);
      #print_r($srcrecs);


      if ($dopopland) {
         if ( (in_array('broilers', $ak)) and (in_array('beef_heiffers', $ak)) and (in_array('dairy_heiffers', $ak)) ) {
            if (strlen($srcyears) > 0) {
               $sy = split(',', $srcyears);
               $currentyear = $sy[count($sy) - 1];

               projectPopLandUse($listobject, $currentyear, $targetyears, $scenarioid, $debug);

               $listobject->querystring = "select * ";
               $listobject->querystring .= " from tmp_sumlandreg ";
               $listobject->performQuery();
               $luneeds = $listobject->queryrecords[0];
               $listobject->showList();

               $pasfuture = number_format($luneeds['pasfuture'],0);
               $rowfuture = number_format($luneeds['rowfuture'],0);
               $hayfuture = number_format($luneeds['hayfuture'],0);
               $pasture = number_format($luneeds['paslast'],0);
               $rowcrops = number_format($luneeds['rowlast'],0);
               $hay = number_format($luneeds['haylast'],0);

               print("<b>Current and Projected Land Use Needed in $targetyears (using $currentyear as current year): </b><br>");
               print("Pasture: $pasture -&gt; $pasfuture<br>");
               print("Row Crops: $rowcrops -&gt; $rowfuture<br>");
               print("Hay: $hay -&gt; $hayfuture<br>");
            } else {
               print("<B>Notice: </b>You must include at least one year in the source years field.<br>");
            }

            if ( ($actiontype == 'applyextrapolation') ) {
               if ( !$sceninfo['locked']) {
                  print("Extrapolation Update Requested.<br>");
                  # check for write permissions
                  #print(" PERMS: $perms <br>");
                  if ( !($perms & 2) ) {
                     print("<b>Error:</b> You do not have edit permissions on this scenario. <br>");
                  } else {
                     # $minresist, $maxresist come from local_variables.php
                     updatePopLandUse($listobject, $currentyear, $targetyears, $minresist, $maxresist, $scenarioid, $debug);
                  }
               } else {
                  print("<b>Error:</b> This scenario has been locked. No data may be modified.<br>");
               }
            }
         }
      }

      $bfgraph = graphBestFitPops($listobject, $goutdir, $goutpath, $sources, $allsegs, $srcyears, $targetyears, $scenarioid, $debug);

      print("<br><img src='$bfgraph'><br>");
      showSubmitButton('applyextrapolation','Apply Best Fit Calculations To Target Years');
   break;


   case 'landpop':
      $debug = 0;
      print("<b>Estimate Population Change based on Land Use Change</b><br>");
      print("This will predict future popluation by the mean rate of change suggested by a least squares regression<br>");
      print("and a relationship between the predicted future land use and animal populations.<br>");
      print("$popmesg");

      # screen for onle year only
      $yearfoo = "(select thisyear ";
      $yearfoo .= "from scen_lrsegs ";
      $yearfoo .= "where scenarioid = $scenarioid ";
      #$yearfoo .= "   and $lrclause ";
      $yearfoo .= "group by thisyear order by thisyear) as foo ";
      $baseyear = $_POST['baseyear'];

      $selyears = join(',', $srcyears);
      print("<b>Select Years for Historical regression curve :</b><br> ");
      showMultiList2($listobject, 'srcyears', $yearfoo, 'thisyear', 'thisyear', $selyears, '', 'thisyear', $debug, 4);
      print("<br>");
      print("<b>Select Base Year for percentage change </b>(one year only)<b>:</b> ");
      showList($listobject, 'baseyear', $yearfoo, 'thisyear', 'thisyear', '', $baseyear, $debug);
      print("<br>");
      print("<b>Select Future Year </b>(one year only)<b>:</b> ");
      showList($listobject, 'targetyears', $yearfoo, 'thisyear', 'thisyear', '', $targetyears, $debug);
      if (isset($_POST['minpct'])) {
         $minpct = $_POST['minpct'];
      } else {
         $minpct = 0.01;
      }
      print("<br><b>Enter Minimum percent of base pop for the projected pop (between 0.0 - 1.0):</b><br>");
      showWidthTextField('minpct', $minpct, 4);

      ShowHiddenField('actiontype','dolandpop');
      print("<br>");
      $selsrc = join(',', $sources);
      showMultiList2($listobject, 'sources', 'sources', 'sourceid', 'sourcename', $selsrc, "projectid = $projectid ", 'sourcename', $debug, 4);
      showSubmitButton('dolandpop','Make Estimate');
      print("<br><b>Results of Estimate:</b><br>");
      #print_r($lurecs);

      $currentyear = $sy[count($sy) - 1];
      $filename = "projected_sources_$scenarioid" . "_$targetyears" . ".$userid" . ".csv";

      if (count($sources) > 0) {
         $srclist = "'" . join("','", $sources) . "'";
         $srccond = " sourceid in ($srclist) ";
      } else {
         $srccond = ' (1 = 1) ';
      }

      if (isset($_POST['applylandpop'])) {

         # perform extrapolations
         print("Calculating Extrapolation<br>");
         lsrGroupPop($listobject, $allsegs, $selyears, $targetyears, $sources, $scenarioid, $projectid, $debug);
         projectPopFromLandUse($listobject, $scenarioid, $projectid, $sources, $baseyear, $targetyears, $allsegs, $minpct, $debug);

         $listobject->querystring = "  delete from scen_landpop_predict ";
         $listobject->querystring .= " where scenarioid = $scenarioid";
         $listobject->querystring .= "    and $srccond ";
         $listobject->querystring .= "    and subshedid in (select subshedid from tmp_estpops group by subshedid) ";
         $listobject->querystring .= "    and thisyear = $targetyears ";
         if ($debug) { print("<br>$listobject->querystring ; <br>"); }
         $listobject->performQuery();

         $listobject->querystring = "  insert into scen_landpop_predict (scenarioid, subshedid, thisyear, lastyear, ";
         $listobject->querystring .= "    sourceid, lastpop, lsr_pop, landpop, rsquare) ";
         $listobject->querystring .= " select $scenarioid, a.subshedid, a.thisyear, a.baseyear, a.sourceid, a.base_pop,  ";
         $listobject->querystring .= "   b.lsr_pop, a.pred_pop, c.rsquare ";
         $listobject->querystring .= " from ( ";
         $listobject->querystring .= "    select subshedid, sourceid, thisyear, baseyear, sum(base_pop) as base_pop,  ";
         $listobject->querystring .= "       sum(pred_pop) as pred_pop  ";
         $listobject->querystring .= "    from tmp_estpops ";
         $listobject->querystring .= "    group by subshedid, sourceid, thisyear, baseyear ";
         $listobject->querystring .= " ) as a, ";
         $listobject->querystring .= " (  select subshedid, sourceid, thisyear, sum(actualpop) as lsr_pop ";
         $listobject->querystring .= "    from tmp_srcextrap  ";
         $listobject->querystring .= "    group by subshedid, sourceid, thisyear ";
         $listobject->querystring .= " ) as b, stat_pop_eq_f_of_land as c ";
         $listobject->querystring .= " where a.subshedid = b.subshedid ";
         $listobject->querystring .= "    and a.sourceid = b.sourceid ";
         $listobject->querystring .= "    and a.sourceid = c.sourceid ";
         $listobject->querystring .= "    and c.projectid = $projectid ";
         $listobject->querystring .= "    and a.thisyear = b.thisyear ";
         $listobject->performQuery();
         if ($debug) { print("<br>$listobject->querystring ; <br>"); }
         exportProjectedSources($listobject, $outdir, $outurl, $filename, $scenarioid, $allsegs, $targetyears, $sources, $debug);
      }

      if (isset($_POST['dolandpop'])) {

         # cache these in our scenario table
         # perform extrapolations
         print("Calculating Extrapolation<br>");
         lsrGroupPop($listobject, $allsegs, $selyears, $targetyears, $sources, $scenarioid, $projectid, $debug);
         projectPopFromLandUse($listobject, $scenarioid, $projectid, $sources, $baseyear, $targetyears, $allsegs, $minpct, $debug);
      }

      if ($listobject->tableExists('tmp_estpops')) {

         $bfgraph = graphBestFitPops($listobject, $goutdir, $goutpath, $sources, $allsegs, $selyears, $targetyears, $scenarioid, $debug);

         print("<br><img src='$bfgraph'><br>");

         $listobject->querystring = "  ( select baseyear as thisyear, sum(base_aucount) as thisaucount, 0.0 as pred_aucount ";
         $listobject->querystring .= "  from tmp_estpops ";
         $listobject->querystring .= "  group by baseyear ";
         $listobject->querystring .= "  order by baseyear ";
         $listobject->querystring .= " ) UNION ( ";
         $listobject->querystring .= "   select thisyear as thisyear, 0.0 as thisaucount, sum(pred_aucount) as pred_aucount ";
         $listobject->querystring .= "  from tmp_estpops ";
         $listobject->querystring .= "  group by thisyear ";
         $listobject->querystring .= "  order by thisyear ";
         $listobject->querystring .= " )";
         #print("<br>$listobject->querystring ; <br>");
         $listobject->performquery();
         #$listobject->showlist();

         $regrecs = $listobject->queryrecords;

         $presgraph = array();
         $presgraph['graphrecs'] = $regrecs;
         $presgraph['xcol'] = 'thisyear';
         $presgraph['ycol'] = 'thisaucount';
         $presgraph['color'] = 'orange';
         $presgraph['ylegend'] = 'Historic';

         # selected records to extrapolate from the best fit in blue
         $predgraph = array();
         $predgraph['graphrecs'] = $regrecs;
         $predgraph['xcol'] = 'thisyear';
         $predgraph['ycol'] = 'pred_aucount';
         $predgraph['color'] = 'blue';
         $predgraph['ylegend'] = 'Projected';
         $extrapgraph['alpha'] = 0.3;

         $multibar = array('title'=>"Pops Predicted on Land-Use", 'xlabel'=>'Year', 'bargraphs'=>array($presgraph, $predgraph));

         $gurl = showGenericMultiBar($goutdir, $goutpath, $multibar, $debug);

         print("<br><img src='$gurl'><br>");

         #$debug = 1;
         $listobject->querystring = "  delete from scen_landpop_predict ";
         $listobject->querystring .= " where scenarioid = $scenarioid";
         $listobject->querystring .= "    and $srccond ";
         $listobject->querystring .= "    and subshedid in (select subshedid from tmp_estpops group by subshedid) ";
         $listobject->querystring .= "    and thisyear = $targetyears ";
         if ($debug) { print("<br>$listobject->querystring ; <br>"); }
         $listobject->performQuery();

         $listobject->querystring = "  insert into scen_landpop_predict (scenarioid, subshedid, thisyear, lastyear, ";
         $listobject->querystring .= "    sourceid, lastpop, lsr_pop, landpop, rsquare) ";
         $listobject->querystring .= " select $scenarioid, a.subshedid, a.thisyear, a.baseyear, a.sourceid, a.base_pop,  ";
         $listobject->querystring .= "   b.lsr_pop, a.pred_pop, c.rsquare ";
         $listobject->querystring .= " from ( ";
         $listobject->querystring .= "    select subshedid, sourceid, thisyear, baseyear, sum(base_pop) as base_pop,  ";
         $listobject->querystring .= "       sum(pred_pop) as pred_pop  ";
         $listobject->querystring .= "    from tmp_estpops ";
         $listobject->querystring .= "    group by subshedid, sourceid, thisyear, baseyear ";
         $listobject->querystring .= " ) as a, ";
         $listobject->querystring .= " (  select subshedid, sourceid, thisyear, sum(actualpop) as lsr_pop ";
         $listobject->querystring .= "    from tmp_srcextrap  ";
         $listobject->querystring .= "    group by subshedid, sourceid, thisyear ";
         $listobject->querystring .= " ) as b, stat_pop_eq_f_of_land as c ";
         $listobject->querystring .= " where a.subshedid = b.subshedid ";
         $listobject->querystring .= "    and a.sourceid = b.sourceid ";
         $listobject->querystring .= "    and a.sourceid = c.sourceid ";
         $listobject->querystring .= "    and a.thisyear = b.thisyear ";
         $listobject->performQuery();
         if ($debug) { print("<br>$listobject->querystring ; <br>"); }
         exportProjectedSources($listobject, $outdir, $outurl, $filename, $scenarioid, $allsegs, $targetyears, $sources, $debug);

         showSubmitButton('applyextrapolation','Apply Best Fit Calculations To Target Years');

      }
   break;

   case 'transport':
   # enter manure transport record(s)
   # move source generation from one place to another
   # first, show existing transport records for this group
   # then, show a form that allows the user to enter a transport record
   # check for write permissions
   #print(" PERMS: $perms <br>");
   if ( !($perms & 2) ) {
      print("<b>Error:</b> You do not have edit permissions on this scenario. <br>");
   } else {
      print("<h3>Transport Sources</h3>");
      print("<br><b>Notice:</b> This function operates at the subshed level (political boundary), therefore transport records are effective at the political boundary, not watershed boundary.<br>");

      # first, enter a record if we submitted
      if ( isset($_POST['addtransport']) ) {
         print("Adding Transport Record<br>");
         $subshedid = $_POST['subshedid'];
         $dest_subshedid = $_POST['dest_subshedid'];
         $sourceid = $_POST['sources'];
         $amount = $_POST['amount'];
         $constit = $_POST['constit'];
         $thisyear = $_POST['thisyear'];
         $binid = $_POST['binid'];
         addTransportRecord($listobject, $scenarioid, $projectid, $sourceid, $constit, $subshedid, $dest_subshedid, $binid, $thisyear, $amount, $debug);
      }

      print("<b>Select Year: </b>");
      # screen for onle year only
      $yearfoo = "(select thisyear ";
      $yearfoo .= "from scen_sourcepops ";
      $yearfoo .= "where scenarioid = $scenarioid ";
      $yearfoo .= "group by thisyear order by thisyear) as foo ";
      # singe year
      showActiveList($listobject, 'thisyear', $yearfoo, 'thisyear', 'thisyear', '', $thisyear, 'submit()', 'thisyear', $debug);
      $debug = 0;
      # screen for onle year only
      $binfoo = "(select sourceid, sourcename ";
      $binfoo .= "from scen_sources ";
      $binfoo .= "where scenarioid = $scenarioid ";
      $binfoo .= "group by sourceid, sourcename order by sourcename) as foo ";
      print("<br><b>Select the source storage type:</b> ");
      showList($listobject, 'sources', $binfoo, 'sourcename', 'sourceid', '', $sources, $debug);
      # screen for onle year only
      $srcfoo = "(select luid, landuse from landuses ";
      $srcfoo .= "where projectid = $projectid ";
      $srcfoo .= "   and major_lutype in ($vwaste_storage_lutype) ";
      $srcfoo .= "group by luid, landuse order by landuse) as foo ";
      print("<br><b>Select the source:</b> ");
      $selsrc = join(',', $sources);
      showList($listobject, 'binid', $srcfoo, 'landuse', 'luid', '', $binid, $debug);
      print("<br><b>Select the source location subshedid:</b> ");
      # screen for subsheds in this group only
      $ssfoo = "( select subshedid from scen_lrsegs ";
      $ssfoo .= " where scenarioid = $scenarioid ";
      $ssfoo .= "    and $lrclause ";
      $ssfoo .= " group by subshedid  ";
      $ssfoo .= " ) as foo ";
      showList($listobject, 'subshedid', $ssfoo, 'subshedid', 'subshedid', '', $subshedid, $debug);
      print("<br><b>Select the destination subshedid:</b> ");
      # screen for subsheds in this group and outside of it
      $ssfoo = "( ( select subshedid as dest_subshedid from scen_subsheds ";
      $ssfoo .= " where scenarioid = $scenarioid ";
      $ssfoo .= " group by subshedid  ";
      $ssfoo .= " ) UNION ( select 'Outside Watershed' as dest_subshedid ) ";
      $ssfoo .= " ) as foo ";
      showList($listobject, 'dest_subshedid', $ssfoo, 'dest_subshedid', 'dest_subshedid', '', $dest_subshedid, $debug);
      print("<br><b>Select the constituent to base transport value:</b> ");
      showList($listobject, 'constit', 'pollutanttype', 'pollutantname', 'typeid', '', $constit, $debug);
      print("<br><b>Enter smount to transport, based on target constituent:</b><br>");
      showWidthTextField('amount', $amount, 12);
      print("<br>");

      showSubmitButton('addtransport','Add Transport Record');

      # now, edit existing records if we submitted
      if ( isset($_POST['updatetransfers']) ) {
         print("Updating Selected Transport Record(s)<br>");
         $trecs['edit_transfer'] = $_POST['edit_transfer'];
         $trecs['delete_transfer'] = $_POST['delete_transfer'];
         $trecs['subshedid'] = $_POST['m_subshedid'];
         $trecs['dest_subshedid'] = $_POST['m_dest_subshedid'];
         $trecs['sourceid'] = $_POST['m_sourceid'];
         $trecs['amount'] = $_POST['m_amount'];
         $trecs['constit'] = $_POST['m_constit'];
         $trecs['thisyear'] = $_POST['m_thisyear'];
         $trecs['storage_bin'] = $_POST['m_binid'];
         $trecs['sstid'] = $_POST['m_sstid'];
         updateTransportRecord($listobject, $scenarioid, $projectid, $trecs, $debug);
      }

      $srcrecs = getSubshedGroupTransportSources($listobject, array(), $allsegs, $scenarioid, $thisyear, $debug);

      if (count($srcrecs) == 0) {
         print("<br><b>Error:</b>There are no sources entered for $viewyear.<br>");
      } else {
         print("<br><b>Source Transport Records for $thisyear: </b> <br> ");
         print("<table cellpadding=3>");
         $i = 0;
         print("<tr><td>");
         print("<b>Edit</b>");
         print("</td>");
         print("<td>");
         print("<b>Delete</b>");
         print("</td>");
         print("<td>");
         print("<b>Source Type</b>");
         print("</td>");
         print("<td>");
         print("<b>Source Location</b>");
         print("</td>");
         print("<td>");
         print("<b>Storage</b>");
         print("</td>");
         print("<td>");
         print("<b>Destination</b>");
         print("</td>");
         print("<td>");
         print("<b>Basis</b>");
         print("</td>");
         print("<td>");
         print("<b>Year</b>");
         print("</td>");
         print("<td>");
         print("<b>Amount</b>");
         print("</td>");

         print("</tr>");
         $o = 1;
         foreach ($srcrecs as $thisrec) {
            $x = ceil(fmod($o,2));
            print("<tr bgcolor=$rc[$x] bordercolor=$rc[$x]>");
            print("<td bgcolor=$rc[$x] bordercolor=$rc[$x]>");
            showCheckBox("edit_transfer[$i]", 1, $edit_nr);
            print("</td>");
            print("<td bgcolor=$rc[$x] bordercolor=$rc[$x]>");
            showCheckBox("delete_transfer[$i]", 1, $delete_nr);
            print("</td>");
            print("<td bgcolor=$rc[$x] bordercolor=$rc[$x]>");
            # get values for this record
            $sourcename = $thisrec['sourcename'];
            $sourceid = $thisrec['sourceid'];
            $subshedid = $thisrec['subshedid'];
            $storage_bin = $thisrec['storage_bin'];
            $dest_subshedid = $thisrec['dest_subshedid'];
            $thisyear = $thisrec['thisyear'];
            $amount = $thisrec['amount'];
            $constit = $thisrec['constit'];
            $sstid = $thisrec['sstid'];
            # print first column values
            # we will prefix all names with "m_" to distinguish from the non-multi source types
            print(" $sourcename ");
            showHiddenField("m_sourceid[$i]", $sourceid);
            showHiddenField("m_sstid[$i]", $sstid);
            print("</td>");
            print("<td bgcolor=$rc[$x] bordercolor=$rc[$x]>");
            # screen for subsheds in this group only
            $ssfoo = "( select subshedid from scen_lrsegs ";
            $ssfoo .= " where scenarioid = $scenarioid ";
            $ssfoo .= "    and $lrclause ";
            $ssfoo .= " group by subshedid  ";
            $ssfoo .= " ) as foo ";
            showList($listobject, "m_subshedid[$i]", $ssfoo, 'subshedid', 'subshedid', '', $subshedid, $debug);
            print("</td>");
            print("<td bgcolor=$rc[$x] bordercolor=$rc[$x]>");
            # screen for onle year only
            $srcfoo = "(select luid, landuse from landuses ";
            $srcfoo .= "where projectid = $projectid ";
            $srcfoo .= "   and major_lutype in ($vwaste_storage_lutype) ";
            $srcfoo .= "group by luid, landuse order by landuse) as foo ";
            showList($listobject, "m_binid[$i]", $srcfoo, 'landuse', 'luid', '', $storage_bin, $debug);
            print("</td>");
            print("<td bgcolor=$rc[$x] bordercolor=$rc[$x]>");
            # screen for subsheds in this group only
            # screen for subsheds in this group and outside of it
            $ssfoo = "( ( select subshedid as dest_subshedid from scen_subsheds ";
            $ssfoo .= " where scenarioid = $scenarioid ";
            $ssfoo .= " group by subshedid  ";
            $ssfoo .= " ) UNION ( select 'Outside Watershed' as dest_subshedid ) ";
            $ssfoo .= " ) as foo ";
            showList($listobject, "m_dest_subshedid[$i]", $ssfoo, 'dest_subshedid', 'dest_subshedid', '', $dest_subshedid, $debug);
            print("</td>");
            print("<td bgcolor=$rc[$x] bordercolor=$rc[$x]>");
            showList($listobject, "m_constit[$i]", 'pollutanttype', 'pollutantname', 'typeid', '', $constit, $debug);
            print("</td>");
            print("<td bgcolor=$rc[$x] bordercolor=$rc[$x]>");
            # screen for onle year only
            $yearfoo = "(select thisyear ";
            $yearfoo .= "from scen_sourcepops ";
            $yearfoo .= "where scenarioid = $scenarioid ";
            $yearfoo .= "group by thisyear order by thisyear) as foo ";
            # singe year
            showList($listobject, "m_thisyear[$i]", $yearfoo, 'thisyear', 'thisyear', '', $thisyear, $debug);
            print("</td>");
            print("<td bgcolor=$rc[$x] bordercolor=$rc[$x]>");
            showWidthTextField("m_amount[$i]",$amount,12);
            print("</td>");
            $o++;

            print("</tr>");
            $i++;
         }

         print("<tr><td valign=top colspan=16>");
         showSubmitButton('updatetransfers','Save Crop Info');
         print("</td></tr>");

         print("</table>");
      }


   }
   break;
}

print("   </td><td valign=top>");
include ('./medit_controlfooter.php');
print("   </td>");
print("</tr>");
print("</table>");

$totaltime = $timer->startSplit();
print("Total Processing Time: $totaltime seconds");
?>

</form>
</body>
</html>
