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
if (isset($_GET['runid'])) {
   $runid = $_GET['runid'];
}

function getFlows($elementid,$lus = '', $debug = 0) {
   $unser = unserializeSingleModelObject($elementid);
   $thisobject = $unser['object'];
   $thisobject->debug = $debug;
   $flowdata = $thisobject->getModelOutputData('flowsum',$lus);
   return array('records'=>$flowdata,'lunames'=>$thisobject->lunames,'debug'=>$thisobject->debugstring);
}
$debug = 1;
print("<table><tr>");
print("<td><b>Model Run Summary</b>");
$quick_num = 1000;
print("Running  retrieveRunSummary(listobject, $elementid, $runid)");

$rundata = retrieveRunSummary($listobject, $elementid, $runid);

if (strlen(trim($rundata['run_summary'])) == 0) {
   $elname = getElementName($listobject, $elementid);
   $order = $rundata['order'];
   $status = $rundata['run_status'];
   $rundata['message'] = "No run info stored for $elname ($elementid) <br>\n";
   $rundata['message'] .= "Run Status: $status <br>\n";
   if ($debug) {
      $rundata['message'] .= "Query: " . $rundata['query'] . "<br>\n";
   }
} else {
   $rundata['message'] = $rundata['run_summary'];
}
$formatted = formatPrintMessages($rundata);
print($formatted);

//if (isset($elementid)) {
//   print("<a href='./verifyTree.php?elementid=$elementid&runid=$runid'>Click here to verify this tree</a> <br>\n");
//}

print("</td>");


print("<td>");
// BEGIN LAND USE RUNOFF panel
// get land seg elements
$landsegs = getCOVACBPLanduseObjects($listobject, $elementid);

print("<table><tr>");
foreach ($landsegs as $thisseg) {
   $lsid = $thisseg['elementid'];
   $landseg = $thisseg['landseg'];
   $result = getFlows($lsid, 'for', $debug);
   $records = $result['records'];
   $listobject->queryrecords = $records;
   print("<td>$landseg<br>");
   if ($debug) {
      print("DEBUG: \n<br>" . $result['debug'] . "<br>\n");
   }
   $listobject->showList();
   print("</td>");
   print_r($result['lunames']);
}
print("</tr></table>");

// END land use RUNOFF panel
print("</td></tr></table>");
?>
