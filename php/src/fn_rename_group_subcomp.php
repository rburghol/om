<?php

$noajax = 1;
$projectid = 3;
$target_scenarioid = 37;
$cbp_scenario = 4;
$userid = 1;
$noajax = 1;
include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
//include_once("./lib_batchmodel.php");

if (count($argv) < 4) {
   print("Usage: php fn_rename_group_subcomp.php scenarioid [subcomp1|newname1],subcomp2...] [elementid] [elemname] [custom1] [custom2] \n");
   print("Use '-1' as value for scenarioid to update all scenarios (use with caution) \n");
   print("Example (rename \"Listen on Child\" to \"Listen to Children\" on 213933 \n");
   print("php fn_rename_group_subcomp.php 37 \"Listen on Child|Listen to Children\" 213933 \n");
   die;
}

$scenarioid = $argv[1];
if (isset($argv[2])) {
   $subcomps = explode(',', $argv[3]); 
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
$listobject->querystring .= " AND elementid <> $src_elementid ";
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
print("$listobject->querystring ; <br>");
$listobject->performQuery();
//$listobject->showList();

$recs = $listobject->queryrecords;
//error_reporting(E_ALL);
foreach ($recs as $thisrec) {
  $elid = $thisrec['elementid'];
  print("Editing $subcomp_name on $elemname ($elid) \n");
  $loadres = unSerializeSingleModelObject($elid);
  $thisobject = $loadres['object'];
  foreach ($subcomps as $thiscomp) {
    $scs = explode("\|", $thiscomp);
    if ( (count($scs) == 1) or ($scs[0] == $scs[1]) ) {
      continue;
    }
    print("Trying to rename Sub-comp $scs[0] to $scs[1] <br>\n" . print_r($scs,1) . "\n");
    $thisobject->processors[$scs[1]] = $thisobject->processors[$scs[0]];
    unset($thisobject->processors[$scs[0]]);
    saveObjectSubComponents($listobject, $thisobject, $elid, 1);
    print("$cr<br>\n");
    print("$msg<br>\n");
    $i++;
  }
}

?>
