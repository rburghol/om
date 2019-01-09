<html>
<head>
<script src="/scripts/scripts.js"></script>
<?php
include("./medit_header.php");
?>
</head>
<?php
$noajax = 1;
include('./xajax_modeling.element.php');
include('./xajax_analysis.php');
# Set the map file to use
error_reporting(E_ERROR);
include("./cust_asetup.php");
//include("./summary/lib_cova_summary.php");
$elementid = -1;
$templateid = 321275;

if (isset($_GET['elementid'])) { 
   // this will happen if we are loading an element
   $elementid = $_GET['elementid'];
}
if (isset($_GET['single'])) { 
   // single mode only allows viewing/editing of 1 specific elementid (for use in embedding in wiki pages)
   $single = $_GET['single'];
} else {
   $single = 0;
}
if (isset($_POST['single'])) { 
   $single = $_POST['single'];
}
if (isset($_POST['elementid'])) { 
   $elementid = $_POST['elementid'];
}
if (isset($_POST['actiontype'])) { 
   $actiontype = $_POST['actiontype'];
   $action = $actiontype;
}
#########################################
# Print Headers
#########################################
if (!$single) {
   include("./medit_menu.php");
}
#########################################
# END - Print Headers
#########################################


$thisrec = array();
$filename = "./forms/project_info.html";
if ($userid == 1) {
   $debug = 1;
} else {
   $debug = 0;
}

$form_name = 'form1';
$adminname = 'project_info';
$adminsetup = $adminsetuparray[$adminname];
$adminsetup['column info']['groupid']['params'] = "groups:groupid:groupname:groupname:0:groupid in (select groupid from mapusergroups where userid = $userid) ";
$adminsetup['table info']['formName'] = $form_name;
$content = file_get_contents($filename);

if ($debug) {
   print("Form submission: " . print_r($_POST,1) . "\n<br>");
}
// if we have had data submitted, save:
if ($actiontype == 'save') { 
   if ($elementid == -1) {
      // clone the template object
      // then save the form data into the results of the clone operation
      $params = array(
         'projectid'=>$projectid,
         'dest_scenarioid'=>$_SESSION['defscenarioid'],
         'elements'=>array($templateid),
         'dest_parent'=>$destination
      );
      print("Creating a new Project<br>");
      $output = copyModelGroupFull($params, 1);
      $elementid = $output['elementid'];
      error_log("Created new copy of template ($templateid) - $elementid ");
      error_log($output['innerHTML']);
   }
   print("Saving results of form submission <br>\n");
   //$debug = 1;
   $result = saveCustomElementForm($listobject, $adminsetup, $elementid, $_POST, $content, 0);
   if ($debug) {
      print($result['innerHTML'] . "<br>");
      print(print_r($result['errors']) . "<br>");
      print(print_r($result['debugHTML']) . "<br>");
   }
}

$toggleText = " style=\"display: $toggleStatus\"";
$menuHTML = '';
if (!$single) {
   if ($elementid == -1) {
      $toggleText = 'style="display: block;"';
   } else {
      $toggleText = 'style="display: none;"';
   }
   $menuHTML .= "<div class='insetBox'><a class='mH' id='op$i' ";
   $menuHTML .= "onclick=\"toggleMenu('vwp_projinfo')\" title='Click to Expand/Hide'>(+) VWP Project Info Sheets (click to view/hide)</a>";
   $menuHTML .= "<div id='vwp_projinfo' $toggleText>";
   $vwp_projs = showVWPTemplates($listobject, $userid);
   //print_r($vwp_projs);
   if ($debug) {
      $menuHTML .= $vwp_projs['debugHTML'];
   }
   $user_vwp = $vwp_projs['user'];
   $group_vwp = $vwp_projs['group'];
   $menuHTML .= "<table><tr><td valign=top width=25%><b>General Functions</b><ul class=mNormal>";
   $menuHTML .= "<li><a onclick='document.forms[\"$form_name\"].elements[\"actiontype\"].value = \"load_template\"; document.forms[\"$form_name\"].submit()'>Create New VWP Permit Model Info Sheet</a></li>";
   $menuHTML .= "</ul></td>";
   $menuHTML .= "<td valign=top width=37%><b>Your Existing Projects</b><ul class=mNormal>";
   foreach ($user_vwp as $thisvwp) {
      $vid = $thisvwp['elementid'];
      $vname = $thisvwp['elemname'];
      $menuHTML .= "<li><a onclick='document.forms[\"$form_name\"].elements[\"actiontype\"].value = \"edit\";  document.forms[\"$form_name\"].elements[\"switch_elid\"].value = $vid; document.forms[\"$form_name\"].submit()'>$vname</a>";
   }
   $menuHTML .= "</ul></td><td valign=top width=37%><b>Other Projects That You Can Access</b><ul class=mNormal>";
   foreach ($group_vwp as $thisvwp) {
      $vid = $thisvwp['elementid'];
      $vname = $thisvwp['elemname'];
      $oname = $thisvwp['owner'];
      $menuHTML .= "<li><a onclick='document.forms[\"$form_name\"].elements[\"actiontype\"].value = \"edit\"; document.forms[\"$form_name\"].elements[\"switch_elid\"].value = $vid; document.forms[\"$form_name\"].submit()'>$vname ($oname)</a>";
   }
   $menuHTML .= "</ul></td></tr></table>";
   $menuHTML .= "</div></div>";
   print($menuHTML);
}

//**** NOW - LOAD OBJECTS to edit if requested ****
if (isset($_POST['switch_elid'])) { 
   if ($_POST['switch_elid'] > 0) {
      // this will happen if we are loading an element
      $elementid = $_POST['switch_elid'];
   }
}
// if elementid = -1, we want to show the template object
if ($actiontype == 'load_template') {
   $showel = $templateid;
   $elementid = -1;
} else {
   $showel = $elementid;
}
// evaluate permissions

if ($showel <> -1) {
   $action = 'save';
   $unserobjects = array(); // clear unserobjects so we will get fresh copies
   if ($debug) {
      print("getModelVarsForCustomForm($showel, $filename, $debug);<br>");
      print("User: $userid, Default Scenario: " . $_SESSION['defscenarioid'] . " <br>");
   }
   $thisrec = getModelVarsForCustomForm($showel, $filename, $debug);
   if ($debug) {
      print("getModelVarsForCustomForm() returned: <br>" . print_r($thisrec,1) . " <br>");
   }
      
   // evaluate permissions
   $disabled = 0;
   /*
   $gid = $thisrec['groupid'];
   if ($elementid == -1) {
      $ownerid = $userid;
   } else {
      $recinfo = getElementInfo($listobject, $elementid);
      $ownerid = $recinfo['ownerid'];
   }
   $mygroups = split(',',$usergroupids);
   if ( ( !in_array($gid, $mygroups) ) or ( ($userid <> $ownerid) and ($gperms < 5) ) ) {
      $disabled = 1;
   }
   if ($ownerid == $userid) {
      $disabled = 0;
   }
   if ($userid <> $ownerid) {
      // don't allow the group to be changed
      $adminsetup['column info']['groupid']['disabled'] = 1;
      $adminsetup['column info']['gperms']['disabled'] = 1;
   }
   */
   
   //print("Parsed Custom Form: " . print_r($thisrec,1) . "<br>");
   $innerHTML .= "Element ID: $elementid <br>";
   $latdd = $thisrec['wd_lat'];
   $londd = $thisrec['wd_lon'];
   $locid = $thisrec['locid'];
   $cia_container = -1; // only set this if contid indicates an existing, COVA model object
   $scenarioid = 37; // the cova framework for chooseing locations
   //if (function_exists('findCOVALocationPossibilities') and ($userid == 1) ) {
   if (function_exists('findCOVALocationPossibilities') ) {
      $options = findCOVALocationPossibilities($listobject, $scenarioid, $latdd, $londd, $debug);
      $locHTML .= "<b>Select Model Location</b><br>";
      $locHTML .= "<div class='insetBox'>";
      $locHTML .= "<table><tr>";
      $locHTML .= "<td>";
      $cdel = '';
      $adminsetup['column info']['locid']['params'] = '';
      foreach ($options as $thisoption) {
         $contid = $thisoption['id'];
         $type = $thisoption['type'];
         $lname = $thisoption['name'];
         $area = round($thisoption['cumulative_area'],1);
         $radval = $type . $contid;
         if ($radval == $locid) {
            // set the location of the map to the selected
            $mapurl = 'http://deq2.bse.vt.edu/cgi-bin/mapserv?map=/var/www/html/wooommdev/nhd_tools/nhd_cbp_small.map&layers=nhd_fulldrainage%20poli_bounds%20proj_seggroups&mode=map&mapext=' . $box . '&mode=indexquerymap&';
            switch ($type) {
               case 'cova_ws_container':
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
            $checkpair = $cdel . $radval . "|" . " $lname - $area sqmi.<a href='/wooommdev/summary/cova_model_infotab.php?elementid=$contid' target='_new'>CIA Info</a>";
            // get map extent
            $ext = getGroupExtents($listobject, 'scen_model_element', 'poly_geom', '', '', "elementid=$contid", 0.15, $debug);
            //print("Geometry extent returned: $ext <br>");
            // mapserver only view
            //$box = join (' ', split(',',$ext));
            //$mapurl = "//deq2.bse.vt.edu/cgi-bin/mapserv?map=/var/www/html/wooommdev/nhd_tools/nhd_cbp_small.map&layers=nhd_fulldrainage%20poli_bounds%20proj_seggroups&mode=map&mapext=$box&mode=indexquerymap&elementid=$contid";
            // gmap view
            list($lon1,$lat1,$lon2,$lat2) = split(',',$ext);
            $mapurl = "//deq2.bse.vt.edu/wooommdev/nhd_tools/gmap_test.php?lon1=$lon1&lat1=$lat1&lon2=$lon2&lat2=$lat2&elementid=$contid";
            //print($mapurl . "<br>");
            $checkpair .= "|document.getElementById(\"watershed_map\").src=\"$mapurl\"";
            $cdel = ',';
         } else {
            $checkpair = $cdel . $radval . "|" .  "NHD+ $contid - $area sqmi. (Create - Admin Approval Needed)";
            $cdel = ',';
            if (!checkNHDBasinShape($usgsdb, $contid)) {
               $result = createMergedNHDShape($usgsdb,$contid, $debug);
            }
            $ext = getGroupExtents($usgsdb, 'nhd_fulldrainage', 'the_geom', '', '', "comid=$contid", 0.15, 0);
            print("Geometry extent returned: $ext <br>");
            // mapserver only view
            //$box = join (' ', split(',',$ext));
            //$mapurl = "//deq2.bse.vt.edu/cgi-bin/mapserv?map=/var/www/html/wooommdev/nhd_tools/nhd_cbp_small.map&layers=nhd_fulldrainage%20poli_bounds%20proj_seggroups&mode=map&mapext=$box&mode=indexquerymap&comid=$contid";
            //print($mapurl . "<br>");
            // gmap view
            list($lon1,$lat1,$lon2,$lat2) = split(',',$ext);
            $mapurl = "//deq2.bse.vt.edu/wooommdev/nhd_tools/gmap_test.php?lon1=$lon1&lat1=$lat1&lon2=$lon2&lat2=$lat2&comid=$contid";
            $checkpair .= "|document.getElementById(\"watershed_map\").src=\"$mapurl\"";
         }
         if ($radval == $locid) {
            $thisrec['watershed_map'] = $mapurl;
         }
         $adminsetup['column info']['locid']['params'] .= $checkpair;
         // show a small map of the location when you click/select one of the options
         // get the extent of the chosen shape for the mapserv request to be zoomed into
         // $ext = getGroupExtents($listobject, $tablename, $geomcol, $colname, $colvals, $extrawhere='', $bufferpct=0.0, $debug=0)
         // use an iframe perhaps?  or a javascript image call? or just show the selected one, and only update the map after a save?
         // iframe: document.getElementById(iframeId).src = url;
      }
      $locHTML .= "</td><td>";
      $locHTML .= "<iframe src='" . $thisrec['watershed_map'] . "'></iframe>";
      //$locHTML .= "<iframe src='http://deq2.bse.vt.edu/cgi-bin/mapserv?map=/var/www/html/wooommdev/nhd_tools/nhd_cbp_small.map&layers=nhd_fulldrainage%20poli_bounds%20proj_seggroups&mode=map&mapext=shape&mode=indexquerymap'></iframe>";
      $locHTML .= "</td></tr></table>";
      $adminsetup['column info']['locid']['params'] .= "::<br>";
      $locHTML .= "</div>";
   } else {
      if ($userid == 1) {
         $locHTML .= "getCOVACBPPointContainer does not exist<br>";
      }
   }
   $debug = 0;
   $innerHTML .= "<table>";
   $innerHTML .= showCustomHTMLForm($listobject,$thisrec,$adminsetup, $content, 0, 0, $debug, $disabled);
   $innerHTML .= "</table>";
   $submit = "document.forms[\"$form_name\"].submit()";
   if (!$disabled) {
      $innerHTML .= showGenericButton('save','Save', $submit, 1, 0);
   }
}

//

// *****************************
// set up a panel object
// *****************************

//error_reporting('E_ALL');
$panelHTML = '';
$taboutput = new tabbedListObject;
$taboutput->name = 'cia_element';
$taboutput->height = '600px';
#$taboutput->width = '100%';
$taboutput->width = '800px';
$taboutput->tab_names = array('model_props','cia_viewer');
$taboutput->tab_buttontext = array(
   'model_props'=>'Project Information',
   'cia_viewer'=>'Run Model / View Results'
);
$taboutput->init();
$taboutput->tab_HTML['model_props'] .= "<b>Properties:</b><br>";
$taboutput->tab_HTML['cia_viewer'] .= "<b>CIA Viewer:</b><br>";


// CIA
$ciarec = $thisrec;
//print("<br>CIA Container = $cia_container <br>");
if ($cia_container > 0) {
   //print("<br>Setting elementid for CIA to $cia_container <br>");
   $ciarec['elementid'] = $cia_container;
}
/*
$ciaHTML = showCOVAViewer($ciarec);
*/
$ciaHTML = "Loading http://deq2.bse.vt.edu/wooommdev/summary/cova_model_infotab.php?elementid=$cia_container <br>";
$ciaHTML .= " -- iframe src='http://deq2.bse.vt.edu/wooommdev/summary/cova_model_infotab.php?elementid=$cia_container -- /iframe --";
$ciaHTML .= "<iframe src='http://deq2.bse.vt.edu/wooommdev/summary/cova_model_infotab.php?elementid=$cia_container></iframe>";
      
$taboutput->tab_HTML['cia_viewer'] .= $ciaHTML;


$headerHTML = '<hr>';
$headerHTML .= "<form id='$form_name' name='$form_name' action='$scriptname' method=post>";
$footerHTML = showHiddenField('elementid',$elementid, 1, 0);
$footerHTML .= showHiddenField('single',$single, 1, 0);
$footerHTML .= showHiddenField('switch_elid',-1, 1, 0);
$footerHTML .= showHiddenField('actiontype',$action, 1, 0);
$footerHTML .= "</form>";

$taboutput->tab_HTML['model_props'] .= $headerHTML;
$taboutput->tab_HTML['model_props'] .= $innerHTML;
$taboutput->tab_HTML['model_props'] .= $footerHTML;

//print("Length of panelHTML = " . strlen($panelHTML) . " <br>");

// RENDER FINAL TABBED OBJECT
$taboutput->createTabListView($activetab);
# add the tabbed view the this object
$panelHTML .= $taboutput->innerHTML;
print($panelHTML);
//print($innerHTML);

if ($debug) {

   print_r($_POST);
}
?>
</html>
