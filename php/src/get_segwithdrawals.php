<?php
$userid = 1;
$projectid = 3; // to point us to the proper output directory
include_once('xajax_modeling.element.php');
include_once('lib_batchmodel.php');

$scenarioid = 28;
$segs = array(33245, 'PS2_6660_6490', 'PS0_6160_6161', 'PU2_4220_3900');
// get the ICPRB withdrawal list for each watershed in question

$segcount = 0;
   
$ann_fields = array('JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER', 'ANNUAL', 'ANNUAL/365');
$quotefields = array('mpid', 'USERID', 'ownname','facility','system', 'SOURCE');

foreach ($segs as $thisseg) {
   print("<b>Segment $thisseg </b><br>");
   print(intval($thisseg) . " <> $thisseg <br>");
   if (strlen(intval($thisseg)) <> strlen($thisseg)) {
      $parentid = getElementID($listobject, $scenarioid, $thisseg);
   } else {
      $parentid = $thisseg;
      $thisseg = getElementName($listobject, $parentid);
   }
   
   $wds = getVWUDSWithdrawals($vwudsdb, $listobject, $parentid, 0);
   $outarr = array();
   foreach ($wds as $reckey=>$record) {
      //print_r($record);
      $mpid = $record['mpid'];
      $action = $record['mpaction'];
      $year = 2005;
      $annual = getVWUDSAnnualData($vwudsdb, $mpid, $action, $year, $debug);
      //print("Annual: " . print_r($annual,1) . " <br>\n");
      foreach ($ann_fields as $thiskey) {
         $thisvalue = $annual[$thiskey];
         $wds[$reckey][$thiskey] = $thisvalue;
      }
      $wds[$reckey]['river_segment'] = $thisseg;
      foreach ($quotefields as $thisfield) {
         $wds[$reckey][$thisfield] = '"' . $wds[$reckey][$thisfield] . '"';
      }
      $outarr[] = array_values($wds[$reckey]);
   }
   $listobject->queryrecords = $wds;
   $listobject->showList();
   $segcount++;
   
   if ($segcount == 1) {
      // configure output file
      $ofile = "$outdir/vwuds_withdrawal2005_v01.csv";
      $colnames[] = array_keys($wds[0]);
      print("Output going to $ofile <br>\n");
      print_r($colnames);
      putDelimitedFile("$ofile",$colnames,',',1,'unix');
   }
   print_r($outarr);
   putDelimitedFile("$ofile",$outarr,',',0,'unix');
   
   print("<hr>");

}
$fileurl = "$outurl/vwuds_withdrawal2005_v01.csv";
print("<a href='$fileurl'>Download $fileurl</a>");
?>