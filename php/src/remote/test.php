<?php
$noajax = 1;
include('./config.php');

if (isset($argv[1])) {
   $elementid = $argv[1];
} else {
   $elementid = -1;
}
if (isset($_GET['elementid'])) {
   $elementid = $_GET['elementid'];
}

function getFlows($elementid,$lus = '') {
   $unser = unserializeSingleModelObject($elementid);
   $thisobject = $unser['object'];
   $thisobject->debug = 1;
   $flowdata = $thisobject->getModelOutputData('flowsum',$lus);
   return array('records'=>$flowdata,'debug'=>$thisobject->debugstring);
}


$result = getFlows($elementid, 'for');
$records = $result['records'];
$listobject->queryrecords = $records;
$listobject->showList();

?>
