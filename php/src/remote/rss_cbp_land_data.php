<?php


include('./config.php');
error_reporting(E_ERROR);

$actiontype = 4; # 1 - data list, 2 - get location list, 3 - get location data, 4 - get data
if (isset($_GET['actiontype'])) {
   $actiontype = $_GET['actiontype'];
}

$scenarioid = 2;
if (isset($_GET['scenarioid'])) {
   $scenarioid = $_GET['scenarioid'];
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
$romode = 'component';
if (isset($_GET['romode'])) {
   $romode = $_GET['romode'];
}
$params = '';
if (isset($_GET['params'])) {
   $params = $_GET['params'];
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
$debug = FALSE;
if (isset($_GET['debug'])) {
   $debug = $_GET['debug'];
}


//error_log("Inputs:" . print_r($_GET,1));

# initilize cbp data connection
$cbp_connstring = "host=$cbp_dbip dbname=cbp user=$dbuser password=$dbpass ";
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
if ($debug) print($cbp_listobject->querystring);
$cbp_listobject->performQuery();
$location_id = $cbp_listobject->getRecordValue(1,'location_id');
if ($debug) print(" = $location_id <br>");

$debug_str = '';
require_once("$libpath/feedcreator/feedcreator.class.php");
// make sure the cache is cleared
// shouldn't do this, as I think this is actually used by magpie, NOT feedcreator

$rss = new UniversalFeedCreator();
//$rss->useCached();
$rss->title = "$id2";
$rss->link = "http://test.com/news";
$rss->syndicationURL = "http://deq1.bse.vt.edu/".$PHP_SELF;
switch ($actiontype) {
   case 1:
$debug = TRUE;
   // this is static for now, but we could use a query like in case 4 to see exactly what the server HAS for a given area
   // GETS INFORMATION ABOUT DATA AVAILABLE
      // this could form the basis of our WFS retrieval system.
      $landuses = "'" . implode("','", explode(",", $id3)) . "'";
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
      switch ($romode) {
         case 'component':
            $basetable_sql = "  select lower(b.id3 || '_' || a.param_name) as newparam_name  ";
         break;
         
         case 'merged':
            $basetable_sql = "  select lower(b.id3 || '_in_ivld') as newparam_name  ";
         break;
      }
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
      if ($debug) error_log("$debug_str<br>");
      $cbp_listobject->performQuery();
      
      foreach ($cbp_listobject->queryrecords as $thisrec) { 
         $cols[] = $thisrec['newparam_name'];
         $coltypes[] = 'float8';
      }
      
      //$cols = array('SURO','IFWO','AGWO');
      
      $item = new FeedItem();
      $item->title = $data['elemname'];
      $options = array("complexType" => "object");
      # unserialize the property list
      // don't return this to the WOOOMM objects, since it breaks the feed somehow
      //$proplist['debug_str'] = $debug_str;
      $proplist['time_column'] = 'thisdatetime';
      $proplist['landuse_names'] = implode(",", $lunames);
      $proplist['data_columns'] = implode(",", $cols);
      $proplist['data_column_types'] = implode(",", $coltypes);
      $allcols = $cols;
      $allcols[] = 'thisdatetime'; // add the date 
      $proplist['all_columns'] = implode(",", $allcols);
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
      $landuses = "'" . implode("','", explode(",", $id3)) . "'";
      $param_names = "'" . implode("','", explode(",", $params)) . "'";
      $cbp_listobject->querystring = "  select location_id ";
      $cbp_listobject->querystring .= " from cbp_model_location ";
      $cbp_listobject->querystring .= " where id1 = '$id1' ";
      $cbp_listobject->querystring .= "    and id2 = '$id2' ";
      $cbp_listobject->querystring .= "    and scenarioid = $scenarioid ";
      if ($landuses <> "''") {
         $cbp_listobject->querystring .= " and id3 in ($landuses) ";
      }
      error_log("Location Query: $cbp_listobject->querystring ");
      //error_log("Getting Parameter List ");
      $cbp_listobject->performQuery();
      $loclist = '';
      $locdel = '';
      foreach ($cbp_listobject->queryrecords as $thisdata) {
         $loclist .= $locdel . $thisdata['location_id'];
         $locdel = ',';
      }
      // this initially will just retrieve the data with column names crosstabbed and fudged to represent land use
      // we should ultimately include many options, such as timestep aggregation (merging hourly output to daily, or
      // multiplpes of hours
      // this could form the basis of our WFS retrieval system.
      if (strlen($loclist) > 0) {
         error_log("Retrieving Data for $id2 ");
         $basetable = 'tmp_cbp_out' . rand(1,1000);
         // iterate through each land use name and add a join
         // if we are passed a timestep, we need to aggregate the data along the timestep to save space
         // as well as permit easy processing by the end use model
         if ($timestep <> '') {
            $tsfloat = floatval($timestep);
            $src_table_sql = "  select mergedate as thisdatetime, ";
            switch ($romode) {
               case 'component':
                  $src_table_sql .= "   lower(id3 || '_' || param_name) as param_name,  ";
               break;
               
               case 'merged':
                  $src_table_sql .= "   lower(id3 || '_' || param_merge) as param_name,  ";
               break;
            }
            $src_table_sql .= " avg(thisvalue) as thisvalue ";
            $src_table_sql .= "  from ";
            $src_table_sql .= "  ( select b.id3, ";
            switch ($romode) {
               case 'component':
                  $src_table_sql .= "  ('$startdate'::date)::timestamp + ";
                  $src_table_sql .= "      ((round(extract(epoch from (thisdate - '$startdate'::timestamp))::integer/$tsfloat)* $tsfloat)::varchar || ' seconds')::interval as mergedate, ";
                  $src_table_sql .= "      a.param_name, thisvalue ";
               break;
               
               case 'merged':
                  // do not include param_name
                  $src_table_sql .= "  ('$startdate'::date)::timestamp + ";
                  $src_table_sql .= "      ((round(extract(epoch from (thisdate - '$startdate'::timestamp))::integer/$tsfloat)* $tsfloat)::varchar || ' seconds')::interval as mergedate, ";
                  $src_table_sql .= "      CASE ";
                  $src_table_sql .= "         WHEN a.param_name in ('SURO','IFWO','AGWO') THEN 'in_ivld' ";
                  $src_table_sql .= "         ELSE a.param_name ";
                  $src_table_sql .= "      END as param_merge, ";
                  $src_table_sql .= "      sum(thisvalue) as thisvalue ";
               break;
            }
            $src_table_sql .= "   from cbp_scenario_output as a, cbp_model_location as b  ";
            $src_table_sql .= "   where a.location_id in ($loclist) ";
            $src_table_sql .= "      and b.location_id in ($loclist) ";
            $src_table_sql .= "      and a.location_id = b.location_id ";
            if (strlen($params) > 0) {
               $src_table_sql .= "      and a.param_name in ($param_names) ";
            }
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
            $src_table_sql .= "          OR ";
            $src_table_sql .= "       (param_name = 'PREC' and param_group = 'EXTNL') ";
            $src_table_sql .= "     ) ";
            $src_table_sql .= "   ";
            switch ($romode) {
               case 'component':
                  $src_table_sql .= "      order by mergedate, param_name ";
               break;
               
               case 'merged':
                  // do not include param_name
                  $src_table_sql .= "      group by thisdate, b.id3, param_merge ";
                  $src_table_sql .= "      order by mergedate ";
               break;
            }
            
            $src_table_sql .= "   ) as foo ";
            switch ($romode) {
               case 'component':
                  $src_table_sql .= "   group by mergedate, id3, param_name ";
                  $src_table_sql .= "   order by mergedate, param_name ";
               break;
               
               case 'merged':
                  $src_table_sql .= "   group by mergedate, id3, param_merge ";
                  $src_table_sql .= "   order by mergedate, param_merge ";
               break;
            }
         } else {
            $src_table_sql = "  select a.thisdate as thisdatetime, ";
            switch ($romode) {
               case 'component':
                  $src_table_sql .= "   lower(id3 || '_' || param_name) as param_name,  ";
                  $src_table_sql .= " a.thisvalue  ";
               break;
               
               case 'merged':
                  $src_table_sql .= "   lower(id3 || '_in_ivld') as param_name,  ";
                  $src_table_sql .= " sum(a.thisvalue) as thisvalue  ";
               break;
            }
            $src_table_sql .= " a.thisvalue  ";
            $src_table_sql .= " from cbp_scenario_output as a, cbp_model_location as b ";
            $src_table_sql .= " where a.location_id in ($loclist) ";
            $src_table_sql .= " and b.location_id in ($loclist) ";
            $src_table_sql .= " and a.location_id = b.location_id ";
            $src_table_sql .= " and a.scenarioid = $scenarioid ";
            if (strlen($params) > 0) {
               $src_table_sql .= "      and a.param_name in ($param_names) ";
            }
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
            $src_table_sql .= "     OR ";
            $src_table_sql .= "       (param_name = 'PREC' and param_group = 'EXTNL') ";
            $src_table_sql .= "     ) ";
            switch ($romode) {
               case 'component':
               break;
               
               case 'merged':
                  $src_table_sql .= " group by a.thisdate, id3  ";
               break;
            }
         }
 
         $basetable_sql = "  create temp table $basetable as  ";
         $basetable_sql .= $src_table_sql;
         $cbp_listobject->querystring = $basetable_sql;
         //if ($this->debug) {
            $debug_str .= "Copying base data " . $basetable_sql . " ; <br>";
         //}
         $cbp_listobject->performQuery();
error_log($cbp_listobject->querystring);
         // get a list of parameters that I can retrieve
         $cbp_listobject->querystring = "  select param_name from $basetable group by param_name ";
         $cbp_listobject->performQuery();
         foreach ($cbp_listobject->queryrecords as $thisrec) { 
            $cols[] = $thisrec['param_name'];
         }
         $rss->description = implode(",", $cols);
         $rss->column_names = implode(",", $cols);

         $groupcols = 'thisdatetime';
         $crosscol = 'param_name';
         $valcol = 'thisvalue';

         //error_log("$basetable_sql<br>");
         //error_log("$debug_str<br>");
         $crosstab_query = doGenericCrossTab ($cbp_listobject, $basetable, $groupcols, $crosscol, $valcol, 1, 0);
         //error_log("$crosstab_query<br>");
         $cbp_listobject->querystring = $crosstab_query;
         $cbp_listobject->performQuery();
         $rc = count($cbp_listobject->queryrecords);
         //error_log("CBP Time series Land Data Retrieved $rc records<br>");
         $j = 0;
         foreach($cbp_listobject->queryrecords as $data) {
            $j++;
            $item = new FeedItem();
            $proplist = array();
            $proplist['thisdatetime'] = $data['thisdatetime'];
            reset($cols);
            foreach ($cols as $thiscol) {
               if (strlen($data[$thiscol]) == 0) {
                  $data[$thiscol] = 0.0;
               }
               $proplist["$thiscol"] = $data[$thiscol];
               if ($j == 1) {
                  //error_log("Adding $thiscol ");
               }
            }
            if ($j == 1) {
               //error_log("Final column names " . print_r(array_keys( $proplist),1) . " <br>\n ");
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
         //error_log("CBP Time series Land Data Added $j records<br>");
      }
      $xml = $rss->createFeed("2.0");
      $xlen = strlen($xml);
      error_log("Time series Feed Created for $id2 with length $xlen<br>");

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
