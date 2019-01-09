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
require("xajax_maintenance.common.php");
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

if (isset($_POST['createscenario'])) {
   $scenyears = $_POST['scenyears'];
   # must have at least one year selected to create a scenario. Otherwise, default to current year
   if ( !(count($scenyears) > 0) ) {
      $scenyears = array( date('Y') );
   }
   if (isset($_POST['scenarioname'])) {
      $scenarioname = $_POST['scenarioname'];
      $shortname = $_POST['shortname'];
      $scenyears = $_POST['scenyears'];
      $otherscen = $_POST['otherscen'];
      $src_scenario = $_POST['src_scenario'];
      $groupid = $_POST['groupid'];
      $gperms = $_POST['gperms'];
      $pperms = $_POST['pperms'];
      #print_r($_POST);
      if (!$otherscen) {
         $src_scenario = -1;
      }
      if (!$selsegs) {
         $impsegs = $allsegs;
      }
      $newscenarioid = createScenario($listobject, $projectid, $userid, $scenarioname, $shortname, $allsegs, $debug);

      if ($newscenarioid > 0) {
         $scenarioid = $newscenarioid;
      }
   }
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
/*
showRadioButton('function', 'setupbmps', $function);
print("Initialize BMP Efficiency Tables<br>");
showRadioButton('function', 'redistributebmps', $function);
print("Re-Distribute BMPs<br>");
showRadioButton('function', 'recalculate', $function);
print("Re-Calculate BMP Efficiencies<br>");
showRadioButton('function', 'recreateml', $function);
print("Re-Create Masslinks<br>");
*/
showRadioButton('function', 'createdomain', $function, "xajax_showCreateDomainForm(xajax.getFormValues(\"activemap\"))");
print("Create a New Modeling Domain <br>");
showRadioButton('function', 'managedomain', $function, "xajax_showManageDomainForm(xajax.getFormValues(\"activemap\"))");
print("Edit Existing Modeling Domain <br>");
showRadioButton('function', 'creategroup', $function, "xajax_showCreateUserGroupForm(xajax.getFormValues(\"activemap\"))");
print("Create a New User Group <br>");
showRadioButton('function', 'editgroup', $function, "xajax_showEditUserGroupForm(xajax.getFormValues(\"activemap\"))");
print("Edit an Existing User Group <br>");
print("</td>");
print("<td valign=top bgcolor=#E2EFF5>");
showRadioButton('function', 'deletescenario', $function);
print("Delete A Scenario <br>");
if ($usertype == 1) {
   showRadioButton('function', 'useradd', $function, "xajax_showUserAddForm(xajax.getFormValues(\"activemap\"))");
   print("Add A User <br>");
   showRadioButton('function', 'sendwelcomes', $function);
   print("Print Welcome Messages <br>");
   showRadioButton('function', 'showlogins', $function, "xajax_showLogins(xajax.getFormValues(\"activemap\"))");
   print("Show Logins <br>");
   showRadioButton('function', 'copygroups', $function, "xajax_showGroupCopyForm(xajax.getFormValues(\"activemap\"))");
   print("Copy Groupings to Users <br>");
}
showRadioButton('function', 'changepassword', $function, "xajax_showChangePasswordForm(xajax.getFormValues(\"activemap\"))");
print("Change Password <br>");
print("<br>");
print("</td>");
print("</tr></table>");
#showSubmitButton('changefunction','Change Function');
print("</form>");
print("<hr>");
print("<table width=100% border=1><tr>");
print("<td valign=top bgcolor=#E2EFF5>");
print("\n<div id='controlpanel' bgcolor='lightgrey'></div>\n");
print("</td>");
print("</tr></table>");

/* START segment of code to show the results of a query */

print("   </td>");
print("   <td valign=top width=350>");

include('medit_controlfooter.php');

print("   </td>");
print("</tr>");
print("</table>");

?>

</body>
</html>
