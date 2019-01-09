<?php

include_once('../xajax_modeling.element.php');
//include('./config.php');
error_reporting(E_ALL);

$actiontype = 4; # 1 - data list, 2 - get location list, 3 - get location data, 4 - get data
if (isset($_GET['actiontype'])) {
   $actiontype = $_GET['actiontype'];
}

$params = '';
if (isset($_GET['params'])) {
   $params = $_GET['params'];
}
$startdate = '';
if (isset($_GET['startdate'])) {
   $startdate = $_GET['startdate'];
}
$enddate = '';
if (isset($_GET['enddate'])) {
   $enddate = $_GET['enddate'];
}
$timestep = '';
if (isset($_GET['timestep'])) {
   $timestep = $_GET['timestep'];
}


error_log("Inputs:" . print_r($_GET,1));
if (isset($_GET['elementid'])) {
   $elementid = $_GET['elementid'];
   $thisobresult = unSerializeSingleModelObject($elementid);
   $thisobject = $thisobresult['object'];
} else {
   $elementid = -1;
}


$debug_str = '';
require_once("$libdir/feedcreator/feedcreator.class.php");
// make sure the cache is cleared
// shouldn't do this, as I think this is actually used by magpie, NOT feedcreator
//shell_exec("rm ../rsscache/* -f");
$rss = new UniversalFeedCreator();
//$rss->useCached();
$rss->title = "Test news";
$rss->link = "http://test.com/news";
$rss->syndicationURL = "http://deq1.bse.vt.edu/".$PHP_SELF;
if (is_object($thisobject)) {
   switch ($actiontype) {
      case 'getVariables':
      // 
      $props = $thisobject->getPublicVars();
      foreach ($props as $thisprop) {
         if (isset($thisobject->dbcolumntypes[$thisprop])) {
            $coltypes[] = $thisobject->dbcolumntypes[$thisprop];
         } else {
            $coltypes[] = 'undefined';
         }
      }
      $item = new FeedItem();
      $item->title = $data['elemname'];
      $options = array("complexType" => "object");
      # unserialize the property list
      // don't return this to the WOOOMM objects, since it breaks the feed somehow
      //$proplist['debug_str'] = $debug_str;
      $proplist['data_columns'] = join(",", $props);
      $proplist['data_column_types'] = join(",", $coltypes);
      $item->additionalElements = $proplist;
      $rss->addItem($item);
      
      break;

      case 'getValues':
      # get model output data
      $props = $thisobject->getPublicVars();
      foreach ($props as $thisprop) {
         if (isset($thisobject->dbcolumntypes[$thisprop])) {
            $coltypes[] = $thisobject->dbcolumntypes[$thisprop];
         }
      }

      foreach($cbp_listobject->queryrecords as $data) {
         $j++;
         $item = new FeedItem();
         $proplist = array();
         $proplist['thisdate'] = $data['thisdate'];
         reset($cols);
         foreach ($cols as $thiscol) {
            if (strlen($data[$thiscol]) == 0) {
               $data[$thiscol] = 0.0;
            }
            $proplist["$thiscol"] = $data[$thiscol];
            if ($j == 1) {
               //error_log("Adding $thiscol ");
            }
         }
         if ($j == 1) {
            error_log("Final column names " . print_r(array_keys( $proplist),1) . " <br>\n ");
         }
         /*
         $proplist['elementid'] = $data['elementid'];
         $proplist['elemname'] = $data['elemname'];
         $proplist['component_type'] = $data['component_type'];
         $proplist['firstcomp'] = $astart;
         $proplist['lastcomp'] = $aend;
         #print_r($proplist);
         */
         $item->additionalElements = $proplist;
         $rss->addItem($item);
         //if ($j > 5) {
         //   break;
         //}
      }
      error_log("CBP Time series Land Data Added $j records<br>");

      
      $xlen = strlen($xml);
      error_log("Time series Feed Created with length $xlen<br>");

      break;

   }
   $xml = $rss->createFeed("2.0");
}


print("$xml");

?>
