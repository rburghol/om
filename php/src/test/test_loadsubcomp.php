<?php

include("config.php");
if (!isset($argv[1])) {
   if (!isset($_GET['elementid'])) {
      print("Usage: php test_loadelement.php elementid \n");
      die;
   } else {
      $elementid = $_GET['elementid'];
   }
} else {
   $elementid = $argv[1];
   $compid = $argv[2];
}
error_reporting(E_ALL);
$listobject->querystring = "select elemoperators[$compid] as opxml from scen_model_element where elementid = $elementid ";
$listobject->performQuery();
$thisop = $listobject->getRecordValue(1,'opxml');
$options = array("complexType" => "object");
$unserializer = new XML_Unserializer($options);
$unserializer->unserialize($thisop, false);
$thisobject = $unserializer->getUnserializedData();

$thisobject = $res['object'];
print("Saving Object\n");
$res = saveModelObject($elementid, $thisobject, array(), 0);
print("Save Info: " . print_r($res,1) . "\n");

?>
