<?php


#require('c:/usr/local/home/httpd/devlib/magpierss/rss_fetch.inc');  

include("./config.php");
require_once("$libpath/magpierss/rss_fetch.inc"); 

function retrieveObjectList($elementid) {
   global $listobject;
   $rss = new UniversalFeedCreator();
   #$rss->useCached();
   define('MAGPIE_CACHE_ON', FALSE);
   $rss->title = "Test news";
   $rss->link = "http://test.com/news";
   $rss->syndicationURL = "http://deq1.bse.vt.edu/".$PHP_SELF;
   $unserializer = new XML_Unserializer($options);
   
   $clist = getChildList($listobject, $elementid);
   if (count($clist) > 0) {
      $cclause = " dest_id in ( " . join(",", $clist) . ", $elementid) ";
   } else {
      $cclause = " (dest_id = $elementid) ";
   }
   
   # get list of model children of this element
   $listobject->querystring = "  select projectid, scenarioid, linktype, src_id, dest_id, src_prop, dest_prop ";
   $listobject->querystring .= " from map_model_linkages ";
   $listobject->querystring .= " where $cclause ";
   $listobject->querystring .= " group by projectid, scenarioid, linktype, src_id, dest_id, src_prop, dest_prop ";
   print("$listobject->querystring ;<br>");
   //error_log($listobject->querystring);
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

function getChildList($listobject, $elementid) {
   
   $clist = array();
   $listobject->querystring = "  select src_id ";
   $listobject->querystring .= " from map_model_linkages ";
   $listobject->querystring .= " where dest_id = $elementid ";
   # get only link type 1, which is contained object, other linkages will be gotten later
   $listobject->querystring .= "    and linktype = 1 ";
   if ($debug) {
      $innerHTML .= "<b>debug:</b> get a list of elements in the current group <br>";
      $innerHTML .= "$listobject->querystring<br>";
   }
   print("Query children of $elementid : $listobject->querystring<br>\n");  
   $listobject->performQuery();
   $contained = $listobject->queryrecords;
   foreach ($contained as $thisrec) {
      $id = $thisrec['src_id'];
      $clist[] = $id;
      $cids = getChildList($listobject, $id);
      $clist = array_merge($clist, $cids);
   }
   return $clist;
}

function retrieveObject($elementid, $actiontype = 1, $compid = -1) {
   global $listobject;
   error_reporting(E_ALL);

   $rss = new UniversalFeedCreator();
   $rss->useCached();
   $rss->title = "Test news";
   $rss->link = "http://test.com/news";
   $rss->syndicationURL = "http://deq1.bse.vt.edu/".$PHP_SELF;
   $options = array();
   $unserializer = new XML_Unserializer($options);
   print("Checkng for action $actiontype \n");
   switch ($actiontype) {
      case 1:
         $listobject->querystring = "  select elementid, elemname, component_type, objectclass, ";
         $listobject->querystring .= "    array_dims(elemoperators) as adims ";
         $listobject->querystring .= " from scen_model_element ";
         $listobject->querystring .= " where elementid = $elementid ";
         $rss->description .= $listobject->querystring;
         //print($listobject->querystring);
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
         $proplist['objectclass'] = $data['objectclass'];
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
         $xml = $listobject->getRecordValue(1,'elemprops');
      break;

      case 6:
         # get all sub-components as single row
         $listobject->querystring = "  select elemoperators ";
         $listobject->querystring .= " from scen_model_element ";
         $listobject->querystring .= " where elementid = $elementid"; 
         $listobject->performQuery();
         $xml = $listobject->getRecordValue(1,'elemoperators');
      break;
   }

   error_reporting(E_NONE);
   return $xml;

}

if ( (count($argv) < 2) and (!isset($_GET['elementid'])) ) {
   print("You must submit an elementid with this request \n");
   die;
}
//error_reporting(E_NONE);
if (isset($_GET['elementid'])) {
   $modelid = $_GET['elementid'];
} else {
   $modelid = $argv[1];
}

if (isset($argv[3])) {
   switch ($argv[3]) {
      case 'deq2':
      $dbconn = pg_connect("host=deq2.bse.vt.edu port=5432 dbname=$dbname user=$dbuser password=$dbpass");
      $listobject = new pgsql_QueryObject;
      $listobject->dbconn = $dbconn;
      #$listobject->adminsetuparray = $adminsetuparray;
      break;
      
      default:
      //do nothing since dbconn is already set
      break;
   }
   
}

$outdir = "./data";

define('MAGPIE_CACHE_ON', FALSE);
$obxml = retrieveObjectList($modelid);
$rss = new MagpieRSS( $obxml, MAGPIE_OUTPUT_ENCODING, MAGPIE_INPUT_ENCODING, MAGPIE_DETECT_ENCODING );
//print_r($rss->items);

$elements = array();
$linklist = $rss->items;
# create an entry for the model itself
$elements[$modelid] = array('old_id'=>$modelid, 'new_id'=>-1);

$links = $outdir . "/export_links.$modelid" . ".csv";
$lfp = fopen($links,'w');
fwrite($lfp, "rowid,projectid,scenarioid,linktype,src_id,dest_id,src_prop,dest_prop\n");
fclose($lfp);
putDelimitedFile($links,$linklist,$thisdelim=',',0,'unix');
print("Storing links in $links<br>");

foreach($linklist as $thislinkage) {
   print("Found Element/Link: ") . print_r($thislinkage, 1) . "<br>";
   if ($thislinkage['linktype'] == 1) {
      # this is a model containment linkage, add it to the retrieval queue
      $elements[$thislinkage['src_id']] = array('old_id'=>$thislinkage['src_id'], 'new_id'=>-1);
   }
} 

$manifest = $outdir . "/export_manifest.$modelid" . ".csv";
print("Storing manifest in $manifest<br>");
$mfp = fopen($manifest,'w');
fwrite($mfp, "elementid,elemname,objectclass,component_type,xmlfile,compfile,inputfile,propfile");

foreach (array_keys($elements) as $thiselement) {
   $elementid = $elements[$thiselement]['old_id'];
   
   # get the basic object information - actiontype = 1
   print("<b>Trying:  retrieveObject($elementid, 1, $compid = -1)<br>");

   $obxml = retrieveObject($elementid, 1, $compid = -1);
   $rss = new MagpieRSS( $obxml, MAGPIE_OUTPUT_ENCODING, MAGPIE_INPUT_ENCODING, MAGPIE_DETECT_ENCODING );

   foreach($rss->items as $thisitem) {
      $elemname = str_replace(',', '', $thisitem['elemname']);
      $elid = $thisitem['elementid'];
      $component_type = $thisitem['component_type'];
      $objectclass = $thisitem['objectclass'];
      
      $manifest_string .= "\n" . $elid . ",$elemname" . ",$objectclass" . ",$component_type";
      
      print("<b>Retrieving: </b>" . $elemname . "<br>");
      #print_r($thisitem);
      print("Getting XML: retrieveObject($elid, 2) ... ");
      $elemxml = retrieveObject($elid, 2);

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
      $fp = fopen($compfile,'w');

      print("record stored with elementid = $newelementid <br>");

      $compxml = retrieveObject($elementid, 6, $compid);
      fwrite($fp, $compxml);
      fclose($fp);
      $manifest_string .= ",export_comps.$elid" . ".xml";
      
      
      # get inputs and props
      $inputfile = $outdir . "/" . "export_inputs.$elid" . ".xml";
      print("Storing inputfile in $inputfile<br>");
      $fp = fopen($inputfile,'w');
      print("Getting Component $compid: <br>");
      $ixml = retrieveObject($elid, 4);
      fwrite($fp, $ixml);
      fclose($fp);
      $manifest_string .= ",export_inputs.$elid" . ".xml";
      
      # props
      $propsfile = $outdir . "/" . "export_props.$elid" . ".xml";
      print("Storing propsfile in $propsfile<br>");
      $fp = fopen($propsfile,'w');
      print("Getting Component $compid: <br>");
      $pxml = retrieveObject($elid, 5);
      fwrite($fp, $pxml);
      fclose($fp);
      $manifest_string .= ",export_props.$elid" . ".xml";
   }
}

fwrite($mfp, $manifest_string);
fclose($mfp);

?>
