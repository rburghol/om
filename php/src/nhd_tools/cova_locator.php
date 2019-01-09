<?php
//error_log("cova_locator.php called with " . print_r($_GET,1));
//$debug = 1;
include_once('../xajax_config.php');
if (isset($_GET['latdd'])) {
   $latdd = $_GET['latdd'];
} else {
   $latdd = 39.0;
}
if (isset($_GET['londd'])) {
   $londd = $_GET['londd'];
} else {
   $londd = -80.0;
}
if (isset($_GET['name'])) {
   $name = $_GET['name'];
} else {
   $name = 'locid';
}
$cia_container = -1; // only set this if contid indicates an existing, COVA model object
$scenarioid = 37; // the cova framework for chooseing locations
if (function_exists('findCOVALocationPossibilities') ) {
   $options = findCOVALocationPossibilities($listobject, $scenarioid, $latdd, $londd,1);
   $locHTML .= "<div class='insetBox' width=320px id=model_locator>";
   //$locHTML .= "<table><tr>";
   //$locHTML .= "<td>";
   $locHTML .= "<ul>";
   $cdel = '';
   $adminsetup = array('table info'=>array(), 'column info'=>array());
   $adminsetup['column info']['locid'] = array("type"=>-1,"params"=>"","label"=>"Loc ID? ","visible"=>1, "readonly"=>1, "width"=>6);
   $adminsetup['column info']['locid']['type'] = 23;
   $selected = 0;
   foreach ($options as $thisoption) {
      $contid = $thisoption['id'];
      $type = $thisoption['type'];
      $lname = $thisoption['name'];
      $area = round($thisoption['cumulative_area'],1);
      $radval = $type . $contid;
      if ($radval == $locid) {
         // set the location of the map to the selected
         $mapurl = 'http://deq2.bse.vt.edu/cgi-bin/mapserv?map=/var/www/html/om/nhd_tools/nhd_cbp_small.map&layers=nhd_fulldrainage%20poli_bounds%20proj_seggroups&mode=map&mapext=' . $box . '&mode=indexquerymap&';
         switch ($type) {
            case 'cova_ws_container':
               $mapurl .= "elementid=$contid";
            break;
            
            case 'cova_ws_subnodal':
               $mapurl .= "elementid=$contid";
            break;
            
            case 'nhd+':
               $mapurl .= "compid=$contid";
            break;
         }
         $thisrec['watershed_map'] = $mapurl;
         // the selected object is a watershed container, so set cia_container
         $cia_container = $contid;
      }
      $checkpair = '';
      if ( ($contid > 0) and ($type <> 'nhd+') ) {
         $label = "VAHydro Main Stem Segment $lname - $area sqmi.<a href='/om/summary/cova_model_infotab.php?elementid=$contid' target='_new'>CIA Info</a>";
         // get map extent
         $ext = getGroupExtents($listobject, 'scen_model_element', 'poly_geom', '', '', "elementid=$contid", 0.15, $debug);
         //print("Geometry extent returned: $ext <br>");
         // gmap view
         list($lon1,$lat1,$lon2,$lat2) = explode(',',$ext);
         $mapurl = "//deq2.bse.vt.edu/om/nhd_tools/gmap_test.php?lon1=$lon1&lat1=$lat1&lon2=$lon2";
         $mapurl .= "&lat2=$lat2&elementid=$contid";
         $onclick = "document.getElementById(\"watershed_map\").src=\"$mapurl\"";
      } else {
         $label = "NHD+ Segment $contid - $area sqmi. ";
         if (!checkNHDBasinShape($usgsdb, $contid)) {
            $result = createMergedNHDShape($usgsdb,$contid, $debug);
         }
         $ext = getGroupExtents($usgsdb, 'nhd_fulldrainage', 'the_geom', '', '', "comid=$contid", 0.15, 0);
         //print("Geometry extent returned: $ext <br>");
         // gmap view
         list($lon1,$lat1,$lon2,$lat2) = explode(',',$ext);
         $mapurl = "//deq2.bse.vt.edu/om/nhd_tools/gmap_test.php?lon1=$lon1&lat1=$lat1&lon2=$lon2&lat2=$lat2&comid=$contid";
         $onclick = "document.getElementById(\"watershed_map\").src=\"$mapurl\"";
      }
      if ($radval == $locid) {
         $thisrec['watershed_map'] = $mapurl;
         $selected = 1;
      }
      $locHTML .= "<li>" . showRadioButton($name, $radval, $locid, $onclick, 1, 0, '') . $label . "</li>";
   }
   $locHTML .= "</ul>";
   if (!$selected) {
      // find the outlet NHD+ container and center the map there
      if (!checkNHDBasinShape($usgsdb, $contid)) {
         $result = createMergedNHDShape($usgsdb,$contid, $debug);
      }
      $nhdinfo = findNHDSegment($usgsdb, $latdd, $londd, $debug, 'sqmi');
      $nhd_area = $nhdinfo['cumdrainag'];
      $comid = $nhdinfo['comid'];
      $ext = getGroupExtents($usgsdb, 'nhd_fulldrainage', 'the_geom', '', '', "comid=$contid", 0.15, 0);
      //error_log("Geometry extent returned: $ext <br>");
      // gmap view
      list($lon1,$lat1,$lon2,$lat2) = explode(',',$ext);
      $mapurl = "http://deq2.bse.vt.edu/om/nhd_tools/gmap_test.php?lon1=$lon1&lat1=$lat1&lon2=$lon2&lat2=$lat2&comid=$comid";
      $thisrec['watershed_map'] = $mapurl;
   }
   //$locHTML .= "</td><td>";
   $locHTML .= "<iframe src='" . $thisrec['watershed_map'] . "' id='watershed_map' width=260 height=260 ></iframe>";
   //$locHTML .= "Map URL:" . $thisrec['watershed_map'] . "<br>";
   //$locHTML .= file_get_contents($thisrec['watershed_map']);
   //$locHTML .= "</td></tr></table>";
   $locHTML .= "</div>";
}
print $locHTML;
?>