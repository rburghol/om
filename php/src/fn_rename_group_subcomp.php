<?php

$noajax = 1;
$projectid = 3;
$cbp_scenario = 4;
$userid = 1;
$noajax = 1;
include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
//include_once("./lib_batchmodel.php");

if (count($argv) < 4) {
   error_log("Usage: php fn_rename_group_subcomp.php scenarioid [subcomp1|newname1],subcomp2...] [elementid] [elemname] [custom1] [custom2]");
   error_log("Use '-1' as value for scenarioid to update all scenarios (use with caution)");
   error_log("Example (rename \"Listen on Child\" to \"Listen to Children\" on 213933");
   error_log("php fn_rename_group_subcomp.php 37 \"Listen on Child|Listen to Children\" 213933");
   die;
}

$scenarioid = $argv[1];
if (isset($argv[2])) {
   $subcomps = explode(',', $argv[2]); 
} else {
   $subcomps = array();
}
if (isset($argv[3])) {
   $elementid = $argv[3];
} else {
   $elementid = '';
}
if (isset($argv[4])) {
   $elemname = $argv[4];
} else {
   $elemname = '';
}
if (isset($argv[5])) {
   $custom1 = $argv[5];
} else {
   $custom1 = '';
}
if (isset($argv[6])) {
   $custom2 = $argv[6];
} else {
   $custom2 = '';
}

$listobject->querystring = "  select elementid, elemname from scen_model_element ";
$listobject->querystring .= " where ( (scenarioid = $scenarioid) or ($scenarioid = -1) ) ";
// don't overwrite the source
if ($elementid <> '') {
   $listobject->querystring .= " AND elementid = $elementid ";
}
if ($elemname <> '') {
   $listobject->querystring .= " AND elemname = '$elemname' ";
}
if ($custom1 <> '') {
   $listobject->querystring .= " AND custom1 = '$custom1' ";
}
if ($custom2 <> '') {
   $listobject->querystring .= " AND custom2 = '$custom2' ";
}
error_log("$listobject->querystring ; <br>");
$listobject->performQuery();

error_log("Renaming Subcomps " . print_r($subcomps,1) );
$recs = $listobject->queryrecords;
//error_reporting(E_ALL);
foreach ($recs as $thisrec) {
  $elid = $thisrec['elementid'];
  $loadres = unSerializeSingleModelObject($elid);
  $thisobject = $loadres['object'];
  foreach ($subcomps as $thiscomp) {
    $scs = explode('|', $thiscomp);
    if ( (count($scs) == 1) or ($scs[0] == $scs[1]) ) {
      continue;
    }
    error_log("Trying to rename Sub-comp '$scs[0]' to '$scs[1]' on element $elid ");
    if (isset($thisobject->processors[$scs[0]])) {
      $thisobject->processors[$scs[1]] = $thisobject->processors[$scs[0]];
      $thisobject->processors[$scs[1]]->name = $scs[1];
      unset($thisobject->processors[$scs[0]]);
      saveObjectSubComponents($listobject, $thisobject, $elid, 1, 1);
    } else {
      error_log("Property $scs[0] does not exist ... skipping.");
    }
    $i++;
  }
}

?>
