<?php


#require('c:/usr/local/home/httpd/devlib/magpierss/rss_fetch.inc');  

include("./config.php");
require_once("$libpath/magpierss/rss_fetch.inc"); 
error_reporting(E_ALL);
$siteroot = 'http://deq1.bse.vt.edu/wooommdev/remote';
$baseurl = "$siteroot/rss_cbpdata.php";
$id1 = 'land';
$id2 = 'A51015';
$scenarioid = 2;
$startdate = '1985-01-01';
$enddate = '1985-01-31';

if (isset($_GET['id1'])) {
   $id1 = $_GET['id1'];
}
if (isset($_GET['id2'])) {
   $id2 = $_GET['id2'];
}
if (isset($_GET['id3'])) {
   $id3 = $_GET['id3'];
}
if (isset($_GET['startdate'])) {
   $startdate = $_GET['startdate'];
}
if (isset($_GET['enddate'])) {
   $enddate = $_GET['enddate'];
}
# first get the basic list of linkages, which will tell us all the objects that we need to retrieve
$url = "$baseurl?id1=$id1&id2=$id2&scenarioid=$scenarioid&startdate=$startdate&enddate=$enddate";
print("<b>Trying: </b>" . $url . "<br>");
define('MAGPIE_CACHE_ON', FALSE);
$rss = fetch_rss($url);

#print_r($rss->items);

$elements = array();
$linklist = $rss->items;

foreach($linklist as $thislinkage) {
   print("Found Element/Link: " . print_r($thislinkage, 1) . "<br>") ;
}

?>