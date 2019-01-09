<?php


include('./config.php');

$scenarioid = 2;

$actiontype = 4; # 1 - data list, 2 - get location list, 3 - get location data, 4 - get data
if (isset($_GET['actiontype'])) {
   $actiontype = $_GET['actiontype'];
}

$id1 = '';
if (isset($_GET['id1'])) {
   $id1 = $_GET['id1'];
}
$id2 = '';
if (isset($_GET['id2'])) {
   $id2 = $_GET['id2'];
}
$id3 = '';
if (isset($_GET['id3'])) {
   $id3 = $_GET['id3'];
}
$startdate = '';
if (isset($_GET['startdate'])) {
   $startdate = $_GET['startdate'];
}
$enddate = '';
if (isset($_GET['enddate'])) {
   $enddate = $_GET['enddate'];
}

# initilize cbp data connection
$cbp_connstring = "host=$dbip dbname=cbp user=$dbuser password=$dbpass ";
//print($cbp_connstring);
$cbp_dbconn = pg_connect($cbp_connstring, PGSQL_CONNECT_FORCE_NEW);
$cbp_listobject = new pgsql_QueryObject;
$cbp_listobject->ogis_compliant = 1;
$cbp_listobject->connstring = $cbp_connstring;
$cbp_listobject->dbconn = $cbp_dbconn;
$cbp_listobject->adminsetuparray = $adminsetuparray;

// get the location ID for this request
$cbp_listobject->querystring = "  select location_id  ";
$cbp_listobject->querystring .= " from cbp_model_location ";
$cbp_listobject->querystring .= " where id1 = '$id1' "; 
$cbp_listobject->querystring .= "    and id2 = '$id2' "; 
$cbp_listobject->querystring .= "    and id3 = '$id3' ";
$cbp_listobject->querystring .= "    and scenarioid = $scenarioid ";
//print($cbp_listobject->querystring);
$cbp_listobject->performQuery();
$location_id = $cbp_listobject->getRecordValue(1,'location_id');
//print($location_id);

require_once("$libdir/feedcreator/feedcreator.class.php");
$rss = new UniversalFeedCreator();
$rss->useCached();
$rss->title = "Test news";
$rss->link = "http://test.com/news";
$rss->syndicationURL = "http://deq1.bse.vt.edu/".$PHP_SELF;
switch ($actiontype) {
   case 1:
      $cbp_listobject->querystring = "  select elementid, elemname, component_type, ";
      $cbp_listobject->querystring .= "    array_dims(elemoperators) as adims ";
      $cbp_listobject->querystring .= " from scen_model_element ";
      $cbp_listobject->querystring .= " where elementid = $elementid ";
      $rss->description .= $cbp_listobject->querystring;
      error_log($cbp_listobject->querystring);
      $cbp_listobject->performQuery();
      $data = $cbp_listobject->queryrecords[0];
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
      $cbp_listobject->querystring = "  select elem_xml ";
      $cbp_listobject->querystring .= " from scen_model_element ";
      $cbp_listobject->querystring .= " where elementid = $elementid ";
      $rss->description .= $cbp_listobject->querystring;
      $cbp_listobject->performQuery();
      $data = $cbp_listobject->queryrecords[0];
      $xml = $data['elem_xml'];
   break;

   case 3:
      # get sub-components of this object
      # handle sub-components on this object
      $opxmls = array();
      if ($compid > 0) {
         $cbp_listobject->querystring = "  select elemoperators[$compid] ";
         $cbp_listobject->querystring .= " from scen_model_element ";
         $cbp_listobject->querystring .= " where elementid = $elementid"; 
         $cbp_listobject->performQuery();
         $xml = $cbp_listobject->getRecordValue(1,'elemoperators');
      }
   break;

   case 4:
      # get model output data
      // this initially will just retrieve the data with column names crosstabbed and fudged to represnt land use
      // we should ultimatley include many options, such as timestep aggregation (merging hourly output to daily, or
      // multiplpes of hours
      // this could form the basis of our WFS retrieval system.
      $landuses = "'" . join("','", split(",", $id3)) . "'";
      $basetable = 'tmp_cbp_out' . rand(1,1000);
      // iterate through each land use name and add a join
      $basetable_sql = "  create temp table $basetable as  ";
      $basetable_sql .= " select a.thisdate, b.id3 || '_' || a.param_name as param_name, a.thisvalue  ";
      $basetable_sql .= " from cbp_scenario_output as a, cbp_model_location as b ";
      $basetable_sql .= " where a.location_id = b.location_id ";
      if ($landuses <> "''") {
         $basetable_sql .= " and b.id3 in ($landuses) ";
      }
      $basetable_sql .= " and b.id1 = '$id1' ";
      $basetable_sql .= " and b.id2 = '$id2' ";
      $basetable_sql .= " and a.scenarioid = $scenarioid ";
      $basetable_sql .= " and a.scenarioid = $scenarioid ";
      if (strlen($startdate) > 0) {
         $basetable_sql .= " and a.thisdate >= '$startdate' ";
      }
      if (strlen($enddate) > 0) {
         $basetable_sql .= " and a.thisdate <= '$enddate' ";
      }
      $basetable_sql .= " and (";
      $basetable_sql .= "        (param_block = 'PERLND' and param_group = 'PWATER') ";
      $basetable_sql .= "     OR ";
      $basetable_sql .= "       (param_block = 'IMPLND' and param_group = 'IWATER') ";
      $basetable_sql .= "     ) ";
      $cbp_listobject->querystring = $basetable_sql;
      //if ($this->debug) {
         $debug_str .= "Copying base data " . $basetable_sql . " ; <br>";
      //}
      $cbp_listobject->performQuery();
      
      // get a list of parameters that I can retrieve
      $cbp_listobject->querystring = "  select param_name from $basetable group by param_name ";
      $cbp_listobject->performQuery();
      foreach ($cbp_listobject->queryrecords as $thisrec) { 
         $cols[] = $thisrec['param_name'];
      }
      
      $groupcols = 'thisdate';
      $crosscol = 'param_name';
      $valcol = 'thisvalue';
      
      $crosstab_query = doGenericCrossTab ($cbp_listobject, $basetable, $groupcols, $crosscol, $valcol, 1, 0);
      $cbp_listobject->querystring = $crosstab_query;
      $cbp_listobject->performQuery();
      $rc = count($cbp_listobject->queryrecords);
      //error_log("$debug_str<br>");
      //error_log("$crosstab_query<br>");
      //error_log("Retrieved $rc records<br>");
      $j = 0;
      foreach($cbp_listobject->queryrecords as $data) {
         $j++;
         $item = new FeedItem();
         $proplist = array();
         $proplist['thisdate'] = $data['thisdate'];
         reset($cols);
         foreach ($cols as $thiscol) {
            $proplist[$thiscol] = $data[$thiscol];
         }
         /*
         $proplist['elementid'] = $data['elementid'];
         $proplist['elemname'] = $data['elemname'];
         $proplist['component_type'] = $data['component_type'];
         $proplist['firstcomp'] = $astart;
         $proplist['lastcomp'] = $aend;
         #print_r($proplist);
         */
         $item->additionalElements = $proplist;
         $rss->addItem($item);
         //if ($j > 5) {
         //   break;
         //}
      }
      $xml = $rss->createFeed("2.0");
      
   break;

   case 5:
      # get properties xml
      $cbp_listobject->querystring = "  select elemprops ";
      $cbp_listobject->querystring .= " from scen_model_element ";
      $cbp_listobject->querystring .= " where elementid = $elementid ";
      $cbp_listobject->performQuery();
      $xml = $cbp_listobject->getRecordValue(1,'eleminputs');
   break;
}


print("$xml");

?>
