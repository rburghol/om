<?php


#require('c:/usr/local/home/httpd/devlib/magpierss/rss_fetch.inc');  

include("./config.php");
require("$libpath/magpierss/rss_fetch.inc"); 
error_reporting(E_ALL);
$siteroot = 'http://deq1.bse.vt.edu/wooomm/remote';
$baseurl = "$siteroot/retrieve_objectlist.php";
$modelid = 198;
$scenarioid = 11;

# first get the basic list of linkages, which will tell us all the objects that we need to retrieve
$url = "$baseurl?elementid=$modelid&actiontype=1";
print("<b>Trying: </b>" . $url . "<br>");
define('MAGPIE_CACHE_ON', FALSE);
$rss = fetch_rss($url);

#print_r($rss->items);

$elements = array();
$linklist = $rss->items;
# create an entry for the model itself
$elements[$modelid] = array('old_id'=>$modelid, 'new_id'=>-1);

foreach($linklist as $thislinkage) {
   print("Found Element/Link: ") . print_r($thislinkage, 1) . "<br>";
   if ($thislinkage['linktype'] == 1) {
      # this is a model containment linkage, add it to the retrieval queue
      $elements[$thislinkage['src_id']] = array('old_id'=>$thislinkage['src_id'], 'new_id'=>-1);
   }
}

$baseurl = "$siteroot/retrieve_object.php";

foreach (array_keys($elements) as $thiselement) {
   $elementid = $elements[$thiselement]['old_id'];
   # get the basic object information - actiontype = 1
   $url = "$baseurl?elementid=$elementid&actiontype=1";
   print("<b>Trying: </b>" . $url . "<br>");

   $rss = fetch_rss($url);  

   foreach($rss->items as $thisitem) {
      $elemname = $thisitem['elemname'];
      $firstcomp = $thisitem['firstcomp'];
      $lastcomp = $thisitem['lastcomp'];
      $component_type = $thisitem['component_type'];
      print("<b>Retrieving: </b>" . $elemname . "<br>");
      #print_r($thisitem);
      $url = "$baseurl?elementid=$elementid&actiontype=2";
      print("Getting XML: $url ... ");
      $elemxml = file_get_contents($url);

      print("Storing copy of item $elementid in database. <br>");
      $listobject->querystring = "  insert into scen_model_element (scenarioid, elemname, elem_xml, ownerid, groupid, ";
      $listobject->querystring .= "    operms, gperms, pperms, component_type) ";
      $listobject->querystring .= " values ($scenarioid, '$elemname', '$elemxml', 1, 1, 7, 6, 4, $component_type ) ";
      if ($debug) {
         $innerHTML .= " $listobject->querystring ; <br>";
         print("$innerHTML");
      }
      $listobject->performQuery();

      $listobject->querystring = "  select CURRVAL(pg_get_serial_sequence('scen_model_element', 'elementid')) as elid ";
      if ($debug) {
         $innerHTML = " $listobject->querystring ; <br>";
      }
      $listobject->performQuery();
      $newelementid = $listobject->getRecordValue(1,'elid');
      $elements[$elementid]['new_id'] = $newelementid;

      print("record stored with elementid = $newelementid <br>");

      for ($compid = $firstcomp; $compid <= $lastcomp; $compid++) {
         $url = "$baseurl?elementid=$elementid&actiontype=3&compid=$compid";
         print("Getting Component $compid: <br>");
         $compxml = file_get_contents($url);

         $listobject->querystring = "  update scen_model_element set elemoperators[$compid] = '$compxml' ";
         $listobject->querystring .= " where elementid = $newelementid ";
         if ($debug) {
            $innerHTML = " $listobject->querystring ; <br>";
            print("$innerHTML");
         }
         $listobject->performQuery();
      }
      # get inputs and props
      $url = "$baseurl?elementid=$elementid&actiontype=4";
      print("Getting Component $compid: <br>");
      $ixml = file_get_contents($url);
      $url = "$baseurl?elementid=$elementid&actiontype=5";
      print("Getting Component $compid: <br>");
      $pxml = file_get_contents($url);

      $listobject->querystring = "  update scen_model_element set eleminputs = '$ixml', elemprops = '$pxml' ";
      $listobject->querystring .= " where elementid = $newelementid ";
      if ($debug) {
         $innerHTML = " $listobject->querystring ; <br>";
         print("$innerHTML");
      }
      $listobject->performQuery();
   }
}

foreach ($linklist as $thislink) {
   
   # now, create the linkages
   $src_id = $elements[$thislink['src_id']]['new_id'];
   $dest_id = $elements[$thislink['dest_id']]['new_id'];
   $linktype = $thislink['linktype'];
   $src_prop = $thislink['src_prop'];
   $dest_prop = $thislink['dest_prop'];
   
   $listobject->querystring = "  insert into map_model_linkages (scenarioid, src_id, dest_id, linktype, src_prop, dest_prop ) ";
   $listobject->querystring .= " values ($scenarioid, $src_id, $dest_id, $linktype, '$src_prop', '$dest_prop') ";
   if ($debug) {
      $innerHTML = " $listobject->querystring ; <br>";
      print("$innerHTML");
   }
   $listobject->performQuery();
   
   print("Creating linkage: $listobject->querystring <br>"); 
}
?>