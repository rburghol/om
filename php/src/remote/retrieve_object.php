<?php


include('./config.php');

$elementid = 378;

if (isset($_GET['elementid'])) {
   $elementid = $_GET['elementid'];
}

$actiontype = 1; # 1 - get object props, 2 - get object xml, 3 - get sub-component
if (isset($_GET['actiontype'])) {
   $actiontype = $_GET['actiontype'];
}

$compid = -1;
if (isset($_GET['compid'])) {
   $compid = $_GET['compid'];
}

include("$libdir/feedcreator/feedcreator.class.php");
$rss = new UniversalFeedCreator();
$rss->useCached();
$rss->title = "Test news";
$rss->link = "http://test.com/news";
$rss->syndicationURL = "http://deq1.bse.vt.edu/".$PHP_SELF;
$unserializer = new XML_Unserializer($options);

switch ($actiontype) {
   case 1:
      $listobject->querystring = "  select elementid, elemname, component_type, ";
      $listobject->querystring .= "    array_dims(elemoperators) as adims ";
      $listobject->querystring .= " from scen_model_element ";
      $listobject->querystring .= " where elementid = $elementid ";
      $rss->description .= $listobject->querystring;
      error_log($listobject->querystring);
      $listobject->performQuery();
      $data = $listobject->queryrecords[0];
      $dimstr = str_replace(']','',str_replace('[','',$data['adims']));
      list($astart, $aend) = split(':', $dimstr);
      $item = new FeedItem();
      $item->title = $data['elemname'];
      $options = array("complexType" => "object");
      # unserialize the property list
      $proplist['elementid'] = $data['elementid'];
      $proplist['elemname'] = $data['elemname'];
      $proplist['component_type'] = $data['component_type'];
      $proplist['firstcomp'] = $astart;
      $proplist['lastcomp'] = $aend;
      #print_r($proplist);
      $item->additionalElements = $proplist;
      $rss->addItem($item);
      $xml = $rss->createFeed("2.0");
   break;

   case 2:
      $listobject->querystring = "  select elem_xml ";
      $listobject->querystring .= " from scen_model_element ";
      $listobject->querystring .= " where elementid = $elementid ";
      $rss->description .= $listobject->querystring;
      $listobject->performQuery();
      $data = $listobject->queryrecords[0];
      $xml = $data['elem_xml'];
   break;

   case 3:
      # get sub-components of this object
      # handle sub-components on this object
      $opxmls = array();
      if ($compid > 0) {
         $listobject->querystring = "  select elemoperators[$compid] ";
         $listobject->querystring .= " from scen_model_element ";
         $listobject->querystring .= " where elementid = $elementid"; 
         $listobject->performQuery();
         $xml = $listobject->getRecordValue(1,'elemoperators');
      }
   break;

   case 4:
      # get inputs xml
      $listobject->querystring = "  select eleminputs ";
      $listobject->querystring .= " from scen_model_element ";
      $listobject->querystring .= " where elementid = $elementid ";
      $listobject->performQuery();
      $xml = $listobject->getRecordValue(1,'eleminputs');
   break;

   case 5:
      # get properties xml
      $listobject->querystring = "  select elemprops ";
      $listobject->querystring .= " from scen_model_element ";
      $listobject->querystring .= " where elementid = $elementid ";
      $listobject->performQuery();
      $xml = $listobject->getRecordValue(1,'eleminputs');
   break;
}


print("$xml");

?>
