<?php


include('./config.php');
include('./lib_cova_model_data.php');
$scenarioid =37;

$actiontype = 1; # 1 - data list, 2 - get location list, 3 - get location data, 4 - get data
if (isset($_GET['actiontype'])) {
   $actiontype = $_GET['actiontype'];
}
$datatype = 'cova_withdrawal';
if (isset($_GET['datatype'])) {
   $datatype = $_GET['datatype'];
}
$id1 = '';
if (isset($_GET['id1'])) {
   $id1 = $_GET['id1'];
}
$authcode = '';
if (isset($_GET['authcode'])) {
   $authcode = $_GET['authcode'];
}
$id2 = '';
if (isset($_GET['id2'])) {
   $id2 = $_GET['id2'];
}
$the_geom = '';
if (isset($_GET['the_geom'])) {
   $the_geom = $_GET['the_geom'];
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
switch ($authcode) {
   case '':
   $perms = 'public';
   break;
   
   case 'vwpmodelers':
   $perms = 'deq';
   break;
   
   case 'hydrologics_roanoke':
   $perms = 'partner';
   break;
   
   case 'rti_roanoke':
   $perms = 'group';
   break;
   
   default:
   $perms = 'public';
   break;
}

error_log("Inputs:" . print_r($_GET,1));

require_once("$libpath/feedcreator/feedcreator.class.php");
error_log("Loaded FeedCreator");
$rss = new UniversalFeedCreator();
$rss->title = "Surface Water Withdrawal";
$rss->link = "http://deq1.bse.vt.edu/".$PHP_SELF;
$rss->syndicationURL = "http://deq1.bse.vt.edu/".$PHP_SELF;
error_log("Handling actiontype = $actiontype");
switch ($actiontype) {
   case 0:
   error_reporting(E_ALL);
      $vwuds_listobject->querystring = "  select typeabbrev, typename from waterusetype order by typeabbrev ";
      error_log("Querying: " . $vwuds_listobject->querystring);
      $vwuds_listobject->performQuery();
      error_log("Result: " . $vwuds_listobject->error);
      
      $recdata = $vwuds_listobject->queryrecords;
      error_log("Retrieved " . count($recdata) . " records <br>\n");
      error_reporting(E_ALL);
      foreach ($recdata as $thistype) {
         $tl[$thistype['typeabbrev']] = $thistype['typeabbrev'] . ' = ' . $thistype['typename'];
      }
      $item = new FeedItem();
      $item->additionalElements = $tl;
      $rss->addItem($item);
      
      $xml = $rss->createFeed("2.0");
      
   break;
   
   case 1:
   // Basic Info: Current Annual, Historic Monthly use Percentages
      $listobject->querystring = "  select a.elementid from scen_model_element as a, ";
      $listobject->querystring .= " scen_model_element as b ";
      $listobject->querystring .= " where a.scenarioid = $scenarioid ";
      $listobject->querystring .= "  and a.custom1 = '$datatype' ";
      $listobject->querystring .= "  and b.scenarioid = $scenarioid ";
      if (strlen($the_geom) > 0) {
         $listobject->querystring .= "    and within(point_geom, geomFromText('$the_geom',4326)) ";
      }
      // restrict to Appomattox at Farmville for now
      switch ($authcode) {
         case '':
         $listobject->querystring .= "    and b.elementid = 210539 ";
         $listobject->querystring .= "  and within (a.point_geom, b.poly_geom) ";
         break;
         
         case 'hydrologics_roanoke':
         case 'rti_roanoke':
         $listobject->querystring .= "    and b.custom1 = 'cova_ws_container'  ";
         $listobject->querystring .= "    and b.custom2 like 'O%'  ";
         $listobject->querystring .= "  and within (a.point_geom, b.poly_geom) ";
         break;
         
         default:
         $listobject->querystring .= "    and b.elementid = 210539 ";
         $listobject->querystring .= "  and within (a.point_geom, b.poly_geom) ";
         break;
      }
      $listobject->querystring .= "  group by a.elementid ";
      //$listobject->querystring .= "  limit 50 ;";
 
 /*
      $listobject->querystring = "  select elementid ";
      $listobject->querystring .= " from scen_model_element ";
      $listobject->querystring .= " WHERE scenarioid = $scenarioid and custom1 = '$datatype' ";
      if (strlen($the_geom) > 0) {
         $listobject->querystring .= "    and within(point_geom, geomFromText('$the_geom',4326)) ";
      }
      // restrict to Appomattox at Farmville for now
      switch ($authcode) {
         case '':
         $listobject->querystring .= "    and within(point_geom, (select poly_geom from scen_model_element where elementid = 210539)) ";
         break;
         
         case 'hydrologics_roanoke':
         $listobject->querystring .= "    and within(point_geom, (select st_union(poly_geom) from scen_model_element where scenarioid = $scenarioid and custom1 = 'cova_ws_container' and custom2 like 'O%')) ";
         break;
         
         default:
         $listobject->querystring .= "    and within(point_geom, (select poly_geom from scen_model_element where elementid = 210539)) ";
         break;
      }
*/
      error_log($listobject->querystring);
      //$rss->link = $listobject->querystring;
      $listobject->performQuery();
   
      $j = 0;
      $recdata = $listobject->queryrecords;
      error_log("Retrieved " . count($recdata) . " records <br>\n");
      error_reporting(E_ALL);
      foreach($recdata as $data) {
         $j++;
         $item = new FeedItem();
         switch ($datatype) {
            case 'cova_withdrawal':
            $proplist = getWithdrawalElementInfo($data['elementid']);
            break;
            
            case 'cova_pointsource':
            $proplist = getPointsourceElementInfo($data['elementid']);
            break;
         }
         $item->link = $proplist['historic_data_url'];
         unset($proplist['historic_data_url']);
         if ($perms == 'public') {
            unset($proplist['waterusetype']);
            unset($proplist['elemname']);
         }
         if ($perms == 'group') {
            unset($proplist['elemname']);
         }
         $item->additionalElements = $proplist;
         $rss->addItem($item);
      }
      
      $xml = $rss->createFeed("2.0");
   break;

   case 2:
   // Historic Monthly/Annual use amounts
   
   break;

}


print("$xml");

?>
