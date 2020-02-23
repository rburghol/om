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

if (count($argv) < 3) {
   print("Usage: fn_set_vahydro1_hydrocode.php scenario custom1 [elementid] \n");
   die;
}
error_log(print_r($argv,1));
$scenario = $argv[1];
$custom1 = $argv[2];
$elementid = isset($argv[3]) ? $argv[3] : -1;

$listobject->querystring = "select elementid, custom2 from scen_model_element where custom1 = '$custom1' and scenarioid = $scenarioid ";
if ($elementid > 0) {
  $listobject->querystring .= " and elementid = $elementid ";
}
$listobject->performQuery();
error_log("$listobject->querystring ");
$elements = $listobject->queryrecords;

foreach ($elements as $element) {
  $elid = $element['elementid'];
  $riverseg = $element['custom2'];
  $loadres = unSerializeSingleModelObject($elid);
  $object = $loadres['object'];
  error_log("Checking $thisproc->name ");
  foreach ($object->processors as $thisproc) {
    // check first for new method, with props.
    // this is the VWUDS/VADEQ UserID value
    if (get_class($thisproc) == 'dataMatrix') {
      $count = count($thisproc->matrix);
      if (!($count > 0)) {
        error_log("$thisproc->name on Element $object->name ($elid) is empty");
      }
    }
  }
}



?>