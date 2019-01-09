<?php


include('./config.php');

$elementid = '-1';

if (isset($_GET['elementid'])) {
   $elementid = $_GET['elementid'];
}

include("$libdir/feedcreator/feedcreator.class.php");
$rss = new UniversalFeedCreator();
#$rss->useCached();
define('MAGPIE_CACHE_ON', FALSE);
$rss->title = "Test news";
$rss->link = "http://test.com/news";
$rss->syndicationURL = "http://deq1.bse.vt.edu/".$PHP_SELF;
$unserializer = new XML_Unserializer($options);

# get list of model children of this element
$listobject->querystring = "  select projectid, scenarioid, linktype, src_id, dest_id, src_prop, dest_prop ";
$listobject->querystring .= " from map_model_linkages ";
$listobject->querystring .= " where ( dest_id in ($elementid) ) ";
$listobject->querystring .= " OR ( dest_id in ( ";
$listobject->querystring .= " select src_id ";
$listobject->querystring .= " from map_model_linkages ";
$listobject->querystring .= " where dest_id in ($elementid)  ";
$listobject->querystring .= " and linktype = 1) ) ";
$listobject->querystring .= " group by projectid, scenarioid, linktype, src_id, dest_id, src_prop, dest_prop ";
#print("$listobject->querystring ;<br>");
error_log($listobject->querystring);
$listobject->performQuery();
$rss->description .= $listobject->querystring;
$eldel = '';
$i = 0;
foreach ($listobject->queryrecords as $thisrec) {
   $item = new FeedItem();
   $item->additionalElements = $thisrec;
   $item->title = "$i";
   $rss->addItem($item);
   $i++;
}

$xml = $rss->createFeed("2.0");

print("$xml");

?>
