<?php
//$debug = 1;
include_once("./config.php");

//include_once('/var/www/html/wooommdev/xajax_modeling.element.php');
$hash = '';
$elementid = -1;
$username = '';
$vars = array('hash','username','elementid', 'runid','dataname', 'reporting_frequency', 'temporal_res', 'dataval', 'datatext', 'starttime', 'endtime');
$notnull = array('hash','username','elementid', 'runid','dataname', 'dataval', 'starttime', 'endtime');
$vals = array();
# http://deq2.bse.vt.edu/om/remote/setModelData.php?hash=dd9ad2d87ef59a38674db95b7391a152&username=robertwb&elementid=234560&runid=10&dataname=7q10&reporting_frequency=single&dataval=10.4&starttime=1984-01-01&endtime=2005-12-31 

foreach ($vars as $key => $varname) {
   if (isset($_GET[$varname])) {
      $vals[$varname] = $_GET[$varname];
   } else {
      if (isset($argv[$key + 1])) {
          $vals[$varname] = $argv[$key + 1];
      }
   }
}
foreach ($notnull as $var) {
   if ( ($vals[$var] == NULL) or ($vals[$var] == '') ) {
      print("You must enter non-null values for: " . print_r($notnull,1) . " <br>\n");
      print("You submitted: " . print_r($vals,1) . " <br>\n");
      die;
   }
}

$dtc = new runVariableStorageObject;
$dtc->name = $vals['dataname'];
if ($vals['hash'] == 'test') {
   $vals['elementid'] = -99999;
}
foreach ($vals as $thiskey => $thisval) {
   print("Setting $thiskey, $thisval <br>\n");
   $dtc->setProp($thiskey, $thisval);
}
$dtc->debug = 0;
$dtc->master_db = $listobject;

if ($vals['hash'] == 'test') {
   print("Testing run - no authentication required <br>\n");
   print("You submitted: " . print_r($vals,1) . " <br>\n");
   $dtc->clearAllValues();
}
$dtc->clearValues();
$dtc->stashValue($vals['dataval'], $vals['datatext'], $vals['starttime'], $vals['endtime'], $vals['endtime']);

?>