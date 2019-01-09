<?php
#########################################
# Initialize libraries and functions
#########################################
#dl('php_mapscript.so');
#dl(php_mapscript.dll);

#error_reporting(2);
# show all errors/warnings/etc/
#error_reporting(2047);

include_once("config.php");
#print("<head>");
include_once("./misc_headers.php");

if ($gbIsHTMLMode and isset($_POST['mapa_x'])) {
   $mapa_x = $_POST['mapa_x'];
   $mapa_y = $_POST['mapa_y'];
   $incoords = "$mapa_x,$mapa_y";
}

$initmap = 1;
$mapbuffer = 0.3;


###################################################################
# LOCAL VARIABLES
###################################################################

###################################################################
# END LOCAL VARIABLES
###################################################################

#########################################
# Initialize map basics
#########################################
# set active layer to watersheds
$invars['active_layer'] = 'selectedsegs';
#print_r($_POST);
$watershed_tbl = "proj_subsheds";
$subshed_tbl = "proj_subsheds";
$reach_tbl = "reaches_dd";
$mapfile = $projinfo['mapfile'];
#$mapfile = 'test_shp.map';
#$debug = 1;
if ($debug) {
   print("Setting up map object.<br>");
}

#########################################
# END - Initialize map basics
#########################################


#########################################
##   Common Form Functions            ###
#########################################

function showSegSelect($listobject, $userid, $projectid, $currentgroup, $debug) {
   showActiveList($listobject, 'currentgroup', 'proj_seggroups', 'groupname', 'gid', "projectid = $projectid and ownerid = $userid ", $currentgroup, 'submit()', 'groupname', $debug);
}

function showMultiLandUseMenu($listobject, $projectid, $scenarioid, $selus, $fieldname, $extrawhere, $rows, $debug) {

   $wc = "projectid = $projectid and hspflu <> ''";
   if (strlen($extrawhere) > 0) {
      $wc .= " and $extrawhere ";
   }
   showMultiList2($listobject, $fieldname, 'landuses', 'hspflu', 'landuse, major_lutype', $selus, $wc, 'major_lutype, landuse', $debug, $rows);

}

function showSingleLandUseMenu($listobject, $projectid, $scenarioid, $selus, $fieldname, $extrawhere, $debug) {

   $wc = "projectid = $projectid and hspflu <> ''";
   if (strlen($extrawhere) > 0) {
      $wc .= " and $extrawhere ";
   }

   showActiveList($listobject, $fieldname, 'landuses', 'landuse', 'hspflu', $wc, $selus, $onchange, 'major_lutype, landuse', $debug);
}
?>
