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
   print("Usage: php copy_group_subcomp.php scenarioid src_elementid [subcomp1|newname1],subcomp2...] [elementid] [elemname] [custom1] [custom2] \n");
   print("Use '-1' as value for scenarioid to update all scenarios (use with caution) \n");
   print("Example (copy \"Listen on Child\" from 211449 to 213933 \n");
   print("php copy_group_subcomp.php 37 211449 \"Listen on Child\" 213933 \n");
   die;
}

$scenarioid = $argv[1];
$src_elementid = $argv[2];
if (isset($argv[3])) {
   $subcomps = explode(',', $argv[3]); 
} else {
   $subcomps = array();
}
if (isset($argv[4])) {
   $elementid = $argv[4];
} else {
   $elementid = '';
}
if (isset($argv[5])) {
   $elemname = $argv[5];
} else {
   $elemname = '';
}
if (isset($argv[6])) {
   $custom1 = $argv[6];
} else {
   $custom1 = '';
}
if (isset($argv[7])) {
   $custom2 = $argv[7];
} else {
   $custom2 = '';
}

$obres = unserializeSingleModelObject($src_elementid);
$srcob = $obres['object'];
$name = $srcob->name;
if (count($subcomps) == 0) {
   $subcomps = array_keys($srcob->processors);
}

print("Copying components: " . print_r($subcomps,1) . "\n");

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
    $dest_object->processors[$scs[1]] = $dest_object->processors[$scs[0]];
    unset($dest_object->processors[$scs[0]]);
    saveObjectSubComponents($listobject, $dest_object, $dest_elementid, 1);
    print("$cr<br>\n");
    print("$msg<br>\n");
    $i++;
  }
}

?>
