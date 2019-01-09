<?php


include('./config.php');

$segmentid = 6280;
$levels = -1;
$criteria = array();
$actiontype = 1;

if (isset($_GET['segmentid'])) {
   $segmentid = $_GET['segmentid'];
}
if (isset($_GET['levels'])) {
   $levels = $_GET['levels'];
}
if (isset($_GET['actiontype'])) {
   $actiontype = $_GET['actiontype'];
}

if (isset($_GET['watershed'])) {
   $criteria['watershed'] = $_GET['watershed'];
}
if (isset($_GET['minbas'])) {
   $criteria['minbas'] = $_GET['minbas'];
}
if (isset($_GET['majbas'])) {
   $criteria['majbas'] = $_GET['majbas'];
}
if (isset($_GET['rivername'])) {
   $criteria['rivername'] = $_GET['rivername'];
}

//$debug = 1;
$tablename = 'sc_cbp5';
$colname = 'catcode2';

include_once("$libdir/feedcreator/feedcreator.class.php");
$rss = new UniversalFeedCreator();
$rss->useCached();
$rss->title = "CBP Segments";
$rss->link = "http://test.com/news";
$rss->syndicationURL = "http://www.soulswimmer.net/".$PHP_SELF;
$unserializer = new XML_Unserializer($options);

switch ($actiontype) {
   case 1:
      $data = getCBPSegList($listobject, $tablename, $colname, $segmentid, $debug,$levels);
      $segments = $data['segments'];
      $segnames = $data['segnames'];
      //print_r($data);
      $item = new FeedItem();
      $item->title = "Segments terminating in watershed outlet $segmentid";
      $options = array("complexType" => "object");
      # unserialize the property list
      $proplist['tributaries'] = join(',', $segments);
      $proplist['trib_names'] = join(',', $segnames);
      $proplist['info'] = $data['info'];
      $proplist['description'] = $proplist['tributaries'];
      #print_r($proplist);
      $item->additionalElements = $proplist;
      $rss->addItem($item);
      $xml = $rss->createFeed("2.0");
   break;
   
   case 2:
      $data = getCBPTerminalNode($listobject, $tablename, $colname, $criteria, $debug);
      $segments = $data['segments'];
      $segnames = $data['segnames'];
      $destinations = $data['destinations'];
      //print_r($data);
      $item = new FeedItem();
      $item->title = "Terminal Nodes in " . print_r($criteria,1);
      $options = array("complexType" => "object");
      # unserialize the property list
      $proplist['terminal_nodes'] = join(",", $segments);
      $proplist['segnames'] = join(",", $segnames);
      $proplist['destinations'] = join(",", $destinations);
      $proplist['description'] = $proplist['terminal_nodes'];
      $proplist['info'] = $data['info'];
      #print_r($proplist);
      $item->additionalElements = $proplist;
      $rss->addItem($item);
      $xml = $rss->createFeed("2.0");
   break;
   
   case 3:
   # get a branch
      $data = getCBPBranch($listobject, $tablename, $colname, $criteria, $debug);
      $segments = $data['segments'];
      //print_r($data);
      $item = new FeedItem();
      $item->title = "Branches in " . print_r($criteria,1);
      $item->title .= "<br>Terminal Nodes: " . join(",", $data['terminal_nodes']);
      $options = array("complexType" => "object");
      # unserialize the property list
      $proplist['branches'] = join(",", $segments);
      $proplist['info'] = $data['info'];
      $proplist['description'] = $proplist['branches'];
      #print_r($proplist);
      $item->additionalElements = $proplist;
      $rss->addItem($item);
      $xml = $rss->createFeed("2.0");
   break;
   
   case 4:
   # get immediate tributaries to a branch
      $data = getCBPBranchTribs($listobject, $tablename, $colname, $criteria, $debug);
      $segments = $data['segments'];
      //print_r($data);
      $item = new FeedItem();
      $item->title = "Branches in " . print_r($criteria,1);
      $item->title .= "<br>Terminal Nodes: " . join(",", $data['terminal_nodes']);
      $options = array("complexType" => "object");
      # unserialize the property list
      $proplist['branches'] = join(",", $segments);
      $proplist['info'] = $data['info'];
      $proplist['description'] = $proplist['branches'];
      #print_r($proplist);
      $item->additionalElements = $proplist;
      $rss->addItem($item);
      $xml = $rss->createFeed("2.0");
   break;

}


print("$xml");

?>
