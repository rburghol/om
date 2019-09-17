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

if (count($argv) < 2) {
   print("Usage: fn_set_vahydro1_hydrocode.php riverseg  \n");
   die;
}
error_log(print_r($argv,1));
$riverseg = $argv[1];

if ($riverseg == 'all') {
  $listobject->querystring = "select elementid, custom2 from scen_model_element where custom1 = 'cova_ws_container' and scenarioid = $scenarioid ";
  $listobject->performQuery();
  $rsegs = $listobject->queryrecords;
} else {
  $elid = getCOVACBPContainer($listobject, $scenarioid, $riverseg);
  $rsegs = array(
    0 => array( 'elementid' => $elid, 'custom2' => $riverseg )
  );
}

foreach ($rsegs as $seg) {
  $elid = $seg['elementid'];
  $riverseg = $seg['custom2'];
  $wds = getCOVAWithdrawals($listobject, $elid, array(), 1);
  
  foreach ($wds as $thiswd) {
    $wd_elid = $thiswd['elementid'];
    $loadres = unSerializeSingleModelObject($wd_elid);
    $wdobject = $loadres['object'];
    // check first for new method, with props.
    // this is the VWUDS/VADEQ UserID value
    $hydrocode = $wdobject->getProp('id1', 'value');
    $wdtype = $wdobject->getProp('wdtype', 'value');
    $q = "update scen_model_element set hydrocode = '$hydrocode', wdtype = '$wdtype', riverseg = '$riverseg' where elementid = $wd_elid";
    $listobject->querystring = $q;
    error_log($q);
    $listobject->performQuery();
  }
}



?>