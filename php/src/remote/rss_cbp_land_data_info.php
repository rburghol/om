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
$timestep = '';
if (isset($_GET['timestep'])) {
   $timestep = $_GET['timestep'];
}


error_log("Inputs:" . print_r($_GET,1));

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
// make sure the cache is cleared
//shell_exec("rm ../rsscache/* -f");
$rss = new UniversalFeedCreator();
//$rss->useCached();
$rss->title = "Test news";
$rss->link = "http://test.com/news";
$rss->syndicationURL = "http://deq1.bse.vt.edu/".$PHP_SELF;
switch ($actiontype) {
   case 1:
   // this is static for now, but we could use a query like in case 4 to see exactly what the server HAS for a given area
      // this could form the basis of our WFS retrieval system.
      $landuses = "'" . join("','", split(",", $id3)) . "'";
      $lunames = array();
      // iterate through each land use name and add a join
      $luname_sql = "  select b.id3 as luname  ";
      $luname_sql .= " from cbp_model_location as b ";
      $luname_sql .= " where b.scenarioid = $scenarioid ";
      $luname_sql .= " and b.id1 = 'land' ";
      if ($landuses <> "''") {
         $luname_sql .= " and b.id3 in ($landuses) ";
      }
      if ($id1 <> '') {
         $luname_sql .= " and b.id1 = '$id1' ";
      }
      if ($id2 <> '') {
         $luname_sql .= " and b.id2 = '$id2' ";
      }
      $luname_sql .= " group by luname ";
      $cbp_listobject->querystring = $luname_sql;
      if ($debug) {
         $debug_str .= "Copying base data " . $luname_sql . " ; <br>";
      }
      $cbp_listobject->performQuery();
      
      foreach ($cbp_listobject->queryrecords as $thisrec) { 
         $lunames[] = $thisrec['luname'];
      }
      
      // iterate through each land use name and add a join
      $basetable_sql = "  select lower(b.id3 || '_' || a.param_name) as newparam_name  ";
      $basetable_sql .= " from cbp_scenario_param_name as a, cbp_model_location as b ";
      $basetable_sql .= " where a.location_id = b.location_id ";
      if ($landuses <> "''") {
         $basetable_sql .= " and b.id3 in ($landuses) ";
      }
      if ($id1 <> '') {
         $basetable_sql .= " and b.id1 = '$id1' ";
      }
      if ($id2 <> '') {
         $basetable_sql .= " and b.id2 = '$id2' ";
      }
      $basetable_sql .= " and a.scenarioid = $scenarioid ";
      $basetable_sql .= " and b.scenarioid = $scenarioid ";
      $basetable_sql .= " and b.id1 = 'land' ";
      $basetable_sql .= " and (";
      $basetable_sql .= "        (param_block = 'PERLND' and param_group = 'PWATER') ";
      $basetable_sql .= "     OR ";
      $basetable_sql .= "       (param_block = 'IMPLND' and param_group = 'IWATER') ";
      $basetable_sql .= "     OR ";
      $basetable_sql .= "       (param_name = 'PREC' and param_group = 'EXTNL') ";
      $basetable_sql .= "     ) ";
      $basetable_sql .= " group by newparam_name ";
      $cbp_listobject->querystring = $basetable_sql;
      //if ($debug) {
         $debug_str .= "Getting Param Names " . $basetable_sql . " ; <br>";
      //}
      error_log("$debug_str<br>");
      $cbp_listobject->performQuery();
      
      foreach ($cbp_listobject->queryrecords as $thisrec) { 
         $cols[] = $thisrec['newparam_name'];
      }
      
      //$cols = array('SURO','IFWO','AGWO');
      
      $item = new FeedItem();
      $item->title = $data['elemname'];
      $options = array("complexType" => "object");
      # unserialize the property list
      // don't return this to the WOOOMM objects, since it breaks the feed somehow
      //$proplist['debug_str'] = $debug_str;
      $proplist['time_column'] = 'thisdatetime';
      $proplist['landuse_names'] = join(",", $lunames);
      $proplist['data_columns'] = join(",", $cols);
      $allcols = $cols;
      $allcols[] = 'thisdate'; // add the date 
      $proplist['all_columns'] = join(",", $allcols);
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
      $landuses = "'" . join("','", split(",", $id3)) . "'";
      $cbp_listobject->querystring = "  select location_id ";
      $cbp_listobject->querystring .= " from cbp_model_location ";
      $cbp_listobject->querystring .= " where id1 = '$id1' ";
      $cbp_listobject->querystring .= "    and id2 = '$id2' ";
      $cbp_listobject->querystring .= "    and scenarioid = $scenarioid ";
      if ($landuses <> "''") {
         $cbp_listobject->querystring .= " and id3 in ($landuses) ";
      }
      error_log("Location Query: $cbp_listobject->querystring ");
      $cbp_listobject->performQuery();
      $loclist = '';
      $locdel = '';
      foreach ($cbp_listobject->queryrecords as $thisdata) {
         $loclist .= $locdel . $thisdata['location_id'];
         $locdel = ',';
      }
      // this initially will just retrieve the data with column names crosstabbed and fudged to represnt land use
      // we should ultimatley include many options, such as timestep aggregation (merging hourly output to daily, or
      // multiplpes of hours
      // this could form the basis of our WFS retrieval system.
      if (strlen($loclist) > 0) {
         $basetable = 'tmp_cbp_out' . rand(1,1000);
         // iterate through each land use name and add a join
         // if we are passed a timestep, we need to aggregate the data along the timestep to save space
         // as well as permit easy processing by the end use model
         if ($timestep <> '') {
            $tsfloat = floatval($timestep);
            $src_table_sql = "  select thisdate,  lower(id3 || '_' || param_name) as param_name, ";
            $src_table_sql .= " avg(thisvalue) as thisvalue ";
            $src_table_sql .= "  from ";
            $src_table_sql .= "  ( select b.id3, ('$startdate'::date)::timestamp + ";
            $src_table_sql .= "      ((round(extract(epoch from (thisdate - '$startdate'::timestamp))::integer/$tsfloat)* $tsfloat)::varchar || ' seconds')::interval as thisdate, ";
            $src_table_sql .= "   param_name, thisvalue ";
            $src_table_sql .= "   from cbp_scenario_output as a, cbp_model_location as b  ";
            $src_table_sql .= "   where a.location_id in ($loclist) ";
            $src_table_sql .= "      and b.location_id in ($loclist) ";
            $src_table_sql .= "      and a.location_id = b.location_id ";
            if (strlen($startdate) > 0) {
               $src_table_sql .= "      and a.thisdate >= '$startdate' ";
            }
            if (strlen($enddate) > 0) {
               $src_table_sql .= "      and a.thisdate <= '$enddate' ";
            }
            $src_table_sql .= "      and (";
            $src_table_sql .= "        (param_block = 'PERLND' and param_group = 'PWATER') ";
            $src_table_sql .= "          OR ";
            $src_table_sql .= "       (param_block = 'IMPLND' and param_group = 'IWATER') ";
            $src_table_sql .= "     ) ";
            $src_table_sql .= "   order by thisdate, param_name ";
            $src_table_sql .= "   ) as foo ";
            $src_table_sql .= "   group by thisdate, id3, param_name ";
            $src_table_sql .= "   order by thisdate, param_name ";
         } else {
            $src_table_sql = "  select a.thisdate, lower(b.id3 || '_' || a.param_name) as param_name, a.thisvalue  ";
            $src_table_sql .= " from cbp_scenario_output as a, cbp_model_location as b ";
            $src_table_sql .= " where a.location_id in ($loclist) ";
            $src_table_sql .= " and b.location_id in ($loclist) ";
            $src_table_sql .= " and a.location_id = b.location_id ";
            $src_table_sql .= " and a.scenarioid = $scenarioid ";
            if (strlen($startdate) > 0) {
               $src_table_sql .= " and a.thisdate >= '$startdate' ";
            }
            if (strlen($enddate) > 0) {
               $src_table_sql .= " and a.thisdate <= '$enddate' ";
            }
            $src_table_sql .= " and (";
            $src_table_sql .= "        (param_block = 'PERLND' and param_group = 'PWATER') ";
            $src_table_sql .= "     OR ";
            $src_table_sql .= "       (param_block = 'IMPLND' and param_group = 'IWATER') ";
            $src_table_sql .= "     ) ";
         }
 
         $basetable_sql = "  create temp table $basetable as  ";
         $basetable_sql .= $src_table_sql;
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
         $rss->description = join(",", $cols);
         $rss->column_names = join(",", $cols);

         $groupcols = 'thisdate';
         $crosscol = 'param_name';
         $valcol = 'thisvalue';

         error_log("$basetable_sql<br>");
         error_log("$debug_str<br>");
         $crosstab_query = doGenericCrossTab ($cbp_listobject, $basetable, $groupcols, $crosscol, $valcol, 1, 0);
         error_log("$crosstab_query<br>");
         $cbp_listobject->querystring = $crosstab_query;
         $cbp_listobject->performQuery();
         $rc = count($cbp_listobject->queryrecords);
         //error_log("Retrieved $rc records<br>");
         $j = 0;
         foreach($cbp_listobject->queryrecords as $data) {
            $j++;
            $item = new FeedItem();
            $proplist = array();
            $proplist['thisdate'] = $data['thisdate'];
            reset($cols);
            foreach ($cols as $thiscol) {
               $proplist["$thiscol"] = $data[$thiscol];
               if ($j == 1) {
                  //error_log("Adding $thiscol ");
               }
            }
            if ($j == 1) {
               error_log("Final column names " . print_r(array_keys( $proplist),1) . " <br>\n ");
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
