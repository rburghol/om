<html>
<?php
#########################################
# Process Form Variable Inputs
#########################################

$noajax = 1;
if (isset($_GET['elementid'])) {
   $actiontype = $_GET['actiontype'];
   $scenarioid = $_GET['scenarioid'];
   $elementid = $_GET['elementid'];
   $formValues = $_GET;
} else {

   if (isset($_POST['elementid'])) {
      $actiontype = $_POST['actiontype'];
      $scenarioid = $_POST['scenarioid'];
      $elementid = $_POST['elementid'];
      $formValues = $_POST;
   }
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

$noajax = 1;
$userid = -1;
$projectid = 3;

$noajax = 1;
include_once('./config.php');
print("<head>");
$runid = 0;
$scenarioid = 37;

error_log("Loading Header");
include_once("../misc_headers.php");
error_reporting(E_ERROR);

?>
</head>
<body bgcolor=ffffff >
<?php
#########################################
# END - Call Header
#########################################
$divname = 'analysiswin';
$formValues['divname'] = $divname;
$awin = showAnalysisWindow($formValues, -1 , 0, 'post');
$controlHTML = $awin['innerHTML'];
print(print_r($formValues,1) . "<br>");

print("<div id='agrid_$elementid' ");
print("    style=\"border: 1px solid rgb(0 , 0, 0); ");
print("       border-style: dotted; ");
print("       overflow: auto; ");
print("       height: 480px; width: 720px; ");
print("       display: block;  ");
print("       background: #eee9e9;\" ");
print("    name='agrid_$elementid'>");
print($controlHTML);
print("</div>");

?>
</body>
</html>
