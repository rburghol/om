<?php

##########################################
#  Set Up Menu Items Here
##########################################
if (!isset($restrict_menus)) {
   $restrict_menus = array();
}
$gbIsHTMLMode = $amap->gbIsHTMLMode;
#$menu['medit_bmps.php']['label'] = 'Edit BMPs';
#$menu['medit_bmps.php']['title'] = 'Vortex -  BMP Reporting and Editing';
#$menu['medit_landuse.php']['label'] = 'Edit Land Use';
#$menu['medit_landuse.php']['title'] = 'Vortex - Land Use Processing';
#$menu['medit_sources.php']['label'] = 'Edit Sources';
#$menu['medit_sources.php']['title'] = 'Vortex - Model Source Inputs';
$menu['medit_modeling2.php']['label'] = 'Model Builder';
$menu['medit_modeling2.php']['title'] = 'On-line Model Builder';
if ($userid == 1) {
   $menu['medit_modeling.php']['label'] = 'Old-version Model Builder';
   $menu['medit_modeling.php']['title'] = 'On-line Model Builder';
}
$menu['medit_watersupply.php']['label'] = 'Water Supply Planning';
$menu['medit_watersupply.php']['title'] = 'Water Supply Planning';
$menu['vwp_project.php']['label'] = 'VWP Permiting';
$menu['vwp_project.php']['title'] = 'VWP Permit Builder';
#$menu['medit_modelinputs.php']['label'] = 'Model Inputs/Outputs';
#$menu['medit_modelinputs.php']['title'] = 'Vortex - Model Input/Output Creation and Visualization';
$menu['medit_maintenance.php']['label'] = 'Maintenance';
$menu['medit_maintenance.php']['title'] = 'Scenario Maintenance';
$menu['helpsystem.php']['label'] = 'Help';
$menu['helpsystem.php']['title'] = 'WOOOMM: Tutorials, Troubleshooting and Error Reporting';
$menu['logout.php']['label'] = 'Logout';
$menu['logout.php']['title'] = 'Logged Out';

##########################################
#  END - Menu Items
##########################################

##################################
# System Tools Menu - may take place of this above menu shortly


#$getvarlist = "projectid=$projectid&lreditlist=$lreditlist&currentgroup=$currentgroup&scenarioid=$scenarioid&thisyear=$thisyear&$viewyear=$viewyear&gbIsHTMLMode=$gbIsHTMLMode&currentextent=$currentextent";

$getvarlist = "projectid=$projectid&currentgroup=$currentgroup&scenarioid=$scenarioid&thisyear=$thisyear&$viewyear=$viewyear&gbIsHTMLMode=$gbIsHTMLMode&currentextent=$currentextent";

#########################################
# Print HTM Interface Headers and Menu
#########################################
$callpieces = preg_split('[\/]', $scriptname);
$callfile = $callpieces[(count($callpieces) - 1)];
#print("$callfile<br>");
print("<table width = 100%><tr><td align=center bgcolor=#E2EFF5>");

$sep = '';
//print_r($restrict_menus);
if (count($restrict_menus) > 0) {
   $pages = array_values($restrict_menus);
} else {
   $pages = array_keys($menu);
}
//print_r($pages);

foreach ($pages as $thispage) {
   $label = $menu[$thispage]['label'];
   $title = $menu[$thispage]['title'];
   print("$sep ");
   if ( ($callfile == $thispage) or ( (!in_array($callfile, array_keys($menu))) and ($defaultpage == $thispage) ) ) {
      print("<b>$label</b>");
      $pagetitle = $title;
   } else {
      print("<a href='./$thispage?$getvarlist'><font size=+1>$label</font></a>");
   }
   $sep = ' | ';
}

print(" <font size=-1><i>( Logged in as $un ) </i> </font>");

print("</td></tr></table>");
print("<font size=+1>$pagetitle</font>");
print("<br>");
#########################################
# END - Print Headers
#########################################


?>
