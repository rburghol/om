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
require("xajax_watersupply.common.php");
$xajax->printJavascript("$liburl/xajax");
print("</head>");
print("<body bgcolor=ffffff onload=\"init()\">");

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
showRadioButton('function', 'showwithdrawals', $function, "xajax_showWithdrawalForm(xajax.getFormValues(\"activemap\"))");
print("Show Withdrawals");
print("<br>");
#showRadioButton('function', 'fishkills', $function, '');
#print("Show Fish Kills");
#print("<br>");
#showRadioButton('function', 'hsi', $function, "xajax_showHSI(xajax.getFormValues(\"activemap\"))");
#print("Show Habitat Suitability Indices");
#print("<br>");
showRadioButton('function', 'mpinfo', $function, "xajax_showWithdrawalInfoForm(xajax.getFormValues(\"activemap\"))");
print("Show Withdrawal Point Info");
print("<br>");
showRadioButton('function', 'hsi', $function, "xajax_showDroughtIndicatorForm(xajax.getFormValues(\"activemap\"))");
print("Show Drought Indicators");
print("<br>");
showRadioButton('function', 'addelement', $function, "xajax_showPlanningForm(xajax.getFormValues(\"activemap\"))");
print("Water Supply Planning Form");
print("<br>");
showRadioButton('function', 'reportingform', $function, "xajax_showAnnualReportingForm(xajax.getFormValues(\"activemap\"))");
print("Mail Annual Water Reporting Form");
print("<br>");
showRadioButton('function', 'createannual', $function, "xajax_showAnnualDataCreationForm(xajax.getFormValues(\"activemap\"))");
print("Create Blank Water Reporting Records");
if ($userid == 1) {
print("<br>");
   showRadioButton('function', 'users', $function, "xajax_showVWUDSForm(xajax.getFormValues(\"activemap\"))");
   print("Edit System Users");
}
print("</td>");
print("<td valign=top bgcolor=#E2EFF5>");
#showRadioButton('function', 'computeflow', $function, "xajax_showCreateFlowForm(xajax.getFormValues(\"activemap\"))");
#print("Create Flow Record");
#print("<br>");
showRadioButton('function', 'preciptrends', $function, "xajax_showPrecipTrends(xajax.getFormValues(\"activemap\"))");
print("Show Precipitation Trends");
print("<br>");
showRadioButton('function', 'annualedit', $function, "xajax_showVWUDSForm(xajax.getFormValues(\"activemap\"))");
print("View/Edit Annual Water Use Data");
print("<br>");
showRadioButton('function', 'mpedit', $function, "xajax_showVWUDSForm(xajax.getFormValues(\"activemap\"))");
print("View/Edit MP Data");
print("<br>");
showRadioButton('function', 'facilityedit', $function, "xajax_showVWUDSForm(xajax.getFormValues(\"activemap\"))");
print("View/Edit Facilities");
print("<br>");
showRadioButton('function', 'regionedit', $function, "xajax_showVWUDSForm(xajax.getFormValues(\"activemap\"))");
print("View/Edit DEQ Regions");
print("<br>");
showRadioButton('function', 'facilityview', $function, "xajax_showFacilityViewForm(xajax.getFormValues(\"activemap\"))");
print("View/Edit Annual Data By Facility");
print("<br>");
#showRadioButton('function', 'flowtrends', $function, "xajax_showFlowZoneForm(xajax.getFormValues(\"activemap\"))");
#print("Show Flow Trends");
print("<br>");
print("</td>");
print("</tr></table>");
#showSubmitButton('changefunction','Change Function');
print("</form>");
print("<hr>");

if (count($allsegs) <= 10) {
   $ls = join(', ', $allsegs);
   print("<b>Selected Segments:</b> $ls <br>");
}
print("<table width=100% border=1><tr>");
print("<td valign=top bgcolor=#E2EFF5>");
print("\n<div id='controlpanel' bgcolor='lightgrey'></div>\n");
print("</td>");
print("</tr></table>");
/* START segment of code to show the results of a query */

# if it is a map click, we get the areas that have been selected and their eligible BMPs
switch ($function) {


}




print("   </td>");
print("   <td valign=top width=350>");

include('medit_controlfooter.php');

print("   </td>");
print("</tr>");

print("<tr>");
print("   <td colspan=2>");
print("\n<div id='workspace'></div>\n");

print("   </td>");
print("</tr>");
print("</table>");

?>

</body>
</html>
