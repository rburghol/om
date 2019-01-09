<?php
$noajax = 1;
include('./config.php');

$scid = 37;
$runid = 0;

if (isset($argv[1])) {
   $elementid = $argv[1];
} else {
   $elementid = -1;
}
if (isset($_GET['elementid'])) {
   $elementid = $_GET['elementid'];
}
if (isset($_GET['scenarioid'])) {
   $scid = $_GET['scenarioid'];
}
if (isset($_GET['runid'])) {
   $runid = $_GET['runid'];
}


switch ($scid) {
   
   case 28:
      // get all watersheds, ordered by minbasin
      $listobject->querystring = "  select ";
      $listobject->querystring .= "    CASE ";
      $listobject->querystring .= "       WHEN ( (shed_merge is null) or (shed_merge = '')) THEN cbp_segmentid ";
      $listobject->querystring .= "       ELSE shed_merge ";
      $listobject->querystring .= "    END as riverseg, substring(cbp_segmentid,1,2) as minbas, shed_merge as rivername from icprb_watersheds order by substring(cbp_segmentid,1,1), minbas, rivername ";
   break;
   
   default:
      // get all watersheds, ordered by minbasin
      $listobject->querystring = "select riverseg, minbas, rivername from sc_cbp53 where riverseg in (select custom2 from scen_model_element where scenarioid = $scid and custom1 = 'cova_ws_container') group by riverseg, minbas, rivername order by substring(riverseg,1,1), minbas, rivername ";
   break;
}

print("$listobject->querystring ; <br>");

$listobject->performQuery();

$recs = $listobject->queryrecords;

$minbas = '';

foreach ($recs as $thisrec) {
   $thisbasin = $thisrec['minbas'];
   $riverseg = $thisrec['riverseg'];
   $rivername = $thisrec['rivername'];
   if ($minbas == '') {
      // do nothing
      print("<b>$thisbasin</b><ul>");
      $minbas = $thisbasin;
   } else {
      if ($thisbasin <> $minbas) {
         $minbas = $thisbasin;
         print("</ul>");
         print("<b>$thisbasin</b><ul>");
      }
   }
   switch ($scid) {

      case 28:
         $elid = getElementID($listobject, $scid, $riverseg);
      break;

      default:
         $elid = getCOVACBPContainer($listobject, $scid, $riverseg);
      break;
   }
   
   $run_info = checkVerified($listobject, $elid, $runid);
   $verified = $run_info['numsum'];
   $starttime = $run_info['starttime'];
   $endtime = $run_info['endtime'];
   print("<li><a href='./model_info.php?scenarioid=$scid&elementid=$elid&runid=$runid'>$riverseg - $rivername</a>");
   switch ($verified) {
      case 0:
         print("<font color=red> Un-Verified</font>");
      break;
      
      default:
         print("<font color=green> Verified ($starttime to $endtime)</font>");
      break;
   }
   $wds = getCOVAWithdrawals($listobject, $elid);
   print(" - " . count($wds) . " withdrawals ");
   $ps = getCOVADischarges($listobject, $scid, $elid);
   print(" - " . count($ps) . " discharges ");
   print("</li>");
}
print("</ul>");

?>
