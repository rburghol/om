<?php

$noajax = 1;
$projectid = 3;
$userid = 1;
$scenarioid = 37;
$wd_template_id = 284895;
$new_wd_template_id = 340402; // a generic shell object with broadcast properties
$cbp6_runoff_container_template_id = 340393;
$cbp6_runoff_file_template_id = 340398;

include_once('xajax_modeling.element.php');
//error_reporting(E_ALL);
##include_once("./lib_batchmodel.php");

if (count($argv) < 4) {
   print("Usage: fn_create_element.php object_class name scenarioid [templateid=-1] [parentid=-1] [custom1] [custom2] \n");
   die;
}
$args = $argv;
$script = $args[0];
$cvalues['newcomponenttype'] = $args[1];
$cvalues['name'] = $args[2];
$cvalues['scenarioid'] = $args[3];

if (isset($args[4]) and $args[4] > 0) {
  $cvalues['templateid'] = $args[4];
}

if (!isset($cvalues['templateid'])) {
  error_log("Attempting to create " . print_r($cvalues,1));
  // insertBlankComponent doesn't actually create anything!
  $feedback = insertBlankComponent($cvalues);
  // createObjectType seems to be OK ?
  $result = createObjectType($cvalues['newcomponenttype'], $cvalues);
  //error_log("Creation routine output:" . print_r($result,1) );
  $thisobject = $result['object'];
  $listobject->querystring = "SELECT currval('scen_model_element_elementid_seq') ";
  error_log("Get parent ID:" . $listobject->querystring );
  $listobject->performQuery();
  $listobject->show = 0;
  #$innerHTML .= "$listobject->outstring <br>";
  $newelid = $listobject->getRecordValue(1,'currval');
  error_log("New Elementid:" . $newelid );
} else {
  // @todo: finish this.
  error_log("Cloning is disabled.");
  /*
  if ($cvalues['templateid'] > 0) {
    $outvals = cloneModelElement($cvalues['scenarioid'], $cvalues['templateid']);
    print("Update routine output:" . $outvals['innerHTML'] . " <br>");
  }
  */
}

?>
