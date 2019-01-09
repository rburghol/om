<?php


#require('c:/usr/local/home/httpd/devlib/magpierss/rss_fetch.inc');  

include("./config.php");
require("$libpath/magpierss/rss_fetch.inc"); 

function retrieveObjectList($elementid) {
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
   
   return $xml;
}









//error_reporting(E_ALL);
$siteroot = 'http://10.173.211.77/html/whodev/remote_tools';
$baseurl = "$siteroot/retrieve_objectlist.phpc";
$modelid = 656;
$scenarioid = 10;

# first get the basic list of linkages, which will tell us all the objects that we need to retrieve
$url = "$baseurl?elementid=$modelid&actiontype=1";
print("<b>Trying: </b>" . $url . "<br>");
define('MAGPIE_CACHE_ON', FALSE);
$rss = fetch_rss($url);

//print_r($rss->items);

$elements = array();
$linklist = $rss->items;
# create an entry for the model itself
$elements[$modelid] = array('old_id'=>$modelid, 'new_id'=>-1);

$links = $outdir . "/export_links.$elementid" . ".csv";
putDelimitedFile($links,$linklist,$thisdelim=',',1,'unix');
print("Storing links in $links<br>");


foreach($linklist as $thislinkage) {
   print("Found Element/Link: ") . print_r($thislinkage, 1) . "<br>";
   if ($thislinkage['linktype'] == 1) {
      # this is a model containment linkage, add it to the retrieval queue
      $elements[$thislinkage['src_id']] = array('old_id'=>$thislinkage['src_id'], 'new_id'=>-1);
   }
} 

$manifest = $outdir . "/export_manifest.$elementid" . ".csv";
print("Storing manifest in $manifest<br>");
$fp = fopen($manifest,'w');
fwrite($fp, "elementid,xmlfile,compfile,inputfile,propfile");
fclose($fp);

$baseurl = "$siteroot/retrieve_object.phpc";

foreach (array_keys($elements) as $thiselement) {
   $elementid = $elements[$thiselement]['old_id'];
   
   # get the basic object information - actiontype = 1
   $url = "$baseurl?elementid=$elementid&actiontype=1";
   print("<b>Trying: </b>" . $url . "<br>");

   $rss = fetch_rss($url); 

   foreach($rss->items as $thisitem) {
      $elemname = $thisitem['elemname'];
      $elid = $thisitem['elementid'];
      $firstcomp = $thisitem['firstcomp'];
      $lastcomp = $thisitem['lastcomp'];
      $component_type = $thisitem['component_type'];
      
      $manifest_string = $elid;
      
      print("<b>Retrieving: </b>" . $elemname . "<br>");
      #print_r($thisitem);
      $url = "$baseurl?elementid=$elementid&actiontype=2";
      print("Getting XML: $url ... ");
      $elemxml = file_get_contents($url);

      print("Storing copy of item $elementid in file. <br>");
      $objectfile = $outdir . "/" . "export_element.$elid" . ".xml";
      print("Storing objectfile in $objectfile<br>");
      $fp = fopen($objectfile,'w');
      fwrite($fp, $elemxml);
      fclose($fp);
      
      $manifest_string .= ",export_element.$elid" . ".xml";
      
      # now get components
      $compfile = $outdir . "/" . "export_comps.$elid" . ".xml";
      print("Storing compfile in $compfile<br>");
      $fp = fopen($objectfile,'w');

      print("record stored with elementid = $newelementid <br>");

      for ($compid = $firstcomp; $compid <= $lastcomp; $compid++) {
         $url = "$baseurl?elementid=$elementid&actiontype=3&compid=$compid";
         print("Getting Component $compid: <br>");
         $compxml = file_get_contents($url);
         fwrite($fp, $compxml);
      }
      fclose($fp);
      $manifest_string .= ",export_comps.$elid" . ".xml";
      
      
      # get inputs and props
      $inputfile = $outdir . "/" . "export_inputs.$elid" . ".xml";
      print("Storing inputfile in $inputfile<br>");
      $fp = fopen($inputfile,'w');
      $url = "$baseurl?elementid=$elementid&actiontype=4";
      print("Getting Component $compid: <br>");
      $ixml = file_get_contents($url);
      fwrite($fp, $ixml);
      fclose($fp);
      $manifest_string .= ",export_inputs.$elid" . ".xml";
      
      # props
      $propsfile = $outdir . "/" . "export_props.$elid" . ".xml";
      print("Storing propsfile in $propsfile<br>");
      $fp = fopen($propsfile,'w');
      $url = "$baseurl?elementid=$elementid&actiontype=5";
      print("Getting Component $compid: <br>");
      $pxml = file_get_contents($url);
      fwrite($fp, $pxml);
      fclose($fp);
      $manifest_string .= ",export_props.$elid" . ".xml";
   }
}

?>