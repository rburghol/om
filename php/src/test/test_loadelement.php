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
}
error_reporting(E_ALL);
$res = loadModelElement( $elementid );

//print("Load Info: " . print_r($res['tableinfo'],1) . "\n");
$thisobject = $res['object'];
print("Saving Object\n");
$res = saveModelObject($elementid, $thisobject, array(), 0);
print("Save Info: " . print_r($res,1) . "\n");

?>
