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

?>

<script type="text/javascript">
function confirmDeleteElement(elemname) {
    var answer = confirm("Are you sure that you wish to delete " + elemname + "?")
    if (answer){
        document.forms['elementtree'].elements.actiontype.value='delete'; 
        xajax_deleteObject(xajax.getFormValues('elementtree'));
    }
}
</script>
</head>
<body bgcolor=ffffff onload='init();'>

<?php
#########################################
# END - Call Header
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
/*
print("<form action='$scriptname' method=post name='activemap' id='activemap'>");
# show project navigation controls
include('medit_controls.php');
# make all layers visible
include('./medit_layers.php');
print("</form>");
*/
# Ned to assemble the control panel interface, this is a table with multiple columns, and in the case of the workspace
# a multi-panel with one view for editing and browsing elements ('workspace'), and a view for viewing the map ('map')
# format output into tabbed display object

###########################################
###    Object Browser Definition        ###
###########################################
$browser = "<div style=\"border: 1px solid rgb(0 , 0, 0); background: #cdc9c9;\" ><b>Map Layer Selector</b> <a class=\"mHier\"id=\"browsertoggle\" ";
$browser .= "onclick=\"toggleMenu('objectbrowser')\" title=\"Toggle Map Layer Selector Visibility\"><i>Show/Hide </i></a><br>";
$browser .= "\n<div id='objectbrowser' style=\"border: 1px solid rgb(0 , 0, 0); border-style: dotted; overflow: auto; height: 360px; width: 240px; display: block;  background: #eee9e9;\">";
//$browser .= showHierarchicalMenu($listobject, $projectid, $scenarioid, $userid, $usergroupids, $debug);$menuHTML = '';
$menuHTML .= "<form name='elementtree' id='elementtree'>";
$menuHTML .= showHiddenField('actiontype', 'editelement', 1);
$menuHTML .= showHiddenField('projectid', $projectid, 1, 'projectid');
$menuHTML .= showHiddenField('scenarioid', $scenarioid, 1, 'scenarioid');
$menuHTML .= showHiddenField('activecontainerid', -1, 1, 'activecontainerid');
$menuHTML .= showHiddenField('elementid', '', 1, 'elementid');
$menuHTML .= showHiddenField('newcomponenttype', '', 1, 'newcomponenttype'); 
$menuHTML .= showHiddenField('vis_allobjects', $vis_allobjects, 1, 'vis_allobjects');
$menuHTML .= "</form>";
$browser .= $menuHTML;
$browser .= "</div></div>\n";

###########################################
###    Object Toolbox Browser           ###
###########################################
$toolbox = "<div style=\"border: 1px solid rgb(0 , 0, 0); background: #cdc9c9;\" ><b>Toolbox</b> <a class=\"mHier\"id=\"toolboxtoggle\" ";
$toolbox .= "onclick=\"toggleMenu('toolbox')\" title=\"Toggle Toolbox Visibility\"><i>Show/Hide </i></a><br>";
$toolbox .= "\n<div id='toolbox' style=\"border: 1px solid rgb(0 , 0, 0); border-style: dotted; overflow: auto; height: 240px; width: 240px; display: block;   background: #eee9e9;\">";
//$toolbox .= showToolboxMenu($listobject, $projectid, $scenarioid, $userid, $usergroupids, $debug);
$toolbox .= "</div></div>\n";

###########################################
###           Control Panel             ###
###########################################
$controlpanel = "\n<div id='controlpanel' bgcolor='lightgrey' style=\"border: 1px solid rgb(0 , 0, 0); border-style: dotted; overflow: auto; \"></div>\n";

###########################################
###             Workspace               ###
###########################################
$workspace = "\n<div id='workspace' bgcolor='lightgrey' style=\"border: 1px solid rgb(0 , 0, 0); border-style: dotted; overflow: auto; \"></div>\n";

###########################################
###    The Main Desktop Area            ###
###########################################
$taboutput = new tabbedListObject;
$taboutput->name = 'map_window';
$taboutput->width = '840px';
$taboutput->height = '700px';
$taboutput->tab_names = array('editentry','openlayers');
$taboutput->tab_buttontext = array(
   'editentry'=>'Data Editor',
   'openlayers'=>'Wikished View'
);
include('medit_controlfooter2.php'); # this populates the variable $mapHTML with the map screen and form values
$taboutput->tab_HTML['openlayers'] = $mapHTML;
$taboutput->tab_HTML['editentry'] = $controlpanel;
# now render these for use in the table below
$taboutput->createTabListView('openlayers');
$workspaceHTML .= $model_status;
$workspaceHTML .= $taboutput->innerHTML;


print("<table width=1024px border=1>");
print("<tr>");
print("<td width=180px valign=top>");
   print("<table width=100% height=100% border=1>");
   print("<tr><td>");
   print($browser);
   print("</td></tr>");
   print("<tr><td>");
   print($toolbox);
   print("</td></tr>");
   print("</table>");
print("</td>");
print("<td width=944px valign=top bgcolor=#E2EFF5 valign=top>");
   print("<table width=100% height=100% border=1>");
   print("<tr><td valign=top>");
   print($controlpanel);
   print("</td></tr>");
   print("<tr><td valign=top>");
   print($workspaceHTML);
   print("</td></tr>");
   print("</table>");
print("   </td>");
print("</tr>");
print("</table>");

?>

</body>
</html>
