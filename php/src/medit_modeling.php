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
   $function = $_POST['function'];
   $viewyear = $_POST['viewyear'];
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
# XAJAX calls required
#$debug = 1;
if ($debug) {
   print("Loading common xajax methods");
}
include_once("xajax_modeling.common.php");
if ($debug) {
   print("Loading xajax javascript: $liburl/xajax");
}
$xajax->printJavascript("$liburl/xajax");
print("</head>");
print("<body bgcolor=ffffff onload='init(); setInterval(\"xajax_showStatus()\",15000); '>");

#########################################
# END - Call Header
#########################################


#########################################
# Custom Button To change to new BMP
#########################################
# check to see if the users pressed the button to Re-create BMP Masslinks
$ae = $_POST['createscenario'];
if (strlen($ae) > 0) {
   $createscenario = 1;
}
$ae = $_POST['adduser'];
if (strlen($ae) > 0) {
   $adduser = 1;
}
#########################################
# END actiontype over-rides
#########################################

#########################################
# Now, process actions
#########################################
switch ($actiontype) {


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

print("<table>");
print("<tr>");
print("   <td valign=top width=800>");
print("<form action='$scriptname' method=post name='activemap' id='activemap'>");
# show project navigation controls
include('medit_controls.php');
# make all layers visible
include('./medit_layers.php');
print("<br><b>Select Function:</b><br>");
print("<table width=100% border=1><tr>");
print("<td valign=top bgcolor=#E2EFF5>");
showRadioButton('function', 'addelement', $function, "xajax_showAddElementForm(xajax.getFormValues(\"activemap\"))");
print("Add/Edit Modeling Element");
print("<br>");
showRadioButton('function', 'importelement', $function, "xajax_showImportModelElementForm(xajax.getFormValues(\"activemap\"))");
print("Import Modeling Element From Another User/Domain");
print("<br>");
showRadioButton('function', 'importgroup', $function, "xajax_showCopyModelGroupForm(xajax.getFormValues(\"activemap\"))");
print("Copy Entire Modeling Group From Another User/Domain");
print("<br>");
print("</td>");
print("<td valign=top bgcolor=#E2EFF5>");
showRadioButton('function', 'runmodel', $function, "xajax_showModelRunForm(xajax.getFormValues(\"activemap\"))");
print("Run Model Component");
if ($userid == 1) {
   print("<br>");
   showRadioButton('function', 'reloadwhoxmlobjects', $function, "xajax_showRefreshWHOObjectsForm(xajax.getFormValues(\"activemap\"))");
   print(" Reload WHO XML Object Templates");
   print("<br>");
   showRadioButton('function', 'showStatus', $function, "xajax_showStatus(xajax.getFormValues(\"activemap\"))");
   print(" Refresh Status");
}
print("</td>");
print("</tr></table>");
#showSubmitButton('changefunction','Change Function');
print("</form>");
print("<hr>");

#if (count($allsegs) <= 10) {
#   $ls = join(', ', $allsegs);
#   print("<b>Selected Segments:</b> $ls <br>");
#}
print("<table width=100% border=1><tr>");
print("<td valign=top bgcolor=#E2EFF5>");
print("\n<div id='controlpanel' bgcolor='lightgrey'>");
print("</div>\n");
print("</td>");
print("</tr></table>");
/* START segment of code to show the results of a query */

# if it is a map click, we get the areas that have been selected and their eligible BMPs
switch ($function) {


}

print("   </td>");
print("   <td valign=top width=350>");

include('medit_controlfooter.php');
print($controlHTML);
print("   </td>");
print("</tr>");

print("<tr>");
print("   <td colspan=2 valign=top bgcolor=#E2EFF5>");
print("\n<div id='status_bar' bgcolor='lightgrey'></div>\n");
print("   </td>");
print("</tr>");
print("<tr>");
print("   <td colspan=2 valign=top bgcolor=#E2EFF5>");
print("\n<div id='commandresult' bgcolor='lightgrey'></div>\n");
print("   </td>");
print("</tr>");

print("<tr>");
print("   <td colspan=2 valign=top bgcolor=#E2EFF5>");
print("\n<div id='workspace' bgcolor='lightgrey'></div>\n");

print("   </td>");
print("</tr>");
print("</table>");

?>

</body>
</html>
