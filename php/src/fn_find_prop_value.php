<?php

$noajax = 1;
$projectid = 3;
$userid = 1;
$scenarioid = 37;
$wd_template_id = 284895;
# New Generic Surface Water User: 340402

include_once('xajax_modeling.element.php');
//error_reporting(E_ALL);
##include_once("./lib_batchmodel.php");
$propvalue = NULL;

if (count($argv) < 3) {
   print("Usage: fn_find_prop_value.php scenario custom1 [elementid=-1] propname [propvalue=NULL]\n");
   die;
}
error_log(print_r($argv,1));
$scenario = $argv[1];
$custom1 = $argv[2];
$elementid = isset($argv[3]) ? $argv[3] : -1;
if (empty($elementid)) {
  $elementid = -1;
}
$propname = isset($argv[4]) ? $argv[4] : NULL;
$propvalue = isset($argv[5]) ? $argv[5] : NULL;
$listobject->querystring = "select elementid, custom2 from scen_model_element where custom1 = '$custom1' and scenarioid = $scenarioid ";
if ($elementid > 0) {
  $listobject->querystring .= " and elementid = $elementid ";
}
$listobject->performQuery();
error_log("$listobject->querystring ");
$elements = $listobject->queryrecords;
$bad_els = array();
$bad_props = array();
$bad_deets = array();

foreach ($elements as $element) {
  $elid = $element['elementid'];
  $riverseg = $element['custom2'];
  $loadres = unSerializeSingleModelObject($elid);
  $object = $loadres['object'];
  if (isset($object->processors['vahydro_hydroid'])) {
    $vahydro_hydroid = $object->processors['vahydro_hydroid']->getProp('value');
  } else {
    $vahydro_hydroid = -1;
  }
  error_log("Checking $object->name ");
  $match = FALSE;
  if (property_exists($object, $propname)) {
    if (!($propvalue === NULL)) {
      $propval = $object->getProp($propname);
      if ($propval == $propvalue) {
        $match = TRUE;
      }
    } else {
      $match = TRUE;
    }
    if ($match) {
      $match_els[] = $elid;
      $match_deets[] = array(
        'elementid' => $elid,
        'vahydro_pid' => $vahydro_hydroid
      );
    }
  }
    
  /*
  foreach ($object->processors as $thisproc) {
    // check first for new method, with props.
    // this is the VWUDS/VADEQ UserID value
    if (get_class($thisproc) == 'dataMatrix') {
      $count = count($thisproc->matrix);
      if (!($count > 0)) {
        error_log("$thisproc->name on Element $object->name ($elid) is empty");
        if (!in_array($thisproc->name, $bad_props)) {
          $bad_props[] = $thisproc->name;
        }
        if (!in_array($vahydro_hydroid, $bad_pids)) {
          if ($vahydro_hydroid > 0) {
            $bad_pids[] = $vahydro_hydroid;
          }
        }
        if (!in_array($elid, $bad_els)) {
          $bad_els[] = $elid;
        }
        if (!isset($bad_deets[$elid])) {
          $bad_deets[$elid] = array('elementid'=>$elid, 'vahydro_pid' => $vahydro_hydroid);
        }
        $bad_deets[$elid][$thisproc->name] = 'empty';
      }
    }
  }
  */
}
error_log("Matching Pairs: " . print_r($match_deets,1));
error_log("Matching Elements: " . implode(" ", $match_els));
error_log("Matching VAHydro pids: " . implode(" ", $match_pids));


?>