<?php


#require('c:/usr/local/home/httpd/devlib/magpierss/rss_fetch.inc');  

include("./config.php");
require_once("$libpath/magpierss/rss_fetch.inc"); 
error_reporting(E_ALL);

if ( (count($argv) < 3) and (!isset($_GET['elementid'])) ) {
   print("You must submit an elementid with this request \n");
   die;
}

$modelid = $argv[1];
$scenarioid = $argv[2];


# need:
#    link list
#    manifest (lists all files for objects in link list)
# later, we should ship this in a tar file, but for now, it should be fine as a loose collection of files
$datadir = './data/';
$linkfile = './data/export_links.' . $modelid . '.csv';
$manifestfile = './data/export_manifest.' . $modelid . '.csv';

$elements = array();
$linklist = readDelimitedFile($linkfile,',',1);
$manifest = readDelimitedFile($manifestfile,',',1);

$baseurl = "$siteroot/retrieve_object.php";
$element_info = array();

# make sure tahtt there are no elementid conflicts
$listobject->querystring = "  select max(elementid) as maxel from scen_model_element ";
$listobject->performQuery();
$maxel = intval($listobject->getRecordValue(1,'maxel')) + 1;
$listobject->querystring = " select setval('scen_model_element_elementid_seq', $maxel) ";
print("$listobject->querystring ; <br>");
$listobject->performQuery();

foreach ($manifest as $thiselement) {
   $elementid = $thiselement['elementid'];
   $element_info[$elementid] = array();
   $xmlfile = $thiselement['xmlfile'];
   $objectclass = $thiselement['objectclass'];
   $compfile = $thiselement['compfile'];
   $inputfile = $thiselement['inputfile'];
   $propfile = $thiselement['propfile'];
   $elemname = $thiselement['elemname'];
   $component_type = $thiselement['component_type'];
   $obxml = file_get_contents($datadir . $xmlfile);   

   print("Storing copy of item $elementid in database. <br>");
   $listobject->querystring = "  insert into scen_model_element (scenarioid, elemname, objectclass, elem_xml, ownerid, groupid, ";
   $listobject->querystring .= "    operms, gperms, pperms) ";
   $listobject->querystring .= " values ($scenarioid, '$elemname', '$objectclass', '$obxml', 1, 1, 7, 6, 4 ) ";
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
   if ($lastval == $newelementid) {
      print("Problem inserting $scenarioid, '$elemname', '$objectclass', , $component_type <br>");
   }
   $lastval = $newelementid;
   $element_info[$elementid]['new_id'] = $newelementid;

   print("record stored with elementid = $newelementid <br>");

   $compxml = file_get_contents($datadir . $compfile);
   $listobject->querystring = "  update scen_model_element set elemoperators = '$compxml' ";
   $listobject->querystring .= " where elementid = $newelementid ";
   if ($debug) {
      $innerHTML = " $listobject->querystring ; <br>";
      print("$innerHTML");
   }
   $listobject->performQuery();

   # get inputs and props
   $ixml = file_get_contents($datadir . $inputfile);
   $pxml = file_get_contents($datadir . $propfile);

   $listobject->querystring = "  update scen_model_element set eleminputs = '$ixml', elemprops = '$pxml' ";
   $listobject->querystring .= " where elementid = $newelementid ";
   if ($debug) {
      $innerHTML = " $listobject->querystring ; <br>";
      print("$innerHTML");
   }
   $listobject->performQuery();

}

foreach ($linklist as $thislink) {
   
   # now, create the linkages
   $src_id = $element_info[$thislink['src_id']]['new_id'];
   $dest_id = $element_info[$thislink['dest_id']]['new_id'];
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
