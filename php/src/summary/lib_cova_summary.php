<?php

function getMergedCOVAShape($scenarioid, $dbobj, $seglist) {
   $segs = "'" . join("','", $seglist) . "'";
   $dbobj->querystring = "  select asText(ST_union(simplify(poly_geom,0.0001))) as seg_geom from scen_model_element ";
   $dbobj->querystring .= " where ";
   if (strlen(trim($segs)) > 0) {
      $dbobj->querystring .= " custom2 in ($segs) ";
   } else {
      $dbobj->querystring .= " ( (custom2 = '') or (custom2 is null) ) ";
   }
   $dbobj->querystring .= " and scenarioid = $scenarioid and custom1 in ( 'cova_ws_container' , 'cova_ws_subnodal') ";
   error_log("$dbobj->querystring ; <br>\n");
   $dbobj->performQuery();
   if ($dbobj->numrows > 0) {
      $shp = $dbobj->getRecordValue(1,'seg_geom');
      return $shp;
   } else {
      return false;
   }
}

function getCOVASegments($elementid, $ncnt, $geoscope) {
   global $listobject;
   //$ncnt - contains the container types that we want
   $segs = array();
   $outlet_seg = array();
   $models = getNestedContainersCriteria ($listobject, $elementid, array(), $ncnt);
   //$models = getNestedContainersCriteria ($listobject, $uscont_id, array(), $ncnt);
   //print("Upstream Tribs: " . print_r($models,1) . "<br>");

   foreach ($models as $thismod) {
      $segs[] = $thismod['custom2'];
      if ($thismod['elementid'] == $elementid) {
         $outlet_seg[] = $thismod['custom2'];
      }
   }
   //print("All segments: " . print_r($segs,1) . "<br>");
   //print("Outlet segment: " . $outlet_seg . "<br>");
   // get shape for overlapping queries
   switch ($geoscope) {
      case 'local':
         $wkt_segs = $outlet_seg;
         $alt_scope = 'cumulative';
      break;
      
      case 'cumulative':
         $wkt_segs = $segs;
         $alt_scope = 'local';
      break;
      
      default:
         $wkt_segs = $outlet_seg;
         $alt_scope = 'cumulative';
      break;
   }

   return $wkt_segs;

}


function cova_graphFlowDuration($result_set, $debug) {
   global $session_db, $goutdir, $goutpath, $gouturl;
   $out = array('img_url'=>'', 'debug'=>'');
   $query = $result_set['query'];
   // create graph object to show bar graph comparisons of monthle median flow
   //$session_db->querystring = "select extract(month from thisdate) as thismo, median(\"Qout_0\") as qout0, median(\"Qout_2\") as qout2 from ($query) as foo group by thismo ";
   
   $color = array('blue', 'green', 'brown', 'red', 'orange', 'thistle', 'tan', 'black', 'pink');
   $bgs = array();
   $ckey = 0;
   $i = 0;
   $ymax = 0.001;
   $ymin = 0.001;
   foreach ($result_set['valid_cols'] as $thiscol) {
      $cdel = ',';
      $session_db->querystring = "select extract(month from thisdate) as thismo, ";
      $session_db->querystring .= "\"$thiscol\" as \"$thiscol\" ";
      $cdel = ',';

      $session_db->querystring .= " from ($query) as foo ";
      $session_db->querystring .= " order by \"$thiscol\" DESC";

      $session_db->performQuery();
      if ($debug) {
         $out['debug'] .= "$session_db->querystring <br>";
      }
      $session_db->show = 0;
      $session_db->showList();
      $out['data_table'] .= $session_db->outstring . "<br>";

      $flowrecs = $session_db->queryrecords;
      $nums = count($flowrecs);
      $num = 0;
      foreach ($flowrecs as $key=>$thisrec) {
         $flow = $thisrec[$thiscol];
         if ($flow > $ymax) {
            $ymax = $flow;
         }
         if ( ($flow < $ymin) and ($flow > 0) ) {
            $ymin = $flow;
         }
         $ret = $num / $nums;
         $num++;
         $flowrecs[$key]["p_$thiscol"] = $ret;
      }
      //print("key:  $ckey<br>");
      $graph = array();
      $graph['graphrecs'] = $flowrecs;
      $graph['xcol'] = "p_$thiscol";
      $graph['ycol'] = "$thiscol";
      $graph['yaxis'] = 1;
      $graph['plottype'] = 'line';
      $ckey = $i;
      if (!isset($color[$ckey])) {
         $ckey = 0;
      }
      $i++;
      $graph['color'] = $color[$ckey];
      $graph['ylegend'] = $thiscol;
      $bgs[] = $graph;
   }

   $multibar = array(
      'title'=> ucwords($function) . " Monthly Flows",
      'xlabel'=>'Exceedance',
      'ylabel'=>'Flow (cfs)',
      'num_xlabels'=>15,
      'x_interval'=>15,
      'gwidth'=>600,
      'gheight'=>400,
      'overlapping'=>0,
      'labelangle'=>90,
      'randomname'=>0,
      'scale'=>'linlog',
      'ymin'=>$ymin,
      //'ymax'=>$ymax,
      'legendlayout'=>LEGEND_HOR,
      'legendpos'=>array(0.20,0.95,'left','bottom'),
      'base
name'=>"medflows_$elementid",
      'bargraphs'=>$bgs
   );
   $graphurl = showGenericMultiPlot($goutdir, $gouturl, $multibar, $debug);
   $out['img_url'] = $graphurl;
   return $out;
}


function cova_graphFlowComparison($result_set, $function, $debug, $minval = NULL, $digits = -1, $mode = 'monthly') {
   global $session_db, $goutdir, $goutpath, $gouturl;
   // $mode - daily - does the stat over the daily values of the month in question
   //   - monthly - computes the mean monthly, then applies the stat to that summary query
   $out = array('img_url'=>'', 'debug'=>'');
   $query = $result_set['query'];
   // create graph object to show bar graph comparisons of monthle median flow
   //$session_db->querystring = "select extract(month from thisdate) as thismo, median(\"Qout_0\") as qout0, median(\"Qout_2\") as qout2 from ($query) as foo group by thismo ";
   $cdel = ',';
   
   switch ($mode) {
   
      case 'daily':
         // just took a median monthly value, that is, the median value that appears in that month during the model period
         $finalquery = $query;
      break;

      case 'monthly':
         // takes the value for mean monthly flow during the modeling period
         $finalquery = " select * from ( ";
         $finalquery .= "    select extract(year from thisdate) as thisyear, "; 
         $finalquery .= "       extract(month from thisdate) as thismonth, "; 
         $finalquery .= "       min(thisdate) as thisdate "; 
         foreach ($result_set['valid_cols'] as $scol) {
            $finalquery .= ", ";
            $finalquery .= "       avg(\"$scol\") "; 
            $finalquery .= "       as \"$scol\" "; 
         }
         $finalquery .= "    from ($query) as foo "; 
         $finalquery .= "    group by extract(year from thisdate), extract(month from thisdate) "; 
         $finalquery .= " ) as bar ";
      break;
   
      default:
         // just took a median monthly value, that is, the median value that appears in that month during the model period
         $finalquery = $query;
      break;
   }

   // now aplpy the chosen stat to the final query
   $session_db->querystring = "select extract(month from thisdate) as thismo ";
   foreach ($result_set['valid_cols'] as $thiscol) {
      $session_db->querystring .= "$cdel  ";
      if ($digits >= 0) {
         $session_db->querystring .= "round(";
      }
      switch ($function) {
      
         case 'gini':
         $session_db->querystring .= "$function(array_accum(\"$thiscol\")) ";
         break;
      
         case 'pct01':
         $session_db->querystring .= "r_quantile(array_accum(\"$thiscol\"), 0.01) ";
         break;
      
         case 'pct05':
         $session_db->querystring .= "r_quantile(array_accum(\"$thiscol\"), 0.05) ";
         break;
      
         case 'pct10':
         $session_db->querystring .= "r_quantile(array_accum(\"$thiscol\"), 0.1) ";
         break;
      
         case 'pct25':
         $session_db->querystring .= "r_quantile(array_accum(\"$thiscol\"), 0.25) ";
         break;
      
         default:
         $session_db->querystring .= "$function(\"$thiscol\") ";
         break;
      }
      if ($digits >= 0) {
         $session_db->querystring .= "::numeric, $digits) ";
      }
      $session_db->querystring .= " as \"$thiscol\" ";
      $cdel = ',';
   }
   $session_db->querystring .= " from ($finalquery) as foo ";
   if (!($minval === NULL)) {
      $firstcol = $result_set['valid_cols'][0];
      $session_db->querystring .= "WHERE \"$firstcol\" > $minval ";
   }
   $session_db->querystring .= " group by thismo order by thismo";

   $session_db->performQuery();
   if ($debug) {
      $out['debug'] .= "$session_db->querystring <br>";
   }
   $session_db->show = 0;
   $session_db->showList();
   $out['data_table'] .= $session_db->outstring . "<br>";
   $out['query'] .= $session_db->querystring . "<br>";
   $flowrecs = $session_db->queryrecords;
   $out['data_records'] = $flowrecs;
   
   $color = array('blue', 'green', 'brown', 'red', 'orange', 'thistle', 'tan', 'black', 'pink');
   $bgs = array();
   foreach ($result_set['valid_cols'] as $key => $val) {
      $graph = array();
      $graph['graphrecs'] = $flowrecs;
      $graph['xcol'] = 'thismo';
      $graph['ycol'] = $val;
      $graph['yaxis'] = 1;
      $graph['plottype'] = 'bar';
      $graph['color'] = $color[$key];
      $graph['ylegend'] = $val;
      $bgs[] = $graph;
   }

   $multibar = array(
      'title'=> ucwords($function) . " Monthly Flows",
      'xlabel'=>'Month',
      'ylabel'=>'Flow (cfs)',
      'num_xlabels'=>15,
      'x_interval'=>15,
      'gwidth'=>600,
      'gheight'=>400,
      'overlapping'=>0,
      'labelangle'=>90,
      'randomname'=>0,
      'legendlayout'=>LEGEND_HOR,
      'legendpos'=>array(0.20,0.95,'left','bottom'),
      'base
name'=>"medflows_$elementid",
      'bargraphs'=>$bgs
   );
   //$graphurl = showGenericMultiPlot($goutdir, $goutpath, $multibar, $debug);
   error_reporting(E_ALL);
   $graphurl = showGenericMultiPlot($goutdir, $gouturl, $multibar, 1);
   error_reporting(E_ERROR);
   $out['img_url'] = $graphurl;
   return $out;
}


function cova_graphHabitatComparison($result_set, $function, $debug, $restrictions = array(), $digits = -1, $gwidth = 600, $gheight = 400, $gname = 'habitat', $title = '') {
   global $session_db, $goutdir, $goutpath, $gouturl;
   $out = array('img_url'=>'', 'debug'=>'', 'query'=>'');
   $query = $result_set['query'];
   // create graph object to show bar graph comparisons of monthle median flow
   //$session_db->querystring = "select extract(month from thisdate) as thismo, median(\"Qout_0\") as qout0, median(\"Qout_2\") as qout2 from ($query) as foo group by thismo ";
   $cdel = ',';
   
   
   // just took a median monthly value, that is, the median value that appears in that month during the model period
   $function_parts = split(',',$function);
   $function = $function_parts[0];
   switch ($function) {
      case 'r_quantile':
         $fopen = 'r_quantile(array_accum(';
         $fclose = "), " . $function_parts[1] . ")";
      break;

      default:
         $fopen = $function . "(";
         $fclose = ")";
      break;
   }
   $session_db->querystring = "select extract(month from thisdate) as thismo ";
   $joiner = 'WHERE';
   $where_sql = '';
   sort($result_set['valid_cols']);
   foreach ($result_set['valid_cols'] as $thiscol) {
      $session_db->querystring .= "$cdel  ";
      if ($digits >= 0) {
         $session_db->querystring .= "round(";
      }
      $session_db->querystring .= "$fopen\"$thiscol\"$fclose ";
      if ($digits >= 0) {
         $session_db->querystring .= "::numeric, $digits) ";
      }
      $session_db->querystring .= " as \"$thiscol\", count(*) as \"$thiscol" . "_count\" ";
      $cdel = ',';
      //print("Checking restrictions on $thiscol in " . print_r($restrictions,1) . "<br>");
      if (isset($restrictions[$thiscol])) {
         foreach($restrictions[$thiscol] as $restrict) {
            $op = $restrict['op'];
            $val = $restrict['val'];
            $where_sql .= " $joiner \"$thiscol\" $op $val ";
            //print("Adding Restriction: $joiner \"$thiscol\" $op $val <br>");
            $joiner = 'AND';
         }
      }
   }
   $session_db->querystring .= " from ($query) as foo ";
   $session_db->querystring .= " $where_sql ";
   /*
   if (!($minval === NULL)) {
      $firstcol = $result_set['valid_cols'][0];
      $session_db->querystring .= "WHERE \"$firstcol\" > $minval ";
   }
   */
   $session_db->querystring .= " group by thismo order by thismo";
   $session_db->performQuery();
   $out['debug'] .= "$session_db->querystring <br>";
   $out['query'] .= "$session_db->querystring <br>";
   $session_db->show = 0;
   $session_db->showList();
   $out['data_table'] .= $session_db->outstring . "<br>";
   $flowrecs = $session_db->queryrecords;
   $out['data_records'] = $flowrecs;
   
   $color = array('blue', 'green', 'brown', 'red', 'orange', 'thistle', 'tan', 'black', 'pink');
   $bgs = array();
   $legend_len = 0;
   foreach ($result_set['valid_cols'] as $key => $val) {
      $graph = array();
      $graph['graphrecs'] = $flowrecs;
      $graph['xcol'] = 'thismo';
      $graph['ycol'] = $val;
      $graph['yaxis'] = 1;
      $graph['plottype'] = 'bar';
      $graph['color'] = $color[$key];
      if (isset($result_set['legends'][$val])) {
         $graph['ylegend'] = $result_set['legends'][$val];
      } else {
         $graph['ylegend'] = $val;
      }
      $bgs[] = $graph;
   }
   if ($title == '') {
      $title = ucwords($function) . ' ' . $gname;
   }
   $multibar = array(
      'title'=> $title,
      'xlabel'=>$result_set['xlabel'],
      'ylabel'=>$result_set['ylabel'],
      'num_xlabels'=>15,
      'x_interval'=>15,
      'gwidth'=>$gwidth,
      'gheight'=>$gheight,
      'overlapping'=>0,
      'labelangle'=>90,
      'randomname'=>0,
      'legendlayout'=>LEGEND_HOR,
      'legendpos'=>array(0.5,0.90,'center','bottom'),
      'basename'=>$gname,
      'bargraphs'=>$bgs
   );
   $graphurl = showGenericMultiPlot($goutdir, $gouturl, $multibar, $debug);
   $out['img_url'] = $graphurl;
   return $out;
}


function cova_graphHabitatComparison2($result_set, $function, $debug, $restrictions = array(), $digits = -1, $gwidth = 600, $gheight = 400, $gname = 'habitat', $title = '', $xvar = 'month') {
   global $session_db, $goutdir, $goutpath, $gouturl;
   $out = array('img_url'=>'', 'debug'=>'', 'query'=>'');
   $query = $result_set['query'];
   // create graph object to show bar graph comparisons of monthle median flow
   //$session_db->querystring = "select extract(month from thisdate) as thismo, median(\"Qout_0\") as qout0, median(\"Qout_2\") as qout2 from ($query) as foo group by thismo ";
   $cdel = ',';
   
   
   // just took a median monthly value, that is, the median value that appears in that month during the model period
   $function_parts = split(',',$function);
   $function = $function_parts[0];
   switch ($function) {
      case 'r_quantile':
         $fopen = 'r_quantile(array_accum(';
         $fclose = "), " . $function_parts[1] . ")";
      break;

      default:
         $fopen = $function . "(";
         $fclose = ")";
      break;
   }
   sort($result_set['valid_cols']);
   $maxkey = max(array_keys($result_set['valid_cols']));
   $reference = array();
   foreach ($result_set['valid_cols'] as $thiskey => $thiscol) {
      $where_sql = '';
         switch ($xvar) {
         case 'month':
            $session_db->querystring = "select extract(month from thisdate) as thismo, ";
         break;
         
         case 'season':
            $session_db->querystring = "select ceil(extract(month from thisdate)::numeric/3.0) as thismo, ";
         break;
         
         case 'default':
            $session_db->querystring = "select extract(month from thisdate) as thismo, ";
         break;
      }
      if ($digits >= 0) {
         $session_db->querystring .= "round(";
      }
      $session_db->querystring .= "$fopen\"$thiscol\"$fclose ";
      if ($digits >= 0) {
         $session_db->querystring .= "::numeric, $digits) ";
      }
      $session_db->querystring .= " as \"$thiscol\", count(*) as \"$thiscol" . "_count\" ";
      $cdel = ',';
      //print("Checking restrictions on $thiscol in " . print_r($restrictions,1) . "<br>");
      $joiner = 'WHERE';
      if (isset($restrictions[$thiscol])) {
         foreach($restrictions[$thiscol] as $restrict) {
            $op = $restrict['op'];
            $val = $restrict['val'];
            $where_sql .= " $joiner \"$thiscol\" $op $val ";
            print("Adding Restriction: $joiner \"$thiscol\" $op $val <br>");
            $joiner = 'AND';
         }
      }
      $session_db->querystring .= " from ($query) as foo ";
      $session_db->querystring .= " $where_sql ";
      $session_db->querystring .= " group by thismo order by thismo";
      $session_db->performQuery();
      $recs = $session_db->queryrecords;
      $out['debug'] .= "$session_db->querystring <br>";
      $out['query'] .= "<b>Query For $thiscol:</b> <br>$session_db->querystring <br>";
      foreach ($recs as $thisrec) {
         $final_array[$thisrec['thismo']]['thismo'] = $thisrec['thismo'];
         $final_array[$thisrec['thismo']][$thiscol] = $thisrec[$thiscol];
         if ($thiskey == $maxkey) {   
            $final_array[$thisrec['thismo']][$thiscol . "_count"] = $thisrec[$thiscol . "_count"];
         }
         if (!isset($reference[$thisrec['thismo']])) {
            $reference[$thisrec['thismo']] = $thisrec[$thiscol];
         } else {
            $final_array[$thisrec['thismo']][$thiscol . "_pct"] = number_format($thisrec[$thiscol] / $reference[$thisrec['thismo']],3);
         }
      }
      // add the count in for the last variable
   }
   /*
   if (!($minval === NULL)) {
      $firstcol = $result_set['valid_cols'][0];
      $session_db->querystring .= "WHERE \"$firstcol\" > $minval ";
   }
   */
   $session_db->queryrecords = $final_array;
   $session_db->show = 0;
   $session_db->showList();
   $out['data_table'] .= $session_db->outstring . "<br>";
   $flowrecs = $session_db->queryrecords;
   $out['data_records'] = $flowrecs;
   
   $color = array('blue', 'green', 'brown', 'red', 'orange', 'thistle', 'tan', 'black', 'pink');
   $bgs = array();
   $legend_len = 0;
   foreach ($result_set['valid_cols'] as $key => $val) {
      $graph = array();
      $graph['graphrecs'] = $flowrecs;
      $graph['xcol'] = 'thismo';
      $graph['ycol'] = $val;
      $graph['yaxis'] = 1;
      $graph['plottype'] = 'bar';
      $graph['color'] = $color[$key];
      if (isset($result_set['legends'][$val])) {
         $graph['ylegend'] = $result_set['legends'][$val];
      } else {
         $graph['ylegend'] = $val;
      }
      $bgs[] = $graph;
   }
   if ($title == '') {
      $title = ucwords($function) . ' ' . $gname;
   }
   $multibar = array(
      'title'=> $title,
      'xlabel'=>$result_set['xlabel'],
      'ylabel'=>$result_set['ylabel'],
      'num_xlabels'=>15,
      'x_interval'=>15,
      'gwidth'=>$gwidth,
      'gheight'=>$gheight,
      'overlapping'=>0,
      'labelangle'=>90,
      'randomname'=>0,
      'legendlayout'=>LEGEND_HOR,
      'legendpos'=>array(0.5,0.90,'center','bottom'),
      'basename'=>$gname,
      'bargraphs'=>$bgs
   );
   $graphurl = showGenericMultiPlot($goutdir, $gouturl, $multibar, $debug);
   $out['img_url'] = $graphurl;
   return $out;
}

function cova_graphStatOfStatComparison($result_set, $external_function, $internal_function, $debug, $restrictions = array(), $digits = -1, $gwidth = 600, $gheight = 400, $gname = 'statofstat', $title = '', $xvar = 'month') {
   global $session_db, $goutdir, $goutpath, $gouturl;
   $out = array('img_url'=>'', 'debug'=>'', 'query'=>'');
   $query = $result_set['query'];
   // create graph object to show bar graph comparisons of monthle median flow
   //$session_db->querystring = "select extract(month from thisdate) as thismo, median(\"Qout_0\") as qout0, median(\"Qout_2\") as qout2 from ($query) as foo group by thismo ";
   $cdel = ',';
   
   
   // just took a median monthly value, that is, the median value that appears in that month during the model period
   list($fopen1, $fclose1) = functionParts($external_function);
   list($fopen2, $fclose2) = functionParts($internal_function);
   
   sort($result_set['valid_cols']);
   $maxkey = max(array_keys($result_set['valid_cols']));
   $reference = array();
   foreach ($result_set['valid_cols'] as $thiskey => $thiscol) {
      $where_sql = '';
      // BEGIN - Internal Query
      switch ($xvar) {
         case 'month':
            $internal_query = "select extract(month from thisdate) as thismo, extract(year from thisdate) as thisyear, ";
         break;
         
         case 'season':
            $internal_query = "select ceil(extract(month from thisdate)::numeric/3.0) as thismo, extract(year from thisdate) as thisyear, ";
         break;
         
         case 'default':
            $internal_query = "select extract(month from thisdate) as thismo, extract(year from thisdate) as thisyear, ";
         break;
      }
      if ($digits >= 0) {
         $internal_query .= "round(";
      }
      $internal_query .= "$fopen2\"$thiscol\"$fclose2 ";
      if ($digits >= 0) {
         $internal_query .= "::numeric, $digits) ";
      }
      $internal_query .= " as \"$thiscol\" ";
      $cdel = ',';
      //print("Checking restrictions on $thiscol in " . print_r($restrictions,1) . "<br>");
      $joiner = 'WHERE';
      if (isset($restrictions[$thiscol])) {
         foreach($restrictions[$thiscol] as $restrict) {
            $op = $restrict['op'];
            $val = $restrict['val'];
            $where_sql .= " $joiner \"$thiscol\" $op $val ";
            print("Adding Restriction: $joiner \"$thiscol\" $op $val <br>");
            $joiner = 'AND';
         }
      }
      $internal_query .= " from ($query) as foo ";
      $internal_query .= " $where_sql ";
      $internal_query .= " group by thismo, thisyear order by thismo";
      // END - Internal Query
      
      // BEGIN - external query
      
      $external_query = " select thismo, ";
      $external_query .= "$fopen1\"$thiscol\"$fclose1 as \"$thiscol\", count(*) as \"$thiscol" . "_count\" ";
      $external_query .= " from ($internal_query) as bar  ";
      $external_query .= " group by thismo order by thismo";
      
      //print("$external_query<br>");
      $session_db->querystring = $external_query;
      $session_db->performQuery();
      $recs = $session_db->queryrecords;
      $out['debug'] .= "$session_db->querystring <br>";
      $out['query'] .= "<b>Query For $thiscol:</b> <br>$session_db->querystring <br>";
      foreach ($recs as $thisrec) {
         $final_array[$thisrec['thismo']]['thismo'] = $thisrec['thismo'];
         $final_array[$thisrec['thismo']][$thiscol] = $thisrec[$thiscol];
         if ($thiskey == $maxkey) {   
            $final_array[$thisrec['thismo']][$thiscol . "_count"] = $thisrec[$thiscol . "_count"];
         }
         if (!isset($reference[$thisrec['thismo']])) {
            $reference[$thisrec['thismo']] = $thisrec[$thiscol];
         } else {
            $final_array[$thisrec['thismo']][$thiscol . "_pct"] = number_format($thisrec[$thiscol] / $reference[$thisrec['thismo']],3);
         }
      }
      // add the count in for the last variable
   }
   /*
   if (!($minval === NULL)) {
      $firstcol = $result_set['valid_cols'][0];
      $session_db->querystring .= "WHERE \"$firstcol\" > $minval ";
   }
   */
   $session_db->queryrecords = $final_array;
   $session_db->show = 0;
   $session_db->showList();
   $out['data_table'] .= $session_db->outstring . "<br>";
   $flowrecs = $session_db->queryrecords;
   $out['data_records'] = $flowrecs;
   
   $color = array('blue', 'green', 'brown', 'red', 'orange', 'thistle', 'tan', 'black', 'pink');
   $bgs = array();
   $legend_len = 0;
   foreach ($result_set['valid_cols'] as $key => $val) {
      $graph = array();
      $graph['graphrecs'] = $flowrecs;
      $graph['xcol'] = 'thismo';
      $graph['ycol'] = $val;
      $graph['yaxis'] = 1;
      $graph['plottype'] = 'bar';
      $graph['color'] = $color[$key];
      if (isset($result_set['legends'][$val])) {
         $graph['ylegend'] = $result_set['legends'][$val];
      } else {
         $graph['ylegend'] = $val;
      }
      $bgs[] = $graph;
   }
   if ($title == '') {
      $title = ucwords($function) . ' ' . $gname;
   }
   $multibar = array(
      'title'=> $title,
      'xlabel'=>$result_set['xlabel'],
      'ylabel'=>$result_set['ylabel'],
      'num_xlabels'=>15,
      'x_interval'=>15,
      'gwidth'=>$gwidth,
      'gheight'=>$gheight,
      'overlapping'=>0,
      'labelangle'=>90,
      'randomname'=>0,
      'legendlayout'=>LEGEND_HOR,
      'legendpos'=>array(0.5,0.90,'center','bottom'),
      'basename'=>$gname,
      'bargraphs'=>$bgs
   );
   $graphurl = showGenericMultiPlot($goutdir, $gouturl, $multibar, $debug);
   $out['img_url'] = $graphurl;
   return $out;
}

function functionParts($function1) {

   $function1_parts = explode(',',$function1);
   $function1 = $function1_parts[0];
   switch ($function1) {
      case 'r_quantile':
         $fopen = 'r_quantile(array_accum(';
         $fclose = "), " . $function1_parts[1] . ")";
      break;

      default:
         $fopen = $function1 . "(";
         $fclose = ")";
      break;
   }
   return array($fopen, $fclose);
}

function cova_graphDuration($result_set, $debug, $restrictions = array(), $digits = -1, $gwidth = 600, $gheight = 400, $gname = 'habitat', $title = '', $scale = 'linlog') {
   global $session_db, $goutdir, $goutpath, $gouturl;
   $out = array('img_url'=>'', 'debug'=>'', 'query'=>'');
   $query = $result_set['query'];
   // create graph object to show bar graph comparisons of monthle median flow
   //$session_db->querystring = "select extract(month from thisdate) as thismo, median(\"Qout_0\") as qout0, median(\"Qout_2\") as qout2 from ($query) as foo group by thismo ";
   $cdel = ',';
   
   $where_sql = '';
   $joiner = 'WHERE';
   foreach ($result_set['valid_cols'] as $thiscol) {
      if (isset($restrictions[$thiscol])) {
         foreach($restrictions[$thiscol] as $restrict) {
            $op = $restrict['op'];
            $val = $restrict['val'];
            $where_sql .= " $joiner \"$thiscol\" $op $val ";
            //print("Adding Restriction: $joiner \"$thiscol\" $op $val <br>");
            $joiner = 'AND';
         }
      }
   }
   $session_db->querystring = "select * ";
   $session_db->querystring .= " from ($query) as foo ";
   $session_db->querystring .= " $where_sql ";
   $session_db->querystring .= " order by thisdate ";
   $session_db->performQuery();
   $flowrecs = $session_db->queryrecords;
   $out['debug'] .= "$session_db->querystring <br>";
   $out['query'] .= "<b>Query For $thiscol:</b> <br>$session_db->querystring <br>";
   $out['data_records'] = $flowrecs;
   
   $color = array('blue', 'green', 'brown', 'red', 'orange', 'thistle', 'tan', 'black', 'pink');
   $bgs = array();
   $legend_len = 0;
   foreach ($result_set['valid_cols'] as $key => $val) {
      $graph = array();
      $grecs = extract_keyvalue($flowrecs, $val);
      rsort($grecs);
      $graph['graphrecs'] = $grecs;
      $graph['xcol'] = $val;
      $graph['ycol'] = $val;
      $graph['yaxis'] = 1;
      $graph['plottype'] = 'line';
      $graph['color'] = $color[$key];
      if (isset($result_set['legends'][$val])) {
         $graph['ylegend'] = $result_set['legends'][$val];
      } else {
         $graph['ylegend'] = $val;
      }
      $bgs[] = $graph;
   }
   if ($title == '') {
      $title = ucwords($function) . ' ' . $gname;
   }
   $multibar = array(
      'title'=> $title,
      'xlabel'=>$result_set['xlabel'],
      'ylabel'=>$result_set['ylabel'],
      'num_xlabels'=>15,
      'x_interval'=>15,
      'gwidth'=>$gwidth,
      'gheight'=>$gheight,
      'overlapping'=>0,
      'labelangle'=>90,
      'randomname'=>0,
      'scale'=>$scale,
      'legendlayout'=>LEGEND_HOR,
      'legendpos'=>array(0.5,0.90,'center','bottom'),
      'basename'=>$gname,
      'bargraphs'=>$bgs
   );
   $graphurl = showGenericMultiPlot($goutdir, $gouturl, $multibar, $debug);
   $out['img_url'] = $graphurl;
   return $out;
}

function cova_graphHabitatTimeSeries($result_set, $debug, $restrictions = array(), $digits = -1, $gwidth = 600, $gheight = 400, $gname = 'habitat', $title = '', $number_of_axis = 2) {
   global $session_db, $goutdir, $goutpath, $gouturl;
   $out = array('img_url'=>'', 'debug'=>'', 'query'=>'');
   $query = $result_set['query'];
   // create graph object to show bar graph comparisons of monthle median flow
   //$session_db->querystring = "select extract(month from thisdate) as thismo, median(\"Qout_0\") as qout0, median(\"Qout_2\") as qout2 from ($query) as foo group by thismo ";
   $cdel = ',';
   
   
   // just took a median monthly value, that is, the median value that appears in that month during the model period
   $function_parts = split(',',$function);
   $function = $function_parts[0];
   switch ($function) {
      case 'r_quantile':
         $fopen = 'r_quantile(array_accum(';
         $fclose = "), " . $function_parts[1] . ")";
      break;

      default:
         $fopen = $function . "(";
         $fclose = ")";
      break;
   }
   sort($result_set['valid_cols']);
   $where_sql = '';
   $joiner = 'WHERE';
   foreach ($result_set['valid_cols'] as $thiscol) {
      if (isset($restrictions[$thiscol])) {
         foreach($restrictions[$thiscol] as $restrict) {
            $op = $restrict['op'];
            $val = $restrict['val'];
            $where_sql .= " $joiner \"$thiscol\" $op $val ";
            //print("Adding Restriction: $joiner \"$thiscol\" $op $val <br>");
            $joiner = 'AND';
         }
      }
   }
   $session_db->querystring = "select * ";
   $session_db->querystring .= " from ($query) as foo ";
   $session_db->querystring .= " $where_sql ";
   $session_db->querystring .= " order by thisdate ";
   $session_db->performQuery();
   $flowrecs = $session_db->queryrecords;
   $out['debug'] .= "$session_db->querystring <br>";
   $out['data_records'] = $flowrecs;
   
   $color = array('blue', 'green', 'brown', 'red', 'orange', 'thistle', 'tan', 'black', 'pink');
   $bgs = array();
   $legend_len = 0;
   foreach ($result_set['valid_cols'] as $key => $val) {
      $graph = array();
      $graph['graphrecs'] = $flowrecs;
      if (!(strpos($val, 'Qout') === FALSE)) {
         // flow time series goes on 2nd axis if we did not specify a single axis
         if ($number_of_axis == 1) {
            $graph['yaxis'] = 1;
         } else {
            $graph['yaxis'] = 2;
         }
         $graph['weight'] = 1;
      } else {
         $graph['yaxis'] = 1;
         $graph['weight'] = 2;
      }
      $graph['xcol'] = 'thisdate';
      $graph['ycol'] = $val;
      $graph['plottype'] = 'line';
      $graph['color'] = $color[$key];
      if (isset($result_set['legends'][$val])) {
         $graph['ylegend'] = $result_set['legends'][$val];
      } else {
         $graph['ylegend'] = $val;
      }
      $out['debug'] .= "Adding graph for column $val on axis " . $graph['yaxis'] . "<br>";
      $bgs[] = $graph;
   }
   if ($title == '') {
      $title = ucwords($function) . ' ' . $gname;
   }
   $multibar = array(
      'title'=> $title,
      'xlabel'=>$result_set['xlabel'],
      'ylabel'=>$result_set['ylabel'],
      'num_xlabels'=>15,
      'x_interval'=>15,
      'gwidth'=>$gwidth,
      'gheight'=>$gheight,
      'overlapping'=>0,
      'labelangle'=>90,
      'randomname'=>0,
      'legendlayout'=>LEGEND_HOR,
      'legendpos'=>array(0.5,0.90,'center','bottom'),
      'basename'=>$gname,
      'bargraphs'=>$bgs
   );
   error_reporting(E_ALL);
   $graphurl = showGenericMultiPlot($goutdir, $gouturl, $multibar, $debug);
   $out['img_url'] = $graphurl;
   return $out;
}



?>
