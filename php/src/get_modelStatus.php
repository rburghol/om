<?php
header('Content-Type: application/json');

# set up db connection
#include('config.php');
$noajax = 1;
$projectid = 3;
include_once('xajax_modeling.element.php');
$debug = 0;

$elementid = FALSE;
if (isset($_GET['elementid'])) {
   $elementid = $_GET['elementid'];
}
if (isset($argv[1])) {
   $elementid = $argv[1];
}
if (isset($argv[2])) {
   $mins = $argv[2];
} else {
  $mins = 120;
}

if ($elementid === FALSE) {
  $info = "ERROR: get_modelStatus.php called without elementid.";
  $json = json_encode(array('error' => "get_model.php called without elementid"));
} else {
  if (($elementid == -1)) {
    $status_update = getModelActivity($mins, $elementid, FALSE);
    error_log("REcs:" . print_r($status_update,1));
  } else {
    $status_update = getModelRunStatus($listobject, $elementid);
  }
  error_log("************ get_modelStatus.php $elementid called **************");
  $info = "json_encode handled object properly.";
  $json = json_encode($status_update);
}


//error_log("$info");
error_log("************ get_modelStatus.php $elementid Finished **************");
echo $json;
?>
