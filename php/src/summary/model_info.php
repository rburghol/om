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
if (isset($_GET['luname'])) {
   $luname = $_GET['luname'];
} else {
   $luname = '';
}

function getFlows($elementid,$lus = '', $debug = 0) {
   $unser = unserializeSingleModelObject($elementid);
   $thisobject = $unser['object'];
   $thisobject->debug = $debug;
   $flowdata = $thisobject->getModelOutputData('flowsum',$lus);
   return array('records'=>$flowdata,'lunames'=>$thisobject->lunames,'debug'=>$thisobject->debugstring);
}
$debug = 0;
// BEGIN - get downstream recipient of this objects flow 
// get contaiing "upstream" object
// $usc = getContainingNodeType($elementid, 0, array('custom1'=>'cova_ws_container'), 10, $debug)
$dsc = getContainingNodeType($elementid, 0, array('custom1'=>array('cova_ws_container')), 10, $debug);
print("<a href='./model_info.php?elementid=$dsc&runid=$runid&luname=$luname'>Click here to see parent container ($dsc)</a><hr>");
// END - next downstream segment

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
$cols = count($landsegs);
print("<td colspan=$cols><a href='./model_info.php?elementid=$elementid&runid=$runid&luname=for'>Click here to see landuse runoff</a></tr><tr>");
foreach ($landsegs as $thisseg) {
   $lsid = $thisseg['elementid'];
   $landseg = $thisseg['landseg'];
   print("<td>$landseg<br>");
   if ($debug) {
      print("DEBUG: \n<br>" . $result['debug'] . "<br>\n");
   }
   if ($luname <> '') {
      $result = getFlows($lsid, $luname, $debug);
      $records = $result['records'];
      $listobject->queryrecords = $records;
      $listobject->showList();
      print_r($result['lunames']);
   }
   print("</td>");
}
print("</tr></table>");

// END land use RUNOFF panel

print("</td></tr></table>");
// BEGIN - links to upstream tributaries
$usc = getCOVAUpstream($listobject, $elementid, $debug);
// then get children of the upstream container
$children = getChildComponentCustom1($listobject, $usc, 'cova_ws_container', -1, $debug);
print("<ul");
foreach ($children as $thischild) {
   $cid = $thischild['elementid'];
   $cname = $thischild['elemname'];
   print("<li><a href='./model_info.php?elementid=$cid&runid=$runid&luname=$luname'>Click here to see tributary $cname ($cid)</a>");
}
print("</ul>");
?>
