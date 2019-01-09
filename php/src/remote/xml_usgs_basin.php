<?php


include('./config.php');
error_reporting(E_ERROR);

$actiontype = 1; # 1 - get shape (only function currently)
if (isset($_GET['actiontype'])) {
   $actiontype = $_GET['actiontype'];
}

$gage_id = '';
if (isset($_GET['gage_id'])) {
   $gage_id = $_GET['gage_id'];
}

//error_log("Inputs:" . print_r($_GET,1));

$usgsdb = -1;
$usgs_host = 'deq3.bse.vt.edu';
$usgs_port = 5432;
$usgs_dbname = 'va_hydro';
$usgs_username = 'usgs_ro';
$usgs_password = '@ustin_CL';
# initilize cbp data connection
$usgs_connstring = "host=$usgs_host dbname=$usgs_dbname user=$usgs_username password=$usgs_password ";
//print($cbp_connstring);
$usgs_dbconn = pg_connect($usgs_connstring, PGSQL_CONNECT_FORCE_NEW);
$usgs_listobject = new pgsql_QueryObject;
$usgs_listobject->ogis_compliant = 1;
$usgs_listobject->connstring = $usgs_connstring;
$usgs_listobject->dbconn = $usgs_dbconn;
$usgs_listobject->adminsetuparray = $adminsetuparray;


$debug_str = '';
require_once("$libdir/feedcreator/feedcreator.class.php");
// make sure the cache is cleared
// shouldn't do this, as I think this is actually used by magpie, NOT feedcreator
//shell_exec("rm ../rsscache/* -f");
$rss = new UniversalFeedCreator();
//$rss->useCached();
$rss->title = "$gage_id";
$rss->link = "http://test.com/news";
$rss->syndicationURL = "http://deq1.bse.vt.edu/".$PHP_SELF;
switch ($actiontype) {
   case 1:
      // 
      // iterate through each land use name and add a join
      $usgs_listobject->querystring = "  select river_basi, station_nu, drainage_a, asText(the_geom) as the_geom  ";
      $usgs_listobject->querystring .= " from usgs_drainage_dd ";
      $usgs_listobject->querystring .= " where station_nu = '$gage_id' ";
      if ($debug) {
         $debug_str .= "Getting Basin Info " . $usgs_listobject->querystring . " ; <br>";
      }
      //error_log("$debug_str<br>");
      $usgs_listobject->performQuery();
      if (count($usgs_listobject->queryrecords) > 0) {
         $data = $usgs_listobject->queryrecords[0];
         $item = new FeedItem();
         $item->title = $data['river_basi'];
         $options = array("complexType" => "object");
         # unserialize the property list
         // don't return this to the WOOOMM objects, since it breaks the feed somehow
         //$proplist['debug_str'] = $debug_str;
         $proplist['gage_id'] = $data['station_nu'];
         $proplist['drainage_area'] = $data['drainage_a'];
         $proplist['the_geom'] = $data['the_geom'];
         $geom = $data['the_geom'];
         
         $item->additionalElements = $proplist;
         $rss->addItem($item);
      }
      $xml = $rss->createFeed("2.0");
   break;

}

print("$xml");
// for now, we are hacking this to avoid XML trouble - WTF is going on with this?
//print($geom);

?>
